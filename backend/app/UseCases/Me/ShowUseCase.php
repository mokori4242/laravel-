<?php

namespace App\UseCases\Me;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class ShowUseCase
{
    public function __invoke(Authenticatable $user): User
    {
        /** @var User $user */
        return $user;
    }
}
