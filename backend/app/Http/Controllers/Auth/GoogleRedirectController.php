<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return Socialite::driver(SocialProvider::Google->value)->redirect();
    }
}
