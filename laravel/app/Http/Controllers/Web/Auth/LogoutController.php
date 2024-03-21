<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function logout(): RedirectResponse
    {
        Auth::logout();
     
        return redirect()->route('home');
    }
}
