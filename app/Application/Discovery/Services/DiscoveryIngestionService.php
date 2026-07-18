<?php

namespace App\Application\Discovery\Services;

use App\Domain\Discovery\Repositories\DiscoveryRepositoryInterface;
use App\Models\DiscoveryObservation;
use Illuminate\Support\Facades\DB;

/** Creates discovery scans and idempotently ingests normalized source observations. */
final readonly class DiscoveryIngestionService
{
    public function __construct(private DiscoveryRepositoryInterface $discovery) {}

    public function startScan(string $organizationId, string $sourceId, array $attributes = [])
    {
        return $this->discovery->createScan($attributes + [
            'organization_id' => $organizationId,
            'discovery_source_id' => $sourceId,
            'status' => 'queued',
            'scan_type' => $attributes['scan_type'] ?? 'incremental',
        ]);
    }

    /** @return list<DiscoveryObservation> */
    public function ingestBatch(string $organizationId, string $sourceId, array $observations): array
    {
        return DB::transaction(function () use ($organizationId, $sourceId, $observations): array {
            $stored = [];
            foreach ($observations as $observation) {
                $identity = [
                    'discovery_source_id' => $sourceId,
                    'external_event_id' => $observation['external_event_id'],
                ];
                $stored[] = $this->discovery->upsertObservation($identity, $observation + [
                    'organization_id' => $organizationId,
                    'discovery_source_id' => $sourceId,
                    'ingested_at' => now(),
                ]);
            }
            return $stored;
        });
    }
}

