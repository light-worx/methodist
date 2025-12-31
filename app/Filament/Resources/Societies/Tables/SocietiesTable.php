<?php

namespace App\Filament\Resources\Societies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SocietiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query){
                $user=Auth::user();
                if (!$user->hasRole('super_admin')){
                    if ($user->circuits) {
                        return $query->whereIn('circuit_id',$user->circuits);
                    } else if ($user->societies){
                        return $query->whereIn('id',$user->societies);
                    } 
                }
            })
            ->columns([
                TextColumn::make('society')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('circuit.reference')->label('Number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('circuit.circuit')
                    ->sortable()
                    ->searchable()
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
