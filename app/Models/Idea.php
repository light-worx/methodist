<?php

namespace App\Models;

use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Idea extends Model
{
    use Taggable;

    public $table = 'ideas';
    protected $guarded = ['id'];
    protected $casts = [
        'published' => 'boolean'
    ];  
    public $timestamps = false;
}
