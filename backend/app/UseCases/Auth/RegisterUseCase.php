<?php

namespace App\UseCases\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUseCase
{
    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function __invoke(array $attributes): User
    {
        $user = User::create($attributes);

        Auth::login($user);

        session()->regenerate();

        return $user;
    }
}
