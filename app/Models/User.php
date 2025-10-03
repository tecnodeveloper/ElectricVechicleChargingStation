<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'otp',
        'otp_expires_at',
        'is_verified',
        'email_verified_at',
        'is_admin',
        'status',
        'suspended_at',
        'subscription_plan',
        'subscription_expires_at',
        'subscription_type',
        'subscription_start_date',
        'subscription_end_date',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_payment_intent_id',
        'subscription_features',
        'weekly_bookings_used',
        'weekly_reset_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'suspended_at' => 'datetime',
            'subscription_start_date' => 'datetime',
            'subscription_end_date' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_admin' => 'boolean',
            'subscription_expires_at' => 'datetime',
            'subscription_features' => 'array',
            'weekly_reset_at' => 'datetime',
        ];
    }

    /**
     * Get the bookings for the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the vehicle preferences for the user.
     */
    public function vehiclePreferences()
    {
        return $this->hasMany(VehiclePreference::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the payment methods for the user.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Check if user is suspended.
     */
    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    /**
     * Get user's primary vehicle preference.
     */
    public function primaryVehiclePreference()
    {
        return $this->vehiclePreferences()->where('is_primary', true)->first();
    }

    /**
     * Check if user has premium subscription.
     */
    public function isPremium()
    {
        // Check if user has premium subscription type
        $hasPremiumPlan = $this->subscription_plan === 'premium' || $this->subscription_type === 'premium';

        if (!$hasPremiumPlan) {
            return false;
        }

        // Check if subscription is not expired
        $isNotExpired = true;

        // Check subscription_expires_at
        if ($this->subscription_expires_at !== null) {
            if (is_string($this->subscription_expires_at)) {
                $isNotExpired = $isNotExpired && Carbon::parse($this->subscription_expires_at)->isFuture();
            } else {
                $isNotExpired = $isNotExpired && $this->subscription_expires_at->isFuture();
            }
        }

        // Check subscription_end_date
        if ($this->subscription_end_date !== null) {
            if (is_string($this->subscription_end_date)) {
                $isNotExpired = $isNotExpired && Carbon::parse($this->subscription_end_date)->isFuture();
            } else {
                $isNotExpired = $isNotExpired && $this->subscription_end_date->isFuture();
            }
        }

        return $hasPremiumPlan && $isNotExpired;
    }

    /**
     * Check if user can make more bookings this week.
     */
    public function canMakeBooking()
    {
        if ($this->isPremium()) {
            return true; // Premium users have unlimited bookings
        }

        // Reset weekly counter if needed
        $this->resetWeeklyBookingsIfNeeded();

        $weeklyLimit = config('stripe.plans.free.features.booking_limit', 5);
        return $this->weekly_bookings_used < $weeklyLimit;
    }

    /**
     * Increment weekly booking count.
     */
    public function incrementWeeklyBookings()
    {
        $this->resetWeeklyBookingsIfNeeded();
        $this->increment('weekly_bookings_used');
    }

    /**
     * Reset weekly bookings if a week has passed.
     */
    private function resetWeeklyBookingsIfNeeded()
    {
        if ($this->weekly_reset_at === null || $this->weekly_reset_at->isPast()) {
            $this->update([
                'weekly_bookings_used' => 0,
                'weekly_reset_at' => now()->addWeek()
            ]);
        }
    }

    /**
     * Get remaining bookings for the week.
     */
    public function getRemainingBookings()
    {
        if ($this->isPremium()) {
            return null; // Unlimited
        }

        $this->resetWeeklyBookingsIfNeeded();
        $weeklyLimit = config('stripe.plans.free.features.booking_limit', 5);
        return max(0, $weeklyLimit - $this->weekly_bookings_used);
    }

    /**
     * Get subscription features.
     */
    public function getSubscriptionFeatures()
    {
        $plan = $this->subscription_plan ?: ($this->subscription_type ?: 'free');
        $planConfig = config("stripe.plans.{$plan}");

        if (!$planConfig) {
            // Fallback to default free features if config not found
            return config('stripe.plans.free.features', [
                'booking_limit' => 5,
                'unlimited_stations' => false,
                'discounted_pricing' => false,
                'weekly_rewards' => false,
                'discount_percentage' => 0,
            ]);
        }

        return $planConfig['features'];
    }

    /**
     * Get discount percentage for premium users.
     */
    public function getDiscountPercentage()
    {
        if ($this->isPremium()) {
            return config('stripe.plans.premium.features.discount_percentage', 20);
        }
        return 0;
    }
}
