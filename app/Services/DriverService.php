<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\User;

class DriverService
{
    public function setOnline(User $user): Driver
    {
        $driver = $user->driver;
        $driver->update(['is_online' => true]);

        return $driver;
    }

    public function setOffline(User $user): Driver
    {
        $driver = $user->driver;
        $driver->update(['is_online' => false]);

        return $driver;
    }

    public function updateLocation(User $user, float $lat, float $lng): Driver
    {
        $driver = $user->driver;
        $driver->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'last_location_at' => now(),
        ]);

        return $driver;
    }
}
