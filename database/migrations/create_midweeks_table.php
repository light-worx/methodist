<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('midweeks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('midweek',199);
            $table->string('type',199)->default('fixed');
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->integer('offset')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('midweeks');
    }
};
