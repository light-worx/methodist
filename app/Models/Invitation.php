<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    public $table = 'invitations';
    protected $guarded = ['id'];
    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'districts' => 'array',
        'circuits' => 'array',
        'societies' => 'array',
        'exclude_services' => 'array',
    ];
}
