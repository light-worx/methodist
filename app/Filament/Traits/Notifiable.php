<?php

namespace App\Filament\Traits;

use App\Jobs\SendPushJob;
use App\Models\Minister;
use App\Models\Preacher;
use App\Models\PushMessage;
use App\Models\UserPreference;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

trait Notifiable {
    protected function getNotificationAction($recordType): Action
    {
        return Action::make('sendNotification')->label('Send message to ' . $recordType)
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g. Circuit news'),
                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->maxLength(4000)
                    ->rows(4)
                    ->placeholder('Your message…')
                    ->helperText('Max 4000 characters'),
                TextInput::make('url')
                    ->label('Link (optional)')
                    ->placeholder('/lectionary')
                    ->default('/')
                    ->helperText('Page to open when the notification is tapped'),
            ])
            ->action(function ($data,$record = null) use ($recordType) {
                $id = $record?->id;
                if (!$id && property_exists($this, 'record')) {
                    $id = $this->record?->id;
                } else if (!$id && method_exists($this, 'getOwnerRecord')) {
                    $id = $this->getOwnerRecord()?->id;
                }
                if ($recordType=="minister"){
                    $person = Minister::with('person')->find($id)->person;
                } elseif ($recordType=="preacher"){
                    $person = Preacher::with('person')->find($id)->person;
                }
                if ($person->phone){
                    $pref = UserPreference::where('mobile', $person->phone)->whereNotNull('push_endpoint')->whereNotNull('push_keys')->first();
                }
                return $this->dispatch('individual', $data['title'], $data['body'], $data['url'], $pref->id);
                $message = PushMessage::create([
                    'user_id'            => Auth::id() ?? 0,  // 0 = system (scheduled job)
                    'type'               => $recordType,
                    'user_preference_id' => $prefId,
                    'title'              => $title,
                    'body'               => $body,
                    'url'                => $url,
                    'sent_at'            => now(),
                ]);
                SendPushJob::dispatch($message->id, $pref->id);
            });
    }
}