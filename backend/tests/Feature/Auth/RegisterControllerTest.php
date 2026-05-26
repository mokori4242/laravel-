<?php

use App\Models\User;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

const REGISTER_USER_NAME = 'Register User';
const REGISTER_USER_EMAIL = 'register@example.com';
const REGISTER_VALID_PASSWORD = 'CorrectHorseBatteryStaple42Aa!';

beforeEach(function (): void {
    $this->mock(UncompromisedVerifier::class)
        ->shouldReceive('verify')
        ->andReturnTrue();
});

describe('正常系', function (): void {
    it('メールアドレスとパスワードでユーザー登録できる', function (): void {
        $this->postJson('/api/register', [
            'name' => REGISTER_USER_NAME,
            'email' => REGISTER_USER_EMAIL,
            'password' => REGISTER_VALID_PASSWORD,
            'password_confirmation' => REGISTER_VALID_PASSWORD,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', REGISTER_USER_NAME)
            ->assertJsonPath('data.email', REGISTER_USER_EMAIL);

        $user = User::query()->where('email', REGISTER_USER_EMAIL)->firstOrFail();

        expect(Hash::check(REGISTER_VALID_PASSWORD, $user->password))->toBeTrue();

        $this->assertAuthenticatedAs($user);
    });
});

describe('異常系', function (): void {
    it('必須項目が未入力の場合は登録できない', function (): void {
        $this->postJson('/api/register')
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => ['The name field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ]);

        $this->assertGuest();
    });

    it('メールアドレスが重複している場合は登録できない', function (): void {
        User::factory()->create([
            'email' => REGISTER_USER_EMAIL,
        ]);

        $this->postJson('/api/register', [
            'name' => REGISTER_USER_NAME,
            'email' => REGISTER_USER_EMAIL,
            'password' => REGISTER_VALID_PASSWORD,
            'password_confirmation' => REGISTER_VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => ['The email has already been taken.'],
            ]);

        $this->assertGuest();
    });

    it('パスワード確認が一致しない場合は登録できない', function (): void {
        $this->postJson('/api/register', [
            'name' => REGISTER_USER_NAME,
            'email' => REGISTER_USER_EMAIL,
            'password' => REGISTER_VALID_PASSWORD,
            'password_confirmation' => 'DifferentPassword42Aa!',
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => ['The password field confirmation does not match.'],
            ]);

        $this->assertGuest();
    });

    it('パスワードが漏洩済みの場合は登録できない', function (): void {
        $this->mock(UncompromisedVerifier::class)
            ->shouldReceive('verify')
            ->andReturnFalse();

        $this->postJson('/api/register', [
            'name' => REGISTER_USER_NAME,
            'email' => REGISTER_USER_EMAIL,
            'password' => REGISTER_VALID_PASSWORD,
            'password_confirmation' => REGISTER_VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => ['The given password has appeared in a data leak. Please choose a different password.'],
            ]);

        $this->assertGuest();
    });
});
