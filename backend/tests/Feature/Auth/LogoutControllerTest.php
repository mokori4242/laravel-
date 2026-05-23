<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('正常系', function (): void {
    it('ログアウトできる', function (): void {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        // Sanctum の stateful API は CSRF 検証とセッション Cookie が必要。
        // actingAs() は認証ユーザーだけを設定するため、CSRF token と session cookie は別途設定する。
        $csrfToken = 'test-csrf-token';

        // CSRF 検証で参照される token をテスト用セッションへ保存する。
        $this->withSession(['_token' => $csrfToken]);

        // JSON リクエストへテスト用セッションと同一の Cookie を渡すため、名前と ID を取得する。
        $session = $this->app['session'];

        $this->actingAs($user)
            ->withCredentials()
            ->withCookie($session->getName(), $session->getId())
            ->withHeaders([
                'Origin' => 'http://localhost:3000',
                'X-CSRF-TOKEN' => $csrfToken,
            ])
            ->postJson('/api/logout')
            ->assertOk()
            ->assertExactJson([
                'message' => 'Logged out.',
            ]);

        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});

describe('異常系', function (): void {
    it('未認証ではログアウトできない', function (): void {
        $this->postJson('/api/logout')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});
