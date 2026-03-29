<?php

namespace App\Filament\Resources\Ministers\Pages;

use App\Filament\Resources\Ministers\MinisterResource;
use App\Models\Minister;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Lightworx\FilamentPwa\Facades\PushNotification;
use Lightworx\FilamentPwa\Models\UserPreference;

class EditMinister extends EditRecord
{
    protected static string $resource = MinisterResource::class;

    protected static ?string $title= 'Edit minister';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notify')
                ->visible(function (Minister $record){
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
                ->action(function (array $data, Minister $record) {
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
