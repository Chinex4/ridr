<?php

use App\Enums\Role;
use App\Models\Ride;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-ride.{rideId}', function ($user, string $rideId) {
    $ride = Ride::find($rideId);

    if (! $ride) {
        return false;
    }

    if ($user->role === Role::Admin) {
        return true;
    }

    return $ride->rider_id === $user->id || $ride->driver_id === $user->id;
});

Broadcast::channel('private-user.{userId}', function ($user, string $userId) {
    if ($user->role === Role::Admin) {
        return true;
    }

    return $user->id === $userId;
});
