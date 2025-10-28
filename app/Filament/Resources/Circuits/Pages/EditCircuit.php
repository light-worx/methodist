<?php

namespace App\Filament\Resources\Circuits\Pages;

use App\Filament\Resources\Circuits\CircuitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCircuit extends EditRecord
{
    protected static string $resource = CircuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
