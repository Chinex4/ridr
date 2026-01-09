<?php

namespace App\Http\Requests;

use App\Enums\DriverDocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminDriverDocumentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(array_column(DriverDocumentStatus::cases(), 'value'))],
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
