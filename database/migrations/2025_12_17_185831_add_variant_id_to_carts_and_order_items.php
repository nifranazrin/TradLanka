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
        // 1. Add column to 'carts' table
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
        });

        // 2. Add column to 'order_items' table
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop column from 'carts' table
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('product_variant_id');
        });

        // 2. Drop column from 'order_items' table
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('product_variant_id');
        });
    }
};