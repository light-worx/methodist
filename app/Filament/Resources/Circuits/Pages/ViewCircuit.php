<?php

namespace App\Filament\Resources\Circuits\Pages;

use App\Filament\Resources\Circuits\CircuitResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCircuit extends ViewRecord
{
    protected static string $resource = CircuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
