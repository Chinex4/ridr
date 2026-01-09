<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideStatusUpdated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, SerializesModels;

    public function __construct(public Ride $ride)
    {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('private-ride.' . $this->ride->id);
    }

    public function broadcastAs(): string
    {
        return 'ride.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'status' => $this->ride->status->value,
            'rider_id' => $this->ride->rider_id,
            'driver_id' => $this->ride->driver_id,
            'timestamps' => [
                'requested_at' => optional($this->ride->requested_at)->toISOString(),
                'accepted_at' => optional($this->ride->accepted_at)->toISOString(),
                'arrived_at' => optional($this->ride->arrived_at)->toISOString(),
                'started_at' => optional($this->ride->started_at)->toISOString(),
                'completed_at' => optional($this->ride->completed_at)->toISOString(),
                'cancelled_at' => optional($this->ride->cancelled_at)->toISOString(),
            ],
        ];
    }
}
