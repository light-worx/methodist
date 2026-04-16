<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Society;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;
 

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query){
                $user=Auth::user();
                if (!$user->hasRole('super_admin')){
                    if ($user->districts) {
                        return $query->where(function($q) use ($user) {
                            // Get all circuits in user's districts
                            $circuitIds = \App\Models\Circuit::whereIn('district_id', $user->districts)
                                ->pluck('id')
                                ->toArray();
                            
                            // Match users with circuits in those districts
                            foreach ($circuitIds as $circuitId) {
                                $q->orWhereJsonContains('circuits', $circuitId);
                            }
                            
                            // Get all societies in those circuits
                            $societyIds = \App\Models\Society::whereIn('circuit_id', $circuitIds)
                                ->pluck('id')
                                ->toArray();
                            
                            // Match users with societies in those districts
                            foreach ($societyIds as $societyId) {
                                $q->orWhereJsonContains('societies', $societyId);
                            }
                        });
                    }
                    if ($user->circuits) {
                        return $query->where(function($q) use ($user) {
                            // Match users with overlapping circuits
                            foreach ($user->circuits as $circuitId) {
                                $q->orWhereJsonContains('circuits', $circuitId);
                            }
                            
                            // Match users with societies belonging to those circuits
                            $societyIds = \App\Models\Society::whereIn('circuit_id', $user->circuits)
                                ->pluck('id')
                                ->toArray();
                            
                            foreach ($societyIds as $societyId) {
                                $q->orWhereJsonContains('societies', $societyId);
                            }
                        });
                    }
                    if ($user->societies) {
                        return $query->where(function($q) use ($user) {
                            // Match users with overlapping societies
                            foreach ($user->societies as $societyId) {
                                $q->orWhereJsonContains('societies', $societyId);
                            }
                        });
                    }
                }
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('roles.name')->badge(),
                TextColumn::make('districts')
                    ->state(function ($record){
                        if ($record->districts){
                            $districts = District::whereIn('id',$record->districts)->get()->pluck('district');
                            return $districts;
                        }
                    })
                    ->badge(),
                TextColumn::make('circuits')
                    ->state(function ($record){
                        if ($record->circuits){
                            $circuits = Circuit::whereIn('id',$record->circuits)->get()->pluck('circuit');
                            return $circuits;
                        }
                    })
                    ->badge(),
                TextColumn::make('societies')
                    ->state(function ($record){
                        if ($record->societies){
                            $societies = Society::whereIn('id',$record->societies)->get()->pluck('society');
                            if ($societies->count()>2){
                                return $societies->take(2)->push(' +' . ($societies->count() - 2) . ' more'); // Show first 2 and indicate more
                            }
                            return $societies;
                        }
                    })
                    ->badge(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Impersonate::make()->label('')->redirectTo(route('filament.admin.pages.dashboard'))
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
