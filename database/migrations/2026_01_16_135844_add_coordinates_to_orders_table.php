<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * * Adds latitude and longitude columns after the 'zipcode' field.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Using 10 total digits and 7 after the decimal point for high precision
            $table->decimal('latitude', 10, 7)->nullable()->after('zipcode');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     * * Drops the coordinate columns if the migration is rolled back.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};