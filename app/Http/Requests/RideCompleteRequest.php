<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RideCompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'final_distance_km' => ['nullable', 'numeric', 'min:0'],
            'final_duration_min' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
