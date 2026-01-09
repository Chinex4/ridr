<?php

namespace App\Http\Middleware;

use App\Enums\KycStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireKycApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->driver || $user->driver->kyc_status !== KycStatus::Approved) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
