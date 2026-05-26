<?php

use App\Enums\SocialProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Socialite;

uses(RefreshDatabase::class);

function fakeGoogleUser(
    string $id = 'google-sub-123',
    ?string $name = 'Google User',
    ?string $email = 'google@example.com',
): SocialiteUser {
    $googleUser = Mockery::mock(SocialiteUser::class);

    $googleUser->shouldReceive('getId')->andReturn($id);
    $googleUser->shouldReceive('getName')->andReturn($name);
    $googleUser->shouldReceive('getEmail')->andReturn($email);

    return $googleUser;
}

describe('正常系', function (): void {
    it('Google認証画面へリダイレクトできる', function (): void {
        Socialite::fake(SocialProvider::Google->value);

        $this->get('/api/auth/google/redirect')
            ->assertRedirect('https://socialite.fake/google/authorize');
    });

    it('Google callbackで新規ユーザーを作成してログインできる', function (): void {
        Socialite::fake(SocialProvider::Google->value, fakeGoogleUser());

        $this->getJson('/api/auth/google/callback')
            ->assertCreated()
            ->assertJsonPath('data.name', 'Google User')
            ->assertJsonPath('data.email', 'google@example.com');

        $user = User::query()->where('email', 'google@example.com')->firstOrFail();

        expect($user->password)->toBeNull();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('user_social_accounts', [
            'user_id' => $user->id,
            'provider' => SocialProvider::Google->value,
            'provider_user_id' => 'google-sub-123',
        ]);
    });

    it('同じprovider_user_idが登録済みの場合は紐づくユーザーでログインできる', function (): void {
        $user = User::factory()->create([
            'email' => 'linked@example.com',
        ]);

        $user->socialAccounts()->create([
            'provider' => SocialProvider::Google->value,
            'provider_user_id' => 'google-sub-123',
        ]);

        Socialite::fake(SocialProvider::Google->value, fakeGoogleUser(email: null));

        $this->getJson('/api/auth/google/callback')
            ->assertOk()
            ->assertJsonPath('data.email', 'linked@example.com');

        $this->assertAuthenticatedAs($user);
    });

    it('同じメールアドレスの既存ユーザーにGoogleアカウントを紐づけできる', function (): void {
        $user = User::factory()->create([
            'email' => 'google@example.com',
        ]);

        Socialite::fake(SocialProvider::Google->value, fakeGoogleUser());

        $this->getJson('/api/auth/google/callback')
            ->assertOk()
            ->assertJsonPath('data.email', 'google@example.com');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('user_social_accounts', [
            'user_id' => $user->id,
            'provider' => SocialProvider::Google->value,
            'provider_user_id' => 'google-sub-123',
        ]);
    });
});

describe('異常系', function (): void {
    it('Google認証がキャンセルされた場合は拒否する', function (): void {
        $this->getJson('/api/auth/google/callback?error=access_denied')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Google authentication was cancelled.');

        $this->assertGuest();
    });
});
