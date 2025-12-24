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
        Schema::table('messages', function (Blueprint $table) {
            // 1. Reply Feature: Stores the ID of the message being replied to
            // "nullable" because most messages are not replies.
            // "after('id')" places it neatly at the start of the table.
            $table->unsignedBigInteger('reply_to_id')->nullable()->after('id');

            // 2. Delete Features
            // "Delete for Everyone" flag
            $table->boolean('is_deleted_everyone')->default(false);

            // "Delete for Me" flags
            $table->boolean('deleted_by_sender')->default(false);
            $table->boolean('deleted_by_receiver')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Remove the columns if we rollback
            $table->dropColumn([
                'reply_to_id', 
                'is_deleted_everyone', 
                'deleted_by_sender', 
                'deleted_by_receiver'
            ]);
        });
    }
};