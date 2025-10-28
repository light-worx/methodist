<?php

namespace App\Filament\Resources\Ministers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MinisterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('person_id')
                    ->relationship('person', 'title')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('active')
                    ->required()
                    ->numeric(),
                TextInput::make('leadership'),
                TextInput::make('ordained'),
            ]);
    }
}
