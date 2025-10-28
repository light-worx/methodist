<?php

namespace App\Filament\Resources\Districts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('district')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('location'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('bishop')
                    ->numeric(),
                Textarea::make('contact')
                    ->columnSpanFull(),
                TextInput::make('active')
                    ->required()
                    ->numeric(),
            ]);
    }
}
