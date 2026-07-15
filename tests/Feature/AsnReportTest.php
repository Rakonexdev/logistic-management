<?php

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to login from asn report', function () {
    $user = User::factory()->create();
    $asn = AdvanceShippingNote::create([
        'asn_reference' => 'ASN-GUEST-TEST',
        'airway_bill' => 'AWB-GUEST',
        'vendor_id' => 'V-GUEST',
        'status' => 'submitted',
        'user_id' => $user->id,
    ]);

    $response = $this->get(route('asns.report', $asn->id));

    $response->assertRedirect(route('login'));
});

test('unauthorized users cannot view others asn reports', function () {
    $user1 = User::factory()->create(['role' => 'end_user']);
    $user2 = User::factory()->create(['role' => 'end_user']);

    $asn = AdvanceShippingNote::create([
        'asn_reference' => 'ASN-OWNER-TEST',
        'airway_bill' => 'AWB-1',
        'vendor_id' => 'VEN-1',
        'status' => 'submitted',
        'user_id' => $user1->id,
    ]);

    $response = $this->actingAs($user2)->get(route('asns.report', $asn->id));

    $response->assertNotFound();
});

test('authorized users can view printable report', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $product = Product::create([
        'sku_code' => 'SKU-REP-1',
        'name' => 'Report Product',
        'type' => 'physical',
        'qty' => 100,
        'status' => 'active',
    ]);

    $asn = AdvanceShippingNote::create([
        'asn_reference' => 'ASN-REP-TEST',
        'airway_bill' => 'AWB-1',
        'vendor_id' => 'VEN-1',
        'status' => 'submitted',
        'user_id' => $user->id,
    ]);

    $item = AsnItem::create([
        'asn_id' => $asn->id,
        'sku_code' => 'SKU-REP-1',
        'quantity' => 10,
        'received_qty' => 8,
        'discrepancy_qty' => -2,
        'discrepancy_reason' => 'shortage',
    ]);

    $response = $this->actingAs($user)->get(route('asns.report', $asn->id));

    $response->assertSuccessful();
    $response->assertSee('Inspection Report');
    $response->assertSee('ASN-REP-TEST');
    $response->assertSee('Report Product');
    $response->assertSee('Prepared By');
});
