<?php

namespace App\Http\Requests;

use App\Enums\DriverDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'documents.*.type' => ['required', 'string', Rule::in(DriverDocumentType::requiredTypes())],
            'documents.*.file_path' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $documents = $this->input('documents', []);
            $types = array_values(array_unique(array_column($documents, 'type')));
            $missing = array_diff(DriverDocumentType::requiredTypes(), $types);

            if ($missing) {
                $validator->errors()->add('documents', 'Missing required document types: ' . implode(', ', $missing) . '.');
            }
        });
    }
}
