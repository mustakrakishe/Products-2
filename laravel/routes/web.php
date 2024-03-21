<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\LogoutController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('auth')->group(function () {
    Route::prefix('register')->controller(RegisterController::class)->middleware('guest')->group(function () {
        Route::get('', 'show')->name('register');
        Route::post('', 'register');

        Route::get('verify', 'getVerificationNoticePage')->name('verification.notice');
        Route::get('verify/{id}/{hash}', 'verify')->middleware('signed')->name('verification.verify');
    });

    Route::get('logout', [LogoutController::class, 'logout'])->middleware('auth')->name('logout');

    Route::prefix('login')->middleware('guest')->group(function () {
        Route::controller(LoginController::class)->group(function () {
            Route::get('', 'show')->name('login');
            Route::post('', 'login');
        });

        Route::prefix('password/reset')->controller(ResetPasswordController::class)->group(function () {
            Route::get('send', 'getLinkRequestForm')->name('password.reset.send');
            Route::post('send', 'sendLink');
            Route::get('{token}', 'getResetForm')->name('password.reset');
            Route::post('', 'reset')->name('password.update');
        });
    });
});

Route::resource('products', ProductController::class)->middleware('auth');
