<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VehiclePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display the authenticated user's profile.
     */
    public function profile()
    {
        $user = Auth::user()->load('vehiclePreferences');

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['profile_picture']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get user's vehicle preferences.
     */
    public function vehiclePreferences()
    {
        $preferences = Auth::user()->vehiclePreferences;

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Store or update vehicle preferences.
     */
    public function storeVehiclePreference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'vehicle_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'battery_capacity' => 'required|numeric|min:0',
            'connector_type' => 'required|string',
            'max_charging_power' => 'sometimes|numeric|min:0',
            'is_primary' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is being set as primary, update all other preferences to false
        if ($request->boolean('is_primary')) {
            Auth::user()->vehiclePreferences()->update(['is_primary' => false]);
        }

        $preference = Auth::user()->vehiclePreferences()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Vehicle preference saved successfully',
            'data' => $preference
        ], 201);
    }

    /**
     * Update a specific vehicle preference.
     */
    public function updateVehiclePreference(Request $request, $id)
    {
        $preference = Auth::user()->vehiclePreferences()->find($id);

        if (!$preference) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle preference not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'vehicle_make' => 'sometimes|string|max:255',
            'vehicle_model' => 'sometimes|string|max:255',
            'vehicle_year' => 'sometimes|integer|min:2000|max:' . (date('Y') + 1),
            'battery_capacity' => 'sometimes|numeric|min:0',
            'connector_type' => 'sometimes|string',
            'max_charging_power' => 'sometimes|numeric|min:0',
            'is_primary' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is being set as primary, update all other preferences to false
        if ($request->boolean('is_primary')) {
            Auth::user()->vehiclePreferences()->where('id', '!=', $id)->update(['is_primary' => false]);
        }

        $preference->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Vehicle preference updated successfully',
            'data' => $preference
        ]);
    }

    /**
     * Delete a vehicle preference.
     */
    public function deleteVehiclePreference($id)
    {
        $preference = Auth::user()->vehiclePreferences()->find($id);

        if (!$preference) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle preference not found'
            ], 404);
        }

        $preference->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle preference deleted successfully'
        ]);
    }

    /**
     * Get user's booking statistics.
     */
    public function bookingStats()
    {
        $user = Auth::user();

        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'completed_bookings' => $user->bookings()->where('status', 'completed')->count(),
            'cancelled_bookings' => $user->bookings()->where('status', 'cancelled')->count(),
            'active_bookings' => $user->bookings()->whereIn('status', ['active', 'charging'])->count(),
            'total_energy_consumed' => $user->bookings()->where('status', 'completed')->sum('actual_energy_consumed'),
            'total_amount_spent' => $user->bookings()->where('status', 'completed')->sum('total_cost'),
            'average_session_duration' => $user->bookings()
                ->where('status', 'completed')
                ->whereNotNull('actual_start_time')
                ->whereNotNull('actual_end_time')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, actual_start_time, actual_end_time)) as avg_duration')
                ->first()->avg_duration ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get user's favorite stations.
     */
    public function favoriteStations()
    {
        $favoriteStations = Auth::user()->bookings()
            ->with('station')
            ->selectRaw('station_id, COUNT(*) as booking_count')
            ->groupBy('station_id')
            ->orderBy('booking_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($booking) {
                return [
                    'station' => $booking->station,
                    'booking_count' => $booking->booking_count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $favoriteStations
        ]);
    }

    /**
     * Get user's recent activity.
     */
    public function recentActivity(Request $request)
    {
        $activities = Auth::user()->bookings()
            ->with('station')
            ->orderBy('created_at', 'desc')
            ->limit($request->limit ?? 10)
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'type' => 'booking',
                    'action' => $booking->status,
                    'station_name' => $booking->station->name,
                    'station_address' => $booking->station->address,
                    'date' => $booking->created_at,
                    'cost' => $booking->total_cost,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'confirmation' => 'required|in:DELETE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 400);
        }

        // Check for active bookings
        $activeBookings = $user->bookings()->whereIn('status', ['active', 'charging'])->count();
        if ($activeBookings > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with active bookings'
            ], 400);
        }

        // Delete profile picture if exists
        if ($user->profile_picture) {
            Storage::delete($user->profile_picture);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}
