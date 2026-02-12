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
                TextInput::make('password')
                    ->hiddenOn('edit')
                    ->password()
                    ->revealable()
                    ->required(),
                Select::make('roles')
                    ->live()
                    ->options(function () {
                        $roles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name', 'id');
                        if (auth()->user()->hasRole('super_admin')) {
                            return $roles;
                        } else {
                            return $roles->filter(function ($role) {
                                return $role !== 'super_admin';
                            });
                        }
                    })->preload()->required(),
                Select::make('districts')->multiple()
                    ->visible(function (Get $get) {
                        $roleId = $get('roles');
                        if (!$roleId) {
                            return false;
                        }
                        
                        $role = Role::find($roleId);
                        return $role && $role->name === 'District user';
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
                        if (!$roleId) {
                            return false;
                        }
                        
                        $role = Role::find($roleId);
                        return $role && $role->name === 'Circuit user';
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
                        if (!$roleId) {
                            return false;
                        }
                        
                        $role = Role::find($roleId);
                        return $role && $role->name === 'Society user';
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
