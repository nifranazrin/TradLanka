<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Connects to your users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            // Connects to your products table
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); 
            
            $table->integer('rating'); // To store 1-5 star values
            $table->text('comment');   // The written review text
            $table->string('image')->nullable(); // Path to the uploaded review image
            
            // Admin can approve reviews before they go live
            $table->boolean('status')->default(0); // 0 = Pending, 1 = Approved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};