<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'cookie_id',
        'circuit_id',
        'email',
        'email_verified_at',
        'verification_pin',
        'pin_expires_at',
        'mobile',
        'push_endpoint',
        'push_keys',
        'notif_lectionary',
        'notif_circuit',
        'notif_ideas',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'pin_expires_at'    => 'datetime',
        'push_keys'         => 'array',
        'notif_lectionary'  => 'boolean',
        'notif_circuit'     => 'boolean',
        'notif_ideas'       => 'boolean',
    ];

    protected $hidden = [
        'verification_pin',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasPushSubscription(): bool
    {
        return $this->push_endpoint !== null && $this->push_keys !== null;
    }

    public function isPinValid(string $plain): bool
    {
        if (! $this->verification_pin || ! $this->pin_expires_at) {
            return false;
        }

        if ($this->pin_expires_at->isPast()) {
            return false;
        }

        return hash_equals($this->verification_pin, hash('sha256', $plain));
    }

    public function clearPin(): void
    {
        $this->verification_pin = null;
        $this->pin_expires_at   = null;
    }
}