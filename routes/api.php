<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\PsikotesController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

// Semua URL berawalan /api/v1
Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);          // mobile: siswa & alumni
    Route::post('admin/auth/login', [AuthController::class, 'adminLogin']); // web: admin & super_admin

    // notifikasi Midtrans (publik, diverifikasi signature)
    Route::post('payments/midtrans/webhook', PaymentWebhookController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // siswa & alumni (konsep seperti LinkedIn: keduanya boleh lowongan & psikotes)
        Route::middleware('role:siswa,alumni')->group(function () {
            Route::put('profile/complete', [AuthController::class, 'completeProfile']);

            Route::middleware('profile.complete')->group(function () {
                // psikotes
                Route::get('psikotes', [PsikotesController::class, 'index']);
                Route::get('psikotes/history', [PsikotesController::class, 'history']);
                Route::post('psikotes/{test}/start', [PsikotesController::class, 'start']);
                Route::get('psikotes/attempts/{attempt}', [PsikotesController::class, 'show']);
                Route::post('psikotes/attempts/{attempt}/answer', [PsikotesController::class, 'answer']);
                Route::post('psikotes/attempts/{attempt}/submit', [PsikotesController::class, 'submit']);

                // token
                Route::get('tokens/packages', [TokenController::class, 'packages']);
                Route::get('tokens/balance', [TokenController::class, 'balance']);
                Route::post('tokens/purchase', [TokenController::class, 'purchase']);

                // TODO modul berikutnya: lowongan, mentoring, notifikasi
            });
        });

        // TODO: grup admin (role:admin,super_admin) dan super admin (role:super_admin)
    });
});
