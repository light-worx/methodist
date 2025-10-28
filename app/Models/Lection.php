<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lection extends Model
{
    public $table = 'lections';
    protected $guarded = ['id'];
    public $timestamps = false;
}
