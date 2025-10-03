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
            // Update the status enum to include approved and denied
            $table->enum('status', ['pending', 'approved', 'denied', 'confirmed', 'active', 'completed', 'cancelled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled'])->default('pending')->change();
        });
    }
};
