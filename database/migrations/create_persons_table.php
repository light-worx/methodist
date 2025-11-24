<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('persons', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('firstname', 199);
            $table->string('surname', 199);
            $table->string('title', 199)->nullable();
            $table->string('phone', 199)->nullable();
            $table->string('image', 199)->nullable();
            $table->integer(`society_id`)->nullable();
            $table->integer(`circuit_id`)->nullable();
            $table->json(`leadership`)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('persons');
    }
};
