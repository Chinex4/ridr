<?php

// app/Http/Controllers/Api/V1/OtpController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;

class OtpController extends Controller
{
    /**
     * @group OTP
     * @unauthenticated
     */
    public function verify(VerifyOtpRequest $request, OtpService $otpService)
    {
        $data = $request->validated();
        $purpose = $data['purpose'] ?? 'email_verification';

        $ok = $otpService->verify($data['email'], $purpose, $data['otp']);
        if (! $ok) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        if ($purpose === 'email_verification') {
            $user = User::where('email', $data['email'])->first();
            if ($user && ! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        return response()->json(['message' => 'OTP verified.']);
    }

    /**
     * @group OTP
     * @unauthenticated
     */
    public function resend(ResendOtpRequest $request, OtpService $otpService)
    {
        $data = $request->validated();
        $purpose = $data['purpose'] ?? 'email_verification';

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($purpose === 'email_verification' && $user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 422);
        }

        $otpService->send($data['email'], $purpose);

        return response()->json(['message' => 'OTP resent.']);
    }
}
