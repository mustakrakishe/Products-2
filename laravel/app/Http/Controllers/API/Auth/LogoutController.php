<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
