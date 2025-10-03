<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - EVC Project</title>
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
                <a href="{{ route('user.bookings') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white hover:bg-slate-700 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                    </svg>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('user.reservations') }}" class="flex items-center space-x-3 bg-green-500 text-white px-4 py-3 rounded-lg">
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
                    <h1 class="text-3xl font-bold">Reservations</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" placeholder="Search reservations..." class="bg-slate-800 border border-slate-600 rounded-lg px-4 py-2 pl-10 text-white placeholder-gray-400">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"/>
                            </svg>
                        </div>
                        <button class="bg-white text-slate-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                            Export
                        </button>
                        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            + New Reservation
                        </button>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="bg-slate-800 rounded-2xl p-6">
                    <div class="flex space-x-1 mb-6">
                        <button class="bg-green-500 text-white px-6 py-2 rounded-lg font-medium">
                            Active Reservations
                        </button>
                        <button class="text-gray-400 hover:text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Completed
                        </button>
                        <button class="text-gray-400 hover:text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            Cancelled
                        </button>
                    </div>

                    <h3 class="text-lg font-semibold mb-6">Active Reservations</h3>

                    <!-- Reservation Item 1 -->
                    <div class="bg-slate-700 rounded-xl p-6 mb-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">Tesla Model S</h4>
                                    <p class="text-gray-400 text-sm">ID: RES001</p>
                                </div>
                            </div>
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">Confirmed</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Location</h5>
                                <p class="font-medium">Downtown Station</p>
                                <p class="text-sm text-gray-400">ST-001</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Date & Time</h5>
                                <p class="font-medium">May 15, 2024</p>
                                <p class="text-sm text-gray-400">10:00 AM - 12:00 PM</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Charging Power</h5>
                                <p class="font-medium">150kW</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Estimated Cost</h5>
                                <p class="font-bold text-xl">$45.00</p>
                            </div>
                        </div>

                        <div class="flex space-x-3 mt-6">
                            <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                View Details
                            </button>
                            <button class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Modify
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Reservation Item 2 -->
                    <div class="bg-slate-700 rounded-xl p-6 mb-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">Tesla Model 3</h4>
                                    <p class="text-gray-400 text-sm">ID: RES002</p>
                                </div>
                            </div>
                            <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-medium">Pending</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Location</h5>
                                <p class="font-medium">Mall Complex</p>
                                <p class="text-sm text-gray-400">ST-002</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Date & Time</h5>
                                <p class="font-medium">May 16, 2024</p>
                                <p class="text-sm text-gray-400">2:00 PM - 4:00 PM</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Charging Power</h5>
                                <p class="font-medium">120kW</p>
                            </div>
                            <div>
                                <h5 class="text-sm font-medium text-gray-400 mb-1">Estimated Cost</h5>
                                <p class="font-bold text-xl">$35.00</p>
                            </div>
                        </div>

                        <div class="flex space-x-3 mt-6">
                            <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                View Details
                            </button>
                            <button class="bg-slate-600 hover:bg-slate-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Modify
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
