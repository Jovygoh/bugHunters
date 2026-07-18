<?php

namespace App\Http\Middleware;

use App\Application\Audit\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class AuditAdministrativeAction
{
    public function __construct(private AuditLogService $auditLogs) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            if ($request->user() !== null && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                $this->recordFailure($request, $exception);
            }

            throw $exception;
        }

        if ($request->user() !== null && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $this->record($request, $response);
        }

        return $response;
    }

    private function record(Request $request, Response $response): void
    {
        $routeName = $request->route()?->getName() ?? 'api.unknown';
        $segments = explode('.', $routeName);
        $module = $segments[2] ?? 'unknown';
        $operation = end($segments) ?: strtolower($request->method());
        $routeParameters = array_map(
            static fn (mixed $value): mixed => is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : $value,
            $request->route()?->parameters() ?? [],
        );
        $targetId = $this->targetId($routeParameters);

        try {
            $this->auditLogs->record($request->user()->organization_id, [
                'actor_user_id' => $request->user()->getKey(),
                'actor_type' => 'user',
                'actor_identifier' => $request->user()->email,
                'action' => $operation,
                'module' => $module,
                'auditable_type' => $targetId === null ? null : $module,
                'auditable_id' => $targetId,
                'outcome' => $response->isSuccessful() ? 'success' : 'failure',
                'description' => sprintf('%s %s', $request->method(), $routeName),
                'new_values' => $request->except(['password', 'password_confirmation', 'token']),
                'source' => 'rest_api',
                'http_method' => $request->method(),
                'request_path' => $request->path(),
                'ip_address' => $request->ip() === null ? null : @inet_pton($request->ip()),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'metadata' => [
                    'route_name' => $routeName,
                    'route_parameters' => $routeParameters,
                    'status_code' => $response->getStatusCode(),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function recordFailure(Request $request, Throwable $exception): void
    {
        $routeName = $request->route()?->getName() ?? 'api.unknown';
        $segments = explode('.', $routeName);

        try {
            $this->auditLogs->record($request->user()->organization_id, [
                'actor_user_id' => $request->user()->getKey(),
                'actor_type' => 'user',
                'actor_identifier' => $request->user()->email,
                'action' => end($segments) ?: strtolower($request->method()),
                'module' => $segments[2] ?? 'unknown',
                'outcome' => 'failure',
                'description' => sprintf('%s %s failed', $request->method(), $routeName),
                'source' => 'rest_api',
                'http_method' => $request->method(),
                'request_path' => $request->path(),
                'metadata' => ['route_name' => $routeName, 'exception' => $exception::class],
            ]);
        } catch (Throwable $auditException) {
            report($auditException);
        }
    }

    private function targetId(array $parameters): ?string
    {
        foreach (array_reverse($parameters) as $value) {
            $id = is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : $value;
            if (is_string($id) && preg_match('/^[0-9a-f-]{36}$/i', $id) === 1) return $id;
        }

        return null;
    }
}
