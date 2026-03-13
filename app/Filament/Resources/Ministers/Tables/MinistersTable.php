<?php

namespace App\Filament\Resources\Ministers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MinistersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                $query->join('persons', 'ministers.person_id', '=', 'persons.id')
                    ->select('ministers.*')
                    ->orderBy('persons.surname')
                    ->orderBy('persons.firstname');
                if (! $user->hasRole('super_admin')) {
                    if ($user->circuits) {
                        return $query->whereExists(function ($sub) use ($user) {
                            $sub->selectRaw(1)
                                ->from('circuit_person')
                                ->whereColumn('circuit_person.person_id', 'ministers.person_id')
                                ->whereIn('circuit_person.circuit_id', $user->circuits);
                        });
                    } else {
                        return $query->whereRaw('1 = 0');
                    }
                }
                return $query;
            })
            ->defaultSort('persons.surname')
            ->columns([
                TextColumn::make('person.surname')
                    ->label('Surname')
                    ->searchable(),
                TextColumn::make('person.firstname')
                    ->label('First name')
                    ->searchable(),
                TextColumn::make('person.circuits.circuit')
                    ->searchable(),
            ])
            ->recordTitleAttribute('fullname')
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
