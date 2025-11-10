<?php

namespace Database\Seeders;

use App\Models\categories;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            
        ];

        foreach ($categories as $index => $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'order' => $index + 1,
                'status' => true,
            ]);
        }
    }
}
