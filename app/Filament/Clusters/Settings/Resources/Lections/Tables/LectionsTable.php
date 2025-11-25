<?php

namespace App\Filament\Clusters\Settings\Resources\Lections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->searchable(),
                TextColumn::make('lection')
                    ->searchable(),
                TextColumn::make('ot')
                    ->searchable(),
                TextColumn::make('psalm')
                    ->searchable(),
                TextColumn::make('nt')
                    ->searchable(),
                TextColumn::make('gospel')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
