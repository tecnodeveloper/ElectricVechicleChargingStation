<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'location',
        'latitude',
        'longitude',
        'status',
        'type',
        'connector_type',
        'power_rating',
        'power_output',
        'price_per_hour',
        'pricing_per_hour',
        'is_available',
        'amenities',
        'description',
        'total_slots',
        'available_slots',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'power_rating' => 'decimal:2',
        'power_output' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
        'pricing_per_hour' => 'decimal:2',
        'is_available' => 'boolean',
        'amenities' => 'array',
    ];

    /**
     * Get the bookings for the station.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get available slots for a specific time period
     */
    public function getAvailableSlotsForTime($startTime, $endTime)
    {
        $conflictingBookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->count();

        return max(0, $this->total_slots - $conflictingBookings);
    }

    /**
     * Update available slots based on current active bookings
     */
    public function updateAvailableSlots()
    {
        $currentTime = now();
        $activeBookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->count();

        $availableSlots = max(0, $this->total_slots - $activeBookings);

        $this->update(['available_slots' => $availableSlots]);

        return $availableSlots;
    }

    /**
     * Get active bookings for this station.
     */
    public function activeBookings()
    {
        return $this->bookings()->whereIn('status', ['active', 'charging']);
    }

    /**
     * Check if station is currently available.
     */
    public function isCurrentlyAvailable()
    {
        return $this->is_available && $this->activeBookings()->count() === 0;
    }

    /**
     * Get the distance from a given point.
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $radius = 10)
    {
        return $query->selectRaw("*,
            ( 6371 * acos( cos( radians(?) ) *
              cos( radians( latitude ) ) *
              cos( radians( longitude ) - radians(?) ) +
              sin( radians(?) ) *
              sin( radians( latitude ) ) ) ) AS distance",
            [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance');
    }

    /**
     * Scope for available stations.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for specific connector type.
     */
    public function scopeByConnectorType($query, $connectorType)
    {
        return $query->where('connector_type', $connectorType);
    }
}
