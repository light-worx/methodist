<?php

namespace App\Filament\Resources\Societies\Schemas;

use EduardoRibeiroDev\FilamentLeaflet\Enums\TileLayer;
use EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
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
                    ->height(400)
                    ->center(function (){
                        return [setting('default_latitude', -26.180611), setting('default_longitude', 28.1046067)];
                    })
                    ->zoom(18)
                    ->autoCenter()  // Auto-center to user's location
                    ->tileLayersUrl([
                        'Mapbox' => 'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=' . setting('mapbox_token'),
                        'OpenStreetMap' => TileLayer::OpenStreetMap,
                        'Satellite' => TileLayer::GoogleSatellite
                    ]),
            ]);
    }
}
