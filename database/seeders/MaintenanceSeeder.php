<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Maintenance;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maintenances = [
            [
                'vehicle_id' => 1, // Ford Transit
                'type' => 'oil_change',
                'description' => 'Regular oil change and filter replacement',
                'scheduled_date' => now()->addDays(5),
                'completed_date' => null,
                'cost' => null,
                'mileage' => 45000,
                'status' => 'pending',
                'service_provider' => 'AutoCare Plus',
                'notes' => 'Urgent - overdue by 2000 miles',
            ],
            [
                'vehicle_id' => 2, // Mercedes Sprinter
                'type' => 'tire_rotation',
                'description' => 'Rotate tires and check alignment',
                'scheduled_date' => now()->addDays(8),
                'completed_date' => null,
                'cost' => null,
                'mileage' => 52000,
                'status' => 'pending',
                'service_provider' => 'Mercedes Service Center',
                'notes' => 'Due soon - schedule appointment',
            ],
            [
                'vehicle_id' => 3, // Volvo FH16
                'type' => 'brake_inspection',
                'description' => 'Complete brake system inspection',
                'scheduled_date' => now()->addDays(12),
                'completed_date' => null,
                'cost' => null,
                'mileage' => 78000,
                'status' => 'pending',
                'service_provider' => 'Volvo Trucks',
                'notes' => 'Annual safety inspection',
            ],
            [
                'vehicle_id' => 4, // Toyota Hilux
                'type' => 'engine_tune_up',
                'description' => 'Engine tune-up and performance check',
                'scheduled_date' => now()->addDays(20),
                'completed_date' => null,
                'cost' => null,
                'mileage' => 15000,
                'status' => 'pending',
                'service_provider' => 'Toyota Service',
                'notes' => 'Scheduled maintenance',
            ],
            [
                'vehicle_id' => 5, // Isuzu NPR
                'type' => 'battery_check',
                'description' => 'Battery replacement and electrical system check',
                'scheduled_date' => now()->addDays(25),
                'completed_date' => null,
                'cost' => null,
                'mileage' => 95000,
                'status' => 'pending',
                'service_provider' => 'Fleet Maintenance Co',
                'notes' => 'Battery showing signs of weakness',
            ],
            [
                'vehicle_id' => 1, // Ford Transit
                'type' => 'oil_change',
                'description' => 'Previous oil change service',
                'scheduled_date' => now()->subDays(90),
                'completed_date' => now()->subDays(88),
                'cost' => 85.50,
                'mileage' => 42000,
                'status' => 'completed',
                'service_provider' => 'AutoCare Plus',
                'notes' => 'Completed on time',
            ],
        ];

        foreach ($maintenances as $maintenance) {
            Maintenance::create($maintenance);
        }
    }
}
