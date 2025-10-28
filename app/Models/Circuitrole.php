<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circuitrole extends Model
{

    public $table = 'circuit_person';
    protected $guarded = ['id'];
    protected $casts = [ 
        'status' => 'json',
        'societies' => 'json'
    ];
    public $timestamps = false;


    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
