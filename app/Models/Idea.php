<?php

namespace App\Models;

use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Idea extends Model
{
    use Taggable;

    public $table = 'ideas';
    protected $guarded = ['id'];
    protected $casts = [
        'published' => 'boolean'
    ];  
    public $timestamps = false;

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }   
}
