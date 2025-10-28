<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ideas', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('circuit_id');
            $table->string('email',199)->nullable();
            $table->string('image',199)->nullable();
            $table->string('idea',199)->nullable();
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('ideas');
    }
};

