<?php

namespace App\Http\Requests;

use App\Enums\KycStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDriverUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kyc_status' => ['sometimes', Rule::in(array_column(KycStatus::cases(), 'value'))],
            'kyc_rejection_reason' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'date'],
            'vehicle_make' => ['nullable', 'string', 'max:100'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'is_online' => ['nullable', 'boolean'],
        ];
    }
}
