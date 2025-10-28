<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->json('districts')->nullable();
            $table->json('circuits')->nullable();
            $table->json('societies')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('districts');
            $table->dropColumn('circuits');
            $table->dropColumn('societies');
        });
    }

};
