<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Authentication\Repositories\UserRepositoryInterface;
use App\Models\User;

final class EloquentUserRepository extends AbstractEloquentRepository implements UserRepositoryInterface
{
    public function __construct(User $model) { parent::__construct($model); }

    public function findByEmail(string $organizationId, string $normalizedEmail): ?User
    {
        return User::query()
            ->with('roles')
            ->where('organization_id', $organizationId)
            ->where('normalized_email', $normalizedEmail)
            ->first();
    }

    public function findActiveById(string $id): ?User
    {
        return User::query()->whereKey($id)->where('status', 'active')->first();
    }

    public function findActiveWithRoles(string $id): ?User
    {
        return User::query()
            ->with(['roles', 'employee.department'])
            ->whereKey($id)
            ->where('status', 'active')
            ->first();
    }

    protected function filterable(): array { return ['organization_id', 'status', 'normalized_email']; }
}
