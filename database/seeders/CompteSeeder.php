<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing clients or create some if none exist
        $clients = \App\Models\Client::all();
        if ($clients->isEmpty()) {
            $clients = \App\Models\Client::factory(10)->create();
        }

        // Create comptes for each client (1 compte per client for production)
        foreach ($clients as $client) {
            \App\Models\Compte::factory()->forClient($client)->create();
        }

        // Create some specific test comptes
        $techCorp = \App\Models\Client::where('email', 'contact@techcorp.com')->first();
        if ($techCorp) {
            \App\Models\Compte::factory()->forClient($techCorp)->create([
                'type' => 'courant',
                'solde' => 15000.50,
                'devise' => 'EUR',
                'is_active' => true,
                'date_ouverture' => now()->subMonths(6),
                'last_transaction_at' => now()->subDays(2),
            ]);

            \App\Models\Compte::factory()->forClient($techCorp)->create([
                'type' => 'epargne',
                'solde' => 25000.75,
                'devise' => 'EUR',
                'is_active' => true,
                'date_ouverture' => now()->subYear(),
                'last_transaction_at' => now()->subDays(30),
            ]);
        }

        $globalEnt = \App\Models\Client::where('email', 'info@globalent.com')->first();
        if ($globalEnt) {
            \App\Models\Compte::factory()->forClient($globalEnt)->create([
                'type' => 'entreprise',
                'solde' => 50000.00,
                'devise' => 'USD',
                'is_active' => true,
                'date_ouverture' => now()->subMonths(12),
                'last_transaction_at' => now()->subDays(5),
            ]);
        }

        $inactiveClient = \App\Models\Client::where('email', 'support@inactive.com')->first();
        if ($inactiveClient) {
            \App\Models\Compte::factory()->forClient($inactiveClient)->create([
                'type' => 'courant',
                'solde' => 0.00,
                'devise' => 'EUR',
                'is_active' => false,
                'date_ouverture' => now()->subYears(2),
                'last_transaction_at' => now()->subMonths(8),
            ]);
        }
    }
}
