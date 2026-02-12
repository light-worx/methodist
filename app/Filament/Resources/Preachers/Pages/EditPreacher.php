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

    public function getTitle(): string
    {
        return $this->record->person->firstname . ' ' . $this->record->person->surname;
    }

    public function getHeading(): string
    {
        return 'Edit preacher: ' . $this->record->person->firstname . ' ' . $this->record->person->surname;
    }

    public function getBreadcrumbs(): array
    {
        
        return [
            url('/admin/circuits/' . $this->record->society->circuit->id . '/edit?relation=2&') => 'Preachers',
            $this->record->person->firstname . ' ' . $this->record->person->surname
        ];
    }
}
