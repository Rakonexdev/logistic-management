<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(LazilyRefreshDatabase::class);

test('forgot password screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertSuccessful()
        ->assertSee('Reset Password');
});

test('password reset link can be requested', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.email'), [
        'email' => $user->email,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $user->email,
    ]);
});

test('reset password screen can be rendered', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->get(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ]));

    $response->assertSuccessful()
        ->assertSee('Reset Password')
        ->assertSee($user->email);
});

test('password can be reset with valid token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasNoErrors();

    $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
});

test('profile screen can be rendered for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertSuccessful()
        ->assertSee('My Profile')
        ->assertSee($user->name)
        ->assertSee($user->email);
});

test('profile information can be updated', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $response = $this->actingAs($user)
        ->post(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $user->refresh();
    $this->assertEquals('Updated Name', $user->name);
    $this->assertEquals('updated@example.com', $user->email);
});

test('change password screen can be rendered for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('profile.password'));

    $response->assertSuccessful()
        ->assertSee('Reset Password')
        ->assertSee('Current Password');
});

test('password can be updated by authenticated user', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-secure-password'),
    ]);

    $response = $this->actingAs($user)
        ->post(route('profile.password.update'), [
            'current_password' => 'current-secure-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
});
