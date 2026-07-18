<?php

namespace App\Application\AiTools\Services;

use App\Domain\AiTools\Repositories\AiToolRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** Registers tenant AI-tool profiles and applies valid governance review outcomes. */
final readonly class AiToolGovernanceService
{
    public function __construct(private AiToolRepositoryInterface $aiTools) {}

    public function register(string $organizationId, array $attributes): Model
    {
        if (! empty($attributes['catalog_ai_tool_id']) && $this->aiTools->findByCatalogId($organizationId, $attributes['catalog_ai_tool_id'])) {
            throw ValidationException::withMessages(['catalog_ai_tool_id' => ['This AI tool is already registered.']]);
        }

        return $this->aiTools->create($attributes + ['organization_id' => $organizationId]);
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
}

