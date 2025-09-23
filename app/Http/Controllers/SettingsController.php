<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function backup_seed(BalanceService $balanceService)
    {
        $mnemonic12 = Auth::user()->phrase12;
        $words = explode(" ", $mnemonic12);
        $title = "Backup Seed";
        $tokens = $balanceService->getFilteredTokens();
        return view('settings.backup-seed', compact('title', 'tokens', 'mnemonic12', 'words'));
    }
    public function checkPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if (!$user || !Hash::check($request->pin, $user->pin_hash)) {
            return response()->json(['success' => false, 'message' => 'Invalid PIN.'], 401);
        }

        return response()->json(['success' => true]);
    }
    public function change_pin_view(BalanceService $balanceService)
    {
        $title = "Change PIN";
        $tokens = $balanceService->getFilteredTokens();
        return view('settings.change-pin', compact('title', 'tokens'));
    }
    public function store_new_pin(Request $request)
    {
        $oldPin = $request->oldPin;
        $newPin = $request->newPin;
        $hashedPin = Auth::user()->pin_hash;
        if (Hash::check($oldPin, $hashedPin)) {
            $user_id = Auth::user()->id;
            $user = User::find($user_id);
            $user->pin_hash = Hash::make($newPin);
            $user->save();

            return redirect()->back()->with('success_msg', 'PIN Updated Successfully');
        } else {
            return redirect()->back()->with('error_msg', 'Old PIN Mismatched!');
        }
    }
    public function faq(BalanceService $balanceService)
    {
        $title = "FAQs";
        $tokens = $balanceService->getFilteredTokens();
        return view('settings.faq', compact('title', 'tokens'));
    }
    public function terms_conditions(BalanceService $balanceService)
    {
        $title = "Terms & Conditions";
        $tokens = $balanceService->getFilteredTokens();
        return view('settings.terms-conditions', compact('title', 'tokens'));
    }
    public function support(BalanceService $balanceService)
    {
        $title = "Support";
        $tokens = $balanceService->getFilteredTokens();
        return view('settings.support', compact('title', 'tokens'));
    }
}
