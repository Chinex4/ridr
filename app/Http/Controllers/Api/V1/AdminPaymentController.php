<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPaymentUpdateRequest;
use App\Models\Payment;

class AdminPaymentController extends Controller
{
    /**
     * @group Admin
     */
    public function index()
    {
        $payments = Payment::query()->with(['ride', 'rider'])->orderByDesc('created_at')->paginate(20);

        return response()->json($payments);
    }

    /**
     * @group Admin
     */
    public function show(Payment $payment)
    {
        return response()->json(['payment' => $payment->load(['ride', 'rider'])]);
    }

    /**
     * @group Admin
     */
    public function update(AdminPaymentUpdateRequest $request, Payment $payment)
    {
        $payment->update($request->validated());

        return response()->json(['payment' => $payment->fresh()]);
    }

    /**
     * @group Admin
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted.']);
    }
}
