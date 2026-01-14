<?php

namespace App\Filament\Resources\Circuits\Tables;

use App\Filament\Pages\PreachingPlan;
use App\Models\Circuit;
use App\Models\Society;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
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
                    if ($user->districts) {
                        return $query->whereIn('district_id',$user->districts);
                    } else if ($user->circuits){
                        return $query->whereIn('id',$user->circuits);
                    } else if ($user->societies) {
                        $circuits=Society::whereIn('id',$user->societies)->select('circuit_id')->get()->pluck('circuit_id');
                        return $query->whereIn('id',$circuits);
                    } else {
                        return $query->whereRaw('1 = 0');
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
                    ->boolean()
            ])
            ->filters([
                Filter::make('hide_inactive_circuits')
                    ->query(fn (Builder $query): Builder => $query->where('active', 1))
                    ->default()
            ])
            ->recordActions([
                Action::make('Preaching plan')
                    ->label('Plan')
                    ->icon(Heroicon::Calendar)
                    ->url(fn (Circuit $record): string => PreachingPlan::getUrl([
                        'record' => $record,
                        'today' => date('Y-m-d'),
                    ])),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
