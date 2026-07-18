<?php

namespace App\Application\Policies\Services;

use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Models\PolicyEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Resolves matched rule effects deterministically and persists the decision with its match evidence. */
final readonly class PolicyDecisionService
{
    private const PRECEDENCE = ['allow' => 1, 'warn' => 2, 'review' => 3, 'block' => 4];

    public function __construct(private PolicyRepositoryInterface $policies) {}

    public function execute(string $organizationId, array $context, array $matches): PolicyEvaluation
    {
        if ($matches === []) {
            throw ValidationException::withMessages(['matches' => ['At least one evaluated policy result is required.']]);
        }

        usort($matches, fn (array $left, array $right): int =>
            [self::PRECEDENCE[$right['effect']] ?? 0, $right['priority'] ?? 0]
            <=>
            [self::PRECEDENCE[$left['effect']] ?? 0, $left['priority'] ?? 0]
        );

        $winner = $matches[0];

        return DB::transaction(function () use ($organizationId, $context, $matches, $winner): PolicyEvaluation {
            $evaluation = $this->policies->createEvaluation([
                'organization_id' => $organizationId,
                'employee_id' => $context['employee_id'] ?? null,
                'device_id' => $context['device_id'] ?? null,
                'organization_ai_tool_id' => $context['organization_ai_tool_id'] ?? null,
                'data_asset_id' => $context['data_asset_id'] ?? null,
                'discovery_observation_id' => $context['discovery_observation_id'] ?? null,
                'requested_action' => $context['requested_action'],
                'decision' => $winner['effect'],
                'reason_code' => $winner['reason_code'],
                'risk_score' => $context['risk_score'] ?? null,
                'context' => $context,
                'context_hash' => hash('sha256', json_encode($context, JSON_THROW_ON_ERROR), true),
                'obligations' => $winner['obligations'] ?? null,
                'correlation_id' => $context['correlation_id'],
                'engine_version' => $context['engine_version'],
                'evaluated_at' => now(),
            ]);

            foreach ($matches as $match) {
                $this->policies->createEvaluationMatch($match + [
                    'organization_id' => $organizationId,
                    'policy_evaluation_id' => $evaluation->getKey(),
                    'matched' => true,
                ]);
            }

            return $evaluation;
        });
    }
}
