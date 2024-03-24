<?php

use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\ProductController;
use App\Http\Middleware\TrustedHost;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [LoginController::class, 'login']);
        Route::prefix('password/reset')->group(function () {
            Route::post('send', [ResetPasswordController::class, 'sendLink'])->middleware(TrustedHost::class);
            Route::post('', [ResetPasswordController::class, 'reset']);
        });

        Route::post('register', [RegisterController::class, 'register'])
            ->middleware(TrustedHost::class);

        Route::get('email/verify/{id}/{hash}', [RegisterController::class, 'verify'])
            ->middleware('signed')
            ->name('api.verification.verify');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LogoutController::class, 'logout']);
    });

    Route::resource('products', ProductController::class);
});
