<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Events\RideStatusUpdated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastingEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ride_request_dispatches_broadcast_event(): void
    {
        Event::fake([RideStatusUpdated::class]);

        $rider = User::factory()->create(['role' => Role::Rider]);

        $this->actingAs($rider, 'api')
            ->postJson('/api/v1/rides', [
                'pickup_lat' => 6.5244,
                'pickup_lng' => 3.3792,
                'pickup_address' => 'Pickup Address',
                'dropoff_lat' => 6.4654,
                'dropoff_lng' => 3.4064,
                'dropoff_address' => 'Dropoff Address',
            ])
            ->assertStatus(201);

        Event::assertDispatched(RideStatusUpdated::class);
    }
}
