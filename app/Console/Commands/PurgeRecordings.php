<?php

namespace App\Console\Commands;

use App\Models\Recording;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PurgeRecordings extends Command
{
    protected $signature = 'app:purge-recordings';

    protected $description = 'Elimina grabaciones completadas que superaron la retención de su cámara';

    public function handle(): int
    {
        $purged = 0;
        Recording::with('camera')->where('status', 'completed')->chunkById(100, function ($recordings) use (&$purged): void {
            foreach ($recordings as $recording) {
                if (! $recording->ended_at || $recording->ended_at->greaterThan(Carbon::now()->subDays($recording->camera->retention_days))) {
                    continue;
                }

                Storage::disk('public')->delete($recording->path);
                $recording->update(['status' => 'purged']);
                $purged++;
            }
        });

        $this->info("Grabaciones purgadas: {$purged}.");

        return self::SUCCESS;
    }
}