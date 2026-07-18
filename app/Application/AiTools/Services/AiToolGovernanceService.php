<?php

namespace App\Application\AiTools\Services;

use App\Domain\AiTools\Repositories\AiToolRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Registers tenant AI-tool profiles and applies valid governance review outcomes. */
final readonly class AiToolGovernanceService
{
    public function __construct(private AiToolRepositoryInterface $aiTools) {}

    public function register(string $organizationId, array $attributes): Model
    {
        $attributes = $this->normalize($attributes);

        if (! empty($attributes['catalog_ai_tool_id']) && $this->aiTools->findByCatalogId($organizationId, $attributes['catalog_ai_tool_id'])) {
            throw ValidationException::withMessages(['catalog_ai_tool_id' => ['This AI tool is already registered.']]);
        }

        if ($this->aiTools->findByPrimaryDomain($organizationId, $attributes['primary_domain'])) {
            throw ValidationException::withMessages(['domain' => ['This domain is already registered.']]);
        }

        return DB::transaction(function () use ($organizationId, $attributes): Model {
            $tool = $this->aiTools->create($attributes + [
                'organization_id' => $organizationId,
                'approval_status' => 'unreviewed',
            ]);
            $this->aiTools->syncPrimaryDomain($organizationId, $tool->getKey(), $attributes['primary_domain']);

            return $tool;
        });
    }

    public function get(string $organizationId, string $aiToolId): Model
    {
        return $this->aiTools->findForOrganization($organizationId, $aiToolId)
            ?? throw (new ModelNotFoundException)->setModel('OrganizationAiTool', [$aiToolId]);
    }

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->aiTools->paginateForOrganization($organizationId, $filters, $perPage);
    }

    public function update(string $organizationId, string $aiToolId, array $attributes): Model
    {
        $tool = $this->get($organizationId, $aiToolId);
        $attributes = $this->normalize($attributes);

        if (isset($attributes['primary_domain'])) {
            $existing = $this->aiTools->findByPrimaryDomain($organizationId, $attributes['primary_domain']);
            if ($existing && $existing->getKey() !== $aiToolId) {
                throw ValidationException::withMessages(['domain' => ['This domain is already registered.']]);
            }
        }

        return DB::transaction(function () use ($organizationId, $aiToolId, $tool, $attributes): Model {
            $updated = $this->aiTools->update($tool, $attributes);
            if (isset($attributes['primary_domain'])) {
                $this->aiTools->syncPrimaryDomain($organizationId, $aiToolId, $attributes['primary_domain']);
            }

            return $updated;
        });
    }

    public function delete(string $organizationId, string $aiToolId): void
    {
        DB::transaction(function () use ($organizationId, $aiToolId): void {
            $tool = $this->get($organizationId, $aiToolId);
            $this->aiTools->deleteEndpoints($organizationId, $aiToolId);
            $this->aiTools->delete($tool);
        });
    }

    public function review(string $organizationId, string $aiToolId, string $decision, string $reviewerId, ?string $riskLevel = null): Model
    {
        if (! in_array($decision, ['approved', 'restricted', 'blocked'], true)) {
            throw ValidationException::withMessages(['decision' => ['Invalid review decision.']]);
        }

        $tool = $this->aiTools->findOrFail($aiToolId);
        abort_unless($tool->getAttribute('organization_id') === $organizationId, 404);

        $attributes = ['approval_status' => $decision, 'reviewed_by' => $reviewerId, 'reviewed_at' => now()];
        if ($riskLevel) {
            $attributes['risk_level'] = $riskLevel;
        }
        if ($decision === 'approved') {
            $attributes += ['approved_by' => $reviewerId, 'approved_at' => now()];
        }
        if ($decision === 'blocked') {
            $attributes += ['blocked_by' => $reviewerId, 'blocked_at' => now()];
        }

        return $this->aiTools->update($tool, $attributes);
    }

    private function normalize(array $attributes): array
    {
        if (array_key_exists('tool_name', $attributes)) {
            $attributes['display_name'] = trim($attributes['tool_name']);
            unset($attributes['tool_name']);
        }

        if (array_key_exists('domain', $attributes)) {
            $domain = mb_strtolower(trim($attributes['domain']));
            $attributes['primary_domain'] = rtrim($domain, '.');
            unset($attributes['domain']);
        }

        return $attributes;
    }
}
