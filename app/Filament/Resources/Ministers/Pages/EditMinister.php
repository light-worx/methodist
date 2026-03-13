<?php

namespace App\Filament\Resources\Ministers\Pages;

use App\Filament\Resources\Ministers\MinisterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMinister extends EditRecord
{
    protected static string $resource = MinisterResource::class;

    protected static ?string $title= 'Edit minister';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->person->firstname . ' ' . $this->record->person->surname;
    }

    public function getHeading(): string
    {
        return 'Edit clergy: ' . $this->record->person->firstname . ' ' . $this->record->person->surname;
    }

    public function getBreadcrumbs(): array
    {
        
        return [
            url('/admin/ministers/') => 'Ministers',
            $this->record->person->firstname . ' ' . $this->record->person->surname
        ];
    }
}
