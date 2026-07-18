<?php

namespace App\Application\Discovery\Services;

use App\Domain\Discovery\Repositories\DiscoveryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/** Creates, assigns, and resolves normalized discovery findings. */
final readonly class DiscoveryFindingService
{
    public function __construct(private DiscoveryRepositoryInterface $discovery) {}

    public function create(string $organizationId, array $attributes): Model
    {
        return $this->discovery->create($attributes + [
            'organization_id' => $organizationId,
            'status' => 'open',
            'occurrence_count' => $attributes['occurrence_count'] ?? 1,
        ]);
    }

    public function resolve(string $organizationId, string $findingId, string $userId, string $resolutionCode, ?string $notes = null): Model
    {
        if ($resolutionCode === '') {
            throw ValidationException::withMessages(['resolution_code' => ['A resolution code is required.']]);
        }

        $finding = $this->get($organizationId, $findingId);

        return $this->discovery->update($finding, [
            'status' => 'resolved',
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_code' => $resolutionCode,
            'resolution_notes' => $notes,
        ]);
    }

    public function get(string $organizationId, string $findingId): Model
    {
        return $this->discovery->findFindingForOrganization($organizationId, $findingId)
            ?? throw (new ModelNotFoundException)->setModel('DiscoveryFinding', [$findingId]);
    }

    public function list(string $organizationId, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->discovery->paginateFindings($organizationId, $filters, $perPage);
    }

    public function changeStatus(string $organizationId, string $findingId, string $status): Model
    {
        return $this->discovery->update($this->get($organizationId, $findingId), ['status' => $status]);
    }
}
