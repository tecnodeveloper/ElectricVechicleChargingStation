<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management - EVC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .delete-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
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
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors">
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="px-4 py-2 text-green-500 border-b-2 border-green-500 font-medium">
                    EVC Users
                </a>
                <a href="{{ route('admin.bookings') }}" class="px-4 py-2 text-gray-300 hover:text-white font-medium transition-colors relative">
                    Booking Management
                    @php
                        $pendingCount = App\Models\Booking::where('status', 'pending')->count();
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
    <div class="flex-1 bg-slate-700 p-6" x-data="userManagement()">
        <div class="max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">User Management</h1>
                <p class="text-gray-400">Manage all EVC users and their accounts</p>
            </div>

            <!-- Search and Filters -->
            <div class="bg-slate-800 rounded-xl p-6 mb-6">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            x-model="searchTerm"
                            placeholder="Search users by name or email..."
                            class="w-full bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-green-500 focus:outline-none"
                        >
                    </div>
                    <select x-model="statusFilter" class="bg-slate-700 text-white px-4 py-2 rounded-lg border border-slate-600 focus:border-green-500 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-700">
                    <h2 class="text-xl font-bold text-white">All Users</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-700">
                            <tr>
                                <th class="text-left py-3 px-6 text-gray-300 font-medium">User</th>
                                <th class="text-left py-3 px-6 text-gray-300 font-medium">Email</th>
                                <th class="text-left py-3 px-6 text-gray-300 font-medium">Phone</th>
                                <th class="text-left py-3 px-6 text-gray-300 font-medium">Status</th>
                                <th class="text-left py-3 px-6 text-gray-300 font-medium">Joined</th>
                                <th class="text-left py-3 px-6 text-gray-300 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="user in filteredUsers" :key="user.id">
                                <tr class="border-b border-slate-700 hover:bg-slate-750 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                                <span class="text-white font-medium text-sm" x-text="user.name.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-white" x-text="user.name"></div>
                                                <div class="text-sm text-gray-400" x-text="'ID: ' + user.id"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-gray-300" x-text="user.email"></td>
                                    <td class="py-4 px-6 text-gray-300" x-text="user.phone || 'Not provided'"></td>
                                    <td class="py-4 px-6">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium"
                                            :class="user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            x-text="user.email_verified_at ? 'Verified' : 'Unverified'"
                                        ></span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-300" x-text="formatDate(user.created_at)"></td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click="viewUser(user)"
                                                class="text-blue-400 hover:text-blue-300 text-sm font-medium"
                                            >
                                                View
                                            </button>
                                            <button
                                                @click="toggleUserStatus(user)"
                                                class="text-yellow-400 hover:text-yellow-300 text-sm font-medium"
                                                x-text="user.email_verified_at ? 'Suspend' : 'Activate'"
                                            >
                                            </button>
                                            <button
                                                @click="deleteUser(user)"
                                                class="delete-btn bg-red-500/20 hover:bg-red-500/30 border border-red-500/40 px-3 py-1 rounded-md text-red-400 hover:text-red-300 text-sm font-medium transition-all duration-200 flex items-center space-x-1"
                                                :disabled="deletingUsers.includes(user.id)"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" x-show="!deletingUsers.includes(user.id)">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" x-show="deletingUsers.includes(user.id)">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span x-text="deletingUsers.includes(user.id) ? 'Deleting...' : 'Delete'"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- No Results Message -->
                <div x-show="filteredUsers.length === 0" class="p-8 text-center">
                    <div class="text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-lg font-medium">No users found</p>
                        <p class="text-sm">Try adjusting your search or filter criteria</p>
                    </div>
                </div>
            </div>

            <!-- User Details Modal -->
            <div
                x-show="showUserModal"
                x-transition
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                @click="showUserModal = false"
            >
                <div
                    class="bg-slate-800 rounded-xl p-6 max-w-md w-full mx-4"
                    @click.stop
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">User Details</h3>
                        <button @click="showUserModal = false" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="selectedUser" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                            <p class="text-white" x-text="selectedUser?.name"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                            <p class="text-white" x-text="selectedUser?.email"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Phone</label>
                            <p class="text-white" x-text="selectedUser?.phone || 'Not provided'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium"
                                :class="selectedUser?.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                x-text="selectedUser?.email_verified_at ? 'Verified' : 'Unverified'"
                            ></span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Member Since</label>
                            <p class="text-white" x-text="formatDate(selectedUser?.created_at)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        function userManagement() {
            return {
                users: @json($users ?? []),
                searchTerm: '',
                statusFilter: '',
                showUserModal: false,
                selectedUser: null,
                deletingUsers: [], // Track users being deleted

                get filteredUsers() {
                    let filtered = this.users;

                    if (this.searchTerm) {
                        filtered = filtered.filter(user =>
                            user.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            user.email.toLowerCase().includes(this.searchTerm.toLowerCase())
                        );
                    }

                    if (this.statusFilter) {
                        filtered = filtered.filter(user => {
                            if (this.statusFilter === 'verified') {
                                return user.email_verified_at !== null;
                            } else if (this.statusFilter === 'unverified') {
                                return user.email_verified_at === null;
                            }
                            return true;
                        });
                    }

                    return filtered;
                },

                viewUser(user) {
                    this.selectedUser = user;
                    this.showUserModal = true;
                },

                async toggleUserStatus(user) {
                    try {
                        const response = await fetch(`/admin/api/users/${user.id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            const result = await response.json();
                            // Update local user data
                            const userIndex = this.users.findIndex(u => u.id === user.id);
                            if (userIndex !== -1) {
                                this.users[userIndex].email_verified_at = result.user.email_verified_at;
                            }
                            alert('User status updated successfully');
                        } else {
                            throw new Error('Failed to update user status');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error updating user status');
                    }
                },

                async deleteUser(user) {
                    // Prevent multiple deletions
                    if (this.deletingUsers.includes(user.id)) return;

                    const confirmMessage = `⚠️ PERMANENT DELETE WARNING ⚠️\n\nYou are about to permanently delete:\n\nUser: ${user.name}\nEmail: ${user.email}\nID: ${user.id}\n\nThis action cannot be undone and will remove:\n• User account and profile\n• All booking history\n• All associated data\n\nType "DELETE" to confirm:`;

                    const userInput = prompt(confirmMessage);

                    if (userInput === 'DELETE') {
                        try {
                            // Add to deleting state
                            this.deletingUsers.push(user.id);

                            const response = await fetch(`/admin/api/users/${user.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });

                            const result = await response.json();

                            if (response.ok) {
                                // Remove user from local array
                                this.users = this.users.filter(u => u.id !== user.id);

                                // Show success toast
                                this.showToast('success', `User "${user.name}" deleted successfully`, result.message);
                            } else {
                                throw new Error(result.message || 'Failed to delete user');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            this.showToast('error', 'Deletion Failed', error.message);
                        } finally {
                            // Remove from deleting state
                            this.deletingUsers = this.deletingUsers.filter(id => id !== user.id);
                        }
                    } else if (userInput !== null) {
                        this.showToast('warning', 'Deletion Cancelled', 'You must type "DELETE" exactly to confirm.');
                    }
                },

                showToast(type, title, message) {
                    const toastId = Date.now();
                    const colors = {
                        success: 'bg-green-500 border-green-400 text-white',
                        error: 'bg-red-500 border-red-400 text-white',
                        warning: 'bg-yellow-500 border-yellow-400 text-white',
                        info: 'bg-blue-500 border-blue-400 text-white'
                    };

                    const icons = {
                        success: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
                        error: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
                        warning: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
                        info: '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
                    };

                    const toast = document.createElement('div');
                    toast.id = `toast-${toastId}`;
                    toast.className = `slide-in ${colors[type]} border-l-4 p-4 rounded shadow-lg max-w-md`;
                    toast.innerHTML = `
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ${icons[type]}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">${title}</p>
                                <p class="mt-1 text-sm opacity-90">${message}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <button onclick="document.getElementById('toast-${toastId}').remove()" class="inline-flex text-white hover:opacity-75">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;

                    document.getElementById('toast-container').appendChild(toast);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        const element = document.getElementById(`toast-${toastId}`);
                        if (element) element.remove();
                    }, 5000);
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            }
        }
    </script>
</body>
</html>
