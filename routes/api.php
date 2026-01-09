<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DriverController;
use App\Http\Controllers\Api\V1\DriverRideController;
use App\Http\Controllers\Api\V1\FareController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RideController;
use App\Http\Controllers\Api\V1\AdminDriverController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Http\Controllers\Api\V1\PasswordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api']]);

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');

    Route::post('/forgot-password', [PasswordController::class, 'forgot'])->middleware('throttle:10,1');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('throttle:10,1');

    Route::post('/verify-otp', [OtpController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('/resend-otp', [OtpController::class, 'resend'])->middleware('throttle:5,1');

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\SessionController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\V1\SessionController::class, 'me']);
        Route::post('/logout-all', [\App\Http\Controllers\Api\V1\SessionController::class, 'logoutAll']);

        Route::post('/fare/quote', [FareController::class, 'quote'])->middleware(['role:rider', 'throttle:20,1']);

        Route::post('/driver/apply', [DriverController::class, 'apply'])->middleware('throttle:10,1');
        Route::get('/driver/profile', [DriverController::class, 'profile'])->middleware('role:driver');
        Route::post('/driver/kyc/submit', [DriverController::class, 'submitKyc'])->middleware('role:driver');
        Route::post('/driver/location', [DriverController::class, 'updateLocation'])->middleware(['role:driver', 'kyc.approved']);
        Route::post('/driver/online', [DriverController::class, 'online'])->middleware(['role:driver', 'kyc.approved']);
        Route::post('/driver/offline', [DriverController::class, 'offline'])->middleware('role:driver');

        Route::get('/driver/rides/available', [DriverRideController::class, 'available'])->middleware(['role:driver', 'kyc.approved']);
        Route::post('/driver/rides/{ride}/accept', [DriverRideController::class, 'accept'])->middleware(['role:driver', 'kyc.approved']);
        Route::post('/driver/rides/{ride}/arrive', [DriverRideController::class, 'arrive'])->middleware(['role:driver', 'kyc.approved']);
        Route::post('/driver/rides/{ride}/start', [DriverRideController::class, 'start'])->middleware(['role:driver', 'kyc.approved']);
        Route::post('/driver/rides/{ride}/complete', [DriverRideController::class, 'complete'])->middleware(['role:driver', 'kyc.approved']);

        Route::post('/rides', [RideController::class, 'store'])->middleware(['role:rider', 'throttle:10,1']);
        Route::get('/rides', [RideController::class, 'index']);
        Route::get('/rides/{ride}', [RideController::class, 'show']);
        Route::post('/rides/{ride}/cancel', [RideController::class, 'cancel']);

        Route::post('/rides/{ride}/pay/init', [PaymentController::class, 'init'])->middleware(['role:rider', 'throttle:10,1']);
        Route::get('/payments/{reference}', [PaymentController::class, 'show']);

        Route::get('/admin/drivers', [AdminDriverController::class, 'index'])->middleware('role:admin');
        Route::post('/admin/drivers/{driver}/approve', [AdminDriverController::class, 'approve'])->middleware('role:admin');
        Route::post('/admin/drivers/{driver}/reject', [AdminDriverController::class, 'reject'])->middleware('role:admin');
    });

    Route::post('/webhooks/paystack', [WebhookController::class, 'paystack']);
});
