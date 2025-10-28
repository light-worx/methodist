<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('circuit_id')
                    ->relationship('circuit', 'id')
                    ->required(),
                DateTimePicker::make('meetingdate'),
                Select::make('society_id')
                    ->relationship('society', 'id'),
                TextInput::make('description'),
                TextInput::make('quarter'),
            ]);
    }
}
