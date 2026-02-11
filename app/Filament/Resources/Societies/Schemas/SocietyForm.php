<?php

namespace App\Filament\Resources\Societies\Schemas;

use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

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
                            ->required(),
                        Select::make('circuit_id')
                            ->disabled()
                            ->relationship('circuit', 'circuit')
                            ->required(),
                        TextInput::make('address'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email(),
                        TextInput::make('website')
                            ->url(),
                        Hidden::make('latitude')
                            ->hiddenLabel(),
                        Hidden::make('longitude')
                            ->hiddenLabel(),
                    ]),
                Map::make('location')
                    ->label('Location (click to move the marker)')
                    ->markerIconUrl('/methodist/images/location.png')
                    ->clickable(true)
                    ->showMyLocationButton(false)
                    ->showFullscreenControl(false)
                    ->zoom(18)
                    ->tilesUrl('https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=' . setting('mapbox_token'))
                    ->markerIconSize([36, 36])
                    ->extraStyles([
                        'min-height: 37vh',
                        'border-radius: 10px'
                    ])
                    ->afterStateUpdated(function (Set $set, ?array $state): void {
                        $set('latitude', $state['lat']);
                        $set('longitude', $state['lng']);
                    })
                    ->afterStateHydrated(function ($state, $record, Set $set): void {
                        if ($record){
                            $set('location', [
                                'lat' => $record->latitude,
                                'lng' => $record->longitude
                            ]);
                        }
                    }),
            ]);
    }
}
