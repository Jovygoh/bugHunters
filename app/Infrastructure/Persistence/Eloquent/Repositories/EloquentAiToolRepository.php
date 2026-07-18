<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\AiTools\Repositories\AiToolRepositoryInterface;
use App\Models\AiToolCatalog;
use App\Models\OrganizationAiTool;
use App\Models\OrganizationAiToolEndpoint;
use Illuminate\Database\Eloquent\Collection;

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

    protected function filterable(): array { return ['organization_id', 'catalog_ai_tool_id', 'status', 'approval_status', 'risk_level']; }
}

