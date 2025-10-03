<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        // Set Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function index()
    {
        $user = Auth::user();

        // Redirect premium users back to dashboard using the isPremium method
        if ($user->isPremium()) {
            return redirect()->route('dashboard')->with('success', 'You are already a premium member!');
        }

        // Get Stripe publishable key
        $stripeKey = env('STRIPE_KEY');

        return view('stripe.payment', compact('user', 'stripeKey'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'plan' => 'required|in:monthly,yearly'
        ]);

        try {
            $user = Auth::user();

            // Check if user is already premium using the isPremium method
            if ($user->isPremium()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already a premium member!'
                ], 400);
            }

            // Set amount based on plan
            $amounts = [
                'monthly' => 999, // $9.99 in cents
                'yearly' => 9999  // $99.99 in cents
            ];

            $amount = $amounts[$request->plan];

            // Create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('dashboard'),
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $request->plan,
                    'email' => $user->email
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Update user subscription - update both fields for consistency
                $expiresAt = $request->plan === 'yearly' ? now()->addYear() : now()->addMonth();

                $user->update([
                    'subscription_type' => 'premium',
                    'subscription_plan' => 'premium',
                    'subscription_start_date' => now(),
                    'subscription_end_date' => $expiresAt,
                    'subscription_expires_at' => $expiresAt,
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'weekly_bookings_used' => 0, // Reset weekly bookings
                    'weekly_reset_at' => now()->addWeek()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! Welcome to Premium!',
                    'redirect' => route('dashboard')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment requires additional authentication.',
                    'requires_action' => true,
                    'payment_intent' => $paymentIntent
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
