<?php

namespace Modules\Export\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Export\Models\Export;

class PurgeExpiredExportsCommand extends Command
{
    protected $signature   = 'exports:purge';
    protected $description = 'Delete expired export files and records';

    public function handle(): int
    {
        $expired = Export::query()
            ->where('status', 'completed')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expired as $export) {
            if ($export->path && Storage::disk('local')->exists(dirname($export->path))) {
                Storage::disk('local')->deleteDirectory(dirname($export->path));
            }

            $export->delete();
            $count++;
        }

        $this->info("Purged {$count} expired export(s).");

        return self::SUCCESS;
    }
}
