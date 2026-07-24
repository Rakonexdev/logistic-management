<?php

use App\Models\RentInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('end user can view rent invoices index page with stats', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    RentInvoice::create([
        'invoice_number' => 'RNT-202607-001',
        'user_id' => $user->id,
        'warehouse_name' => 'Main Warehouse',
        'rent_month' => 'July 2026',
        'monthly_rent_amount' => 1200.00,
        'total_amount' => 1200.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->get(route('rent-invoices.index'));

    $response->assertStatus(200);
    $response->assertSee('RNT-202607-001');
    $response->assertSee('July 2026');
    $response->assertSee('1,200.00');
});

test('sfq user can view rent invoices index page', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $response = $this->actingAs($user)->get(route('rent-invoices.index'));

    $response->assertStatus(200);
});

test('end user can create a rent invoice for warehouse rent', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $postData = [
        'invoice_number' => 'RNT-202607-999',
        'warehouse_name' => 'Birkat Al Awamer Main Warehouse',
        'rent_month' => 'July 2026',
        'monthly_rent_amount' => 1200.00,
        'utility_charges' => 150.00,
        'due_date' => '2026-07-31',
        'remarks' => 'July warehouse rent payment',
    ];

    $response = $this->actingAs($user)->post(route('rent-invoices.store'), $postData);

    $response->assertRedirect(route('rent-invoices.index'));

    $this->assertDatabaseHas('rent_invoices', [
        'invoice_number' => 'RNT-202607-999',
        'warehouse_name' => 'Birkat Al Awamer Main Warehouse',
        'rent_month' => 'July 2026',
        'monthly_rent_amount' => 1200.00,
        'utility_charges' => 150.00,
        'total_amount' => 1350.00,
        'status' => 'Unpaid',
    ]);
});

test('end user can mark rent invoice as paid', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $invoice = RentInvoice::create([
        'invoice_number' => 'RNT-202607-777',
        'user_id' => $user->id,
        'warehouse_name' => 'Main Warehouse',
        'rent_month' => 'July 2026',
        'monthly_rent_amount' => 1200.00,
        'total_amount' => 1200.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->post(route('rent-invoices.mark-paid', $invoice->id));

    $response->assertRedirect();
    $this->assertDatabaseHas('rent_invoices', [
        'id' => $invoice->id,
        'status' => 'Paid',
    ]);
});

test('end user can delete a rent invoice', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $invoice = RentInvoice::create([
        'invoice_number' => 'RNT-202607-888',
        'user_id' => $user->id,
        'warehouse_name' => 'Main Warehouse',
        'rent_month' => 'July 2026',
        'monthly_rent_amount' => 1200.00,
        'total_amount' => 1200.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->delete(route('rent-invoices.destroy', $invoice->id));

    $response->assertRedirect(route('rent-invoices.index'));
    $this->assertDatabaseMissing('rent_invoices', [
        'id' => $invoice->id,
    ]);
});
