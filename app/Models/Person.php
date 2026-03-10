<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    public $table = 'persons';
    protected $guarded = ['id'];
    protected $casts = [ 
        'leadership' => 'array' 
    ];

    public function minister(): HasOne
    {
        return $this->HasOne(Minister::class);
    }

    public function getNameAttribute($value)
    {
        return $this->title . " " . substr($this->firstname,0,1) . " " . $this->surname;
    }

    public function getFullnameAttribute($value)
    {
        return $this->firstname . " " . $this->surname;
    }

    public function getFullnamebackAttribute($value)
    {
        return $this->surname . ", " . $this->firstname;
    }

    public function preacher(): HasOne
    {
        return $this->HasOne(Preacher::class)->orderBy('surname');
    }

    public function circuitroles(): HasMany
    {
        return $this->hasMany(Circuitrole::class);
    }

    public function circuits(): BelongsToMany
    {
        return $this->belongsToMany(Circuit::class,'circuit_person')->withPivot('status','societies','is_preacher');
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

}
