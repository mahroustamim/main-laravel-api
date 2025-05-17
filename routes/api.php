<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocalizatoinController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;






Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('posts', PostController::class);
});

// Password reset routes
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-password-reset-otp', [AuthController::class, 'verifyPasswordResetOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');





Route::get('/test', function () {
    return app()->getLocale('');
});

