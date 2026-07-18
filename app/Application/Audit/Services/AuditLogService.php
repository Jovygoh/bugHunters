<?php

namespace App\Application\Audit\Services;

use App\Domain\Audit\Repositories\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Redacts sensitive values and appends immutable audit records. */
final readonly class AuditLogService
{
    private const SENSITIVE_KEYS = ['password', 'password_confirmation', 'token', 'secret', 'authorization', 'api_key'];

    public function __construct(private AuditLogRepositoryInterface $auditLogs) {}

    public function record(string $organizationId, array $attributes): AuditLog
    {
        foreach (['old_values', 'new_values', 'metadata'] as $field) {
            if (isset($attributes[$field]) && is_array($attributes[$field])) {
                $attributes[$field] = $this->redact($attributes[$field]);
            }
        }

        return $this->auditLogs->append($attributes + [
            'organization_id' => $organizationId,
            'occurred_at' => now(),
        ]);
    }

    public function list(string $organizationId, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->auditLogs->paginate($organizationId, $filters, $perPage);
    }

    public function get(string $organizationId, string $id): AuditLog
    {
        return $this->auditLogs->findForOrganization($organizationId, $id) ?? abort(404);
    }

    private function redact(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array(mb_strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $values[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $values[$key] = $this->redact($value);
            }
        }

        return $values;
    }
}
