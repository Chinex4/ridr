<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverKycSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'date'],
            'vehicle_make' => ['nullable', 'string', 'max:100'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*.type' => ['required', 'string', 'max:50'],
            'documents.*.file_path' => ['required', 'string', 'max:255'],
        ];
    }
}
