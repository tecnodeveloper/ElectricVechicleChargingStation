<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class RegisterController extends Controller
{
    public function index()
    {
        return view('user.register');
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'name'       => 'required|string|min:2|max:25',
            'email'      => 'required|string|email|min:15|max:255|unique:users',
            'password'   => 'required|string|min:8|max:50|confirmed',
            'terms'      => 'required|accepted',
        ], [
            'name.max' => 'Full name must not exceed 25 characters.',
            'name.min' => 'Full name must be at least 2 characters.',
            'email.min' => 'Email must be at least 15 characters long.',
            'email.email' => 'Email must contain @ symbol and be valid.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password must not exceed 50 characters.',
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);

        // Store user with OTP
        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'is_verified'    => false,
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new WelcomeEmail("Your OTP is: $otp", "Verify Your Email"));

        return redirect()->route('otp.view', ['email' => $user->email])
            ->with('success', 'We sent an OTP to your email.');
    }
}
