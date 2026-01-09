<?php

namespace App\Http\Requests;

use App\Enums\RideStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRideUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(array_column(RideStatus::cases(), 'value'))],
            'driver_id' => ['nullable', 'uuid', 'exists:users,id'],
            'final_fare_amount' => ['nullable', 'integer', 'min:0'],
            'cancel_reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
