<?php

namespace App\Domain\Incidents\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\IncidentEvent;
use App\Models\IncidentEvidence;
use Illuminate\Database\Eloquent\Collection;

/** @extends RepositoryInterface<Incident> */
interface IncidentRepositoryInterface extends RepositoryInterface
{
    public function findByNumber(string $organizationId, int $incidentNumber): ?Incident;

    public function openForAssignee(string $organizationId, string $userId): Collection;

    public function createEvent(array $attributes): IncidentEvent;

    public function createComment(array $attributes): IncidentComment;

    public function createEvidence(array $attributes): IncidentEvidence;
}

