<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Employees\EmployeeController;
use App\Http\Controllers\Api\V1\Departments\DepartmentController;
use App\Http\Controllers\Api\V1\Devices\DeviceController;
use App\Http\Controllers\Api\V1\AiTools\AiToolController;
use App\Http\Controllers\Api\V1\Discovery\UnknownAiToolDetectionController;
use App\Http\Controllers\Api\V1\DataClassification\ClassificationLevelController;
use App\Http\Controllers\Api\V1\DataClassification\ViolationTypeController;
use App\Http\Controllers\Api\V1\Policies\PolicyController;
use App\Http\Controllers\Api\V1\Policies\PolicyEvaluationController;
use App\Http\Controllers\Api\V1\Incidents\IncidentController;
use App\Http\Controllers\Api\V1\Notifications\NotificationController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\Audit\AuditLogController;
use App\Http\Middleware\AuditAdministrativeAction;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->name('api.v1.auth.')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', AuditAdministrativeAction::class])
    ->group(function (): void {
        Route::apiResource('employees', EmployeeController::class)
            ->whereUuid('employee');
        Route::apiResource('departments', DepartmentController::class)
            ->whereUuid('department');
        Route::post('devices/{device}/heartbeat', [DeviceController::class, 'heartbeat'])
            ->whereUuid('device')
            ->name('devices.heartbeat');
        Route::post('devices/{device}/verify', [DeviceController::class, 'verify'])
            ->whereUuid('device')
            ->name('devices.verify');
        Route::apiResource('devices', DeviceController::class)
            ->whereUuid('device');
        Route::apiResource('ai-tools', AiToolController::class)
            ->parameters(['ai-tools' => 'aiTool'])
            ->whereUuid('aiTool');
        Route::get('discovery/unknown-tools', [UnknownAiToolDetectionController::class, 'index'])
            ->name('discovery.unknown-tools.index');
        Route::post('discovery/unknown-tools', [UnknownAiToolDetectionController::class, 'store'])
            ->name('discovery.unknown-tools.store');
        Route::get('discovery/unknown-tools/{finding}', [UnknownAiToolDetectionController::class, 'show'])
            ->whereUuid('finding')
            ->name('discovery.unknown-tools.show');
        Route::patch('discovery/unknown-tools/{finding}/status', [UnknownAiToolDetectionController::class, 'updateStatus'])
            ->whereUuid('finding')
            ->name('discovery.unknown-tools.status');
        Route::apiResource('classification-levels', ClassificationLevelController::class)
            ->parameters(['classification-levels' => 'classificationLevel'])
            ->whereUuid('classificationLevel');
        Route::apiResource('violation-types', ViolationTypeController::class)
            ->parameters(['violation-types' => 'violationType'])
            ->whereUuid('violationType');
        Route::post('policies/{policy}/publish', [PolicyController::class, 'publish'])
            ->whereUuid('policy')
            ->name('policies.publish');
        Route::apiResource('policies', PolicyController::class)
            ->whereUuid('policy');
        Route::post('policy-evaluations', [PolicyEvaluationController::class, 'store'])
            ->name('policy-evaluations.store');
        Route::post('incidents/{incident}/transition', [IncidentController::class, 'transition'])
            ->whereUuid('incident')
            ->name('incidents.transition');
        Route::apiResource('incidents', IncidentController::class)
            ->except('destroy')
            ->whereUuid('incident');
        Route::get('notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('notifications.unread-count');
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])
            ->whereUuid('notification')
            ->name('notifications.read');
        Route::put('notification-preferences', [NotificationController::class, 'updatePreference'])
            ->name('notification-preferences.update');
        Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
            Route::get('summary', [DashboardController::class, 'summary'])->name('summary');
            Route::get('monthly-statistics', [DashboardController::class, 'monthlyStatistics'])->name('monthly-statistics');
            Route::get('incident-trends', [DashboardController::class, 'incidentTrends'])->name('incident-trends');
            Route::get('top-violations', [DashboardController::class, 'topViolations'])->name('top-violations');
        });
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])
            ->whereUuid('auditLog')
            ->name('audit-logs.show');
    });
