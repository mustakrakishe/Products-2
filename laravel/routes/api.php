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
        Route::post('register', [RegisterController::class, 'register'])
            ->middleware(TrustedHost::class)
            ->name('api.register');

        Route::get('email/verify/{id}/{hash}', [RegisterController::class, 'verify'])
            ->middleware('signed')
            ->name('api.verification.verify');

        Route::post('login', [LoginController::class, 'login'])->name('api.login');
        Route::prefix('password/reset')->group(function () {
            Route::post('send', [ResetPasswordController::class, 'sendLink'])->middleware(TrustedHost::class)->name('api.password.reset.send');
            Route::post('', [ResetPasswordController::class, 'reset'])->name('api.password.reset');
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [LogoutController::class, 'logout'])
            ->name('api.logout');
    });

    Route::apiResource('products', ProductController::class, [
        'names' => [
            'index'   => 'api.products.index',
            'store'   => 'api.products.store',
            'show'    => 'api.products.show',
            'update'  => 'api.products.update',
            'destroy' => 'api.products.destroy',
        ],
    ]);
});
