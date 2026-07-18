<?php

namespace App\Domain\Authentication\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\User;

/** @extends RepositoryInterface<User> */
interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $organizationId, string $normalizedEmail): ?User;

    public function findActiveById(string $id): ?User;

    public function findActiveWithRoles(string $id): ?User;
}
