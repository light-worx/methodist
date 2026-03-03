<?php

namespace App\Filament\Resources\Districts\Schemas;

use App\Models\Person;
use EduardoRibeiroDev\FilamentLeaflet\Enums\TileLayer;
use EduardoRibeiroDev\FilamentLeaflet\Fields\MapPicker;
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
                    ->height(400)
                    ->center(-23.5505, -46.6333)
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
