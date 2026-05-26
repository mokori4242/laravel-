<?php

namespace App\UseCases\Auth;

use App\Enums\SocialProvider;
use App\Models\User;
use App\Models\UserSocialAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginUseCase
{
    public function __invoke(): User
    {
        $googleUser = Socialite::driver(SocialProvider::Google->value)->user();
        $providerUserId = $googleUser->getId();
        $email = $googleUser->getEmail();

        $user = DB::transaction(function () use ($googleUser, $providerUserId, $email): User {
            $socialAccount = UserSocialAccount::query()
                ->with('user')
                ->where('provider', SocialProvider::Google->value)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($socialAccount instanceof UserSocialAccount) {
                return $socialAccount->user;
            }

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email_verified_at' => now(),
                    'password' => null,
                ],
            );

            $user->socialAccounts()->create([
                'provider' => SocialProvider::Google->value,
                'provider_user_id' => $providerUserId,
            ]);

            return $user;
        });

        Auth::login($user);

        session()->regenerate();

        return $user;
    }
}
