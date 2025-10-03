<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Plan - EV Charging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen">

    <!-- Navigation Header -->
    <nav class="bg-slate-800/80 backdrop-blur-sm border-b border-slate-700 sticky top-0 z-50">
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
    <div class="min-h-screen px-4 py-12">
        <div class="max-w-7xl mx-auto">

            <!-- Header Section -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-full mb-8">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold text-white mb-6">Choose Your Plan</h1>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">Select the perfect plan for your EV charging needs. Upgrade anytime to unlock premium features and unlimited access.</p>
            </div>

            <!-- Current Plan Status -->
            @if(Auth::user()->isPremium())
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl p-6 mb-12 text-center">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                        </svg>
                        <h2 class="text-2xl font-bold text-white">You're Premium! ✨</h2>
                    </div>
                    <p class="text-green-100 text-lg">You already have access to all premium features and unlimited bookings.</p>
                </div>
            @endif

            <!-- Plans Comparison -->
            <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">

                <!-- Free Plan -->
                <div class="relative bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition-colors">
                    <!-- Plan Header -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-600 rounded-full mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Free Plan</h3>
                        <div class="text-4xl font-bold text-white mb-1">
                            $0<span class="text-lg text-gray-400">/month</span>
                        </div>
                        <p class="text-gray-400">Perfect for occasional users</p>
                    </div>

                    <!-- Features -->
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">5 charging sessions per week</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Access to all public stations</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Basic booking system</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Standard customer support</span>
                        </li>
                    </ul>

                    <!-- Current Plan Badge -->
                    @if(!Auth::user()->isPremium())
                        <div class="bg-blue-600 text-white text-center py-3 px-6 rounded-lg font-semibold mb-6">
                            Current Plan
                        </div>
                    @else
                        <div class="text-center py-3 px-6 rounded-lg font-semibold mb-6 border border-gray-600 text-gray-400">
                            Previously Used
                        </div>
                    @endif

                    <!-- Bookings Remaining -->
                    @if(!Auth::user()->isPremium())
                        <div class="bg-slate-700 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">Weekly bookings used:</span>
                                <span class="font-bold text-white">{{ Auth::user()->weekly_bookings_used }}/5</span>
                            </div>
                            <div class="w-full bg-slate-600 rounded-full h-2 mt-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ (Auth::user()->weekly_bookings_used / 5) * 100 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Premium Plan -->
                <div class="relative bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl p-8 transform hover:scale-105 transition-transform">
                    <!-- Popular Badge -->
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-white text-orange-600 px-6 py-2 rounded-full text-sm font-bold">MOST POPULAR</span>
                    </div>

                    <!-- Plan Header -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Premium Plan</h3>
                        <div class="text-4xl font-bold text-white mb-1">
                            $9.99<span class="text-lg text-yellow-100">/month</span>
                        </div>
                        <p class="text-yellow-100">For unlimited charging freedom</p>
                    </div>

                    <!-- Features -->
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white font-semibold">Unlimited charging sessions</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">20% discount on all charges</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Priority station booking</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Access to premium stations</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Weekly extra rewards</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-white mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                            <span class="text-white">Priority customer support</span>
                        </li>
                    </ul>

                    <!-- Action Button -->
                    @if(!Auth::user()->isPremium())
                        <a href="{{ route('stripe.payment') }}"
                           class="w-full bg-white text-orange-600 font-bold py-4 px-6 rounded-lg hover:bg-gray-100 transition duration-300 block text-center text-lg">
                            Upgrade to Premium
                        </a>
                    @else
                        <div class="w-full bg-white/20 text-white font-bold py-4 px-6 rounded-lg text-center text-lg">
                            ✅ Current Plan
                        </div>
                    @endif

                    <!-- Yearly Option -->
                    @if(!Auth::user()->isPremium())
                        <div class="mt-4 text-center">
                            <p class="text-yellow-100 text-sm">
                                💡 Save 17% with yearly plan: <strong>$99.99/year</strong>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-20 max-w-4xl mx-auto">
                <h2 class="text-3xl font-bold text-white text-center mb-12">Frequently Asked Questions</h2>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                        <h3 class="text-xl font-bold text-white mb-3">Can I switch plans anytime?</h3>
                        <p class="text-gray-300">Yes! You can upgrade to Premium at any time. Your new benefits take effect immediately.</p>
                    </div>

                    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                        <h3 class="text-xl font-bold text-white mb-3">What happens to unused bookings?</h3>
                        <p class="text-gray-300">Free plan bookings reset every week. Premium users get unlimited bookings that never expire.</p>
                    </div>

                    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                        <h3 class="text-xl font-bold text-white mb-3">Is the 20% discount immediate?</h3>
                        <p class="text-gray-300">Yes! Premium members get 20% off all charging sessions automatically applied at checkout.</p>
                    </div>

                    <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                        <h3 class="text-xl font-bold text-white mb-3">Can I cancel anytime?</h3>
                        <p class="text-gray-300">Absolutely. No long-term commitments. Cancel anytime and continue using benefits until period ends.</p>
                    </div>
                </div>
            </div>

            <!-- Back to Dashboard -->
            <div class="text-center mt-16">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-6 py-3 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

</body>
</html>
