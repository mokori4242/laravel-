<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\UseCases\Auth\LogoutUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutUseCase $logout): JsonResponse
    {
        $logout($request);

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}
