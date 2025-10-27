<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FilterSwagger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:filter {--tags=* : List of tags to keep (case-sensitive)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filter generated swagger JSON to show only specific tags (and their paths)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keep = $this->option('tags') ?: ['Authentification', 'Comptes'];

    // Correction du chemin pour l'environnement local
    $docsDir = base_path('storage/api-docs');
    $docsFile = $docsDir . '/' . (config('l5-swagger.documentations.default.paths.docs_json') ?? 'api-docs.json');

        if (!file_exists($docsFile)) {
            $this->error("Swagger docs file not found: $docsFile");
            return 1;
        }

        $json = file_get_contents($docsFile);
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse swagger json: ' . json_last_error_msg());
            return 1;
        }

        // Filter paths: keep only those where any operation's tags intersect with $keep
        $paths = $data['paths'] ?? [];
        $newPaths = [];
        foreach ($paths as $path => $operations) {
            $keepPath = false;
            foreach ($operations as $method => $op) {
                if (isset($op['tags']) && is_array($op['tags'])) {
                    foreach ($op['tags'] as $t) {
                        if (in_array($t, $keep, true)) {
                            $keepPath = true;
                            break 2;
                        }
                    }
                }
            }
            if ($keepPath) {
                $newPaths[$path] = $operations;
            }
        }

        $data['paths'] = $newPaths;

        // Filter tags list in top-level 'tags' (if present)
        if (isset($data['tags']) && is_array($data['tags'])) {
            $data['tags'] = array_values(array_filter($data['tags'], function ($tag) use ($keep) {
                return isset($tag['name']) && in_array($tag['name'], $keep, true);
            }));
        }

        // Optionally prune components.schemas entries that are not referenced? Keep as-is for safety.

        // Backup original
        copy($docsFile, $docsFile . '.bak');

        $written = file_put_contents($docsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if ($written === false) {
            $this->error('Failed to write filtered swagger json');
            return 1;
        }

        $this->info('Filtered swagger documentation written to: ' . $docsFile);
        $this->info('Kept tags: ' . implode(', ', $keep));

        return 0;
    }
}
