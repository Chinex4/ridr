<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRideUpdateRequest;
use App\Models\Ride;

class AdminRideController extends Controller
{
    /**
     * @group Admin
     */
    public function index()
    {
        $rides = Ride::query()->with(['rider', 'driver'])->orderByDesc('created_at')->paginate(20);

        return response()->json($rides);
    }

    /**
     * @group Admin
     */
    public function show(Ride $ride)
    {
        return response()->json(['ride' => $ride->load(['rider', 'driver', 'events'])]);
    }

    /**
     * @group Admin
     */
    public function update(AdminRideUpdateRequest $request, Ride $ride)
    {
        $data = $request->validated();
        $ride->update($data);

        return response()->json(['ride' => $ride->fresh()]);
    }

    /**
     * @group Admin
     */
    public function destroy(Ride $ride)
    {
        $ride->delete();

        return response()->json(['message' => 'Ride deleted.']);
    }
}
