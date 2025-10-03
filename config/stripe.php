<?php

return [
    'publishable_key' => env('STRIPE_KEY'),
    'secret_key' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'endpoint_secret' => env('STRIPE_ENDPOINT_SECRET'),

    // Subscription Plans
    'plans' => [
        'free' => [
            'name' => 'Free Plan',
            'price' => 0,
            'currency' => 'usd',
            'interval' => 'month',
            'features' => [
                'booking_limit' => 5, // 5 bookings per week as requested
                'unlimited_stations' => false,
                'discounted_pricing' => false,
                'weekly_rewards' => false,
                'discount_percentage' => 0,
            ]
        ],
        'premium' => [
            'name' => 'Premium Plan',
            'monthly_price' => 999, // $9.99 in cents
            'yearly_price' => 9999,  // $99.99 in cents
            'price' => 999, // Default to monthly
            'currency' => 'usd',
            'interval' => 'month',
            'stripe_price_id' => env('STRIPE_PREMIUM_PRICE_ID', 'price_premium'),
            'features' => [
                'booking_limit' => null, // unlimited
                'unlimited_stations' => true,
                'discounted_pricing' => true,
                'weekly_rewards' => true,
                'discount_percentage' => 20, // 20% discount
            ]
        ]
    ]
];
