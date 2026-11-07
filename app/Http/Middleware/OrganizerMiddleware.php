<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Harus sudah login dan memiliki role organizer
        if (! $user || ! $user->isOrganizer()) {
            return redirect()->route('login')->withErrors(['email' => 'Akses ditolak. Silakan login sebagai Organizer.']);
        }

        // Jika belum disetujui, dan tidak sedang mengakses halaman waiting
        if (! $user->canAccessOrganizerPanel() && ! $request->routeIs('organizer.waiting')) {
            return redirect()->route('organizer.waiting');
        }

        // Jika sudah disetujui tapi mengakses halaman waiting, kembalikan ke dashboard
        if ($user->canAccessOrganizerPanel() && $request->routeIs('organizer.waiting')) {
            return redirect()->route('organizer.dashboard');
        }

        return $next($request);
    }
}
