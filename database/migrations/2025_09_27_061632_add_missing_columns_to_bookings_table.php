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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('actual_start_time')->nullable();
            $table->timestamp('actual_end_time')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->decimal('estimated_energy_needed', 8, 2)->nullable();
            $table->decimal('actual_energy_consumed', 8, 2)->nullable();
            $table->boolean('cancelled_by_admin')->default(false);
            $table->string('cancellation_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'actual_start_time',
                'actual_end_time',
                'total_cost',
                'estimated_energy_needed',
                'actual_energy_consumed',
                'cancelled_by_admin',
                'cancellation_reason'
            ]);
        });
    }
};
