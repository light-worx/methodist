<?php

namespace App\Filament\Resources\Preachers\Pages;

use App\Filament\Resources\Preachers\PreacherResource;
use App\Models\Preacher;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Lightworx\FilamentPwa\Facades\PushNotification;
use Lightworx\FilamentPwa\Models\UserPreference;

class EditPreacher extends EditRecord
{
    protected static string $resource = PreacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notify')
                ->visible(function (Preacher $record){
                    $pref=UserPreference::with('pushSubscriptions')->where('phone',$record->person->phone)->get();
                    if (count($pref)){
                        return true;
                    } else {
                        return false;
                    }
                })
                ->label('Send notification')
                ->schema([
                    Textarea::make('message')
                ])
                ->action(function (array $data, Preacher $record) {
                    $result = PushNotification::toPhone(
                        $record->person->phone,
                        'Message for ' . $record->person->fullname,
                        $data['message'],
                        '/'
                    );
                    if ($result->noDevices) {
                        Notification::make()->warning()->title('No registered devices for this number')->send();
                    } elseif ($result->allDelivered()) {
                        Notification::make()->success()->title('Notification sent')->send();
                    } else {
                        Notification::make()->danger()->title('Delivery failed for some devices')->send();
                    }
                }),
            Action::make('Remove as preacher')
                ->requiresConfirmation()
                ->action(function () {
                    $circuit = $this->record->society->circuit_id;
                    $person = $this->record->person;
                    foreach ($person->circuitroles as $circuitrole) {
                        if ($circuitrole->circuit_id == $circuit) {
                            $status = $circuitrole->status; // must already be an array
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
