<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;

class BookingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $user;
    public $station;
    public $stats;

    /**
     * Create a new event instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load(['user', 'station']);
        $this->user = $this->booking->user;
        $this->station = $this->booking->station;

        // Calculate updated stats for admin dashboard
        // Revenue calculation rules:
        // 1. Monthly revenue: Only count completed bookings in current month
        // 2. Total revenue: All approved bookings (updates after every approval)
        // 3. Today revenue: All approved bookings today
        $this->stats = [
            'pendingBookings' => \App\Models\Booking::where('status', 'pending')->count(),
            'activeBookings' => \App\Models\Booking::where('status', 'active')->count(),
            'approvedBookings' => \App\Models\Booking::where('status', 'approved')->count(),
            'completedBookings' => \App\Models\Booking::where('status', 'completed')->count(),

            // Monthly revenue: Only completed bookings in the current month
            'monthlyRevenue' => \App\Models\Booking::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->where('status', 'completed')
                                  ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')),

            // Today revenue: All approved bookings today (approved means money confirmed)
            'todayRevenue' => \App\Models\Booking::whereDate('updated_at', now()->toDateString())
                                 ->whereIn('status', ['approved', 'active', 'completed'])
                                 ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')),

            // Total revenue: All approved/active/completed bookings (updates after every approval)
            'totalRevenue' => \App\Models\Booking::whereIn('status', ['approved', 'active', 'completed'])
                                 ->sum(DB::raw('COALESCE(total_amount, total_cost, 0)')),

            'totalBookings' => \App\Models\Booking::count(),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-dashboard'),
            new Channel('bookings-channel')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking' => [
                'id' => $this->booking->id,
                'user_id' => $this->booking->user_id,
                'station_id' => $this->booking->station_id,
                'status' => $this->booking->status,
                'total_amount' => $this->booking->total_amount,
                'start_time' => $this->booking->start_time,
                'end_time' => $this->booking->end_time,
                'created_at' => $this->booking->created_at,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'station' => [
                'id' => $this->station->id,
                'name' => $this->station->name,
                'address' => $this->station->address,
            ],
            'stats' => $this->stats,
            'message' => "New booking created by {$this->user->name} at {$this->station->name}",
            'timestamp' => now()->toISOString()
        ];
    }
}
