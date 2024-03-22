<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetPasswordLinkRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function sendLink(SendResetPasswordLinkRequest $request): JsonResponse
    {
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $url = config(
                sprintf('clients.%s.urls.password_reset', request()->host())
            );

            return $url . '?token=' . $token;
        });

        $email = $request->input('email');

        $status = Password::sendResetLink(compact('email'));

        return match($status) {
            Password::RESET_LINK_SENT => response()->json([
                'message' => __($status)
            ]),

            Password::INVALID_USER => response()->json([
                'errors' => ['email' => __($status)]
            ], Response::HTTP_UNPROCESSABLE_ENTITY),

            Password::RESET_THROTTLED => response()->json([
                'message' => __($status)
            ], Response::HTTP_TOO_MANY_REQUESTS),

            default => response()->json([
                'message' => __($status)
            ], Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->tokens()->delete();

                $user->save();
            }
        );

        return match($status) {
            Password::PASSWORD_RESET => response()->json([
                'message' => __($status)
            ]),

            Password::INVALID_TOKEN  => response()->json([
                'errors' => ['token' => __($status)]
            ], Response::HTTP_UNPROCESSABLE_ENTITY),

            Password::INVALID_USER  => response()->json([
                'errors' => ['email' => __($status)]
            ], Response::HTTP_UNPROCESSABLE_ENTITY),

            default => response()->json([
                'message' => __($status)
            ], Response::HTTP_INTERNAL_SERVER_ERROR),
        };
    }
}
