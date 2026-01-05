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
        Schema::create('submitted_reports', function (Blueprint $table) {
            $table->id();
            // Connects to your staff/seller table
            $table->unsignedBigInteger('seller_id'); 
            
            // Stores 'top_selling', 'low_stock', or 'slow_moving'
            $table->string('report_type'); 
            
            // A friendly name like "Low Stock Alert - Jan 2026"
            $table->string('report_name'); 
            
            // Status allows admin to track if they have viewed it
            $table->enum('status', ['pending', 'viewed', 'resolved'])->default('pending');
            
            $table->timestamp('submitted_at');
            $table->timestamps();

            // Optional: Add foreign key if your sellers are in a specific table
            // $table->foreign('seller_id')->references('id')->on('staff')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submitted_reports');
    }
};