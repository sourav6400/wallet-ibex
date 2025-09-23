<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class UserController extends Controller
{
    public function onboarding1()
    {
        $title = "Welcome";
        return view('guest.onboarding1', compact('title'));
    }
    public function onboarding2()
    {
        $title = "Welcome";
        return view('guest.onboarding2', compact('title'));
    }
    public function onboarding3()
    {
        $title = "Welcome";
        return view('guest.onboarding3', compact('title'));
    }
    
    public function send_support_mail(Request $request)
    {
        $data = [
            'email'   => $request->email,
            'subject' => $request->subject,
            'details' => $request->details,
        ];
        
        try {
            // Send the email
            Mail::to("sourav.das@w3scloud.com")->send(new ContactMail($data));
            
            return response()->json([
                'message' => 'Email sent successfully!'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // public function send_support_mail(Request $request)
    // {
    //     // Get user (example)
    //     $user = User::find($request->user_id);
        
    //     // Or create a mock user object
    //     $user = (object) [
    //         'name' => $request->name,
    //         'email' => $request->email
    //     ];

    //     try {
    //         // Send the email
    //         Mail::to($user->email)->send(new WelcomeEmail($user));
            
    //         return response()->json([
    //             'message' => 'Email sent successfully!'
    //         ], 200);
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Failed to send email: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}
