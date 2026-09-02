<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsSet
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->last_connected_at === null && ! $request->routeIs('password.setup', 'password.setup.update', 'logout')) {
            return redirect()->route('password.setup');
        }

        return $next($request);
    }
}
