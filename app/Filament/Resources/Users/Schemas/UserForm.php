<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Society;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('roles')
                    ->selectablePlaceholder(false)
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->live()
                    ->preload()
                    ->required()
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
                                    'label' => $soc->society . ' (' . $soc->circuit->circuit . ')'
                                ];
                            })->pluck('label', 'value');
                        } elseif (auth()->user()->societies) {
                            return Society::whereIn('id', auth()->user()->societies)->orderBy('society')->get()->map(function ($soc) {
                                return [
                                    'value' => $soc->id,
                                    'label' => $soc->society . ' (' . $soc->circuit->circuit . ')'
                                ];
                            })->pluck('label', 'value');
                        } elseif (auth()->user()->circuits) {
                            return Society::whereHas('circuit', function ($query) {
                                $query->whereIn('id', auth()->user()->circuits);
                            })->orderBy('society')->get()->map(function ($soc) {
                                return [
                                    'value' => $soc->id,
                                    'label' => $soc->society . ' (' . $soc->circuit->circuit . ')'
                                ];
                            })->pluck('label', 'value');
                        } elseif (auth()->user()->districts) {
                            return Society::whereHas('circuit', function ($query) {
                                $query->whereIn('district_id', auth()->user()->districts);
                            })->orderBy('society')->get()->map(function ($soc) {
                                return [
                                    'value' => $soc->id,
                                    'label' => $soc->society . ' (' . $soc->circuit->circuit . ')'
                                ];
                            })->pluck('label', 'value');
                        } else {
                            return [];
                        }
                    })
                    ->searchable(),
            ]);
    }
}
