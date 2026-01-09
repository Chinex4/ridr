<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required','email'],
            'otp' => ['required','digits:5'],
            'password' => ['required','string','min:8','confirmed'],
        ];
    }
}
