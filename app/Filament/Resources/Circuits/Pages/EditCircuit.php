<?php

namespace App\Filament\Resources\Circuits\Pages;

use App\Filament\Pages\PreachingPlan;
use App\Filament\Resources\Circuits\CircuitResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCircuit extends EditRecord
{
    protected static string $resource = CircuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Preaching plan')
                ->url(fn (): string => PreachingPlan::getUrl([
                    'record' => $this->record,
                    'today' => date('Y-m-d'),
                ])),
            DeleteAction::make(),
        ];
    }
}
