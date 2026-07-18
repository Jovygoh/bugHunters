<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Models\Policy;
use App\Models\PolicyEvaluation;
use App\Models\PolicyEvaluationMatch;
use App\Models\PolicyVersion;
use Illuminate\Database\Eloquent\Collection;

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

    protected function filterable(): array { return ['organization_id', 'category', 'status', 'is_mandatory', 'default_effect']; }
}

