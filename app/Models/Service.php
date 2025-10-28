<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    public $table = 'services';
    protected $guarded = ['id'];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }
}
