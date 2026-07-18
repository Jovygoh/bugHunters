<?php

namespace App\Domain\Audit\Repositories;

use App\Models\AuditLog;
use App\Models\AuditLogExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function append(array $attributes): AuditLog;

    public function findForOrganization(string $organizationId, string $id): ?AuditLog;

    public function paginate(string $organizationId, array $filters = [], int $perPage = 100): LengthAwarePaginator;

    public function createExport(array $attributes): AuditLogExport;
}
