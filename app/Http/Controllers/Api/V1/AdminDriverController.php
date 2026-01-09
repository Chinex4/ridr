<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\KycStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminDriverReviewRequest;
use App\Models\Driver;
use App\Services\KycService;
use Illuminate\Http\Request;

class AdminDriverController extends Controller
{
    /**
     * @group Admin
     */
    public function index(Request $request)
    {
        $status = $request->query('kyc_status');

        $query = Driver::query()->with('user');

        if ($status && in_array($status, array_column(KycStatus::cases(), 'value'), true)) {
            $query->where('kyc_status', $status);
        }

        $drivers = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($drivers);
    }

    /**
     * @group Admin
     */
    public function approve(AdminDriverReviewRequest $request, Driver $driver, KycService $kycService)
    {
        $driver = $kycService->review($driver, true, $request->validated('reason'));

        return response()->json(['driver' => $driver]);
    }

    /**
     * @group Admin
     */
    public function reject(AdminDriverReviewRequest $request, Driver $driver, KycService $kycService)
    {
        $driver = $kycService->review($driver, false, $request->validated('reason'));

        return response()->json(['driver' => $driver]);
    }
}
