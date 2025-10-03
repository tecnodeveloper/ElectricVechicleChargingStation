<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Events\BookingCreated;

class ReservationController extends Controller
{
    public function store(Request $request, $userId)
    {
        // Get the user to check subscription limits
        $user = \App\Models\User::findOrFail($userId);

        // Check if user can make more bookings
        if (!$user->canMakeBooking()) {
            $remainingBookings = $user->getRemainingBookings();
            return response()->json([
                'success' => false,
                'error' => 'You have reached your weekly booking limit. ' .
                          ($user->isPremium() ? '' : "Remaining bookings: $remainingBookings. Upgrade to Premium for unlimited bookings."),
                'upgrade_required' => !$user->isPremium(),
                'subscription_url' => route('subscription.index')
            ], 403);
        }

        // Validate the request
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'total_amount' => 'required|numeric|min:0',
            'duration_hours' => 'required|numeric|min:0.5'
        ]);

        // Apply premium discount if applicable
        $discountPercentage = $user->getDiscountPercentage();
        if ($discountPercentage > 0) {
            $validated['total_amount'] = $validated['total_amount'] * (1 - $discountPercentage / 100);
        }

        $reservation = Booking::create([
            'user_id' => $userId,
            'station_id' => $validated['station_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending', // Changed from 'active' to 'pending' for admin approval
            'total_amount' => $validated['total_amount'],
            'duration_hours' => $validated['duration_hours'],
        ]);

        // Increment weekly booking count for free users
        $user->incrementWeeklyBookings();

        // Load relationships for the event
        $reservation->load(['user', 'station']);

        // Broadcast the booking created event for real-time admin updates
        broadcast(new BookingCreated($reservation));

        $message = 'Booking request submitted successfully! Awaiting admin approval.';
        if ($discountPercentage > 0) {
            $message .= " Premium discount of {$discountPercentage}% applied.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'reservation' => $reservation,
            'discount_applied' => $discountPercentage,
            'remaining_bookings' => $user->getRemainingBookings()
        ]);
    }

    public function index($userId)
    {
        return response()->json([
            'reservations' => Booking::with(['user', 'station'])
                                   ->where('user_id', $userId)
                                   ->orderBy('created_at', 'desc')
                                   ->get()
        ]);
    }

    public function destroy($reservationId)
    {
        Booking::destroy($reservationId);
        return response()->json(['message' => 'Reservation cancelled']);
    }

    public function all()
    {
        return response()->json(['reservations' => Booking::all()]);
    }
}
