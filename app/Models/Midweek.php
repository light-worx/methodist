<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Midweek extends Model
{
    public $table = 'midweeks';
    public $timestamps = false;
    protected $guarded = ['id'];
}
