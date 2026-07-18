<?php

namespace App\Application\DataClassification\Services;

use App\Domain\DataClassification\Repositories\DataClassificationRepositoryInterface;
use App\Models\ClassificationAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Classifies assets while preserving an immutable assignment history and current projection. */
final readonly class DataClassificationService
{
    public function __construct(private DataClassificationRepositoryInterface $classifications) {}

    public function registerAsset(string $organizationId, array $attributes): Model
    {
        if (isset($attributes['source_system'], $attributes['external_id'])) {
            $existing = $this->classifications->findAssetByExternalId($organizationId, $attributes['source_system'], $attributes['external_id']);
            if ($existing) {
                return $existing;
            }
        }

        return $this->classifications->create($attributes + ['organization_id' => $organizationId]);
    }

    public function classify(string $organizationId, string $assetId, string $levelId, array $attributes): ClassificationAssignment
    {
        return DB::transaction(function () use ($organizationId, $assetId, $levelId, $attributes): ClassificationAssignment {
            $asset = $this->classifications->findOrFail($assetId);
            abort_unless($asset->getAttribute('organization_id') === $organizationId, 404);

            $assignment = $this->classifications->createAssignment($attributes + [
                'organization_id' => $organizationId,
                'data_asset_id' => $assetId,
                'classification_level_id' => $levelId,
                'effective_at' => $attributes['effective_at'] ?? now(),
            ]);

            $this->classifications->update($asset, ['current_classification_level_id' => $levelId]);

            return $assignment;
        });
    }
}

