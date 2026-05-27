<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const ME_USER_NAME = 'Test User';
const ME_USER_EMAIL = 'user@example.com';

describe('正常系', function (): void {
    it('認証済みユーザーを取得できる', function (): void {
        $user = User::factory()->create([
            'name' => ME_USER_NAME,
            'email' => ME_USER_EMAIL,
        ]);

        $this->actingAs($user)
            ->getJson('/api/me')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $user->id,
                    'name' => ME_USER_NAME,
                    'email' => ME_USER_EMAIL,
                ],
            ]);
    });
});

describe('異常系', function (): void {
    it('未認証では認証済みユーザーを取得できない', function (): void {
        $this->getJson('/api/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});
