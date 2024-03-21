<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        event(new Registered($user));

        return redirect()
            ->route('verification.notice')
            ->with(['email' => $user->email]);
    }

    public function getVerificationNoticePage(): View
    {
        return view('auth.verify-email', [
            'email' => session('email'),
        ]);
    }

    public function verify(VerifyEmailRequest $request): RedirectResponse
    {
        Auth::loginUsingId($request->id);

        $request->fulfill();
     
        return redirect()->route('home');
    }
}
