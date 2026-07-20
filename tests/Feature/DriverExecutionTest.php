<?php

use App\Models\ChequeCollection;
use App\Models\ReturnPickup;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('driver can view assigned deliveries, returns, and cheques', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $otherDriver = User::factory()->create(['name' => 'Other Driver', 'role' => 'driver']);

    // Delivery for $driver
    $order = SalesOrder::create([
        'so_number' => 'SO-TEST-1',
        'customer_name' => 'Client A',
        'customer_address' => 'NJ',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Assigned',
        'driver' => 'John Driver',
        'user_id' => User::factory()->create()->id,
    ]);
    SalesOrderItem::create(['sales_order_id' => $order->id, 'sku_code' => 'SKU-A', 'quantity' => 2]);

    // Delivery for $otherDriver
    $otherOrder = SalesOrder::create([
        'so_number' => 'SO-TEST-OTHER',
        'customer_name' => 'Client B',
        'customer_address' => 'NY',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Assigned',
        'driver' => 'Other Driver',
        'user_id' => User::factory()->create()->id,
    ]);

    // Return for $driver
    $ret = ReturnPickup::create([
        'return_ref' => 'RET-TEST-1',
        'driver' => 'John Driver',
        'pickup_location' => 'NJ',
        'product_sku' => 'SKU-A',
        'quantity' => 1,
        'status' => 'Pending Pickup',
    ]);

    // Cheque for $driver
    $chq = ChequeCollection::create([
        'collection_ref' => 'CHQ-TEST-1',
        'customer_name' => 'Client A',
        'collection_location' => 'NJ',
        'amount' => 500.00,
        'status' => 'Pending Collection',
        'driver' => 'John Driver',
    ]);

    $response = $this->actingAs($driver)->get(route('driver.dashboard'));

    $response->assertSuccessful();
    $response->assertSee('DEL-'.$order->id);
    $response->assertSee('RET-TEST-1');
    $response->assertSee('CHQ-TEST-1');

    // Should not see other driver's delivery
    $response->assertDontSee('DEL-'.$otherOrder->id);
});

test('driver can mark delivery as arrived', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $order = SalesOrder::create([
        'so_number' => 'SO-TEST-1',
        'customer_name' => 'Client A',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Assigned',
        'driver' => 'John Driver',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($driver)->post(route('driver.deliveries.arrive', $order->id));

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'id' => $order->id,
        'delivery_status' => 'Arrived',
    ]);
});

test('driver can complete delivery with proof uploads', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $order = SalesOrder::create([
        'so_number' => 'SO-TEST-1',
        'customer_name' => 'Client A',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Arrived',
        'driver' => 'John Driver',
        'user_id' => User::factory()->create()->id,
    ]);

    Storage::fake('public');
    $signedProof = UploadedFile::fake()->image('signature.png');
    $deliveryPhoto = UploadedFile::fake()->image('delivery.png');

    $response = $this->actingAs($driver)->post(route('driver.deliveries.complete', $order->id), [
        'recipient_name' => 'Jane Client',
        'delivery_remarks' => 'Dropped off at reception.',
        'signed_proof' => $signedProof,
        'delivery_photo' => $deliveryPhoto,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'id' => $order->id,
        'delivery_status' => 'Delivered',
        'recipient_name' => 'Jane Client',
        'delivery_remarks' => 'Dropped off at reception.',
    ]);

    $updatedOrder = SalesOrder::find($order->id);
    expect($updatedOrder->signed_proof_path)->not->toBeNull();
    expect($updatedOrder->delivery_photo_path)->not->toBeNull();
});

test('driver can report issue with delivery', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $order = SalesOrder::create([
        'so_number' => 'SO-TEST-1',
        'customer_name' => 'Client A',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Assigned',
        'driver' => 'John Driver',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($driver)->post(route('driver.deliveries.issue', $order->id), [
        'delivery_issue' => 'Vehicle flat tire.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'id' => $order->id,
        'delivery_status' => 'Issue Reported',
        'delivery_issue' => 'Vehicle flat tire.',
    ]);
});

test('driver can start, complete, and handover return pickup', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $ret = ReturnPickup::create([
        'return_ref' => 'RET-TEST-1',
        'driver' => 'John Driver',
        'pickup_location' => 'NJ',
        'product_sku' => 'SKU-A',
        'quantity' => 5,
        'status' => 'Pending Pickup',
    ]);

    // Start pickup
    $response = $this->actingAs($driver)->post(route('driver.returns.start', $ret->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('return_pickups', [
        'id' => $ret->id,
        'status' => 'Pickup Started',
    ]);

    // Complete pickup
    $photo = UploadedFile::fake()->image('return.png');
    $response = $this->actingAs($driver)->post(route('driver.returns.complete', $ret->id), [
        'quantity_picked_up' => 5,
        'condition_data' => 'Good condition, sealed box',
        'remarks' => 'None',
        'photo' => $photo,
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('return_pickups', [
        'id' => $ret->id,
        'status' => 'Completed',
        'quantity_picked_up' => 5,
        'condition_data' => 'Good condition, sealed box',
    ]);

    // Handover pickup
    $response = $this->actingAs($driver)->post(route('driver.returns.handover', $ret->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('return_pickups', [
        'id' => $ret->id,
        'status' => 'Returned to Warehouse',
    ]);
});

test('driver can collect, submit, and report issue on cheque', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $chq = ChequeCollection::create([
        'collection_ref' => 'CHQ-TEST-1',
        'customer_name' => 'Client A',
        'collection_location' => 'NJ',
        'amount' => 500.00,
        'status' => 'Pending Collection',
        'driver' => 'John Driver',
    ]);

    // Collect Cheque
    $photo = UploadedFile::fake()->image('cheque.png');
    $response = $this->actingAs($driver)->post(route('driver.cheques.collect', $chq->id), [
        'photo' => $photo,
        'remarks' => 'Collected from front desk.',
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('cheque_collections', [
        'id' => $chq->id,
        'status' => 'Collected',
        'remarks' => 'Collected from front desk.',
    ]);

    // Submit Cheque
    $response = $this->actingAs($driver)->post(route('driver.cheques.submit', $chq->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('cheque_collections', [
        'id' => $chq->id,
        'status' => 'Submitted',
    ]);
});

test('driver can report issue on cheque', function () {
    $driver = User::factory()->create(['name' => 'John Driver', 'role' => 'driver']);
    $chq = ChequeCollection::create([
        'collection_ref' => 'CHQ-TEST-2',
        'customer_name' => 'Client A',
        'collection_location' => 'NJ',
        'amount' => 500.00,
        'status' => 'Pending Collection',
        'driver' => 'John Driver',
    ]);

    $response = $this->actingAs($driver)->post(route('driver.cheques.issue', $chq->id), [
        'remarks' => 'Customer refused to pay.',
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('cheque_collections', [
        'id' => $chq->id,
        'status' => 'Issue Reported',
        'remarks' => 'Customer refused to pay.',
    ]);
});
