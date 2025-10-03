<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:25',
            'email' => 'required|email|min:15|max:255|unique:users',
            'password' => 'required|string|min:8|max:50|confirmed',
        ], [
            'name.max' => 'Name must not exceed 25 characters.',
            'email.min' => 'Email must be at least 15 characters long.',
            'email.unique' => 'Email is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password must not exceed 50 characters.',
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'is_verified' => false,
        ]);

        // Send OTP email
        try {
            Mail::to($user->email)->send(new WelcomeEmail("Your OTP is: $otp", "Verify Your Email"));
        } catch (\Exception $e) {
            // Handle email sending error
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email with the OTP sent.',
            'user_id' => $user->id,
            'email' => $user->email
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Check if user is verified
        if (!$user->is_verified) {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email before logging in.'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->otp != $request->otp || $user->otp_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.'
            ], 400);
        }

        // Verify user
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()->load(['bookings', 'vehiclePreferences'])
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:25',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'email']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate reset token (in production, use Laravel's password reset feature)
        $token = rand(100000, 999999);
        $user->update([
            'otp' => $token,
            'otp_expires_at' => now()->addMinutes(15)
        ]);

        // Send reset email
        try {
            Mail::to($user->email)->send(new WelcomeEmail("Your password reset code is: $token", "Password Reset"));
        } catch (\Exception $e) {
            // Handle email sending error
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent to your email.'
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|digits:6',
            'password' => 'required|string|min:8|max:50|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp != $request->token || $user->otp_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.'
        ]);
    }
}
