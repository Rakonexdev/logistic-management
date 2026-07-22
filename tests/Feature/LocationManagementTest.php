<?php

use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected from location creation', function () {
    $this->get(route('sfq.locations.create'))
        ->assertRedirect(route('login'));
});

test('sfq users can view locations create page and store a location', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);
    $product = Product::create([
        'sku_code' => 'SKU-LOC-1',
        'name' => 'Loc Product',
        'type' => 'physical',
        'category' => 'apparel',
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('sfq.locations.create'))
        ->assertSuccessful()
        ->assertSee('Create Location');

    $payload = [
        'warehouse' => 'WH-Test',
        'zone' => 'Z',
        'rack' => '10',
        'bin' => 'F2',
        'level' => '3',
        'sku' => 'SKU-LOC-1',
        'qty' => 200,
        'status' => 'Available',
    ];

    $this->actingAs($user)
        ->post(route('sfq.locations.store'), $payload)
        ->assertRedirect(route('sfq.locations.index'));

    $this->assertDatabaseHas('locations', $payload);

    $this->actingAs($user)
        ->get(route('sfq.locations.index'))
        ->assertSee('SKU-LOC-1')
        ->assertSee('Loc Product');
});

test('sfq users can filter locations stock by per page limits', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    // Create 15 products
    for ($i = 1; $i <= 15; $i++) {
        Product::create([
            'sku_code' => "SKU-PROD-{$i}",
            'name' => "Product {$i}",
            'type' => 'physical',
            'category' => 'apparel',
            'qty' => 10,
            'status' => 'active',
        ]);
    }

    $this->actingAs($user)
        ->get(route('sfq.locations.index', ['per_page' => 10]))
        ->assertSuccessful()
        ->assertSee('Showing 1 to 10 of 15 records');

    $this->actingAs($user)
        ->get(route('sfq.locations.index', ['per_page' => 20]))
        ->assertSuccessful()
        ->assertSee('Showing 1 to 15 of 15 records');
});

test('sfq users can edit and update a location', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);
    $product = Product::create([
        'sku_code' => 'SKU-LOC-1',
        'name' => 'Loc Product',
        'type' => 'physical',
        'category' => 'apparel',
        'status' => 'active',
    ]);
    $location = Location::create([
        'warehouse' => 'WH-Old',
        'zone' => 'Z',
        'rack' => '10',
        'bin' => 'F2',
        'level' => '3',
        'sku' => 'SKU-LOC-1',
        'qty' => 200,
        'status' => 'Available',
    ]);

    $this->actingAs($user)
        ->get(route('sfq.locations.edit', $location->id))
        ->assertSuccessful()
        ->assertSee('Edit Location')
        ->assertSee('WH-Old');

    $this->actingAs($user)
        ->put(route('sfq.locations.update', $location->id), [
            'warehouse' => 'WH-New',
            'zone' => 'Z',
            'rack' => '10',
            'bin' => 'F2',
            'level' => '3',
            'sku' => 'SKU-LOC-1',
            'qty' => 500,
            'status' => 'Reserved',
        ])
        ->assertRedirect(route('sfq.locations.index'));

    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'warehouse' => 'WH-New',
        'qty' => 500,
        'status' => 'Reserved',
    ]);
});

test('sfq users can delete a location', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);
    $location = Location::create([
        'warehouse' => 'WH-Test',
        'zone' => 'Z',
        'rack' => '10',
        'bin' => 'F2',
        'level' => '3',
        'sku' => 'SKU-LOC-1',
        'qty' => 200,
        'status' => 'Available',
    ]);

    $this->actingAs($user)
        ->delete(route('sfq.locations.destroy', $location->id))
        ->assertRedirect(route('sfq.locations.index'));

    $this->assertDatabaseMissing('locations', [
        'id' => $location->id,
    ]);
});
