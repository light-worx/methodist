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
            $table->date('servicedate')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('midweeks');
    }
};
