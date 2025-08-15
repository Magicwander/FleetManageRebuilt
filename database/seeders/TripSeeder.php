<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trip;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trips = [
            [
                'vehicle_id' => 1, // Ford Transit
                'driver_id' => 1, // Michael Johnson
                'start_location' => 'Warehouse A',
                'end_location' => 'Downtown',
                'start_time' => now()->subHours(4),
                'end_time' => null,
                'start_mileage' => 45000,
                'end_mileage' => null,
                'distance' => null,
                'status' => 'in_progress',
                'purpose' => 'Delivery',
                'notes' => 'Regular delivery route',
            ],
            [
                'vehicle_id' => 2, // Mercedes Sprinter
                'driver_id' => 2, // Sarah Williams
                'start_location' => 'Main Office',
                'end_location' => 'Airport',
                'start_time' => now()->subHours(2),
                'end_time' => null,
                'start_mileage' => 52000,
                'end_mileage' => null,
                'distance' => null,
                'status' => 'in_progress',
                'purpose' => 'Airport Transfer',
                'notes' => 'VIP client pickup',
            ],
            [
                'vehicle_id' => 3, // Volvo FH16
                'driver_id' => 3, // Robert Davis
                'start_location' => 'City Center',
                'end_location' => 'Industrial Zone',
                'start_time' => now()->subHours(1),
                'end_time' => null,
                'start_mileage' => 78000,
                'end_mileage' => null,
                'distance' => null,
                'status' => 'in_progress',
                'purpose' => 'Heavy Cargo',
                'notes' => 'Delayed due to traffic',
            ],
            [
                'vehicle_id' => 4, // Toyota Hilux
                'driver_id' => 4, // Jennifer Brown
                'start_location' => 'North Depot',
                'end_location' => 'South Terminal',
                'start_time' => now()->subMinutes(30),
                'end_time' => null,
                'start_mileage' => 15000,
                'end_mileage' => null,
                'distance' => null,
                'status' => 'in_progress',
                'purpose' => 'Equipment Transport',
                'notes' => 'On schedule',
            ],
            [
                'vehicle_id' => 1, // Ford Transit
                'driver_id' => 1, // Michael Johnson
                'start_location' => 'Warehouse B',
                'end_location' => 'Customer Site',
                'start_time' => now()->subDays(1),
                'end_time' => now()->subDays(1)->addHours(3),
                'start_mileage' => 44800,
                'end_mileage' => 45000,
                'distance' => 200.0,
                'status' => 'completed',
                'purpose' => 'Installation',
                'notes' => 'Successful installation completed',
            ],
        ];

        foreach ($trips as $trip) {
            Trip::create($trip);
        }
    }
}
