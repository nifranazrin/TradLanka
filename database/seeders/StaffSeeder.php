<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::create([
            'name' => 'Admin',
            'email' => 'admin@tradlanka.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => true,
        ]);

        Staff::create([
            'name' => 'Seller',
            'email' => 'seller@tradlanka.com',
            'password' => Hash::make('seller123'),
            'role' => 'seller',
            'status' => true,
        ]);

        Staff::create([
            'name' => 'Delivery Person',
            'email' => 'delivery@tradlanka.com',
            'password' => Hash::make('delivery123'),
            'role' => 'delivery',
            'status' => true,
        ]);
    }
}
