<?php

use App\Models\ChequeCollection;
use App\Models\ChequeCollectionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('end user and sfq user can view cheque collection invoices index page', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $sfqUser = User::factory()->create(['role' => 'sfq_user']);

    $responseEnd = $this->actingAs($user)->get(route('cheque-collection-invoices.index'));
    $responseEnd->assertStatus(200);

    $responseSfq = $this->actingAs($sfqUser)->get(route('cheque-collection-invoices.index'));
    $responseSfq->assertStatus(200);
});

test('user can create a cheque collection invoice with total cheque amount', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $cheque1 = ChequeCollection::create([
        'user_id' => $user->id,
        'collection_ref' => 'CHQ-COL-001',
        'customer_name' => 'Acme Qatar',
        'collection_location' => 'Doha Tower',
        'amount' => 5000.00,
        'amount_usd' => 1370.00,
        'cheque_number' => 'CHQ-987654',
        'cheque_date' => '2026-07-25',
        'po_reference' => 'PO-101',
        'so_reference' => 'SO-202',
        'invoice_reference' => 'INV-303',
        'collection_fee' => 35.00,
        'status' => 'Submitted',
    ]);

    $postData = [
        'invoice_number' => 'CHQ-INV-20260724-999',
        'customer_name' => 'Acme Qatar',
        'items' => [
            [
                'cheque_collection_id' => $cheque1->id,
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('cheque-collection-invoices.store'), $postData);

    $response->assertRedirect(route('cheque-collection-invoices.index'));

    $this->assertDatabaseHas('cheque_collection_invoices', [
        'invoice_number' => 'CHQ-INV-20260724-999',
        'customer_name' => 'Acme Qatar',
        'total_amount' => 5000.00,
        'status' => 'Unpaid',
    ]);

    $this->assertDatabaseHas('cheque_collection_invoice_items', [
        'cheque_collection_id' => $cheque1->id,
        'cheque_amount' => 5000.00,
    ]);
});

test('user can mark cheque collection invoice as paid', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $invoice = ChequeCollectionInvoice::create([
        'invoice_number' => 'CHQ-INV-PAID-11',
        'user_id' => $user->id,
        'customer_name' => 'Client Test',
        'total_amount' => 70.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->post(route('cheque-collection-invoices.mark-paid', $invoice->id));

    $response->assertRedirect();
    $this->assertDatabaseHas('cheque_collection_invoices', [
        'id' => $invoice->id,
        'status' => 'Paid',
    ]);
});

test('user can delete a cheque collection invoice', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $invoice = ChequeCollectionInvoice::create([
        'invoice_number' => 'CHQ-INV-DEL-22',
        'user_id' => $user->id,
        'customer_name' => 'Client Test',
        'total_amount' => 35.00,
        'status' => 'Unpaid',
    ]);

    $response = $this->actingAs($user)->delete(route('cheque-collection-invoices.destroy', $invoice->id));

    $response->assertRedirect(route('cheque-collection-invoices.index'));
    $this->assertDatabaseMissing('cheque_collection_invoices', [
        'id' => $invoice->id,
    ]);
});
