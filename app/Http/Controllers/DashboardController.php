<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's recent bookings
        $recentBookings = Booking::where('user_id', $user->id)
            ->with('station')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get nearby stations (sample data)
        $nearbyStations = Station::where('is_available', true)
            ->take(10)
            ->get();

        // Get user statistics
        $totalBookings = Booking::where('user_id', $user->id)->count();
        $activeBookings = Booking::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        return view('dashboard', compact(
            'recentBookings',
            'nearbyStations',
            'totalBookings',
            'activeBookings'
        ));
    }

    /**
     * Get nearby stations via AJAX
     */
    public function getNearbyStations(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radius = $request->input('radius', 10); // Default 10km

        // For now, return sample data
        // In production, you'd calculate actual distances
        $stations = Station::where('is_available', true)
            ->with(['bookings' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function($station) {
                return [
                    'id' => $station->id,
                    'name' => $station->name,
                    'latitude' => $station->latitude,
                    'longitude' => $station->longitude,
                    'connector_type' => $station->connector_type,
                    'power_output' => $station->power_output,
                    'pricing_per_hour' => $station->pricing_per_hour,
                    'available_slots' => 4 - $station->bookings->count(), // Simple calculation
                    'total_slots' => 4,
                    'status' => $station->bookings->count() >= 4 ? 'busy' : 'available'
                ];
            });

        return response()->json([
            'success' => true,
            'stations' => $stations
        ]);
    }

    /**
     * Create a booking
     */
    public function createBooking(Request $request)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'station_id' => $request->station_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending', // Changed from 'confirmed' to 'pending' for admin approval
            'estimated_energy_needed' => $request->input('estimated_energy_needed', 50),
            'total_amount' => $request->input('total_amount', 0),
        ]);

        // Load relationships for the event
        $booking->load(['user', 'station']);

        // Broadcast the booking created event for real-time admin updates
        broadcast(new \App\Events\BookingCreated($booking));

        return response()->json([
            'success' => true,
            'message' => 'Booking request submitted successfully! Awaiting admin approval.',
            'booking_id' => $booking->id
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ];

        // Add password validation if password is being updated
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $user = Auth::user();

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    public function pastTrips($userId)
    {
        return response()->json(['user_id' => $userId, 'trips' => []]);
    }

    public function chargingHistory($userId)
    {
        return response()->json(['user_id' => $userId, 'history' => []]);
    }

    public function spendingAnalytics($userId)
    {
        return response()->json(['user_id' => $userId, 'analytics' => []]);
    }
}
