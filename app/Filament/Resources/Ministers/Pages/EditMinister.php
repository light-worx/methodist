<?php

namespace App\Filament\Resources\Ministers\Pages;

use App\Filament\Resources\Ministers\MinisterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMinister extends EditRecord
{
    protected static string $resource = MinisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
