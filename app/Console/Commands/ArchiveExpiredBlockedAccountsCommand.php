<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveExpiredBlockedAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comptes:archive-expired-blocked {--dry-run : Show what would be archived without actually archiving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive comptes whose blockage period has expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('DRY RUN MODE: No comptes will be archived.');
        }

        // Find comptes where the blockage period has expired
        $expiredBlockedComptes = \App\Models\Compte::whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<', now())
            ->where('is_archived', false)
            ->get();

        if ($expiredBlockedComptes->isEmpty()) {
            $this->info('No expired blocked comptes found.');
            return;
        }

        $this->info("Found {$expiredBlockedComptes->count()} expired blocked compte(s).");

        foreach ($expiredBlockedComptes as $compte) {
            $this->line("Processing compte: {$compte->numero} (ID: {$compte->id})");

            if (!$isDryRun) {
                // Archive the compte and its transactions
                $compte->archive();

                // Log the archiving action
                \Illuminate\Support\Facades\Log::info('Archived expired blocked compte via command', [
                    'compte_id' => $compte->id,
                    'numero' => $compte->numero,
                    'date_fin_blocage' => $compte->date_fin_blocage,
                    'archived_at' => $compte->archived_at,
                ]);

                $this->info("✓ Archived compte: {$compte->numero}");
            } else {
                $this->info("Would archive compte: {$compte->numero}");
            }
        }

        if (!$isDryRun) {
            $this->info('Archiving completed successfully.');
        }
    }
}
