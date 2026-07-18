<?php

namespace App\Domain\Discovery\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\DiscoveryFinding;
use App\Models\DiscoveryObservation;
use App\Models\DiscoveryScan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends RepositoryInterface<DiscoveryFinding> */
interface DiscoveryRepositoryInterface extends RepositoryInterface
{
    public function createScan(array $attributes): DiscoveryScan;

    public function createObservation(array $attributes): DiscoveryObservation;

    public function upsertObservation(array $identity, array $attributes): DiscoveryObservation;

    public function paginateObservations(string $organizationId, array $filters = [], int $perPage = 100): LengthAwarePaginator;

    public function findFindingForOrganization(string $organizationId, string $findingId): ?DiscoveryFinding;

    public function findByDeduplicationKey(string $organizationId, string $deduplicationKey): ?DiscoveryFinding;

    public function paginateFindings(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator;
}
