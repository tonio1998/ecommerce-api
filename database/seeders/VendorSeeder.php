<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => 'Vendor Owner ' . $i,
                'email' => 'vendor' . $i . '@example.com',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('vendor');

            Vendor::create([
                'user_id' => $user->id,
                'shop_name' => 'Shop ' . $i,
                'description' => 'Description for Shop ' . $i,
                'address' => 'Sample Address ' . $i,
                'latitude' => 9.789 + ($i * 0.001),
                'longitude' => 125.495 + ($i * 0.001),
                'is_active' => true,
            ]);
        }
    }
}
