<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\UseCases\Me\ShowUseCase;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request, ShowUseCase $show): UserResource
    {
        return new UserResource($show($request->user()));
    }
}
