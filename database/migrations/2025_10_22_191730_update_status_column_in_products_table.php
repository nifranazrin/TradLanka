<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Change from boolean to string (to store 'pending', 'approved', 'rejected')
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back if needed
            $table->boolean('status')->default(true)->change();
        });
    }
};
