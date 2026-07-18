<?php

namespace App\Domain\DataClassification\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\ClassificationAssignment;
use App\Models\ClassificationScheme;
use App\Models\DataAsset;
use App\Models\ClassificationLevel;
use App\Models\ViolationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<DataAsset> */
interface DataClassificationRepositoryInterface extends RepositoryInterface
{
    public function defaultScheme(string $organizationId): ?ClassificationScheme;

    public function levelsForScheme(string $schemeId): Collection;

    public function findAssetByExternalId(string $organizationId, string $sourceSystem, string $externalId): ?DataAsset;

    public function createAssignment(array $attributes): ClassificationAssignment;

    public function createScheme(array $attributes): ClassificationScheme;

    public function findLevelForOrganization(string $organizationId, string $levelId): ?ClassificationLevel;

    public function findLevelByCode(string $organizationId, string $code): ?ClassificationLevel;

    public function paginateLevels(string $organizationId, int $perPage = 25): LengthAwarePaginator;

    public function createLevel(array $attributes): ClassificationLevel;

    public function updateLevel(ClassificationLevel $level, array $attributes): ClassificationLevel;

    public function deleteLevel(ClassificationLevel $level): bool;

    public function levelIsInUse(string $levelId): bool;

    public function findViolationTypeForOrganization(string $organizationId, string $violationTypeId): ?ViolationType;

    public function findViolationTypeByCode(string $organizationId, string $code): ?ViolationType;

    public function paginateViolationTypes(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function createViolationType(array $attributes): ViolationType;

    public function updateViolationType(ViolationType $violationType, array $attributes): ViolationType;

    public function deleteViolationType(ViolationType $violationType): bool;
}
