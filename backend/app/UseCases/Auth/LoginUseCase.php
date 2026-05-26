<?php

namespace App\UseCases\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginUseCase
{
    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws AuthenticationException
     */
    public function __invoke(array $credentials): User
    {
        $user = User::query()
            ->where('email', $credentials['email'])
            ->whereNotNull('password')
            ->first();

        if (! $user instanceof User || ! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException(__('auth.failed'));
        }

        Auth::login($user);

        session()->regenerate();

        return $user;
    }
}
