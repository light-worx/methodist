<?php

namespace App\Filament\Resources\Preachers\Pages;

use App\Filament\Resources\Preachers\PreacherResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Livewire\Livewire;

class EditPreacher extends EditRecord
{
    protected static string $resource = PreacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Remove as preacher')
                ->requiresConfirmation()
                ->action(function () {
                    $circuit = $this->record->society->circuit_id;
                    $person = $this->record->person;
                    foreach ($person->circuitroles as $circuitrole) {
                        if ($circuitrole->circuit_id == $circuit) {
                            $status = $circuitrole->status; // must already be an array
                            if (($key = array_search("Preacher", $status, true)) !== false) {
                                unset($status[$key]);
                            }
                            $status = array_values($status); // reindex
                            if (empty($status)) {
                                $circuitrole->delete();
                            } else {
                                $circuitrole->status = $status;
                                $circuitrole->save();
                            }
                        }
                    }
                    $this->record->delete();
                    Notification::make()
                        ->title('Preacher removed')
                        ->body('The preacher has been successfully removed.')
                        ->success()
                        ->send();
                    $this->redirect(PreacherResource::getUrl('index'));
                })
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
