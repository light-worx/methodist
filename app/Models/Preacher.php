<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Preacher extends Model
{
    use SoftDeletes;

    public $table = 'preachers';
    protected $guarded = ['id'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class)->orderBy('surname');
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }
}
