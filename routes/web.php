<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\PupilController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Middleware\EnsureUserHasSchool;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('schools/onboarding', [SchoolController::class, 'onboarding'])->name('schools.onboarding');
    Route::post('schools', [SchoolController::class, 'store'])->name('schools.store');

    Route::middleware(EnsureUserHasSchool::class)->group(function () {
        Route::inertia('dashboard', 'dashboard')->name('dashboard');

        Route::get('schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::get('schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
        Route::put('schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::post('schools/{school}/switch', [SchoolController::class, 'switch'])->name('schools.switch');

        Route::resource('exercises', ExerciseController::class)->except(['destroy']);
        Route::patch('exercises/{exercise}/archive', [ExerciseController::class, 'archive'])->name('exercises.archive');
        Route::patch('exercises/{exercise}/restore', [ExerciseController::class, 'restore'])->name('exercises.restore');

        Route::scopeBindings()->group(function () {
            Route::resource('schools.pupils', PupilController::class)->except(['destroy']);
            Route::patch('schools/{school}/pupils/{pupil}/archive', [PupilController::class, 'archive'])->name('schools.pupils.archive');
            Route::patch('schools/{school}/pupils/{pupil}/restore', [PupilController::class, 'restore'])->name('schools.pupils.restore');

            Route::resource('schools.sessions', TrainingSessionController::class)->only(['index', 'create', 'store', 'show'])->parameters(['sessions' => 'session']);
            Route::patch('schools/{school}/sessions/{session}/archive', [TrainingSessionController::class, 'archive'])->name('schools.sessions.archive');
            Route::patch('schools/{school}/sessions/{session}/restore', [TrainingSessionController::class, 'restore'])->name('schools.sessions.restore');
            Route::get('schools/{school}/sessions/{session}/exercises', [TrainingSessionController::class, 'exercisesPicker'])->name('schools.sessions.exercises');
            Route::post('schools/{school}/sessions/{session}/exercises', [TrainingSessionController::class, 'syncExercises'])->name('schools.sessions.exercises.sync');
            Route::delete('schools/{school}/sessions/{session}/exercises/{exercise}', [TrainingSessionController::class, 'detachExercise'])->name('schools.sessions.exercises.detach');
            Route::post('schools/{school}/sessions/{session}/pupils', [TrainingSessionController::class, 'addPupil'])->name('schools.sessions.pupils.add');
            Route::delete('schools/{school}/sessions/{session}/pupils/{pupil}', [TrainingSessionController::class, 'removePupil'])->name('schools.sessions.pupils.remove')->withoutScopedBindings();
            Route::patch('schools/{school}/sessions/{session}/assignments/{assignment}', [TrainingSessionController::class, 'updateAssignment'])->name('schools.sessions.assignments.update')->withoutScopedBindings();
        });
    });
});

require __DIR__.'/settings.php';
