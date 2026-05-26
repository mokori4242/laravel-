<?php

use App\Models\User;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

const LOGIN_USER_NAME = 'Test User';
const LOGIN_USER_EMAIL = 'user@example.com';
const VALID_PASSWORD = 'CorrectHorseBatteryStaple42Aa!';

beforeEach(function (): void {
    // 対象テスト以外、パスワード漏洩チェックで外部通信させない
    $this->mock(UncompromisedVerifier::class)
        ->shouldReceive('verify')
        ->andReturnTrue();
});

describe('正常系', function (): void {
    it('ログインできる', function (): void {
        $user = User::factory()->create([
            'name' => LOGIN_USER_NAME,
            'email' => LOGIN_USER_EMAIL,
            'password' => Hash::make(VALID_PASSWORD),
        ]);

        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => VALID_PASSWORD,
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $user->id,
                    'name' => LOGIN_USER_NAME,
                    'email' => LOGIN_USER_EMAIL,
                ],
            ]);

        $this->assertAuthenticatedAs($user);
    });
});

describe('異常系', function (): void {
    it('メールアドレスが未入力の場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'password' => VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => ['The email field is required.'],
            ]);

        $this->assertGuest();
    });

    it('パスワードが未入力の場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => ['The password field is required.'],
            ]);

        $this->assertGuest();
    });

    it('メールアドレスが文字列でない場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => [LOGIN_USER_EMAIL],
            'password' => VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => [
                    'The email field must be a string.',
                    'The email field must be a valid email address.',
                ],
            ]);

        $this->assertGuest();
    });

    it('メールアドレスの形式が不正な場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => ['The email field must be a valid email address.'],
            ]);

        $this->assertGuest();
    });

    it('メールアドレスが255文字を超える場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => 'user@'.str_repeat('a', 63).'.'.str_repeat('b', 63).'.'.str_repeat('c', 63).'.'.str_repeat('d', 58).'.com',
            'password' => VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => [
                    'The email field must be a valid email address.',
                    'The email field must not be greater than 255 characters.',
                ],
            ]);

        $this->assertGuest();
    });

    it('パスワードが文字列でない場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => ['password'],
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => [
                    'The password field must be a string.',
                    'The password field must be at least 8 characters.',
                ],
            ]);

        $this->assertGuest();
    });

    it('パスワードが8文字未満の場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => 'Aa1!',
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => ['The password field must be at least 8 characters.'],
            ]);

        $this->assertGuest();
    });

    it('パスワードに大文字と小文字が含まれていない場合はログインできない', function (): void {
        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => 'lowercase-password',
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => ['The password field must contain at least one uppercase and one lowercase letter.'],
            ]);

        $this->assertGuest();
    });

    it('パスワードが漏洩済みの場合はログインできない', function (): void {
        $this->mock(UncompromisedVerifier::class)
            ->shouldReceive('verify')
            ->andReturnFalse();

        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => VALID_PASSWORD,
        ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => ['The given password has appeared in a data leak. Please choose a different password.'],
            ]);

        $this->assertGuest();
    });

    it('認証情報が誤っている場合はログインできない', function (): void {
        User::factory()->create([
            'email' => LOGIN_USER_EMAIL,
            'password' => Hash::make(VALID_PASSWORD),
        ]);

        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => 'WrongPassword42Aa!',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'These credentials do not match our records.');

        $this->assertGuest();
    });

    it('ログイン試行回数が上限を超えると拒否される', function (): void {
        config(['app.debug' => false]);

        User::factory()->create([
            'email' => LOGIN_USER_EMAIL,
            'password' => Hash::make(VALID_PASSWORD),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/login', [
                'email' => LOGIN_USER_EMAIL,
                'password' => 'WrongPassword42Aa!',
            ])
                ->assertUnauthorized()
                ->assertJsonPath('message', 'These credentials do not match our records.');
        }

        $this->postJson('/api/login', [
            'email' => LOGIN_USER_EMAIL,
            'password' => 'WrongPassword42Aa!',
        ])
            ->assertTooManyRequests()
            ->assertExactJson([
                'message' => 'Too Many Attempts.',
            ]);
    });
});
