<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\PsikotesAnswer;
use App\Models\PsikotesAttempt;
use App\Models\PsikotesOption;
use App\Models\PsikotesQuestion;
use App\Models\PsikotesResultRange;
use App\Models\PsikotesTest;
use App\Models\TokenTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PsikotesService
{
    /**
     * Mulai sesi tes.
     * - Percobaan ke-1..gratis_percobaan (per user per tes) gratis.
     * - Selebihnya memotong token_cost token, dipotong SAAT MULAI dan tidak dikembalikan
     *   (baik user submit, waktu habis, maupun aplikasi ditinggal).
     * - Kalau masih ada sesi yang berjalan, sesi itu dilanjutkan (tidak memotong token lagi).
     */
    public function start(User $user, PsikotesTest $test): PsikotesAttempt
    {
        if (! $test->is_active) {
            throw new ApiException('Tes ini belum tersedia.', 404);
        }

        return DB::transaction(function () use ($user, $test) {
            // kunci baris user supaya dua request bersamaan tidak memakai token yang sama
            $u = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $running = PsikotesAttempt::where('user_id', $u->id)
                ->where('test_id', $test->id)
                ->where('status', 'berjalan')
                ->where('expires_at', '>', now())
                ->first();

            if ($running) {
                return $running;
            }

            $ke = (int) PsikotesAttempt::where('user_id', $u->id)
                ->where('test_id', $test->id)
                ->max('attempt_ke') + 1;

            $tokenUsed = 0;

            if ($ke > $test->gratis_percobaan) {
                $tokenUsed = (int) $test->token_cost;

                if ($u->token_balance < $tokenUsed) {
                    throw new ApiException('Token tidak cukup untuk memulai tes ini.', 402, [
                        'token_dibutuhkan' => $tokenUsed,
                        'token_saldo' => (int) $u->token_balance,
                    ]);
                }

                $u->decrement('token_balance', $tokenUsed);
            }

            $attempt = PsikotesAttempt::create([
                'test_id' => $test->id,
                'user_id' => $u->id,
                'attempt_ke' => $ke,
                'token_used' => $tokenUsed,
                'status' => 'berjalan',
                'started_at' => now(),
                'expires_at' => now()->addMinutes((int) $test->durasi_menit),
            ]);

            if ($tokenUsed > 0) {
                TokenTransaction::create([
                    'user_id' => $u->id,
                    'tipe' => 'spend',
                    'jumlah' => -$tokenUsed,
                    'saldo_setelah' => $u->token_balance,
                    'attempt_id' => $attempt->id,
                    'keterangan' => 'Percobaan ke-' . $ke . ': ' . $test->judul,
                ]);
            }

            return $attempt;
        });
    }

    /**
     * Simpan / ubah jawaban selama waktu belum habis.
     */
    public function answer(PsikotesAttempt $attempt, int $questionId, int $optionId): PsikotesAnswer
    {
        if ($attempt->status !== 'berjalan' || now()->greaterThanOrEqualTo($attempt->expires_at)) {
            throw new ApiException('Waktu tes sudah habis atau sesi sudah selesai.', 422);
        }

        PsikotesQuestion::where('id', $questionId)->where('test_id', $attempt->test_id)->firstOrFail();
        PsikotesOption::where('id', $optionId)->where('question_id', $questionId)->firstOrFail();

        return PsikotesAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
            ['option_id' => $optionId]
        );
    }

    /**
     * Selesaikan sesi: hitung skor dari jawaban yang sudah tersimpan.
     * Dipakai oleh tombol submit dan oleh scheduler saat waktu habis (idempotent).
     */
    public function submit(PsikotesAttempt $attempt): PsikotesAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $a = PsikotesAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($a->status !== 'berjalan') {
                return $a;
            }

            $total = (int) PsikotesAnswer::where('psikotes_answers.attempt_id', $a->id)
                ->join('psikotes_options', 'psikotes_options.id', '=', 'psikotes_answers.option_id')
                ->sum('psikotes_options.skor');

            $range = PsikotesResultRange::where('test_id', $a->test_id)
                ->where('min_skor', '<=', $total)
                ->where('max_skor', '>=', $total)
                ->first();

            $timedOut = $a->expires_at !== null && now()->greaterThanOrEqualTo($a->expires_at);

            $a->update([
                'status' => $timedOut ? 'kedaluwarsa' : 'selesai',
                'finished_at' => now(),
                'total_skor' => $total,
                'hasil' => $range?->kategori,
            ]);

            return $a;
        });
    }
}
