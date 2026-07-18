<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\DataClassification\Repositories\DataClassificationRepositoryInterface;
use App\Models\ClassificationAssignment;
use App\Models\ClassificationLevel;
use App\Models\ClassificationScheme;
use App\Models\DataAsset;
use App\Models\ViolationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class EloquentDataClassificationRepository extends AbstractEloquentRepository implements DataClassificationRepositoryInterface
{
    public function __construct(DataAsset $model) { parent::__construct($model); }

    public function defaultScheme(string $organizationId): ?ClassificationScheme
    {
        return ClassificationScheme::query()->where('organization_id', $organizationId)->where('is_default', true)->first();
    }

    public function levelsForScheme(string $schemeId): Collection
    {
        return ClassificationLevel::query()->where('classification_scheme_id', $schemeId)->orderBy('rank')->get();
    }

    public function findAssetByExternalId(string $organizationId, string $sourceSystem, string $externalId): ?DataAsset
    {
        return DataAsset::query()->where('organization_id', $organizationId)->where('source_system', $sourceSystem)->where('external_id', $externalId)->first();
    }

    public function createAssignment(array $attributes): ClassificationAssignment { return ClassificationAssignment::query()->create($attributes); }

    public function createScheme(array $attributes): ClassificationScheme
    {
        return ClassificationScheme::query()->create($attributes);
    }

    public function findLevelForOrganization(string $organizationId, string $levelId): ?ClassificationLevel
    {
        return ClassificationLevel::query()
            ->with('classificationScheme')
            ->where('organization_id', $organizationId)
            ->whereKey($levelId)
            ->first();
    }

    public function findLevelByCode(string $organizationId, string $code): ?ClassificationLevel
    {
        return ClassificationLevel::query()
            ->where('organization_id', $organizationId)
            ->where('code', $code)
            ->first();
    }

    public function paginateLevels(string $organizationId, int $perPage = 25): LengthAwarePaginator
    {
        return ClassificationLevel::query()
            ->with('classificationScheme')
            ->where('organization_id', $organizationId)
            ->orderBy('rank')
            ->paginate($perPage);
    }

    public function createLevel(array $attributes): ClassificationLevel
    {
        return ClassificationLevel::query()->create($attributes);
    }

    public function updateLevel(ClassificationLevel $level, array $attributes): ClassificationLevel
    {
        $level->fill($attributes)->save();

        return $level->refresh();
    }

    public function deleteLevel(ClassificationLevel $level): bool
    {
        return (bool) $level->delete();
    }

    public function levelIsInUse(string $levelId): bool
    {
        return DataAsset::query()->where('current_classification_level_id', $levelId)->exists()
            || ClassificationAssignment::query()->where('classification_level_id', $levelId)->exists();
    }

    public function findViolationTypeForOrganization(string $organizationId, string $violationTypeId): ?ViolationType
    {
        return ViolationType::query()->where('organization_id', $organizationId)->whereKey($violationTypeId)->first();
    }

    public function findViolationTypeByCode(string $organizationId, string $code): ?ViolationType
    {
        return ViolationType::query()->where('organization_id', $organizationId)->where('code', $code)->first();
    }

    public function paginateViolationTypes(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = ViolationType::query()->where('organization_id', $organizationId);
        foreach (array_intersect_key($filters, array_flip(['severity', 'status'])) as $column => $value) {
            $query->where($column, $value);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function createViolationType(array $attributes): ViolationType
    {
        return ViolationType::query()->create($attributes);
    }

    public function updateViolationType(ViolationType $violationType, array $attributes): ViolationType
    {
        $violationType->fill($attributes)->save();

        return $violationType->refresh();
    }

    public function deleteViolationType(ViolationType $violationType): bool
    {
        return (bool) $violationType->delete();
    }

    protected function filterable(): array { return ['organization_id', 'department_id', 'owner_employee_id', 'current_classification_level_id', 'asset_type', 'status']; }
}
