<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function show(Request $request)
    {
        return view('user.otp', ['email' => $request->email]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $email = $request->email;
        $otp   = $request->otp;

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Check if OTP matches and hasn't expired
        if ($user->otp == $otp && $user->otp_expires_at > now()) {
            // Clear OTP data
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->email_verified_at = now();
            $user->is_verified = true;
            $user->save();

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Your email has been verified!');
        }

        // Check if OTP expired
        if ($user->otp_expires_at <= now()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        return back()->withErrors(['otp' => 'Invalid OTP. Please check and try again.']);
    }
}
