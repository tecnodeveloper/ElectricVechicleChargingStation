<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('user.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate a simple reset token (in production, use Laravel's password reset feature)
            $token = Str::random(60);
            $user->update(['remember_token' => $token]);

            // For now, just return success message
            // In production, you would send an actual email
            return back()->with('status', 'Password reset link sent to your email!');
        }

        return back()->withErrors(['email' => 'We could not find a user with that email address.']);
    }
}
