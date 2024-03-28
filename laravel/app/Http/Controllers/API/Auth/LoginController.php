<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\NewAccessTokenResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where($request->only('email'))
            ->whereNotNull('email_verified_at')
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'errors' => [
                    'email'    => 'The provided credentials do not match our records.',
                    'password' => 'The provided credentials do not match our records.',
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return (new NewAccessTokenResource(
            $user->createToken('On login')
        ))->response();
    }
}
