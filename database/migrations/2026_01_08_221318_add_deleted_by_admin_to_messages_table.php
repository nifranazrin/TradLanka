<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Adds the flags to hide messages for specific users
            $table->boolean('deleted_by_admin')->default(false)->after('is_read');
            $table->boolean('deleted_by_staff')->default(false)->after('deleted_by_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Removes the columns if the migration is rolled back
            $table->dropColumn(['deleted_by_admin', 'deleted_by_staff']);
        });
    }
};