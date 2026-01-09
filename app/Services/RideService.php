<?php

namespace App\Services;

use App\Enums\CancelBy;
use App\Enums\RideStatus;
use App\Events\DriverLocationUpdated;
use App\Events\RideStatusUpdated;
use App\Enums\PaymentStatus;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\RideEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RideService
{
    public function __construct(
        private FareService $fareService,
        private PaystackService $paystackService
    ) {
    }

    public function createRideRequest(User $rider, array $data): Ride
    {
        $estimate = $this->fareService->estimate(
            $data['estimated_distance_km'] ?? null,
            $data['estimated_duration_min'] ?? null
        );

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'status' => RideStatus::Requested,
            'pickup_lat' => $data['pickup_lat'],
            'pickup_lng' => $data['pickup_lng'],
            'pickup_address' => $data['pickup_address'],
            'dropoff_lat' => $data['dropoff_lat'],
            'dropoff_lng' => $data['dropoff_lng'],
            'dropoff_address' => $data['dropoff_address'],
            'estimated_distance_km' => $data['estimated_distance_km'] ?? null,
            'estimated_duration_min' => $data['estimated_duration_min'] ?? null,
            'estimated_fare_amount' => $estimate,
            'currency' => config('fare.currency'),
            'requested_at' => now(),
        ]);

        $this->logEvent($ride, 'requested', $rider, [
            'estimated_fare_amount' => $estimate,
        ]);

        RideStatusUpdated::dispatch($ride);

        return $ride;
    }

    public function listAvailableRidesForDriver(float $lat, float $lng, float $radiusKm = 5): array
    {
        $latDelta = $radiusKm / 111;
        $lngDelta = $radiusKm / (111 * max(cos(deg2rad($lat)), 0.01));

        $rides = Ride::where('status', RideStatus::Requested)
            ->whereBetween('pickup_lat', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('pickup_lng', [$lng - $lngDelta, $lng + $lngDelta])
            ->orderBy('requested_at')
            ->limit(50)
            ->get();

        return $rides->all();
    }

    public function acceptRide(User $driver, Ride $ride): Ride
    {
        return DB::transaction(function () use ($driver, $ride) {
            $locked = Ride::whereKey($ride->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== RideStatus::Requested || $locked->driver_id) {
                abort(409, 'Ride cannot be accepted in current status.');
            }

            $locked->update([
                'driver_id' => $driver->id,
                'status' => RideStatus::Accepted,
                'accepted_at' => now(),
                'driver_last_lat' => $driver->driver?->current_lat,
                'driver_last_lng' => $driver->driver?->current_lng,
            ]);

            $this->logEvent($locked, 'accepted', $driver);
            RideStatusUpdated::dispatch($locked->fresh());

            return $locked->fresh();
        });
    }

    public function markArrived(User $driver, Ride $ride): Ride
    {
        if ($ride->status !== RideStatus::Accepted) {
            abort(409, 'Ride cannot be marked arrived in current status.');
        }

        $ride->update([
            'status' => RideStatus::Arrived,
            'arrived_at' => now(),
        ]);

        $this->logEvent($ride, 'arrived', $driver);
        RideStatusUpdated::dispatch($ride->fresh());

        return $ride->fresh();
    }

    public function startRide(User $driver, Ride $ride): Ride
    {
        if ($ride->status !== RideStatus::Arrived) {
            abort(409, 'Ride cannot be started in current status.');
        }

        $ride->update([
            'status' => RideStatus::InProgress,
            'started_at' => now(),
        ]);

        $this->logEvent($ride, 'started', $driver);
        RideStatusUpdated::dispatch($ride->fresh());

        return $ride->fresh();
    }

    public function completeRide(User $driver, Ride $ride, array $data = []): Ride
    {
        if ($ride->status !== RideStatus::InProgress) {
            abort(409, 'Ride cannot be completed in current status.');
        }

        $fare = $this->fareService->estimate(
            $data['final_distance_km'] ?? $ride->estimated_distance_km,
            $data['final_duration_min'] ?? $ride->estimated_duration_min
        );

        $ride->update([
            'status' => RideStatus::Completed,
            'completed_at' => now(),
            'final_fare_amount' => $fare,
        ]);

        $this->logEvent($ride, 'completed', $driver, [
            'final_fare_amount' => $fare,
        ]);

        RideStatusUpdated::dispatch($ride->fresh());

        $payment = $ride->payments()->latest()->first();
        if ($payment && $payment->status === PaymentStatus::Pending) {
            $this->paystackService->verifyTransaction($payment->reference);
        }

        return $ride->fresh();
    }

    public function cancelRide(User $actor, Ride $ride, string $reason): Ride
    {
        if ($ride->status === RideStatus::Completed) {
            abort(409, 'Ride cannot be cancelled in current status.');
        }

        if ($actor->id === $ride->rider_id && $ride->status === RideStatus::InProgress) {
            abort(409, 'Rider cannot cancel after ride start.');
        }

        $cancelBy = $actor->id === $ride->driver_id
            ? CancelBy::Driver
            : ($actor->id === $ride->rider_id ? CancelBy::Rider : CancelBy::System);

        $ride->update([
            'status' => RideStatus::Cancelled,
            'cancelled_at' => now(),
            'cancel_by' => $cancelBy,
            'cancel_reason' => $reason,
        ]);

        $this->logEvent($ride, 'cancelled', $actor, ['reason' => $reason]);
        RideStatusUpdated::dispatch($ride->fresh());

        return $ride->fresh();
    }

    public function recordDriverLocation(Driver $driver, ?Ride $ride = null): void
    {
        if ($ride) {
            $ride->update([
                'driver_last_lat' => $driver->current_lat,
                'driver_last_lng' => $driver->current_lng,
            ]);
        }

        DriverLocationUpdated::dispatch($ride, $driver);
    }

    private function logEvent(Ride $ride, string $type, User $actor, array $meta = []): void
    {
        RideEvent::create([
            'ride_id' => $ride->id,
            'type' => $type,
            'actor_user_id' => $actor->id,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
