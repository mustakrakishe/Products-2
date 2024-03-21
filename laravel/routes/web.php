<?php

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
});
