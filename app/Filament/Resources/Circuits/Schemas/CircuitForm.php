<?php

namespace App\Filament\Resources\Circuits\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CircuitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('circuit')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('district_id')
                    ->relationship('district', 'id')
                    ->required(),
                TextInput::make('reference')
                    ->required()
                    ->numeric(),
                TextInput::make('plan_month')
                    ->required()
                    ->numeric(),
                TextInput::make('servicetypes'),
                TextInput::make('midweeks'),
                TextInput::make('showphone')
                    ->tel()
                    ->numeric(),
                TextInput::make('active')
                    ->required()
                    ->numeric(),
            ]);
    }
}
