<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to login from sfq interfaces', function (string $url) {
    $this->get($url)
        ->assertRedirect(route('login'));
})->with([
    '/sfq/grns',
    '/sfq/locations',
    '/sfq/fulfillment',
    '/sfq/deliveries',
    '/sfq/returns',
    '/sfq/cheques',
    '/sfq/reconciliation',
    '/sfq/invoices',
    '/sfq/reports',
]);

test('end users are forbidden from sfq interfaces', function (string $url) {
    $user = User::factory()->create(['role' => 'end_user']);

    $this->actingAs($user)
        ->get($url)
        ->assertForbidden();
})->with([
    '/sfq/grns',
    '/sfq/locations',
    '/sfq/fulfillment',
    '/sfq/deliveries',
    '/sfq/returns',
    '/sfq/cheques',
    '/sfq/reconciliation',
    '/sfq/invoices',
    '/sfq/reports',
]);

test('sfq users can access all sfq interfaces', function (string $url) {
    $user = User::factory()->create(['role' => 'sfq_user']);

    $this->actingAs($user)
        ->get($url)
        ->assertSuccessful();
})->with([
    '/sfq/grns',
    '/sfq/locations',
    '/sfq/fulfillment',
    '/sfq/deliveries',
    '/sfq/returns',
    '/sfq/cheques',
    '/sfq/reconciliation',
    '/sfq/invoices',
    '/sfq/reports',
]);
