<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BalanceService;
use App\Models\CustomMessage;
use Illuminate\Support\Facades\Auth;

class CustomMessageController extends Controller
{
    public function alerts(BalanceService $balanceService)
    {
        $title = "Alerts";
        $tokens = $balanceService->getFilteredTokens();
        
        $alerts = CustomMessage::where('message_type', 'alert')
            ->active()
            ->current()
            ->where(function ($query) {
                $query->where('is_global', true)
                      ->orWhere('user_id', Auth::user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('custom-messages.alerts', compact('title', 'tokens', 'alerts'));
    }

    public function announcements(BalanceService $balanceService)
    {
        $title = "Announcements";
        $tokens = $balanceService->getFilteredTokens();
        
        $announcements = CustomMessage::where('message_type', 'announcement')
            ->active()
            // ->current()
            ->where(function ($query) {
                $query->where('is_global', true)
                      ->orWhere('user_id', Auth::user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('custom-messages.announcements', compact('title', 'tokens', 'announcements'));
    }
}
