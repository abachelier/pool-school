<?php

namespace App\Http\Controllers;

use App\Enums\SessionStatus;
use App\Http\Requests\Sessions\StoreSessionRequest;
use App\Http\Requests\Sessions\UpdateSessionRequest;
use App\Models\Exercise;
use App\Models\School;
use App\Models\TrainingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingSessionController extends Controller
{
    /**
     * Display a listing of the school's training sessions.
     */
    public function index(Request $request, School $school): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        $sessions = $school->sessions()
            ->with('pupils')
            ->latest('date')
            ->get();

        return Inertia::render('sessions/index', [
            'school' => $school,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Show the form for creating a new training session.
     */
    public function create(Request $request, School $school): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        return Inertia::render('sessions/create', [
            'school' => $school,
            'pupils' => $school->pupils()->active()->orderBy('name')->get(),
            'exercises' => Exercise::active()->orderBy('category')->orderBy('difficulty')->get(),
        ]);
    }

    /**
     * Store a newly created training session.
     */
    public function store(StoreSessionRequest $request, School $school): RedirectResponse
    {
        $validated = $request->validated();

        $session = $school->sessions()->create([
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
            'status' => SessionStatus::Planned,
        ]);

        // Create exercise assignments if provided
        if (! empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignment) {
                $session->exerciseAssignments()->create([
                    'pupil_id' => $assignment['pupil_id'],
                    'exercise_id' => $assignment['exercise_id'],
                ]);
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session created.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Display the specified training session.
     */
    public function show(Request $request, School $school, TrainingSession $session): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->load(['exerciseAssignments.pupil', 'exerciseAssignments.exercise']);

        // Group assignments by pupil for the view
        $pupilAssignments = $session->exerciseAssignments
            ->groupBy('pupil_id')
            ->map(function ($assignments) {
                return [
                    'pupil' => $assignments->first()->pupil,
                    'assignments' => $assignments->map(fn ($a) => [
                        'id' => $a->id,
                        'exercise' => $a->exercise,
                        'result_value' => $a->result_value,
                        'notes' => $a->notes,
                        'is_completed' => $a->is_completed,
                    ])->values(),
                ];
            })
            ->values();

        return Inertia::render('sessions/show', [
            'school' => $school,
            'session' => $session,
            'pupilAssignments' => $pupilAssignments,
            'sessionExercises' => $session->exercises,
        ]);
    }

    /**
     * Show the form for editing the specified training session.
     */
    public function edit(Request $request, School $school, TrainingSession $session): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->load(['exerciseAssignments.pupil', 'exerciseAssignments.exercise']);

        return Inertia::render('sessions/edit', [
            'school' => $school,
            'session' => $session,
            'pupils' => $school->pupils()->active()->orderBy('name')->get(),
            'exercises' => Exercise::active()->orderBy('category')->orderBy('difficulty')->get(),
            'existingAssignments' => $session->exerciseAssignments,
        ]);
    }

    /**
     * Update the specified training session.
     */
    public function update(UpdateSessionRequest $request, School $school, TrainingSession $session): RedirectResponse
    {
        $validated = $request->validated();

        $session->update([
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync exercise assignments
        $session->exerciseAssignments()->delete();

        if (! empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignment) {
                $session->exerciseAssignments()->create([
                    'pupil_id' => $assignment['pupil_id'],
                    'exercise_id' => $assignment['exercise_id'],
                    'result_value' => $assignment['result_value'] ?? null,
                    'notes' => $assignment['notes'] ?? null,
                    'is_completed' => $assignment['is_completed'] ?? false,
                ]);
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session updated.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Mark the specified training session as completed.
     */
    public function complete(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->update(['status' => SessionStatus::Completed]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session completed.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Mark the specified training session as in progress.
     */
    public function start(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->update(['status' => SessionStatus::InProgress]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session started.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Display the exercise picker for a session.
     */
    public function exercisesPicker(Request $request, School $school, TrainingSession $session): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        $exercises = Exercise::active()->orderBy('category')->orderBy('difficulty')->get();
        $selectedExerciseIds = $session->exercises()->pluck('exercises.id')->toArray();

        return Inertia::render('sessions/exercises', [
            'school' => $school,
            'session' => $session,
            'exercises' => $exercises,
            'selectedExerciseIds' => $selectedExerciseIds,
        ]);
    }

    /**
     * Sync exercises for a session.
     */
    public function syncExercises(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $validated = $request->validate([
            'exercise_ids' => ['nullable', 'array'],
            'exercise_ids.*' => ['integer', 'exists:exercises,id'],
        ]);

        $session->exercises()->sync($validated['exercise_ids'] ?? []);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session exercises updated.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }
}
