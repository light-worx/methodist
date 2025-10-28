<?php

namespace App\Filament\Resources\Preachers\Pages;

use App\Filament\Resources\Preachers\PreacherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPreachers extends ListRecords
{
    protected static string $resource = PreacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
