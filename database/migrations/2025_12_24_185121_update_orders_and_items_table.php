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
        // 1. Update the 'orders' table
        Schema::table('orders', function (Blueprint $table) {
            // Add Address Line 2 if it doesn't exist
            if (!Schema::hasColumn('orders', 'address2')) {
                $table->string('address2')->nullable()->after('address1');
            }
            
            // Add Currency column to track LKR vs USD
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency')->default('LKR')->after('total_price');
            }

            // Ensure total_price is a decimal to handle USD amounts
            $table->decimal('total_price', 10, 2)->change();
        });

        // 2. Update the 'order_items' table
        Schema::table('order_items', function (Blueprint $table) {
            // Change price to decimal so $5.76 doesn't become $5.00
            $table->decimal('price', 10, 2)->change();

            // Rename product_variant_id to variant_id to match your Model
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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['address2', 'currency']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->renameColumn('variant_id', 'product_variant_id');
            }
        });
    }
};