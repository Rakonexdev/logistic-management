<?php

use App\Models\User;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('completed sales order appears in delivery planning index', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    // Create a Sales Order that is completed (meaning fulfillment is done)
    $order = SalesOrder::create([
        'so_number' => 'SO-DELIVERY-TEST-1',
        'customer_name' => 'John Client',
        'designation' => 'Manhattan, NY',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Pending Assignment',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($user)
        ->get(route('sfq.deliveries.index'));

    $response->assertStatus(200);
    $response->assertSee('SO-DELIVERY-TEST-1');
    $response->assertSee('John Client (Manhattan, NY)');
});

test('operator can assign driver and vehicle to delivery', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $order = SalesOrder::create([
        'so_number' => 'SO-DELIVERY-TEST-2',
        'customer_name' => 'Jane Client',
        'designation' => 'Queens, NY',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Pending Assignment',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($user)
        ->post(route('sfq.deliveries.assign'), [
            'delivery_ref' => 'DEL-' . $order->id,
            'driver' => 'John Doe',
            'vehicle' => 'Truck A',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'id' => $order->id,
        'driver' => 'John Doe',
        'vehicle' => 'Truck A',
        'delivery_status' => 'Assigned',
    ]);
});

test('operator can complete delivery', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $order = SalesOrder::create([
        'so_number' => 'SO-DELIVERY-TEST-3',
        'customer_name' => 'Bob Client',
        'designation' => 'Brooklyn, NY',
        'order_date' => '2026-07-14',
        'status' => 'completed',
        'delivery_status' => 'Assigned',
        'driver' => 'John Doe',
        'vehicle' => 'Truck A',
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->actingAs($user)
        ->post(route('sfq.deliveries.complete'), [
            'delivery_ref' => 'DEL-' . $order->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('sales_orders', [
        'id' => $order->id,
        'delivery_status' => 'Delivered',
    ]);
});
