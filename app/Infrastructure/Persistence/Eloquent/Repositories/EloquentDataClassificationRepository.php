<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\DataClassification\Repositories\DataClassificationRepositoryInterface;
use App\Models\ClassificationAssignment;
use App\Models\ClassificationLevel;
use App\Models\ClassificationScheme;
use App\Models\DataAsset;
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

    protected function filterable(): array { return ['organization_id', 'department_id', 'owner_employee_id', 'current_classification_level_id', 'asset_type', 'status']; }
}

