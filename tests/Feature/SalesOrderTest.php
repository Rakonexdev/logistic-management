<?php

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to login', function () {
    $this->get(route('sales-orders.index'))
        ->assertRedirect(route('login'));
});

test('end users can access sales orders index', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $this->actingAs($user)
        ->get(route('sales-orders.index'))
        ->assertSuccessful()
        ->assertSee('Sales Orders (SO)');
});

test('end users can create a sales order draft', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $payload = [
        'so_number' => 'SO-TEST-1234',
        'customer_name' => 'Test Customer',
        'designation' => 'Test Destination',
        'order_date' => '2026-07-10',
        'remarks' => 'Some test remarks',
        'items' => [
            ['sku_code' => 'SKU-001', 'quantity' => 10],
            ['sku_code' => 'SKU-002', 'quantity' => 20],
        ],
        'status' => 'draft',
    ];

    $response = $this->actingAs($user)
        ->post(route('sales-orders.store'), $payload);

    $response->assertRedirect(route('sales-orders.index'));

    $this->assertDatabaseHas('sales_orders', [
        'so_number' => 'SO-TEST-1234',
        'customer_name' => 'Test Customer',
        'designation' => 'Test Destination',
        'status' => 'draft',
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('sales_order_items', [
        'sku_code' => 'SKU-001',
        'quantity' => 10,
    ]);
});

test('stock check endpoint calculates correct availability', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    // Create a product
    Product::create([
        'sku_code' => 'SKU-STOCK',
        'name' => 'Stock Product',
        'type' => 'physical',
        'status' => 'active',
    ]);

    // Create ASN with items (Inbound)
    $asn = AdvanceShippingNote::create([
        'asn_reference' => 'ASN-TEST-999',
        'airway_bill' => 'AWB-123',
        'vendor_id' => 'Vendor A',
        'status' => 'completed',
        'user_id' => $user->id,
    ]);

    AsnItem::create([
        'asn_id' => $asn->id,
        'sku_code' => 'SKU-STOCK',
        'quantity' => 50,
    ]);

    // Create existing Sales Order (Outbound)
    $so = SalesOrder::create([
        'so_number' => 'SO-TEST-EXISTING',
        'customer_destination' => 'Cust A',
        'order_date' => '2026-07-10',
        'status' => 'submitted',
        'user_id' => $user->id,
    ]);

    SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku_code' => 'SKU-STOCK',
        'quantity' => 15,
    ]);

    // Perform stock check
    // Total: 50 Inbound - 15 Outbound = 35 Available
    $response = $this->actingAs($user)
        ->postJson(route('sales-orders.stock-check'), [
            'items' => [
                ['sku_code' => 'SKU-STOCK'],
                ['sku_code' => 'SKU-NONEXISTENT'],
            ],
        ]);

    $response->assertSuccessful()
        ->assertJson([
            'stocks' => [
                'SKU-STOCK' => [
                    'available' => 35,
                    'status' => 'active',
                    'name' => 'Stock Product',
                ],
                'SKU-NONEXISTENT' => [
                    'available' => 0,
                    'status' => 'not_found',
                    'name' => 'Unknown Product',
                ],
            ],
        ]);
});
