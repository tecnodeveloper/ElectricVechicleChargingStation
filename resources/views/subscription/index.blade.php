<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans - EV Charging</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white py-6">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">EV Charging Subscription</h1>
                <a href="{{ route('dashboard') }}" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12" x-data="subscriptionData()">
        <!-- Current Status -->
        @if($user->isPremium())
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-green-800">Premium Member</h3>
                        <p class="text-green-700">You're currently on the Premium plan with unlimited access!</p>
                        @if($user->subscription_expires_at)
                            <p class="text-sm text-green-600 mt-1">Expires: {{ $user->subscription_expires_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                    <div class="ml-auto">
                        <button @click="cancelSubscription()"
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                            Cancel Subscription
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-blue-800">Free Plan</h3>
                        <p class="text-blue-700">You're currently on the Free plan.</p>
                        <p class="text-sm text-blue-600 mt-1">Remaining bookings this week: {{ $user->getRemainingBookings() ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Subscription Plans -->
        <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <!-- Free Plan -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200 card-hover">
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Free Plan</h3>
                    <div class="text-4xl font-bold text-gray-900 mb-2">$0</div>
                    <p class="text-gray-600 mb-8">per month</p>
                </div>

                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700">6 bookings per week</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700">Access to all charging stations</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-gray-500">No discounted pricing</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-gray-500">No weekly rewards</span>
                    </li>
                </ul>

                @if(!$user->isPremium())
                    <div class="text-center">
                        <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">
                            Current Plan
                        </span>
                    </div>
                @endif
            </div>

            <!-- Premium Plan -->
            <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-purple-500 card-hover relative">
                <!-- Popular Badge -->
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-purple-500 text-white px-6 py-2 rounded-full text-sm font-medium">
                        Most Popular
                    </span>
                </div>

                <div class="text-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Premium Plan</h3>
                    <div class="text-4xl font-bold text-purple-600 mb-2">$29.99</div>
                    <p class="text-gray-600 mb-8">per month</p>
                </div>

                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700"><strong>Unlimited bookings</strong></span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700">Access to all charging stations</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700"><strong>20% discount</strong> on all charges</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700">Weekly extra rewards</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700">Priority customer support</span>
                    </li>
                </ul>

                <div class="text-center">
                    @if($user->isPremium())
                        <span class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 rounded-lg">
                            Current Plan
                        </span>
                    @else
                        <button @click="upgradeToPremium()"
                                :disabled="loading"
                                class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-purple-700 transition disabled:opacity-50"
                                :class="{ 'cursor-not-allowed': loading }">
                            <span x-show="!loading">Upgrade to Premium</span>
                            <span x-show="loading">Processing...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>


    </div>

    <!-- Stripe Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6" @click.away="showPaymentModal = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Upgrade to Premium</h3>
                <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-6">
                <div class="text-center p-4 bg-purple-50 rounded-lg mb-4">
                    <div class="text-3xl font-bold text-purple-600">$29.99</div>
                    <div class="text-gray-600">per month</div>
                </div>

                <ul class="space-y-2 text-sm">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Unlimited bookings
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        20% discount on charges
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Weekly rewards
                    </li>
                </ul>
            </div>

            <form id="payment-form" @submit.prevent="processPayment()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Card Information</label>
                    <div id="card-element" class="p-3 border border-gray-300 rounded-lg">
                        <!-- Stripe Elements will create form elements here -->
                    </div>
                    <div id="card-errors" role="alert" class="text-red-500 text-sm mt-1"></div>
                </div>

                <button type="submit"
                        :disabled="loading"
                        class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg hover:bg-purple-700 transition disabled:opacity-50"
                        :class="{ 'cursor-not-allowed': loading }">
                    <span x-show="!loading">Subscribe Now - $29.99/month</span>
                    <span x-show="loading">Processing...</span>
                </button>
            </form>

            <p class="text-xs text-gray-500 mt-4 text-center">
                Secure payment processed by Stripe. Cancel anytime.
            </p>
        </div>
    </div>

    <script>
        // Initialize Stripe
        const stripe = Stripe('{{ env("STRIPE_KEY") }}');
        const elements = stripe.elements();

        function subscriptionData() {
            return {
                loading: false,
                showPaymentModal: false,
                cardElement: null,

                init() {
                    // Initialize Stripe Elements when modal opens
                    this.$watch('showPaymentModal', (isOpen) => {
                        if (isOpen && !this.cardElement) {
                            this.$nextTick(() => {
                                this.setupStripeElements();
                            });
                        }
                    });
                },

                setupStripeElements() {
                    const style = {
                        base: {
                            fontSize: '16px',
                            color: '#424770',
                            '::placeholder': {
                                color: '#aab7c4',
                            },
                        },
                    };

                    this.cardElement = elements.create('card', { style });
                    this.cardElement.mount('#card-element');

                    this.cardElement.on('change', ({ error }) => {
                        const displayError = document.getElementById('card-errors');
                        if (error) {
                            displayError.textContent = error.message;
                        } else {
                            displayError.textContent = '';
                        }
                    });
                },

                upgradeToPremium() {
                    this.showPaymentModal = true;
                },

                async processPayment() {
                    this.loading = true;

                    try {
                        // Create payment method
                        const { error, paymentMethod } = await stripe.createPaymentMethod({
                            type: 'card',
                            card: this.cardElement,
                        });

                        if (error) {
                            throw new Error(error.message);
                        }

                        // Send payment method to backend
                        const response = await fetch('{{ route("subscription.checkout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                payment_method: paymentMethod.id,
                                plan: 'premium'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Handle successful subscription
                            window.location.href = '{{ route("subscription.success") }}?payment_method=' + paymentMethod.id;
                        } else if (data.requires_action) {
                            // Handle 3D Secure authentication
                            const { error: confirmError } = await stripe.confirmCardPayment(data.payment_intent_client_secret);
                            if (confirmError) {
                                throw new Error(confirmError.message);
                            } else {
                                window.location.href = '{{ route("subscription.success") }}?payment_method=' + paymentMethod.id;
                            }
                        } else {
                            throw new Error(data.error || 'Payment failed');
                        }

                    } catch (error) {
                        alert('Payment Error: ' + error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                async cancelSubscription() {
                    if (!confirm('Are you sure you want to cancel your premium subscription?')) {
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("subscription.cancel") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            location.reload();
                        } else {
                            throw new Error(data.error || 'Failed to cancel subscription');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                }
            }
        }
    </script>
</body>
</html>
