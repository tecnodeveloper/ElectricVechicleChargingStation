<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Station;
use App\Models\Booking;
use App\Events\StationCreated;

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
            // Get statistics with error handling
            $totalUsers = User::count();
            $activeBookings = Booking::where('status', 'active')->count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $totalStations = Station::count();
            // Monthly revenue: Only completed bookings in current month
            $monthlyRevenue = Booking::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->where('status', 'completed')
                                ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')) ?: 0;

            // Get all users for the EVC Users section
            $users = User::withCount('bookings')->latest()->get();

        } catch (\Exception $e) {
            // Fallback values in case of database issues
            $totalUsers = 0;
            $activeBookings = 0;
            $pendingBookings = 0;
            $totalStations = 0;
            $monthlyRevenue = 0;
            $users = collect([]); // Empty collection

            Log::error('Admin dashboard database error: ' . $e->getMessage());
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeBookings',
            'pendingBookings',
            'totalStations',
            'monthlyRevenue'
        ));
    }

    /**
     * Get real-time dashboard statistics (API endpoint)
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $stats = [
                'totalUsers' => User::count(),
                'activeBookings' => Booking::where('status', 'active')->count(),
                'pendingBookings' => Booking::where('status', 'pending')->count(),
                'approvedBookings' => Booking::where('status', 'approved')->count(),
                'completedBookings' => Booking::where('status', 'completed')->count(),
                'totalStations' => Station::count(),

                // Monthly revenue: Only completed bookings in current month
                'monthlyRevenue' => Booking::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->where('status', 'completed')
                                      ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')) ?: 0,

                // Today revenue: All approved bookings today
                'todayRevenue' => Booking::whereDate('updated_at', now()->toDateString())
                                     ->whereIn('status', ['approved', 'active', 'completed'])
                                     ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')) ?: 0,

                // Total revenue: All approved/active/completed bookings
                'totalRevenue' => Booking::whereIn('status', ['approved', 'active', 'completed'])
                                     ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')) ?: 0,

                'recentBookings' => Booking::with(['user', 'station'])
                                       ->latest()
                                       ->take(5)
                                       ->get(),
                'timestamp' => now()->toISOString()
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $stats
                ]);
            }

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Dashboard stats API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show users page directly
     */
    public function users()
    {
        try {
            // Get all users with their booking counts for the dedicated users page
            $users = User::withCount('bookings')
                         ->select('id', 'name', 'email', 'phone', 'email_verified_at', 'created_at')
                         ->latest()
                         ->get();

            return view('admin.users', compact('users'));
        } catch (\Exception $e) {
            Log::error('Admin users page error: ' . $e->getMessage());
            return view('admin.users', ['users' => collect([])]);
        }
    }

    /**
     * Show stations page directly
     */
    public function stations()
    {
        try {
            // Get all existing stations for display on the map
            $stations = Station::select('id', 'name', 'address', 'latitude as lat', 'longitude as lng', 'status', 'price_per_hour as price')
                              ->latest()
                              ->get();

            return view('admin.stations', compact('stations'));
        } catch (\Exception $e) {
            Log::error('Admin stations page error: ' . $e->getMessage());
            return view('admin.stations', ['stations' => collect([])]);
        }
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

            // Broadcast the station created event for real-time updates
            broadcast(new StationCreated($station));

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

    /**
     * Toggle user status (API endpoint)
     */
    public function toggleUserStatus(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Toggle email verification status
            if ($user->email_verified_at) {
                $user->email_verified_at = null;
            } else {
                $user->email_verified_at = now();
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user (API endpoint)
     */
    public function deleteUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Log the deletion for audit purposes
            Log::info('Admin deleting user', [
                'admin_action' => 'delete_user',
                'deleted_user_id' => $user->id,
                'deleted_user_name' => $user->name,
                'deleted_user_email' => $user->email,
                'deleted_at' => now(),
                'ip_address' => $request->ip()
            ]);

            // Store user info before deletion for response
            $deletedUserInfo = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];

            // Delete related data first (if needed)
            // This will cascade delete bookings, notifications, etc.
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => "User '{$deletedUserInfo['name']}' (ID: {$deletedUserInfo['id']}) has been permanently deleted from the database.",
                'deleted_user' => $deletedUserInfo
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found. The user may have already been deleted.'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stations (API endpoint)
     */
    public function getStations(Request $request)
    {
        try {
            $stations = Station::select('id', 'name', 'address', 'latitude as lat', 'longitude as lng', 'status', 'price_per_hour as price', 'available_slots', 'total_slots')
                              ->latest()
                              ->get()
                              ->map(function ($station) {
                                  // Update available slots before returning
                                  $station->updateAvailableSlots();
                                  return $station;
                              });

            return response()->json($stations);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete station (API endpoint)
     */
    public function deleteStation(Request $request, $id)
    {
        try {
            $station = Station::findOrFail($id);
            $station->delete();

            return response()->json([
                'success' => true,
                'message' => 'Station deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting station: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show bookings management page
     */
    public function bookings()
    {
        try {
            // Get all bookings with user and station relationships
            $bookings = Booking::with(['user', 'station'])
                              ->orderBy('created_at', 'desc')
                              ->get();

            return view('admin.bookings', compact('bookings'));
        } catch (\Exception $e) {
            Log::error('Admin bookings page error: ' . $e->getMessage());
            return view('admin.bookings', ['bookings' => collect([])]);
        }
    }

    /**
     * Get bookings data (API endpoint)
     */
    public function getBookings(Request $request)
    {
        try {
            $bookings = Booking::with(['user', 'station'])
                              ->orderBy('created_at', 'desc')
                              ->get();

            return response()->json([
                'success' => true,
                'bookings' => $bookings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status (approve/deny bookings)
     */
    public function updateBookingStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,approved,denied,active,completed'
            ]);

            $booking = Booking::with(['user', 'station'])->findOrFail($id);
            $oldStatus = $booking->status;
            $newStatus = $validated['status'];

            // Update booking status
            $booking->status = $newStatus;
            $booking->save();

            // Log the status change
            Log::info('Booking status updated', [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => 'admin',
                'updated_at' => now()
            ]);

            // Broadcast booking update event for real-time updates
            broadcast(new \App\Events\BookingCreated($booking))->toOthers();

            return response()->json([
                'success' => true,
                'message' => "Booking #{$booking->id} has been {$newStatus} successfully",
                'booking' => $booking
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error updating booking status', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating booking status: ' . $e->getMessage()
            ], 500);
        }
    }
}
