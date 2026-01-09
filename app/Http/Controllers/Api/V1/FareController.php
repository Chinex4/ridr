<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FareQuoteRequest;
use App\Services\FareService;

class FareController extends Controller
{
    /**
     * @group Fare
     */
    public function quote(FareQuoteRequest $request, FareService $fareService)
    {
        $amount = $fareService->estimate(
            $request->validated('estimated_distance_km'),
            $request->validated('estimated_duration_min')
        );

        return response()->json([
            'amount' => $amount,
            'currency' => config('fare.currency'),
        ]);
    }
}
