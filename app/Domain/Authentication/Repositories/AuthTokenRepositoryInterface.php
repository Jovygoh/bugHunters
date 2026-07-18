<?php

namespace App\Domain\Authentication\Repositories;

use App\Models\User;
use DateTimeInterface;
use Laravel\Sanctum\NewAccessToken;

interface AuthTokenRepositoryInterface
{
    public function issue(User $user, string $name, array $abilities, ?DateTimeInterface $expiresAt = null): NewAccessToken;

    public function revokeCurrent(User $user): void;
}
