<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pupils\StorePupilRequest;
use App\Http\Requests\Pupils\UpdatePupilRequest;
use App\Models\Pupil;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PupilController extends Controller
{
    /**
     * Display a listing of the school's pupils.
     */
    public function index(Request $request, School $school): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        $query = $school->pupils();

        if ($request->boolean('archived')) {
            $query->archived();
        } else {
            $query->active();
        }

        return Inertia::render('pupils/index', [
            'school' => $school,
            'pupils' => $query->latest()->get(),
            'isShowingArchived' => $request->boolean('archived'),
        ]);
    }

    /**
     * Show the form for creating a new pupil.
     */
    public function create(Request $request, School $school): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        return Inertia::render('pupils/create', [
            'school' => $school,
        ]);
    }

    /**
     * Store a newly created pupil.
     */
    public function store(StorePupilRequest $request, School $school): RedirectResponse
    {
        $pupil = $school->pupils()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pupil created.')]);

        return to_route('schools.pupils.show', [$school, $pupil]);
    }

    /**
     * Display the specified pupil.
     */
    public function show(Request $request, School $school, Pupil $pupil): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        return Inertia::render('pupils/show', [
            'school' => $school,
            'pupil' => $pupil,
        ]);
    }

    /**
     * Show the form for editing the specified pupil.
     */
    public function edit(Request $request, School $school, Pupil $pupil): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        return Inertia::render('pupils/edit', [
            'school' => $school,
            'pupil' => $pupil,
        ]);
    }

    /**
     * Update the specified pupil.
     */
    public function update(UpdatePupilRequest $request, School $school, Pupil $pupil): RedirectResponse
    {
        $pupil->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pupil updated.')]);

        return to_route('schools.pupils.show', [$school, $pupil]);
    }

    /**
     * Archive the specified pupil.
     */
    public function archive(Request $request, School $school, Pupil $pupil): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $pupil->update(['is_active' => false]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pupil archived.')]);

        return to_route('schools.pupils.index', $school);
    }

    /**
     * Restore an archived pupil.
     */
    public function restore(Request $request, School $school, Pupil $pupil): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $pupil->update(['is_active' => true]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pupil restored.')]);

        return to_route('schools.pupils.show', [$school, $pupil]);
    }
}
