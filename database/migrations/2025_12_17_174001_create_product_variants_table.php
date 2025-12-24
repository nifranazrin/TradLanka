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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            // Link to products table
            // This ensures if a product is deleted, its variants are deleted too
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // Variant label like: 100g, 250g, 1kg, 50ml
            $table->string('unit_label');

            // Price for this specific size
            $table->decimal('price', 10, 2);

            // ✅ STOCK ADDED: Inventory count for this specific size
            $table->integer('stock')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};