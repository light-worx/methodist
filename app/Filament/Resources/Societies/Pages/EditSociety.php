<?php

namespace App\Filament\Resources\Societies\Pages;

use App\Filament\Resources\Societies\SocietyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSociety extends EditRecord
{
    protected static string $resource = SocietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
