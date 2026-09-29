<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Payment;
use App\Models\TokenPackage;
use App\Models\TokenTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Buat transaksi pembelian token dan minta snap_token ke Midtrans.
     */
    public function createPurchase(User $user, TokenPackage $package): Payment
    {
        if (! $package->is_active) {
            throw new ApiException('Paket token tidak tersedia.', 404);
        }

        $amount = (int) round((float) $package->harga);

        $payment = Payment::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'jumlah_token' => $package->jumlah_token,
            'order_id' => 'TLN-' . $user->id . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'amount' => $package->harga,
            'status' => 'pending',
        ]);

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $payment->order_id,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [[
                'id' => 'PKG-' . $package->id,
                'price' => $amount,
                'quantity' => 1,
                'name' => Str::limit($package->nama, 45, ''),
            ]],
        ]);

        $payment->update(['snap_token' => $snapToken]);

        return $payment;
    }

    /**
     * Dipanggil oleh webhook Midtrans. Status "paid" HANYA boleh berubah lewat sini.
     * Aman dipanggil berulang (idempotent): token hanya dikreditkan sekali per pembayaran.
     */
    public function handleNotification(array $payload): void
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $gross = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        $expected = hash('sha512', $orderId . $statusCode . $gross . config('services.midtrans.server_key'));

        if (! hash_equals($expected, $signature)) {
            throw new ApiException('Signature tidak valid.', 403);
        }

        DB::transaction(function () use ($orderId, $payload, $gross) {
            $payment = Payment::where('order_id', $orderId)->lockForUpdate()->first();

            if (! $payment || $payment->status === 'paid') {
                return;
            }

            if ((int) round((float) $gross) !== (int) round((float) $payment->amount)) {
                throw new ApiException('Nominal pembayaran tidak sesuai.', 422);
            }

            $trx = (string) ($payload['transaction_status'] ?? '');
            $fraud = (string) ($payload['fraud_status'] ?? 'accept');

            $status = match (true) {
                $trx === 'settlement', ($trx === 'capture' && $fraud === 'accept') => 'paid',
                in_array($trx, ['deny', 'failure'], true) => 'failed',
                $trx === 'cancel' => 'cancelled',
                $trx === 'expire' => 'expired',
                default => 'pending',
            };

            $payment->update([
                'status' => $status,
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                'gateway_transaction_id' => $payload['transaction_id'] ?? $payment->gateway_transaction_id,
                'paid_at' => $status === 'paid' ? now() : null,
                'raw_response' => $payload,
            ]);

            if ($status === 'paid') {
                $user = User::withTrashed()->whereKey($payment->user_id)->lockForUpdate()->firstOrFail();
                $user->increment('token_balance', (int) $payment->jumlah_token);

                TokenTransaction::create([
                    'user_id' => $user->id,
                    'tipe' => 'purchase',
                    'jumlah' => (int) $payment->jumlah_token,
                    'saldo_setelah' => $user->token_balance,
                    'payment_id' => $payment->id,
                    'keterangan' => 'Pembelian ' . $payment->jumlah_token . ' token',
                ]);
            }
        });
    }
}
