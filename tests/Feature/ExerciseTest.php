<?php

use App\Enums\ExerciseCategory;
use App\Models\Exercise;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('exercises index page is displayed', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.index'));

    $response->assertOk();
});

test('active exercises are shown by default', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $active = Exercise::factory()->create(['category' => ExerciseCategory::BackSpin, 'difficulty' => 2]);
    Exercise::factory()->archived()->create(['category' => ExerciseCategory::TopSpin, 'difficulty' => 3]);

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('exercises/index')
        ->has('exercises', 1)
        ->where('exercises.0.name', 'back-spin-2-1')
        ->where('isShowingArchived', false)
    );
});

test('archived exercises are shown when filtered', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    Exercise::factory()->create(['category' => ExerciseCategory::BackSpin, 'difficulty' => 2]);
    Exercise::factory()->archived()->create(['category' => ExerciseCategory::TopSpin, 'difficulty' => 3]);

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.index', ['archived' => '1']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('exercises/index')
        ->has('exercises', 1)
        ->where('exercises.0.name', 'top-spin-3-1')
        ->where('isShowingArchived', true)
    );
});

test('categories are provided to index page', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('categories', count(ExerciseCategory::cases()))
    );
});

test('create exercise page is displayed', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('exercises/create')
        ->has('categories', count(ExerciseCategory::cases()))
    );
});

test('teacher can create an exercise', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'category' => 'stop_shot',
            'image' => UploadedFile::fake()->image('exercise.jpg'),
            'description' => 'Practice stop shots from various positions.',
            'difficulty' => 3,
            'notes' => 'Use center cue ball hit.',
        ]);

    $response->assertSessionHasNoErrors();

    $exercise = Exercise::first();
    expect($exercise)->not->toBeNull();
    expect($exercise->name)->toBe('stop-shot-3-1');
    expect($exercise->category)->toBe(ExerciseCategory::StopShot);
    expect($exercise->description)->toBe('Practice stop shots from various positions.');
    expect($exercise->difficulty)->toBe(3);
    expect($exercise->notes)->toBe('Use center cue ball hit.');
    expect($exercise->is_active)->toBeTrue();
    Storage::disk('public')->assertExists($exercise->image_path);

    $response->assertRedirect(route('exercises.show', $exercise));
});

test('image is required when creating an exercise', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'category' => 'stop_shot',
            'difficulty' => 3,
        ]);

    $response->assertSessionHasErrors('image');
});

test('category is required when creating an exercise', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'image' => UploadedFile::fake()->image('exercise.jpg'),
            'difficulty' => 3,
        ]);

    $response->assertSessionHasErrors('category');
});

test('difficulty is required when creating an exercise', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'category' => 'stop_shot',
            'image' => UploadedFile::fake()->image('exercise.jpg'),
        ]);

    $response->assertSessionHasErrors('difficulty');
});

test('category must be a valid enum value', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $response = $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'category' => 'invalid_category',
            'image' => UploadedFile::fake()->image('exercise.jpg'),
            'difficulty' => 3,
        ]);

    $response->assertSessionHasErrors('category');
});

test('difficulty must be between 1 and 5', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();

    $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'category' => 'stop_shot',
            'image' => UploadedFile::fake()->image('exercise.jpg'),
            'difficulty' => 0,
        ])
        ->assertSessionHasErrors('difficulty');

    $this
        ->actingAs($user)
        ->post(route('exercises.store'), [
            'category' => 'stop_shot',
            'image' => UploadedFile::fake()->image('exercise.jpg'),
            'difficulty' => 6,
        ])
        ->assertSessionHasErrors('difficulty');
});

test('exercise show page is displayed', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.show', $exercise));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('exercises/show')
        ->where('exercise.id', $exercise->id)
    );
});

test('edit exercise page is displayed', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('exercises.edit', $exercise));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('exercises/edit')
        ->where('exercise.id', $exercise->id)
        ->has('categories', count(ExerciseCategory::cases()))
    );
});

test('teacher can update an exercise', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('exercises.update', $exercise), [
            'category' => 'back_spin',
            'image' => UploadedFile::fake()->image('updated.jpg'),
            'description' => 'Updated description.',
            'difficulty' => 5,
            'notes' => 'Updated notes.',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('exercises.show', $exercise));

    $exercise->refresh();
    expect($exercise->category)->toBe(ExerciseCategory::BackSpin);
    expect($exercise->description)->toBe('Updated description.');
    expect($exercise->difficulty)->toBe(5);
    expect($exercise->notes)->toBe('Updated notes.');
    Storage::disk('public')->assertExists($exercise->image_path);
});

test('teacher can update an exercise without changing image', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->create(['image_path' => 'exercises/original.jpg']);

    $response = $this
        ->actingAs($user)
        ->put(route('exercises.update', $exercise), [
            'category' => 'back_spin',
            'difficulty' => 4,
        ]);

    $response->assertSessionHasNoErrors();

    $exercise->refresh();
    expect($exercise->image_path)->toBe('exercises/original.jpg');
});

test('exercise name is dynamically generated', function () {
    $first = Exercise::factory()->create([
        'category' => ExerciseCategory::BasicPotting,
        'difficulty' => 3,
    ]);
    $second = Exercise::factory()->create([
        'category' => ExerciseCategory::BasicPotting,
        'difficulty' => 3,
    ]);
    $different = Exercise::factory()->create([
        'category' => ExerciseCategory::BasicPotting,
        'difficulty' => 4,
    ]);

    expect($first->name)->toBe('basic-potting-3-1');
    expect($second->name)->toBe('basic-potting-3-2');
    expect($different->name)->toBe('basic-potting-4-1');
});

test('teacher can archive an exercise', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('exercises.archive', $exercise));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('exercises.index'));

    expect($exercise->refresh()->is_active)->toBeFalse();
});

test('teacher can restore an archived exercise', function () {
    $user = User::factory()->create();
    School::factory()->forUser($user)->create();
    $exercise = Exercise::factory()->archived()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('exercises.restore', $exercise));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('exercises.show', $exercise));

    expect($exercise->refresh()->is_active)->toBeTrue();
});

test('unauthenticated users cannot access exercises', function () {
    $this->get(route('exercises.index'))->assertRedirect(route('login'));
    $this->get(route('exercises.create'))->assertRedirect(route('login'));
    $this->post(route('exercises.store'))->assertRedirect(route('login'));
});
