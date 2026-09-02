<?php

namespace App\Http\Controllers;

use App\Enums\SchoolRole;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * Display the specified school settings.
     */
    public function show(Request $request, School $school): Response
    {
        abort_unless($school->hasAdmin($request->user()), 403);

        return Inertia::render('schools/show', [
            'school' => $school,
        ]);
    }

    /**
     * Update the specified school.
     */
    public function update(UpdateSchoolRequest $request, School $school): RedirectResponse
    {
        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('schools', 'public');
        }

        $school->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('School updated.')]);

        return to_route('schools.show', $school);
    }

    /**
     * Display the members of the school.
     */
    public function members(Request $request, School $school): Response
    {
        abort_unless($school->hasAdmin($request->user()), 403);

        return Inertia::render('schools/members', [
            'school' => $school,
            'members' => $school->users()
                ->orderBy('name')
                ->get()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'is_self' => $user->id === $request->user()->id,
                ]),
        ]);
    }

    /**
     * Toggle a member's role between admin and member.
     */
    public function toggleRole(Request $request, School $school, int $userId): RedirectResponse
    {
        abort_unless($school->hasAdmin($request->user()), 403);

        $member = $school->users()->where('users.id', $userId)->firstOrFail();

        abort_if($member->id === $request->user()->id, 403);

        $newRole = $member->pivot->role === SchoolRole::Admin->value
            ? SchoolRole::Member
            : SchoolRole::Admin;

        $school->users()->updateExistingPivot($userId, ['role' => $newRole]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return back();
    }

    /**
     * Add a new member to the school, creating the user account if needed.
     */
    public function addMember(Request $request, School $school): RedirectResponse
    {
        abort_unless($school->hasAdmin($request->user()), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && $school->hasMember($user)) {
            return back()->withErrors(['email' => __('This user is already a member of this school.')]);
        }

        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Str::random(32),
            ]);
        }

        $school->users()->attach($user, ['role' => SchoolRole::from($validated['role'])]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Member added.')]);

        return back();
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
