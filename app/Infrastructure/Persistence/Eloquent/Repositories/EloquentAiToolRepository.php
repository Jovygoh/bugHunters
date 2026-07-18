<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\AiTools\Repositories\AiToolRepositoryInterface;
use App\Models\AiToolCatalog;
use App\Models\OrganizationAiTool;
use App\Models\OrganizationAiToolEndpoint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentAiToolRepository extends AbstractEloquentRepository implements AiToolRepositoryInterface
{
    public function __construct(OrganizationAiTool $model) { parent::__construct($model); }

    public function catalogBySlug(string $slug): ?AiToolCatalog
    {
        return AiToolCatalog::query()->where('slug', $slug)->first();
    }

    public function findByCatalogId(string $organizationId, string $catalogId): ?OrganizationAiTool
    {
        return OrganizationAiTool::query()->where('organization_id', $organizationId)->where('catalog_ai_tool_id', $catalogId)->first();
    }

    public function findByEndpointHash(string $organizationId, string $endpointType, string $hash): Collection
    {
        return OrganizationAiToolEndpoint::query()->where('organization_id', $organizationId)->where('endpoint_type', $endpointType)->where('normalized_value_hash', $hash)->get();
    }

    public function findForOrganization(string $organizationId, string $aiToolId): ?OrganizationAiTool
    {
        return OrganizationAiTool::query()
            ->with(['catalogAiTool.vendor', 'endpoints'])
            ->where('organization_id', $organizationId)
            ->whereKey($aiToolId)
            ->first();
    }

    public function findByPrimaryDomain(string $organizationId, string $domain): ?OrganizationAiTool
    {
        return OrganizationAiTool::query()
            ->where('organization_id', $organizationId)
            ->where('primary_domain', $domain)
            ->first();
    }

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = OrganizationAiTool::query()
            ->with(['catalogAiTool.vendor', 'endpoints'])
            ->where('organization_id', $organizationId);

        foreach (array_intersect_key($filters, array_flip(['category', 'risk_level', 'status', 'approval_status'])) as $column => $value) {
            $query->where($column, $value);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($query) => $query
                ->where('display_name', 'like', "%{$search}%")
                ->orWhere('primary_domain', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return $query->orderBy('display_name')->paginate($perPage);
    }

    public function syncPrimaryDomain(string $organizationId, string $aiToolId, string $domain): void
    {
        OrganizationAiToolEndpoint::query()
            ->where('organization_id', $organizationId)
            ->where('organization_ai_tool_id', $aiToolId)
            ->where('endpoint_type', 'domain')
            ->delete();

        OrganizationAiToolEndpoint::query()->create([
            'organization_id' => $organizationId,
            'organization_ai_tool_id' => $aiToolId,
            'endpoint_type' => 'domain',
            'value' => $domain,
            'normalized_value' => $domain,
            'normalized_value_hash' => hash('sha256', $domain, true),
            'match_mode' => 'exact',
        ]);
    }

    public function deleteEndpoints(string $organizationId, string $aiToolId): void
    {
        OrganizationAiToolEndpoint::query()
            ->where('organization_id', $organizationId)
            ->where('organization_ai_tool_id', $aiToolId)
            ->delete();
    }

    protected function filterable(): array { return ['organization_id', 'catalog_ai_tool_id', 'status', 'approval_status', 'risk_level']; }
}
