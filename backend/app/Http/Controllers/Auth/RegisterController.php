<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\UseCases\Auth\RegisterUseCase;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUseCase $register): UserResource
    {
        return new UserResource($register($request->validated()));
    }
}
