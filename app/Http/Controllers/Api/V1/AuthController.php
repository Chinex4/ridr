<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @group Auth
     * @unauthenticated
     */
    public function register(RegisterRequest $request, OtpService $otpService)
    {
        $data = $request->validated();

        $existing = User::where('email', $data['email'])->first();
        if ($existing && $existing->email_verified_at) {
            return response()->json(['message' => 'Email already in use.'], 409);
        }

        if ($existing && ! $existing->email_verified_at) {
            // Don’t create a second row; resend OTP instead
            $otpService->send($existing->email, 'email_verification');

            return response()->json([
                'message' => 'Account exists but not verified. OTP resent.',
            ], 200);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        $otpService->send($user->email, 'email_verification');

        return response()->json([
            'message' => 'Registered successfully. OTP sent to email.',
        ], 201);
    }

    /**
     * @group Auth
     * @unauthenticated
     */
    public function login(LoginRequest $request, OtpService $otpService)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Credentials are valid, but email not verified -> send OTP automatically
        if (! $user->email_verified_at) {
            $otpService->send($user->email, 'email_verification');

            return response()->json([
                'message' => 'Email not verified. We sent a new OTP to your email.',
                'requires_verification' => true,
                'email' => $user->email,
            ], 202); // 202 Accepted is a nice fit here
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
        ]);
    }
}
