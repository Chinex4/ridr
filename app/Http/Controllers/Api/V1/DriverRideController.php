<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRideAvailableRequest;
use App\Http\Requests\RideCompleteRequest;
use App\Models\Ride;
use App\Services\RideService;
use Illuminate\Http\Request;

class DriverRideController extends Controller
{
    /**
     * @group Driver Rides
     */
    public function available(DriverRideAvailableRequest $request, RideService $rideService)
    {
        $rides = $rideService->listAvailableRidesForDriver(
            (float) $request->validated('lat'),
            (float) $request->validated('lng'),
            (float) ($request->validated('radius_km') ?? 5)
        );

        return response()->json(['rides' => $rides]);
    }

    /**
     * @group Driver Rides
     */
    public function accept(Request $request, Ride $ride, RideService $rideService)
    {
        $this->authorize('accept', $ride);

        $ride = $rideService->acceptRide($request->user(), $ride);

        return response()->json(['ride' => $ride]);
    }

    /**
     * @group Driver Rides
     */
    public function arrive(Request $request, Ride $ride, RideService $rideService)
    {
        $this->authorize('arrive', $ride);

        $ride = $rideService->markArrived($request->user(), $ride);

        return response()->json(['ride' => $ride]);
    }

    /**
     * @group Driver Rides
     */
    public function start(Request $request, Ride $ride, RideService $rideService)
    {
        $this->authorize('start', $ride);

        $ride = $rideService->startRide($request->user(), $ride);

        return response()->json(['ride' => $ride]);
    }

    /**
     * @group Driver Rides
     */
    public function complete(RideCompleteRequest $request, Ride $ride, RideService $rideService)
    {
        $this->authorize('complete', $ride);

        $ride = $rideService->completeRide($request->user(), $ride, $request->validated());

        return response()->json(['ride' => $ride]);
    }
}
