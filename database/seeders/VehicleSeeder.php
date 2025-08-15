<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'license_plate' => 'TR-789',
                'make' => 'Ford',
                'model' => 'Transit',
                'year' => 2022,
                'vin' => 'WF0AXXGBWAXW12345',
                'color' => 'White',
                'fuel_type' => 'diesel',
                'capacity' => 80.0,
                'mileage' => 45000,
                'status' => 'active',
                'purchase_date' => '2022-01-15',
                'purchase_price' => 35000.00,
                'insurance_expiry' => '2025-01-15',
                'registration_expiry' => '2025-01-15',
            ],
            [
                'license_plate' => 'SP-456',
                'make' => 'Mercedes',
                'model' => 'Sprinter',
                'year' => 2021,
                'vin' => 'WDF90700123456789',
                'color' => 'Silver',
                'fuel_type' => 'diesel',
                'capacity' => 75.0,
                'mileage' => 52000,
                'status' => 'active',
                'purchase_date' => '2021-03-20',
                'purchase_price' => 42000.00,
                'insurance_expiry' => '2024-12-20',
                'registration_expiry' => '2024-12-20',
            ],
            [
                'license_plate' => 'VH-123',
                'make' => 'Volvo',
                'model' => 'FH16',
                'year' => 2020,
                'vin' => 'YV2A0D0C0LA123456',
                'color' => 'Blue',
                'fuel_type' => 'diesel',
                'capacity' => 300.0,
                'mileage' => 78000,
                'status' => 'active',
                'purchase_date' => '2020-06-10',
                'purchase_price' => 85000.00,
                'insurance_expiry' => '2024-11-10',
                'registration_expiry' => '2024-11-10',
            ],
            [
                'license_plate' => 'TH-987',
                'make' => 'Toyota',
                'model' => 'Hilux',
                'year' => 2023,
                'vin' => 'MR0FB22G0N0123456',
                'color' => 'Red',
                'fuel_type' => 'gasoline',
                'capacity' => 80.0,
                'mileage' => 15000,
                'status' => 'active',
                'purchase_date' => '2023-02-28',
                'purchase_price' => 28000.00,
                'insurance_expiry' => '2025-02-28',
                'registration_expiry' => '2025-02-28',
            ],
            [
                'license_plate' => 'IS-654',
                'make' => 'Isuzu',
                'model' => 'NPR',
                'year' => 2019,
                'vin' => 'JALC4B16097123456',
                'color' => 'Yellow',
                'fuel_type' => 'diesel',
                'capacity' => 100.0,
                'mileage' => 95000,
                'status' => 'maintenance',
                'purchase_date' => '2019-08-15',
                'purchase_price' => 45000.00,
                'insurance_expiry' => '2024-08-15',
                'registration_expiry' => '2024-08-15',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
