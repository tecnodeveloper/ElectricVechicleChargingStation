<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - EV Charging Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            </div>
        </div>
    </nav>

    <!-- Cancel Content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 border border-slate-600 text-center">

                <!-- Cancel Icon -->
                <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-white mb-4">Payment Cancelled</h1>

                <p class="text-gray-300 mb-8">
                    😔 Your payment was cancelled and no booking was created.
                    <br><br>
                    💳 No charges were made to your account.
                    <br><br>
                    🔄 You can try booking again when you're ready.
                </p>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <button onclick="history.back()"
                            class="block w-full bg-gradient-to-r from-orange-600 to-red-600 text-white font-bold py-3 px-6 rounded-xl hover:from-orange-700 hover:to-red-700 transition duration-300">
                        🔙 Try Booking Again
                    </button>

                    <a href="{{ route('dashboard') }}"
                       class="block w-full bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-xl transition duration-300">
                        🏠 Back to Dashboard
                    </a>

                    <a href="{{ route('dashboard') }}?section=map"
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-xl transition duration-300">
                        🗺️ Find Other Stations
                    </a>
                </div>

                <!-- Help Section -->
                <div class="mt-8 p-4 bg-slate-700/50 rounded-xl text-left">
                    <h3 class="text-lg font-semibold text-white mb-3">💡 Need Help?</h3>
                    <div class="space-y-2 text-sm text-gray-300">
                        <p>• Check your payment method details</p>
                        <p>• Ensure sufficient funds are available</p>
                        <p>• Try a different payment method</p>
                        <p>• Contact support if issues persist</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
