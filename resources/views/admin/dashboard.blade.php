<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - EVC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-collapsed {
            width: 70px !important;
        }
        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        /* Real-time dashboard animations */
        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out forwards;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-slate-900 text-white h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-slate-800 shadow-lg border-b border-slate-700">
        <div class="flex items-center justify-between px-6 py-4">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11 1l-8 9h5v12h6V10h5L11 1z"/>
                    </svg>
                </div>
                <div class="text-green-500 font-bold text-2xl tracking-wider">EVC Admin</div>
            </div>

            <!-- Navigation -->
            <nav class="flex items-center space-x-8">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-green-500 border-b-2 border-green-500 font-medium">
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors">
                    EVC Users
                </a>
                <a href="{{ route('admin.bookings') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors relative">
                    Booking Management
                    @php
                        $pendingCount = isset($pendingBookings) ? $pendingBookings : App\Models\Booking::where('status', 'pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.stations') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors">
                    Add Charging Station
                </a>
            </nav>

            <!-- Admin Profile -->
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 bg-slate-700 px-4 py-2 rounded-lg">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">A</span>
                    </div>
                    <div class="text-left">
                        <div class="font-medium">Admin</div>
                        <div class="text-xs text-gray-400">admin@evc.com</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-10 h-10 bg-red-500/20 hover:bg-red-500/30 border border-red-500/40 rounded-full flex items-center justify-center text-red-400 hover:text-red-300 transition-all duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 bg-slate-700 p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Admin Dashboard</h1>
                <p class="text-gray-400">Overview of your EV charging network</p>
            </div>

            <!-- Stats Cards with Real-time Updates -->
            <div x-data="dashboardManager" class="space-y-6">
                <!-- Stats Cards -->
                <div class="flex flex-wrap gap-6 mb-8 justify-between">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-6 rounded-xl text-white flex-1 min-w-[200px]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm">Total Users</p>
                                <p class="text-3xl font-bold" data-stat="totalUsers" x-text="stats.totalUsers"></p>
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
                                <p class="text-3xl font-bold" data-stat="activeBookings" x-text="stats.activeBookings"></p>
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
                                <p class="text-3xl font-bold" data-stat="totalStations" x-text="stats.totalStations"></p>
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
                                <p class="text-3xl font-bold" data-stat="monthlyRevenue" x-text="formatCurrency(stats.monthlyRevenue)"></p>
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

                <!-- Real-time Notifications -->
                <div class="fixed top-20 right-4 z-50 space-y-2" x-show="notifications.length > 0">
                    <template x-for="notification in notifications" :key="notification.id">
                        <div class="bg-slate-800 border-l-4 border-green-500 p-4 rounded-lg shadow-lg min-w-[300px] animate-slide-in-right"
                             :class="{
                                'border-green-500 bg-green-900/20': notification.type === 'success',
                                'border-blue-500 bg-blue-900/20': notification.type === 'info',
                                'border-red-500 bg-red-900/20': notification.type === 'error'
                             }">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white text-sm font-medium" x-text="notification.message"></p>
                                    <p class="text-gray-400 text-xs" x-text="notification.timestamp"></p>
                                </div>
                                <button @click="removeNotification(notification.id)" class="text-gray-400 hover:text-white">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                    </path>
                                </svg>
                            </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Connection Status -->
                <div class="mb-4 text-right">
                    <span class="text-xs text-gray-400">Last update: </span>
                    <span class="text-xs" :class="isConnected ? 'text-green-400' : 'text-red-400'" x-text="lastUpdate || 'Never'"></span>
                    <span class="ml-2 w-2 h-2 rounded-full inline-block" :class="isConnected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></span>
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
                        <p class="text-purple-400 text-sm mt-1">⏰ <span id="current-time"></span></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('admin.users') }}" class="bg-slate-800 p-6 rounded-xl hover:bg-slate-750 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 616 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-medium">Manage Users</h3>
                            <p class="text-gray-400 text-sm">View and manage EVC users</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.stations') }}" class="bg-slate-800 p-6 rounded-xl hover:bg-slate-750 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-medium">Add Station</h3>
                            <p class="text-gray-400 text-sm">Add new charging stations</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.bookings') }}" class="bg-slate-800 p-6 rounded-xl hover:bg-slate-750 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h3 class="text-white font-medium">Booking Management</h3>
                                @if($pendingCount > 0)
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingCount }} pending</span>
                                @endif
                            </div>
                            <p class="text-gray-400 text-sm">Approve and manage reservations</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Real-time dashboard management
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardManager', () => ({
                stats: {
                    totalUsers: {{ $totalUsers ?? 0 }},
                    activeBookings: {{ $activeBookings ?? 0 }},
                    totalStations: {{ $totalStations ?? 0 }},
                    monthlyRevenue: {{ $monthlyRevenue ?? 0 }},
                    todayRevenue: 0,
                    totalBookings: 0
                },
                isConnected: false,
                lastUpdate: null,
                notifications: [],

                init() {
                    this.startRealTimeUpdates();
                    this.initWebSocket();
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },

                async startRealTimeUpdates() {
                    // Fetch stats every 30 seconds
                    await this.fetchStats();
                    setInterval(async () => {
                        await this.fetchStats();
                    }, 30000);
                },

                async fetchStats() {
                    try {
                        const response = await fetch('/admin/api/dashboard-stats', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.data) {
                                this.updateStats(data.data);
                                this.lastUpdate = new Date().toLocaleTimeString();
                            }
                        }
                    } catch (error) {
                        console.error('Failed to fetch dashboard stats:', error);
                    }
                },

                updateStats(newStats) {
                    // Animate changes
                    Object.keys(newStats).forEach(key => {
                        if (this.stats.hasOwnProperty(key) && this.stats[key] !== newStats[key]) {
                            this.animateStatChange(key, this.stats[key], newStats[key]);
                        }
                    });

                    this.stats = { ...this.stats, ...newStats };
                },

                animateStatChange(statKey, oldValue, newValue) {
                    const element = document.querySelector(`[data-stat="${statKey}"]`);
                    if (element) {
                        element.classList.add('animate-pulse');
                        setTimeout(() => {
                            element.classList.remove('animate-pulse');
                        }, 1000);
                    }

                    // Show notification for significant changes
                    if (statKey === 'activeBookings' && newValue > oldValue) {
                        this.addNotification(`New booking! Active bookings: ${newValue}`, 'success');
                    }
                    if (statKey === 'monthlyRevenue' && newValue > oldValue) {
                        const increase = (newValue - oldValue).toFixed(2);
                        this.addNotification(`Revenue increased by $${increase}`, 'success');
                    }
                },

                initWebSocket() {
                    if (typeof window.Echo !== 'undefined') {
                        console.log('🚀 Connecting to WebSocket for real-time updates...');

                        // Listen to admin dashboard channel
                        window.Echo.channel('admin-dashboard')
                            .listen('.booking.created', (event) => {
                                console.log('📊 New booking created:', event);
                                this.handleBookingUpdate(event);
                            });

                        // Listen to bookings channel for general updates
                        window.Echo.channel('bookings-channel')
                            .listen('.booking.updated', (event) => {
                                console.log('🔄 Booking updated:', event);
                                this.fetchStats(); // Refresh all stats
                            });

                        this.isConnected = true;
                        console.log('✅ WebSocket connected for admin dashboard');
                    } else {
                        console.warn('⚠️ Laravel Echo not available. Real-time updates disabled.');
                    }
                },

                handleBookingUpdate(event) {
                    // Update stats from the event
                    if (event.stats) {
                        this.updateStats(event.stats);
                    }

                    // Show notification
                    this.addNotification(event.message || 'New booking created', 'info');
                },

                addNotification(message, type = 'info') {
                    const notification = {
                        id: Date.now(),
                        message: message,
                        type: type,
                        timestamp: new Date().toLocaleTimeString()
                    };

                    this.notifications.unshift(notification);

                    // Keep only last 5 notifications
                    if (this.notifications.length > 5) {
                        this.notifications = this.notifications.slice(0, 5);
                    }

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        this.removeNotification(notification.id);
                    }, 5000);
                },

                removeNotification(id) {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                },

                updateTime() {
                    const timeElement = document.getElementById('current-time');
                    if (timeElement) {
                        timeElement.textContent = new Date().toLocaleTimeString();
                    }
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(amount || 0);
                }
            }));
        });
    </script>
</body>
</html>
