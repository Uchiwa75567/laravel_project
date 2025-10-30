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
            // Archive the compte to Neon PostgreSQL
            $this->archiveToNeon($compte);

            // Log the archiving action
            \Illuminate\Support\Facades\Log::info('Archived expired blocked compte', [
                'compte_id' => $compte->id,
                'numero' => $compte->numero,
                'date_fin_blocage' => $compte->date_fin_blocage,
                'archived_at' => now(),
            ]);
        }
    }

    /**
     * Archive le compte vers la base Neon PostgreSQL
     */
    private function archiveToNeon(\App\Models\Compte $compte): void
    {
        \App\Models\ArchivedAccount::create([
            'numero' => $compte->numero,
            'type' => $compte->type,
            'devise' => $compte->devise,
            'date_ouverture' => $compte->date_ouverture,
            'client_id' => $compte->client_id,
            'is_blocked' => true,
            'blocked_until' => $compte->date_fin_blocage,
            'blocked_reason' => $compte->motif_blocage,
            'archived_at' => now(),
            'original_id' => $compte->id,
        ]);

        // Marquer le compte comme archivé dans la base principale
        $compte->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);
    }
}
