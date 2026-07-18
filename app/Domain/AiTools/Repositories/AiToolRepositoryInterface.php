<?php

namespace App\Domain\AiTools\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\AiToolCatalog;
use App\Models\OrganizationAiTool;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends RepositoryInterface<OrganizationAiTool> */
interface AiToolRepositoryInterface extends RepositoryInterface
{
    public function catalogBySlug(string $slug): ?AiToolCatalog;

    public function findByCatalogId(string $organizationId, string $catalogId): ?OrganizationAiTool;

    public function findByEndpointHash(string $organizationId, string $endpointType, string $hash): Collection;

    public function findForOrganization(string $organizationId, string $aiToolId): ?OrganizationAiTool;

    public function findByPrimaryDomain(string $organizationId, string $domain): ?OrganizationAiTool;

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function syncPrimaryDomain(string $organizationId, string $aiToolId, string $domain): void;

    public function deleteEndpoints(string $organizationId, string $aiToolId): void;
}
