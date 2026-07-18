<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Models\Policy;
use App\Models\PolicyEvaluation;
use App\Models\PolicyEvaluationMatch;
use App\Models\PolicyVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\PolicyRule;
use App\Models\PolicyScope;

final class EloquentPolicyRepository extends AbstractEloquentRepository implements PolicyRepositoryInterface
{
    public function __construct(Policy $model) { parent::__construct($model); }

    public function findByCode(string $organizationId, string $code): ?Policy
    {
        return Policy::query()->where('organization_id', $organizationId)->where('code', $code)->first();
    }

    public function publishedForOrganization(string $organizationId, ?string $at = null): Collection
    {
        $at ??= now()->toDateTimeString();

        return Policy::query()
            ->where('organization_id', $organizationId)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhere('effective_from', '<=', $at))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhere('effective_until', '>=', $at))
            ->with(['activeVersion.rules.conditions', 'scopes'])
            ->orderByDesc('priority')
            ->get();
    }

    public function createVersion(array $attributes): PolicyVersion { return PolicyVersion::query()->create($attributes); }

    public function createEvaluation(array $attributes): PolicyEvaluation { return PolicyEvaluation::query()->create($attributes); }

    public function createEvaluationMatch(array $attributes): PolicyEvaluationMatch { return PolicyEvaluationMatch::query()->create($attributes); }

    public function findForOrganization(string $organizationId, string $policyId): ?Policy
    {
        return Policy::query()
            ->with(['activeVersion.rules.conditions', 'versions', 'scopes'])
            ->where('organization_id', $organizationId)
            ->whereKey($policyId)
            ->first();
    }

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Policy::query()->with('activeVersion')->where('organization_id', $organizationId);
        foreach (array_intersect_key($filters, array_flip(['category', 'status', 'default_effect', 'is_mandatory'])) as $column => $value) {
            $query->where($column, $value);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        return $query->orderByDesc('priority')->paginate($perPage);
    }

    public function findVersionForPolicy(string $policyId, string $versionId): ?PolicyVersion
    {
        return PolicyVersion::query()->where('policy_id', $policyId)->whereKey($versionId)->first();
    }

    public function createRule(array $attributes): PolicyRule { return PolicyRule::query()->create($attributes); }

    public function createScope(array $attributes): PolicyScope { return PolicyScope::query()->create($attributes); }

    public function deleteScopes(string $policyId): void
    {
        PolicyScope::query()->where('policy_id', $policyId)->delete();
    }

    public function applicablePolicies(
        string $organizationId,
        string $aiToolId,
        string $classificationLevelId,
        array $roleIds,
        mixed $at,
    ): Collection {
        return Policy::query()
            ->with(['activeVersion.rules', 'scopes'])
            ->where('organization_id', $organizationId)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhere('effective_from', '<=', $at))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhere('effective_until', '>=', $at))
            ->whereHas('scopes', fn ($query) => $query->where('scope_effect', 'include')->where('organization_ai_tool_id', $aiToolId))
            ->whereHas('scopes', fn ($query) => $query->where('scope_effect', 'include')->where('classification_level_id', $classificationLevelId))
            ->whereHas('scopes', fn ($query) => $query->where('scope_effect', 'include')->whereIn('role_id', $roleIds))
            ->orderByDesc('priority')
            ->get();
    }

    protected function filterable(): array { return ['organization_id', 'category', 'status', 'is_mandatory', 'default_effect']; }
}
