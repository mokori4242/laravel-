<?php

namespace App\UseCases\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUseCase
{
    public function __invoke(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::forgetGuards();
    }
}
