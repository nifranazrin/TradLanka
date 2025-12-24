<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('carts', function (Blueprint $table) {
        $table->id();
        // Link to the User who owns the cart
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // Link to the Product being bought
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        
        // How many items?
        $table->integer('product_qty')->default(1);
        
        $table->timestamps();
    });
}
};
