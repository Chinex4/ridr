<?php

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\RideStatus;
use App\Enums\Role;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RideFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_can_request_and_driver_can_complete_ride(): void
    {
        $rider = User::factory()->create(['role' => Role::Rider]);
        $driverUser = User::factory()->create(['role' => Role::Driver]);
        Driver::create([
            'user_id' => $driverUser->id,
            'kyc_status' => KycStatus::Approved,
        ]);

        $rideResponse = $this->actingAs($rider, 'api')
            ->postJson('/api/v1/rides', [
                'pickup_lat' => 6.5244,
                'pickup_lng' => 3.3792,
                'pickup_address' => 'Pickup Address',
                'dropoff_lat' => 6.4654,
                'dropoff_lng' => 3.4064,
                'dropoff_address' => 'Dropoff Address',
                'estimated_distance_km' => 5.2,
                'estimated_duration_min' => 12,
            ])
            ->assertStatus(201);

        $rideId = $rideResponse->json('ride.id');

        $this->actingAs($driverUser, 'api')
            ->postJson("/api/v1/driver/rides/{$rideId}/accept")
            ->assertStatus(200)
            ->assertJsonPath('ride.status', RideStatus::Accepted->value);

        $this->actingAs($driverUser, 'api')
            ->postJson("/api/v1/driver/rides/{$rideId}/arrive")
            ->assertStatus(200)
            ->assertJsonPath('ride.status', RideStatus::Arrived->value);

        $this->actingAs($driverUser, 'api')
            ->postJson("/api/v1/driver/rides/{$rideId}/start")
            ->assertStatus(200)
            ->assertJsonPath('ride.status', RideStatus::InProgress->value);

        $this->actingAs($driverUser, 'api')
            ->postJson("/api/v1/driver/rides/{$rideId}/complete", [
                'final_distance_km' => 6.0,
                'final_duration_min' => 14,
            ])
            ->assertStatus(200)
            ->assertJsonPath('ride.status', RideStatus::Completed->value);
    }
}
