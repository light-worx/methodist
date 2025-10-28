<?php

namespace App\Filament\Resources\Preachers\Pages;

use App\Filament\Resources\Preachers\PreacherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPreacher extends EditRecord
{
    protected static string $resource = PreacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
