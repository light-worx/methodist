<?php

namespace App\Filament\Resources\People\Tables;

use App\Models\Circuit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PeopleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('surname')
            ->modifyQueryUsing(function (Builder $query){
                $user=Auth::user();
                if (!$user->hasRole('super_admin')){
                    if ($user->districts){
                        $circuits=Circuit::whereIn('district_id',$user->districts)->get()->pluck('id');
                        return $query->whereHas('circuits', function ($q) use ($circuits) {
                            $q->whereIn('circuits.id', $circuits);
                        });
                    } else if ($user->circuits){
                        $circuits=$user->circuits;
                        return $query->whereHas('circuits', function ($q) use ($circuits) {
                            $q->whereIn('circuits.id', $circuits);
                        });
                    }
                }
            })
            ->columns([
                TextColumn::make('surname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('firstname')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('phone')
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
