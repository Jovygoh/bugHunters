<?php

namespace App\Domain\AiTools\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\AiToolCatalog;
use App\Models\OrganizationAiTool;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<OrganizationAiTool> */
interface AiToolRepositoryInterface extends RepositoryInterface
{
    public function catalogBySlug(string $slug): ?AiToolCatalog;

    public function findByCatalogId(string $organizationId, string $catalogId): ?OrganizationAiTool;

    public function findByEndpointHash(string $organizationId, string $endpointType, string $hash): Collection;
}

