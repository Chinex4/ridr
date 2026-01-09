<?php

namespace Database\Factories;

use App\Enums\RideStatus;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RideFactory extends Factory
{
    protected $model = Ride::class;

    public function definition(): array
    {
        return [
            'rider_id' => User::factory(),
            'status' => RideStatus::Requested,
            'pickup_lat' => 6.5244,
            'pickup_lng' => 3.3792,
            'pickup_address' => 'Pickup Address',
            'dropoff_lat' => 6.4654,
            'dropoff_lng' => 3.4064,
            'dropoff_address' => 'Dropoff Address',
            'estimated_distance_km' => 5.0,
            'estimated_duration_min' => 12,
            'estimated_fare_amount' => 1000,
            'currency' => 'NGN',
            'requested_at' => now(),
        ];
    }
}
