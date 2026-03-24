<?php

namespace App\Filament\Actions;

use App\Models\UserPreference;
use App\Services\NotificationDispatcher;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class SendPushNotificationAction
{
    /**
     * Action for a circuit page.
     * The record is expected to be a Circuit model with an ->id property.
     *
     * Usage in a Filament page (e.g. ViewCircuit, EditCircuit):
     *
     *   protected function getHeaderActions(): array
     *   {
     *       return [
     *           SendPushNotificationAction::forCircuit(),
     *       ];
     *   }
     */
    public static function forCircuit(): Action
    {
        return Action::make('notifyCircuit')
            ->label('Notify circuit')
            ->icon('heroicon-o-bell')
            ->color('warning')
            ->authorize(fn () => auth()->user()->hasAnyRole(['super_admin', 'circuit_admin']))
            ->schema(self::notificationForm())
            ->action(function (array $data, $record) {
                $dispatcher = app(NotificationDispatcher::class);

                $message = $dispatcher->toCircuit(
                    circuitId: $record->id,
                    title:     $data['title'],
                    body:      $data['body'],
                    url:       $data['url'] ?? '/',
                );

                Notification::make()
                    ->title("Sent to {$message->recipient_count} subscriber(s)")
                    ->success()
                    ->send();
            });
    }

    /**
     * Action for a person page.
     * The record is expected to be a Person model with a ->phone property.
     * We match person->phone to user_preferences.mobile (normalising if needed).
     *
     * Usage in a Filament page (e.g. ViewPerson, EditPerson):
     *
     *   protected function getHeaderActions(): array
     *   {
     *       return [
     *           SendPushNotificationAction::forPerson(),
     *       ];
     *   }
     */
    public static function forPerson(): Action
    {
        return Action::make('notifyPerson')
            ->label('Send notification')
            ->icon('heroicon-o-bell')
            ->color('warning')
            ->authorize(fn () => auth()->user()->hasAnyRole(['super_admin', 'circuit_admin']))
            ->schema(self::notificationForm())
            ->before(function ($record, Action $action) {
                // Find the matching user_preference for this person
                $pref = self::preferenceForPerson($record);
                if (! $pref) {
                    Notification::make()
                        ->title('This person has not registered in the app.')
                        ->warning()
                        ->send();

                    $action->cancel();
                    return;
                }

                if (! $pref->push_endpoint) {
                    Notification::make()
                        ->title('This person has not enabled push notifications.')
                        ->warning()
                        ->send();

                    $action->cancel();
                }
            })
            ->action(function (array $data, $record) {
                $pref = self::preferenceForPerson($record);

                if (! $pref) {
                    return;
                }

                $dispatcher = app(NotificationDispatcher::class);

                $dispatcher->toIndividual(
                    userPreferenceId: $pref->id,
                    title:            $data['title'],
                    body:             $data['body'],
                    url:              $data['url'] ?? '/',
                );

                Notification::make()
                    ->title('Notification sent.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Broadcast action — super admin only.
     * Add to any global admin page or dashboard.
     */
    public static function broadcast(): Action
    {
        return Action::make('broadcast')
            ->label('Broadcast to all')
            ->icon('heroicon-o-megaphone')
            ->color('danger')
            ->authorize(fn () => auth()->user()->hasRole('super_admin'))
            ->requiresConfirmation()
            ->modalDescription('This will send a push notification to every subscriber across all circuits.')
            ->schema(self::notificationForm())
            ->action(function (array $data) {
                $dispatcher = app(NotificationDispatcher::class);

                $message = $dispatcher->broadcast(
                    title: $data['title'],
                    body:  $data['body'],
                    url:   $data['url'] ?? '/',
                );

                Notification::make()
                    ->title("Broadcast sent to {$message->recipient_count} subscriber(s)")
                    ->success()
                    ->send();
            });
    }

    // ── Shared form schema ────────────────────────────────────────────

    private static function notificationForm(): array
    {
        return [
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
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Resolve a UserPreference from a Person record by matching mobile numbers.
     * persons.phone may be local format (0821234567) or E.164 (+27821234567).
     * user_preferences.mobile is always stored as E.164.
     */
    private static function preferenceForPerson($person): ?UserPreference
    {
        $phone = $person->phone ?? '';

        if (empty($phone)) {
            return null;
        }

        // Normalise to E.164
        $e164 = match (true) {
            str_starts_with($phone, '+') => $phone,
            str_starts_with($phone, '0') => '+27' . substr($phone, 1),
            default                      => '+' . $phone,
        };

        return UserPreference::where('mobile', $e164)->first();
    }
}