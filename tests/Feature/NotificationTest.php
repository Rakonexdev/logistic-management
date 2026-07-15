<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to login from notifications index', function () {
    $this->get(route('notifications.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can access notifications index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Notification Center');
});

test('authenticated users can fetch notifications via API', function () {
    $user = User::factory()->create();

    Notification::create([
        'title' => 'Test Title',
        'message' => 'Test Message',
        'type' => 'info',
        'module' => 'system',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('notifications.index'));

    $response->assertSuccessful();
    $response->assertJsonPath('data.0.title', 'Test Title');
});

test('authenticated users can fetch unread notifications count', function () {
    $user = User::factory()->create();

    Notification::create([
        'title' => 'Title 1',
        'message' => 'Msg 1',
        'type' => 'info',
        'module' => 'system',
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    Notification::create([
        'title' => 'Title 2',
        'message' => 'Msg 2',
        'type' => 'success',
        'module' => 'sales_orders',
        'user_id' => $user->id,
        'is_read' => true,
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('notifications.unread-count'));

    $response->assertSuccessful();
    $response->assertJsonFragment(['unread_count' => 1]);
});

test('authenticated users can mark a notification as read', function () {
    $user = User::factory()->create();
    $n = Notification::create([
        'title' => 'Unread title',
        'message' => 'Unread message',
        'type' => 'info',
        'module' => 'system',
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('notifications.read', ['id' => $n->id]));

    $response->assertSuccessful();
    $this->assertTrue($n->fresh()->is_read);
});

test('authenticated users can mark all notifications as read', function () {
    $user = User::factory()->create();
    $n1 = Notification::create([
        'title' => 'Title 1',
        'message' => 'Msg 1',
        'type' => 'info',
        'module' => 'system',
        'user_id' => $user->id,
        'is_read' => false,
    ]);
    $n2 = Notification::create([
        'title' => 'Title 2',
        'message' => 'Msg 2',
        'type' => 'info',
        'module' => 'system',
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('notifications.read-all'));

    $response->assertSuccessful();
    $this->assertTrue($n1->fresh()->is_read);
    $this->assertTrue($n2->fresh()->is_read);
});

test('authenticated users can delete a notification', function () {
    $user = User::factory()->create();
    $n = Notification::create([
        'title' => 'Title',
        'message' => 'Msg',
        'type' => 'info',
        'module' => 'system',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->deleteJson(route('notifications.destroy', ['id' => $n->id]));

    $response->assertSuccessful();
    $this->assertDatabaseMissing('notifications', ['id' => $n->id]);
});
