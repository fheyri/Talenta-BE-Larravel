<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function packages(): JsonResponse
    {
        $rows = TokenPackage::where('is_active', true)
            ->orderBy('jumlah_token')
            ->get(['id', 'nama', 'jumlah_token', 'harga']);

        return response()->json(['data' => $rows]);
    }

    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        $riwayat = TokenTransaction::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'tipe', 'jumlah', 'saldo_setelah', 'keterangan', 'created_at']);

        return response()->json([
            'token_balance' => $user->token_balance,
            'riwayat' => $riwayat,
        ]);
    }

    /** Buat transaksi Midtrans; aplikasi membuka Snap memakai snap_token. */
    public function purchase(Request $request, PaymentService $payments): JsonResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'integer', 'exists:token_packages,id'],
        ]);

        $package = TokenPackage::findOrFail($data['package_id']);
        $payment = $payments->createPurchase($request->user(), $package);

        return response()->json([
            'order_id' => $payment->order_id,
            'amount' => $payment->amount,
            'jumlah_token' => $payment->jumlah_token,
            'snap_token' => $payment->snap_token,
        ], 201);
    }
}
