<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\UseCases\Auth\GoogleLoginUseCase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class GoogleCallbackController extends Controller
{
    /**
     * @throws AuthenticationException
     */
    public function __invoke(Request $request, GoogleLoginUseCase $login): UserResource
    {
        // GoogleのOAuth 2.0コールバックでは、ユーザーが同意画面で拒否した場合などに
        // codeではなくerrorクエリパラメータ（例: error=access_denied）が返る。
        // Socialiteでユーザー情報を取得する前に、認証キャンセルとして扱う。
        // @see https://developers.google.com/identity/protocols/oauth2/web-server?hl=ja
        if ($request->query('error') !== null) {
            throw new AuthenticationException('Google authentication was cancelled.');
        }

        return new UserResource($login());
    }
}
