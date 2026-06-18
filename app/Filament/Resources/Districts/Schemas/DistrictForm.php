<?php

namespace App\Filament\Resources\Districts\Schemas;

use App\Models\Person;
use App\Services\MapCoordinateService;
use EduardoRibeiroDev\FilamentLeaflet\Enums\TileLayer;
use EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('district')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('bishop')
                    ->options( function () {
                        $persons = Person::whereHas('minister')->orderBy('surname')->orderBy('firstname')->get();
                        foreach ($persons as $person){
                            $options[$person->id]=$person->surname . ", " . $person->firstname;
                        }
                        return $options;
                    })
                    ->searchable(),
                Toggle::make('active'),
                RichEditor::make('contact')->label('District office details'),
                MapPicker::make('location')
                    ->height(418)
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record) {
                        if ($record && $record->latitude && $record->longitude) {
                            return new \EduardoRibeiroDev\FilamentLeaflet\ValueObjects\Coordinate(
                                $record->latitude,
                                $record->longitude
                            );
                        }
                        $coords = MapCoordinateService::resolve(request()->query('circuit_id'));
                        return new \EduardoRibeiroDev\FilamentLeaflet\ValueObjects\Coordinate(
                            $coords['latitude'],
                            $coords['longitude']
                        );
                    })
                    ->default(function () {
                        $coords = MapCoordinateService::resolve(request()->query('circuit_id'));
                        return new \EduardoRibeiroDev\FilamentLeaflet\ValueObjects\Coordinate(
                            $coords['latitude'],
                            $coords['longitude']
                        );
                    })
                    ->center(function () {
                        $coords = MapCoordinateService::resolve(request()->query('circuit_id'));
                        return [$coords['latitude'], $coords['longitude']];
                    })
                    ->zoom(18)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state instanceof \EduardoRibeiroDev\FilamentLeaflet\ValueObjects\Coordinate) {
                            $set('latitude', $state->lat);
                            $set('longitude', $state->lng);
                        }
                    })
                    ->tileLayersUrl([
                        'Mapbox' => 'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=' . setting('mapbox_token'),
                        'OpenStreetMap' => TileLayer::OpenStreetMap,
                        'Satellite' => TileLayer::GoogleSatellite
                    ]),
                Hidden::make('latitude'),
                Hidden::make('longitude'),
            ]);
    }
}
