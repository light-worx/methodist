<?php

namespace App\Filament\Resources\Preachers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PreacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('person_id')
                    ->relationship('person', 'title')
                    ->required(),
                Select::make('society_id')
                    ->relationship('society', 'id'),
                TextInput::make('status')
                    ->required(),
                TextInput::make('leadership'),
                TextInput::make('induction'),
                TextInput::make('number'),
                TextInput::make('active')
                    ->numeric(),
            ]);
    }
}
