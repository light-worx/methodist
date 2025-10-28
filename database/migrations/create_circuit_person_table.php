<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('circuit_person', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('person_id');
            $table->integer('circuit_id');
            $table->json('societies');
            $table->json('status');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('circuit_person');
    }
};
                         