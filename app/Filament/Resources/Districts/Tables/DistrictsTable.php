<?php

namespace App\Filament\Resources\Districts\Tables;

use App\Models\Circuit;
use App\Models\Society;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DistrictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query){
                $user=Auth::user();
                if (!$user->hasRole('super_admin')){
                    if ($user->districts){
                        return $query->whereIn('id',$user->districts);
                    } else if ($user->circuits) {
                        $districts=Circuit::whereIn('id',$user->circuits)->select('district_id')->get()->pluck('district_id');
                        return $query->whereIn('id',$districts);
                    } else if ($user->societies) {
                        $circuits=Society::whereIn('id',$user->societies)->select('circuit_id')->get()->pluck('circuit_id');
                        $districts=Circuit::whereIn('id',$circuits)->select('district_id')->get()->pluck('district_id');
                        return $query->whereIn('id',$districts);
                    } else {
                        return $query->where('id',0);
                    }
                }
            })
            ->columns([
                TextColumn::make('id')->label('Number')
                    ->searchable(),
                TextColumn::make('district')
                    ->searchable(),
                IconColumn::make('active')
                    ->icon(fn (string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle'
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'danger',
                        '1' => 'success'
                    })
            ])
            ->filters([
                Filter::make('hide_inactive_districts')
                    ->query(fn (Builder $query): Builder => $query->where('active', 1))
                    ->default()
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
