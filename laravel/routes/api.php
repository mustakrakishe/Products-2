<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LogoutController::class, 'logout']);
    });
});
