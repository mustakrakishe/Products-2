<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\NewAccessTokenResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        VerifyEmail::createUrlUsing(function (User $user) {
            $name = 'api.verification.verify';
            $expiration = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
            $parameters = [
                'id'   => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ];

            $redirect = URL::temporarySignedRoute($name, $expiration, $parameters);

            $clientUrl = config(
                sprintf('clients.%s.urls.email_verify', request()->host())
            );

            return $clientUrl . '?' . http_build_query(
                compact('redirect')
            );
        });

        $user = User::create($request->validated());

        event(new Registered($user));

        return response()->json([
            'message' => sprintf('Follow the link at %s to confirm your email.', $user->email),
        ]);
    }

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::find($request->id);
        $user->markEmailAsVerified();

        return (new NewAccessTokenResource(
            $user->createToken('On register')
        ))->response();
    }
}
