<?php

namespace App\Application\Policies\Services;

use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Models\PolicyEvaluation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Application\Notifications\Services\ManagerNotificationService;

final readonly class EvaluatePolicyService
{
    public function __construct(
        private PolicyRepositoryInterface $policies,
        private EmployeeRepositoryInterface $employees,
        private PolicyDecisionService $decisions,
        private ManagerNotificationService $managerNotifications,
    ) {}

    public function execute(string $organizationId, array $context): PolicyEvaluation
    {
        $employee = $this->employees->findForOrganization($organizationId, $context['employee_id'])
            ?? throw (new ModelNotFoundException)->setModel('Employee', [$context['employee_id']]);

        $roleIds = $employee->user?->roles?->pluck('id')->all() ?? [];
        $policies = $this->policies->applicablePolicies(
            $organizationId,
            $context['ai_tool_id'],
            $context['classification_level_id'],
            $roleIds,
            now(),
        );

        $evaluationContext = [
            'employee_id' => $context['employee_id'],
            'organization_ai_tool_id' => $context['ai_tool_id'],
            'data_asset_id' => $context['data_asset_id'] ?? null,
            'requested_action' => $context['action'],
            'classification_level_id' => $context['classification_level_id'],
            'employee_role_ids' => $roleIds,
            'correlation_id' => $context['correlation_id'],
            'engine_version' => '1.0',
        ];

        if ($policies->isEmpty()) {
            $evaluation = $this->policies->createEvaluation([
                'organization_id' => $organizationId,
                ...$evaluationContext,
                'decision' => 'block',
                'reason_code' => 'NO_APPLICABLE_POLICY',
                'context' => $evaluationContext,
                'context_hash' => hash('sha256', json_encode($evaluationContext, JSON_THROW_ON_ERROR), true),
                'evaluated_at' => now(),
            ]);

            $this->managerNotifications->policyViolation(
                $organizationId,
                $context['employee_id'],
                $evaluation->getKey(),
                'block',
            );

            return $evaluation;
        }

        $matches = $policies->map(function ($policy): array {
            $rule = $policy->activeVersion->rules->first();

            return [
                'policy_id' => $policy->getKey(),
                'policy_version_id' => $policy->active_version_id,
                'policy_rule_id' => $rule?->getKey(),
                'effect' => $rule?->effect ?? $policy->default_effect,
                'reason_code' => $rule?->reason_code ?? 'POLICY_MATCHED',
                'priority' => $policy->priority,
                'match_details' => ['matched_scope_ids' => $policy->scopes->pluck('id')->all()],
            ];
        })->all();

        $evaluation = $this->decisions->execute($organizationId, $evaluationContext, $matches);

        if ($evaluation->decision !== 'allow') {
            $this->managerNotifications->policyViolation(
                $organizationId,
                $context['employee_id'],
                $evaluation->getKey(),
                $evaluation->decision,
            );
        }

        return $evaluation;
    }
}
