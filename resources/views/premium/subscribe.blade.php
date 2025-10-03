<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Subscription - EV Charging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen">

    <!-- Navigation Header -->
    <nav class="bg-slate-800/80 backdrop-blur-sm border-b border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">EV Charging</span>
                    </a>
                </div>
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen py-12 px-4" x-data="premiumSubscription()">
        <div class="max-w-6xl mx-auto">

            <!-- Header Section -->
            <div class="text-center mb-16">
                <h1 class="text-5xl font-bold text-white mb-6">
                    ⚡ <span class="bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">Premium</span>
                    Charging Experience
                </h1>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Unlock unlimited bookings, priority access, and exclusive discounts with our Premium subscription
                </p>
            </div>

            <!-- Current Status (if user is already premium) -->
            @auth
                @if(Auth::user()->isPremium())
                    <div class="bg-gradient-to-r from-green-500/20 to-blue-500/20 border border-green-500/50 rounded-2xl p-6 mb-12">
                        <div class="flex items-center justify-center space-x-4">
                            <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-white">🎉 You're Already Premium!</h3>
                                <p class="text-gray-300">Enjoy your unlimited charging benefits</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Pricing Cards -->
            <div class="grid md:grid-cols-2 gap-8 mb-16">

                <!-- Free Plan -->
                <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl p-8 border border-slate-600">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-white mb-2">🆓 Free Plan</h3>
                        <div class="text-4xl font-bold text-gray-400 mb-4">$0<span class="text-lg font-normal">/month</span></div>
                        <p class="text-gray-400">Perfect for occasional users</p>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-300">Up to 5 bookings per week</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-300">Standard pricing</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-300">Basic customer support</span>
                        </li>
                        <li class="flex items-center space-x-3 opacity-50">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-500">No priority access</span>
                        </li>
                        <li class="flex items-center space-x-3 opacity-50">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-500">No discounts</span>
                        </li>
                    </ul>

                    <button disabled class="w-full py-3 px-6 bg-gray-600 text-gray-400 font-medium rounded-xl cursor-not-allowed">
                        Current Plan
                    </button>
                </div>

                <!-- Premium Plan -->
                <div class="bg-gradient-to-br from-yellow-500/20 to-orange-500/20 backdrop-blur-sm rounded-2xl p-8 border-2 border-yellow-500/50 relative overflow-hidden">

                    <!-- Popular Badge -->
                    <div class="absolute -top-1 -right-1 bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-4 py-1 text-sm font-bold rounded-bl-xl">
                        ⭐ POPULAR
                    </div>

                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-white mb-2">⚡ Premium Plan</h3>
                        <div class="text-4xl font-bold bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent mb-4">
                            $19.99<span class="text-lg font-normal text-gray-300">/month</span>
                        </div>
                        <p class="text-gray-300">For serious EV drivers</p>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">🚀 Unlimited bookings</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">💰 20% discount on all charging</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">⚡ Priority station access</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">📱 Advanced booking features</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">🎧 Priority customer support</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">📊 Detailed usage analytics</span>
                        </li>
                    </ul>

                    @auth
                        @if(!Auth::user()->isPremium())
                            <button @click="subscribeToPremium()" :disabled="subscribing"
                                    class="w-full py-4 px-6 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-bold text-lg rounded-xl hover:from-yellow-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition duration-300 shadow-lg">
                                <span x-show="!subscribing" class="flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    🚀 Upgrade to Premium
                                </span>
                                <span x-show="subscribing" class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating Payment...
                                </span>
                            </button>
                        @else
                            <button disabled class="w-full py-4 px-6 bg-green-600 text-white font-bold rounded-xl cursor-not-allowed">
                                ✅ Active Subscription
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full py-4 px-6 bg-slate-600 hover:bg-slate-500 text-white font-bold text-center rounded-xl transition duration-300">
                            Login to Subscribe
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Features Section -->
            <div class="bg-slate-800/30 backdrop-blur-sm rounded-2xl p-12 border border-slate-600 mb-16">
                <h2 class="text-3xl font-bold text-white text-center mb-12">🌟 Premium Benefits in Detail</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-yellow-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-3xl">🚀</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Unlimited Access</h3>
                        <p class="text-gray-300">Book as many charging sessions as you need without weekly limits.</p>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-3xl">💰</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Save Money</h3>
                        <p class="text-gray-300">Get 20% off all charging sessions. Heavy users save hundreds per year!</p>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-3xl">⚡</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Priority Access</h3>
                        <p class="text-gray-300">Get first access to available charging slots during peak hours.</p>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-slate-800/30 backdrop-blur-sm rounded-2xl p-12 border border-slate-600">
                <h2 class="text-3xl font-bold text-white text-center mb-12">❓ Frequently Asked Questions</h2>

                <div class="space-y-6 max-w-4xl mx-auto">
                    <div class="border-b border-slate-600 pb-6">
                        <h4 class="text-lg font-semibold text-white mb-2">Can I cancel my subscription anytime?</h4>
                        <p class="text-gray-300">Yes! You can cancel your Premium subscription at any time. You'll continue to have access until the end of your billing period.</p>
                    </div>

                    <div class="border-b border-slate-600 pb-6">
                        <h4 class="text-lg font-semibold text-white mb-2">How much can I save with the 20% discount?</h4>
                        <p class="text-gray-300">If you charge $100 worth per month, you save $20, so the subscription pays for itself! Heavy users save even more.</p>
                    </div>

                    <div class="border-b border-slate-600 pb-6">
                        <h4 class="text-lg font-semibold text-white mb-2">Is my payment information secure?</h4>
                        <p class="text-gray-300">Absolutely! We use Stripe for payment processing, which provides bank-level security for all transactions.</p>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-white mb-2">Do I get priority access during peak hours?</h4>
                        <p class="text-gray-300">Yes! Premium members get first access to available charging slots and can make reservations ahead of free users.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function premiumSubscription() {
            return {
                subscribing: false,

                async subscribeToPremium() {
                    if (this.subscribing) return;

                    this.subscribing = true;

                    try {
                        const response = await fetch('/subscription/checkout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                plan: 'premium',
                                price: 19.99
                            })
                        });

                        const result = await response.json();

                        if (response.ok && result.success && result.stripe_checkout_url) {
                            // Redirect to Stripe Checkout
                            window.location.href = result.stripe_checkout_url;
                        } else {
                            alert('❌ Failed to create subscription: ' + (result.error || 'Unknown error'));
                            this.subscribing = false;
                        }
                    } catch (error) {
                        console.error('Subscription error:', error);
                        alert('❌ Network error occurred while creating subscription.');
                        this.subscribing = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
