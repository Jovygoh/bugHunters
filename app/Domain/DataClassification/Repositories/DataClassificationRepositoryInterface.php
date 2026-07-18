<?php

namespace App\Domain\DataClassification\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\ClassificationAssignment;
use App\Models\ClassificationScheme;
use App\Models\DataAsset;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<DataAsset> */
interface DataClassificationRepositoryInterface extends RepositoryInterface
{
    public function defaultScheme(string $organizationId): ?ClassificationScheme;

    public function levelsForScheme(string $schemeId): Collection;

    public function findAssetByExternalId(string $organizationId, string $sourceSystem, string $externalId): ?DataAsset;

    public function createAssignment(array $attributes): ClassificationAssignment;
}

