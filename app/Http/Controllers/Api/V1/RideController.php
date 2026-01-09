<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\RideCancelRequest;
use App\Http\Requests\RideRequest;
use App\Models\Ride;
use App\Services\IdempotencyService;
use App\Services\RideService;
use Illuminate\Http\Request;

class RideController extends Controller
{
    /**
     * @group Rides
     */
    public function store(RideRequest $request, RideService $rideService, IdempotencyService $idempotencyService)
    {
        $user = $request->user();

        if ($user->role !== Role::Rider) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $key = $request->header('Idempotency-Key');
        if ($key) {
            $existing = $idempotencyService->findExisting($user, 'rides.store', $key);
            if ($existing) {
                if (! $idempotencyService->assertRequestHash($existing, $request->validated())) {
                    return response()->json(['message' => 'Idempotency key conflict.'], 409);
                }

                if ($existing->response_code && $existing->response_body) {
                    return response()->json($existing->response_body, $existing->response_code);
                }
            } else {
                $existing = $idempotencyService->create($user, 'rides.store', $key, $request->validated());
            }
        }

        $ride = $rideService->createRideRequest($user, $request->validated());

        $response = [
            'ride' => $ride,
        ];

        if (isset($existing)) {
            $idempotencyService->storeResponse($existing, 201, $response);
        }

        return response()->json($response, 201);
    }

    /**
     * @group Rides
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ride::query()->with(['rider', 'driver']);

        if ($user->role === Role::Rider) {
            $query->where('rider_id', $user->id);
        } elseif ($user->role === Role::Driver) {
            $query->where('driver_id', $user->id);
        }

        $rides = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($rides);
    }

    /**
     * @group Rides
     */
    public function show(Request $request, Ride $ride)
    {
        $this->authorize('view', $ride);

        return response()->json(['ride' => $ride->load(['rider', 'driver', 'events'])]);
    }

    /**
     * @group Rides
     */
    public function cancel(RideCancelRequest $request, Ride $ride, RideService $rideService)
    {
        $this->authorize('cancel', $ride);

        $ride = $rideService->cancelRide($request->user(), $ride, $request->validated('reason'));

        return response()->json(['ride' => $ride]);
    }
}
