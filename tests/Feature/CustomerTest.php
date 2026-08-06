<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('authenticated end users can view the customer index page', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    Customer::factory()->create(['name' => 'Acme Corp']);

    $this->actingAs($user)
        ->get(route('customers.index'))
        ->assertSuccessful()
        ->assertSee('Customer Management')
        ->assertSee('Acme Corp');
});

test('authenticated sfq users can view the customer index page', function () {
    $user = User::factory()->create(['role' => 'sfq_user']);
    Customer::factory()->create(['name' => 'Beta Logistics']);

    $this->actingAs($user)
        ->get(route('customers.index'))
        ->assertSuccessful()
        ->assertSee('Beta Logistics');
});

test('unauthenticated users are redirected to login', function () {
    $this->get(route('customers.index'))
        ->assertRedirect(route('login'));
});

test('user can view the create customer form', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $this->actingAs($user)
        ->get(route('customers.create'))
        ->assertSuccessful()
        ->assertSee('Add New Customer');
});

test('user can create a customer with UAE or Qatar 8-digit contact number', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Dubai Freight Corp',
            'country_code' => '+971',
            'contact_number_digits' => '50123456',
            'address' => '123 Business Bay, Dubai',
        ]);

    $response->assertRedirect(route('customers.index'))
        ->assertSessionHas('success', 'Customer created successfully.');

    $this->assertDatabaseHas('customers', [
        'name' => 'Dubai Freight Corp',
        'contact_number' => '+971 50123456',
    ]);
});

test('contact number creation fails if digits count is not 8', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)
        ->post(route('customers.store'), [
            'name' => 'Invalid Phone Corp',
            'country_code' => '+974',
            'contact_number_digits' => '12345',
        ]);

    $response->assertSessionHasErrors('contact_number_digits');
});

test('customer creation fails when name is missing', function () {
    $user = User::factory()->create(['role' => 'end_user']);

    $response = $this->actingAs($user)
        ->post(route('customers.store'), [
            'name' => '',
            'contact_number' => '12345',
        ]);

    $response->assertSessionHasErrors('name');
});

test('user can view the edit customer form', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $customer = Customer::factory()->create(['name' => 'Original Name']);

    $this->actingAs($user)
        ->get(route('customers.edit', $customer))
        ->assertSuccessful()
        ->assertSee('Edit Customer')
        ->assertSee('Original Name');
});

test('user can update an existing customer', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $customer = Customer::factory()->create([
        'name' => 'Old Name',
        'contact_number' => '11111',
    ]);

    $response = $this->actingAs($user)
        ->put(route('customers.update', $customer), [
            'name' => 'Updated Name',
            'contact_number' => '99999',
            'address' => 'New Address Street',
        ]);

    $response->assertRedirect(route('customers.index'))
        ->assertSessionHas('success', 'Customer updated successfully.');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Updated Name',
        'contact_number' => '99999',
        'address' => 'New Address Street',
    ]);
});

test('user can delete a customer', function () {
    $user = User::factory()->create(['role' => 'end_user']);
    $customer = Customer::factory()->create(['name' => 'Customer To Delete']);

    $response = $this->actingAs($user)
        ->delete(route('customers.destroy', $customer));

    $response->assertRedirect(route('customers.index'))
        ->assertSessionHas('success', 'Customer deleted successfully.');

    $this->assertDatabaseMissing('customers', [
        'id' => $customer->id,
    ]);
});
