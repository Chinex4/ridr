<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required','email'],
            'purpose' => ['sometimes','in:email_verification,password_reset'],
        ];
    }
}
