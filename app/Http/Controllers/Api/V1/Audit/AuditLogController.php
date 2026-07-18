<?php

namespace App\Http\Controllers\Api\V1\Audit;

use App\Application\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Audit\IndexAuditLogRequest;
use App\Http\Requests\Api\V1\Audit\ShowAuditLogRequest;
use App\Http\Resources\Api\V1\AuditLogResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogs) {}

    public function index(IndexAuditLogRequest $request): AnonymousResourceCollection
    {
        return AuditLogResource::collection($this->auditLogs->list(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 50),
        ));
    }

    public function show(ShowAuditLogRequest $request, string $auditLog): AuditLogResource
    {
        return new AuditLogResource($this->auditLogs->get(
            $request->user()->organization_id,
            $auditLog,
        ));
    }
}
