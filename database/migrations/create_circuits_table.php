<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('circuits', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('circuit', 199);
            $table->string('slug', 199);
            $table->integer('district_id');
            $table->integer('reference');
            $table->integer('plan_month');
            $table->json('servicetypes')->nullable();
            $table->json('midweeks')->nullable();
            $table->integer('showphone')->nullable();
            $table->tinyinteger('active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('circuits');
    }
};
