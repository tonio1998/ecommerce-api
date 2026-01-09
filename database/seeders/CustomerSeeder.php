<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $user = User::create([
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'password' => Hash::make('password'),
            ]);

            $user->assignRole('customer');
        }
    }
}
