<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests can view driver login form', function () {
    $response = $this->get(route('driver.login'));

    $response->assertSuccessful();
    $response->assertSee('Driver Connect');
});

test('guests are redirected to login when accessing driver dashboard', function () {
    $response = $this->get(route('driver.dashboard'));

    $response->assertRedirect('/login');
});

test('non driver roles cannot login via driver login post', function () {
    $user = User::factory()->create(['role' => 'sfq_user', 'password' => bcrypt('password')]);

    $response = $this->post('/driver/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('drivers can login via driver login post and access dashboard', function () {
    $driver = User::factory()->create(['role' => 'driver', 'password' => bcrypt('password')]);

    $response = $this->post('/driver/login', [
        'email' => $driver->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('driver.dashboard'));
    $this->assertAuthenticatedAs($driver);

    $dashboardResponse = $this->actingAs($driver)->get(route('driver.dashboard'));
    $dashboardResponse->assertSuccessful();
    $dashboardResponse->assertSee($driver->name);
});

test('authenticated driver can logout', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $response = $this->actingAs($driver)->post(route('driver.logout'));

    $response->assertRedirect(route('driver.login'));
    $this->assertGuest();
});
