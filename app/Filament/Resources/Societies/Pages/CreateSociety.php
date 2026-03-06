<?php

namespace App\Filament\Resources\Societies\Pages;

use App\Filament\Resources\Societies\SocietyResource;
use App\Models\Log;
use Filament\Resources\Pages\CreateRecord;

class CreateSociety extends CreateRecord
{
    protected static string $resource = SocietyResource::class;

    protected function afterCreate(): void
    {
        Log::create([
            'user_id' => auth()->id(),
            'model' => 'Society',
            'model_id' => $this->record->id,
            'action' => 'Created'
        ]);
    }
}
