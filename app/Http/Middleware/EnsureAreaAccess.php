<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAreaAccess
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->uiRoleKey() === $area) {
            return $next($request);
        }

        return redirect()
            ->route($user->dashboardRouteName())
            ->with('error', 'You do not have permission to access that section.');
    }
}
