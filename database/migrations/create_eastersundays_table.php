<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('eastersundays', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('year');
            $table->date('eastersunday');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('eastersundays');
    }
};
