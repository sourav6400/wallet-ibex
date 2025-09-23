<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user status is not "Active"
            if ($user->status !== 'Active') {
                // For API requests, return JSON response
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your account has been permanently disabled due to violations of our Terms and Conditions. You no longer have access to this wallet. If you believe this is an error, please contact our support team.'
                    ], 403);
                }
                
                // For web requests, redirect with error message
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'account_disabled' => 'Your account has been permanently disabled due to violations of our Terms and Conditions. You no longer have access to this wallet. If you believe this is an error, please contact our support team.'
                ]);
            }
        }
        
        return $next($request);
    }
}