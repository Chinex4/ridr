<?php

namespace App\Events;

use App\Models\Driver;
use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, SerializesModels;

    public function __construct(public ?Ride $ride, public Driver $driver)
    {
    }

    public function broadcastOn(): Channel
    {
        if ($this->ride) {
            return new PrivateChannel('private-ride.' . $this->ride->id);
        }

        return new PrivateChannel('private-user.' . $this->driver->user_id);
    }

    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride?->id,
            'driver_id' => $this->driver->user_id,
            'lat' => $this->driver->current_lat,
            'lng' => $this->driver->current_lng,
            'updated_at' => optional($this->driver->last_location_at)->toISOString(),
        ];
    }
}
