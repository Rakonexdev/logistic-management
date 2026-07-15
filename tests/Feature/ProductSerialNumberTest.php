<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(LazilyRefreshDatabase::class);

test('authenticated users can store and update product with serial number', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $payload = [
        'sku_code' => 'SKU-SERIAL-TEST',
        'name' => 'Serial Test Product',
        'type' => 'physical',
        'qty' => 5,
        'serial_number' => 'FG100FTK25011385',
        'vendor_id' => 'V-FORTINET',
        'category' => 'Firewall',
        'status' => 'active',
    ];

    $this->actingAs($user)
        ->post(route('products.store'), $payload)
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'sku_code' => 'SKU-SERIAL-TEST',
        'qty' => 5,
        'serial_number' => 'FG100FTK25011385',
    ]);

    $product = Product::where('sku_code', 'SKU-SERIAL-TEST')->first();

    $this->actingAs($user)
        ->put(route('products.update', $product->id), array_merge($payload, ['serial_number' => 'FG100FTK25011386']))
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'serial_number' => 'FG100FTK25011386',
    ]);
});

test('users can bulk upload products with serial numbers', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $csvContent = "sku_code,name,type,qty,serial_number,vendor_id,category,status\n";
    $csvContent .= "FG-100F,FG-100F,physical,10,FG100FTK25011385,V-FORTINET,Firewall,active\n";
    $csvContent .= "FG-40F,FG-40F,physical,25,FGT40FTK24083675,V-FORTINET,Firewall,active\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

    $this->actingAs($user)
        ->post(route('products.bulk-upload'), [
            'csv_file' => $file,
        ])
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'sku_code' => 'FG-100F',
        'qty' => 10,
        'serial_number' => 'FG100FTK25011385',
    ]);

    $this->assertDatabaseHas('products', [
        'sku_code' => 'FG-40F',
        'qty' => 25,
        'serial_number' => 'FGT40FTK24083675',
    ]);
});

test('adding a product with duplicate serial number fails validation', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    Product::create([
        'sku_code' => 'SKU-EXISTING',
        'name' => 'Existing Product',
        'type' => 'physical',
        'qty' => 1,
        'serial_number' => 'FG100FTK25011385',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->from(route('products.create'))
        ->post(route('products.store'), [
            'sku_code' => 'SKU-DUPLICATE-SN',
            'name' => 'Duplicate SN Product',
            'type' => 'physical',
            'qty' => 2,
            'serial_number' => 'FG100FTK25011385', // duplicate
            'status' => 'active',
        ]);

    $response->assertRedirect(route('products.create'));
    $response->assertSessionHasErrors('serial_number');
});
