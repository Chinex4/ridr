<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Enums\RideStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DriverApplyRequest;
use App\Http\Requests\DriverKycSubmitRequest;
use App\Http\Requests\DriverLocationRequest;
use App\Services\DriverService;
use App\Services\KycService;
use App\Services\RideService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * @group Driver
     */
    public function apply(DriverApplyRequest $request)
    {
        $user = $request->user();

        if (! in_array($user->role->value, [Role::Driver->value, Role::Rider->value], true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($user->role !== Role::Driver) {
            $user->update(['role' => Role::Driver]);
        }

        $driver = $user->driver()->firstOrCreate([]);
        $driver->update($request->validated());

        return response()->json([
            'driver' => $driver->fresh(),
        ], 201);
    }

    /**
     * @group Driver
     */
    public function profile(Request $request)
    {
        $driver = $request->user()->driver;

        if (! $driver) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        return response()->json(['driver' => $driver->load('documents')]);
    }

    /**
     * @group Driver
     */
    public function submitKyc(DriverKycSubmitRequest $request, KycService $kycService)
    {
        $driver = $kycService->submit($request->user(), $request->validated());

        return response()->json(['driver' => $driver], 202);
    }

    /**
     * @group Driver
     */
    public function updateLocation(DriverLocationRequest $request, DriverService $driverService, RideService $rideService)
    {
        $driver = $driverService->updateLocation(
            $request->user(),
            (float) $request->validated('lat'),
            (float) $request->validated('lng')
        );

        $ride = $request->user()->ridesAsDriver()
            ->whereIn('status', [
                RideStatus::Accepted,
                RideStatus::Arrived,
                RideStatus::InProgress,
            ])
            ->latest()
            ->first();

        $rideService->recordDriverLocation($driver, $ride);

        return response()->json(['driver' => $driver]);
    }

    /**
     * @group Driver
     */
    public function online(Request $request, DriverService $driverService)
    {
        $driver = $driverService->setOnline($request->user());

        return response()->json(['driver' => $driver]);
    }

    /**
     * @group Driver
     */
    public function offline(Request $request, DriverService $driverService)
    {
        $driver = $driverService->setOffline($request->user());

        return response()->json(['driver' => $driver]);
    }
}
