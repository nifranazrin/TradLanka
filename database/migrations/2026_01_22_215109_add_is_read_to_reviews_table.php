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
        Schema::table('reviews', function (Blueprint $table) {
            // ✅ Add is_read column to track notifications
            // 0 = unread (show badge), 1 = read (hide badge)
            $table->boolean('is_read')->default(0)->after('status'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Remove the column if we rollback the migration
            $table->dropColumn('is_read');
        });
    }
};