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
    Schema::create('product_views', function (Blueprint $table) {
        $table->id();
        // If user is logged in, we save user_id. If guest, we use session_id.
        $table->unsignedBigInteger('user_id')->nullable(); 
        $table->string('session_id')->nullable();

        $table->unsignedBigInteger('product_id');
        $table->timestamps();

        // Foreign key (optional, but good for data integrity)
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
