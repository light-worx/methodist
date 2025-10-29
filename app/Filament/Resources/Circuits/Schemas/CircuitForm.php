<?php

namespace App\Filament\Resources\Circuits\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class CircuitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Edit Circuit')->columnSpanFull()->tabs([
                    Tab::make('Circuit')->columns(2)->schema([
                        TextInput::make('circuit')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))   
                            ->maxLength(199),
                        TextInput::make('reference')->label('Circuit number')
                            ->required()
                            ->live(onBlur:true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (strlen($state)==3){
                                    $set('district_id', intval(substr($state,0,1)));
                                } elseif (strlen($state)==4){
                                    if (substr($state,0,1)=="0"){
                                        $set('district_id', intval(substr($state,1,1)));
                                    } else {
                                        $set('district_id', intval(substr($state,0,2)));
                                    }
                                }
                            })
                            ->numeric(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(199),
                        Select::make('district_id')
                            ->relationship('district', 'district')
                            ->required(),
                        Toggle::make('active')
                    ]),
                    Tab::make('Service settings')->columns(2)->schema([
                        Select::make('midweeks')->label('Midweek services')
                            ->multiple()
                            ->options(function (){
                                return DB::table('midweeks')->select('midweek')->orderBy('midweek')->groupBy('midweek')->get()->pluck('midweek','midweek');
                            }),
                        KeyValue::make('servicetypes')->label('Service types')
                            ->keyLabel('Abbreviation')
                            ->valueLabel('Description'),
                    ]),
                    Tab::make('Plan settings')->columns(2)->schema([
                        Toggle::make('showphone')->label('Show phone numbers on plan'),
                        Select::make('plan_month')->label('First plan starts in this month')
                            ->default(2)    
                            ->options([
                                '1' => 'January',
                                '2' => 'February',
                                '3' => 'March'
                            ]),
                        ]),
                ]),
            ]);
    }
}
