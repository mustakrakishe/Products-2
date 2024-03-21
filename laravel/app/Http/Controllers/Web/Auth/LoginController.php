<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $hasVerifiedEmailCallback = function (User $user): bool {
            return $user->hasVerifiedEmail();
        };

        if (Auth::attemptWhen($request->validated(), $hasVerifiedEmailCallback)) {
            $request->session()->regenerate();
 
            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email'    => 'The provided credentials do not match our records.',
            'password' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
