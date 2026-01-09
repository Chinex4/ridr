<?php

// app/Http/Controllers/Api/V1/SessionController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class SessionController extends Controller
{
    /**
     * @group Session
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * @group Session
     */
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if (! $token) {
                return response()->json(['message' => 'No token provided.'], 400);
            }

            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Logged out.'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not log out.'], 400);
        }
    }

    /**
     * @group Session
     */
    public function logoutAll(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if (! $token) {
                return response()->json(['message' => 'No token provided.'], 400);
            }

            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Logged out from all devices.'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not log out.'], 400);
        }
    }
}
