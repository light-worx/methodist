<?php

namespace App\Filament\Clusters\Settings\Resources\Lections\Pages;

use App\Filament\Clusters\Settings\Resources\Lections\LectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLections extends ListRecords
{
    protected static string $resource = LectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
