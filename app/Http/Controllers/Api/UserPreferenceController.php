<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationPinMail;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserPreferenceController extends Controller
{
    // How long a PIN stays valid
    private const PIN_TTL_MINUTES = 15;

    // ── Resolve or create the preference row for this browser ────────

    private function resolve(Request $request): UserPreference
    {
        $cookieId = $request->cookie('pref_id');

        if ($cookieId) {
            $pref = UserPreference::where('cookie_id', $cookieId)->first();
            if ($pref) {
                return $pref;
            }
        }

        // First visit — create a new row with a fresh UUID
        return new UserPreference(['cookie_id' => Str::uuid()]);
    }

    // ── Save a preference row and attach the cookie to the response ──

    private function respond(UserPreference $pref, array $data, int $status = 200): JsonResponse
    {
        return response()
            ->json($data, $status)
            ->cookie(
                'pref_id',
                $pref->cookie_id,
                60 * 24 * 365,   // 1 year
                '/',
                null,
                request()->isSecure(),
                true,            // httpOnly — JS cannot read this one
                false,
                'Lax'
            );
    }

    // ── GET /api/preferences ─────────────────────────────────────────
    // Returns the current preference state for the UI to hydrate from.

    public function show(Request $request): JsonResponse
    {
        $pref = $this->resolve($request);

        if (! $pref->exists) {
            return $this->respond($pref, ['exists' => false]);
        }

        return $this->respond($pref, [
            'exists'           => true,
            'circuit_id'       => $pref->circuit_id,
            'email'            => $pref->email,
            'email_verified'   => $pref->isEmailVerified(),
            'mobile'           => $pref->mobile,
            'has_push'         => $pref->hasPushSubscription(),
            'notif_lectionary' => $pref->notif_lectionary,
            'notif_circuit'    => $pref->notif_circuit,
            'notif_ideas'      => $pref->notif_ideas,
        ]);
    }

    // ── POST /api/preferences ────────────────────────────────────────
    // Upserts circuit, mobile, and notification prefs.
    // Email is NOT updated here — it has its own flow via send-pin.

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'circuit_id'       => ['nullable', 'exists:circuits,id'],
            'mobile'           => ['nullable', 'string', 'max:30'],
            'notif_lectionary' => ['boolean'],
            'notif_circuit'    => ['boolean'],
            'notif_ideas'      => ['boolean'],
        ]);

        $pref = $this->resolve($request);
        $pref->fill($data);
        $pref->save();

        return $this->respond($pref, ['saved' => true]);
    }

    // ── POST /api/preferences/send-pin ───────────────────────────────
    // Stores a new email address (unverified) and dispatches the PIN email.

    public function sendPin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $pref = $this->resolve($request);

        // If the email address has changed, clear previous verification
        if ($pref->email !== $request->email) {
            $pref->email_verified_at = null;
        }

        $plain = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $pref->email            = $request->email;
        $pref->verification_pin = hash('sha256', $plain);
        $pref->pin_expires_at   = now()->addMinutes(self::PIN_TTL_MINUTES);
        $pref->save();

        Mail::to($request->email)->send(new VerificationPinMail($plain));

        return $this->respond($pref, ['sent' => true]);
    }

    // ── POST /api/preferences/verify-pin ────────────────────────────
    // Validates the submitted PIN and marks email as verified.

    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6'],
        ]);

        $pref = $this->resolve($request);

        if (! $pref->exists) {
            return response()->json(['error' => 'No preferences found.'], 404);
        }

        if (! $pref->isPinValid($request->pin)) {
            return response()->json(['error' => 'Invalid or expired code.'], 422);
        }

        $pref->email_verified_at = now();
        $pref->clearPin();
        $pref->save();

        return $this->respond($pref, ['verified' => true]);
    }

    // ── POST /api/preferences/push-subscribe ────────────────────────
    // Stores the browser's Web Push subscription object.
    // Only accepted if email is verified.

    public function pushSubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'       => ['required', 'url'],
            'keys.p256dh'    => ['required', 'string'],
            'keys.auth'      => ['required', 'string'],
        ]);

        $pref = $this->resolve($request);

        if (! $pref->exists || ! $pref->isEmailVerified()) {
            return response()->json(['error' => 'Email must be verified first.'], 403);
        }

        $pref->push_endpoint = $request->endpoint;
        $pref->push_keys     = $request->only(['keys'])['keys'];
        $pref->save();

        return $this->respond($pref, ['subscribed' => true]);
    }
}