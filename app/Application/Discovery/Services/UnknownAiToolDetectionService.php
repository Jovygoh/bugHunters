<?php

namespace App\Application\Discovery\Services;

use App\Domain\Discovery\Repositories\DiscoveryRepositoryInterface;
use App\Models\DiscoveryFinding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Application\Notifications\Services\ManagerNotificationService;

final readonly class UnknownAiToolDetectionService
{
    public function __construct(
        private DiscoveryRepositoryInterface $discovery,
        private ManagerNotificationService $managerNotifications,
    ) {}

    public function detect(string $organizationId, array $attributes): DiscoveryFinding
    {
        $domain = mb_strtolower(rtrim(trim($attributes['domain']), '.'));
        $detectedAt = $attributes['detection_time'];
        $deduplicationKey = hash(
            'sha256',
            implode('|', [$organizationId, $domain, $attributes['employee_id'], $attributes['device_id']]),
            true
        );

        $isNew = false;
        $finding = DB::transaction(function () use ($organizationId, $attributes, $domain, $detectedAt, $deduplicationKey, &$isNew): DiscoveryFinding {
            $existing = $this->discovery->findByDeduplicationKey($organizationId, $deduplicationKey);

            if ($existing) {
                $riskScore = max((float) $existing->risk_score, (float) $attributes['risk_score']);
                $detectedTimestamp = Carbon::parse($detectedAt);
                $lastObservedAt = $detectedTimestamp->greaterThan($existing->last_observed_at)
                    ? $detectedTimestamp
                    : $existing->last_observed_at;

                return $this->discovery->update($existing, [
                    'last_observed_at' => $lastObservedAt,
                    'occurrence_count' => $existing->occurrence_count + 1,
                    'risk_score' => $riskScore,
                    'severity' => $this->severity($riskScore),
                    'status' => $existing->status === 'resolved' ? 'open' : $attributes['status'],
                    'resolved_by' => null,
                    'resolved_at' => null,
                    'resolution_code' => null,
                    'resolution_notes' => null,
                ]);
            }

            /** @var DiscoveryFinding $finding */
            $isNew = true;
            $finding = $this->discovery->create([
                'organization_id' => $organizationId,
                'employee_id' => $attributes['employee_id'],
                'device_id' => $attributes['device_id'],
                'finding_type' => 'unknown_ai_tool',
                'detected_domain' => $domain,
                'severity' => $this->severity((float) $attributes['risk_score']),
                'status' => $attributes['status'],
                'title' => "Unknown AI tool detected: {$domain}",
                'description' => 'An unregistered AI tool domain was detected for an employee device.',
                'risk_score' => $attributes['risk_score'],
                'first_observed_at' => $detectedAt,
                'last_observed_at' => $detectedAt,
                'occurrence_count' => 1,
                'deduplication_key' => $deduplicationKey,
            ]);

            return $finding;
        });

        if ($isNew) {
            $this->managerNotifications->newAiToolDetected(
                $organizationId,
                $attributes['employee_id'],
                $domain,
                $finding->getKey(),
                (float) $attributes['risk_score'],
            );
        }

        return $finding;
    }

    private function severity(float $riskScore): string
    {
        return match (true) {
            $riskScore >= 90 => 'critical',
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'medium',
            default => 'low',
        };
    }
}
