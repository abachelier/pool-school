<?php

use App\Enums\SchoolRole;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;

test('user can accept an invitation and join the school', function () {
    $admin = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    School::factory()->forUser($invitee)->create();

    $invitation = SchoolInvitation::factory()->create([
        'school_id' => $school->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'invited_by' => $admin->id,
    ]);

    $response = $this
        ->actingAs($invitee)
        ->post(route('invitations.accept', $invitation));

    $response->assertRedirect(route('dashboard'));

    expect($school->hasMember($invitee))->toBeTrue();
    expect(SchoolInvitation::find($invitation->id))->toBeNull();
});

test('user can decline an invitation', function () {
    $admin = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    School::factory()->forUser($invitee)->create();

    $invitation = SchoolInvitation::factory()->create([
        'school_id' => $school->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'invited_by' => $admin->id,
    ]);

    $response = $this
        ->actingAs($invitee)
        ->post(route('invitations.decline', $invitation));

    $response->assertRedirect();

    expect($school->hasMember($invitee))->toBeFalse();
    expect(SchoolInvitation::find($invitation->id))->toBeNull();
});

test('user cannot accept another users invitation', function () {
    $admin = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    School::factory()->forUser($invitee)->create();
    $otherUser = User::factory()->create(['email' => 'other@example.com']);
    School::factory()->forUser($otherUser)->create();

    $invitation = SchoolInvitation::factory()->create([
        'school_id' => $school->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'invited_by' => $admin->id,
    ]);

    $response = $this
        ->actingAs($otherUser)
        ->post(route('invitations.accept', $invitation));

    $response->assertForbidden();

    expect(SchoolInvitation::find($invitation->id))->not->toBeNull();
});

test('accepting invitation sets the correct role', function () {
    $admin = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    School::factory()->forUser($invitee)->create();

    $invitation = SchoolInvitation::factory()->create([
        'school_id' => $school->id,
        'email' => 'invitee@example.com',
        'role' => 'admin',
        'invited_by' => $admin->id,
    ]);

    $this
        ->actingAs($invitee)
        ->post(route('invitations.accept', $invitation));

    expect($school->users()->where('users.id', $invitee->id)->first()->pivot->role)
        ->toBe(SchoolRole::Admin->value);
});

test('pending invitations are shared with authenticated user', function () {
    $admin = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);
    School::factory()->forUser($invitee)->create();

    SchoolInvitation::factory()->create([
        'school_id' => $school->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'invited_by' => $admin->id,
    ]);

    $response = $this
        ->actingAs($invitee)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('auth.pendingInvitations.0.school_name', $school->name)
    );
});

test('user without school sees invitations on onboarding page', function () {
    $admin = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    SchoolInvitation::factory()->create([
        'school_id' => $school->id,
        'email' => 'invitee@example.com',
        'role' => 'member',
        'invited_by' => $admin->id,
    ]);

    $response = $this
        ->actingAs($invitee)
        ->get(route('schools.onboarding'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('auth.pendingInvitations.0.school_name', $school->name)
    );
});
