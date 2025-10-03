<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_plan')->default('free')->after('email_verified_at');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_plan');
            $table->string('stripe_customer_id')->nullable()->after('subscription_expires_at');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            $table->json('subscription_features')->nullable()->after('stripe_subscription_id');
            $table->integer('weekly_bookings_used')->default(0)->after('subscription_features');
            $table->timestamp('weekly_reset_at')->nullable()->after('weekly_bookings_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'subscription_expires_at',
                'stripe_customer_id',
                'stripe_subscription_id',
                'subscription_features',
                'weekly_bookings_used',
                'weekly_reset_at'
            ]);
        });
    }
};
