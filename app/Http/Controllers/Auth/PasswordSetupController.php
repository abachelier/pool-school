<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordSetupController extends Controller
{
    /**
     * Show the password setup form for first-time users.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()->last_connected_at !== null) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/setup-password', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Handle the password setup submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $request->password,
            'last_connected_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password set successfully.')]);

        return redirect()->route('dashboard');
    }
}
