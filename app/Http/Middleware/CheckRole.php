<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! Auth::check()) {
            return redirect()->route('user.login');
        }

        $user = Auth::user();

        if (! $user->hasRole(...$roles)) {
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->isOrganizer()) {
                return redirect()->route('organizer.dashboard');
            }

            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($user->isOrganizer() && ! $user->canAccessOrganizerPanel()) {
            if ($request->routeIs('organizer.waiting')) {
                return $next($request);
            }

            return redirect()->route('organizer.waiting');
        }

        if ($user->isOrganizer() && $request->routeIs('organizer.waiting')) {
            return redirect()->route('organizer.dashboard');
        }

        return $next($request);
    }
}
