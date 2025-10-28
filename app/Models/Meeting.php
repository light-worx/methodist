<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    public $table = 'meetings';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }
}
