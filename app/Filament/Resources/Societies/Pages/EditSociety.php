<?php

namespace App\Filament\Resources\Societies\Pages;

use App\Filament\Pages\PreachingPlan;
use App\Filament\Resources\Societies\SocietyResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSociety extends EditRecord
{
    protected static string $resource = SocietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Preaching plan')
                ->url(fn (): string => PreachingPlan::getUrl([
                    'record' => $this->record->circuit_id,
                    'today' => date('Y-m-d'),
                ])),
            DeleteAction::make(),
        ];
    }
}