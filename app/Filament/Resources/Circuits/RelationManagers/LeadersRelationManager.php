<?php

namespace App\Filament\Resources\Circuits\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadersRelationManager extends RelationManager
{
    protected static string $relationship = 'persons';

    protected static ?string $title = 'Circuit leaders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('surname')
            ->modifyQueryUsing(
                fn (Builder $query) => $query->whereHas('circuitroles', function (Builder $q) {
                    $q->where('status', 'like', '%Circuit%');
                })
            )
            ->defaultSort('surname')
            ->columns([
                TextColumn::make('surname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('firstname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')->searchable()
                    ->formatStateUsing(function ($state){
                        return implode(', ',json_decode($state));
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                EditAction::make()
            ])
            ->toolbarActions([
            ]);
    }
}
