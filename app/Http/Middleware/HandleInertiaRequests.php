<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $schools = [];
        $currentSchoolId = null;
        $currentSchoolRole = null;

        if ($user) {
            $schools = $user->schools()->select('schools.id', 'schools.name')->get()->map(fn ($school) => [
                'id' => $school->id,
                'name' => $school->name,
            ])->all();

            if (count($schools) > 0) {
                $sessionSchoolId = $request->session()->get('current_school_id');
                $schoolIds = array_column($schools, 'id');

                $currentSchoolId = in_array($sessionSchoolId, $schoolIds)
                    ? $sessionSchoolId
                    : $schools[0]['id'];

                $currentSchool = $user->schools()->where('schools.id', $currentSchoolId)->first();
                $currentSchoolRole = $currentSchool?->pivot->role;
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'schools' => $schools,
                'currentSchoolId' => $currentSchoolId,
                'currentSchoolRole' => $currentSchoolRole,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
