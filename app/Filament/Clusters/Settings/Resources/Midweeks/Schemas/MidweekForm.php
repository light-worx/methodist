<?php

namespace App\Filament\Clusters\Settings\Resources\Midweeks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MidweekForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('midweek')
                    ->label('Name')
                    ->required(),
                Select::make('type')
                    ->options([
                        'fixed' => 'Fixed date',
                        'relative' => 'Relative to Easter'
                    ]),
                TextInput::make('month')->numeric(),
                TextInput::make('day')->numeric(),
                TextInput::make('offset')->numeric()
            ]);
    }
}
