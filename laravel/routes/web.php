<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\LogoutController;
use App\Http\Controllers\Web\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('auth')->group(function () {
    Route::prefix('register')->controller(RegisterController::class)->group(function () {
        Route::get('', 'show')->name('register');
        Route::post('', 'register');
        Route::get('verify', 'getVerificationNoticePage')->name('verification.notice');
        Route::get('verify/{id}/{hash}', 'verify')->middleware('signed')->name('verification.verify');
    });

    Route::get('logout', [LogoutController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::prefix('login')->controller(LoginController::class)->group(function () {
        Route::get('', 'show')->name('login');
        Route::post('', 'login');
    });
});
