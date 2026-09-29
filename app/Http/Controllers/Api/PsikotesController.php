<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PsikotesAnswer;
use App\Models\PsikotesAttempt;
use App\Models\PsikotesQuestion;
use App\Models\PsikotesTest;
use App\Services\PsikotesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PsikotesController extends Controller
{
    public function __construct(private PsikotesService $service)
    {
    }

    /** Daftar tes + sisa percobaan gratis + biaya token. */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $tests = PsikotesTest::where('is_active', true)
            ->withCount(['attempts as percobaan_user' => fn ($q) => $q->where('user_id', $userId)])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'judul' => $t->judul,
                'deskripsi' => $t->deskripsi,
                'durasi_menit' => $t->durasi_menit,
                'gratis_percobaan' => $t->gratis_percobaan,
                'sisa_gratis' => max(0, $t->gratis_percobaan - $t->percobaan_user),
                'token_cost' => $t->token_cost,
            ]);

        return response()->json(['data' => $tests]);
    }

    public function start(Request $request, PsikotesTest $test): JsonResponse
    {
        $attempt = $this->service->start($request->user(), $test);

        return response()->json($this->attemptPayload($attempt, true));
    }

    public function show(Request $request, PsikotesAttempt $attempt): JsonResponse
    {
        $this->ensureOwner($request, $attempt);

        return response()->json($this->attemptPayload($attempt, $attempt->status === 'berjalan'));
    }

    public function answer(Request $request, PsikotesAttempt $attempt): JsonResponse
    {
        $this->ensureOwner($request, $attempt);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['required', 'integer'],
        ]);

        $this->service->answer($attempt, (int) $data['question_id'], (int) $data['option_id']);

        return response()->json(['ok' => true]);
    }

    public function submit(Request $request, PsikotesAttempt $attempt): JsonResponse
    {
        $this->ensureOwner($request, $attempt);

        $attempt = $this->service->submit($attempt);

        return response()->json($this->attemptPayload($attempt, false));
    }

    public function history(Request $request): JsonResponse
    {
        $rows = PsikotesAttempt::where('user_id', $request->user()->id)
            ->with('test:id,judul')
            ->orderByDesc('started_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'tes' => $a->test?->judul,
                'attempt_ke' => $a->attempt_ke,
                'status' => $a->status,
                'total_skor' => $a->total_skor,
                'hasil' => $a->hasil,
                'started_at' => $a->started_at,
                'finished_at' => $a->finished_at,
            ]);

        return response()->json(['data' => $rows]);
    }

    private function ensureOwner(Request $request, PsikotesAttempt $attempt): void
    {
        abort_unless($attempt->user_id === $request->user()->id, 403, 'Ini bukan sesi tesmu.');
    }

    private function attemptPayload(PsikotesAttempt $attempt, bool $withQuestions): array
    {
        $running = $attempt->status === 'berjalan';

        $data = [
            'id' => $attempt->id,
            'test_id' => $attempt->test_id,
            'attempt_ke' => $attempt->attempt_ke,
            'token_used' => $attempt->token_used,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at,
            'expires_at' => $attempt->expires_at,
            'sisa_detik' => $running ? max(0, $attempt->expires_at->getTimestamp() - now()->getTimestamp()) : 0,
            'total_skor' => $attempt->total_skor,
            'hasil' => $attempt->hasil,
        ];

        if ($withQuestions) {
            // skor opsi TIDAK dikirim ke client
            $data['questions'] = PsikotesQuestion::where('test_id', $attempt->test_id)
                ->orderBy('urutan')
                ->with('options:id,question_id,teks')
                ->get(['id', 'test_id', 'pertanyaan', 'urutan'])
                ->map(fn ($q) => [
                    'id' => $q->id,
                    'pertanyaan' => $q->pertanyaan,
                    'urutan' => $q->urutan,
                    'options' => $q->options->map(fn ($o) => ['id' => $o->id, 'teks' => $o->teks]),
                ]);

            $data['jawaban_tersimpan'] = PsikotesAnswer::where('attempt_id', $attempt->id)
                ->pluck('option_id', 'question_id');
        }

        return $data;
    }
}
