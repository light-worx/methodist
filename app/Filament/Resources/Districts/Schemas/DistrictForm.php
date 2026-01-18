<?php

namespace App\Filament\Resources\Districts\Schemas;

use App\Models\Person;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
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
                Map::make('location')
                    ->markerIconUrl('/methodist/images/location.png')
                    ->clickable(true)
                    ->zoom(18)
                    ->tilesUrl('https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoiYmlzaG9wbSIsImEiOiJjanNjenJ3MHMwcWRyM3lsbmdoaDU3ejI5In0.M1x6KVBqYxC2ro36_Ipz_w')
                    ->markerIconSize([36, 36])
                    ->extraStyles([
                        'min-height: 50vh',
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
                Hidden::make('latitude')
                    ->hiddenLabel(),
                Hidden::make('longitude')
                    ->hiddenLabel(),
            ]);
    }
}
