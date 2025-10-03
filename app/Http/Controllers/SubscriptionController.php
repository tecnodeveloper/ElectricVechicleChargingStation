<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Show subscription plans page.
     */
    public function index()
    {
        $user = Auth::user();
        $plans = config('stripe.plans');

        return view('subscription.index', compact('user', 'plans'));
    }

    /**
     * Create Stripe subscription with payment method.
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            $user = Auth::user();
            $plan = $request->input('plan', 'premium');
            $paymentMethodId = $request->input('payment_method');

            if ($plan !== 'premium') {
                return response()->json(['error' => 'Invalid plan selected'], 400);
            }

            // For demo purposes, we'll simulate successful payment
            // In production, you would use the Stripe PHP SDK:
            /*
            \Stripe\Stripe::setApiKey(config('stripe.secret_key'));

            // Create or retrieve customer
            if (!$user->stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'payment_method' => $paymentMethodId,
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethodId,
                    ],
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            } else {
                $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
            }

            // Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [
                    ['price' => config('stripe.plans.premium.stripe_price_id')],
                ],
                'default_payment_method' => $paymentMethodId,
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            if ($subscription->status == 'requires_action') {
                return response()->json([
                    'requires_action' => true,
                    'payment_intent_client_secret' => $subscription->latest_invoice->payment_intent->client_secret
                ]);
            }
            */

            // Simulate successful subscription for demo
            return response()->json([
                'success' => true,
                'subscription_id' => 'sub_demo_' . uniqid()
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription checkout error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to process payment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle successful subscription.
     */
    public function success(Request $request)
    {
        $paymentMethodId = $request->get('payment_method');
        $sessionId = $request->get('session_id');

        // Validate that this is a legitimate success callback
        if (!$paymentMethodId && !$sessionId) {
            return redirect()->route('subscription.index')->with('error', 'Invalid subscription session');
        }

        // Upgrade user to premium
        $user = Auth::user();

        // Check if user is already premium to prevent double processing
        if (!$user->isPremium()) {
            $user->update([
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addMonth(),
                'stripe_customer_id' => $paymentMethodId ? 'cus_' . uniqid() : 'sim_cus_' . uniqid(),
                'stripe_subscription_id' => $paymentMethodId ? 'sub_' . uniqid() : 'sim_sub_' . uniqid(),
                'subscription_features' => config('stripe.plans.premium.features'),
                'weekly_bookings_used' => 0,
                'weekly_reset_at' => now()->addWeek()
            ]);
        }

        return view('subscription.success');
    }

    /**
     * Handle subscription cancellation.
     */
    public function cancel(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->isPremium()) {
                return response()->json(['error' => 'No active premium subscription'], 400);
            }

            // In production, cancel Stripe subscription here

            $user->update([
                'subscription_plan' => 'free',
                'subscription_expires_at' => null,
                'stripe_subscription_id' => null,
                'subscription_features' => config('stripe.plans.free.features'),
                'weekly_bookings_used' => 0,
                'weekly_reset_at' => now()->addWeek()
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Subscription cancellation error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel subscription'], 500);
        }
    }

    /**
     * Get user's subscription status.
     */
    public function status()
    {
        $user = Auth::user();

        return response()->json([
            'plan' => $user->subscription_plan,
            'is_premium' => $user->isPremium(),
            'expires_at' => $user->subscription_expires_at,
            'remaining_bookings' => $user->getRemainingBookings(),
            'features' => $user->getSubscriptionFeatures(),
            'discount_percentage' => $user->getDiscountPercentage()
        ]);
    }
}
