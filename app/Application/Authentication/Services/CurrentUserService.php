<?php

namespace App\Application\Authentication\Services;

use App\Domain\Authentication\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

final readonly class CurrentUserService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(string $userId): User
    {
        return $this->users->findActiveWithRoles($userId)
            ?? throw new AuthenticationException('The authenticated account is unavailable.');
    }
}

