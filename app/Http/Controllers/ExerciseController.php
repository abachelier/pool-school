<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exercises\StoreExerciseRequest;
use App\Http\Requests\Exercises\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    /**
     * Display a listing of exercises.
     */
    public function index(Request $request): Response
    {
        $query = Exercise::with('exerciseCategory');

        if ($request->boolean('archived')) {
            $query->archived();
        } else {
            $query->active();
        }

        return Inertia::render('exercises/index', [
            'exercises' => $query->orderBy('exercise_category_id')->orderBy('difficulty')->orderBy('id')->get(),
            'isShowingArchived' => $request->boolean('archived'),
            'categories' => ExerciseCategory::orderBy('name')->get()->map(fn (ExerciseCategory $c) => [
                'value' => $c->id,
                'label' => $c->name,
            ])->all(),
        ]);
    }

    /**
     * Show the form for creating a new exercise.
     */
    public function create(): Response
    {
        return Inertia::render('exercises/create', [
            'categories' => ExerciseCategory::orderBy('name')->get()->map(fn (ExerciseCategory $c) => [
                'value' => $c->id,
                'label' => $c->name,
            ])->all(),
        ]);
    }

    /**
     * Store a newly created exercise.
     */
    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = $request->file('image')->store('exercises', 'public');

        $exercise = Exercise::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise created.')]);

        return to_route('exercises.show', $exercise);
    }

    /**
     * Display the specified exercise.
     */
    public function show(Exercise $exercise): Response
    {
        return Inertia::render('exercises/show', [
            'exercise' => $exercise->load('exerciseCategory'),
        ]);
    }

    /**
     * Show the form for editing the specified exercise.
     */
    public function edit(Exercise $exercise): Response
    {
        return Inertia::render('exercises/edit', [
            'exercise' => $exercise->load('exerciseCategory'),
            'categories' => ExerciseCategory::orderBy('name')->get()->map(fn (ExerciseCategory $c) => [
                'value' => $c->id,
                'label' => $c->name,
            ])->all(),
        ]);
    }

    /**
     * Update the specified exercise.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($exercise->image_path);
            $data['image_path'] = $request->file('image')->store('exercises', 'public');
        }

        $exercise->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise updated.')]);

        return to_route('exercises.show', $exercise);
    }

    /**
     * Archive the specified exercise.
     */
    public function archive(Request $request, Exercise $exercise): RedirectResponse
    {
        $exercise->update(['is_active' => false]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise archived.')]);

        return to_route('exercises.index');
    }

    /**
     * Restore an archived exercise.
     */
    public function restore(Request $request, Exercise $exercise): RedirectResponse
    {
        $exercise->update(['is_active' => true]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise restored.')]);

        return to_route('exercises.show', $exercise);
    }
}
