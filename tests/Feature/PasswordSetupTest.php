<?php

use App\Models\School;
use App\Models\User;

test('first-login user is redirected to password setup', function () {
    $user = User::factory()->neverConnected()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertRedirect(route('password.setup'));
});

test('first-login user can view password setup page', function () {
    $user = User::factory()->neverConnected()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('password.setup'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('auth/setup-password')
        ->has('passwordRules')
    );
});

test('first-login user can set their password', function () {
    $user = User::factory()->neverConnected()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('password.setup.update'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->last_connected_at)->not->toBeNull();
});

test('password setup requires confirmation', function () {
    $user = User::factory()->neverConnected()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('password.setup.update'), [
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertSessionHasErrors('password');
});

test('connected user is redirected away from password setup', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('password.setup'));

    $response->assertRedirect(route('dashboard'));
});

test('connected user is not redirected to password setup', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('first-login user can still logout', function () {
    $user = User::factory()->neverConnected()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('logout'));

    $this->assertGuest();
});
