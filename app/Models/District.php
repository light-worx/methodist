<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public $table = 'districts';
    protected $guarded = ['id'];

    public function circuits(): HasMany
    {
        return $this->hasMany(Circuit::class);
    }
}
