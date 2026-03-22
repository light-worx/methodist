<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_preferences', function ($table) {
            $table->id();
 
            // Anonymous identity — UUID stored in an unencrypted browser cookie
            $table->string('cookie_id')->unique()->index();
 
            // Circuit selection (step 1 — always required)
            $table->integer('circuit_id')->nullable();
 
            // Email + verification (step 2)
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_pin')->nullable();   // stored as sha256 hash
            $table->timestamp('pin_expires_at')->nullable();
 
            // Mobile (step 3 — trusted once email is verified)
            $table->string('mobile')->nullable();
 
            // Web Push subscription (set by browser after mobile is added)
            $table->text('push_endpoint')->nullable();
            $table->json('push_keys')->nullable();            // {p256dh, auth}
 
            // Per-category notification preferences
            $table->boolean('notif_lectionary')->default(false);
            $table->boolean('notif_circuit')->default(false);
            $table->boolean('notif_ideas')->default(false);
 
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
