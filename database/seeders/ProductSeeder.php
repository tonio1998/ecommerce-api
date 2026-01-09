<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Vendor;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendorIds = Vendor::pluck('id')->toArray();

        if (empty($vendorIds)) {
            $this->command->error('No vendors found.');
            return;
        }

        $products = [];

        for ($i = 1; $i <= 1500; $i++) {
            $products[] = [
                'vendor_id'   => $vendorIds[array_rand($vendorIds)],
                'name'        => 'Product ' . $i,
                'description' => 'Description for product ' . $i,
                'price'       => rand(100, 5000),
                'stock'       => rand(1, 100),
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        Product::insert($products);
    }
}
