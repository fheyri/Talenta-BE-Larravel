<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    /** Endpoint publik untuk notifikasi Midtrans (diverifikasi lewat signature_key). */
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $payments->handleNotification($request->all());

        return response()->json(['ok' => true]);
    }
}
