<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function __construct(private readonly NotificationDispatcher $dispatcher) {}

    // ── POST /admin/push/circuit/{circuitId} ──────────────────────────
    // Circuit admins can only send to their own circuit.
    // Super admins can send to any circuit.

    public function toCircuit(Request $request, int $circuitId): RedirectResponse
    {
        $this->authoriseCircuit($circuitId);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body'  => ['required', 'string', 'max:4000'],
            'url'   => ['nullable', 'string', 'max:255'],
        ]);

        $message = $this->dispatcher->toCircuit(
            circuitId: $circuitId,
            title:     $data['title'],
            body:      $data['body'],
            url:       $data['url'] ?? '/',
        );

        return back()->with('success',
            "Notification sent to {$message->recipient_count} subscriber(s)."
        );
    }

    // ── POST /admin/push/individual/{userPreferenceId} ────────────────
    // Send to a single subscriber identified by their user_preference id.

    public function toIndividual(Request $request, int $userPreferenceId): RedirectResponse
    {
        $pref = UserPreference::findOrFail($userPreferenceId);

        // Circuit admins may only message people in their own circuit
        $this->authoriseCircuit($pref->circuit_id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body'  => ['required', 'string', 'max:4000'],
            'url'   => ['nullable', 'string', 'max:255'],
        ]);

        if (! $pref->push_endpoint) {
            return back()->with('warning', 'This person has not enabled push notifications.');
        }

        $this->dispatcher->toIndividual(
            userPreferenceId: $userPreferenceId,
            title:            $data['title'],
            body:             $data['body'],
            url:              $data['url'] ?? '/',
        );

        return back()->with('success', 'Notification sent.');
    }

    // ── POST /admin/push/broadcast ────────────────────────────────────
    // Super admin only.

    public function broadcast(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body'  => ['required', 'string', 'max:4000'],
            'url'   => ['nullable', 'string', 'max:255'],
        ]);

        $message = $this->dispatcher->broadcast(
            title: $data['title'],
            body:  $data['body'],
            url:   $data['url'] ?? '/',
        );

        return back()->with('success',
            "Broadcast sent to {$message->recipient_count} subscriber(s)."
        );
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function authoriseCircuit(int $circuitId): void
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        // Circuit admin — ensure they belong to this circuit
        abort_unless(
            $user->hasRole('circuit_admin') && $user->circuit_id === $circuitId,
            403
        );
    }
}