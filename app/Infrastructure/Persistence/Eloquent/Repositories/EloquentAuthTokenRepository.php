<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Authentication\Repositories\AuthTokenRepositoryInterface;
use App\Models\User;
use DateTimeInterface;
use Laravel\Sanctum\NewAccessToken;

final class EloquentAuthTokenRepository implements AuthTokenRepositoryInterface
{
    public function issue(User $user, string $name, array $abilities, ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        return $user->createToken($name, $abilities, $expiresAt);
    }

    public function revokeCurrent(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
