<?php

namespace App\Services;

use App\Jobs\SendPushJob;
use App\Models\PushMessage;
use App\Models\UserPreference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationDispatcher
{
    /**
     * Dispatch a broadcast to all subscribers.
     */
    public function broadcast(string $title, string $body, string $url = '/'): PushMessage
    {
        $recipients = UserPreference::query()
            ->whereNotNull('push_endpoint')
            ->whereNotNull('push_keys')
            ->where('notif_circuit', true)
            ->get();

        return $this->dispatch('broadcast', $title, $body, $url, $recipients);
    }

    /**
     * Dispatch to all subscribers within a specific circuit.
     */
    public function toCircuit(int $circuitId, string $title, string $body, string $url = '/'): PushMessage
    {
        $recipients = UserPreference::query()
            ->whereNotNull('push_endpoint')
            ->whereNotNull('push_keys')
            ->where('circuit_id', $circuitId)
            ->where('notif_circuit', true)
            ->get();

        return $this->dispatch('circuit', $title, $body, $url, $recipients, circuitId: $circuitId);
    }

    /**
     * Dispatch to a single subscriber by their user_preference id.
     */
    public function toIndividual(int $userPreferenceId, string $title, string $body, string $url = '/'): PushMessage
    {
        $recipients = UserPreference::query()
            ->whereNotNull('push_endpoint')
            ->whereNotNull('push_keys')
            ->where('id', $userPreferenceId)
            ->get();

        return $this->dispatch('individual', $title, $body, $url, $recipients, prefId: $userPreferenceId);
    }

    /**
     * Dispatch a lectionary notification to a pre-resolved collection of recipients.
     * Recipients are already filtered to those preaching + notif_lectionary = true.
     */
    public function lectionary(string $title, string $body, Collection $recipients, string $url = '/lectionary'): PushMessage
    {
        return $this->dispatch('lectionary', $title, $body, $url, $recipients);
    }

    // ── Internal ──────────────────────────────────────────────────────

    private function dispatch(
        string $type,
        string $title,
        string $body,
        string $url,
        Collection $recipients,
        ?int $circuitId = null,
        ?int $prefId = null,
    ): PushMessage {

        $message = PushMessage::create([
            'user_id'            => Auth::id() ?? 0,  // 0 = system (scheduled job)
            'type'               => $type,
            'circuit_id'         => $circuitId,
            'user_preference_id' => $prefId,
            'title'              => $title,
            'body'               => $body,
            'url'                => $url,
            'recipient_count'    => $recipients->count(),
            'sent_at'            => now(),
        ]);

        foreach ($recipients as $pref) {
            SendPushJob::dispatch($message->id, $pref->id);
        }

        return $message;
    }
}