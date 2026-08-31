<?php

use App\Models\Pupil;
use App\Models\School;
use App\Models\User;

test('pupils index page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.index', $school));

    $response->assertOk();
});

test('active pupils are shown by default', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    Pupil::factory()->for($school)->create(['name' => 'Active Pupil']);
    Pupil::factory()->for($school)->archived()->create(['name' => 'Archived Pupil']);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.index', $school));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pupils/index')
        ->has('pupils', 1)
        ->where('pupils.0.name', 'Active Pupil')
        ->where('isShowingArchived', false)
    );
});

test('archived pupils are shown when filtered', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    Pupil::factory()->for($school)->create(['name' => 'Active Pupil']);
    Pupil::factory()->for($school)->archived()->create(['name' => 'Archived Pupil']);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.index', [$school, 'archived' => '1']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pupils/index')
        ->has('pupils', 1)
        ->where('pupils.0.name', 'Archived Pupil')
        ->where('isShowingArchived', true)
    );
});

test('create pupil page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.create', $school));

    $response->assertOk();
});

test('teacher can create a pupil', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.pupils.store', $school), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'notes' => 'Good swimmer',
        ]);

    $response->assertSessionHasNoErrors();

    $pupil = Pupil::first();
    expect($pupil)->not->toBeNull();
    expect($pupil->name)->toBe('John Doe');
    expect($pupil->email)->toBe('john@example.com');
    expect($pupil->phone)->toBe('555-1234');
    expect($pupil->notes)->toBe('Good swimmer');
    expect($pupil->is_active)->toBeTrue();
    expect($pupil->school_id)->toBe($school->id);

    $response->assertRedirect(route('schools.pupils.show', [$school, $pupil]));
});

test('teacher can create a pupil with only required fields', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.pupils.store', $school), [
            'name' => 'Jane Doe',
        ]);

    $response->assertSessionHasNoErrors();

    $pupil = Pupil::first();
    expect($pupil->name)->toBe('Jane Doe');
    expect($pupil->email)->toBeNull();
    expect($pupil->phone)->toBeNull();
    expect($pupil->notes)->toBeNull();
});

test('name is required when creating a pupil', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.pupils.store', $school), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors('name');
});

test('pupil show page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.show', [$school, $pupil]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pupils/show')
        ->where('pupil.id', $pupil->id)
    );
});

test('teacher cannot view another teachers pupil', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $otherUser = User::factory()->create();
    $otherSchool = School::factory()->forUser($otherUser)->create();
    $pupil = Pupil::factory()->for($otherSchool)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.show', [$otherSchool, $pupil]));

    $response->assertForbidden();
});

test('edit pupil page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.edit', [$school, $pupil]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('pupils/edit')
        ->where('pupil.id', $pupil->id)
    );
});

test('teacher can update a pupil', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.pupils.update', [$school, $pupil]), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '555-9999',
            'notes' => 'Updated notes',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.pupils.show', [$school, $pupil]));

    $pupil->refresh();
    expect($pupil->name)->toBe('Updated Name');
    expect($pupil->email)->toBe('updated@example.com');
    expect($pupil->phone)->toBe('555-9999');
    expect($pupil->notes)->toBe('Updated notes');
});

test('teacher cannot update another teachers pupil', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $otherUser = User::factory()->create();
    $otherSchool = School::factory()->forUser($otherUser)->create();
    $pupil = Pupil::factory()->for($otherSchool)->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.pupils.update', [$otherSchool, $pupil]), [
            'name' => 'Hacked',
        ]);

    $response->assertForbidden();
});

test('teacher can archive a pupil', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.pupils.archive', [$school, $pupil]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.pupils.index', $school));

    expect($pupil->refresh()->is_active)->toBeFalse();
});

test('teacher cannot archive another teachers pupil', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $otherUser = User::factory()->create();
    $otherSchool = School::factory()->forUser($otherUser)->create();
    $pupil = Pupil::factory()->for($otherSchool)->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.pupils.archive', [$otherSchool, $pupil]));

    $response->assertForbidden();
    expect($pupil->refresh()->is_active)->toBeTrue();
});

test('teacher can restore an archived pupil', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->archived()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.pupils.restore', [$school, $pupil]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.pupils.show', [$school, $pupil]));

    expect($pupil->refresh()->is_active)->toBeTrue();
});

test('teacher cannot restore another teachers pupil', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $otherUser = User::factory()->create();
    $otherSchool = School::factory()->forUser($otherUser)->create();
    $pupil = Pupil::factory()->for($otherSchool)->archived()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.pupils.restore', [$otherSchool, $pupil]));

    $response->assertForbidden();
    expect($pupil->refresh()->is_active)->toBeFalse();
});

test('unauthenticated users cannot access pupils', function () {
    $school = School::factory()->create();

    $this->get(route('schools.pupils.index', $school))->assertRedirect(route('login'));
    $this->get(route('schools.pupils.create', $school))->assertRedirect(route('login'));
    $this->post(route('schools.pupils.store', $school))->assertRedirect(route('login'));
});

test('pupil must belong to the school in the route', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $otherSchool = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($otherSchool)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.pupils.show', [$school, $pupil]));

    $response->assertNotFound();
});
