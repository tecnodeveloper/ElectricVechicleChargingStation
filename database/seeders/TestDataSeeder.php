<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Station;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@evc.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567890',
                'is_admin' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        // Create test regular users if not exists
        User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password123'),
                'phone' => '+1987654321',
                'is_admin' => false,
                'is_verified' => true,
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password123'),
                'phone' => '+1555666777',
                'is_admin' => false,
                'is_verified' => false,
                'status' => 'active',
            ]
        );

        // Create test charging stations
        $stations = [
            [
                'name' => 'Downtown Charging Hub',
                'location' => 'Downtown District',
                'address' => '123 Main Street, Downtown',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'connector_type' => 'Type 2',
                'power_output' => 150.0,
                'pricing_per_hour' => 25.00,
                'is_available' => true,
                'description' => 'Fast charging station in the heart of downtown',
                'amenities' => json_encode(['parking', 'wifi', 'restroom', 'cafe'])
            ],
            [
                'name' => 'Mall Charging Station',
                'location' => 'Shopping District',
                'address' => '456 Shopping Center Blvd',
                'latitude' => 40.7589,
                'longitude' => -73.9851,
                'connector_type' => 'CCS',
                'power_output' => 100.0,
                'pricing_per_hour' => 20.00,
                'is_available' => true,
                'description' => 'Convenient charging while you shop',
                'amenities' => json_encode(['parking', 'shopping', 'food_court'])
            ],
            [
                'name' => 'Airport Charging Point',
                'location' => 'Airport Terminal',
                'address' => '789 Airport Terminal Way',
                'latitude' => 40.6892,
                'longitude' => -74.1745,
                'connector_type' => 'CHAdeMO',
                'power_output' => 200.0,
                'pricing_per_hour' => 30.00,
                'is_available' => false,
                'description' => 'High-speed charging at the airport',
                'amenities' => json_encode(['parking', 'terminal_access', 'security'])
            ],
            [
                'name' => 'Highway Rest Stop',
                'location' => 'Highway 95 North',
                'address' => '321 Highway 95 North',
                'latitude' => 40.8176,
                'longitude' => -74.0431,
                'connector_type' => 'Type 2',
                'power_output' => 120.0,
                'pricing_per_hour' => 22.00,
                'is_available' => true,
                'description' => 'Perfect for long-distance travel',
                'amenities' => json_encode(['parking', 'restroom', 'vending_machines'])
            ]
        ];

        foreach ($stations as $stationData) {
            Station::create($stationData);
        }

        // Create some test bookings
        $user = User::where('email', 'john@example.com')->first();
        $station = Station::first();

        Booking::create([
            'user_id' => $user->id,
            'station_id' => $station->id,
            'start_time' => now()->addHours(2),
            'end_time' => now()->addHours(4),
            'status' => 'active',
            'total_cost' => 50.00,
            'estimated_energy_needed' => 45.0,
        ]);

        Booking::create([
            'user_id' => $user->id,
            'station_id' => $station->id,
            'start_time' => now()->subDays(1),
            'end_time' => now()->subDays(1)->addHours(2),
            'actual_start_time' => now()->subDays(1),
            'actual_end_time' => now()->subDays(1)->addHours(2),
            'status' => 'completed',
            'total_cost' => 50.00,
            'estimated_energy_needed' => 40.0,
            'actual_energy_consumed' => 38.5,
        ]);
    }
}
