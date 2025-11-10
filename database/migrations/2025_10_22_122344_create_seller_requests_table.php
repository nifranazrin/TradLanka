<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
     Schema::create('seller_requests', function (Blueprint $table) {
    $table->id();
$table->string('name');
$table->string('email')->unique();
$table->string('phone');
$table->string('nic_number')->unique();
$table->string('nic_image')->nullable(); 
$table->string('preferred_name')->nullable(); 
$table->string('address')->nullable();
$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
$table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('seller_requests');
    }
};
