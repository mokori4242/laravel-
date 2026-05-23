<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\UseCases\Auth\LoginUseCase;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginUseCase $login): UserResource
    {
        return new UserResource($login($request->validated()));
    }
}
