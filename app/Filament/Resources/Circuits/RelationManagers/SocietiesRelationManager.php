<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SocietiesRelationManager extends RelationManager
{
    protected static string $relationship = 'societies';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('society')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('address'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('website')
                    ->url(),
                TextInput::make('location'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('society')
            ->defaultSort('society')
            ->columns([
                TextColumn::make('society')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('services.servicetime')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
            ])
            ->recordActions([
                EditAction::make()
            ])
            ->toolbarActions([
            ]);
    }
}
