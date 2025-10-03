<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
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

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        textarea {
            cursor: text !important;
        }

        .no-scale-hover:hover {
            transform: none !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    <script>
        window.adminData = {
            totalUsers: {{ $totalUsers ?? 0 }},
            activeBookings: {{ $activeBookings ?? 0 }},
            totalStations: {{ $totalStations ?? 0 }},
            monthlyRevenue: {{ $monthlyRevenue ?? 0 }},
            users: @json($users ?? [])
        };
        console.log('Admin panel loaded successfully');
    </script>
</head>
<body class="bg-slate-900 text-white h-screen flex flex-col" x-data="{
    activeSection: '{{ $activeSection ?? 'dashboard' }}',
    users: @json($users ?? []),
    totalUsers: {{ $totalUsers ?? 0 }},
    activeBookings: {{ $activeBookings ?? 0 }},
    totalStations: {{ $totalStations ?? 0 }},
    monthlyRevenue: {{ $monthlyRevenue ?? 0 }},
    init() {
        this.loadServerData();
    },
    loadServerData() {
        try {
            const serverData = window.adminData;
            if (serverData) {
                this.users = Array.isArray(serverData.users) ? serverData.users : this.users;
                this.totalUsers = Number(serverData.totalUsers) || this.totalUsers;
                this.activeBookings = Number(serverData.activeBookings) || this.activeBookings;
                this.totalStations = Number(serverData.totalStations) || this.totalStations;
                this.monthlyRevenue = Number(serverData.monthlyRevenue) || this.monthlyRevenue;
            }
            console.log('Alpine.js initialized successfully');
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
            alert('Please select a location on the map first!');
            return;
        }
        if (confirm('Add new station "' + this.newStation.name + '"?')) {
            try {
                const formData = new FormData();
                formData.append('name', this.newStation.name);
                formData.append('address', this.newStation.address);
                formData.append('latitude', this.newStation.lat);
                formData.append('longitude', this.newStation.lng);
                formData.append('price_per_hour', this.newStation.price);
                formData.append('status', this.newStation.status);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
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
            window.location.href = '/admin/users/page';
        } catch (error) {
            console.error('Error:', error);
            location.reload();
        }
    },
    stationForm: {
        name: '',
        type: '',
        power: '',
        ports: '',
        address: '',
        city: '',
        state: '',
        latitude: '',
        longitude: '',
        price: '',
        status: '',
        amenities: [],
        description: ''
    },
    isSubmitting: false,
    showSuccessMessage: false,
    showErrorMessage: false,
    errorMessage: '',
    async submitStation() {
        this.isSubmitting = true;
        this.showSuccessMessage = false;
        this.showErrorMessage = false;

        try {
            // Basic validation
            const requiredFields = ['name', 'type', 'power', 'ports', 'address', 'city', 'state', 'latitude', 'longitude', 'price', 'status'];
            for (const field of requiredFields) {
                if (!this.stationForm[field] || this.stationForm[field].toString().trim() === '') {
                    throw new Error(`Please fill in the ${field} field.`);
                }
            }

            // Ensure amenities is an array
            if (!Array.isArray(this.stationForm.amenities)) {
                this.stationForm.amenities = [];
            }

            const formData = new FormData();
            Object.keys(this.stationForm).forEach(key => {
                // Skip undefined or null values
                if (this.stationForm[key] != null) {
                    if (key === 'amenities') {
                        formData.append(key, JSON.stringify(this.stationForm[key]));
                    } else {
                        formData.append(key, this.stationForm[key].toString());
                    }
                }
            });

            // CSRF token handling with fallback
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement || !csrfTokenElement.getAttribute('content')) {
                throw new Error('CSRF token is missing. Please ensure the meta tag is correctly set.');
            }
            formData.append('_token', csrfTokenElement.getAttribute('content'));

            const response = await fetch('/admin/stations', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server did not return a valid JSON response.');
            }

            const result = await response.json();

            if (result.success) {
                this.showSuccessMessage = true;
                this.resetForm();
                setTimeout(() => {
                    this.showSuccessMessage = false;
                }, 5000);
            } else {
                this.errorMessage = result.message || 'Failed to create station';
                this.showErrorMessage = true;
                setTimeout(() => {
                    this.showErrorMessage = false;
                }, 5000);
            }
        } catch (error) {
            console.error('Error in submitStation:', error);
            this.errorMessage = error.message || 'Network error occurred while creating station';
            this.showErrorMessage = true;
            setTimeout(() => {
                this.showErrorMessage = false;
            }, 5000);
        } finally {
            this.isSubmitting = false;
        }
    },
    resetForm() {
        this.stationForm = {
            name: '',
            type: '',
            power: '',
            ports: '',
            address: '',
            city: '',
            state: '',
            latitude: '',
            longitude: '',
            price: '',
            status: '',
            amenities: [],
            description: ''
        };
    }
}">
    <!-- Header -->
    <header class="bg-slate-800 shadow-lg border-b border-slate-700 relative z-50">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Left side: EVC Logo + Text -->
            <div class="flex items-center space-x-3">
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
                <a href="#" @click.prevent="activeSection = 'stations'"
                   :class="activeSection === 'stations' ? 'text-green-500 border-b-2 border-green-500' : 'text-gray-300 hover:text-white hover:border-gray-300'"
                   class="px-4 py-2 font-medium transition-colors border-b-2 border-transparent">
                    Add Charging Station
                </a>
            </nav>

            <!-- Right side: Admin Profile -->
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">A</span>
                    </div>
                    <div class="text-left">
                        <div class="font-medium">Admin</div>
                        <div class="text-xs text-gray-400">admin@evc.com</div>
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

                    <!-- Stats Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Users Card -->
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 rounded-xl text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-100 text-sm">Total Users</p>
                                    <p class="text-3xl font-bold" x-text="totalUsers">{{ $totalUsers ?? 0 }}</p>
                                </div>
                                <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 616 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 715 5v1H1v-1a5 5 0 715-5z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Active Bookings Card -->
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl text-white">
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

                        <!-- Total Stations Card -->
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6 rounded-xl text-white">
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

                        <!-- Monthly Revenue Card -->
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-6 rounded-xl text-white">
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
        <div x-show="activeSection === 'users'" class="w-full h-full">
            <div class="bg-slate-700 p-6 h-full overflow-y-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="bg-slate-900/50 backdrop-blur-sm rounded-3xl border border-slate-600 shadow-2xl p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-cyan-500 bg-clip-text text-transparent flex items-center">
                                <svg class="w-8 h-8 mr-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 616 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 715 5v1H1v-1a5 5 0 715-5z"/>
                                </svg>
                                EVC Users
                            </h1>
                            <div class="flex items-center space-x-4">
                                <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 px-6 py-3 rounded-xl border border-blue-500/30">
                                    <p class="text-blue-300 font-semibold">Total Users: <span class="text-white text-xl" x-text="users.length">{{ count($users ?? []) }}</span></p>
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
                <div class="max-w-7xl mx-auto">
                    <div class="bg-slate-900/50 backdrop-blur-sm rounded-3xl border border-slate-600 shadow-2xl p-8">
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-green-400 to-emerald-500 bg-clip-text text-transparent flex items-center">
                                <svg class="w-8 h-8 mr-3 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11 1l-8 9h5v12h6V10h5L11 1z"/>
                                </svg>
                                Add Charging Station
                            </h1>
                            <p class="text-slate-300 mt-2">Create a new charging station for the EVC network</p>
                        </div>

                        <!-- Station Creation Form -->
                        <form @submit.prevent="submitStation()" class="space-y-8">
                            <!-- Basic Information -->
                            <div class="bg-slate-800/30 rounded-2xl border border-slate-700/50 p-6">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Basic Information
                                </h2>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <label for="station_name" class="block text-sm font-medium text-slate-300 mb-2">
                                            Station Name *
                                        </label>
                                        <input type="text" id="station_name" x-model="stationForm.name" required
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="Enter station name">
                                    </div>
                                    <div>
                                        <label for="station_type" class="block text-sm font-medium text-slate-300 mb-2">
                                            Station Type *
                                        </label>
                                        <select id="station_type" x-model="stationForm.type" required
                                                class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors">
                                            <option value="">Select station type</option>
                                            <option value="fast">Fast Charging (DC)</option>
                                            <option value="standard">Standard Charging (AC)</option>
                                            <option value="superfast">Super Fast Charging</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="station_power" class="block text-sm font-medium text-slate-300 mb-2">
                                            Power Output (kW) *
                                        </label>
                                        <input type="number" id="station_power" x-model="stationForm.power" required min="1" max="350"
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="e.g., 50">
                                    </div>
                                    <div>
                                        <label for="station_ports" class="block text-sm font-medium text-slate-300 mb-2">
                                            Number of Charging Ports *
                                        </label>
                                        <input type="number" id="station_ports" x-model="stationForm.ports" required min="1" max="20"
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="e.g., 4">
                                    </div>
                                </div>
                            </div>

                            <!-- Location Information -->
                            <div class="bg-slate-800/30 rounded-2xl border border-slate-700/50 p-6">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Location Details
                                </h2>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="lg:col-span-2">
                                        <label for="station_address" class="block text-sm font-medium text-slate-300 mb-2">
                                            Full Address *
                                        </label>
                                        <textarea id="station_address" x-model="stationForm.address" required rows="3"
                                                  class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors resize-none"
                                                  placeholder="Enter complete address including street, city, state, and postal code"></textarea>
                                    </div>
                                    <div>
                                        <label for="station_city" class="block text-sm font-medium text-slate-300 mb-2">
                                            City *
                                        </label>
                                        <input type="text" id="station_city" x-model="stationForm.city" required
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="Enter city name">
                                    </div>
                                    <div>
                                        <label for="station_state" class="block text-sm font-medium text-slate-300 mb-2">
                                            State/Province *
                                        </label>
                                        <input type="text" id="station_state" x-model="stationForm.state" required
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="Enter state or province">
                                    </div>
                                    <div>
                                        <label for="station_latitude" class="block text-sm font-medium text-slate-300 mb-2">
                                            Latitude *
                                        </label>
                                        <input type="number" id="station_latitude" x-model="stationForm.latitude" required step="any"
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="e.g., 37.7749">
                                    </div>
                                    <div>
                                        <label for="station_longitude" class="block text-sm font-medium text-slate-300 mb-2">
                                            Longitude *
                                        </label>
                                        <input type="number" id="station_longitude" x-model="stationForm.longitude" required step="any"
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="e.g., -122.4194">
                                    </div>
                                </div>

                                <!-- Map Placeholder -->
                                <div class="mt-6">
                                    <div class="bg-slate-700 rounded-xl p-8 text-center border-2 border-dashed border-slate-600">
                                        <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        <p class="text-slate-400 text-lg font-medium">Map Integration Available</p>
                                        <p class="text-slate-500 text-sm mt-2">Enter coordinates manually above or integrate with mapping service</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Details -->
                            <div class="bg-slate-800/30 rounded-2xl border border-slate-700/50 p-6">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Additional Details
                                </h2>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <label for="station_price" class="block text-sm font-medium text-slate-300 mb-2">
                                            Price per kWh ($) *
                                        </label>
                                        <input type="number" id="station_price" x-model="stationForm.price" required step="0.01" min="0"
                                               class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors"
                                               placeholder="e.g., 0.25">
                                    </div>
                                    <div>
                                        <label for="station_status" class="block text-sm font-medium text-slate-300 mb-2">
                                            Initial Status *
                                        </label>
                                        <select id="station_status" x-model="stationForm.status" required
                                                class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors">
                                            <option value="">Select status</option>
                                            <option value="available">Available</option>
                                            <option value="maintenance">Under Maintenance</option>
                                            <option value="testing">Testing Phase</option>
                                        </select>
                                    </div>
                                    <div class="lg:col-span-2">
                                        <label for="station_amenities" class="block text-sm font-medium text-slate-300 mb-2">
                                            Available Amenities
                                        </label>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
                                            <label class="flex items-center space-x-3 bg-slate-800 p-3 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors">
                                                <input type="checkbox" x-model="stationForm.amenities" value="wifi" class="rounded bg-slate-700 border-slate-600 text-green-500 focus:ring-green-500">
                                                <span class="text-sm text-slate-300">WiFi</span>
                                            </label>
                                            <label class="flex items-center space-x-3 bg-slate-800 p-3 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors">
                                                <input type="checkbox" x-model="stationForm.amenities" value="restroom" class="rounded bg-slate-700 border-slate-600 text-green-500 focus:ring-green-500">
                                                <span class="text-sm text-slate-300">Restroom</span>
                                            </label>
                                            <label class="flex items-center space-x-3 bg-slate-800 p-3 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors">
                                                <input type="checkbox" x-model="stationForm.amenities" value="restaurant" class="rounded bg-slate-700 border-slate-600 text-green-500 focus:ring-green-500">
                                                <span class="text-sm text-slate-300">Restaurant</span>
                                            </label>
                                            <label class="flex items-center space-x-3 bg-slate-800 p-3 rounded-lg cursor-pointer hover:bg-slate-700 transition-colors">
                                                <input type="checkbox" x-model="stationForm.amenities" value="shopping" class="rounded bg-slate-700 border-slate-600 text-green-500 focus:ring-green-500">
                                                <span class="text-sm text-slate-300">Shopping</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="lg:col-span-2">
                                        <label for="station_description" class="block text-sm font-medium text-slate-300 mb-2">
                                            Description
                                        </label>
                                        <textarea id="station_description" x-model="stationForm.description" rows="4"
                                                  class="w-full px-4 py-3 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-colors resize-none"
                                                  placeholder="Optional description about the charging station and its location"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-4">
                                <button type="button" @click="resetForm()"
                                        class="px-8 py-3 bg-slate-600 hover:bg-slate-500 text-white rounded-xl font-medium transition-colors">
                                    Reset Form
                                </button>
                                <button type="submit" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                                        class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center">
                                    <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="!isSubmitting" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="isSubmitting ? 'Creating Station...' : 'Create Charging Station'"></span>
                                </button>
                            </div>
                        </form>

                        <!-- Success/Error Messages -->
                        <div x-show="showSuccessMessage" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-4" class="mt-6 bg-green-500/20 border border-green-500/50 rounded-xl p-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-green-300 font-medium">Charging station created successfully!</p>
                            </div>
                        </div>

                        <div x-show="showErrorMessage" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-4" class="mt-6 bg-red-500/20 border border-red-500/50 rounded-xl p-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-red-300 font-medium" x-text="errorMessage">An error occurred while creating the station.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let addStationMap = null;
        let currentMarker = null;
        function initAddStationMap() {
            console.log('Google Maps functionality has been disabled');
            return;
        }
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Google Maps functionality has been disabled');
        });
        document.addEventListener('alpine:initialized', () => {
            console.log('Map functionality disabled');
        });
    </script>
</body>
</html>
