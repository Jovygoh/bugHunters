<?php

namespace App\Application\Policies\Services;

use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Models\Policy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class PolicyManagementService
{
    public function __construct(private PolicyRepositoryInterface $policies) {}

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->policies->paginateForOrganization($organizationId, $filters, $perPage);
    }

    public function get(string $organizationId, string $policyId): Policy
    {
        return $this->policies->findForOrganization($organizationId, $policyId)
            ?? throw (new ModelNotFoundException)->setModel(Policy::class, [$policyId]);
    }

    public function create(string $organizationId, array $attributes): Policy
    {
        if ($this->policies->findByCode($organizationId, $attributes['code'])) {
            throw ValidationException::withMessages(['code' => ['The policy code is already in use.']]);
        }

        return DB::transaction(function () use ($organizationId, $attributes): Policy {
            /** @var Policy $policy */
            $policy = $this->policies->create([
                'organization_id' => $organizationId,
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'description' => $attributes['description'] ?? null,
                'category' => $attributes['category'] ?? 'ai_governance',
                'status' => 'draft',
                'priority' => $attributes['priority'] ?? 0,
                'is_mandatory' => $attributes['is_mandatory'] ?? false,
                'default_effect' => $attributes['decision'],
                'effective_from' => $attributes['effective_from'] ?? null,
                'effective_until' => $attributes['effective_until'] ?? null,
                'created_by' => $attributes['actor_user_id'],
                'updated_by' => $attributes['actor_user_id'],
            ]);

            $this->createDefinition($organizationId, $policy, $attributes, 1);

            return $policy->refresh();
        });
    }

    public function update(string $organizationId, string $policyId, array $attributes): Policy
    {
        $policy = $this->get($organizationId, $policyId);
        if ($policy->status === 'published') {
            throw ValidationException::withMessages(['policy' => ['Published policies cannot be edited; create a replacement policy.']]);
        }

        return DB::transaction(function () use ($organizationId, $policy, $attributes): Policy {
            $updates = ['updated_by' => $attributes['actor_user_id']];
            foreach ([
                'name' => 'name',
                'description' => 'description',
                'priority' => 'priority',
                'is_mandatory' => 'is_mandatory',
                'decision' => 'default_effect',
                'effective_from' => 'effective_from',
                'effective_until' => 'effective_until',
            ] as $input => $column) {
                if (array_key_exists($input, $attributes)) {
                    $updates[$column] = $attributes[$input];
                }
            }
            $updated = $this->policies->update($policy, $updates);

            $this->policies->deleteScopes($policy->getKey());
            $versionNumber = ((int) $policy->versions->max('version_number')) + 1;
            $this->createDefinition($organizationId, $policy, $attributes + [
                'decision' => $policy->default_effect,
                'ai_tool_ids' => $policy->scopes->pluck('organization_ai_tool_id')->filter()->all(),
                'classification_level_ids' => $policy->scopes->pluck('classification_level_id')->filter()->all(),
                'role_ids' => $policy->scopes->pluck('role_id')->filter()->all(),
            ], $versionNumber);

            return $updated->refresh();
        });
    }

    public function publish(string $organizationId, string $policyId, string $publisherId): Policy
    {
        return DB::transaction(function () use ($organizationId, $policyId, $publisherId): Policy {
            $policy = $this->get($organizationId, $policyId);
            $version = $policy->versions->sortByDesc('version_number')->first();

            if (! $version || $version->status !== 'draft') {
                throw ValidationException::withMessages(['policy' => ['No draft policy version is available.']]);
            }

            $this->policies->update($version, ['status' => 'published', 'published_by' => $publisherId, 'published_at' => now()]);
            /** @var Policy $published */
            $published = $this->policies->update($policy, [
                'status' => 'published',
                'active_version_id' => $version->getKey(),
                'updated_by' => $publisherId,
            ]);

            return $published;
        });
    }

    public function delete(string $organizationId, string $policyId): void
    {
        $policy = $this->get($organizationId, $policyId);
        if ($policy->status === 'published') {
            throw ValidationException::withMessages(['policy' => ['Published policies must be retired instead of deleted.']]);
        }

        $this->policies->delete($policy);
    }

    private function createDefinition(string $organizationId, Policy $policy, array $attributes, int $versionNumber): void
    {
        $definition = [
            'decision' => $attributes['decision'],
            'ai_tool_ids' => array_values($attributes['ai_tool_ids']),
            'classification_level_ids' => array_values($attributes['classification_level_ids']),
            'role_ids' => array_values($attributes['role_ids']),
        ];

        $version = $this->policies->createVersion([
            'organization_id' => $organizationId,
            'policy_id' => $policy->getKey(),
            'version_number' => $versionNumber,
            'status' => 'draft',
            'definition_schema' => '1.0',
            'definition' => $definition,
            'definition_hash' => hash('sha256', json_encode($definition, JSON_THROW_ON_ERROR), true),
            'created_by' => $attributes['actor_user_id'],
        ]);

        $this->policies->createRule([
            'organization_id' => $organizationId,
            'policy_version_id' => $version->getKey(),
            'name' => 'Primary decision',
            'sequence' => 1,
            'effect' => $attributes['decision'],
            'condition_mode' => 'all',
            'reason_code' => 'POLICY_'.$attributes['decision'],
            'is_enabled' => true,
        ]);

        foreach (['organization_ai_tool_id' => 'ai_tool_ids', 'classification_level_id' => 'classification_level_ids', 'role_id' => 'role_ids'] as $column => $input) {
            foreach ($attributes[$input] as $targetId) {
                $this->policies->createScope([
                    'organization_id' => $organizationId,
                    'policy_id' => $policy->getKey(),
                    'scope_effect' => 'include',
                    $column => $targetId,
                ]);
            }
        }
    }
}
