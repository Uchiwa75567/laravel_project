<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing comptes or create some if none exist
        $comptes = \App\Models\Compte::all();
        if ($comptes->isEmpty()) {
            $comptes = \App\Models\Compte::factory(10)->create();
        }

        // Create transactions for each compte (2-3 transactions per compte for production)
        foreach ($comptes as $compte) {
            $numTransactions = rand(2, 3);
            for ($i = 0; $i < $numTransactions; $i++) {
                \App\Models\Transaction::factory()->forCompte($compte)->create();
            }
        }

        // Create some specific test transactions
        $techCorpComptes = \App\Models\Compte::whereHas('client', function ($query) {
            $query->where('email', 'contact@techcorp.com');
        })->get();

        if ($techCorpComptes->isNotEmpty()) {
            $chequeCompte = $techCorpComptes->where('type', 'cheque')->first();
            $epargneCompte = $techCorpComptes->where('type', 'epargne')->first();

            if ($chequeCompte) {
                // Dépôt initial
                \App\Models\Transaction::factory()->forCompte($chequeCompte)->create([
                    'type' => 'depot',
                    'montant' => 10000.00,
                    'description' => 'Dépôt initial société',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subMonths(6),
                ]);

                // Quelques retraits et paiements
                \App\Models\Transaction::factory()->forCompte($chequeCompte)->create([
                    'type' => 'paiement',
                    'montant' => 2500.00,
                    'description' => 'Paiement fournisseur informatique',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subMonths(5),
                ]);

                \App\Models\Transaction::factory()->forCompte($chequeCompte)->create([
                    'type' => 'retrait',
                    'montant' => 1000.00,
                    'description' => 'Retrait espèces',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subDays(15),
                ]);
            }

            if ($epargneCompte && $chequeCompte) {
                // Virement du compte cheque vers épargne
                \App\Models\Transaction::factory()->betweenComptes($chequeCompte, $epargneCompte)->create([
                    'montant' => 5000.00,
                    'description' => 'Virement vers compte épargne',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subMonths(4),
                ]);

                // Et la transaction correspondante sur le compte épargne
                \App\Models\Transaction::factory()->forCompte($epargneCompte)->create([
                    'type' => 'virement_recue',
                    'montant' => 5000.00,
                    'description' => 'Virement depuis compte cheque',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subMonths(4),
                ]);
            }
        }

        $globalEntComptes = \App\Models\Compte::whereHas('client', function ($query) {
            $query->where('email', 'info@globalent.com');
        })->get();

        if ($globalEntComptes->isNotEmpty()) {
            $chequeCompte = $globalEntComptes->where('type', 'cheque')->first();

            if ($chequeCompte) {
                // Grande transaction en USD
                \App\Models\Transaction::factory()->forCompte($chequeCompte)->create([
                    'type' => 'depot',
                    'montant' => 25000.00,
                    'devise' => 'USD',
                    'description' => 'Dépôt client international',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subMonths(2),
                ]);

                // Transaction récente
                \App\Models\Transaction::factory()->forCompte($chequeCompte)->create([
                    'type' => 'paiement',
                    'montant' => 5000.00,
                    'devise' => 'USD',
                    'description' => 'Paiement partenaire commercial',
                    'statut' => 'effectuee',
                    'date_transaction' => now()->subDays(3),
                ]);
            }
        }

        // Quelques transactions en attente ou annulées pour les tests
        $randomComptes = \App\Models\Compte::inRandomOrder()->take(3)->get();
        foreach ($randomComptes as $compte) {
            \App\Models\Transaction::factory()->forCompte($compte)->create([
                'type' => 'virement_emis',
                'montant' => 1500.00,
                'description' => 'Virement en attente de validation',
                'statut' => 'en_cours',
                'date_transaction' => now()->subDays(1),
            ]);

            \App\Models\Transaction::factory()->forCompte($compte)->create([
                'type' => 'paiement',
                'montant' => 750.00,
                'description' => 'Paiement annulé',
                'statut' => 'annulee',
                'date_transaction' => now()->subDays(7),
            ]);
        }
    }
}
