<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\LogoutController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('register', [RegisterController::class, 'show'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);

        Route::get('email/verify', [RegisterController::class, 'getVerificationNoticePage'])->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [RegisterController::class, 'verify'])->middleware('signed')->name('verification.verify');
    
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'login']);

        Route::get('password/reset/send', [ResetPasswordController::class, 'getLinkRequestForm'])->name('password.reset.send');
        Route::post('password/reset/send', [ResetPasswordController::class, 'sendLink']);
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'getResetForm'])->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('logout', [LogoutController::class, 'logout'])->name('logout');
    });

    Route::resource('products', ProductController::class);
});
