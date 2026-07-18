<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Audit\Repositories\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use App\Models\AuditLogExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function append(array $attributes): AuditLog
    {
        return AuditLog::query()->create($attributes);
    }

    public function findForOrganization(string $organizationId, string $id): ?AuditLog
    {
        return AuditLog::query()
            ->where('organization_id', $organizationId)
            ->whereKey($id)
            ->first();
    }

    public function paginate(string $organizationId, array $filters = [], int $perPage = 100): LengthAwarePaginator
    {
        $query = AuditLog::query()->where('organization_id', $organizationId);

        foreach (array_intersect_key($filters, array_flip(['actor_user_id', 'actor_type', 'action', 'module', 'auditable_type', 'auditable_id', 'outcome', 'source', 'correlation_id'])) as $column => $value) {
            $query->where($column, $value);
        }

        if (isset($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to']);
        }

        return $query->orderByDesc('occurred_at')->paginate($perPage);
    }

    public function createExport(array $attributes): AuditLogExport
    {
        return AuditLogExport::query()->create($attributes);
    }
}
