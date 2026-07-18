<?php

namespace App\Application\Notifications\Services;

use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;

final readonly class ManagerNotificationService
{
    public function __construct(
        private EmployeeRepositoryInterface $employees,
        private NotificationService $notifications,
    ) {}

    public function newAiToolDetected(string $organizationId, string $employeeId, string $domain, string $findingId, float $riskScore): void
    {
        $this->notifyManagers($organizationId, $employeeId, [
            'notification_type' => 'new_ai_tool_detected',
            'severity' => $riskScore >= 70 ? 'high' : 'medium',
            'title' => 'New AI tool detected',
            'body' => "An unknown AI tool domain ({$domain}) was detected.",
            'data' => ['finding_id' => $findingId, 'domain' => $domain, 'risk_score' => $riskScore],
        ]);
    }

    public function highRiskIncident(string $organizationId, string $employeeId, string $incidentId, float $riskScore): void
    {
        $this->notifyManagers($organizationId, $employeeId, [
            'notification_type' => 'high_risk_incident',
            'severity' => $riskScore >= 90 ? 'critical' : 'high',
            'title' => 'High-risk AI incident',
            'body' => 'A high-risk AI governance incident requires attention.',
            'incident_id' => $incidentId,
            'data' => ['incident_id' => $incidentId, 'risk_score' => $riskScore],
        ]);
    }

    public function policyViolation(string $organizationId, string $employeeId, string $evaluationId, string $decision): void
    {
        $this->notifyManagers($organizationId, $employeeId, [
            'notification_type' => 'policy_violation',
            'severity' => $decision === 'block' ? 'high' : 'medium',
            'title' => 'AI policy violation',
            'body' => "An employee action resulted in a {$decision} policy decision.",
            'policy_evaluation_id' => $evaluationId,
            'data' => ['policy_evaluation_id' => $evaluationId, 'decision' => $decision],
        ]);
    }

    public function deviceSuspicious(string $organizationId, string $employeeId, string $deviceId, string $status, ?float $riskScore): void
    {
        $this->notifyManagers($organizationId, $employeeId, [
            'notification_type' => 'device_suspicious',
            'severity' => ($riskScore ?? 0) >= 90 ? 'critical' : 'high',
            'title' => 'Suspicious employee device',
            'body' => 'A device reported suspicious or noncompliant posture.',
            'data' => ['device_id' => $deviceId, 'compliance_status' => $status, 'risk_score' => $riskScore],
        ]);
    }

    private function notifyManagers(string $organizationId, string $employeeId, array $message): void
    {
        foreach ($this->employees->managerUsersForEmployee($organizationId, $employeeId) as $manager) {
            $this->notifications->send($organizationId, $manager->getKey(), $message, ['database']);
        }
    }
}

