<?php

use App\Enums\SchoolRole;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('create school page is displayed', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.create'));

    $response->assertOk();
});

test('teacher can create a school', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.store'), [
            'name' => 'My Pool School',
            'description' => 'A great school for pool.',
        ]);

    $response->assertSessionHasNoErrors();

    $school = School::first();
    expect($school)->not->toBeNull();
    expect($school->name)->toBe('My Pool School');
    expect($school->description)->toBe('A great school for pool.');

    expect($school->hasAdmin($user))->toBeTrue();

    $response->assertRedirect(route('schools.show', $school));
});

test('teacher can create a school with only required fields', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.store'), [
            'name' => 'Minimal School',
        ]);

    $response->assertSessionHasNoErrors();

    $school = School::first();
    expect($school->name)->toBe('Minimal School');
    expect($school->description)->toBeNull();
});

test('name is required when creating a school', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.store'), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors('name');
});

test('school show page is displayed for admin', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.show', $school));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('schools/show')
        ->where('school.id', $school->id)
    );
});

test('member cannot view school settings', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $school->users()->attach($member, ['role' => SchoolRole::Member]);

    $response = $this
        ->actingAs($member)
        ->get(route('schools.show', $school));

    $response->assertForbidden();
});

test('non-member cannot view a school', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $school = School::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.show', $school));

    $response->assertForbidden();
});

test('admin can update a school', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.update', $school), [
            'name' => 'Updated School Name',
            'description' => 'Updated description.',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.show', $school));

    $school->refresh();
    expect($school->name)->toBe('Updated School Name');
    expect($school->description)->toBe('Updated description.');
});

test('member cannot update a school', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $school->users()->attach($member, ['role' => SchoolRole::Member]);

    $response = $this
        ->actingAs($member)
        ->put(route('schools.update', $school), [
            'name' => 'Hacked',
        ]);

    $response->assertForbidden();
});

test('non-member cannot update a school', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $school = School::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.update', $school), [
            'name' => 'Hacked',
        ]);

    $response->assertForbidden();
});

test('unauthenticated users cannot access schools', function () {
    $this->get(route('schools.create'))->assertRedirect(route('login'));
    $this->post(route('schools.store'))->assertRedirect(route('login'));
});

test('creating a school assigns user as admin', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post(route('schools.store'), [
            'name' => 'Test School',
        ]);

    $school = School::first();
    expect($school->users()->where('users.id', $user->id)->first()->pivot->role)->toBe(SchoolRole::Admin->value);
});

test('admin can upload a logo when updating a school', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.update', $school), [
            'name' => $school->name,
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.show', $school));

    $school->refresh();
    expect($school->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($school->logo_path);
});

test('uploading a new logo deletes the previous one', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create(['logo_path' => 'schools/old-logo.png']);
    Storage::disk('public')->put('schools/old-logo.png', 'old');

    $this
        ->actingAs($user)
        ->put(route('schools.update', $school), [
            'name' => $school->name,
            'logo' => UploadedFile::fake()->image('new-logo.png', 200, 200),
        ]);

    $school->refresh();
    Storage::disk('public')->assertMissing('schools/old-logo.png');
    Storage::disk('public')->assertExists($school->logo_path);
});

test('school can be updated without changing the logo', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create(['logo_path' => 'schools/existing.png']);

    $response = $this
        ->actingAs($user)
        ->put(route('schools.update', $school), [
            'name' => 'New Name',
        ]);

    $response->assertSessionHasNoErrors();

    $school->refresh();
    expect($school->name)->toBe('New Name');
    expect($school->logo_path)->toBe('schools/existing.png');
});

test('logo must be an image file', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.update', $school), [
            'name' => $school->name,
            'logo' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

    $response->assertSessionHasErrors('logo');
});
