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
        Schema::table('orders', function (Blueprint $table) {
            // 1. Add the delivery_boy_id column after the 'id' column
            // We use unsignedBigInteger because it must match the 'id' type in the 'staff' table
            $table->unsignedBigInteger('delivery_boy_id')->nullable()->after('id');

            // 2. Create the foreign key relationship
            // This ensures that only valid staff IDs can be assigned to orders
            $table->foreign('delivery_boy_id')
                  ->references('id')
                  ->on('staff')
                  ->onDelete('set null'); // If a staff member is deleted, the order remains but becomes unassigned
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign key first, then the column
            $table->dropForeign(['delivery_boy_id']);
            $table->dropColumn('delivery_boy_id');
        });
    }
};