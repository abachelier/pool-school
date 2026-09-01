<?php

use App\Enums\SchoolRole;
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
    expect($session->is_archived)->toBeFalse();
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
        ->has('pupilRows')
        ->has('availablePupils')
    );
});

test('session show page displays pupil rows with assignments keyed by exercise', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $session->exercises()->attach($exercise->id);
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
        'score' => 8,
        'max_score' => 10,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertInertia(fn ($page) => $page
        ->has('pupilRows', 1)
        ->where('pupilRows.0.pupil.id', $pupil->id)
        ->where('pupilRows.0.pupil.name', $pupil->name)
        ->where("pupilRows.0.assignments.{$exercise->id}.score", 8)
        ->where("pupilRows.0.assignments.{$exercise->id}.max_score", 10)
    );
});

test('teacher can archive a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.sessions.archive', [$school, $session]));

    $session->refresh();
    expect($session->is_archived)->toBeTrue();

    $response->assertRedirect(route('schools.sessions.index', $school));
});

test('teacher can restore an archived session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->archived()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.sessions.restore', [$school, $session]));

    $session->refresh();
    expect($session->is_archived)->toBeFalse();

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
});

test('sessions index filters by archived status', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    TrainingSession::factory()->for($school)->create();
    TrainingSession::factory()->for($school)->archived()->create();

    $this->actingAs($user)
        ->get(route('schools.sessions.index', $school))
        ->assertInertia(fn ($page) => $page
            ->has('sessions', 1)
            ->where('isShowingArchived', false)
        );

    $this->actingAs($user)
        ->get(route('schools.sessions.index', [$school, 'archived' => 1]))
        ->assertInertia(fn ($page) => $page
            ->has('sessions', 1)
            ->where('isShowingArchived', true)
        );
});

test('non-member cannot access session pages', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create(); // user needs a school to pass middleware
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();

    $this->actingAs($user)->get(route('schools.sessions.index', $school))->assertForbidden();
    $this->actingAs($user)->get(route('schools.sessions.create', $school))->assertForbidden();
    $this->actingAs($user)->get(route('schools.sessions.show', [$school, $session]))->assertForbidden();
});

test('non-member cannot create sessions', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create(); // user needs a school to pass middleware
    $school = School::factory()->create();
    $pupil = Pupil::factory()->for($school)->create();

    $this->actingAs($user)
        ->post(route('schools.sessions.store', $school), [
            'date' => '2026-03-09',
            'pupil_ids' => [$pupil->id],
        ])
        ->assertForbidden();
});

test('non-member cannot archive or restore sessions', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create(); // user needs a school to pass middleware
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();

    $this->actingAs($user)
        ->patch(route('schools.sessions.archive', [$school, $session]))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('schools.sessions.restore', [$school, $session]))
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
    );
});

test('exercises picker excludes already attached exercises', function () {
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
        ->has('exercises', 1)
        ->where('exercises.0.id', $exercises[1]->id)
    );
});

test('teacher can add exercises to a session', function () {
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

test('adding exercises preserves previously attached exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(3)->create();
    $session->exercises()->attach([$exercises[0]->id, $exercises[1]->id]);

    $this->actingAs($user)
        ->post(route('schools.sessions.exercises.sync', [$school, $session]), [
            'exercise_ids' => [$exercises[2]->id],
        ]);

    expect($session->exercises()->count())->toBe(3);
    expect($session->exercises()->pluck('exercises.id')->sort()->values()->toArray())
        ->toBe([$exercises[0]->id, $exercises[1]->id, $exercises[2]->id]);
});

test('adding with empty array preserves existing exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(2)->create();
    $session->exercises()->attach($exercises->pluck('id'));

    $this->actingAs($user)
        ->post(route('schools.sessions.exercises.sync', [$school, $session]), [
            'exercise_ids' => [],
        ]);

    expect($session->exercises()->count())->toBe(2);
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

test('session show page includes session exercises with pupil assignment flag', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(2)->create();
    $session->exercises()->attach($exercises->pluck('id'));
    $pupil = Pupil::factory()->for($school)->create();
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercises[0]->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('sessionExercises', 2)
        ->where('sessionExercises.0.has_pupil_assignment', true)
        ->where('sessionExercises.1.has_pupil_assignment', false)
    );
});

test('teacher can detach an exercise from a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(2)->create();
    $session->exercises()->attach($exercises->pluck('id'));

    $response = $this
        ->actingAs($user)
        ->delete(route('schools.sessions.exercises.detach', [$school, $session, $exercises[0]]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
    expect($session->exercises()->count())->toBe(1);
    expect($session->exercises()->first()->id)->toBe($exercises[1]->id);
});

test('teacher cannot detach an exercise that has pupil assignments', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $session->exercises()->attach($exercise->id);
    $pupil = Pupil::factory()->for($school)->create();
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('schools.sessions.exercises.detach', [$school, $session, $exercise]));

    $response->assertSessionHasErrors('exercise');
    expect($session->exercises()->count())->toBe(1);
});

test('non-member cannot detach an exercise from a session', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $session->exercises()->attach($exercise->id);

    $this->actingAs($user)
        ->delete(route('schools.sessions.exercises.detach', [$school, $session, $exercise]))
        ->assertForbidden();

    expect($session->exercises()->count())->toBe(1);
});

test('historical results remain after pupil is archived', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $session->exercises()->attach($exercise->id);
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
        'score' => 8,
        'max_score' => 10,
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
        ->has('pupilRows', 1)
        ->where("pupilRows.0.assignments.{$exercise->id}.score", 8)
        ->where("pupilRows.0.assignments.{$exercise->id}.max_score", 10)
    );
});

test('teacher can add a pupil to a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(2)->create();
    $session->exercises()->attach($exercises->pluck('id'));

    $response = $this
        ->actingAs($user)
        ->post(route('schools.sessions.pupils.add', [$school, $session]), [
            'pupil_id' => $pupil->id,
        ]);

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
    expect($session->exerciseAssignments()->where('pupil_id', $pupil->id)->count())->toBe(2);
});

test('adding a pupil creates assignments for all session exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercises = Exercise::factory()->count(3)->create();
    $session->exercises()->attach($exercises->pluck('id'));

    $this->actingAs($user)
        ->post(route('schools.sessions.pupils.add', [$school, $session]), [
            'pupil_id' => $pupil->id,
        ]);

    $assignedExerciseIds = $session->exerciseAssignments()
        ->where('pupil_id', $pupil->id)
        ->pluck('exercise_id')
        ->sort()
        ->values();

    expect($assignedExerciseIds->toArray())->toBe($exercises->pluck('id')->sort()->values()->toArray());
});

test('adding a pupil inherits default max score from exercises', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exerciseWithDefault = Exercise::factory()->create(['default_max_score' => 20]);
    $exerciseWithoutDefault = Exercise::factory()->create(['default_max_score' => null]);
    $session->exercises()->attach([$exerciseWithDefault->id, $exerciseWithoutDefault->id]);

    $this->actingAs($user)
        ->post(route('schools.sessions.pupils.add', [$school, $session]), [
            'pupil_id' => $pupil->id,
        ]);

    $assignmentWith = $session->exerciseAssignments()
        ->where('pupil_id', $pupil->id)
        ->where('exercise_id', $exerciseWithDefault->id)
        ->first();
    $assignmentWithout = $session->exerciseAssignments()
        ->where('pupil_id', $pupil->id)
        ->where('exercise_id', $exerciseWithoutDefault->id)
        ->first();

    expect($assignmentWith->max_score)->toBe(20);
    expect($assignmentWithout->max_score)->toBeNull();
});

test('teacher can remove a pupil from a session', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $session->exercises()->attach($exercise->id);
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('schools.sessions.pupils.remove', [$school, $session, $pupil]));

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
    expect($session->exerciseAssignments()->where('pupil_id', $pupil->id)->count())->toBe(0);
});

test('teacher can update an assignment result', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $assignment = ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.sessions.assignments.update', [$school, $session, $assignment]), [
            'score' => 9,
            'max_score' => 10,
        ]);

    $response->assertRedirect(route('schools.sessions.show', [$school, $session]));
    $assignment->refresh();
    expect($assignment->score)->toBe(9);
    expect($assignment->max_score)->toBe(10);
});

test('assignment result rejects non-numeric values', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();
    $exercise = Exercise::factory()->create();
    $assignment = ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $pupil->id,
        'exercise_id' => $exercise->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('schools.sessions.assignments.update', [$school, $session, $assignment]), [
            'score' => 'abc',
            'max_score' => 'xyz',
        ]);

    $response->assertSessionHasErrors(['score', 'max_score']);
});

test('non-member cannot add or remove pupils from a session', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $school = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($school)->create();

    $this->actingAs($user)
        ->post(route('schools.sessions.pupils.add', [$school, $session]), [
            'pupil_id' => $pupil->id,
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('schools.sessions.pupils.remove', [$school, $session, $pupil]))
        ->assertForbidden();
});

test('available pupils excludes already assigned pupils', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $session = TrainingSession::factory()->for($school)->create();
    $assignedPupil = Pupil::factory()->for($school)->create(['name' => 'Alice']);
    $availablePupil = Pupil::factory()->for($school)->create(['name' => 'Bob']);
    $exercise = Exercise::factory()->create();
    $session->exercises()->attach($exercise->id);
    ExerciseAssignment::factory()->create([
        'session_id' => $session->id,
        'pupil_id' => $assignedPupil->id,
        'exercise_id' => $exercise->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('schools.sessions.show', [$school, $session]));

    $response->assertInertia(fn ($page) => $page
        ->has('availablePupils', 1)
        ->where('availablePupils.0.id', $availablePupil->id)
    );
});

test('cannot add a pupil from another school', function () {
    $user = User::factory()->create();
    $school = School::factory()->forUser($user)->create();
    $otherSchool = School::factory()->create();
    $session = TrainingSession::factory()->for($school)->create();
    $pupil = Pupil::factory()->for($otherSchool)->create();

    $this->actingAs($user)
        ->post(route('schools.sessions.pupils.add', [$school, $session]), [
            'pupil_id' => $pupil->id,
        ])
        ->assertStatus(422);
});
