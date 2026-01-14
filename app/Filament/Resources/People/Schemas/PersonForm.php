<?php

namespace App\Filament\Resources\People\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')->label('First name')
                    ->required(),
                TextInput::make('surname')
                    ->required(),
                TextInput::make('title'),
                TextInput::make('phone')
                    ->tel(),
                FileUpload::make('image')
                    ->image(),
            ]);
    }
}
