<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_messages', function ($table) {
            $table->id();
 
            // Who sent it and what kind of send
            $table->integer('user_id')->nullable();
            $table->enum('type', ['broadcast', 'circuit', 'society','individual', 'lectionary']);
 
            // Payload
            $table->string('title');
            $table->text('body');
            $table->string('url')->nullable();
 
            // Outcome
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamp('sent_at')->nullable();
 
            $table->timestamps();
        });
 
        Schema::create('push_logs', function ($table) {
            $table->id();
 
            $table->integer('push_message_id')->nullable();
            $table->integer('user_preference_id')->nullable();
 
            $table->enum('status', ['sent', 'failed', 'expired']);
            $table->text('error')->nullable();
            $table->timestamp('delivered_at')->nullable();
 
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('push_logs');
        Schema::dropIfExists('push_messages');
    }
};
