<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sessions\StoreSessionRequest;
use App\Models\Exercise;
use App\Models\ExerciseAssignment;
use App\Models\Pupil;
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

        $isShowingArchived = $request->boolean('archived');

        $sessions = $school->sessions()
            ->with('pupils')
            ->where('is_archived', $isShowingArchived)
            ->latest('date')
            ->get();

        return Inertia::render('sessions/index', [
            'school' => $school,
            'sessions' => $sessions,
            'isShowingArchived' => $isShowingArchived,
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

        $assignedExerciseIds = $session->exerciseAssignments->pluck('exercise_id')->unique();

        $sessionExercises = $session->exercises->map(fn (Exercise $exercise) => [
            ...$exercise->toArray(),
            'has_pupil_assignment' => $assignedExerciseIds->contains($exercise->id),
        ]);

        // Build pupil rows with assignments keyed by exercise_id for the table
        $pupilRows = $session->exerciseAssignments
            ->groupBy('pupil_id')
            ->map(function ($assignments) {
                $pupil = $assignments->first()->pupil;

                return [
                    'pupil' => ['id' => $pupil->id, 'name' => $pupil->name],
                    'assignments' => $assignments->keyBy('exercise_id')->map(fn ($a) => [
                        'id' => $a->id,
                        'score' => $a->score,
                        'max_score' => $a->max_score,
                    ]),
                ];
            })
            ->values();

        $assignedPupilIds = $session->exerciseAssignments->pluck('pupil_id')->unique();

        $availablePupils = $school->pupils()
            ->active()
            ->whereNotIn('id', $assignedPupilIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('sessions/show', [
            'school' => $school,
            'session' => $session,
            'sessionExercises' => $sessionExercises,
            'pupilRows' => $pupilRows,
            'availablePupils' => $availablePupils,
        ]);
    }

    /**
     * Archive the specified training session.
     */
    public function archive(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->update(['is_archived' => true]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session archived.')]);

        return to_route('schools.sessions.index', $school);
    }

    /**
     * Restore an archived training session.
     */
    public function restore(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->update(['is_archived' => false]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session restored.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Display the exercise picker for a session.
     */
    public function exercisesPicker(Request $request, School $school, TrainingSession $session): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        $attachedIds = $session->exercises()->pluck('exercises.id');

        $exercises = Exercise::active()
            ->whereNotIn('id', $attachedIds)
            ->orderBy('category')
            ->orderBy('difficulty')
            ->get();

        return Inertia::render('sessions/exercises', [
            'school' => $school,
            'session' => $session,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Add exercises to a session.
     */
    public function syncExercises(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $validated = $request->validate([
            'exercise_ids' => ['nullable', 'array'],
            'exercise_ids.*' => ['integer', 'exists:exercises,id'],
        ]);

        $newIds = collect($validated['exercise_ids'] ?? [])
            ->diff($session->exercises()->pluck('exercises.id'))
            ->values()
            ->all();

        $session->exercises()->attach($newIds);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Session exercises updated.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Detach an exercise from a session.
     */
    public function detachExercise(Request $request, School $school, TrainingSession $session, Exercise $exercise): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $hasPupilAssignment = $session->exerciseAssignments()
            ->where('exercise_id', $exercise->id)
            ->exists();

        if ($hasPupilAssignment) {
            return back()->withErrors([
                'exercise' => __('Cannot remove an exercise that has pupil assignments.'),
            ]);
        }

        $session->exercises()->detach($exercise->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise removed from session.')]);

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Add a pupil to the session by creating assignments for all session exercises.
     */
    public function addPupil(Request $request, School $school, TrainingSession $session): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $validated = $request->validate([
            'pupil_id' => ['required', 'integer', 'exists:pupils,id'],
        ]);

        $pupil = Pupil::findOrFail($validated['pupil_id']);
        abort_unless($pupil->school_id === $school->id, 422);

        $exercises = $session->exercises()->get(['exercises.id', 'exercises.default_max_score']);

        foreach ($exercises as $exercise) {
            $session->exerciseAssignments()->firstOrCreate(
                [
                    'pupil_id' => $pupil->id,
                    'exercise_id' => $exercise->id,
                ],
                [
                    'max_score' => $exercise->default_max_score,
                ],
            );
        }

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Remove a pupil from the session by deleting all their assignments.
     */
    public function removePupil(Request $request, School $school, TrainingSession $session, Pupil $pupil): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $session->exerciseAssignments()
            ->where('pupil_id', $pupil->id)
            ->delete();

        return to_route('schools.sessions.show', [$school, $session]);
    }

    /**
     * Update an exercise assignment result.
     */
    public function updateAssignment(Request $request, School $school, TrainingSession $session, ExerciseAssignment $assignment): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);
        abort_unless($assignment->session_id === $session->id, 404);

        $validated = $request->validate([
            'score' => ['nullable', 'integer', 'min:0'],
            'max_score' => ['nullable', 'integer', 'min:1'],
        ]);

        $assignment->update([
            'score' => $validated['score'],
            'max_score' => $validated['max_score'],
        ]);

        return to_route('schools.sessions.show', [$school, $session]);
    }
}
