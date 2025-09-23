<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

class LockController extends Controller
{
    public function show()
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        // dd($user);
        return view('lock'); // Blade file
    }

    public function lock(Request $request)
    {
        abort_unless(Auth::check(), 403);
        session(['locked' => true]);
        return response()->noContent();
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->pin, $user->pin_hash)) {
            return back()->withErrors(['pin' => 'Incorrect PIN']);
        }

        // Clear lock on server
        session([
            'locked' => false,
            'last_active_at' => now()->timestamp
        ]);

        // Retrieve intended URL (default to /dashboard if none)
        $intended = session('url.intended', route('dashboard'));
        session()->forget('url.intended'); // clear it once used

        return redirect()->to($intended)->with('unlocked', true);

        // // Flag to clear localStorage in Blade
        // return redirect()->intended('/dashboard')->with('unlocked', true);
    }
}
