<?php

use App\Models\DeliveryInstruction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('end user can view delivery invoices page', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)->get(route('delivery-invoices.index'));

    $response->assertStatus(200);
});

test('sfq user can view delivery invoices page', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $response = $this->actingAs($user)->get(route('delivery-invoices.index'));

    $response->assertStatus(200);
});

test('end user can create a delivery invoice with serial charges and auto total', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $di = DeliveryInstruction::create([
        'user_id' => $user->id,
        'di_number' => 'DI-TEST-900',
        'customer_name' => 'Acme Corporation',
        'so_reference' => 'SO-REF-888',
        'end_user_name' => 'Acme Health System',
        'delivery_address' => '123 West St',
        'status' => 'completed',
    ]);

    $di->items()->create([
        'sku_code' => 'SKU-SER-2001',
        'quantity' => 2,
        'serial_numbers' => 'SN-9001, SN-9002',
    ]);

    $postData = [
        'invoice_number' => 'INV-20260724-999',
        'delivery_instruction_id' => $di->id,
        'items' => [
            [
                'sku_code' => 'SKU-SER-2001',
                'serial_number' => 'SN-9001',
                'quantity' => 1,
                'charge_amount' => 150.00,
            ],
            [
                'sku_code' => 'SKU-SER-2001',
                'serial_number' => 'SN-9002',
                'quantity' => 1,
                'charge_amount' => 250.00,
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('delivery-invoices.store'), $postData);

    $response->assertRedirect(route('delivery-invoices.index'));

    $this->assertDatabaseHas('delivery_invoices', [
        'invoice_number' => 'INV-20260724-999',
        'delivery_instruction_id' => $di->id,
        'total_amount' => 400.00,
        'status' => 'Unpaid',
    ]);

    $this->assertDatabaseHas('delivery_invoice_items', [
        'sku_code' => 'SKU-SER-2001',
        'serial_number' => 'SN-9001',
        'charge_amount' => 150.00,
        'total_amount' => 150.00,
    ]);

    $this->assertDatabaseHas('delivery_invoice_items', [
        'sku_code' => 'SKU-SER-2001',
        'serial_number' => 'SN-9002',
        'charge_amount' => 250.00,
        'total_amount' => 250.00,
    ]);
});

test('invoiced delivery instructions are excluded from create invoice dropdown', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $di1 = DeliveryInstruction::create([
        'user_id' => $user->id,
        'di_number' => 'DI-INVOICED-01',
        'customer_name' => 'Client One',
        'delivery_address' => 'Location 1',
        'status' => 'completed',
    ]);

    $di2 = DeliveryInstruction::create([
        'user_id' => $user->id,
        'di_number' => 'DI-UNINVOICED-02',
        'customer_name' => 'Client Two',
        'delivery_address' => 'Location 2',
        'status' => 'completed',
    ]);

    // Create an invoice for $di1
    $di1->invoice()->create([
        'invoice_number' => 'INV-EXCLUDE-1',
        'user_id' => $user->id,
        'customer_name' => 'Client One',
        'total_amount' => 500.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->get(route('delivery-invoices.create'));

    $response->assertStatus(200);
    $response->assertDontSee('DI-INVOICED-01');
    $response->assertSee('DI-UNINVOICED-02');
});

test('end user can delete a delivery invoice', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $di = DeliveryInstruction::create([
        'user_id' => $user->id,
        'di_number' => 'DI-DEL-100',
        'customer_name' => 'Client Del',
        'delivery_address' => 'Addr 100',
        'status' => 'completed',
    ]);

    $invoice = $di->invoice()->create([
        'invoice_number' => 'INV-DELETE-999',
        'user_id' => $user->id,
        'customer_name' => 'Client Del',
        'total_amount' => 300.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->delete(route('delivery-invoices.destroy', $invoice->id));

    $response->assertRedirect(route('delivery-invoices.index'));
    $this->assertDatabaseMissing('delivery_invoices', [
        'id' => $invoice->id,
    ]);
});
