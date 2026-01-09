<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorOrder;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('customer')->get();
        $products  = Product::with('vendor')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->error('Customers or products missing.');
            return;
        }

        DB::transaction(function () use ($customers, $products) {

            for ($i = 1; $i <= 30; $i++) {

                $customer = $customers->random();

                $order = Order::create([
                    'user_id'        => $customer->id,
                    'total_amount'   => 0,
                    'payment_status' => 'paid',
                    'order_status'   => 'completed',
                ]);

                $items = $products->random(rand(1, 4));

                $vendorTotals = [];
                $orderTotal   = 0;

                foreach ($items as $product) {
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;

                    OrderItem::create([
                        'order_id'  => $order->id,
                        'vendor_id' => $product->vendor_id,
                        'product_id'=> $product->id,
                        'quantity'  => $qty,
                        'price'     => $product->price,
                        'subtotal'  => $subtotal,
                    ]);

                    $vendorTotals[$product->vendor_id] =
                        ($vendorTotals[$product->vendor_id] ?? 0) + $subtotal;

                    $orderTotal += $subtotal;
                }

                foreach ($vendorTotals as $vendorId => $subtotal) {
                    VendorOrder::create([
                        'order_id' => $order->id,
                        'vendor_id'=> $vendorId,
                        'subtotal' => $subtotal,
                        'status'   => 'delivered',
                    ]);
                }

                Payment::create([
                    'order_id'        => $order->id,
                    'payment_gateway' => 'gcash',
                    'reference'       => 'PAY-' . strtoupper(uniqid()),
                    'amount'          => $orderTotal,
                    'status'          => 'paid',
                ]);

                $order->update([
                    'total_amount' => $orderTotal
                ]);
            }
        });
    }
}
