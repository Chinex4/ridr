<?php

// app/Http/Controllers/Api/V1/PasswordController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * @group Password
     * @unauthenticated
     */
    public function forgot(ForgotPasswordRequest $request, OtpService $otpService)
    {
        $email = $request->validated()['email'];

        $user = User::where('email', $email)->first();
        if (! $user) {
            // Don’t leak whether the email exists
            return response()->json(['message' => 'If that email exists, an OTP has been sent.']);
        }

        $otpService->send($email, 'password_reset');

        return response()->json(['message' => 'If that email exists, an OTP has been sent.']);
    }

    /**
     * @group Password
     * @unauthenticated
     */
    public function reset(ResetPasswordRequest $request, OtpService $otpService)
    {
        $data = $request->validated();

        $ok = $otpService->verify($data['email'], 'password_reset', $data['otp']);
        if (! $ok) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return response()->json(['message' => 'Password reset successful.']);
    }
}
