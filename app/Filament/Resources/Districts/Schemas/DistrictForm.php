<?php

namespace App\Filament\Resources\Districts\Schemas;

use App\Models\Person;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                TextInput::make('location'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Select::make('bishop')
                    ->options( function () {
                        $persons = Person::whereHas('minister')->orderBy('surname')->orderBy('firstname')->get();
                        foreach ($persons as $person){
                            $options[$person->id]=$person->surname . ", " . $person->firstname;
                        }
                        return $options;
                    })
                    ->searchable(),
                RichEditor::make('contact')->label('District office details')
                    ->columnSpanFull(),
                Toggle::make('active'),
            ]);
    }
}
