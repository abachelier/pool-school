<?php

namespace App\Http\Controllers;

use App\Enums\SchoolRole;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolController extends Controller
{
    /**
     * Show the full-page school creation for users with no schools.
     */
    public function onboarding(): Response
    {
        return Inertia::render('schools/onboarding');
    }

    /**
     * Show the form for creating a new school.
     */
    public function create(): Response
    {
        return Inertia::render('schools/create');
    }

    /**
     * Store a newly created school.
     */
    public function store(StoreSchoolRequest $request): RedirectResponse
    {
        $school = School::create($request->validated());

        $school->users()->attach($request->user(), ['role' => SchoolRole::Admin]);

        $request->session()->put('current_school_id', $school->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('School created.')]);

        return to_route('schools.show', $school);
    }

    /**
     * Display the specified school.
     */
    public function show(Request $request, School $school): Response
    {
        abort_unless($school->hasMember($request->user()), 403);

        return Inertia::render('schools/show', [
            'school' => $school,
        ]);
    }

    /**
     * Show the form for editing the specified school.
     */
    public function edit(Request $request, School $school): Response
    {
        abort_unless($school->hasAdmin($request->user()), 403);

        return Inertia::render('schools/edit', [
            'school' => $school,
        ]);
    }

    /**
     * Update the specified school.
     */
    public function update(UpdateSchoolRequest $request, School $school): RedirectResponse
    {
        $school->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('School updated.')]);

        return to_route('schools.show', $school);
    }

    /**
     * Switch the active school for the current user.
     */
    public function switch(Request $request, School $school): RedirectResponse
    {
        abort_unless($school->hasMember($request->user()), 403);

        $request->session()->put('current_school_id', $school->id);

        return to_route('schools.show', $school);
    }
}
