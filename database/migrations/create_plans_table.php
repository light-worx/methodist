<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('service_id')->nullable();
            $table->date('servicedate')->nullable();
            $table->integer('person_id')->nullable();
            $table->string('servicetype',199)->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
