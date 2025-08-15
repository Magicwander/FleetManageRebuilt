<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Fleet Admin',
            'email' => 'admin@fleetcompany.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create manager user
        User::create([
            'name' => 'John Manager',
            'email' => 'manager@fleetcompany.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'status' => 'active',
        ]);

        // Create dispatcher user
        User::create([
            'name' => 'Sarah Dispatcher',
            'email' => 'dispatcher@fleetcompany.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'dispatcher',
            'status' => 'active',
        ]);

        // Create mechanic user
        User::create([
            'name' => 'Mike Mechanic',
            'email' => 'mechanic@fleetcompany.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'role' => 'mechanic',
            'status' => 'active',
        ]);

        // Create inactive user for testing
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@fleetcompany.com',
            'email_verified_at' => now(),
            'password' => Hash::make('demo123'),
            'role' => 'dispatcher',
            'status' => 'inactive',
        ]);
    }
}
