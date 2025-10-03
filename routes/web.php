<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ForgotPasswordController;


// Registration
Route::get('/register', [RegisterController::class, 'index'])->name('register.form');
Route::post('/register', [RegisterController::class, 'store'])->name('register.submit');

// OTP
Route::get('/otp', [OtpController::class, 'show'])->name('otp.view');
Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');

// Login routes
Route::get('/', function () {
    return view('user.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email|min:15|max:255',
        'password' => 'required|string|min:8|max:50',
    ], [
        'email.min' => 'Email must be at least 15 characters long.',
        'email.email' => 'Please enter a valid email address.',
        'email.required' => 'Email is required.',
        'password.min' => 'Password must be at least 8 characters long.',
        'password.max' => 'Password must not exceed 50 characters.',
    ]);

    // Check if this is admin trying to login on wrong page
    if ($credentials['email'] === 'admin@gmail.com') {
        return redirect()->route('admin.dashboard')->with('info', 'Redirected to admin dashboard.');
    }

    // Only use email for login and check if user is verified
    if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->filled('remember'))) {
        $user = Auth::user();

        // Check if user is verified
        if (!$user->is_verified) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Please verify your email before logging in.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->name('login.submit');// Forgot Password routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

// Dashboard - Protected route that shows the EV charging dashboard
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// Booking Form Route
Route::get('/book-station', function (Request $request) {
    $stationId = $request->get('station');

    // Validate station exists and has available slots
    if ($stationId) {
        $station = \App\Models\Station::find($stationId);

        if (!$station) {
            return redirect()->route('dashboard')->with('error', 'Station not found.');
        }

        // Check if station has any available slots
        if ($station->available_slots <= 0) {
            return redirect()->route('dashboard')->with('error', 'This charging station is fully reserved. Please select another station.');
        }
    }

    return view('booking-form');
})->middleware('auth')->name('booking.form');

// Payment routes
Route::get('/booking/payment/success', [App\Http\Controllers\BookingController::class, 'handlePaymentSuccess'])->middleware('auth')->name('booking.payment.success');

// Stripe Payment Route
Route::get('/stripe-payment', [App\Http\Controllers\StripePaymentController::class, 'index'])->middleware('auth')->name('stripe.payment');
Route::post('/stripe-payment/process', [App\Http\Controllers\StripePaymentController::class, 'processPayment'])->middleware('auth')->name('stripe.process');

// AJAX endpoints for dashboard functionality
Route::middleware('auth')->group(function () {
    Route::get('/api/nearby-stations', [App\Http\Controllers\DashboardController::class, 'getNearbyStations'])->name('api.nearby-stations');
    Route::post('/api/create-booking', [App\Http\Controllers\DashboardController::class, 'createBooking'])->name('api.create-booking');
    Route::post('/api/update-profile', [App\Http\Controllers\DashboardController::class, 'updateProfile'])->name('api.update-profile');
});

// User dashboard routes
Route::get('/bookings', function () {
    return view('user.bookings');
})->middleware('auth')->name('user.bookings');

Route::get('/reservations', function () {
    return view('user.reservations');
})->middleware('auth')->name('user.reservations');

Route::get('/profile', function () {
    return view('user.profile');
})->middleware('auth')->name('user.profile');

// Admin routes - Separate pages for better organization
Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
Route::get('/admin/stations', [App\Http\Controllers\AdminController::class, 'stations'])->name('admin.stations');
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->name('admin.index');

// Admin API routes for data management
Route::get('/admin/api/dashboard-stats', [App\Http\Controllers\AdminController::class, 'getDashboardStats'])->name('admin.api.dashboard-stats');
Route::get('/admin/api/users', [App\Http\Controllers\AdminController::class, 'getUsers'])->name('admin.api.users');
Route::post('/admin/api/users/{id}/toggle-status', [App\Http\Controllers\AdminController::class, 'toggleUserStatus'])->name('admin.api.users.toggle');
Route::delete('/admin/api/users/{id}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.api.users.delete');

Route::get('/admin/api/stations', [App\Http\Controllers\AdminController::class, 'getStations'])->name('admin.api.stations.list');
Route::post('/admin/api/stations', [App\Http\Controllers\AdminController::class, 'addStation'])->name('admin.api.stations');
Route::delete('/admin/api/stations/{id}', [App\Http\Controllers\AdminController::class, 'deleteStation'])->name('admin.api.stations.delete');

// Admin booking management routes
Route::get('/admin/bookings', [App\Http\Controllers\AdminController::class, 'bookings'])->name('admin.bookings');
Route::get('/admin/api/bookings', [App\Http\Controllers\AdminController::class, 'getBookings'])->name('admin.api.bookings');
Route::post('/admin/api/bookings/{id}/status', [App\Http\Controllers\AdminController::class, 'updateBookingStatus'])->name('admin.api.bookings.status');

// User reservation API routes (for web-authenticated users)
Route::post('/api/reservations/{userId}', [App\Http\Controllers\ReservationController::class, 'store'])->name('api.reservations.store');
Route::get('/api/reservations/{userId}', [App\Http\Controllers\ReservationController::class, 'index'])->name('api.reservations.index');

// Booking API routes for web-authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/api/bookings/{userId}', [App\Http\Controllers\BookingController::class, 'getUserBookings'])->name('api.bookings.get');
    Route::post('/api/bookings', [App\Http\Controllers\BookingController::class, 'store'])->name('api.bookings.store');
    Route::post('/api/bookings/{id}/cancel', [App\Http\Controllers\BookingController::class, 'cancel'])->name('api.bookings.cancel');
    Route::put('/api/bookings/{id}', [App\Http\Controllers\BookingController::class, 'update'])->name('api.bookings.update');
});

// Payment success/cancel routes
Route::get('/payment/success', [App\Http\Controllers\BookingController::class, 'paymentSuccess'])->name('payment.success');
Route::get('/payment/cancel', function () {
    return view('payment.cancel');
})->name('payment.cancel');

// Premium subscription routes
Route::get('/premium/subscribe', function () {
    return view('premium.subscribe');
})->name('premium.subscribe');

// Test page for booking API
Route::get('/test-booking', function () {
    return view('test_booking_api');
})->name('test.booking');

// Test auth endpoint
Route::get('/api/test-auth', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user_name' => Auth::user()?->name ?? 'Not authenticated'
    ]);
})->middleware('auth')->name('api.test.auth');

// Profile update route
Route::middleware(['auth'])->group(function () {
    Route::post('/dashboard/profile', [App\Http\Controllers\DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
});

// Subscription routes
Route::middleware(['auth'])->group(function () {
    // Main subscription plans page
    Route::get('/subscription/plans', function () {
        return view('subscription-plans');
    })->name('subscription.plans');

    // Legacy redirect for old subscription route
    Route::get('/subscription', function () {
        return redirect()->route('subscription.plans');
    })->name('subscription.index');

    Route::post('/subscription/checkout', [App\Http\Controllers\SubscriptionController::class, 'createCheckoutSession'])->name('subscription.checkout');
    Route::get('/subscription/success', [App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');
    Route::post('/subscription/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/status', [App\Http\Controllers\SubscriptionController::class, 'status'])->name('subscription.status');
});
