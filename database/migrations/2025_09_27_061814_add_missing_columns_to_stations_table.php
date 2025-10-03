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
        Schema::table('stations', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('connector_type')->nullable();
            $table->decimal('power_output', 8, 2)->nullable();
            $table->decimal('pricing_per_hour', 8, 2)->nullable();
            $table->json('amenities')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'connector_type',
                'power_output',
                'pricing_per_hour',
                'amenities',
                'description'
            ]);
        });
    }
};
