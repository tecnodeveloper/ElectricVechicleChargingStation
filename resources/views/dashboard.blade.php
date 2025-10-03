<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>EVC Dashboard - Electric Vehicle Charging</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Custom styles for fullscreen map */
        .map-container {
            height: calc(100vh - 80px); /* Full height minus header */
        }

        .sidebar-collapsed {
            width: 70px !important;
        }

        /* Fix for main content container height */
        .main-container {
            height: calc(100vh - 80px);
            max-height: calc(100vh - 80px);
        }

        /* Enhanced scrolling fix for sections */
        .section-container {
            height: calc(100vh - 80px) !important;
            max-height: calc(100vh - 80px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-collapsed .sidebar-logo-text {
            display: none;
        }

        /* Alpine.js cloak */
        [x-cloak] { display: none !important; }

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

        /* Enhanced Custom Scrollbar Styling */
        .custom-scrollbar {
            /* Firefox */
            scrollbar-width: auto;
            scrollbar-color: #7c3aed #1e293b;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 14px;
            height: 14px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
            border-radius: 8px;
            border: 1px solid #334155;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 50%, #5b21b6 100%);
            border-radius: 8px;
            border: 2px solid #1e293b;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3), inset 0 1px 2px rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
            box-shadow: 0 3px 6px rgba(0,0,0,0.4), inset 0 1px 3px rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:active {
            background: linear-gradient(135deg, #a855f7 0%, #8b5cf6 50%, #7c3aed 100%);
            transform: scale(0.98);
        }

        .custom-scrollbar::-webkit-scrollbar-corner {
            background: #1e293b;
        }

        /* Smooth scrolling behavior */
        .smooth-scroll {
            scroll-behavior: smooth;
        }

        /* Enhanced scrolling for mobile */
        .section-container {
            -webkit-overflow-scrolling: touch;
            scroll-padding-top: 20px;
            overscroll-behavior: contain;
        }
    </style>
</head>
<body class="bg-slate-900 text-white overflow-hidden" x-data="{
    sidebarOpen: true,
    showReservations: false,
    activeSection: 'dashboard',
    bookings: [],
    reservations: [], // Add reservations array
    profile: {
        name: '{{ Auth::user()->name }}',
        email: '{{ Auth::user()->email }}',
        password: '',
        password_confirmation: ''
    },

    // Modal data
    showModifyBookingModal: false,
    modifyBooking: {
        id: null,
        start_time: '',
        duration_hours: 1,
        station_name: '',
        current_total: ''
    },
    modifySubmitting: false,

    async init() {
        await this.loadUserReservations();
        // Initialize Google Maps if available
        if (typeof google !== 'undefined' && google.maps && typeof initMap === 'function') {
            initMap();
        }
    },

    async loadUserReservations() {
        try {
            // Load bookings (primary source) with cache-busting
            const bookingsResponse = await fetch('/api/bookings/{{ Auth::id() }}?_t=' + Date.now(), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                }
            });

            if (bookingsResponse.ok) {
                const bookingsData = await bookingsResponse.json();
                this.bookings = bookingsData.bookings || [];
                this.reservations = bookingsData.bookings || []; // Sync reservations with bookings
            } else {
                // Fallback to reservations API if bookings fail
                const response = await fetch('/api/reservations/{{ Auth::id() }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.bookings = data.reservations || [];
                    this.reservations = data.reservations || []; // Sync both arrays
                }
            }
        } catch (error) {
            console.log('Could not load user data:', error);
            // Use fallback data for demo
            this.bookings = [
                {id: 1, station: {name: 'Downtown Station'}, start_time: '2025-10-05 09:00:00', status: 'pending', total_amount: 25.50, duration_hours: 2},
                {id: 2, station: {name: 'Mall Station'}, start_time: '2025-10-07 15:00:00', status: 'approved', total_amount: 30.00, duration_hours: 1.5}
            ];
            this.bookings = this.reservations;
        }
    },

    async updateProfile() {
        try {
            const response = await fetch('/dashboard/profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: this.profile.name,
                    email: this.profile.email,
                    password: this.profile.password,
                    password_confirmation: this.profile.password_confirmation
                })
            });

            const data = await response.json();
            if (data.success) {
                // Update the profile data with the response
                if (data.user) {
                    this.profile.name = data.user.name;
                    this.profile.email = data.user.email;
                }

                // Clear password fields
                this.profile.password = '';
                this.profile.password_confirmation = '';

                // Show success message
                showNotification('Profile updated successfully!', 'success');
            } else {
                // Show error message
                showNotification('Error updating profile: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Network error:', error);
            showNotification('Network error updating profile. Please try again.', 'error');
        }
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    },

    formatTime(dateString) {
        return new Date(dateString).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    },

    formatCurrency(amount) {
        // Handle string amounts that might already be formatted
        const numericAmount = typeof amount === 'string'
            ? parseFloat(amount.replace(/[$,]/g, '')) || 0
            : parseFloat(amount) || 0;

        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(numericAmount);
    },

    getStatusColor(status) {
        const colors = {
            'pending': 'bg-yellow-500/20 text-yellow-300 border-yellow-500/40',
            'approved': 'bg-green-500/20 text-green-300 border-green-500/40',
            'active': 'bg-blue-500/20 text-blue-300 border-blue-500/40',
            'completed': 'bg-gray-500/20 text-gray-300 border-gray-500/40',
            'denied': 'bg-red-500/20 text-red-300 border-red-500/40'
        };
        return colors[status] || 'bg-gray-500/20 text-gray-300 border-gray-500/40';
    },

    // Show modify modal
    showModifyModal(booking) {
        // Format the datetime for datetime-local input
        const startDate = new Date(booking.start_time);
        const formattedDateTime = startDate.toISOString().slice(0, 16);

        this.modifyBooking = {
            id: booking.id,
            start_time: formattedDateTime,
            duration_hours: booking.duration_hours,
            station_name: booking.station.name,
            current_total: booking.total_amount
        };
        this.showModifyBookingModal = true;
    },

    // Close modify modal
    closeModifyModal() {
        this.showModifyBookingModal = false;
        this.modifySubmitting = false;
    },

    // Cancel booking
    async cancelBooking(bookingId) {
        if (!confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`/api/bookings/${bookingId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (result.success) {
                // Remove the booking from the list or reload the list
                await this.loadUserReservations();
                alert('✅ Booking cancelled successfully!');
            } else {
                alert('❌ Failed to cancel booking: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Cancel booking error:', error);
            alert('❌ Network error occurred while cancelling booking.');
        }
    },

    // Update booking
    async updateBooking() {
        if (this.modifySubmitting) return;

        this.modifySubmitting = true;

        try {
            const response = await fetch(`/api/bookings/${this.modifyBooking.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                body: JSON.stringify({
                    start_time: this.modifyBooking.start_time,
                    duration_hours: parseFloat(this.modifyBooking.duration_hours)
                })
            });

            const result = await response.json();

            if (result.success) {
                // Reload the bookings list
                await this.loadUserReservations();
                this.closeModifyModal();
                alert('✅ Booking updated successfully!');
            } else {
                alert('❌ Failed to update booking: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Update booking error:', error);
            alert('❌ Network error occurred while updating booking.');
        } finally {
            this.modifySubmitting = false;
        }
    },

    // Mark booking as completed
    async markAsCompleted(bookingId) {
        if (!confirm('Mark this charging session as completed? This will finalize the booking and generate a receipt.')) {
            return;
        }

        try {
            const response = await fetch(`/api/bookings/${bookingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: 'completed'
                })
            });

            const result = await response.json();

            if (result.success) {
                await this.loadUserReservations();
                alert('✅ Charging session marked as completed! You can now view your receipt.');
            } else {
                alert('❌ Failed to mark as completed: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Mark completed error:', error);
            alert('❌ Network error occurred while marking as completed.');
        }
    },

    // View receipt for completed bookings
    async viewReceipt(bookingId) {
        try {
            // For now, show a detailed receipt modal or redirect to receipt page
            const booking = this.bookings.find(b => b.id === bookingId);
            if (booking) {
                const receiptDetails = `
🧾 CHARGING SESSION RECEIPT
━━━━━━━━━━━━━━━━━━━━━━━━━━━

📍 Station: ${booking.station.name}
📍 Location: ${booking.station.address}
📅 Date: ${booking.date}
⏰ Time: ${booking.time}
⏱️ Duration: ${booking.duration_hours} hours
💰 Total Amount: ${booking.total_amount}
📊 Status: ${booking.status.toUpperCase()}

━━━━━━━━━━━━━━━━━━━━━━━━━━━
Thank you for using EV Charging Platform!
                `;
                alert(receiptDetails);
            }
        } catch (error) {
            console.error('View receipt error:', error);
            alert('❌ Unable to load receipt details.');
        }
    }
}">
    <!-- Header -->
    <header class="bg-slate-800 shadow-lg border-b border-slate-700 relative z-50">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-3">
                <!-- Sidebar Toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-slate-700 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- EVC Logo -->
                <button @click="activeSection = 'dashboard'" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center relative">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11 1l-8 9h5v12h6V10h5L11 1z"/>
                        </svg>
                    </div>
                    <div class="text-green-500 font-bold text-2xl tracking-wider">EVC</div>
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Search Bar -->
                <div class="relative">
                    <input type="text" id="search-input" placeholder="Search locations..."
                           class="bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 pl-10 text-white placeholder-gray-400 w-64">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"/>
                    </svg>
                </div>

                <!-- Find Stations Button -->
                <button id="findEVButtonHeader" onclick="searchNearbyStations()"
                        class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Find Stations</span>
                </button>

                <!-- Reservations Button -->
                <button @click="activeSection = 'reservations'; await this.loadUserReservations();"
                        class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg text-sm transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                    </svg>
                    <span>My Reservations</span>
                </button>

                <!-- User Info with Profile Edit -->
                <div class="flex items-center space-x-3" x-data="{ showProfile: false }">
                    <!-- Profile Button -->
                    <div class="relative">
                        <button @click="showProfile = !showProfile" class="flex items-center space-x-2 bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg text-sm transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div class="text-left">
                                <div class="font-medium">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Profile Dropdown -->
                        <div x-show="showProfile"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             @click.away="showProfile = false"
                             class="absolute right-0 top-12 mt-2 w-80 bg-slate-800 border border-slate-700 rounded-lg shadow-xl z-50">

                            <!-- Profile Header -->
                            <div class="p-4 border-b border-slate-700">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-white">{{ Auth::user()->name }}</h3>
                                        <p class="text-sm text-gray-400">{{ Auth::user()->email }}</p>
                                        <span class="inline-block px-2 py-1 bg-green-600 text-xs rounded-full mt-1">
                                            {{ Auth::user()->is_verified ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Form -->
                            <div class="p-4">
                                <form id="profile-form" class="space-y-4">
                                    @csrf

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                                        <input type="text" id="profile-name" name="name" value="{{ Auth::user()->name }}"
                                               class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                                        <input type="email" id="profile-email" name="email" value="{{ Auth::user()->email }}"
                                               class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>



                                    <div class="flex space-x-2 pt-2">
                                        <button type="button" @click="updateProfile()"
                                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                            Save Changes
                                        </button>
                                        <button type="button" @click="showProfile = false"
                                                class="flex-1 bg-slate-600 hover:bg-slate-500 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Admin Link (if user is admin) -->
                            @if(Auth::user()->is_admin)
                            <div class="border-t border-slate-700 p-4">
                                <a href="{{ route('admin.dashboard') }}"
                                   class="flex items-center space-x-2 text-orange-400 hover:text-orange-300 font-medium">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                    </svg>
                                    <span>Admin Dashboard</span>
                                </a>
                            </div>
                            @endif

                            <!-- Logout -->
                            <div class="border-t border-slate-700 p-4">
                                <form action="{{ route('logout') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center space-x-2 w-full text-red-400 hover:text-red-300 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span>Sign out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div x-data="{show: true}" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="fixed top-4 right-4 z-50 bg-green-600 text-white px-6 py-4 rounded-lg shadow-lg max-w-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p>{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{show: true}" x-init="setTimeout(() => show = false, 7000)" x-show="show" x-transition class="fixed top-4 right-4 z-50 bg-red-600 text-white px-6 py-4 rounded-lg shadow-lg max-w-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <p>{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="flex main-container">
        <!-- Sidebar -->
        <div class="bg-slate-800 border-r border-slate-700 transition-all duration-300 relative z-40"
             :class="sidebarOpen ? 'w-64' : 'w-16'">
            <nav class="p-4 space-y-2">
                <button @click="activeSection = 'dashboard'"
                        :class="activeSection === 'dashboard' ? 'bg-green-500 text-white' : 'text-gray-400 hover:text-white hover:bg-slate-700'"
                        class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-colors group relative">
                    <svg class="w-5 h-5 min-w-[20px]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="sidebar-text transition-opacity text-left" :class="!sidebarOpen && 'opacity-0'">Home</span>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-700 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        Home
                    </div>
                </button>

                <button @click="activeSection = 'bookings'"
                        :class="activeSection === 'bookings' ? 'bg-blue-500 text-white' : 'text-gray-400 hover:text-white hover:bg-slate-700'"
                        class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-colors group relative">
                    <svg class="w-5 h-5 min-w-[20px]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                    </svg>
                    <span class="sidebar-text transition-opacity text-left" :class="!sidebarOpen && 'opacity-0'">Bookings</span>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-700 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        Bookings
                    </div>
                </button>

                <button @click="activeSection = 'reservations'; await this.loadUserReservations();"
                        :class="activeSection === 'reservations' ? 'bg-purple-500 text-white' : 'text-gray-400 hover:text-white hover:bg-slate-700'"
                        class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-colors group relative">
                    <svg class="w-5 h-5 min-w-[20px]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-text transition-opacity text-left" :class="!sidebarOpen && 'opacity-0'">My Reservations</span>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-700 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        My Reservations
                    </div>
                </button>

                <button @click="activeSection = 'profile'"
                        :class="activeSection === 'profile' ? 'bg-orange-500 text-white' : 'text-gray-400 hover:text-white hover:bg-slate-700'"
                        class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-colors group relative">
                    <svg class="w-5 h-5 min-w-[20px]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                    <span class="sidebar-text transition-opacity text-left" :class="!sidebarOpen && 'opacity-0'">Profile</span>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-700 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        Profile
                    </div>
                </button>

                <a href="{{ route('subscription.plans') }}"
                   class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-colors group relative text-gray-400 hover:text-white hover:bg-slate-700 {{ auth()->user()->isPremium() ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white' : '' }}">
                    <svg class="w-5 h-5 min-w-[20px]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="sidebar-text transition-opacity text-left" :class="!sidebarOpen && 'opacity-0'">
                        @if(auth()->user()->isPremium())
                            Premium ✨
                        @else
                            Upgrade
                        @endif
                    </span>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-700 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        @if(auth()->user()->isPremium())
                            Premium
                        @else
                            Upgrade
                        @endif
                    </div>
                </a>
            </nav>
                    <div x-show="!sidebarOpen" class="absolute left-16 bg-slate-700 text-white px-2 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        Profile
                    </div>
                </a>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 relative">
            <!-- Dashboard Map View (Default) -->
            <div x-show="activeSection === 'dashboard'" class="w-full h-full relative">
                <!-- Subscription Status Banner -->
                @if(!auth()->user()->isPremium())
                <div class="absolute top-4 left-4 right-4 z-50" x-data="{ showBanner: true }" x-show="showBanner">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg p-4 shadow-lg border border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold">Free Plan - {{ auth()->user()->getRemainingBookings() ?? 0 }} bookings remaining this week</p>
                                    <p class="text-sm text-purple-100">Upgrade to Premium for unlimited bookings and 20% discount!</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('premium.subscribe') }}"
                                   class="bg-white text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition">
                                    Upgrade Now
                                </a>
                                <button @click="showBanner = false"
                                        class="text-white hover:text-purple-200 transition-colors p-1 rounded-full hover:bg-purple-600/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div x-data="{ showBanner: true }" x-show="showBanner" class="absolute top-4 left-4 right-4 z-50">
                    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg p-4 shadow-lg border border-green-500">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold">Premium Member ✨</p>
                                    <p class="text-sm text-green-100">Unlimited bookings • 20% discount • Weekly rewards</p>
                                </div>
                            </div>
                            <button @click="showBanner = false" class="text-green-200 hover:text-white ml-4">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Full-width Full-height Google Maps -->
                <div id="map" class="w-full map-container bg-slate-700"></div>

                <!-- Map Loading State -->
                <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-slate-700 z-30">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500 mx-auto mb-4"></div>
                        <p class="text-gray-400">Loading map...</p>
                    </div>
                </div>

                <!-- Fallback Map -->
                <div id="fallback-map" class="absolute inset-0 hidden bg-slate-700 z-20">
                    <div class="w-full h-full relative overflow-hidden">
                        <!-- Sample stations for fallback -->
                        <div class="absolute top-1/4 left-1/4 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-green-600 transition-colors z-10"
                             onclick="showStationInfo('Downtown Station', '123 Main St')">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 616 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                        </div>
                        <div class="absolute top-1/3 right-1/3 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-green-600 transition-colors z-10"
                             onclick="showStationInfo('Mall Station', '456 Oak Ave')">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 616 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                        </div>
                        <div class="absolute bottom-1/3 left-2/3 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-red-600 transition-colors z-10"
                             onclick="showStationInfo('Busy Station', '789 Pine Rd')">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 616 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Section -->
            <div x-show="activeSection === 'bookings'" class="w-full h-full bg-slate-700 p-6 overflow-y-auto custom-scrollbar smooth-scroll">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-white">My Bookings</h2>
                        <button @click="activeSection = 'dashboard'"
                                class="bg-blue-500 hover:bg-blue-600 px-6 py-3 rounded-lg text-white transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Booking
                        </button>
                    </div>

                    <!-- Bookings List -->
                    <div class="space-y-4">
                        <template x-for="booking in bookings" :key="booking.id">
                            <div class="bg-slate-800 rounded-lg p-6 border border-slate-600">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-white" x-text="booking.station?.name || 'Unknown Station'"></h3>
                                            <p class="text-gray-400 text-sm" x-text="booking.station?.address || 'Address not available'"></p>
                                            <p class="text-gray-500 text-xs" x-text="`${booking.date} at ${booking.time}`"></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-white" x-text="booking.total_amount || 'TBD'"></div>
                                        <div class="text-sm text-gray-400" x-text="`${booking.duration_hours}h session`"></div>
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-medium mt-1"
                                              :class="booking.status === 'approved' ? 'bg-green-600 text-white' :
                                                     booking.status === 'pending' ? 'bg-orange-600 text-white' :
                                                     booking.status === 'confirmed' ? 'bg-blue-600 text-white' :
                                                     booking.status === 'completed' ? 'bg-purple-600 text-white' :
                                                     booking.status === 'cancelled' ? 'bg-red-600 text-white' :
                                                     'bg-gray-600 text-white'"
                                              x-html="(booking.status === 'completed' ? '✅ ' : '') + (booking.status?.charAt(0).toUpperCase() + booking.status?.slice(1)) || 'Unknown'">
                                        </span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <!-- Modify Button for pending bookings only -->
                                    <button @click="showModifyModal(booking)"
                                            x-show="booking.status === 'pending'"
                                            class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Modify
                                    </button>

                                    <!-- Mark as Completed Button for active/confirmed bookings -->
                                    <button @click="markAsCompleted(booking.id)"
                                            x-show="booking.status === 'confirmed' || booking.status === 'approved'"
                                            class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Mark Completed
                                    </button>

                                    <!-- View Receipt Button for completed bookings -->
                                    <button @click="viewReceipt(booking.id)"
                                            x-show="booking.status === 'completed'"
                                            class="bg-purple-500 hover:bg-purple-600 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Receipt
                                    </button>

                                    <!-- Cancel Button for pending/approved bookings -->
                                    <button @click="cancelBooking(booking.id)"
                                            x-show="booking.status === 'pending' || booking.status === 'approved'"
                                            class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="bookings.length === 0" class="text-center py-12">
                            <div class="w-24 h-24 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-2">No Bookings Yet</h3>
                            <p class="text-gray-400 mb-6">You haven't made any charging station bookings yet.</p>
                            <button @click="activeSection = 'dashboard'"
                                    class="bg-blue-500 hover:bg-blue-600 px-6 py-3 rounded-lg text-white transition-colors">
                                Make Your First Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservations Section -->
            <div x-show="activeSection === 'reservations'" class="w-full section-container bg-slate-700 p-6 custom-scrollbar smooth-scroll">
                <div class="max-w-6xl mx-auto">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-3xl font-bold text-white">My Reservations</h2>
                            <p class="text-gray-400 mt-1">Manage your charging station bookings</p>
                        </div>
                        <button @click="activeSection = 'dashboard'"
                                class="bg-purple-500 hover:bg-purple-600 px-6 py-3 rounded-lg text-white transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Book New Station
                        </button>
                    </div>

                    <!-- Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 rounded-xl text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-100 text-sm">Total Bookings</p>
                                    <p class="text-3xl font-bold" x-text="reservations.length"></p>
                                </div>
                                <svg class="w-8 h-8 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 p-6 rounded-xl text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-yellow-100 text-sm">Pending Approval</p>
                                    <p class="text-3xl font-bold" x-text="bookings.filter(r => r.status === 'pending').length"></p>
                                </div>
                                <svg class="w-8 h-8 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 rounded-xl text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-100 text-sm">Active Sessions</p>
                                    <p class="text-3xl font-bold" x-text="bookings.filter(r => ['approved', 'active'].includes(r.status)).length"></p>
                                </div>
                                <svg class="w-8 h-8 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 rounded-xl text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-100 text-sm">Total Spent</p>
                                    <p class="text-3xl font-bold" x-text="formatCurrency(bookings.reduce((sum, r) => sum + (parseFloat(r.total_amount) || 0), 0))"></p>
                                </div>
                                <svg class="w-8 h-8 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Reservations List -->
                    <div class="space-y-4" x-show="bookings.length > 0">
                        <template x-for="reservation in bookings" :key="reservation.id || Math.random()">
                            <div x-show="reservation && reservation.status" class="bg-slate-800 rounded-xl p-6 border border-slate-600 hover:border-slate-500 transition-colors">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-4">
                                        <!-- Status Icon -->
                                        <div class="w-14 h-14 rounded-lg flex items-center justify-center"
                                             :class="reservation.status === 'pending' ? 'bg-yellow-500/20' :
                                                    reservation.status === 'approved' ? 'bg-green-500/20' :
                                                    reservation.status === 'active' ? 'bg-blue-500/20' :
                                                    reservation.status === 'completed' ? 'bg-gray-500/20' : 'bg-red-500/20'">
                                            <svg class="w-7 h-7" :class="reservation.status === 'pending' ? 'text-yellow-400' :
                                                                        reservation.status === 'approved' ? 'text-green-400' :
                                                                        reservation.status === 'active' ? 'text-blue-400' :
                                                                        reservation.status === 'completed' ? 'text-gray-400' : 'text-red-400'"
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <template x-if="reservation.status === 'pending'">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                                </template>
                                                <template x-if="['approved', 'active', 'completed'].includes(reservation.status)">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                                </template>
                                                <template x-if="reservation.status === 'denied'">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                                </template>
                                            </svg>
                                        </div>

                                        <!-- Reservation Details -->
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="font-bold text-white text-lg" x-text="reservation.station?.name || 'Unknown Station'"></h3>
                                                <span class="px-3 py-1 rounded-full text-xs font-medium border"
                                                      :class="getStatusColor(reservation.status)"
                                                      x-text="reservation.status.toUpperCase()"></span>
                                            </div>
                                            <div class="grid md:grid-cols-3 gap-4 text-sm">
                                                <div class="text-gray-300">
                                                    <span class="text-gray-400">📅 Date:</span>
                                                    <span x-text="formatDate(reservation.start_time)"></span>
                                                </div>
                                                <div class="text-gray-300">
                                                    <span class="text-gray-400">⏰ Time:</span>
                                                    <span x-text="formatTime(reservation.start_time)"></span>
                                                </div>
                                                <div class="text-gray-300">
                                                    <span class="text-gray-400">⏱️ Duration:</span>
                                                    <span x-text="`${reservation.duration_hours || 1} hours`"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Amount -->
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-white" x-text="formatCurrency(reservation.total_amount)"></div>
                                        <div class="text-gray-400 text-sm">Total Amount</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-between items-center pt-4 border-t border-slate-600">
                                    <div class="flex items-center gap-4 text-sm text-gray-400">
                                        <span>Booking ID: #<span x-text="reservation.id"></span></span>
                                        <span>•</span>
                                        <span x-text="`Created ${formatDate(reservation.created_at || reservation.start_time)}`"></span>
                                    </div>

                                    <div class="flex gap-3">
                                        <template x-if="reservation.status === 'pending'">
                                            <div class="flex gap-2">
                                                <button @click="showModifyModal(reservation)" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Modify
                                                </button>
                                                <button @click="cancelBooking(reservation.id)" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Cancel
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="['approved', 'confirmed'].includes(reservation.status)">
                                            <button @click="markAsCompleted(reservation.id)" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Mark Completed
                                            </button>
                                        </template>
                                        <template x-if="reservation.status === 'completed'">
                                            <button class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-sm text-white transition-colors flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                View Receipt
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="reservations.length === 0" class="text-center py-16">
                        <div class="mb-6">
                            <svg class="w-24 h-24 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-400 mb-3">No Reservations Yet</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">You haven't made any charging station reservations yet. Start by booking your first charging session!</p>
                        <button @click="activeSection = 'dashboard'"
                                class="bg-purple-500 hover:bg-purple-600 text-white px-8 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Find Charging Stations
                        </button>
                    </div>
                </div>
                <!-- Bottom padding for scrolling -->
                <div class="h-20"></div>
            </div>

            <!-- Profile Section -->
            <div x-show="activeSection === 'profile'" class="w-full section-container bg-slate-700 custom-scrollbar smooth-scroll">
                <div class="w-full p-8">
                    <div class="w-full max-w-6xl mx-auto">
                        <h2 class="text-4xl font-bold text-white mb-10 text-center">Profile Settings</h2>

                        <!-- Profile Form -->
                        <div class="bg-slate-800 rounded-2xl p-10 border border-slate-600 shadow-2xl">
                            <div class="flex flex-col items-center text-center mb-10">
                                <div class="w-28 h-28 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mb-6">
                                    <span class="text-white font-bold text-4xl">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <h3 class="text-3xl font-bold text-white">{{ Auth::user()->name }}</h3>
                                <p class="text-gray-400 text-xl">{{ Auth::user()->email }}</p>

                                <!-- Subscription Status & Upgrade Button -->
                                <div class="mt-6">
                                    @if(!Auth::user()->isPremium())
                                        <div class="flex flex-col items-center space-y-4">
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-base font-medium bg-gray-700 text-gray-300 border border-gray-600">
                                                🆓 Free Member ({{ Auth::user()->getRemainingBookings() ?? 0 }}/5 bookings left this week)
                                            </span>
                                            <a href="{{ route('premium.subscribe') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-bold text-lg rounded-full hover:from-yellow-600 hover:to-orange-600 transition duration-300 transform hover:scale-105 shadow-lg">
                                                <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                                </svg>
                                                Upgrade to Premium
                                            </a>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-base font-medium bg-yellow-700 text-yellow-100 border border-yellow-600">
                                            👑 Premium Member
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <form @submit.prevent="updateProfile()" class="space-y-10">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <div class="space-y-4">
                                        <label class="block text-xl font-medium text-gray-300 mb-4">Full Name</label>
                                        <input type="text" x-model="profile.name" required
                                               class="w-full px-6 py-4 bg-slate-700 border border-slate-600 rounded-xl text-white text-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-lg">
                                    </div>
                                    <div class="space-y-4">
                                        <label class="block text-xl font-medium text-gray-300 mb-4">Email Address</label>
                                        <input type="email" x-model="profile.email" required
                                               class="w-full px-6 py-4 bg-slate-700 border border-slate-600 rounded-xl text-white text-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-lg">
                                    </div>
                                </div>

                                <div class="border-t border-slate-600 pt-10">
                                    <h4 class="text-2xl font-bold text-white mb-8">Change Password</h4>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                        <div class="space-y-4">
                                            <label class="block text-xl font-medium text-gray-300 mb-4">New Password</label>
                                            <input type="password" x-model="profile.password"
                                                   placeholder="Leave blank to keep current password"
                                                   class="w-full px-6 py-4 bg-slate-700 border border-slate-600 rounded-xl text-white text-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-lg">
                                        </div>
                                        <div class="space-y-4">
                                            <label class="block text-xl font-medium text-gray-300 mb-4">Confirm Password</label>
                                            <input type="password" x-model="profile.password_confirmation"
                                                   placeholder="Confirm new password"
                                                   class="w-full px-6 py-4 bg-slate-700 border border-slate-600 rounded-xl text-white text-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-lg">
                                        </div>
                                    </div>
                                    <div class="mt-4 text-gray-400 space-y-2">
                                        <p class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Password must be at least 8 characters long
                                        </p>
                                        <p class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Leave password fields empty to keep your current password
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col lg:flex-row space-y-6 lg:space-y-0 lg:space-x-8 pt-12">
                                    <button type="submit"
                                            class="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-5 px-10 rounded-xl transition-all transform hover:scale-105 flex items-center justify-center gap-4 text-xl shadow-lg">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Update Profile
                                    </button>
                                    <button type="button"
                                            @click="profile = {name: '{{ Auth::user()->name }}', email: '{{ Auth::user()->email }}', password: '', password_confirmation: ''}"
                                            class="flex-1 bg-slate-600 hover:bg-slate-500 text-white font-bold py-5 px-10 rounded-xl transition-all flex items-center justify-center gap-4 text-xl shadow-lg">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Reset Form
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Bottom padding for scrolling -->
                <div class="h-20"></div>
            </div>
        </div>

        <!-- Reservations Sidebar -->
        <div class="bg-slate-800 border-l border-slate-700 transition-all duration-300 absolute right-0 top-0 h-full z-30 shadow-lg"
             :class="showReservations ? 'w-96 translate-x-0' : 'w-0 translate-x-full'"
             x-show="showReservations"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">

            <div class="p-6 h-full overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold">Reservations</h3>
                    <button @click="showReservations = false" class="p-2 hover:bg-slate-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Active Reservations -->
                <div class="space-y-4">
                    <div class="bg-slate-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold">Downtown Hub</h4>
                            <span class="text-green-400 text-sm">Active</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-3">Today, 2:00 PM - 4:00 PM</p>
                        <div class="flex space-x-2">
                            <button class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-3 rounded text-sm">
                                Start
                            </button>
                            <button class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold">Mall Station</h4>
                            <span class="text-blue-400 text-sm">Upcoming</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-3">Tomorrow, 10:00 AM - 12:00 PM</p>
                        <div class="flex space-x-2">
                            <button class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded text-sm">
                                Modify
                            </button>
                            <button class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compiled Assets for Laravel Echo -->
    @vite(['resources/js/app.js'])

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&libraries=places" async defer></script>

    <script>
        let map;
        let userLocation = null;
        let stationsData = [];
        let markers = [];
        let infoWindow;

        // Initialize Google Maps
        function initMap() {
            // Default to New York if geolocation fails
            const defaultCenter = { lat: 40.7128, lng: -74.0060 };

            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: defaultCenter,
                styles: [
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

            infoWindow = new google.maps.InfoWindow();

            // Hide loading indicator
            document.getElementById('map-loading').style.display = 'none';

            // Get user's location
            getUserLocation();

            // Load sample charging stations
            loadChargingStations();
        }

        // Fallback if Google Maps fails
        function initFallbackMap() {
            document.getElementById('map-loading').style.display = 'none';
            document.getElementById('fallback-map').classList.remove('hidden');
            console.log('Using fallback map - Google Maps API not available');
        }

        // Get user's current location
        function getUserLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };

                        if (map) {
                            map.setCenter(userLocation);

                            // Add user location marker
                            new google.maps.Marker({
                                position: userLocation,
                                map: map,
                                title: "Your Location",
                                icon: {
                                    url: 'data:image/svg+xml;base64,' + btoa(`
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#3B82F6">
                                            <circle cx="12" cy="12" r="8"/>
                                            <circle cx="12" cy="12" r="3" fill="white"/>
                                        </svg>
                                    `),
                                    scaledSize: new google.maps.Size(24, 24)
                                }
                            });
                        }
                    },
                    (error) => {
                        console.warn('Geolocation failed:', error);
                    }
                );
            }
        }

        // Load charging stations data from admin API
        function loadChargingStations() {
            fetch('/admin/api/stations', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    // Map admin stations to user dashboard format
                    stationsData = data.map(station => ({
                        id: station.id,
                        name: station.name,
                        position: { lat: parseFloat(station.lat), lng: parseFloat(station.lng) },
                        status: station.status || "available",
                        type: "Fast Charging",
                        power: "150kW",
                        price: `$${station.price || '25'}/hour`,
                        available_slots: station.available_slots !== undefined ? station.available_slots : Math.floor(Math.random() * 5), // 0-4 slots
                        total_slots: station.total_slots || 4,
                        address: station.address
                    }));
                } else {
                    console.log('No stations data received, using fallback');
                    // Fallback to sample data if API fails
                    stationsData = [
                        {
                            id: 1,
                            name: "Downtown Charging Hub",
                            position: { lat: 40.7589, lng: -73.9851 },
                            status: "available",
                            type: "Fast Charging",
                            power: "150kW",
                            price: "$25/hour",
                            available_slots: 3,
                            total_slots: 4
                        },
                        {
                            id: 2,
                            name: "Mall Charging Station",
                            position: { lat: 40.7505, lng: -73.9934 },
                            status: "available",
                            type: "Standard Charging",
                            power: "50kW",
                            price: "$20/hour",
                            available_slots: 2,
                            total_slots: 2
                        },
                        {
                            id: 3,
                            name: "Park & Charge",
                            position: { lat: 40.7412, lng: -74.0055 },
                            status: "busy",
                            type: "Ultra Fast",
                            power: "250kW",
                            price: "$30/hour",
                            available_slots: 0,
                            total_slots: 6
                        }
                    ];
                }

                // Add markers for each station
                stationsData.forEach(station => {
                    addStationMarker(station);
                });
            })
            .catch(error => {
                console.error('Error loading stations:', error);
                initFallbackMap();
            });
        }

        // Add station marker to map
        function addStationMarker(station) {
            if (!map) return;

            const marker = new google.maps.Marker({
                position: station.position,
                map: map,
                title: station.name,
                icon: {
                    url: getStationIcon(station.status),
                    scaledSize: new google.maps.Size(32, 32)
                }
            });

            marker.addListener('click', () => {
                showStationInfo(station, marker);
            });

            markers.push(marker);
        }

        // Get station icon - Red markers for admin-added stations
        function getStationIcon(status) {
            // Always show red markers for charging stations as requested
            const color = '#EF4444'; // Red color for all charging station markers
            return 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                    <circle cx="16" cy="16" r="14" fill="${color}" stroke="white" stroke-width="2"/>
                    <path d="M12 8C8.13 8 5 11.13 5 15c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="white"/>
                    <circle cx="12" cy="15" r="3" fill="${color}"/>
                </svg>
            `);
        }

        // Show station information
        function showStationInfo(station, marker = null) {
            console.log('🚗 Station Debug:', {
                name: station.name,
                available_slots: station.available_slots,
                total_slots: station.total_slots,
                showButton: station.available_slots > 0
            });

            const content = `
                <div class="p-4 min-w-64">
                    <h3 class="font-bold text-lg text-gray-800 mb-2">${station.name || station}</h3>
                    ${station.type ? `
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Type:</span>
                                <span class="font-medium">${station.type}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Power:</span>
                                <span class="font-medium">${station.power}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Price:</span>
                                <span class="font-medium">${station.price}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Available:</span>
                                <span class="font-medium ${station.available_slots > 0 ? 'text-green-600' : 'text-red-600'}">
                                    ${station.available_slots}/${station.total_slots} slots
                                </span>
                            </div>
                            <div class="pt-2 border-t">
                                ${(station.available_slots > 0) ? `
                                    <button onclick="bookStation(${station.id})"
                                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition-colors">
                                        Book Now
                                    </button>
                                ` : `
                                    <div class="w-full bg-gray-400 text-white font-medium py-2 px-4 rounded text-center cursor-not-allowed">
                                        🚫 Fully Booked
                                    </div>
                                `}
                            </div>
                        </div>
                    ` : `
                        <p class="text-gray-600">Sample charging station</p>
                        ${(station.available_slots === undefined || station.available_slots > 0) ? `
                            <button onclick="bookStation('${station}')"
                                    class="mt-2 w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition-colors">
                                Book Now
                            </button>
                        ` : `
                            <div class="mt-2 w-full bg-gray-400 text-white font-medium py-2 px-4 rounded text-center cursor-not-allowed">
                                🚫 Fully Booked
                            </div>
                        `}
                    `}
                </div>
            `;

            if (marker && infoWindow) {
                infoWindow.setContent(content);
                infoWindow.open(map, marker);
            } else {
                // Fallback for non-Google Maps
                alert(`Station: ${station.name || station}\nClick "Book Now" to make a reservation.`);
            }
        }

        // Book a charging station - Navigate to booking form
        function bookStation(stationId) {
            console.log('Booking station with ID:', stationId);

            // Handle different station ID formats
            let finalStationId = stationId;
            let station = null;

            if (typeof stationId === 'string' && isNaN(stationId)) {
                // If it's a station name, try to find the ID
                station = stationsData.find(s => s.name === stationId);
                if (station) {
                    finalStationId = station.id;
                } else {
                    // Create a default ID for unknown stations
                    finalStationId = 1; // Default to first station
                }
            } else {
                // Find station by ID
                station = stationsData.find(s => s.id == finalStationId);
            }

            // Check if station is fully booked before allowing booking
            if (station && station.available_slots <= 0) {
                alert('⚠️ Station Fully Reserved\n\nThis charging station is currently fully booked. Please select another station or try again later.');
                return; // Don't navigate to booking form
            }

            // Navigate to booking form page with station ID
            window.location.href = `/book-station?station=${finalStationId}`;
        }

        // Reset Find EV button
        function resetFindButton() {
            const button = document.getElementById('findEVButtonHeader');
            if (button) {
                button.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Find Stations</span>
                `;
            }
        }

        // Search nearby stations
        function searchNearbyStations() {
            // Show loading state on header button
            const headerButton = document.getElementById('findEVButtonHeader');
            if (headerButton) {
                headerButton.innerHTML = `
                    <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Searching...</span>
                `;
            }

            if (!userLocation) {
                getUserLocation();
                setTimeout(() => {
                    if (userLocation) {
                        performSearch();
                    } else {
                        alert('Please allow location access to find nearby charging stations.');
                        resetFindButton();
                    }
                }, 2000);
            } else {
                performSearch();
            }
        }

        function performSearch() {
            // Reload stations with current location
            loadChargingStations();

            setTimeout(() => {
                // Filter stations by distance
                const nearbyStations = stationsData.filter(station => {
                    const distance = calculateDistance(userLocation, station.position);
                    return distance < 10; // Within 10km
                });

                if (nearbyStations.length > 0) {
                    // Zoom to show nearby stations
                    if (map) {
                        const bounds = new google.maps.LatLngBounds();
                        bounds.extend(userLocation);
                        nearbyStations.forEach(station => bounds.extend(station.position));
                        map.fitBounds(bounds);
                    }

                    // Show results in a better way
                    showSearchResults(nearbyStations);
                } else {
                    alert('No charging stations found within 10km of your location.');
                }

                resetFindButton();
            }, 1000);
        }

        // Show search results
        function showSearchResults(stations) {
            const resultText = stations.map(station =>
                `📍 ${station.name} (${station.available_slots}/${station.total_slots} available)`
            ).join('\n');

            alert(`Found ${stations.length} charging stations nearby:\n\n${resultText}\n\nClick on map markers for more details and booking.`);
        }

        // Calculate distance between two points
        function calculateDistance(pos1, pos2) {
            const R = 6371; // Earth's radius in km
            const dLat = (pos2.lat - pos1.lat) * Math.PI / 180;
            const dLon = (pos2.lng - pos1.lng) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(pos1.lat * Math.PI / 180) * Math.cos(pos2.lat * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        // Find EV button functionality - removed since using onclick in header
        document.addEventListener('DOMContentLoaded', function() {
            // Search input functionality
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        searchLocation(this.value);
                    }
                });
            }
        });

        // Search for location
        function searchLocation(query) {
            if (!query.trim()) return;

            if (map && google.maps.places) {
                const service = new google.maps.places.PlacesService(map);
                const request = {
                    query: query,
                    fields: ['name', 'geometry'],
                };

                service.textSearch(request, (results, status) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK && results[0]) {
                        map.setCenter(results[0].geometry.location);
                        map.setZoom(14);

                        // Add marker for searched location
                        new google.maps.Marker({
                            position: results[0].geometry.location,
                            map: map,
                            title: results[0].name,
                            icon: {
                                url: 'data:image/svg+xml;base64,' + btoa(`
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#EF4444">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                `),
                                scaledSize: new google.maps.Size(24, 24)
                            }
                        });
                    }
                });
            }
        }

        // Fallback initialization
        window.addEventListener('load', function() {
            // If Google Maps hasn't loaded after 5 seconds, use fallback
            setTimeout(() => {
                if (!window.google) {
                    initFallbackMap();
                }
            }, 5000);
        });

        // Error handling for Google Maps
        window.gm_authFailure = function() {
            console.error('Google Maps authentication failed');
            initFallbackMap();
        };

        // Profile update function


        // ==================== REAL-TIME WEBSOCKET FUNCTIONALITY ====================

        // Initialize WebSocket connection for real-time station updates
        function initWebSocketConnection() {
            // Check if Echo is available (loaded via Vite)
            if (typeof window.Echo !== 'undefined') {
                console.log('🚀 Connecting to WebSocket for real-time station updates...');

                // Listen to the stations channel for new station events
                window.Echo.channel('stations-channel')
                    .listen('.station.created', (event) => {
                        console.log('🆕 New station added:', event);

                        // Show notification
                        showStationNotification(event);

                        // Add new station to the map
                        addNewStationToMap(event);

                        // Update stations data
                        loadChargingStations();
                    });

                console.log('✅ WebSocket connection established for station updates');
            } else {
                console.warn('⚠️ Laravel Echo not available. Real-time updates disabled.');
                console.log('💡 Falling back to periodic refresh every 30 seconds...');

                // Fallback: Refresh stations every 30 seconds
                setInterval(() => {
                    loadChargingStations();
                }, 30000);
            }
        }

        // Show notification when new station is added
        function showStationNotification(stationData) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium">New Charging Station Added!</p>
                        <p class="text-sm">${stationData.name} - ${stationData.address}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Add new station to map in real-time
        function addNewStationToMap(stationData) {
            if (!map) return;

            const newStation = {
                id: stationData.id,
                name: stationData.name,
                position: {
                    lat: parseFloat(stationData.latitude),
                    lng: parseFloat(stationData.longitude)
                },
                status: stationData.status || 'available',
                type: 'Fast Charging',
                power: '150kW',
                price: `$${stationData.price_per_hour}/hour`,
                available_slots: 4,
                total_slots: 4
            };

            // Add to stations data
            stationsData.push(newStation);

            // Add marker to map
            addStationMarker(newStation);

            console.log('🗺️ New station added to map:', newStation.name);
        }

        // Initialize WebSocket when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for Echo to be fully loaded via Vite
            setTimeout(() => {
                initWebSocketConnection();
            }, 1000);
        });

        // Notification helper function
        function showNotification(message, type = 'success') {
            const notificationDiv = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';

            notificationDiv.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            notificationDiv.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${icon}
                    </svg>
                    ${message}
                </div>
            `;
            document.body.appendChild(notificationDiv);

            // Slide in animation
            setTimeout(() => {
                notificationDiv.style.transform = 'translateX(0)';
            }, 100);

            // Auto remove after 3 seconds
            setTimeout(() => {
                notificationDiv.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notificationDiv)) {
                        document.body.removeChild(notificationDiv);
                    }
                }, 300);
            }, 3000);
        }
    </script>

    <!-- Modify Booking Modal -->
    <div x-show="showModifyBookingModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModifyModal()"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-600 shadow-2xl">

                <!-- Header -->
                <div class="border-b border-slate-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white">Modify Booking</h3>
                        <button @click="closeModifyModal()" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <form @submit.prevent="updateBooking()" class="p-6 space-y-6">
                    <!-- Station Info -->
                    <div class="bg-slate-700/50 rounded-lg p-4">
                        <h4 class="font-semibold text-white mb-1" x-text="modifyBooking.station_name"></h4>
                        <p class="text-sm text-gray-400">Current Total: <span x-text="modifyBooking.current_total" class="text-blue-400"></span></p>
                    </div>

                    <!-- Date and Time -->
                    <div>
                        <label for="modify-start-time" class="block text-sm font-medium text-white mb-2">
                            New Date & Time
                        </label>
                        <input type="datetime-local"
                               id="modify-start-time"
                               x-model="modifyBooking.start_time"
                               :min="new Date().toISOString().slice(0, 16)"
                               class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                    </div>

                    <!-- Duration -->
                    <div>
                        <label for="modify-duration" class="block text-sm font-medium text-white mb-2">
                            Duration (hours)
                        </label>
                        <select x-model="modifyBooking.duration_hours"
                                id="modify-duration"
                                class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <option value="0.5">30 minutes</option>
                            <option value="1">1 hour</option>
                            <option value="1.5">1.5 hours</option>
                            <option value="2">2 hours</option>
                            <option value="2.5">2.5 hours</option>
                            <option value="3">3 hours</option>
                            <option value="4">4 hours</option>
                            <option value="5">5 hours</option>
                            <option value="6">6 hours</option>
                            <option value="8">8 hours</option>
                        </select>
                    </div>

                    <!-- Warning -->
                    <div class="bg-yellow-900/30 border border-yellow-500/50 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-yellow-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-300 mb-1">Important Notice</h4>
                                <p class="text-sm text-yellow-200">Modifying your booking may change the total cost. The new amount will be calculated based on current pricing.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="button"
                                @click="closeModifyModal()"
                                class="flex-1 py-3 px-4 bg-slate-600 hover:bg-slate-500 text-white font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="modifySubmitting"
                                class="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <span x-show="!modifySubmitting">Update Booking</span>
                            <span x-show="modifySubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
