<?php

namespace App\Http\Controllers;

use App\Enums\SchoolRole;
use App\Models\SchoolInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    /**
     * Accept an invitation and join the school.
     */
    public function accept(Request $request, SchoolInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->email === $request->user()->email, 403);

        $school = $invitation->school;

        if (! $school->hasMember($request->user())) {
            $school->users()->attach($request->user(), ['role' => SchoolRole::from($invitation->role)]);
        }

        $invitation->delete();

        $request->session()->put('current_school_id', $school->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation accepted.')]);

        return redirect()->route('dashboard');
    }

    /**
     * Decline an invitation.
     */
    public function decline(Request $request, SchoolInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->email === $request->user()->email, 403);

        $invitation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Invitation declined.')]);

        return back();
    }
}
