<?php

namespace App\Filament\Clusters\Settings\Resources\Lections\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year'),
                TextInput::make('lection')
                    ->required(),
                TextInput::make('ot')
                    ->required(),
                TextInput::make('psalm')
                    ->required(),
                TextInput::make('nt')
                    ->required(),
                TextInput::make('gospel')
                    ->required(),
            ]);
    }
}
