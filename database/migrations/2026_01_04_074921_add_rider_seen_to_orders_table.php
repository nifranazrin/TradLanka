<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add the rider_seen column to track notifications.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Adds a boolean column after 'status' to track if rider has seen the update
            // 0 = Not seen (Show red badge), 1 = Seen (Hide red badge)
            $table->boolean('rider_seen')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Removes the column if the migration is rolled back
            $table->dropColumn('rider_seen');
        });
    }
};