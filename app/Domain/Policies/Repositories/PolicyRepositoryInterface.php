<?php

namespace App\Domain\Policies\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Policy;
use App\Models\PolicyEvaluation;
use App\Models\PolicyEvaluationMatch;
use App\Models\PolicyVersion;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<Policy> */
interface PolicyRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $organizationId, string $code): ?Policy;

    public function publishedForOrganization(string $organizationId, ?string $at = null): Collection;

    public function createVersion(array $attributes): PolicyVersion;

    public function createEvaluation(array $attributes): PolicyEvaluation;

    public function createEvaluationMatch(array $attributes): PolicyEvaluationMatch;
}

