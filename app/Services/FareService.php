<?php

namespace App\Services;

class FareService
{
    public function estimate(?float $distanceKm, ?int $durationMin): int
    {
        $base = (int) config('fare.base_fare_kobo');
        $perKm = (int) config('fare.per_km_kobo');
        $perMin = (int) config('fare.per_min_kobo');

        $distanceCharge = $distanceKm ? (int) round($distanceKm * $perKm) : 0;
        $timeCharge = $durationMin ? (int) round($durationMin * $perMin) : 0;

        return max($base + $distanceCharge + $timeCharge, $base);
    }
}
