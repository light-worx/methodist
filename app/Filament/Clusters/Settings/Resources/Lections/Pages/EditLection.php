<?php

namespace App\Filament\Clusters\Settings\Resources\Lections\Pages;

use App\Filament\Clusters\Settings\Resources\Lections\LectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLection extends EditRecord
{
    protected static string $resource = LectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
