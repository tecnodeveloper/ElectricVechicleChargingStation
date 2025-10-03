<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\ChargingStationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\RouteOptimizationController;
use App\Http\Controllers\API\AuthApiController;
use App\Http\Controllers\API\UserApiController;
use App\Http\Controllers\API\StationApiController;
use App\Http\Controllers\API\BookingApiController;
use App\Http\Controllers\API\AdminApiController;
use App\Http\Controllers\API\NotificationApiController;

// ==================== AUTH ====================
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/user', [AuthController::class, 'user']);
Route::get('/me', [AuthController::class, 'me']);

// ==================== RESERVATIONS ====================
// User reservations
Route::post('/users/{user}/reservations', [ReservationController::class, 'store']);
Route::get('/users/{user}/reservations', [ReservationController::class, 'index']);
Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);

// Alternative reservation routes (for compatibility with frontend)
Route::post('/reservations/{userId}', [ReservationController::class, 'store']);
Route::get('/reservations/{userId}', [ReservationController::class, 'index']);

// Bookings routes moved to web.php for session authentication

// Admin view all reservations
Route::get('/admin/reservations', [ReservationController::class, 'all']);

// ==================== CHARGING STATIONS (Admin CRUD) ====================
Route::prefix('admin/charging-stations')->group(function () {
    Route::post('/', [ChargingStationController::class, 'store']);
    Route::get('/', [ChargingStationController::class, 'index']);
    Route::get('/{id}', [ChargingStationController::class, 'show']);
    Route::put('/{id}', [ChargingStationController::class, 'update']);
    Route::delete('/{id}', [ChargingStationController::class, 'destroy']);
});

// ==================== PUBLIC STATIONS ====================
Route::get('/stations', [StationApiController::class, 'index']);
Route::get('/stations/{id}', [StationApiController::class, 'show']);
Route::get('/nearby-stations', [StationApiController::class, 'nearby']);

// ==================== USER DASHBOARD ====================
Route::get('/user/{user}/past-trips', [DashboardController::class, 'pastTrips']);
Route::get('/user/{user}/charging-history', [DashboardController::class, 'chargingHistory']);
Route::get('/user/{user}/spending-analytics', [DashboardController::class, 'spendingAnalytics']);

// ==================== TRIPS ====================
Route::get('/trips/summary', [TripController::class, 'summary']);
Route::get('/trips/route-plan', [TripController::class, 'routePlan']);

// ==================== ROUTE OPTIMIZATION ====================
Route::get('/route-optimization', [RouteOptimizationController::class, 'optimize']);

// ==================== NEW COMPREHENSIVE API ENDPOINTS ====================

// Authentication API Routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/verify-otp', [AuthApiController::class, 'verifyOtp']);
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthApiController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthApiController::class, 'profile']);
        Route::put('/profile', [AuthApiController::class, 'updateProfile']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
    });
});

// Stations API Routes
Route::prefix('v1/stations')->group(function () {
    Route::get('/', [StationApiController::class, 'index']);
    Route::get('/{id}', [StationApiController::class, 'show']);
    Route::get('/nearby', [StationApiController::class, 'nearby']);
    Route::get('/{id}/availability', [StationApiController::class, 'availability']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [StationApiController::class, 'store']);
        Route::put('/{id}', [StationApiController::class, 'update']);
        Route::delete('/{id}', [StationApiController::class, 'destroy']);
    });
});

// Bookings API Routes
Route::middleware('auth:sanctum')->prefix('v1/bookings')->group(function () {
    Route::get('/', [BookingApiController::class, 'index']);
    Route::post('/', [BookingApiController::class, 'store']);
    Route::get('/active', [BookingApiController::class, 'active']);
    Route::get('/history', [BookingApiController::class, 'history']);
    Route::get('/{id}', [BookingApiController::class, 'show']);
    Route::put('/{id}', [BookingApiController::class, 'update']);
    Route::post('/{id}/cancel', [BookingApiController::class, 'cancel']);
    Route::post('/{id}/start', [BookingApiController::class, 'start']);
    Route::post('/{id}/stop', [BookingApiController::class, 'stop']);
});

// User API Routes
Route::middleware('auth:sanctum')->prefix('v1/user')->group(function () {
    Route::get('/profile', [UserApiController::class, 'profile']);
    Route::put('/profile', [UserApiController::class, 'updateProfile']);
    Route::post('/change-password', [UserApiController::class, 'changePassword']);
    Route::get('/booking-stats', [UserApiController::class, 'bookingStats']);
    Route::get('/favorite-stations', [UserApiController::class, 'favoriteStations']);
    Route::get('/recent-activity', [UserApiController::class, 'recentActivity']);
    Route::delete('/account', [UserApiController::class, 'deleteAccount']);

    // Vehicle Preferences
    Route::get('/vehicle-preferences', [UserApiController::class, 'vehiclePreferences']);
    Route::post('/vehicle-preferences', [UserApiController::class, 'storeVehiclePreference']);
    Route::put('/vehicle-preferences/{id}', [UserApiController::class, 'updateVehiclePreference']);
    Route::delete('/vehicle-preferences/{id}', [UserApiController::class, 'deleteVehiclePreference']);
});

// Notifications API Routes
Route::middleware('auth:sanctum')->prefix('v1/notifications')->group(function () {
    Route::get('/', [NotificationApiController::class, 'index']);
    Route::post('/', [NotificationApiController::class, 'store']);
    Route::get('/stats', [NotificationApiController::class, 'stats']);
    Route::get('/unread-count', [NotificationApiController::class, 'unreadCount']);
    Route::post('/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);
    Route::delete('/clear-read', [NotificationApiController::class, 'clearRead']);
    Route::get('/{id}', [NotificationApiController::class, 'show']);
    Route::post('/{id}/mark-read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/{id}/mark-unread', [NotificationApiController::class, 'markAsUnread']);
    Route::delete('/{id}', [NotificationApiController::class, 'destroy']);

    // Admin only routes
    Route::post('/send/{userId}', [NotificationApiController::class, 'sendToUser']);
    Route::post('/broadcast', [NotificationApiController::class, 'broadcast']);
});

// Admin API Routes
Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('/dashboard', [AdminApiController::class, 'dashboard']);
    Route::get('/analytics/revenue', [AdminApiController::class, 'revenueAnalytics']);
    Route::get('/analytics/usage', [AdminApiController::class, 'usageAnalytics']);
    Route::post('/create-admin', [AdminApiController::class, 'createAdmin']);
    Route::get('/export', [AdminApiController::class, 'exportData']);

    // User Management
    Route::get('/users', [AdminApiController::class, 'users']);
    Route::get('/users/{id}', [AdminApiController::class, 'userDetails']);
    Route::put('/users/{id}/status', [AdminApiController::class, 'updateUserStatus']);

    // Station Management
    Route::get('/stations', [AdminApiController::class, 'stations']);

    // Booking Management
    Route::get('/bookings', [AdminApiController::class, 'bookings']);
    Route::post('/bookings/{id}/cancel', [AdminApiController::class, 'cancelBooking']);
});


