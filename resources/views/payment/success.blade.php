<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - EV Charging Platform</title>
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

    <!-- Success Content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <div class="bg-slate-800/80 backdrop-blur-sm rounded-2xl p-8 border border-slate-600 text-center">

                <!-- Success Icon -->
                <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-white mb-4">Payment Successful!</h1>

                <p class="text-gray-300 mb-8">
                    🎉 Your charging station has been booked successfully!
                    <br><br>
                    ⚡ You will receive a confirmation email shortly with all the details.
                    <br><br>
                    📱 You can view your booking status in your dashboard.
                </p>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <a href="{{ route('dashboard') }}"
                       class="block w-full bg-gradient-to-r from-green-600 to-blue-600 text-white font-bold py-3 px-6 rounded-xl hover:from-green-700 hover:to-blue-700 transition duration-300">
                        📊 View My Bookings
                    </a>

                    <a href="{{ route('dashboard') }}?section=map"
                       class="block w-full bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-xl transition duration-300">
                        🗺️ Find More Stations
                    </a>
                </div>

                <!-- Booking Details (if available) -->
                @if(session('booking_details'))
                    <div class="mt-8 p-4 bg-slate-700/50 rounded-xl text-left">
                        <h3 class="text-lg font-semibold text-white mb-3">📋 Booking Details</h3>
                        <div class="space-y-2 text-sm text-gray-300">
                            <div class="flex justify-between">
                                <span>Station:</span>
                                <span class="text-white">{{ session('booking_details.station_name') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Date & Time:</span>
                                <span class="text-white">{{ session('booking_details.start_time') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Duration:</span>
                                <span class="text-white">{{ session('booking_details.duration_hours') }} hours</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Paid:</span>
                                <span class="text-green-400 font-semibold">${{ session('booking_details.total_amount') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
