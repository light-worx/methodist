<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preacher extends Model
{
    public $table = 'preachers';
    protected $guarded = ['id'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }
}
