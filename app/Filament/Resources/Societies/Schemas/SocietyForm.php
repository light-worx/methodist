<?php

namespace App\Filament\Resources\Societies\Schemas;

use App\Models\Circuit;
use App\Models\District;
use App\Models\Log;
use EduardoRibeiroDev\FilamentLeaflet\Enums\TileLayer;
use EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SocietyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->dense()
                    ->schema([
                        TextInput::make('society')
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->required(),
                        Hidden::make('circuit_id')->default(fn () => request()->query('circuit_id')),
                        Select::make('circuit')
                            ->disabled()
                            ->default(fn () => request()->query('circuit_id'))
                            ->label('Circuit')
                            ->relationship('circuit', 'circuit'),
                        TextInput::make('address'),
                        TextInput::make('slug'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email(),
                        TextInput::make('website')
                            ->url(),
                    ]),
                MapPicker::make('location')
                    ->height(418)
                    ->center(function (){
                        $circuit = Circuit::with(['societies' => function ($q) { $q->whereNotNull('latitude')->whereNotNull('longitude');}])->find(request()->query('circuit_id'));
                        if ($circuit){
                            if ($circuit->societies){
                                $society = $circuit->societies->last();
                                return [$society->latitude, $society->longitude];
                            } else {
                                $district = District::find($circuit->district_id);
                                if ($district && $district->latitude && $district->longitude){
                                    return [$district->latitude, $district->longitude];
                                } else {
                                    return [setting('default_latitude', -26.180611), setting('default_longitude', 28.1046067)];
                                }
                            }
                        } else {
                            return [setting('default_latitude', -26.180611), setting('default_longitude', 28.1046067)];
                        }
                    })
                    ->zoom(18)
                    ->tileLayersUrl([
                        'Mapbox' => 'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=' . setting('mapbox_token'),
                        'OpenStreetMap' => TileLayer::OpenStreetMap,
                        'Satellite' => TileLayer::GoogleSatellite
                    ]),
                TextEntry::make('log_details')
                    ->hiddenLabel(true)
                    ->state(function ($record){
                        $log = Log::where('model','Society')->where('action','Created')->where('model_id',$record->id)->orderBy('created_at','desc')->first();
                        if ($log) {
                            return "Added by " . $log->user->name . " on " . $log->created_at->format('d/m/Y');
                        }
                    })->hiddenOn('create'),
            ]);
    }
}
