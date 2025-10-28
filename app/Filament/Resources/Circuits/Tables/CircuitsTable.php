<?php

namespace App\Filament\Resources\Circuits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CircuitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('circuit')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('district.id')
                    ->searchable(),
                TextColumn::make('reference')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('plan_month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('showphone')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('active')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
