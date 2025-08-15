<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use App\Models\Driver;

class FuelRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::all();
        $drivers = Driver::all();

        if ($vehicles->isEmpty() || $drivers->isEmpty()) {
            return;
        }

        $fuelRecords = [
            [
                'vehicle_id' => $vehicles->first()->id,
                'driver_id' => $drivers->first()->id,
                'fuel_date' => '2024-01-15',
                'fuel_type' => 'diesel',
                'quantity' => 45.5,
                'cost_per_liter' => 1.45,
                'total_cost' => 65.98,
                'mileage' => 45000,
                'location' => 'Shell Station Downtown',
                'receipt_number' => 'SH001234',
                'notes' => 'Regular fuel stop',
            ],
            [
                'vehicle_id' => $vehicles->skip(1)->first()->id ?? $vehicles->first()->id,
                'driver_id' => $drivers->skip(1)->first()->id ?? $drivers->first()->id,
                'fuel_date' => '2024-01-20',
                'fuel_type' => 'diesel',
                'quantity' => 52.0,
                'cost_per_liter' => 1.42,
                'total_cost' => 73.84,
                'mileage' => 52000,
                'location' => 'BP Station Highway',
                'receipt_number' => 'BP005678',
                'notes' => 'Long distance trip fuel',
            ],
            [
                'vehicle_id' => $vehicles->first()->id,
                'driver_id' => $drivers->first()->id,
                'fuel_date' => '2024-02-01',
                'fuel_type' => 'diesel',
                'quantity' => 38.2,
                'cost_per_liter' => 1.48,
                'total_cost' => 56.54,
                'mileage' => 45500,
                'location' => 'Esso Station Central',
                'receipt_number' => 'ES009876',
                'notes' => 'Emergency fuel stop',
            ],
        ];

        foreach ($fuelRecords as $record) {
            FuelRecord::create($record);
        }
    }
}
