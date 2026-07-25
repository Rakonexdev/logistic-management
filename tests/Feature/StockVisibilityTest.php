<?php

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to login from stock visibility', function () {
    $this->get(route('products.stock-visibility'))
        ->assertRedirect(route('login'));
});

test('end users can view stock visibility page with correct calculations', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $product = Product::create([
        'sku_code' => 'SKU-VISIBILITY-TEST',
        'name' => 'Visibility Test Unit',
        'type' => 'physical',
        'category' => 'electronics',
        'status' => 'active',
    ]);

    // Create 100 inbound stock
    $asn = AdvanceShippingNote::create([
        'asn_reference' => 'ASN-TEST-STK-1',
        'airway_bill' => 'AWB-TEST-STK-1',
        'vendor_id' => 'VENDOR-STK',
        'user_id' => $user->id,
        'status' => 'submitted',
    ]);
    AsnItem::create([
        'asn_id' => $asn->id,
        'sku_code' => 'SKU-VISIBILITY-TEST',
        'quantity' => 100,
    ]);

    // Create 30 outbound stock
    $so = SalesOrder::create([
        'so_number' => 'SO-TEST-STK-1',
        'customer_name' => 'Cust Stk',
        'order_date' => now(),
        'user_id' => $user->id,
        'status' => 'submitted',
    ]);
    SalesOrderItem::create([
        'sales_order_id' => $so->id,
        'sku_code' => 'SKU-VISIBILITY-TEST',
        'quantity' => 30,
    ]);

    $this->actingAs($user)
        ->get(route('products.stock-visibility'))
        ->assertSuccessful()
        ->assertSee('SKU-VISIBILITY-TEST')
        ->assertSee('100') // Inbound
        ->assertSee('30')  // Outbound
        ->assertSee('70'); // Available (100 - 30)
});

test('end users can change per page limit on stock visibility', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    // Clean up existing products to prevent test pollution
    Product::query()->delete();

    // Create 15 products
    for ($i = 1; $i <= 15; $i++) {
        Product::create([
            'sku_code' => "SKU-PAGE-{$i}",
            'name' => "Page Product {$i}",
            'type' => 'physical',
            'category' => 'electronics',
            'status' => 'active',
        ]);
    }

    $this->actingAs($user)
        ->get(route('products.stock-visibility', ['per_page' => 10]))
        ->assertSuccessful()
        ->assertSee('Showing 1 to 10 of 15');

    $this->actingAs($user)
        ->get(route('products.stock-visibility', ['per_page' => 25]))
        ->assertSuccessful()
        ->assertSee('Showing 1 to 15 of 15');
});

test('sfq users can view stock visibility page and see sidebar link', function () {
    $sfqUser = User::factory()->create(['role' => 'sfq_user']);

    $response = $this->actingAs($sfqUser)->get(route('products.stock-visibility'));

    $response->assertSuccessful();
    $response->assertSee('Stock');
    $response->assertSee(route('products.stock-visibility'));
});
