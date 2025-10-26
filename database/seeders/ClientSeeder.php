<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 random clients using the factory
        \App\Models\Client::factory(50)->create();

        // Create some specific test clients
            // Create or update some specific test clients (idempotent)
            \App\Models\Client::updateOrCreate([
                'email' => 'contact@techcorp.com',
            ], [
                'name' => 'TechCorp Solutions',
                'phone' => '+1-555-0101',
                'address' => '123 Tech Street',
                'city' => 'San Francisco',
                'country' => 'USA',
                'postal_code' => '94105',
                'is_active' => true,
                'last_order_at' => now()->subDays(5),
            ]);

            \App\Models\Client::updateOrCreate([
                'email' => 'info@globalent.com',
            ], [
                'name' => 'Global Enterprises',
                'phone' => '+44-20-7946-0958',
                'address' => '456 Business Avenue',
                'city' => 'London',
                'country' => 'UK',
                'postal_code' => 'EC1A 1BB',
                'is_active' => true,
                'last_order_at' => now()->subDays(15),
            ]);

            \App\Models\Client::updateOrCreate([
                'email' => 'support@inactive.com',
            ], [
                'name' => 'Inactive Support',
                'phone' => '+33-1-2345-6789',
                'address' => '789 Old Rd',
                'city' => 'Paris',
                'country' => 'France',
                'postal_code' => '75001',
                'is_active' => false,
                'last_order_at' => now()->subMonths(6),
            ]);
    }
}
