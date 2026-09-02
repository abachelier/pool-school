<?php

namespace App\Http\Controllers;

use App\Enums\SchoolRole;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'pendingInvitations' => SchoolInvitation::where('school_id', $school->id)
                ->with('inviter:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn (SchoolInvitation $invitation) => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'invited_by_name' => $invitation->inviter->name,
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
     * Invite a user to the school by email.
     */
    public function inviteMember(Request $request, School $school): RedirectResponse
    {
        abort_unless($school->hasAdmin($request->user()), 403);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser && $school->hasMember($existingUser)) {
            return back()->withErrors(['email' => __('This user is already a member of this school.')]);
        }

        if (SchoolInvitation::where('school_id', $school->id)->where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => __('An invitation has already been sent to this email.')]);
        }

        SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'invited_by' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation sent.')]);

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
