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
        Schema::table('users', function (Blueprint $table) {
            // Check if 'fname' exists, if not, add it
            if (!Schema::hasColumn('users', 'fname')) {
                $table->string('fname')->nullable()->after('name');
            }
            
            // Check if 'lname' exists, if not, add it
            if (!Schema::hasColumn('users', 'lname')) {
                $table->string('lname')->nullable()->after('fname');
            }

            // Check if 'address1' exists, if not, add it (This prevents your current error)
            if (!Schema::hasColumn('users', 'address1')) {
                $table->string('address1')->nullable()->after('password');
            }

            // Check if 'address2' exists, if not, add it
            if (!Schema::hasColumn('users', 'address2')) {
                $table->string('address2')->nullable()->after('address1');
            }

            // Check if 'city' exists, if not, add it
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address2');
            }

            // Check if 'state' exists, if not, add it
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }

            // Check if 'zipcode' exists, if not, add it
            if (!Schema::hasColumn('users', 'zipcode')) {
                $table->string('zipcode')->nullable()->after('state');
            }

            // Check if 'country' exists, if not, add it
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('zipcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fname', 
                'lname', 
                'address1', 
                'address2', 
                'city', 
                'state', 
                'zipcode', 
                'country'
            ]);
        });
    }
};