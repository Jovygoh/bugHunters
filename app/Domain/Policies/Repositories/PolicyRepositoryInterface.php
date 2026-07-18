<?php

namespace App\Domain\Policies\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Policy;
use App\Models\PolicyEvaluation;
use App\Models\PolicyEvaluationMatch;
use App\Models\PolicyVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\PolicyRule;
use App\Models\PolicyScope;

/** @extends RepositoryInterface<Policy> */
interface PolicyRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $organizationId, string $code): ?Policy;

    public function publishedForOrganization(string $organizationId, ?string $at = null): Collection;

    public function createVersion(array $attributes): PolicyVersion;

    public function createEvaluation(array $attributes): PolicyEvaluation;

    public function createEvaluationMatch(array $attributes): PolicyEvaluationMatch;

    public function findForOrganization(string $organizationId, string $policyId): ?Policy;

    public function paginateForOrganization(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function findVersionForPolicy(string $policyId, string $versionId): ?PolicyVersion;

    public function createRule(array $attributes): PolicyRule;

    public function createScope(array $attributes): PolicyScope;

    public function deleteScopes(string $policyId): void;

    public function applicablePolicies(
        string $organizationId,
        string $aiToolId,
        string $classificationLevelId,
        array $roleIds,
        mixed $at,
    ): Collection;
}
