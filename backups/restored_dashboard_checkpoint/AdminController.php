<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Station;
use App\Models\Booking;

class AdminController extends Controller
{

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Direct access to admin dashboard
        // Admin credentials for reference: admin@gmail.com / 12345678

        try {
            // Get all users for the EVC Users section (only needed data)
            $users = User::withCount('bookings')->latest()->get();

        } catch (\Exception $e) {
            // Fallback values in case of database issues
            $users = collect([]); // Empty collection
            
            Log::error('Admin dashboard database error: ' . $e->getMessage());
        }

        // Set stats to 0 since cards were removed
        $totalUsers = 0;
        $activeBookings = 0;
        $totalStations = 0;
        $monthlyRevenue = 0;        return view('user.adminPanel', compact(
            'totalUsers',
            'activeBookings',
            'totalStations',
            'monthlyRevenue',
            'users'
        ))->with('activeSection', 'dashboard');
    }

    /**
     * Show users page directly
     */
    public function usersPage()
    {
        // Get all users with their booking counts for the dedicated users page
        $users = User::withCount('bookings')
                     ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
                     ->latest()
                     ->get();

        return view('admin.users', compact('users'));
    }

    /**
     * Show stations page directly
     */
    public function stationsPage()
    {
        // Get all existing stations for display on the map
        $stations = Station::select('id', 'name', 'address', 'latitude', 'longitude', 'status', 'price_per_hour')
                          ->latest()
                          ->get();

        return view('admin.stations', compact('stations'));
    }

    /**
     * Get users data for admin panel (GET method)
     */
    public function getUsers(Request $request)
    {
        try {
            // Get all users with their booking counts
            $users = User::withCount('bookings')
                         ->select('id', 'name', 'email', 'email_verified_at', 'created_at')
                         ->latest()
                         ->get();

            // Return JSON response for API calls or redirect back for web requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'users' => $users,
                    'total' => $users->count()
                ]);
            }

            // For web requests, redirect back to dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Users data refreshed successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching users: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Error fetching users data');
        }
    }

    /**
     * Add a new charging station
     */
    public function addStation(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'price_per_hour' => 'required|numeric|min:0',
                'status' => 'required|in:active,maintenance,offline'
            ]);

            // Create new station
            $station = Station::create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'location' => $validated['address'], // Use address as location
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'price_per_hour' => $validated['price_per_hour'],
                'pricing_per_hour' => $validated['price_per_hour'], // Duplicate for compatibility
                'status' => $validated['status'],
                'type' => 'fast', // Default type
                'power_rating' => 50.00, // Default power rating
                'power_output' => 150.00, // Default power output
                'connector_type' => 'Type 2', // Default connector
                'is_available' => true,
                'amenities' => json_encode(['parking']), // Default amenities
                'description' => 'New charging station added via admin panel'
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Charging station added successfully!',
                    'station' => $station
                ]);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Charging station added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->route('admin.dashboard')->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding station: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Error adding charging station');
        }
    }


}
