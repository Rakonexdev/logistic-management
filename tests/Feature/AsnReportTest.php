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

test('submitting ASN with physical items validates serial numbers and quantity match', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    Product::create([
        'sku_code' => 'SKU-PHYS-1',
        'name' => 'Physical Product 1',
        'type' => 'physical',
        'qty' => 10,
        'status' => 'active',
    ]);

    // 1. Missing serial numbers fails validation
    $this->actingAs($user)
        ->from(route('asns.create'))
        ->post(route('asns.store'), [
            'asn_reference' => 'ASN-PHYS-TEST-1',
            'airway_bill' => 'AWB-1',
            'vendor_id' => 'Solutions Four W.L.L',
            'items' => [
                [
                    'sku_code' => 'SKU-PHYS-1',
                    'quantity' => 2,
                    'serial_numbers' => '', // Empty
                ],
            ],
            'status' => 'submitted',
        ])
        ->assertRedirect(route('asns.create'))
        ->assertSessionHasErrors('items.0.serial_numbers');

    // 2. Quantity and serial count mismatch fails validation
    $this->actingAs($user)
        ->from(route('asns.create'))
        ->post(route('asns.store'), [
            'asn_reference' => 'ASN-PHYS-TEST-2',
            'airway_bill' => 'AWB-2',
            'vendor_id' => 'Solutions Four W.L.L',
            'items' => [
                [
                    'sku_code' => 'SKU-PHYS-1',
                    'quantity' => 2,
                    'serial_numbers' => 'SN-001', // Count is 1, qty is 2
                ],
            ],
            'status' => 'submitted',
        ])
        ->assertRedirect(route('asns.create'))
        ->assertSessionHasErrors('items.0.serial_numbers');

    // 3. Correct match passes validation
    $this->actingAs($user)
        ->post(route('asns.store'), [
            'asn_reference' => 'ASN-PHYS-TEST-3',
            'airway_bill' => 'AWB-3',
            'vendor_id' => 'Solutions Four W.L.L',
            'items' => [
                [
                    'sku_code' => 'SKU-PHYS-1',
                    'quantity' => 2,
                    'serial_numbers' => 'SN-001, SN-002',
                ],
            ],
            'status' => 'submitted',
        ])
        ->assertRedirect(route('asns.index'));
});
