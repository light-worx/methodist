<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Society;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

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
                Select::make('roles')->multiple()->relationship('roles', 'name'),
                Select::make('districts')->multiple()
                    ->options(District::orderBy('district')->get()->pluck('district', 'id'))
                    ->searchable(),
                Select::make('circuits')->multiple()
                    ->options(Circuit::orderBy('circuit')->get()->map(function ($circ) {
                        return [
                            'value' => $circ->id,
                            'label' => $circ->circuit . ' (' . $circ->reference . ')'
                        ];
                    })->pluck('label', 'value'))
                    ->searchable(),
                Select::make('societies')->multiple()
                    ->options(Society::with('circuit')->orderBy('society')->get()->map(function ($soc) {
                        return [
                            'value' => $soc->id,
                            'label' => $soc->society . ' (' . $soc->circuit->reference . ')'
                        ];
                    })->pluck('label', 'value'))
                    ->searchable(),
            ]);
    }
}
