<?php

namespace App\Policies;

use App\Enums\RideStatus;
use App\Enums\Role;
use App\Models\Ride;
use App\Models\User;

class RidePolicy
{
    public function view(User $user, Ride $ride): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        }

        return $ride->rider_id === $user->id || $ride->driver_id === $user->id;
    }

    public function accept(User $user, Ride $ride): bool
    {
        return $user->role === Role::Driver
            && $ride->status === RideStatus::Requested
            && $ride->driver_id === null;
    }

    public function arrive(User $user, Ride $ride): bool
    {
        return $user->role === Role::Driver
            && $ride->driver_id === $user->id
            && $ride->status === RideStatus::Accepted;
    }

    public function start(User $user, Ride $ride): bool
    {
        return $user->role === Role::Driver
            && $ride->driver_id === $user->id
            && $ride->status === RideStatus::Arrived;
    }

    public function complete(User $user, Ride $ride): bool
    {
        return $user->role === Role::Driver
            && $ride->driver_id === $user->id
            && $ride->status === RideStatus::InProgress;
    }

    public function cancel(User $user, Ride $ride): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        }

        if ($ride->status === RideStatus::Completed) {
            return false;
        }

        return $ride->rider_id === $user->id || $ride->driver_id === $user->id;
    }
}
