<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find comptes where the blockage period has expired
        $expiredBlockedComptes = \App\Models\Compte::whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<', now())
            ->where('is_archived', false)
            ->get();

        foreach ($expiredBlockedComptes as $compte) {
            // Archive the compte and its transactions
            $compte->archive();

            // Log the archiving action
            \Illuminate\Support\Facades\Log::info('Archived expired blocked compte', [
                'compte_id' => $compte->id,
                'numero' => $compte->numero,
                'date_fin_blocage' => $compte->date_fin_blocage,
                'archived_at' => $compte->archived_at,
            ]);
        }
    }
}
