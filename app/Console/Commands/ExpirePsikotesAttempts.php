<?php

namespace App\Console\Commands;

use App\Models\PsikotesAttempt;
use App\Services\PsikotesService;
use Illuminate\Console\Command;

class ExpirePsikotesAttempts extends Command
{
    protected $signature = 'talenta:expire-attempts';

    protected $description = 'Selesaikan otomatis sesi psikotes yang waktunya habis (jawaban tersimpan tetap dinilai)';

    public function handle(PsikotesService $service): int
    {
        $n = 0;

        PsikotesAttempt::where('status', 'berjalan')
            ->where('expires_at', '<=', now())
            ->each(function ($attempt) use ($service, &$n) {
                $service->submit($attempt);
                $n++;
            });

        $this->info($n . ' sesi psikotes diselesaikan.');

        return self::SUCCESS;
    }
}
