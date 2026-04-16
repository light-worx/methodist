<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\UserInvitationMail;
use App\Models\Circuit;
use App\Models\District;
use App\Models\Invitation;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inviteUser')->label('Invite a new user')
                ->schema([
                    TextInput::make('email')->email()->required(),
                    Select::make('roles')
                        ->required()
                        ->placeholder('Select a role')
                        ->label('Role')
                        ->live()
                        ->preload()
                        ->options(function () {
                            $user = auth()->user();

                            if ($user->hasRole('super_admin')) {
                                return Role::orderBy('name')->pluck('name', 'id');
                            }

                            if ($user->hasRole('District user')) {
                                return Role::whereIn('name', [
                                    'District user',
                                    'Circuit user',
                                    'Society user',
                                ])->pluck('name', 'id');
                            }

                            if ($user->hasRole('Circuit user')) {
                                return Role::whereIn('name', [
                                    'Circuit user',
                                    'Society user',
                                ])->pluck('name', 'id');
                            }

                            if ($user->hasRole('Society user')) {
                                return Role::whereIn('name', [
                                    'Society user',
                                ])->pluck('name', 'id');
                            }

                            return collect();
                        })
                        ->formatStateUsing(function ($record) {
                            return $record?->roles()->first()?->id;
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('districts', null);
                            $set('circuits', null);
                            $set('societies', null);
                        }),
                    Select::make('districts')->multiple()
                        ->visible(function (Get $get) {
                            $roleId = $get('roles');
                            if (! $roleId) {
                                return false;
                            }
                            return Role::find($roleId)?->name === 'District user';
                        })
                        ->options(function () {
                            if (auth()->user()->hasRole('super_admin')) {
                                return District::orderBy('district')->get()->pluck('district', 'id');
                            } elseif (auth()->user()->districts) {
                                return District::whereIn('id', auth()->user()->districts)->orderBy('district')->get()->pluck('district', 'id');
                            } else {
                                return [];
                            }
                        })
                        ->searchable(),
                    Select::make('circuits')->multiple()
                        ->visible(function (Get $get) {
                            $roleId = $get('roles');
                            if (! $roleId) {
                                return false;
                            }
                            return Role::find($roleId)?->name === 'Circuit user';
                        })
                        ->options(function (){
                            if (auth()->user()->hasRole('super_admin')) {
                                return Circuit::orderBy('circuit')->get()->map(function ($circ) {
                                    return [
                                        'value' => $circ->id,
                                        'label' => $circ->circuit . ' (' . $circ->reference . ')'
                                    ];
                            })->pluck('label', 'value');
                            } elseif (auth()->user()->districts) {
                                return Circuit::whereIn('district_id', auth()->user()->districts)->orderBy('circuit')->get()->map(function ($circ) {
                                    return [
                                        'value' => $circ->id,
                                        'label' => $circ->circuit . ' (' . $circ->reference . ')'
                                    ];
                                })->pluck('label', 'value');
                            } elseif (auth()->user()->circuits) {
                                return Circuit::whereIn('id', auth()->user()->circuits)->orderBy('circuit')->get()->map(function ($circ) {
                                    return [
                                        'value' => $circ->id,
                                        'label' => $circ->circuit . ' (' . $circ->reference . ')'
                                    ];
                                })->pluck('label', 'value');
                            } else {
                                return [];
                            }
                        })
                        ->searchable(),
                    Select::make('societies')->multiple()
                        ->visible(function (Get $get) {
                            $roleId = $get('roles');
                            if (! $roleId) {
                                return false;
                            }
                            return Role::find($roleId)?->name === 'Society user';
                        })
                        ->options(function (){
                            if (auth()->user()->hasRole('super_admin')) {
                                return Society::orderBy('society')->get()->map(function ($soc) {
                                    return [
                                        'value' => $soc->id,
                                        'label' => $soc->society . ' (' . $soc->circuit->circuitname . ')'
                                    ];
                                })->pluck('label', 'value');
                            } elseif (auth()->user()->societies) {
                                return Society::whereIn('id', auth()->user()->societies)->orderBy('society')->get()->map(function ($soc) {
                                    return [
                                        'value' => $soc->id,
                                        'label' => $soc->society . ' (' . $soc->circuit->circuitname . ')'
                                    ];
                                })->pluck('label', 'value');
                            } elseif (auth()->user()->circuits) {
                                return Society::whereHas('circuit', function ($query) {
                                    $query->whereIn('id', auth()->user()->circuits);
                                })->orderBy('society')->get()->map(function ($soc) {
                                    return [
                                        'value' => $soc->id,
                                        'label' => $soc->society . ' (' . $soc->circuit->circuitname . ')'
                                    ];
                                })->pluck('label', 'value');
                            } elseif (auth()->user()->districts) {
                                return Society::whereHas('circuit', function ($query) {
                                    $query->whereIn('district_id', auth()->user()->districts);
                                })->orderBy('society')->get()->map(function ($soc) {
                                    return [
                                        'value' => $soc->id,
                                        'label' => $soc->society . ' (' . $soc->circuit->circuitname . ')'
                                    ];
                                })->pluck('label', 'value');
                            } else {
                                return [];
                            }
                        })
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    if (!isset($data['districts'])) {
                        $data['districts'] = null;
                    }
                    if (!isset($data['circuits'])) {
                        $data['circuits'] = null;
                    }
                    if (!isset($data['societies'])) {
                        $data['societies'] = null;
                    }
                    $invitation = Invitation::create([
                        'email' => $data['email'],
                        'role' => $data['roles'],
                        'districts' => $data['districts'] ? implode(',', $data['districts']) : null,
                        'circuits' => $data['circuits'] ? implode(',', $data['circuits']) : null,
                        'societies' => $data['societies'] ? implode(',', $data['societies']) : null,
                        'token' => Str::uuid(),
                        'invited_by' => Auth::user()->name,
                        'expires_at' => now()->addDays(7),
                    ]);
                    Mail::to($data['email'])->send(
                        new UserInvitationMail($invitation)
                    );
                    Notification::make()
                        ->title('A sign-up invitation has been sent to ' . $data['email'])
                        ->success()
                        ->send();
                })
        ];
    }
}
