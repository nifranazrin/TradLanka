<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('section_name')->unique(); // <--- THIS is what was missing
            $table->string('image_path');
            $table->string('title');
            $table->string('button_link');
            $table->string('button_text');
            $table->timestamps();
        });

        // Insert Default Data
        DB::table('banners')->insert([
            'section_name' => 'home_festive_offer',
            'image_path' => 'https://via.placeholder.com/1200x400?text=Default+Banner',
            'title' => 'Special Festive Offers!',
            'button_link' => '#',
            'button_text' => 'View',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('banners');
    }
};