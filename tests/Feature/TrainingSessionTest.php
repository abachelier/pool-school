<?php

use App\Enums\SchoolRole;
use App\Enums\SessionStatus;
use App\Models\Exercise;
use App\Models\ExerciseAssignment;
use App\Models\Pupil;
use App\Models\School;
use App\Models\TrainingSession;
use App\Models\User;

test('sessions index page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.index', $school));

    $response->assertOk();
});

test('sessions index shows school sessions', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    TrainingSession::factory()->for($school)->create(['date' => '2026-03-09']);
    TrainingSession::factory()->for($school)->create(['date' => '2026-03-16']);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.index', $school));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sessions/index')
        ->has('sessions', 2)
    );
});

test('sessions index does not show sessions from other schools', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $otherSchool = School::factory()->create();
    TrainingSession::factory()->for($school)->create();
    TrainingSession::factory()->for($otherSchool)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.index', $school));

    $response->assertInertia(fn ($page) => $page
        ->has('sessions', 1)
    );
});

test('create session page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.create', $school));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sessions/create')
        ->has('pupils')
        ->has('exercises')
    );
});

test('teacher can create a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.sessions.store', $school), [
            'date' => '2026-03-09',
            'notes' => 'Focus on potting drills',
            'pupil_ids' => [$pupil->id],
        ]);

    $response->assertSessionHasNoErrors();

    $session = TrainingSession::first();
    expect($session)->not->toBeNull();
    expect($session->date->toDateString())->toBe('2026-03-09');
    expect($session->notes)->toBe('Focus on potting drills');
    expect($session->status)->toBe(SessionStatus::Planned);
    expect($session->school_id)->toBe($school->id);

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
});

test('teacher can create a session with exercise assignments', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.sessions.store', $school), [
            'date' => '2026-03-09',
            'pupil_ids' => [$pupil->id],
            'assignments' => [
                ['pupil_id' => $pupil->id, 'exercise_id' => $exercise->id],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $session = TrainingSession::first();
    expect($session->exerciseAssignments)->toHaveCount(1);

    $assignment = $session->exerciseAssignments->first();
    expect($assignment->pupil_id)->toBe($pupil->id);
    expect($assignment->exercise_id)->toBe($exercise->id);
    expect($assignment->is_completed)->toBeFalse();
});

test('teacher can create a session with multiple pupils and different exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $pupilA = Pupil::factory()->for($school)->create(['name' => 'Alice']);
    $pupilB = Pupil::factory()->for($school)->create(['name' => 'Bob']);
    $exerciseA = Exercise::factory()->create();
    $exerciseB = Exercise::factory()->create();
    $exerciseC = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.sessions.store', $school), [
            'date' => '2026-03-09',
            'pupil_ids' => [$pupilA->id, $pupilB->id],
            'assignments' => [
                ['pupil_id' => $pupilA->id, 'exercise_id' => $exerciseA->id],
                ['pupil_id' => $pupilA->id, 'exercise_id' => $exerciseB->id],
                ['pupil_id' => $pupilB->id, 'exercise_id' => $exerciseA->id],
                ['pupil_id' => $pupilB->id, 'exercise_id' => $exerciseC->id],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $session = TrainingSession::first();
    expect($session->exerciseAssignments)->toHaveCount(4);
});

test('session creation requires date', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.sessions.store', $school), []);

    $response->assertSessionHasErrors(['date']);
    $response->assertSessionDoesntHaveErrors(['pupil_ids']);
});

test('session show page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sessions/show')
        ->has('session')
        ->has('pupilAssignments')
    );
});

test('session show page displays pupil assignments grouped by pupil', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertInertia(fn ($page) => $page
        ->has('pupilAssignments', 1)
        ->where('pupilAssignments.0.pupil.id', $pupil->id)
        ->has('pupilAssignments.0.assignments', 1)
    );
});

test('session edit page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.edit', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sessions/edit')
        ->has('session')
        ->has('pupils')
        ->has('exercises')
        ->has('existingAssignments')
    );
});

test('teacher can update a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create(['date' => '2026-03-09']);
    $pupil = Pupil::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.sessions.update', [$school, $session]), [
            'date' => '2026-03-16',
            'notes' => 'Updated notes',
            'pupil_ids' => [$pupil->id],
        ]);

    $response->assertSessionHasNoErrors();

    $session->refresh();
    expect($session->date->toDateString())->toBe('2026-03-16');
    expect($session->notes)->toBe('Updated notes');

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
});

test('teacher can update a session with exercise results', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('schools.sessions.update', [$school, $session]), [
            'date' => $session->date->toDateString(),
            'pupil_ids' => [$pupil->id],
            'assignments' => [
                [
                    'pupil_id' => $pupil->id,
                    'exercise_id' => $exercise->id,
                    'result_value' => '8/10',
                    'notes' => 'Great improvement',
                    'is_completed' => true,
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $assignment = ExerciseAssignment::first();
    expect($assignment->result_value)->toBe('8/10');
    expect($assignment->notes)->toBe('Great improvement');
    expect($assignment->is_completed)->toBeTrue();
});

test('teacher can mark a session as started', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create(['status' => SessionStatus::Planned]);

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.sessions.start', [$school, $session]));

    $session->refresh();
    expect($session->status)->toBe(SessionStatus::InProgress);

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
});

test('teacher can mark a session as completed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->inProgress()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.sessions.complete', [$school, $session]));

    $session->refresh();
    expect($session->status)->toBe(SessionStatus::Completed);

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
});

test('non-member cannot access session pages', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create(); // user needs a school to pass middleware
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();

    $this->actingAs($user)->get(route('schools.sessions.index', $school))->assertForbidden();
    $this->actingAs($user)->get(route('schools.sessions.create', $school))->assertForbidden();
    $this->actingAs($user)->get(route('schools.sessions.show', [$school, $session]))->assertForbidden();
    $this->actingAs($user)->get(route('schools.sessions.edit', [$school, $session]))->assertForbidden();
});

test('non-member cannot create or update sessions', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create(); // user needs a school to pass middleware
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $this->actingAs($user)
        ->post(route('schools.sessions.store', $school), [
            'date' => '2026-03-09',
            'pupil_ids' => [$pupil->id],
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('schools.sessions.update', [$school, $session]), [
            'date' => '2026-03-09',
            'pupil_ids' => [$pupil->id],
        ])
        ->assertForbidden();
});

test('non-member cannot change session status', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create(); // user needs a school to pass middleware
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();

    $this->actingAs($user)
        ->patch(route('schools.sessions.start', [$school, $session]))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('schools.sessions.complete', [$school, $session]))
        ->assertForbidden();
});

test('member can access and manage sessions', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $school = School::factory()->forUser($admin)->create();
    $school->users()->attach($member, ['role' => SchoolRole::Member]);
    $pupil = Pupil::factory()->for($school)->create();

    $this->actingAs($member)
        ->get(route('schools.sessions.index', $school))
        ->assertOk();

    $this->actingAs($member)
        ->post(route('schools.sessions.store', $school), [
            'date' => '2026-03-09',
            'pupil_ids' => [$pupil->id],
        ])
        ->assertSessionHasNoErrors();
});

test('unauthenticated user cannot access session pages', function () {
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();

    $this->get(route('schools.sessions.index', $school))->assertRedirect(route('login'));
    $this->get(route('schools.sessions.create', $school))->assertRedirect(route('login'));
    $this->get(route('schools.sessions.show', [$school, $session]))->assertRedirect(route('login'));
});

test('session scoped binding ensures session belongs to school', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $otherSchool = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($otherSchool)->create();

    $this->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]))
        ->assertNotFound();
});

test('exercises picker page is displayed', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    Exercise::factory()->count(3)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.exercises', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sessions/exercises')
        ->has('exercises', 3)
        ->has('selectedExerciseIds')
    );
});

test('exercises picker shows already selected exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(3)->create();
    $session->exercises()->attach([$exercises[0]->id, $exercises[2]->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.exercises', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('selectedExerciseIds', [$exercises[0]->id, $exercises[2]->id])
    );
});

test('teacher can sync exercises to a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(3)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('schools.sessions.exercises.sync', [$school, $session]), [
            'exercise_ids' => [$exercises[0]->id, $exercises[1]->id],
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
    expect($session->exercises()->count())->toBe(2);
    expect($session->exercises()->pluck('exercises.id')->sort()->values()->toArray())
        ->toBe([$exercises[0]->id, $exercises[1]->id]);
});

test('syncing exercises replaces previous selection', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(3)->create();
    $session->exercises()->attach([$exercises[0]->id, $exercises[1]->id]);

    $this->actingAs($user)
        ->post(route('schools.sessions.exercises.sync', [$school, $session]), [
            'exercise_ids' => [$exercises[2]->id],
        ]);

    expect($session->exercises()->count())->toBe(1);
    expect($session->exercises()->first()->id)->toBe($exercises[2]->id);
});

test('syncing with empty array removes all exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(2)->create();
    $session->exercises()->attach($exercises->pluck('id'));

    $this->actingAs($user)
        ->post(route('schools.sessions.exercises.sync', [$school, $session]), [
            'exercise_ids' => [],
        ]);

    expect($session->exercises()->count())->toBe(0);
});

test('non-member cannot access exercises picker', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();

    $this->actingAs($user)
        ->get(route('schools.sessions.exercises', [$school, $session]))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('schools.sessions.exercises.sync', [$school, $session]), [
            'exercise_ids' => [],
        ])
        ->assertForbidden();
});

test('session show page includes session exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(2)->create();
    $session->exercises()->attach($exercises->pluck('id'));

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('sessionExercises', 2)
    );
});

test('historical results remain after pupil is archived', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->completed()->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
        'result_value' => '8/10',
        'is_completed' => true,
    ]);

    // Archive the pupil
    $pupil->update(['is_active' => false]);

    // Results should still be visible on the session page
    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('pupilAssignments', 1)
        ->where('pupilAssignments.0.assignments.0.result_value', '8/10')
    );
});
