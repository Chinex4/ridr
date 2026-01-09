<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackService
{
    public function initializeTransaction(Ride $ride, User $rider): array
    {
        $reference = (string) Str::uuid();
        $amount = $ride->estimated_fare_amount;

        $payment = Payment::create([
            'ride_id' => $ride->id,
            'rider_id' => $rider->id,
            'provider' => 'paystack',
            'reference' => $reference,
            'status' => PaymentStatus::Initiated,
            'amount' => $amount,
            'currency' => $ride->currency,
        ]);

        $response = $this->client()
            ->post('/transaction/initialize', [
                'email' => $rider->email,
                'amount' => $amount,
                'reference' => $reference,
                'currency' => $ride->currency,
                'metadata' => [
                    'ride_id' => $ride->id,
                    'rider_id' => $rider->id,
                ],
            ])
            ->throw()
            ->json();

        $payment->update([
            'status' => PaymentStatus::Pending,
            'raw_payload' => $response,
        ]);

        return [
            'reference' => $reference,
            'authorization_url' => data_get($response, 'data.authorization_url'),
            'access_code' => data_get($response, 'data.access_code'),
        ];
    }

    public function verifyTransaction(string $reference): Payment
    {
        $payment = Payment::where('reference', $reference)->firstOrFail();

        $response = $this->client()
            ->get("/transaction/verify/{$reference}")
            ->throw()
            ->json();

        $status = data_get($response, 'data.status');
        $paidAt = data_get($response, 'data.paid_at');
        $authorization = data_get($response, 'data.authorization.authorization_code');

        if ($status === 'success') {
            $payment->update([
                'status' => PaymentStatus::Success,
                'paid_at' => $paidAt ? Carbon::parse($paidAt) : now(),
                'authorization_code' => $authorization,
                'raw_payload' => $response,
            ]);
        } elseif ($status === 'failed') {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'raw_payload' => $response,
            ]);
        } else {
            $payment->update([
                'status' => PaymentStatus::Pending,
                'raw_payload' => $response,
            ]);
        }

        return $payment;
    }

    public function handleWebhook(array $payload): ?Payment
    {
        $reference = data_get($payload, 'data.reference');

        if (! $reference) {
            return null;
        }

        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            return null;
        }

        $event = data_get($payload, 'event');

        if ($event === 'charge.success') {
            $payment->update([
                'status' => PaymentStatus::Success,
                'paid_at' => now(),
                'authorization_code' => data_get($payload, 'data.authorization.authorization_code'),
                'raw_payload' => $payload,
            ]);
        } elseif ($event === 'charge.failed') {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'raw_payload' => $payload,
            ]);
        }

        return $payment;
    }

    public function validateWebhookSignature(string $payload, ?string $signature): bool
    {
        $secret = (string) config('paystack.webhook_secret');

        if ($secret === '' || $signature === null) {
            return false;
        }

        $hash = hash_hmac('sha512', $payload, $secret);

        return hash_equals($hash, $signature);
    }

    private function client()
    {
        return Http::baseUrl(config('paystack.base_url'))
            ->withToken(config('paystack.secret_key'))
            ->timeout((int) config('paystack.timeout'))
            ->retry((int) config('paystack.retry.times'), (int) config('paystack.retry.sleep_ms'));
    }
}
