<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasSchool
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->schools()->count() === 0) {
            if (! $request->routeIs('schools.onboarding', 'schools.store')) {
                return redirect()->route('schools.onboarding');
            }
        }

        return $next($request);
    }
}
