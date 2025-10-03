<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Premium - EV Charging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
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
    <div class="min-h-screen flex items-center justify-center px-4 py-12" x-data="stripePayment()">
        <div class="max-w-4xl w-full">

            <!-- Header Section -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-full mb-6">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold text-white mb-4">Upgrade to Premium</h1>
                <p class="text-xl text-gray-300 max-w-2xl mx-auto">Unlock unlimited charging sessions, priority booking, and exclusive premium stations</p>
            </div>

            <!-- Pricing Cards -->
            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <!-- Monthly Plan -->
                <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-8 hover:bg-slate-800/70 transition duration-300"
                     :class="selectedPlan === 'monthly' ? 'ring-2 ring-blue-500 bg-slate-800/70' : ''"
                     @click="selectPlan('monthly')">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-white mb-2">Monthly Plan</h3>
                        <div class="text-4xl font-bold text-blue-400 mb-4">$9.99<span class="text-lg text-gray-400">/month</span></div>
                        <ul class="text-gray-300 space-y-3 mb-6">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Unlimited charging sessions
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Priority booking access
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Premium stations access
                            </li>
                        </ul>
                        <button type="button" @click="selectPlan('monthly')"
                                class="w-full py-3 px-6 rounded-lg font-semibold transition duration-300"
                                :class="selectedPlan === 'monthly' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600'">
                            Select Monthly
                        </button>
                    </div>
                </div>

                <!-- Yearly Plan -->
                <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-8 hover:bg-slate-800/70 transition duration-300 relative"
                     :class="selectedPlan === 'yearly' ? 'ring-2 ring-yellow-500 bg-slate-800/70' : ''"
                     @click="selectPlan('yearly')">
                    <!-- Popular Badge -->
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-4 py-2 rounded-full text-sm font-bold">
                            Most Popular - Save 17%
                        </span>
                    </div>
                    <div class="text-center mt-4">
                        <h3 class="text-2xl font-bold text-white mb-2">Yearly Plan</h3>
                        <div class="text-4xl font-bold text-yellow-400 mb-1">$99.99<span class="text-lg text-gray-400">/year</span></div>
                        <p class="text-sm text-green-400 mb-4">Save $19.89 compared to monthly</p>
                        <ul class="text-gray-300 space-y-3 mb-6">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Everything in Monthly
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                17% savings (2 months free)
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Premium support priority
                            </li>
                        </ul>
                        <button type="button" @click="selectPlan('yearly')"
                                class="w-full py-3 px-6 rounded-lg font-semibold transition duration-300"
                                :class="selectedPlan === 'yearly' ? 'bg-yellow-600 text-white' : 'bg-slate-700 text-gray-300 hover:bg-slate-600'">
                            Select Yearly
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-8" x-show="selectedPlan">
                <h3 class="text-2xl font-bold text-white mb-6 text-center">Complete Your Payment</h3>

                <!-- Selected Plan Summary -->
                <div class="bg-slate-900/50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Selected Plan:</span>
                        <span class="text-white font-semibold" x-text="selectedPlan === 'monthly' ? 'Monthly Premium' : 'Yearly Premium'"></span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-gray-300">Amount:</span>
                        <span class="text-2xl font-bold text-yellow-400" x-text="selectedPlan === 'monthly' ? '$9.99' : '$99.99'"></span>
                    </div>
                </div>

                <form @submit.prevent="processPayment" id="payment-form">
                    <div class="mb-6">
                        <label class="block text-gray-300 text-sm font-medium mb-2">Card Information</label>
                        <div id="card-element" class="bg-slate-900 border border-slate-600 rounded-lg p-4">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                        <div id="card-errors" class="text-red-400 text-sm mt-2" role="alert"></div>
                    </div>

                    <button type="submit" id="submit-payment"
                            :disabled="processing"
                            class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg rounded-lg hover:from-blue-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-300">
                        <span x-show="!processing">
                            Complete Payment - <span x-text="selectedPlan === 'monthly' ? '$9.99' : '$99.99'"></span>
                        </span>
                        <span x-show="processing" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-400">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        Secured by Stripe. Your payment information is encrypted and secure.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function stripePayment() {
            return {
                selectedPlan: 'yearly',
                processing: false,
                stripe: null,
                cardElement: null,

                init() {
                    // Initialize Stripe
                    this.stripe = Stripe('{{ $stripeKey }}');

                    // Create card element
                    const elements = this.stripe.elements();
                    this.cardElement = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#fff',
                                '::placeholder': {
                                    color: '#9ca3af',
                                },
                                backgroundColor: 'transparent'
                            },
                        },
                    });

                    this.cardElement.mount('#card-element');

                    // Handle real-time validation errors from the card Element
                    this.cardElement.on('change', ({error}) => {
                        const displayError = document.getElementById('card-errors');
                        if (error) {
                            displayError.textContent = error.message;
                        } else {
                            displayError.textContent = '';
                        }
                    });
                },

                selectPlan(plan) {
                    this.selectedPlan = plan;
                },

                async processPayment() {
                    if (this.processing) return;

                    this.processing = true;

                    try {
                        // Create payment method
                        const {paymentMethod, error} = await this.stripe.createPaymentMethod({
                            type: 'card',
                            card: this.cardElement,
                        });

                        if (error) {
                            throw new Error(error.message);
                        }

                        // Process payment on server
                        const response = await fetch('/stripe-payment/process', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                payment_method_id: paymentMethod.id,
                                plan: this.selectedPlan
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Show success message and redirect
                            alert('Payment successful! Welcome to Premium!');
                            window.location.href = result.redirect;
                        } else {
                            throw new Error(result.message);
                        }

                    } catch (error) {
                        alert('Payment failed: ' + error.message);
                    } finally {
                        this.processing = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
