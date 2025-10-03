<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EVC Project</title>
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
                <a href="{{ route('user.reservations') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white hover:bg-slate-700 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Reservations</span>
                </a>
                <a href="{{ route('user.profile') }}" class="flex items-center space-x-3 bg-green-500 text-white px-4 py-3 rounded-lg">
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
                    <h1 class="text-3xl font-bold">Profile</h1>
                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        <span>Edit Profile</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Personal Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-slate-800 rounded-2xl p-6 mb-8">
                            <h2 class="text-xl font-semibold mb-6">Personal Information</h2>

                            <div class="flex items-center space-x-6 mb-8">
                                <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center text-2xl font-bold">
                                    JD
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold">{{ Auth::user()->name ?? 'John Doe' }}</h3>
                                    <p class="text-gray-400">EVC Member since January 2024</p>
                                    <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium mt-2">
                                        Premium Member
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Full Name</label>
                                    <p class="text-white font-medium">{{ Auth::user()->name ?? 'John Doe' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Phone</label>
                                    <p class="text-white font-medium">+1 (555) 123-4567</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Email</label>
                                    <p class="text-white font-medium">{{ Auth::user()->email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-1">Address</label>
                                    <p class="text-white font-medium">123 Main St, New York, NY 10001</p>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Preferences -->
                        <div class="bg-slate-800 rounded-2xl p-6">
                            <!-- Tab Navigation -->
                            <div class="flex space-x-1 mb-6">
                                <button class="bg-green-500 text-white px-6 py-2 rounded-lg font-medium">
                                    Vehicle Preferences
                                </button>
                                <button class="text-gray-400 hover:text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    Payment Methods
                                </button>
                                <button class="text-gray-400 hover:text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                    Settings
                                </button>
                            </div>

                            <h3 class="text-lg font-semibold mb-6">Preferred Vehicles</h3>

                            <!-- Vehicle 1 -->
                            <div class="bg-slate-700 rounded-xl p-4 mb-4 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-slate-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                            <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Tesla Model S</h4>
                                        <p class="text-sm text-gray-400">Range: 405 miles</p>
                                    </div>
                                </div>
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">Primary</span>
                            </div>

                            <!-- Vehicle 2 -->
                            <div class="bg-slate-700 rounded-xl p-4 mb-6 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-slate-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                            <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Tesla Model 3</h4>
                                        <p class="text-sm text-gray-400">Range: 358 miles</p>
                                    </div>
                                </div>
                                <span class="bg-slate-600 text-white px-3 py-1 rounded-full text-sm font-medium">Secondary</span>
                            </div>

                            <!-- Add Vehicle Button -->
                            <button class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                                Add Vehicle Preference
                            </button>
                        </div>
                    </div>

                    <!-- Usage Statistics -->
                    <div class="lg:col-span-1">
                        <div class="bg-slate-800 rounded-2xl p-6">
                            <h2 class="text-xl font-semibold mb-6">Usage Statistics</h2>

                            <!-- Stat 1 -->
                            <div class="text-center mb-8">
                                <div class="text-4xl font-bold text-green-500 mb-2">24</div>
                                <div class="text-sm font-medium text-gray-300">Total Bookings</div>
                            </div>

                            <!-- Stat 2 -->
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-10 h-10 bg-slate-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold">Distance Traveled</div>
                                    <div class="text-2xl font-bold">1,245 miles</div>
                                </div>
                            </div>

                            <!-- Stat 3 -->
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-10 h-10 bg-slate-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold">Carbon Saved</div>
                                    <div class="text-2xl font-bold">194 kg CO₂</div>
                                </div>
                            </div>

                            <!-- Stat 4 -->
                            <div class="flex items-center space-x-3 mb-8">
                                <div class="w-10 h-10 bg-slate-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold">Member Since</div>
                                    <div class="text-lg font-bold">January 2024</div>
                                </div>
                            </div>

                            <!-- Achievements -->
                            <div class="bg-slate-700 rounded-xl p-4">
                                <h3 class="font-semibold mb-3">Recent Achievements</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm">First Booking Milestone</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm">Eco Warrior</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
