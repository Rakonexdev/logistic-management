<?php

use App\Models\Product;
use App\Models\ReturnInstruction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('end user can view return instructions page', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)->get(route('return-instructions.index'));

    $response->assertStatus(200);
});

test('end user can create a return instruction', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $product = Product::create([
        'sku_code' => 'SKU-RET-01',
        'name' => 'Test Return SKU',
        'type' => 'physical',
        'qty' => 10,
        'status' => 'active',
    ]);

    $postData = [
        'return_ref' => 'RET-20260723-999',
        'customer_name' => 'Acme Corporation',
        'pickup_address' => '123 Business Bay, Dubai',
        'items' => [
            [
                'sku_code' => $product->sku_code,
                'description' => 'Faulty unit return',
                'quantity' => 2,
                'serial_numbers' => 'SN-RET-101, SN-RET-102',
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('return-instructions.store'), $postData);

    $response->assertRedirect(route('return-instructions.index'));
    $this->assertDatabaseHas('return_instructions', [
        'return_ref' => 'RET-20260723-999',
        'customer_name' => 'Acme Corporation',
        'status' => 'Created',
    ]);
    $this->assertDatabaseHas('return_instruction_items', [
        'sku_code' => 'SKU-RET-01',
        'quantity' => 2,
    ]);
});

test('sfq user can assign driver and storage location to return instruction', function () {
    $endUser = User::factory()->create(['role' => 'end_user']);
    $sfqUser = User::factory()->create(['role' => 'sfq_user']);

    $instruction = ReturnInstruction::create([
        'user_id' => $endUser->id,
        'return_ref' => 'RET-TEST-001',
        'customer_name' => 'Test Client',
        'pickup_address' => 'Test Street 5',
        'status' => 'Created',
    ]);

    $response = $this->actingAs($sfqUser)->post(route('sfq.returns.assign'), [
        'return_ref' => $instruction->return_ref,
        'driver_name' => 'Ahmed Driver',
        'storing_location' => 'WH-1 (Zone-A, Rack-1, Bin-1, Level-1)',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('return_instructions', [
        'return_ref' => 'RET-TEST-001',
        'driver_name' => 'Ahmed Driver',
        'storing_location' => 'WH-1 (Zone-A, Rack-1, Bin-1, Level-1)',
        'status' => 'Driver Assigned',
    ]);
});

test('sfq user marking return as stored automatically restocks product inventory', function () {
    $endUser = User::factory()->create(['role' => 'end_user']);
    $sfqUser = User::factory()->create(['role' => 'sfq_user']);
    $product = Product::create([
        'sku_code' => 'SKU-RESTOCK-01',
        'name' => 'Restock Product',
        'type' => 'physical',
        'qty' => 5,
        'serial_number' => 'SN-1, SN-2',
        'status' => 'active',
    ]);

    $instruction = ReturnInstruction::create([
        'user_id' => $endUser->id,
        'return_ref' => 'RET-RESTOCK-001',
        'customer_name' => 'Client XYZ',
        'pickup_address' => 'Location XYZ',
        'status' => 'Driver Assigned',
    ]);

    $instruction->items()->create([
        'sku_code' => $product->sku_code,
        'quantity' => 3,
        'serial_numbers' => 'SN-3, SN-4, SN-5',
    ]);

    $response = $this->actingAs($sfqUser)->post(route('sfq.returns.status'), [
        'return_ref' => $instruction->return_ref,
        'status' => 'Stored',
    ]);

    $response->assertSessionHasNoErrors();
    $product->refresh();

    expect($product->qty)->toBe(8);
    expect($product->serial_number)->toContain('SN-3');
});
