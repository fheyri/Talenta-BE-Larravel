<?php

namespace App\Console\Commands;

use App\Models\JobListing;
use Illuminate\Console\Command;

class ExpireJobs extends Command
{
    protected $signature = 'talenta:expire-jobs';

    protected $description = 'Tandai lowongan yang melewati deadline sebagai expired';

    public function handle(): int
    {
        $n = JobListing::where('status', 'aktif')
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $this->info($n . ' lowongan di-expire.');

        return self::SUCCESS;
    }
}
