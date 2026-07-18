<?php

namespace App\Application\Authentication\Services;

use App\Domain\Authentication\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/** Verifies tenant-scoped credentials and returns the authenticated identity. */
final readonly class AuthenticateUserService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(string $organizationId, string $email, string $password): User
    {
        $user = $this->users->findByEmail($organizationId, mb_strtolower(trim($email)));

        if (! $user || $user->status !== 'active' || ! $user->password || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('The provided credentials are invalid.');
        }

        return $user;
    }
}

