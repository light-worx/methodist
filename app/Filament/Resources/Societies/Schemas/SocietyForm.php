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
                    ->markerIconUrl('/methodist/images/location.png')
                    ->clickable(true)
                    ->zoom(18)
                    ->tilesUrl('https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoiYmlzaG9wbSIsImEiOiJjanNjenJ3MHMwcWRyM3lsbmdoaDU3ejI5In0.M1x6KVBqYxC2ro36_Ipz_w')
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
