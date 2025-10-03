<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Station;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get admin dashboard statistics.
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'total_stations' => Station::count(),
            'total_bookings' => Booking::count(),
            'active_sessions' => Booking::where('status', 'charging')->count(),
            'completed_bookings_today' => Booking::where('status', 'completed')
                ->whereDate('created_at', today())->count(),
            'revenue_today' => Booking::where('status', 'completed')
                ->whereDate('created_at', today())->sum('total_cost'),
            'revenue_this_month' => Booking::where('status', 'completed')
                ->whereMonth('created_at', now()->month)->sum('total_cost'),
            'available_stations' => Station::where('is_available', true)->count(),
            'unavailable_stations' => Station::where('is_available', false)->count(),
        ];

        // Recent activities
        $recentBookings = Booking::with(['user', 'station'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_bookings' => $recentBookings
            ]
        ]);
    }

    /**
     * Get all users with pagination.
     */
    public function users(Request $request)
    {
        $query = User::where('is_admin', false);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by verification status
        if ($request->has('verified')) {
            $query->whereNotNull('email_verified_at');
        }

        $users = $query->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get specific user details.
     */
    public function userDetails($id)
    {
        $user = User::with(['bookings.station', 'vehiclePreferences'])
            ->withCount(['bookings as completed_bookings_count' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['bookings as total_spent' => function($query) {
                $query->where('status', 'completed');
            }], 'total_cost')
            ->find($id);

        if (!$user || $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user status.
     */
    public function updateUserStatus(Request $request, $id)
    {
        $user = User::where('is_admin', false)->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'status' => $request->status,
            'suspended_at' => $request->status === 'suspended' ? now() : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Get all stations with management data.
     */
    public function stations(Request $request)
    {
        $query = Station::withCount([
            'bookings as total_bookings',
            'bookings as active_bookings' => function($q) {
                $q->whereIn('status', ['active', 'charging']);
            }
        ]);

        // Filter by availability
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('connector_type', 'LIKE', "%{$search}%");
            });
        }

        $stations = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $stations
        ]);
    }

    /**
     * Get all bookings with management data.
     */
    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'station']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('start_time', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('start_time', '<=', $request->to_date);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhereHas('station', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Cancel a booking (admin override).
     */
    public function cancelBooking(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status === 'completed' || $booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already ' . $booking->status
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancelled_by_admin' => true,
            'cancellation_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }

    /**
     * Get revenue analytics.
     */
    public function revenueAnalytics(Request $request)
    {
        $period = $request->period ?? 'month'; // week, month, year

        $query = Booking::where('status', 'completed');

        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
        }

        $totalRevenue = $query->sum('total_cost');
        $totalBookings = $query->count();
        $averageBookingValue = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;

        // Daily breakdown for the period
        $dailyRevenue = $query->selectRaw('DATE(created_at) as date, SUM(total_cost) as revenue, COUNT(*) as bookings')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Station performance
        $stationPerformance = $query->with('station')
            ->selectRaw('station_id, COUNT(*) as bookings, SUM(total_cost) as revenue')
            ->groupBy('station_id')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_bookings' => $totalBookings,
                    'average_booking_value' => round($averageBookingValue, 2),
                ],
                'daily_breakdown' => $dailyRevenue,
                'top_stations' => $stationPerformance,
                'period' => $period
            ]
        ]);
    }

    /**
     * Get system usage analytics.
     */
    public function usageAnalytics()
    {
        $stats = [
            'peak_hours' => Booking::selectRaw('HOUR(start_time) as hour, COUNT(*) as bookings')
                ->groupBy('hour')
                ->orderBy('bookings', 'desc')
                ->get(),
            'popular_connector_types' => Station::selectRaw('connector_type, COUNT(*) as stations')
                ->groupBy('connector_type')
                ->orderBy('stations', 'desc')
                ->get(),
            'monthly_growth' => User::where('is_admin', false)
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as users')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
            'booking_status_distribution' => Booking::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Create admin user.
     */
    public function createAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin user created successfully',
            'data' => $admin
        ], 201);
    }

    /**
     * Export data.
     */
    public function exportData(Request $request)
    {
        $type = $request->type; // users, stations, bookings

        switch ($type) {
            case 'users':
                $data = User::where('is_admin', false)->get();
                break;
            case 'stations':
                $data = Station::withCount('bookings')->get();
                break;
            case 'bookings':
                $data = Booking::with(['user:id,name,email', 'station:id,name,address'])
                    ->get();
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid export type'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'export_type' => $type,
            'exported_at' => now(),
            'total_records' => $data->count()
        ]);
    }
}
