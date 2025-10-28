<?php

namespace App\Filament\Resources\Societies\Pages;

use App\Filament\Resources\Societies\SocietyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocieties extends ListRecords
{
    protected static string $resource = SocietyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
