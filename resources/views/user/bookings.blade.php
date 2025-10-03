<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - EVC Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-900 text-white min-h-screen">
    <!-- Header -->
    <header class="bg-slate-800 shadow-lg border-b border-slate-700">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold">EVC Project</h1>
                    <p class="text-sm text-gray-400">Electric Vehicle Charging Management</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-300">{{ Auth::user()->email }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm transition-colors">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-slate-800 border-r border-slate-700">
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white hover:bg-slate-700 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span>Home</span>
                </a>
                <a href="{{ route('user.bookings') }}" class="flex items-center space-x-3 bg-green-500 text-white px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                    </svg>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('user.reservations') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white hover:bg-slate-700 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Reservations</span>
                </a>
                <a href="{{ route('user.profile') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white hover:bg-slate-700 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                    <span>Profile</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-bold">Bookings</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" placeholder="Search bookings..." class="bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 pl-10 text-white placeholder-gray-400">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"/>
                            </svg>
                        </div>
                        <button class="bg-white text-slate-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                            Export
                        </button>
                    </div>
                </div>

                <!-- Book New EV Section -->
                <div class="bg-slate-800 rounded-2xl p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-6">Book New EV</h2>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Pickup Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Pickup Location</label>
                            <div class="relative">
                                <input type="text" placeholder="Enter pickup location" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 pl-10">
                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Date</label>
                            <input type="text" placeholder="mm/dd/yyyy" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-400">
                        </div>

                        <!-- Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Time</label>
                            <input type="text" placeholder="--:-- --" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-400">
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Duration</label>
                            <select class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white">
                                <option>Select duration</option>
                                <option>1 Hour</option>
                                <option>2 Hours</option>
                                <option>3 Hours</option>
                                <option>4 Hours</option>
                                <option>All Day</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <div class="mt-6">
                        <button class="bg-green-500 hover:bg-green-600 text-white font-medium px-6 py-3 rounded-lg transition-colors">
                            Search Available EVs
                        </button>
                    </div>
                </div>

                <!-- Upcoming Bookings Section -->
                <div class="bg-slate-800 rounded-2xl p-6">
                    <!-- Tab Navigation -->
                    <div class="flex space-x-1 mb-6">
                        <button class="bg-green-500 text-white px-6 py-2 rounded-lg font-medium">
                            Upcoming Bookings
                        </button>
                        <button class="text-gray-400 hover:text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Booking History
                        </button>
                    </div>

                    <h3 class="text-lg font-semibold mb-6">Upcoming Bookings</h3>

                    <!-- Booking Item -->
                    <div class="bg-slate-700 rounded-xl p-6 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-slate-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">Tesla Model S</h4>
                                    <p class="text-gray-400 text-sm">Business District</p>
                                    <p class="text-gray-400 text-sm">May 15, 2024 • 3:00 PM - 5:00 PM</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">Confirmed</span>
                                <span class="text-xl font-bold">$50.00</span>
                                <div class="flex space-x-2">
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        View Details
                                    </button>
                                    <button class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        Modify
                                    </button>
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State (if no bookings) -->
                    <!--
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-300 mb-2">No upcoming bookings</h3>
                        <p class="text-gray-400 text-sm mb-6">You don't have any upcoming EV bookings scheduled.</p>
                        <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            Book Your First EV
                        </button>
                    </div>
                    -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>
