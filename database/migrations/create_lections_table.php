<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lections', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('year');
            $table->string('lection');
            $table->string('ot');
            $table->string('psalm');
            $table->string('nt');
            $table->string('gospel');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('lections');
    }
};
