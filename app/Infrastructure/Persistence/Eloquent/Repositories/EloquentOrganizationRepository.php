<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Organizations\Repositories\OrganizationRepositoryInterface;
use App\Models\Organization;

final class EloquentOrganizationRepository extends AbstractEloquentRepository implements OrganizationRepositoryInterface
{
    public function __construct(Organization $model) { parent::__construct($model); }

    public function findBySlug(string $slug): ?Organization
    {
        return Organization::query()->where('slug', $slug)->first();
    }

    protected function filterable(): array { return ['status', 'slug']; }
}

