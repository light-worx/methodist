<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circuit extends Model
{
    public $table = 'circuits';
    protected $guarded = ['id'];
    protected $casts = [
        'servicetypes' => 'array',
        'midweeks' => 'array'
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function societies(): HasMany
    {
        return $this->hasMany(Society::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function ministers(): HasMany
    {
        return $this->hasMany(Minister::class);
    }

    public function circuitroles(): HasMany
    {
        return $this->hasMany(Circuitrole::class);
    }

    public function persons(): BelongsToMany
    {
        return $this->belongsToMany(Person::class,'circuit_person')->withPivot('status','societies');
    }

    public function getCircuitnameAttribute($value)
    {
        return $this->reference . " " . $this->circuit;
    }
}
