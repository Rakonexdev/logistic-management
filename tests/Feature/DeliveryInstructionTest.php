<?php

use App\Models\DeliveryInstruction;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('authenticated user can view delivery instructions index', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)
        ->get(route('delivery-instructions.index'));

    $response->assertSuccessful();
});

test('submitting delivery instruction with fully available stock passes validation', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    // Create products in stock
    $product1 = Product::create([
        'sku_code' => 'SKU-TEST-1',
        'name' => 'Test Product 1',
        'type' => 'physical',
        'qty' => 10,
        'serial_number' => 'SN-1111',
        'status' => 'active',
    ]);

    $product2 = Product::create([
        'sku_code' => 'SKU-TEST-2',
        'name' => 'Test Product 2',
        'type' => 'physical',
        'qty' => 20,
        'status' => 'active',
    ]);

    $payload = [
        'di_number' => 'DI-TEST-SUCCESS',
        'customer_name' => 'Acme Corp',
        'delivery_address' => '123 Main St',
        'items' => [
            [
                'sku_code' => 'SKU-TEST-1',
                'quantity' => 2,
                'serial_numbers' => 'SN-1111, SN-1111',
            ],
            [
                'sku_code' => 'SKU-TEST-2',
                'quantity' => 5,
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->post(route('delivery-instructions.store'), $payload);

    $response->assertRedirect(route('delivery-instructions.index'));

    $this->assertDatabaseHas('delivery_instructions', [
        'di_number' => 'DI-TEST-SUCCESS',
        'status' => 'completed',
    ]);

    $this->assertDatabaseHas('delivery_notes', [
        'delivery_instruction_id' => DeliveryInstruction::where('di_number', 'DI-TEST-SUCCESS')->first()->id,
    ]);

    // Check inventory deducted
    expect($product1->fresh()->qty)->toBe(8);
    expect($product2->fresh()->qty)->toBe(15);
});

test('submitting delivery instruction with missing stock or wrong serials shows warning screen', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $product1 = Product::create([
        'sku_code' => 'SKU-TEST-1',
        'name' => 'Test Product 1',
        'type' => 'physical',
        'qty' => 2, // low stock
        'serial_number' => 'SN-1111',
        'status' => 'active',
    ]);

    $payload = [
        'di_number' => 'DI-TEST-WARNING',
        'customer_name' => 'Acme Corp',
        'delivery_address' => '123 Main St',
        'items' => [
            [
                'sku_code' => 'SKU-TEST-1',
                'quantity' => 5, // request more than 2
                'serial_numbers' => 'SN-WRONG', // wrong serial number
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->post(route('delivery-instructions.store'), $payload);

    $response->assertSuccessful(); // renders warning view directly
    $response->assertViewIs('dashboards.delivery_instructions.warning');
    $response->assertSee('Mismatched or Unavailable Items Detected');
    $response->assertSee('Requested quantity (5) exceeds available stock (2)');
    $response->assertSee("Serial number 'SN-WRONG' does not match any of the available serial numbers: SN-1111.");
});

test('confirming partial delivery only delivers available items and leaves others pending', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $product1 = Product::create([
        'sku_code' => 'SKU-TEST-1',
        'name' => 'Test Product 1',
        'type' => 'physical',
        'qty' => 2,
        'serial_number' => 'SN-1111',
        'status' => 'active',
    ]);

    $payload = [
        'di_number' => 'DI-TEST-PARTIAL',
        'customer_name' => 'Acme Corp',
        'delivery_address' => '123 Main St',
        'confirm_partial' => '1', // confirm partial delivery flag
        'items' => [
            [
                'sku_code' => 'SKU-TEST-1',
                'quantity' => 5,
                'serial_numbers' => 'SN-1111, SN-1111',
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->post(route('delivery-instructions.store'), $payload);

    $response->assertRedirect(route('delivery-instructions.index'));

    $this->assertDatabaseHas('delivery_instructions', [
        'di_number' => 'DI-TEST-PARTIAL',
        'status' => 'partial',
    ]);

    $di = DeliveryInstruction::where('di_number', 'DI-TEST-PARTIAL')->first();

    $this->assertDatabaseHas('delivery_instruction_items', [
        'delivery_instruction_id' => $di->id,
        'sku_code' => 'SKU-TEST-1',
        'quantity' => 5,
        'delivered_quantity' => 2, // only 2 delivered
        'status' => 'partial',
    ]);

    // Check inventory is decremented to 0
    expect($product1->fresh()->qty)->toBe(0);

    // Delivery Note generated for the 2 delivered items
    $this->assertDatabaseHas('delivery_notes', [
        'delivery_instruction_id' => $di->id,
    ]);
});

test('end user can release a delivery note and it appears under order fulfillment', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $operator = User::factory()->create(['role' => 'sfq_user']);

    $di = DeliveryInstruction::create([
        'di_number' => 'DI-RELEASE-TEST',
        'customer_name' => 'Release Customer',
        'delivery_address' => 'Release Address',
        'status' => 'completed',
        'user_id' => $user->id,
    ]);

    $dn = DeliveryNote::create([
        'dn_number' => 'DN-RELEASE-TEST',
        'delivery_instruction_id' => $di->id,
        'user_id' => $user->id,
        'status' => 'draft',
    ]);

    // Ensure it is not visible on fulfillment index initially
    $response = $this->actingAs($operator)
        ->get(route('sfq.fulfillment.index'));
    $response->assertSuccessful();
    $response->assertDontSee('DN-RELEASE-TEST');

    // Release the delivery note
    $response = $this->actingAs($user)
        ->post(route('delivery-notes.release', $dn->id));
    $response->assertRedirect();

    expect($dn->fresh()->status)->toBe('released');

    // Ensure it is now visible on fulfillment index
    $response = $this->actingAs($operator)
        ->get(route('sfq.fulfillment.index'));
    $response->assertSuccessful();
    $response->assertSee('DN-RELEASE-TEST');
});

test('authenticated user can download delivery instruction template with product_name and no dummy data', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)
        ->get(route('delivery-instructions.template'));

    $response->assertSuccessful();

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($content)->toContain('sku_code,product_name,quantity,serial_numbers');
    expect($content)->not->toContain('description');
    expect($content)->not->toContain('FortiGate');
});
