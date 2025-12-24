<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates the order_items table to support decimal pricing and consistent naming.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // 1. Change price to decimal(10,2) to support values like 5.76 and 1800.00
            $table->decimal('price', 10, 2)->change();

            // 2. Rename product_variant_id to variant_id to match your Model and Controller
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->renameColumn('product_variant_id', 'variant_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Revert column name if necessary
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->renameColumn('variant_id', 'product_variant_id');
            }
            
            // Revert price to integer if that was the previous state (optional)
            // $table->integer('price')->change();
        });
    }
};