<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EV Charging Dashboard - EVC Project</title>
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
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 bg-green-500 text-white px-4 py-3 rounded-lg">
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
                <a href="{{ route('user.profile') }}" class="flex items-center space-x-3 text-gray-400 hover:text-white hover:bg-slate-700 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                    <span>Profile</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex">
            <!-- Map Section -->
            <div class="flex-1 p-6">
                <div class="bg-slate-800 rounded-2xl p-6 h-full">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold">Find Charging Stations</h2>
                        <div class="relative">
                            <input type="text" placeholder="Search..." class="bg-slate-700 border border-slate-600 rounded-lg px-4 py-2 pl-10 text-white placeholder-gray-400">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Map Container with Google Maps -->
                    <div class="bg-slate-700 rounded-xl h-96 mb-6 relative overflow-hidden">
                        <div id="map" class="w-full h-full"></div>
                        <!-- Loading state -->
                        <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-slate-700">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500 mx-auto mb-4"></div>
                                <p class="text-gray-400">Loading map...</p>
                            </div>
                        </div>
                        <!-- Fallback: Simulated Map if Google Maps fails -->
                        <div id="fallback-map" class="absolute inset-0 p-8 hidden">
                            <!-- Station markers -->
                            <div class="absolute top-16 left-20 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:scale-110 transition-transform"
                                 onclick="showStationInfo('Downtown Station', '123 Main St')">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                            <div class="absolute top-24 right-32 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:scale-110 transition-transform"
                                 onclick="showStationInfo('Mall Station', '456 Oak Ave')">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                            <div class="absolute bottom-20 left-1/3 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:scale-110 transition-transform"
                                 onclick="showStationInfo('Park Station', '789 Pine Rd')">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                            <div class="absolute bottom-32 right-20 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                            <div class="absolute top-32 right-16 w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center cursor-pointer hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>

                            <!-- Route line -->
                            <svg class="absolute inset-0 w-full h-full pointer-events-none">
                                <path d="M 80 120 Q 200 80 300 160" stroke="#10b981" stroke-width="2" stroke-dasharray="5,5" fill="none"/>
                            </svg>

                            <!-- Start/End markers -->
                            <div class="absolute bottom-20 left-8 bg-slate-600 px-3 py-1 rounded-lg text-sm">Start</div>
                            <div class="absolute bottom-20 right-8 bg-slate-600 px-3 py-1 rounded-lg text-sm">Long</div>
                        </div>
                    </div>

                    <!-- Reservations Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- My Reservations -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Reservations</h3>
                            <div class="space-y-3">
                                <div class="bg-slate-700 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-8 bg-slate-600 rounded flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium">Model S</p>
                                            <p class="text-sm text-gray-400">8:00 AM - 10:00 AM</p>
                                        </div>
                                    </div>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Confirm</span>
                                </div>

                                <div class="bg-slate-700 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-8 bg-slate-600 rounded flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium">Model 3</p>
                                            <p class="text-sm text-gray-400">10:30 PM - 12:00 PM</p>
                                        </div>
                                    </div>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Confirm</span>
                                </div>

                                <div class="bg-slate-700 rounded-lg p-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-8 bg-slate-600 rounded flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium">Model Y</p>
                                            <p class="text-sm text-gray-400">1:00 PM - 2:30 PM</p>
                                        </div>
                                    </div>
                                    <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm">Pending</span>
                                </div>
                            </div>
                        </div>

                        <!-- Available Stations -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Results</h3>
                            <div class="space-y-3">
                                <div class="bg-slate-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-8 bg-slate-600 rounded flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                    <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium">Tesla Model S</p>
                                                <p class="text-sm text-gray-400">9:00 AM - 10:00 AM</p>
                                                <p class="text-xs text-gray-500">89%</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-sm">Confirmed</span>
                                            <p class="text-sm text-gray-400 mt-1">$25/hr</p>
                                            <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm mt-1 transition-colors">Booked</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-8 bg-slate-600 rounded flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                    <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium">Tesla Model 3</p>
                                                <p class="text-sm text-gray-400">10:30 AM - 12:00 PM</p>
                                                <p class="text-xs text-gray-500">92%</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="bg-blue-500 text-white px-2 py-1 rounded text-sm">Available</span>
                                            <p class="text-sm text-gray-400 mt-1">$20/hr</p>
                                            <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm mt-1 transition-colors">Book</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-8 bg-slate-600 rounded flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                    <path d="M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium">Tesla Model Y</p>
                                                <p class="text-sm text-gray-400">1:00 PM - 2:30 PM</p>
                                                <p class="text-xs text-gray-500">76%</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="bg-blue-500 text-white px-2 py-1 rounded text-sm">Available</span>
                                            <p class="text-sm text-gray-400 mt-1">$30/hr</p>
                                            <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm mt-1 transition-colors">Book</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Panel -->
            <div class="w-80 p-6">
                <div class="bg-slate-800 rounded-2xl p-6 h-full">
                    <h3 class="text-lg font-semibold mb-6">Book an EV</h3>

                    <div class="space-y-6">
                        <!-- Start Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Start location</label>
                            <div class="relative">
                                <input type="text" placeholder="Enter pickup location" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 pl-10">
                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- End Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">End location</label>
                            <div class="relative">
                                <input type="text" placeholder="Enter destination" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 pl-10">
                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Date</label>
                                <input type="date" value="2024-05-10" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Time</label>
                                <input type="time" value="10:00" class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white">
                            </div>
                        </div>

                        <!-- Find EV Button -->
                        <button id="findEVButton" class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Find Charging Stations
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=places" async defer></script>

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
                        "stylers": [{ "color": "#242f3e" }]
                    },
                    {
                        "elementType": "labels.text.stroke",
                        "stylers": [{ "color": "#242f3e" }]
                    },
                    {
                        "elementType": "labels.text.fill",
                        "stylers": [{ "color": "#746855" }]
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
                    },
                    (error) => {
                        console.warn('Geolocation failed:', error);
                    }
                );
            }
        }

        // Load charging stations data from API
        function loadChargingStations() {
            // Get user location and fetch nearby stations
            const defaultLocation = { lat: 40.7128, lng: -74.0060 };
            const location = userLocation || defaultLocation;

            fetch(`/api/nearby-stations?latitude=${location.lat}&longitude=${location.lng}&radius=10`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.stations) {
                    stationsData = data.stations.map(station => ({
                        id: station.id,
                        name: station.name,
                        position: { lat: parseFloat(station.latitude), lng: parseFloat(station.longitude) },
                        status: station.status,
                        type: station.connector_type,
                        power: `${station.power_output}kW`,
                        price: `$${station.pricing_per_hour}/hour`,
                        available_slots: station.available_slots,
                        total_slots: station.total_slots
                    }));
                } else {
                    // Fallback to sample data if API fails
                    stationsData = [
                        {
                            id: 1,
                            name: "Downtown Charging Hub",
                            position: { lat: 40.7589, lng: -73.9851 },
                            status: "available",
                            type: "Fast Charging",
                            power: "150kW",
                            price: "$0.25/kWh",
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
                            price: "$0.20/kWh",
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
                            price: "$0.30/kWh",
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
                // Use fallback sample data
                stationsData = [
                    {
                        id: 1,
                        name: "Sample Station 1",
                        position: { lat: 40.7589, lng: -73.9851 },
                        status: "available",
                        type: "Fast Charging",
                        power: "150kW",
                        price: "$25/hour",
                        available_slots: 3,
                        total_slots: 4
                    }
                ];

                stationsData.forEach(station => {
                    addStationMarker(station);
                });
            });
        }

        // Add station marker to map
        function addStationMarker(station) {
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

        // Get station icon based on status
        function getStationIcon(status) {
            const color = status === 'available' ? '#10B981' : status === 'busy' ? '#EF4444' : '#6B7280';
            return 'data:image/svg+xml;base64,' + btoa(`
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                    <circle cx="16" cy="16" r="14" fill="${color}" stroke="white" stroke-width="2"/>
                    <path d="M16 8l-2 8h4l-2 8" stroke="white" stroke-width="2" fill="none"/>
                </svg>
            `);
        }

        // Show station information
        function showStationInfo(station, marker = null) {
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
                                <button onclick="bookStation(${station.id})"
                                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition-colors ${station.available_slots === 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                                        ${station.available_slots === 0 ? 'disabled' : ''}>
                                    ${station.available_slots > 0 ? 'Book Now' : 'Fully Booked'}
                                </button>
                            </div>
                        </div>
                    ` : `
                        <p class="text-gray-600">Sample charging station</p>
                        <button onclick="bookStation('${station}')"
                                class="mt-2 w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition-colors">
                            Book Now
                        </button>
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

        // Book a charging station
        function bookStation(stationId) {
            const station = stationsData.find(s => s.id == stationId) || { name: `Station ${stationId}` };

            // Show booking modal/form (simplified for demo)
            const startTime = prompt(`Booking ${station.name}\n\nEnter start time (YYYY-MM-DD HH:MM):`,
                new Date(Date.now() + 60 * 60 * 1000).toISOString().slice(0, 16));

            if (!startTime) return;

            const endTime = prompt('Enter end time (YYYY-MM-DD HH:MM):',
                new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString().slice(0, 16));

            if (!endTime) return;

            // Create booking via API
            fetch('/api/create-booking', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    station_id: stationId,
                    start_time: startTime,
                    end_time: endTime,
                    estimated_energy_needed: 50
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`${data.message}\nBooking ID: ${data.booking_id}`);
                    // Refresh station data
                    loadChargingStations();
                } else {
                    alert('Booking failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Booking error:', error);
                alert('Booking failed. Please try again later.');
            });
        }

        // Find EV button functionality
        document.addEventListener('DOMContentLoaded', function() {
            const findEVButton = document.getElementById('findEVButton');

            if (findEVButton) {
                findEVButton.addEventListener('click', function() {
                    this.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Searching...
                    `;

                    // Search for nearby stations
                    if (userLocation) {
                        searchNearbyStations();
                    } else {
                        getUserLocation();
                        setTimeout(() => {
                            if (userLocation) {
                                searchNearbyStations();
                            } else {
                                alert('Please allow location access to find nearby charging stations.');
                                resetFindButton();
                            }
                        }, 2000);
                    }
                });
            }

            // Make all sidebar links interactive
            document.querySelectorAll('nav a').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Add active state
                    document.querySelectorAll('nav a').forEach(l => {
                        l.classList.remove('bg-green-500', 'text-white');
                        l.classList.add('text-gray-400', 'hover:text-white', 'hover:bg-slate-700');
                    });

                    this.classList.remove('text-gray-400', 'hover:text-white', 'hover:bg-slate-700');
                    this.classList.add('bg-green-500', 'text-white');
                });
            });
        });

        // Reset Find EV button
        function resetFindButton() {
            const button = document.getElementById('findEVButton');
            if (button) {
                button.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Find Charging Stations
                `;
            }
        }

        // Search nearby stations
        function searchNearbyStations() {
            if (!userLocation) {
                resetFindButton();
                return;
            }

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
    </script>
</body>
</html>
