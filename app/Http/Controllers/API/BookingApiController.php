<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BookingApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->bookings()->with('station');

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

        $bookings = $query->orderBy('start_time', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'station_id' => 'required|exists:stations,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'estimated_energy_needed' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $station = Station::find($request->station_id);

        if (!$station->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Station is not available'
            ], 400);
        }

        // Check for conflicts
        $conflicts = Booking::where('station_id', $request->station_id)
            ->where('status', 'active')
            ->where(function($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })->exists();

        if ($conflicts) {
            return response()->json([
                'success' => false,
                'message' => 'Station is already booked for the selected time'
            ], 400);
        }

        // Calculate cost
        $startTime = new \DateTime($request->start_time);
        $endTime = new \DateTime($request->end_time);
        $duration = $startTime->diff($endTime);
        $hours = $duration->h + ($duration->i / 60);
        $cost = $hours * $station->pricing_per_hour;

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'station_id' => $request->station_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'active',
            'total_cost' => $cost,
            'total_amount' => $cost, // Add total_amount for admin dashboard
            'estimated_energy_needed' => $request->estimated_energy_needed,
        ]);

        // Load relationships for the event
        $booking->load(['user', 'station']);

        // Broadcast the booking created event for real-time admin dashboard updates
        try {
            broadcast(new \App\Events\BookingCreated($booking))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast booking created event: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $booking = Auth::user()->bookings()->with('station')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = Auth::user()->bookings()->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status === 'completed' || $booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify completed or cancelled bookings'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'sometimes|date|after:now',
            'end_time' => 'sometimes|date|after:start_time',
            'estimated_energy_needed' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Recalculate cost if time changed
        if ($request->has('start_time') || $request->has('end_time')) {
            $startTime = new \DateTime($request->start_time ?? $booking->start_time);
            $endTime = new \DateTime($request->end_time ?? $booking->end_time);
            $duration = $startTime->diff($endTime);
            $hours = $duration->h + ($duration->i / 60);
            $cost = $hours * $booking->station->pricing_per_hour;
            $request->merge(['total_cost' => $cost]);
        }

        $booking->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking->load('station')
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel($id)
    {
        $booking = Auth::user()->bookings()->find($id);

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

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }

    /**
     * Start a charging session.
     */
    public function start($id)
    {
        $booking = Auth::user()->bookings()->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot start charging for this booking'
            ], 400);
        }

        $now = now();
        if ($now < $booking->start_time || $now > $booking->end_time) {
            return response()->json([
                'success' => false,
                'message' => 'Charging can only be started during the booked time'
            ], 400);
        }

        $booking->update([
            'status' => 'charging',
            'actual_start_time' => $now
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Charging session started',
            'data' => $booking
        ]);
    }

    /**
     * Stop a charging session.
     */
    public function stop($id, Request $request)
    {
        $booking = Auth::user()->bookings()->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status !== 'charging') {
            return response()->json([
                'success' => false,
                'message' => 'No active charging session for this booking'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'actual_energy_consumed' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update([
            'status' => 'completed',
            'actual_end_time' => now(),
            'actual_energy_consumed' => $request->actual_energy_consumed
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Charging session completed',
            'data' => $booking
        ]);
    }

    /**
     * Get active bookings.
     */
    public function active()
    {
        $bookings = Auth::user()->bookings()
            ->with('station')
            ->whereIn('status', ['active', 'charging'])
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Get booking history.
     */
    public function history(Request $request)
    {
        $bookings = Auth::user()->bookings()
            ->with('station')
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('start_time', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }
}
