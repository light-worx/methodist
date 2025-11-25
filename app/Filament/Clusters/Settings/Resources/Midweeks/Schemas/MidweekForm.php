<?php

namespace App\Filament\Clusters\Settings\Resources\Midweeks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MidweekForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('midweek')
                    ->required(),
                DatePicker::make('servicedate'),
            ]);
    }
}
