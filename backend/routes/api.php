<?php

use App\Http\Controllers\Auth\GoogleCallbackController;
use App\Http\Controllers\Auth\GoogleRedirectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Me\MeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class)
    ->middleware(['guest', 'throttle:register']);

Route::post('/login', LoginController::class)
    ->middleware(['guest', 'throttle:login']);

Route::middleware(['web', 'guest', 'throttle:google-auth'])->group(function (): void {
    Route::get('/auth/google/redirect', GoogleRedirectController::class);
    Route::get('/auth/google/callback', GoogleCallbackController::class);
});

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/me', MeController::class);
    Route::post('/logout', LogoutController::class);
});
