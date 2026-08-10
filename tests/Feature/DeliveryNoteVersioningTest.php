<?php

use App\Models\DeliveryInstruction;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('user can view delivery notes index', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)
        ->get(route('delivery-notes.index'));

    $response->assertSuccessful();
});

test('user can amend a delivery note creating v1 version', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $di = DeliveryInstruction::create([
        'di_number' => 'DI-TEST-100',
        'customer_name' => 'Acme Corp',
        'delivery_address' => '123 Main St',
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $dn = DeliveryNote::create([
        'dn_number' => 'DN-20260807-100',
        'delivery_instruction_id' => $di->id,
        'user_id' => $user->id,
        'status' => 'released',
        'version' => 1,
        'is_latest' => true,
    ]);

    DeliveryNoteItem::create([
        'delivery_note_id' => $dn->id,
        'sku_code' => 'SKU-001',
        'quantity' => 5,
        'serial_numbers' => 'SN-1, SN-2',
    ]);

    $amendPayload = [
        'amendment_reason' => 'Corrected quantity from 5 to 3',
        'driver' => 'John Doe',
        'vehicle' => 'QA-9999',
        'items' => [
            [
                'sku_code' => 'SKU-001',
                'quantity' => 3,
                'serial_numbers' => 'SN-1, SN-2',
            ],
        ],
    ];

    $response = $this->actingAs($user)
        ->post(route('delivery-notes.amend', $dn->id), $amendPayload);

    $response->assertRedirect(route('delivery-notes.index'));

    // Verify parent note status updated
    $dn->refresh();
    expect($dn->status)->toBe('amended');
    expect($dn->is_latest)->toBeFalse();

    // Verify new v1 note created
    $amendedNote = DeliveryNote::where('parent_dn_id', $dn->id)->first();
    expect($amendedNote)->not->toBeNull();
    expect($amendedNote->dn_number)->toBe('DN-20260807-100-v1');
    expect($amendedNote->version)->toBe(2);
    expect($amendedNote->version_label)->toBe('v1');
    expect($amendedNote->is_latest)->toBeTrue();
    expect($amendedNote->amendment_reason)->toBe('Corrected quantity from 5 to 3');
    expect($amendedNote->driver)->toBe('John Doe');
    expect($amendedNote->vehicle)->toBe('QA-9999');

    // Verify items
    expect($amendedNote->items->first()->quantity)->toBe(3);
});

test('user can cancel a delivery note with reason', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $di = DeliveryInstruction::create([
        'di_number' => 'DI-TEST-200',
        'customer_name' => 'Beta Corp',
        'delivery_address' => '456 Side St',
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $dn = DeliveryNote::create([
        'dn_number' => 'DN-20260807-200',
        'delivery_instruction_id' => $di->id,
        'user_id' => $user->id,
        'status' => 'released',
        'version' => 1,
        'is_latest' => true,
    ]);

    $response = $this->actingAs($user)
        ->post(route('delivery-notes.cancel', $dn->id), [
            'cancellation_reason' => 'Duplicate order submitted',
        ]);

    $response->assertRedirect(route('delivery-notes.index'));

    $dn->refresh();
    expect($dn->status)->toBe('canceled');
    expect($dn->is_latest)->toBeFalse();
    expect($dn->amendment_reason)->toBe('Duplicate order submitted');
});

test('user can retrieve revision history timeline JSON', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $di = DeliveryInstruction::create([
        'di_number' => 'DI-TEST-300',
        'customer_name' => 'Gamma Corp',
        'delivery_address' => '789 High St',
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $dn1 = DeliveryNote::create([
        'dn_number' => 'DN-20260807-300',
        'delivery_instruction_id' => $di->id,
        'user_id' => $user->id,
        'status' => 'amended',
        'version' => 1,
        'is_latest' => false,
    ]);

    $dn2 = DeliveryNote::create([
        'dn_number' => 'DN-20260807-300-v1',
        'delivery_instruction_id' => $di->id,
        'user_id' => $user->id,
        'status' => 'released',
        'version' => 2,
        'version_label' => 'v1',
        'parent_dn_id' => $dn1->id,
        'is_latest' => true,
        'amendment_reason' => 'First revision',
    ]);

    $response = $this->actingAs($user)
        ->get(route('delivery-notes.history', $dn2->id));

    $response->assertSuccessful();
    $response->assertJsonPath('dn_number', 'DN-20260807-300');
    $response->assertJsonCount(2, 'history');
});
