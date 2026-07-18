<?php

namespace App\Application\Policies\Services;

use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Models\PolicyVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Creates policy identities and immutable-numbered draft versions. */
final readonly class PolicyAuthoringService
{
    public function __construct(private PolicyRepositoryInterface $policies) {}

    public function create(string $organizationId, array $attributes): Model
    {
        if ($this->policies->findByCode($organizationId, $attributes['code'])) {
            throw ValidationException::withMessages(['code' => ['The policy code is already in use.']]);
        }

        return $this->policies->create($attributes + ['organization_id' => $organizationId, 'status' => 'draft']);
    }

    public function createVersion(string $organizationId, string $policyId, array $definition, int $versionNumber, array $attributes = []): PolicyVersion
    {
        return DB::transaction(function () use ($organizationId, $policyId, $definition, $versionNumber, $attributes): PolicyVersion {
            $policy = $this->policies->findOrFail($policyId);
            abort_unless($policy->getAttribute('organization_id') === $organizationId, 404);

            return $this->policies->createVersion($attributes + [
                'organization_id' => $organizationId,
                'policy_id' => $policyId,
                'version_number' => $versionNumber,
                'status' => 'draft',
                'definition' => $definition,
                'definition_hash' => hash('sha256', json_encode($definition, JSON_THROW_ON_ERROR), true),
            ]);
        });
    }
}

