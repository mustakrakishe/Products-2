<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetPasswordLinkRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function getLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(SendResetPasswordLinkRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->validated());

        return match($status) {
            Password::RESET_LINK_SENT => back()->with(['message' => __($status)]),
            Password::INVALID_USER    => back()->withErrors(['password' => __($status)]),
            Password::RESET_THROTTLED => back()->with(['message' => __($status)]),
            default                   => response(status: Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }

    public function getResetForm(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
            }
        );

        return match($status) {
            Password::PASSWORD_RESET => redirect()->route('login')->with(['message' =>  __($status)]),
            Password::INVALID_USER   => back()->withErrors(['password' => __($status)]),
            Password::INVALID_TOKEN  => redirect()->route('password.reset.send'),
            default                  => response(status: Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }
}
