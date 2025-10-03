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
            $table->string('subscription_type')->default('free')->after('email_verified_at');
            $table->timestamp('subscription_start_date')->nullable()->after('subscription_type');
            $table->timestamp('subscription_end_date')->nullable()->after('subscription_start_date');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_type', 'subscription_start_date', 'subscription_end_date', 'stripe_payment_intent_id']);
        });
    }
};
