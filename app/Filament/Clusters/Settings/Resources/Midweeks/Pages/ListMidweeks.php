<?php

namespace App\Filament\Clusters\Settings\Resources\Midweeks\Pages;

use App\Filament\Clusters\Settings\Resources\Midweeks\MidweekResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMidweeks extends ListRecords
{
    protected static string $resource = MidweekResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
