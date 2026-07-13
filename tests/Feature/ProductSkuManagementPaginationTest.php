<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('end users can use pagination options on product management page', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    // Create 15 products
    for ($i = 1; $i <= 15; $i++) {
        Product::create([
            'sku_code' => "SKU-PAG-{56885-$i}",
            'name' => "Sku Product {$i}",
            'type' => 'physical',
            'category' => 'apparel',
            'status' => 'active',
        ]);
    }

    $this->actingAs($user)
        ->get(route('products.index', ['per_page' => 10]))
        ->assertSuccessful()
        ->assertSee('Showing 1 to 10 of 15 records');

    $this->actingAs($user)
        ->get(route('products.index', ['per_page' => 25]))
        ->assertSuccessful()
        ->assertSee('Showing 1 to 15 of 15 records');
});
