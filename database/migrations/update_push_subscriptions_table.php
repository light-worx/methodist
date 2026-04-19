<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The laravel-webpush package creates push_subscriptions with non-nullable
 * subscribable_type / subscribable_id morph columns, expecting every subscription
 * to belong to an authenticated user model.
 *
 * This package links subscriptions to devices (UserPreference), not to users,
 * so those columns must be nullable. This migration alters them in place.
 * Existing rows are unaffected — NULL is a valid value after the change.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('push_subscriptions')) return;

        Schema::table('push_subscriptions', function (Blueprint $table) {
            // Change() requires doctrine/dbal on Laravel < 11, or is native on Laravel 11+.
            // We guard with hasColumn to make the migration safely re-runnable.
            if (Schema::hasColumn('push_subscriptions', 'subscribable_type')) {
                $table->string('subscribable_type')->nullable()->change();
            }
            if (Schema::hasColumn('push_subscriptions', 'subscribable_id')) {
                $table->unsignedBigInteger('subscribable_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('push_subscriptions')) return;

        Schema::table('push_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('push_subscriptions', 'subscribable_type')) {
                $table->string('subscribable_type')->nullable(false)->change();
            }
            if (Schema::hasColumn('push_subscriptions', 'subscribable_id')) {
                $table->unsignedBigInteger('subscribable_id')->nullable(false)->change();
            }
        });
    }
};