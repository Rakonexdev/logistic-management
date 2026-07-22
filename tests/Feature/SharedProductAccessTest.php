<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('end users can access products index', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertSuccessful()
        ->assertSee('Product Management');
});

test('sfq users can access products index and view template download', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertSuccessful()
        ->assertSee('Product Management');

    $this->actingAs($user)
        ->get(route('products.template'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});
