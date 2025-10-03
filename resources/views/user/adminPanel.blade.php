<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="EVC Admin Dashboard - Manage electric vehicle charging stations, users, and bookings">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Admin Dashboard - EVC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Custom styles for fullscreen map */
        .map-container {
            height: calc(100vh - 80px);
        }

        .sidebar-collapsed {
            width: 70px !important;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-collapsed .sidebar-logo-text {
            display: none;
        }

        /* Normalize cursor behavior */
        * {
            cursor: default;
        }

        /* Only allow pointer cursor on specific interactive elements */
        button,
        a,
        input,
        select,
        textarea,
        .cursor-pointer,
        [onclick],
        [role="button"] {
            cursor: pointer !important;
        }

        /* Text cursor for text inputs */
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        textarea {
            cursor: text !important;
        }

        /* Disable hover scale animations to prevent cursor confusion */
        .no-scale-hover:hover {
            transform: none !important;
        }

        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script>
        // Safely set server data in global scope before Alpine.js initializes
        window.adminData = {
            totalUsers: {{ $totalUsers ?? 0 }},
            activeBookings: {{ $activeBookings ?? 0 }},
            totalStations: {{ $totalStations ?? 0 }},
            monthlyRevenue: {{ $monthlyRevenue ?? 0 }},
            users: @json($users ?? [])
        };

        // Admin panel data initialized
    </script>
</head>
<body class="bg-slate-900 text-white h-screen flex flex-col" x-data="{
    activeSection: '{{ $activeSection ?? "dashboard" }}',
    users: @json($users ?? []),
    totalUsers: {{ $totalUsers ?? 0 }},
    activeBookings: {{ $activeBookings ?? 0 }},
    totalStations: {{ $totalStations ?? 0 }},
    monthlyRevenue: {{ $monthlyRevenue ?? 0 }},
    init() {
        // Load server data safely
        this.loadServerData();
    },
    loadServerData() {
        try {
            // Get data from global window object for additional validation
            const serverData = window.adminData;

            if (serverData) {
                // Update with server data if available and different
                this.users = Array.isArray(serverData.users) ? serverData.users : this.users;
                this.totalUsers = Number(serverData.totalUsers) || this.totalUsers;
                this.activeBookings = Number(serverData.activeBookings) || this.activeBookings;
                this.totalStations = Number(serverData.totalStations) || this.totalStations;
                this.monthlyRevenue = Number(serverData.monthlyRevenue) || this.monthlyRevenue;
            }

            // Alpine.js initialized successfully - data loaded
        } catch (error) {
            console.error('Error in loadServerData:', error);
        }
    },
    stations: [
        {id: 1, name: 'Downtown Station', address: '123 Main St', lat: 40.7128, lng: -74.0060, status: 'active'},
        {id: 2, name: 'Mall Station', address: '456 Oak Ave', lat: 40.7589, lng: -73.9851, status: 'active'},
        {id: 3, name: 'Airport Station', address: '789 Pine Rd', lat: 40.6892, lng: -74.1445, status: 'maintenance'}
    ],
    newStation: {
        name: '',
        address: '',
        lat: '',
        lng: '',
        price: 25.00,
        status: 'active'
    },
    clearForm() {
        this.newStation = {
            name: '',
            address: '',
            lat: '',
            lng: '',
            price: 25.00,
            status: 'active'
        };
    },
    async addStation() {
        if (!this.newStation.lat || !this.newStation.lng) {
            alert('Please enter latitude and longitude coordinates!');
            return;
        }

        if (confirm(`Add new station '${this.newStation.name}'?`)) {
            try {
                const formData = new FormData();
                formData.append('name', this.newStation.name);
                formData.append('address', this.newStation.address);
                formData.append('latitude', this.newStation.lat);
                formData.append('longitude', this.newStation.lng);
                formData.append('price_per_hour', this.newStation.price);
                formData.append('status', this.newStation.status);
                formData.append('_token', document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'));

                const response = await fetch('/admin/stations', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert('Station added successfully!');
                    this.clearForm();

                    // Remove marker from map
                    if (currentMarker) {
                        currentMarker.setMap(null);
                        currentMarker = null;
                    }
                } else {
                    alert('Error: ' + (result.message || 'Failed to add station'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error occurred while adding station');
            }
        }
    },
    async refreshUsers() {
        try {
            // Redirect to users page to refresh data
            window.location.href = '/admin/users/page';
        } catch (error) {
            console.error('Error:', error);
            location.reload(); // Fallback to page reload
        }
    }
}">
    <!-- Header -->
    <header class="bg-slate-800 shadow-lg border-b border-slate-700 relative z-50">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Left side: EVC Logo + Text -->
            <div class="flex items-center space-x-3">
                <!-- EVC Logo + Text -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center relative">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11 1l-8 9h5v12h6V10h5L11 1z"/>
                        </svg>
                    </div>
                    <div class="text-green-500 font-bold text-2xl tracking-wider">EVC</div>
                </div>
            </div>

            <!-- Center: Navigation Links -->
            <nav class="flex items-center space-x-8">
                <a href="{{ route('admin.dashboard') }}"
                   :class="activeSection === 'dashboard' ? 'text-green-500 border-b-2 border-green-500' : 'text-gray-300 hover:text-white hover:border-gray-300'"
                   class="px-4 py-2 font-medium transition-colors border-b-2 border-transparent">
                    Dashboard
                </a>
                <a href="{{ route('admin.users.page') }}"
                   :class="activeSection === 'users' ? 'text-green-500 border-b-2 border-green-500' : 'text-gray-300 hover:text-white hover:border-gray-300'"
                   class="px-4 py-2 font-medium transition-colors border-b-2 border-transparent">
                    EVC Users
                </a>
                <a href="{{ route('admin.stations.page') }}"
                   class="px-4 py-2 font-medium transition-colors border-b-2 border-transparent text-gray-300 hover:text-white hover:border-gray-300">
                    Add Charging Station
                </a>
            </nav>

            <!-- Right side: Admin Profile -->
            <div class="flex items-center space-x-3">
                <!-- Admin Profile Dropdown -->
                <div class="relative" x-data="{ showDropdown: false }">
                    <button @click="showDropdown = !showDropdown"
                            class="flex items-center space-x-2 bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm transition-colors focus:outline-none">
                        <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">A</span>
                        </div>
                        <div class="text-left">
                            <div class="font-medium">Admin</div>
                            <div class="text-xs text-gray-400">admin@evc.com</div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{'rotate-180': showDropdown}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="showDropdown"
                         @click.away="showDropdown = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-600 rounded-md shadow-lg z-50">
                        <div class="py-1">
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-slate-700 hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                Profile Settings
                            </a>
                            <hr class="border-slate-600">
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex-1 relative">
        <!-- Dashboard Overview (Default) -->
        <div x-show="activeSection === 'dashboard'" class="w-full h-full flex">
            <!-- Dashboard Content -->
            <div class="flex-1 bg-slate-700 p-6 overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-white mb-2">Admin Dashboard</h1>
                        <p class="text-gray-400">Manage your EV charging network</p>
                    </div>

                    <!-- Stats Cards -->
                    <div class="flex flex-wrap gap-6 mb-8 justify-between">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 rounded-xl text-white flex-1 min-w-[200px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-100 text-sm">Total Users</p>
                                    <p class="text-3xl font-bold" x-text="totalUsers">{{ $totalUsers ?? 0 }}</p>
                                </div>
                                <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl text-white flex-1 min-w-[200px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-100 text-sm">Active Bookings</p>
                                    <p class="text-3xl font-bold" x-text="activeBookings">{{ $activeBookings ?? 0 }}</p>
                                </div>
                                <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6 rounded-xl text-white flex-1 min-w-[200px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-100 text-sm">Total Stations</p>
                                    <p class="text-3xl font-bold" x-text="totalStations">{{ $totalStations ?? 0 }}</p>
                                </div>
                                <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 rounded-xl text-white flex-1 min-w-[200px]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-yellow-100 text-sm">Monthly Revenue</p>
                                    <p class="text-3xl font-bold">$<span x-text="monthlyRevenue">{{ $monthlyRevenue ?? 0 }}</span></p>
                                </div>
                                <div class="bg-yellow-400 bg-opacity-30 p-3 rounded-full">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-Time System Status -->
                    <div class="bg-slate-800 rounded-xl p-6 mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-white">Real-Time System Status</h2>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-green-400 text-sm">WebSocket Active</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-slate-700 p-4 rounded-lg">
                                <h3 class="text-white font-medium mb-2">Broadcasting Status</h3>
                                <p class="text-gray-400 text-sm">Real-time updates enabled</p>
                                <p class="text-green-400 text-sm mt-1">✅ Reverb Server Running</p>
                            </div>
                            <div class="bg-slate-700 p-4 rounded-lg">
                                <h3 class="text-white font-medium mb-2">User Connections</h3>
                                <p class="text-gray-400 text-sm">Active WebSocket connections</p>
                                <p class="text-blue-400 text-sm mt-1">📡 Channel: stations-channel</p>
                            </div>
                            <div class="bg-slate-700 p-4 rounded-lg">
                                <h3 class="text-white font-medium mb-2">Last Update</h3>
                                <p class="text-gray-400 text-sm">Station data synchronized</p>
                                <p class="text-purple-400 text-sm mt-1" x-text="`⏰ ${new Date().toLocaleTimeString()}`"></p>
                            </div>
                        </div>
                        <div class="mt-4 p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg">
                            <h3 class="text-blue-300 font-medium mb-2">💡 How Real-Time Updates Work:</h3>
                            <ul class="text-gray-300 text-sm space-y-1">
                                <li>• When you add a station via "Add Charging Station", it's broadcast instantly</li>
                                <li>• User maps update automatically without page refresh</li>
                                <li>• WebSocket technology ensures sub-second latency</li>
                                <li>• Fallback to 30-second polling if WebSocket fails</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-slate-800 rounded-xl p-6 mb-8">
                        <h2 class="text-xl font-bold text-white mb-4">Recent Users</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-slate-700">
                                        <th class="text-left py-3 px-4 text-gray-300 font-medium">Name</th>
                                        <th class="text-left py-3 px-4 text-gray-300 font-medium">Email</th>
                                        <th class="text-left py-3 px-4 text-gray-300 font-medium">Joined</th>
                                        <th class="text-left py-3 px-4 text-gray-300 font-medium">Bookings</th>
                                        <th class="text-left py-3 px-4 text-gray-300 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($users) && $users->count() > 0)
                                        @foreach($users as $user)
                                        <tr class="border-b border-slate-700 hover:bg-slate-700">
                                            <td class="py-3 px-4 text-white">{{ $user->name }}</td>
                                            <td class="py-3 px-4 text-gray-300">{{ $user->email }}</td>
                                            <td class="py-3 px-4 text-gray-300">{{ $user->created_at->format('M d, Y') }}</td>
                                            <td class="py-3 px-4 text-gray-300">{{ $user->bookings_count ?? 0 }}</td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-500 text-white">Active</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-gray-400">No users found</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- EVC Users Section -->
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-blue-100 text-sm">Active Bookings</p>
                                        <p class="text-3xl font-bold" x-text="activeBookings" x-cloak>{{ $activeBookings ?? 0 }}</p>
                                    </div>
                                    <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-xl text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-purple-100 text-sm">Total Stations</p>
                                        <p class="text-3xl font-bold" x-text="totalStations" x-cloak>{{ $totalStations ?? 0 }}</p>
                                    </div>
                                    <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 rounded-xl text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-yellow-100 text-sm">Monthly Revenue</p>
                                        <p class="text-3xl font-bold">$<span x-text="monthlyRevenue" x-cloak>{{ $monthlyRevenue ?? 0 }}</span></p>
                                    </div>
                                    <div class="bg-yellow-400 bg-opacity-30 p-3 rounded-full">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

            <!-- EVC Users Section -->
            <div x-show="activeSection === 'users'" class="w-full h-full">
                <div class="bg-slate-700 p-6 h-full overflow-y-auto">
                    <div class="max-w-7xl mx-auto">
                        <div class="bg-slate-900/50 backdrop-blur-sm rounded-3xl border border-slate-600 shadow-2xl p-8">
                            <div class="flex items-center justify-between mb-8">
                                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-cyan-500 bg-clip-text text-transparent flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                    EVC Users
                                </h1>
                                <div class="flex items-center space-x-4">
                                    <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 px-6 py-3 rounded-xl border border-blue-500/30">
                                        <p class="text-blue-300 font-semibold">Total Users: <span class="text-white text-xl" x-text="users.length" x-cloak>{{ count($users ?? []) }}</span></p>
                                    </div>
                                    <button @click="refreshUsers()" class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 px-6 py-3 rounded-xl text-white font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                        </svg>
                                        Refresh Data
                                    </button>
                                </div>
                            </div>

                            <!-- User Table -->
                            <div class="bg-slate-800/30 rounded-2xl border border-slate-700/50 overflow-hidden shadow-xl">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gradient-to-r from-slate-800 to-slate-700">
                                            <tr>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider border-b border-slate-600">
                                                    #
                                                </th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider border-b border-slate-600">
                                                    Username
                                                </th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider border-b border-slate-600">
                                                    Email
                                                </th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider border-b border-slate-600">
                                                    Status
                                                </th>
                                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-300 uppercase tracking-wider border-b border-slate-600">
                                                    Joined
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-700/50">
                                            @if(isset($users) && $users->count() > 0)
                                                @foreach($users as $index => $user)
                                                <tr class="hover:bg-slate-700/30 transition-all duration-300">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-200">
                                                        {{ $index + 1 }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                                                    <span class="text-white font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-slate-300">{{ $user->email }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($user->email_verified_at)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                                Verified
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                                </svg>
                                                                Pending
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                                        {{ $user->created_at->format('M d, Y') }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="px-6 py-12 text-center">
                                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                                            <svg class="w-12 h-12 mb-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                                            </svg>
                                                            <p class="text-lg font-medium">No users found</p>
                                                            <p class="text-sm">Users will appear here once they register</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Add Charging Station Section -->
            <div x-show="activeSection === 'stations'" class="w-full h-full">
                <div class="bg-slate-700 p-6 h-full overflow-y-auto">
                    <div class="max-w-7xl mx-auto h-full">
                        <div class="bg-slate-900/50 backdrop-blur-sm rounded-3xl border border-slate-600 shadow-2xl p-8 h-full flex flex-col">
                            <div class="flex items-center justify-between mb-8">
                                <h1 class="text-3xl font-bold bg-gradient-to-r from-green-400 to-emerald-500 bg-clip-text text-transparent flex items-center">
                                    <svg class="w-8 h-8 mr-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                    Add Charging Station
                                </h1>
                                <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 px-6 py-3 rounded-xl border border-green-500/30">
                                    <p class="text-green-300 font-semibold">Click on map to add station</p>
                                </div>
                            </div>

                            <div class="flex-1 flex gap-6">
                                <!-- Station Form -->
                                <div class="w-1/3 bg-slate-800/50 rounded-2xl border border-slate-700/50 p-6">
                                    <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                                        <svg class="w-6 h-6 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                        </svg>
                                        Station Details
                                    </h3>

                                    <form @submit.prevent="addStation()" class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-2">Station Name *</label>
                                            <input x-model="newStation.name" type="text" required
                                                   placeholder="Enter station name"
                                                   class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-2">Address *</label>
                                            <textarea x-model="newStation.address" required rows="3"
                                                      placeholder="Enter complete address"
                                                      class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300 resize-none"></textarea>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-2">Latitude *</label>
                                                <input x-model="newStation.lat" type="number" step="any" required
                                                       placeholder="Enter latitude (e.g., 40.7128)"
                                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-300 mb-2">Longitude *</label>
                                                <input x-model="newStation.lng" type="number" step="any" required
                                                       placeholder="Enter longitude (e.g., -74.0060)"
                                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-2">Price/Hour ($) *</label>
                                            <input x-model="newStation.price" type="number" min="0" step="0.01" required
                                                   placeholder="e.g. 25.00"
                                                   class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                                            <select x-model="newStation.status" class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300">
                                                <option value="active">Active</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="offline">Offline</option>
                                            </select>
                                        </div>

                                        <div class="flex space-x-4 pt-4">
                                            <button type="button" @click="clearForm()"
                                                    class="flex-1 px-6 py-3 bg-slate-600 hover:bg-slate-500 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105">
                                                Clear Form
                                            </button>
                                            <button type="submit"
                                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                                                Add Station
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Interactive Google Map -->
                                <div class="flex-1 bg-slate-800/30 rounded-2xl border border-slate-700/50 overflow-hidden">
                                    <div class="bg-slate-800 px-6 py-4 border-b border-slate-700/50">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-semibold text-white">📍 Click Map to Add Station</h3>
                                            <div class="flex items-center space-x-2 text-sm text-green-400">
                                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                                <span>Interactive Mode</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Google Map Container -->
                                    <div id="admin-map" class="w-full bg-slate-700" style="height: 600px;">
                                        <!-- Map will load here -->
                                        <div class="w-full h-full flex items-center justify-center bg-slate-700">
                                            <div class="text-center text-gray-400">
                                                <div class="animate-spin w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                                                <p class="text-sm">Loading Google Map...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Instructions -->
                                    <div class="bg-slate-800 px-6 py-3 border-t border-slate-700/50">
                                        <div class="flex items-center text-sm text-slate-300">
                                            <svg class="w-4 h-4 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            <span><strong>Instructions:</strong> Click anywhere on the map to place a charging station. Coordinates and address will auto-fill in the form.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps API for Admin Station Addition -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initAddStationMap&libraries=places" async defer></script>

    <script>
        // STEP 1: Initialize Google Maps for Adding Stations
        let addStationMap = null;
        let currentMarker = null;

        // STEP 2: Initialize Google Map in Admin Panel
        function initAddStationMap() {
            console.log('🗺️ Initializing Admin Google Map for station addition...');

            // Default center (you can change this to your city)
            const defaultCenter = { lat: 40.7128, lng: -74.0060 }; // New York

            addStationMap = new google.maps.Map(document.getElementById("admin-map"), {
                zoom: 12,
                center: defaultCenter,
                styles: [
                    // Dark theme for admin panel
                    {
                        "elementType": "geometry",
                        "stylers": [{ "color": "#1a202c" }]
                    },
                    {
                        "elementType": "labels.text.stroke",
                        "stylers": [{ "color": "#1a202c" }]
                    },
                    {
                        "elementType": "labels.text.fill",
                        "stylers": [{ "color": "#8b949e" }]
                    },
                    {
                        "featureType": "road",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#374151" }]
                    },
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#0f172a" }]
                    }
                ]
            });

            // STEP 3: Add Click Listener - THE MAGIC HAPPENS HERE! ✨
            addStationMap.addListener('click', function(event) {
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();
                console.log('📍 Admin clicked map at:', lat, lng);

                // Remove previous marker if exists
                if (currentMarker) {
                    currentMarker.setMap(null);
                }

                // Add new marker at clicked location
                currentMarker = new google.maps.Marker({
                    position: event.latLng,
                    map: addStationMap,
                    title: 'New Station Location',
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(`
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                                <circle cx="16" cy="16" r="14" fill="#10B981" stroke="white" stroke-width="2"/>
                                <path d="M16 8l-2 8h4l-2 8" stroke="white" stroke-width="2" fill="none"/>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(32, 32)
                    }
                });

                // STEP 4: Auto-fill form with coordinates
                fillStationForm(lat, lng);

                // STEP 5: Get address using reverse geocoding (optional but nice!)
                getAddressFromCoordinates(lat, lng);
            });

            console.log('✅ Admin Google Map ready! Click anywhere to add a station.');
        }

        // STEP 6: Auto-fill the station form with clicked coordinates
        function fillStationForm(lat, lng) {
            // Get the Alpine.js component and update coordinates
            const adminComponent = document.querySelector('[x-data]').__x;
            if (adminComponent && adminComponent.$data) {
                adminComponent.$data.newStation.lat = lat.toFixed(6);
                adminComponent.$data.newStation.lng = lng.toFixed(6);

                console.log('📝 Form auto-filled with coordinates:', lat, lng);

                // Show success message
                showTemporaryMessage('📍 Location selected! Coordinates filled automatically.');
            }
        }

        // STEP 7: Convert coordinates to human-readable address
        function getAddressFromCoordinates(lat, lng) {
            const geocoder = new google.maps.Geocoder();
            const latlng = { lat: lat, lng: lng };

            geocoder.geocode({ location: latlng }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const address = results[0].formatted_address;

                    // Auto-fill address in form
                    const adminComponent = document.querySelector('[x-data]').__x;
                    if (adminComponent && adminComponent.$data) {
                        adminComponent.$data.newStation.address = address;
                        console.log('🏠 Address auto-filled:', address);
                    }
                } else {
                    console.log('⚠️ Could not get address for coordinates');
                }
            });
        }

        // STEP 8: Helper function to show temporary messages
        function showTemporaryMessage(message) {
            // Create temporary notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }
                const lat = event.latLng.lat();
                const lng = event.latLng.lng();

                // Update form fields using Alpine.js and DOM fallback
                try {
                    const bodyElement = document.querySelector('body[x-data]');
                    if (bodyElement && bodyElement._x_dataStack && bodyElement._x_dataStack[0]) {
                        const appData = bodyElement._x_dataStack[0];
                        appData.newStation.lat = lat.toFixed(6);
                        appData.newStation.lng = lng.toFixed(6);
                        console.log('Updated coordinates via Alpine.js:', { lat: lat.toFixed(6), lng: lng.toFixed(6) });
                    } else {
                        // Fallback: Update DOM directly
                        const latInput = document.querySelector('input[x-model="newStation.lat"]');
                        const lngInput = document.querySelector('input[x-model="newStation.lng"]');
                        if (latInput && lngInput) {
                            latInput.value = lat.toFixed(6);
                            lngInput.value = lng.toFixed(6);
                            latInput.dispatchEvent(new Event('input', { bubbles: true }));
                            lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                            console.log('Updated coordinates via DOM:', { lat: lat.toFixed(6), lng: lng.toFixed(6) });
                        } else {
                            console.error('Coordinate input fields not found');
                        }
                    }
                } catch (error) {
                    console.error('Error updating coordinates:', error);
                }

                // Remove existing marker
                if (currentMarker) {
                    currentMarker.setMap(null);
                }

                // Add new marker
                currentMarker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: addStationMap,
                    title: 'New Station Location',
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(`
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                                <circle cx="16" cy="16" r="12" fill="#10b981" stroke="#ffffff" stroke-width="2"/>
                                <path d="M16 8l-6 8h4v8h4v-8h4z" fill="#ffffff"/>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(32, 32)
                    }
                });

                // Reverse geocode to get address
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        appData.newStation.address = results[0].formatted_address;
                    }
                });

                // Create info window
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="color: #1f2937; padding: 8px; max-width: 200px;">
                            <h3 style="font-weight: bold; margin: 0 0 8px 0; color: #10b981;">New Station Location</h3>
                            <p style="margin: 0; font-size: 12px;">Lat: ${lat.toFixed(6)}</p>
                            <p style="margin: 0; font-size: 12px;">Lng: ${lng.toFixed(6)}</p>
                            <p style="margin: 4px 0 0 0; font-size: 11px; color: #6b7280;">Fill out the form to add this station</p>
                        </div>
                    `
                });

                currentMarker.addListener('click', () => {
                    infoWindow.open(addStationMap, currentMarker);
                });
            });

            // Add existing stations as markers
            const existingStations = [
                { id: 1, name: 'Downtown Station', lat: 40.7128, lng: -74.0060, status: 'active' },
                { id: 2, name: 'Mall Station', lat: 40.7589, lng: -73.9851, status: 'active' },
                { id: 3, name: 'Airport Station', lat: 40.6892, lng: -74.1445, status: 'maintenance' }
            ];

            existingStations.forEach(station => {
                const marker = new google.maps.Marker({
                    position: { lat: station.lat, lng: station.lng },
                    map: addStationMap,
                    title: station.name,
                    icon: {
                        url: 'data:image/svg+xml;base64,' + btoa(`
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
                                <circle cx="14" cy="14" r="10" fill="${station.status === 'active' ? '#3b82f6' : station.status === 'maintenance' ? '#f59e0b' : '#ef4444'}" stroke="#ffffff" stroke-width="2"/>
                                <path d="M14 6l-5 7h3v7h4v-7h3z" fill="#ffffff"/>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(28, 28)
                    }
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="color: #1f2937; padding: 8px;">
                            <h3 style="font-weight: bold; margin: 0 0 8px 0;">${station.name}</h3>
                            <p style="margin: 0; font-size: 14px;">Status: ${station.status}</p>
                        </div>
                    `
                });

                marker.addListener('click', () => {
                    infoWindow.open(addStationMap, marker);
                });
            });
        }

        // Google Maps initialization disabled - API removed
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Google Maps functionality has been disabled');
        });

        // Map initialization disabled for stations section
        document.addEventListener('alpine:initialized', () => {
            console.log('Map functionality disabled');
        });
    </script>
</body>
</html>
