<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'station_id',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'payment_session_id',
        'total_cost',
        'total_amount',
        'duration_hours',
        'estimated_energy_needed',
        'actual_energy_consumed',
        'cancelled_by_admin',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'total_amount' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'duration_hours' => 'decimal:2',
        'estimated_energy_needed' => 'decimal:2',
        'actual_energy_consumed' => 'decimal:2',
        'cancelled_by_admin' => 'boolean',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the station that owns the booking.
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get the payment for this booking.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Check if booking is active.
     */
    public function isActive()
    {
        return in_array($this->status, ['active', 'charging']);
    }

    /**
     * Check if booking is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if booking is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the actual duration in hours.
     */
    public function getActualDurationAttribute()
    {
        if ($this->actual_start_time && $this->actual_end_time) {
            $start = new \DateTime($this->actual_start_time);
            $end = new \DateTime($this->actual_end_time);
            $diff = $start->diff($end);
            return $diff->h + ($diff->i / 60);
        }
        return null;
    }

    /**
     * Get the planned duration in hours.
     */
    public function getPlannedDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $start = new \DateTime($this->start_time);
            $end = new \DateTime($this->end_time);
            $diff = $start->diff($end);
            return $diff->h + ($diff->i / 60);
        }
        return null;
    }
}

