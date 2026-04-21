<?php

namespace App\Filament\Resources\Ideas\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('circuit_id')
                    ->relationship('circuit', 'id')
                    ->required(),
                TextInput::make('idea'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                FileUpload::make('image')
                    ->image(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('published')
                    ->required(),
            ]);
    }
}
