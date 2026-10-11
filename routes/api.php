<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - Smart School
|--------------------------------------------------------------------------
*/

// Public Authentication Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/teacher/login', [AuthController::class, 'loginTeacher']);
    Route::post('/parent/login', [AuthController::class, 'loginParent']);
    
    // Pemulihan Akun via Gmail OTP
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Endpoints (Memerlukan Bearer Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
