<?php

namespace App\Application\DataClassification\Services;

use App\Domain\DataClassification\Repositories\DataClassificationRepositoryInterface;
use App\Models\ClassificationLevel;
use App\Models\ClassificationScheme;
use App\Models\ViolationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

final readonly class ClassificationDefinitionService
{
    private const LEVELS = [
        'Public' => ['code' => 'public', 'rank' => 1, 'severity' => 'low'],
        'Internal' => ['code' => 'internal', 'rank' => 2, 'severity' => 'medium'],
        'Confidential' => ['code' => 'confidential', 'rank' => 3, 'severity' => 'high'],
        'Highly Confidential' => ['code' => 'highly_confidential', 'rank' => 4, 'severity' => 'critical'],
    ];

    private const VIOLATION_TYPES = [
        'PII' => ['code' => 'pii', 'severity' => 'high'],
        'Financial' => ['code' => 'financial', 'severity' => 'high'],
        'Source Code' => ['code' => 'source_code', 'severity' => 'high'],
        'Company Secret' => ['code' => 'company_secret', 'severity' => 'critical'],
    ];

    public function __construct(private DataClassificationRepositoryInterface $classifications) {}

    public function listLevels(string $organizationId, int $perPage = 25): LengthAwarePaginator
    {
        return $this->classifications->paginateLevels($organizationId, $perPage);
    }

    public function getLevel(string $organizationId, string $levelId): ClassificationLevel
    {
        return $this->classifications->findLevelForOrganization($organizationId, $levelId)
            ?? throw (new ModelNotFoundException)->setModel(ClassificationLevel::class, [$levelId]);
    }

    public function createLevel(string $organizationId, array $attributes): ClassificationLevel
    {
        $definition = $this->levelDefinition($attributes['name']);
        if ($this->classifications->findLevelByCode($organizationId, $definition['code'])) {
            throw ValidationException::withMessages(['name' => ['This classification level already exists.']]);
        }

        $scheme = $this->defaultScheme($organizationId);

        return $this->classifications->createLevel($attributes + [
            'organization_id' => $organizationId,
            'classification_scheme_id' => $scheme->getKey(),
            ...$definition,
        ]);
    }

    public function updateLevel(string $organizationId, string $levelId, array $attributes): ClassificationLevel
    {
        $level = $this->getLevel($organizationId, $levelId);

        if (isset($attributes['name'])) {
            $definition = $this->levelDefinition($attributes['name']);
            $existing = $this->classifications->findLevelByCode($organizationId, $definition['code']);
            if ($existing && $existing->getKey() !== $levelId) {
                throw ValidationException::withMessages(['name' => ['This classification level already exists.']]);
            }
            $attributes = array_merge($attributes, $definition);
        }

        return $this->classifications->updateLevel($level, $attributes);
    }

    public function deleteLevel(string $organizationId, string $levelId): void
    {
        $level = $this->getLevel($organizationId, $levelId);
        if ($this->classifications->levelIsInUse($levelId)) {
            throw ValidationException::withMessages(['level' => ['This level is assigned to data and cannot be deleted.']]);
        }

        $this->classifications->deleteLevel($level);
    }

    public function listViolationTypes(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->classifications->paginateViolationTypes($organizationId, $filters, $perPage);
    }

    public function getViolationType(string $organizationId, string $violationTypeId): ViolationType
    {
        return $this->classifications->findViolationTypeForOrganization($organizationId, $violationTypeId)
            ?? throw (new ModelNotFoundException)->setModel(ViolationType::class, [$violationTypeId]);
    }

    public function createViolationType(string $organizationId, array $attributes): ViolationType
    {
        $definition = $this->violationDefinition($attributes['name']);
        if ($this->classifications->findViolationTypeByCode($organizationId, $definition['code'])) {
            throw ValidationException::withMessages(['name' => ['This violation type already exists.']]);
        }

        return $this->classifications->createViolationType($attributes + [
            'organization_id' => $organizationId,
            ...$definition,
        ]);
    }

    public function updateViolationType(string $organizationId, string $violationTypeId, array $attributes): ViolationType
    {
        $type = $this->getViolationType($organizationId, $violationTypeId);

        if (isset($attributes['name'])) {
            $definition = $this->violationDefinition($attributes['name']);
            $existing = $this->classifications->findViolationTypeByCode($organizationId, $definition['code']);
            if ($existing && $existing->getKey() !== $violationTypeId) {
                throw ValidationException::withMessages(['name' => ['This violation type already exists.']]);
            }
            $attributes = array_merge($attributes, $definition);
        }

        return $this->classifications->updateViolationType($type, $attributes);
    }

    public function deleteViolationType(string $organizationId, string $violationTypeId): void
    {
        $this->classifications->deleteViolationType($this->getViolationType($organizationId, $violationTypeId));
    }

    private function defaultScheme(string $organizationId): ClassificationScheme
    {
        return $this->classifications->defaultScheme($organizationId)
            ?? $this->classifications->createScheme([
                'organization_id' => $organizationId,
                'name' => 'Enterprise Data Classification',
                'description' => 'Default enterprise data classification scheme.',
                'status' => 'active',
                'is_default' => true,
            ]);
    }

    private function levelDefinition(string $name): array
    {
        return self::LEVELS[$name]
            ?? throw ValidationException::withMessages(['name' => ['Unsupported classification level.']]);
    }

    private function violationDefinition(string $name): array
    {
        return self::VIOLATION_TYPES[$name]
            ?? throw ValidationException::withMessages(['name' => ['Unsupported violation type.']]);
    }
}

