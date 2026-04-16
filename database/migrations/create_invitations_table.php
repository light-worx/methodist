<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invitations', function ($table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token')->unique();

            $table->string('role');
            $table->json('districts')->nullable();
            $table->json('circuits')->nullable();
            $table->json('societies')->nullable();
            $table->json('exclude_services')->nullable();
            $table->string('invited_by')->nullable();

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('invitations');
    }
};
