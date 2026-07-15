<?php

namespace Database\Seeders;

use App\Models\ChequeCollection;
use App\Models\ReturnPickup;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DriverTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $endUser = User::where('role', 'end_user')->first();
        if (! $endUser) {
            $endUser = User::factory()->create(['role' => 'end_user']);
        }

        // 1. Seed Deliveries (Sales Orders with Completed status, and Delivery status Assigned/Arrived/Delivered)
        // Delivery for Driver User (Pending Arrive)
        $so1 = SalesOrder::create([
            'so_number' => 'SO-DRV-001',
            'customer_name' => 'TechSolutions Inc',
            'designation' => '100 Broadway, New York, NY',
            'order_date' => '2026-07-14',
            'status' => 'completed',
            'delivery_status' => 'Assigned',
            'driver' => 'Driver User',
            'vehicle' => 'Van-A1',
            'user_id' => $endUser->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so1->id,
            'sku_code' => 'SKU-001',
            'quantity' => 10,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so1->id,
            'sku_code' => 'SKU-002',
            'quantity' => 5,
        ]);

        // Delivery for Driver User (Arrived, pending delivery completion)
        $so2 = SalesOrder::create([
            'so_number' => 'SO-DRV-002',
            'customer_name' => 'Global Logistics LLC',
            'designation' => '452 Fifth Ave, New York, NY',
            'order_date' => '2026-07-14',
            'status' => 'completed',
            'delivery_status' => 'Arrived',
            'arrived_at' => now()->subMinutes(15),
            'driver' => 'Driver User',
            'vehicle' => 'Van-A1',
            'user_id' => $endUser->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so2->id,
            'sku_code' => 'SKU-STOCK',
            'quantity' => 20,
        ]);

        // Delivery for John Doe (Already Delivered)
        $so3 = SalesOrder::create([
            'so_number' => 'SO-DRV-003',
            'customer_name' => 'Apex Retail',
            'designation' => '12 Route 17, Paramus, NJ',
            'order_date' => '2026-07-13',
            'status' => 'completed',
            'delivery_status' => 'Delivered',
            'arrived_at' => now()->subHours(5),
            'delivery_completed_at' => now()->subHours(4),
            'recipient_name' => 'Michael Scott',
            'delivery_remarks' => 'Delivered to back loading dock.',
            'driver' => 'John Doe',
            'vehicle' => 'Truck-04',
            'user_id' => $endUser->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $so3->id,
            'sku_code' => 'SKU-001',
            'quantity' => 15,
        ]);

        // 2. Seed Return Pickups
        ReturnPickup::create([
            'return_ref' => 'RET-001',
            'driver' => 'Driver User',
            'pickup_location' => 'TechSolutions Inc, 100 Broadway, NY',
            'product_sku' => 'SKU-001',
            'quantity' => 5,
            'status' => 'Pending Pickup',
        ]);

        ReturnPickup::create([
            'return_ref' => 'RET-002',
            'driver' => 'John Doe',
            'pickup_location' => 'Apex Retail, 12 Route 17, Paramus, NJ',
            'product_sku' => 'SKU-STOCK',
            'quantity' => 12,
            'status' => 'Pickup Started',
        ]);

        // 3. Seed Cheque Collections
        ChequeCollection::create([
            'collection_ref' => 'CHQ-201',
            'customer_name' => 'Global Logistics LLC',
            'collection_location' => '452 Fifth Ave, New York, NY',
            'amount' => 1500.00,
            'status' => 'Pending Collection',
            'driver' => 'Driver User',
        ]);

        ChequeCollection::create([
            'collection_ref' => 'CHQ-202',
            'customer_name' => 'Apex Retail',
            'collection_location' => '12 Route 17, Paramus, NJ',
            'amount' => 3200.50,
            'status' => 'Collected',
            'driver' => 'John Doe',
            'remarks' => 'Collected from Finance desk.',
            'submission_time' => now()->subHours(2),
        ]);
    }
}
