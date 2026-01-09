<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_verification_updates_status(): void
    {
        $rider = User::factory()->create();
        $ride = Ride::factory()->create(['rider_id' => $rider->id]);

        $payment = Payment::create([
            'ride_id' => $ride->id,
            'rider_id' => $rider->id,
            'provider' => 'paystack',
            'reference' => 'ref_test_123',
            'status' => PaymentStatus::Pending,
            'amount' => 1000,
            'currency' => 'NGN',
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/ref_test_123' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'paid_at' => now()->toISOString(),
                    'authorization' => [
                        'authorization_code' => 'AUTH_123',
                    ],
                ],
            ], 200),
        ]);

        $service = app(PaystackService::class);
        $service->verifyTransaction('ref_test_123');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Success->value,
            'authorization_code' => 'AUTH_123',
        ]);
    }
}
