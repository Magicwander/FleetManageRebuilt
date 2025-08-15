<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@fleetcompany.com',
                'phone' => '+1-555-0101',
                'license_number' => 'DL123456789',
                'license_expiry' => '2026-05-15',
                'date_of_birth' => '1985-03-20',
                'hire_date' => '2020-01-15',
                'status' => 'active',
                'address' => '123 Main St, City, State 12345',
                'emergency_contact_name' => 'Sarah Johnson',
                'emergency_contact_phone' => '+1-555-0102',
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@fleetcompany.com',
                'phone' => '+1-555-0201',
                'license_number' => 'DL987654321',
                'license_expiry' => '2025-08-22',
                'date_of_birth' => '1990-07-12',
                'hire_date' => '2021-03-10',
                'status' => 'active',
                'address' => '456 Oak Ave, City, State 12345',
                'emergency_contact_name' => 'John Williams',
                'emergency_contact_phone' => '+1-555-0202',
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'Robert',
                'last_name' => 'Davis',
                'email' => 'robert.davis@fleetcompany.com',
                'phone' => '+1-555-0301',
                'license_number' => 'DL456789123',
                'license_expiry' => '2025-12-05',
                'date_of_birth' => '1982-11-30',
                'hire_date' => '2019-06-20',
                'status' => 'active',
                'address' => '789 Pine St, City, State 12345',
                'emergency_contact_name' => 'Lisa Davis',
                'emergency_contact_phone' => '+1-555-0302',
            ],
            [
                'employee_id' => 'EMP004',
                'first_name' => 'Jennifer',
                'last_name' => 'Brown',
                'email' => 'jennifer.brown@fleetcompany.com',
                'phone' => '+1-555-0401',
                'license_number' => 'DL789123456',
                'license_expiry' => '2026-02-18',
                'date_of_birth' => '1988-04-25',
                'hire_date' => '2022-01-05',
                'status' => 'active',
                'address' => '321 Elm St, City, State 12345',
                'emergency_contact_name' => 'Mark Brown',
                'emergency_contact_phone' => '+1-555-0402',
            ],
            [
                'employee_id' => 'EMP005',
                'first_name' => 'Thomas',
                'last_name' => 'Miller',
                'email' => 'thomas.miller@fleetcompany.com',
                'phone' => '+1-555-0501',
                'license_number' => 'DL321654987',
                'license_expiry' => '2025-09-10',
                'date_of_birth' => '1975-12-08',
                'hire_date' => '2018-04-12',
                'status' => 'active',
                'address' => '654 Maple Ave, City, State 12345',
                'emergency_contact_name' => 'Nancy Miller',
                'emergency_contact_phone' => '+1-555-0502',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
