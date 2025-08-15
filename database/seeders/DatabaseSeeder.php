<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create Admin User
        User::factory()->create([
            'name' => 'Fleet Admin',
            'email' => 'admin@fleet.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        // Create Customer Users
        User::factory()->create([
            'name' => 'John Customer',
            'email' => 'customer@fleet.com',
            'password' => bcrypt('customer123'),
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Sarah Wilson',
            'email' => 'sarah@fleet.com',
            'password' => bcrypt('customer123'),
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Mike Johnson',
            'email' => 'mike@fleet.com',
            'password' => bcrypt('customer123'),
            'role' => 'customer',
        ]);

        // Seed fleet management data
        $this->call([
            VehicleSeeder::class,
            DriverSeeder::class,
            TripSeeder::class,
            MaintenanceSeeder::class,
            FuelRecordSeeder::class,
        ]);
    }
}
