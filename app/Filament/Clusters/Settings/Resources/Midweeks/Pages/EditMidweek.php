<?php

namespace App\Filament\Clusters\Settings\Resources\Midweeks\Pages;

use App\Filament\Clusters\Settings\Resources\Midweeks\MidweekResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMidweek extends EditRecord
{
    protected static string $resource = MidweekResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
