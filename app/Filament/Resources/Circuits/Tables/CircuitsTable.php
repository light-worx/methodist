<?php

namespace App\Filament\Resources\Circuits\Tables;

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

class CircuitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query){
                $user=Auth::user();
                if (!$user->hasRole('super_admin')){
                    if ($user->circuits){
                        return $query->whereIn('id',$user->circuits);
                    } else if ($user->societies) {
                        $circuits=Society::whereIn('id',$user->societies)->select('circuit_id')->get()->pluck('circuit_id');
                        return $query->whereIn('id',$circuits);
                    }
                }
            })
            ->columns([
                TextColumn::make('reference')->label('No.')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('circuit')
                    ->searchable(),
                TextColumn::make('district.district')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('active')
                    ->icon(fn (string $state): string => match ($state) {
                        '0' => 'heroicon-o-x-circle',
                        '1' => 'heroicon-o-check-circle'
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'danger',
                        '1' => 'success'
                    }),
            ])
            ->filters([
                Filter::make('hide_inactive_circuits')
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
