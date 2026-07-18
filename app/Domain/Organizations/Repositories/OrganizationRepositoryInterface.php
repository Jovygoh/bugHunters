<?php

namespace App\Domain\Organizations\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Organization;

/** @extends RepositoryInterface<Organization> */
interface OrganizationRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug): ?Organization;
}

