<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Management - EVC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .booking-card {
            transition: all 0.3s ease;
        }
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .status-pending { @apply bg-yellow-500/20 border-yellow-500 text-yellow-300; }
        .status-approved { @apply bg-green-500/20 border-green-500 text-green-300; }
        .status-denied { @apply bg-red-500/20 border-red-500 text-red-300; }
        .status-active { @apply bg-blue-500/20 border-blue-500 text-blue-300; }
        .status-completed { @apply bg-gray-500/20 border-gray-500 text-gray-300; }
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
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors">
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors">
                    EVC Users
                </a>
                <a href="{{ route('admin.bookings') }}" class="px-4 py-2 text-green-500 border-b-2 border-green-500 font-medium">
                    Booking Management
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
    <div x-data="bookingManager" class="flex-1 bg-slate-700 p-6">
        <div class="max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Booking Management</h1>
                <p class="text-gray-400">Approve, deny, and manage charging station bookings</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 rounded-xl text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm">Pending Requests</p>
                            <p class="text-3xl font-bold" x-text="stats.pending"></p>
                        </div>
                        <div class="bg-yellow-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-xl text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Approved</p>
                            <p class="text-3xl font-bold" x-text="stats.approved"></p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">Active Sessions</p>
                            <p class="text-3xl font-bold" x-text="stats.active"></p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-xl text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Total Revenue</p>
                            <p class="text-3xl font-bold" x-text="formatCurrency(stats.revenue)"></p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-slate-800 rounded-xl p-6 mb-8">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1">
                        <input type="text" x-model="searchQuery" @input="filterBookings()"
                               placeholder="Search by user name, station, or ID..."
                               class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-green-500 focus:outline-none">
                    </div>
                    <div>
                        <select x-model="statusFilter" @change="filterBookings()"
                                class="bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-green-500 focus:outline-none">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="denied">Denied</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <button @click="refreshBookings()"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Bookings List -->
            <div class="bg-slate-800 rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-6">Recent Booking Requests</h2>

                <div class="space-y-4" x-show="filteredBookings.length > 0">
                    <template x-for="booking in filteredBookings" :key="booking.id">
                        <div class="booking-card bg-slate-700 border-l-4 p-4 rounded-lg"
                             :class="`status-${booking.status}`">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-2">
                                        <span class="text-lg font-semibold text-white" x-text="`#${booking.id}`"></span>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium border"
                                              :class="`status-${booking.status}`"
                                              x-text="booking.status.toUpperCase()"></span>
                                        <span class="text-gray-400 text-sm" x-text="formatDate(booking.created_at)"></span>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <p class="text-gray-300 text-sm">
                                                <span class="font-medium">User:</span>
                                                <span x-text="booking.user?.name || 'Unknown'"></span>
                                            </p>
                                            <p class="text-gray-300 text-sm">
                                                <span class="font-medium">Email:</span>
                                                <span x-text="booking.user?.email || 'N/A'"></span>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-gray-300 text-sm">
                                                <span class="font-medium">Station:</span>
                                                <span x-text="booking.station?.name || 'Unknown Station'"></span>
                                            </p>
                                            <p class="text-gray-300 text-sm">
                                                <span class="font-medium">Address:</span>
                                                <span x-text="booking.station?.address || 'N/A'"></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-3 gap-4 text-sm text-gray-300">
                                        <div>
                                            <span class="font-medium">Start:</span>
                                            <span x-text="formatDateTime(booking.start_time)"></span>
                                        </div>
                                        <div>
                                            <span class="font-medium">End:</span>
                                            <span x-text="formatDateTime(booking.end_time)"></span>
                                        </div>
                                        <div>
                                            <span class="font-medium">Amount:</span>
                                            <span x-text="formatCurrency(booking.total_amount || booking.total_cost)"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2 ml-4" x-show="booking.status === 'pending'">
                                    <button @click="updateBookingStatus(booking.id, 'approved')"
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        ✓ Approve
                                    </button>
                                    <button @click="updateBookingStatus(booking.id, 'denied')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        ✗ Deny
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="filteredBookings.length === 0" class="text-center py-12">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-xl font-semibold text-gray-400 mb-2">No Bookings Found</h3>
                    <p class="text-gray-500">No booking requests match your current filters.</p>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingManager', () => ({
                bookings: @json($bookings ?? []),
                filteredBookings: [],
                searchQuery: '',
                statusFilter: '',
                stats: {
                    pending: 0,
                    approved: 0,
                    active: 0,
                    revenue: 0
                },

                init() {
                    this.calculateStats();
                    this.filterBookings();
                    this.initWebSocket();
                    setInterval(() => this.refreshBookings(), 30000); // Refresh every 30 seconds
                },

                calculateStats() {
                    this.stats = {
                        pending: this.bookings.filter(b => b.status === 'pending').length,
                        approved: this.bookings.filter(b => b.status === 'approved').length,
                        active: this.bookings.filter(b => b.status === 'active').length,
                        revenue: this.bookings.filter(b => ['approved', 'active', 'completed'].includes(b.status))
                                           .reduce((sum, b) => sum + parseFloat(b.total_amount || b.total_cost || 0), 0)
                    };
                },

                filterBookings() {
                    let filtered = this.bookings;

                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(booking =>
                            booking.id.toString().includes(query) ||
                            booking.user?.name?.toLowerCase().includes(query) ||
                            booking.user?.email?.toLowerCase().includes(query) ||
                            booking.station?.name?.toLowerCase().includes(query)
                        );
                    }

                    if (this.statusFilter) {
                        filtered = filtered.filter(booking => booking.status === this.statusFilter);
                    }

                    this.filteredBookings = filtered.sort((a, b) =>
                        new Date(b.created_at) - new Date(a.created_at)
                    );
                },

                async refreshBookings() {
                    try {
                        const response = await fetch('/admin/api/bookings', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.bookings = data.bookings || [];
                            this.calculateStats();
                            this.filterBookings();
                        }
                    } catch (error) {
                        console.error('Failed to refresh bookings:', error);
                    }
                },

                async updateBookingStatus(bookingId, status) {
                    try {
                        const response = await fetch(`/admin/api/bookings/${bookingId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status })
                        });

                        if (response.ok) {
                            const data = await response.json();

                            // Update local booking status
                            const booking = this.bookings.find(b => b.id === bookingId);
                            if (booking) {
                                booking.status = status;
                                this.calculateStats();
                                this.filterBookings();
                            }

                            // Show success notification
                            alert(`Booking ${status} successfully!`);
                        } else {
                            throw new Error('Failed to update booking status');
                        }
                    } catch (error) {
                        console.error('Error updating booking:', error);
                        alert('Failed to update booking status. Please try again.');
                    }
                },

                initWebSocket() {
                    if (typeof window.Echo !== 'undefined') {
                        window.Echo.channel('admin-dashboard')
                            .listen('.booking.created', (event) => {
                                console.log('New booking created:', event);
                                this.refreshBookings();
                            })
                            .listen('.booking.updated', (event) => {
                                console.log('Booking updated:', event);
                                this.refreshBookings();
                            });
                    }
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(amount || 0);
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString();
                },

                formatDateTime(dateString) {
                    return new Date(dateString).toLocaleString();
                }
            }));
        });
    </script>
</body>
</html>
