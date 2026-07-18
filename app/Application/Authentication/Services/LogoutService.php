<?php

namespace App\Application\Authentication\Services;

use App\Domain\Authentication\Repositories\AuthTokenRepositoryInterface;
use App\Models\User;

final readonly class LogoutService
{
    public function __construct(private AuthTokenRepositoryInterface $tokens) {}

    public function execute(User $user): void
    {
        $this->tokens->revokeCurrent($user);
    }
}

