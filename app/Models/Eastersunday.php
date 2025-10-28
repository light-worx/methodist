<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eastersunday extends Model
{
    public $table = 'eastersundays';
    protected $guarded = ['id'];
    public $timestamps = false;
}
