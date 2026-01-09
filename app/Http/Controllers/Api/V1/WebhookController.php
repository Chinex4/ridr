<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * @group Webhooks
     * @unauthenticated
     */
    public function paystack(Request $request, PaystackService $paystackService)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $paystackService->validateWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $data = $request->json()->all();
        $paystackService->handleWebhook($data);

        return response()->json(['message' => 'ok'], 200);
    }
}
