<?php

// app/Services/OtpService.php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function send(string $email, string $purpose): void
    {
        $otp = (string) random_int(10000, 99999);

        $recent = OtpCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('created_at', '>', now()->subSeconds(60))
            ->exists();

        if ($recent) {
            return;
        }

        OtpCode::create([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes((int) config('auth_otp.expires_minutes')),
        ]);

        Mail::to($email)->send(new OtpMail($otp, $purpose));
    }

    public function verify(string $email, string $purpose, string $otp): bool
    {
        $record = OtpCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            return false;
        }

        $record->increment('attempts');

        if ($record->attempts > (int) config('auth_otp.max_attempts')) {
            return false;
        }

        if (! Hash::check($otp, $record->code_hash)) {
            return false;
        }

        $record->update(['used_at' => now()]);

        return true;
    }
}
