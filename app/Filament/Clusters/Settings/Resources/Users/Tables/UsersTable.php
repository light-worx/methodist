<?php

namespace App\Filament\Clusters\Settings\Resources\Users\Tables;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Society;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Actions\Impersonate;
 

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
