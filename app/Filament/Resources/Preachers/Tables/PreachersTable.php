<?php

namespace App\Filament\Resources\Preachers\Tables;

use App\Models\Society;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PreachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query){
                $user=Auth::user();
                if (!$user->hasRole('super_admin')){
                    if ($user->circuits) {
                        $societies = Society::whereIn('circuit_id', $user->circuits)->pluck('id');
                        return $query->whereIn('society_id',$societies);
                    } else if ($user->societies){
                        return $query->whereIn('id',$user->societies);
                    } else {
                        return $query->whereRaw('1 = 0');
                    }
                }
            })
            ->columns([
                TextColumn::make('person.surname')
                    ->label('Surname')
                    ->searchable(),
                TextColumn::make('person.firstname')
                    ->label('First name')
                    ->searchable(),
                TextColumn::make('society.society')
                    ->searchable(),
                TextColumn::make('status')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('active')
                    ->boolean(),
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
