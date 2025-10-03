<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Premium - EV Charging</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .celebration-animation {
            animation: bounce 1s infinite alternate;
        }
        @keyframes bounce {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Success Message -->
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 celebration-animation">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Success Title -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome to Premium! ✨</h1>

                <!-- Success Message -->
                <p class="text-gray-600 mb-6">
                    Congratulations! Your subscription has been activated successfully. You now have access to all Premium features.
                </p>

                <!-- Premium Features -->
                <div class="bg-purple-50 rounded-xl p-6 mb-6 text-left">
                    <h3 class="font-semibold text-purple-900 mb-4 text-center">Your Premium Benefits</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700"><strong>Unlimited bookings</strong> per week</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700"><strong>20% discount</strong> on all charging sessions</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700"><strong>Weekly extra rewards</strong> and bonuses</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700"><strong>Priority customer support</strong></span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a href="{{ route('dashboard') }}"
                       class="w-full bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-purple-700 transition font-medium inline-block">
                        Start Booking Now
                    </a>
                    <a href="{{ route('subscription.index') }}"
                       class="w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-200 transition font-medium inline-block">
                        Manage Subscription
                    </a>
                </div>

                <!-- Footer Info -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        Your subscription will renew automatically on the same date each month.
                        You can cancel anytime from your subscription settings.
                    </p>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Need help? <a href="mailto:support@evcharging.com" class="text-purple-600 hover:text-purple-700 font-medium">Contact Support</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Auto redirect after 10 seconds -->
    <script>
        // Show countdown and redirect to dashboard
        let countdown = 10;
        const updateCountdown = () => {
            if (countdown > 0) {
                setTimeout(() => {
                    countdown--;
                    updateCountdown();
                }, 1000);
            } else {
                window.location.href = '{{ route("dashboard") }}';
            }
        };

        // Start countdown after 3 seconds
        setTimeout(updateCountdown, 3000);
    </script>
</body>
</html>
