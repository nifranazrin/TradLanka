<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // SENDER INFO (Who sent the message?)
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_type'); // 'seller', 'admin', 'delivery'

            // RECEIVER INFO (Who is it for?)
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_type'); // 'seller', 'admin', 'delivery'

            // CONTENT
            $table->text('message')->nullable(); // Text content
            $table->string('attachment')->nullable(); // Image path if uploaded

            // STATUS
            $table->boolean('is_read')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};