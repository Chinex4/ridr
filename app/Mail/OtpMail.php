<?php

// app/Mail/OtpMail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose
    ) {}

    public function build()
    {
        $title = $this->purpose === 'password_reset'
            ? 'Your password reset code'
            : 'Your verification code';

        return $this->subject($title)
            ->view('emails.otp');
    }
}

