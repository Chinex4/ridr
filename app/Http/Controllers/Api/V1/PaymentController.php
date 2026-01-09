<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentInitRequest;
use App\Models\Payment;
use App\Models\Ride;
use App\Services\IdempotencyService;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * @group Payments
     */
    public function init(PaymentInitRequest $request, Ride $ride, PaystackService $paystackService, IdempotencyService $idempotencyService)
    {
        $user = $request->user();

        if ($user->role !== Role::Rider || $ride->rider_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $key = $request->header('Idempotency-Key');
        if ($key) {
            $existing = $idempotencyService->findExisting($user, 'payments.init', $key);
            if ($existing) {
                if (! $idempotencyService->assertRequestHash($existing, $request->validated())) {
                    return response()->json(['message' => 'Idempotency key conflict.'], 409);
                }

                if ($existing->response_code && $existing->response_body) {
                    return response()->json($existing->response_body, $existing->response_code);
                }
            } else {
                $existing = $idempotencyService->create($user, 'payments.init', $key, $request->validated());
            }
        }

        $payload = $paystackService->initializeTransaction($ride, $user);

        $response = [
            'payment' => [
                'reference' => $payload['reference'],
                'authorization_url' => $payload['authorization_url'],
                'access_code' => $payload['access_code'],
            ],
        ];

        if (isset($existing)) {
            $idempotencyService->storeResponse($existing, 201, $response);
        }

        return response()->json($response, 201);
    }

    /**
     * @group Payments
     */
    public function show(Request $request, string $reference)
    {
        $payment = Payment::where('reference', $reference)->firstOrFail();
        $user = $request->user();

        if ($user->role !== Role::Admin && $payment->rider_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(['payment' => $payment]);
    }
}
