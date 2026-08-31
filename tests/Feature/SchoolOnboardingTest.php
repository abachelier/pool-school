<?php

use App\Models\School;
use App\Models\User;

test('onboarding page is displayed for users without schools', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.onboarding'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('schools/onboarding'));
});

test('users without schools are redirected to onboarding from protected routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('schools.onboarding'));
});

test('users with schools can access protected routes', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('users without schools can still store a school', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.store'), [
            'name' => 'First School',
        ]);

    $response->assertSessionHasNoErrors();

    $school = School::first();
    expect($school)->not->toBeNull();
    expect($school->name)->toBe('First School');
    expect($school->hasAdmin($user))->toBeTrue();

    $response->assertRedirect(route('schools.show', $school));
});

test('creating a school sets it as current in session', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('schools.store'), [
            'name' => 'My School',
        ]);

    $school = School::first();

    expect(session('current_school_id'))->toBe($school->id);
});

test('unauthenticated users cannot access onboarding', function () {
    $this->get(route('schools.onboarding'))
        ->assertRedirect(route('login'));
});

test('member can switch to a school they belong to', function () {
    $user = User::factory()->create();
    $school1 = School::factory()->forUser($user)->create();
    $school2 = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.switch', $school2));

    $response->assertRedirect(route('schools.show', $school2));
    expect(session('current_school_id'))->toBe($school2->id);
});

test('non-member cannot switch to a school', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $otherSchool = School::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.switch', $otherSchool));

    $response->assertForbidden();
});

test('schools are shared in inertia props', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('auth.schools.0.id', $school->id)
        ->where('auth.schools.0.name', $school->name)
        ->where('auth.currentSchoolId', $school->id)
    );
});
