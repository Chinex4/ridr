<?php

return [
    'expires_minutes' => env('OTP_EXPIRES_MINUTES', 10),
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),
];
