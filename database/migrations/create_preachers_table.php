<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('preachers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('person_id');
            $table->integer('society_id')->nullable();
            $table->string('status', 199);
            $table->json('leadership')->nullable();
            $table->string('induction', 10)->nullable();
            $table->string('number', 20)->nullable();
            $table->tinyinteger('active')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('preachers');
    }
};
