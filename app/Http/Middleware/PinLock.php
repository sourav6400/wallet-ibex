<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PinLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        // $timeout = 300; // seconds, or higher for production
        $timeout = 525600;
        $lastActive = session('last_active_at', now()->timestamp);
        $now = now()->timestamp;

        // If session is locked or last activity exceeded timeout
        if (($now - $lastActive) > $timeout || session('locked', false) === true) {
            // session(['locked' => true]);
            // return redirect()->route('lock.show');

            session([
                'locked' => true,
                'url.intended' => $request->fullUrl(), // <-- store last visited page
            ]);
            return redirect()->route('lock.show');
        }

        // Update last active timestamp
        session(['last_active_at' => $now]);
        return $next($request);
    }
}
