<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)
    ->middleware(['guest', 'throttle:login']);

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('/me', MeController::class);
    Route::post('/logout', LogoutController::class);
});
