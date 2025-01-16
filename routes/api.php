<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register']);
Route::middleware(['auth:sanctum', 'verified'])->post('/logout', [AuthController::class, 'logout']);
Route::post('/email/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{uuid}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
