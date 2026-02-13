<?php

namespace App\Filament\Resources\Societies\Tables;

use App\Filament\Pages\PreachingPlan;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
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
                    } else {
                        return $query->whereRaw('1 = 0');
                    }
                }
            })
            ->defaultSort('society')
            ->columns([
                TextColumn::make('society')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('circuit.reference')->label('Circuit no.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('circuit.circuit')->label('Circuit name')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->recordActions([                
                Action::make('Preaching plan')
                    ->icon(Heroicon::Calendar)
                    ->url(fn (Society $record): string => PreachingPlan::getUrl([
                        'record' => $record->circuit_id,
                        'today' => date('Y-m-d'),
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
            ]);
    }
}
