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

Route::get('live-detections', function() {
    $detections = \Illuminate\Support\Facades\Cache::remember('live_detections', 300, function() {
        return [
            [
                'id' => 'w1', 'name' => 'Clara Ng', 'dept' => 'Engineering',
                'tool' => 'Phind.com', 'toolApproved' => false,
                'file' => 'auth_service_src.zip',
                'uploadStatus' => 'Blocked — Confidential', 'riskLevel' => 'high', 'riskScore' => 89,
                'ip' => '10.0.12.55', 'date' => null,
                'fileType' => 'Archive (.zip)',
                'dataFound' => ['Proprietary source code', 'API secret keys']
            ],
            [
                'id' => 'w2', 'name' => 'Brian Tan', 'dept' => 'Finance',
                'tool' => 'ChatPDF.com', 'toolApproved' => false,
                'file' => 'employee_salary_matrix_2026.xlsx',
                'uploadStatus' => 'Blocked — Confidential', 'riskLevel' => 'high', 'riskScore' => 95,
                'ip' => '10.0.8.77', 'date' => null,
                'fileType' => 'Spreadsheet (.xlsx)',
                'dataFound' => ['Employee salary data', 'Personal IC numbers']
            ],
            [
                'id' => 'w3', 'name' => 'Priya Nair', 'dept' => 'Human Resources',
                'tool' => 'Writesonic.com', 'toolApproved' => false,
                'file' => 'performance_reviews_Q2.docx',
                'uploadStatus' => 'Blocked — Confidential', 'riskLevel' => 'high', 'riskScore' => 87,
                'ip' => '10.0.4.88', 'date' => null,
                'fileType' => 'Document (.docx)',
                'dataFound' => ['Employee performance ratings']
            ],
            [
                'id' => 'w4', 'name' => 'Henry Loh', 'dept' => 'Sales',
                'tool' => 'AskAI.so', 'toolApproved' => false,
                'file' => 'client_list_Q3_2026.csv',
                'uploadStatus' => 'Blocked — Confidential', 'riskLevel' => 'high', 'riskScore' => 82,
                'ip' => '10.0.21.99', 'date' => null,
                'fileType' => 'CSV (.csv)',
                'dataFound' => ['Customer names and contacts']
            ],
            [
                'id' => 'w5', 'name' => 'Marcus Vance', 'dept' => 'Finance',
                'tool' => 'PDFSummarize.ai', 'toolApproved' => false,
                'file' => 'Q2_Financial_Report.xlsx',
                'uploadStatus' => 'Blocked — Confidential', 'riskLevel' => 'high', 'riskScore' => 91,
                'ip' => '10.0.8.12', 'date' => null,
                'fileType' => 'Spreadsheet (.xlsx)',
                'dataFound' => ['Revenue figures', 'Unreleased earnings data']
            ],
            [
                'id' => 'w6', 'name' => 'Rachel Lim', 'dept' => 'Marketing',
                'tool' => 'PromptBase.com', 'toolApproved' => false,
                'file' => 'brand_guidelines_v3_CONFIDENTIAL.pdf',
                'uploadStatus' => 'Blocked — Confidential', 'riskLevel' => 'high', 'riskScore' => 78,
                'ip' => '10.0.15.44', 'date' => null,
                'fileType' => 'Document (.pdf)',
                'dataFound' => ['Confidential brand strategy']
            ],
            [
                'id' => 'w7', 'name' => 'Alexander Wright', 'dept' => 'Engineering',
                'tool' => 'GitHub Copilot', 'toolApproved' => true,
                'file' => 'api_routes.js (code refactor)',
                'uploadStatus' => 'Allowed', 'riskLevel' => 'low', 'riskScore' => 8,
                'ip' => '10.0.12.34', 'date' => null,
                'fileType' => 'Source Code (.js)',
                'dataFound' => ['No confidential content detected']
            ],
            [
                'id' => 'w8', 'name' => 'Sophia Martinez', 'dept' => 'Marketing',
                'tool' => 'ChatGPT Enterprise', 'toolApproved' => true,
                'file' => 'campaign_brief.docx',
                'uploadStatus' => 'Allowed', 'riskLevel' => 'low', 'riskScore' => 14,
                'ip' => '10.0.15.89', 'date' => null,
                'fileType' => 'Document (.docx)',
                'dataFound' => ['No confidential content detected']
            ]
        ];
    });

    foreach ($detections as &$detection) {
        if (empty($detection['date'])) {
            $detection['date'] = now()->format('d M Y, H:i');
        }
    }

    // Dynamic Approved Tools list cached for governance
    $approvedTools = \Illuminate\Support\Facades\Cache::get('approved_tools_list', [
        'GitHub Copilot', 'ChatGPT Enterprise', 'Claude Team', 'Midjourney (Approved)', 'Llama-3 (Local)', 'Gemini 3.1 Pro', 'Claude Sonnet 5'
    ]);

    return response()->json([
        'status' => 'success',
        'timestamp' => now()->toIso8601String(),
        'summary' => [
            'monitored_employees' => 148,
            'approved_tools' => count($approvedTools),
            'approved_tools_list' => $approvedTools,
            'blocked_today' => collect($detections)->filter(fn($d) => str_starts_with($d['uploadStatus'] ?? '', 'Blocked'))->count(),
            'undefined_alerts' => collect($detections)->filter(fn($d) => ($d['riskLevel'] ?? '') === 'high')->count()
        ],
        'detections' => $detections
    ]);
});

Route::post('live-detections/action', function(Illuminate\Http\Request $request) {
    $workerName = $request->input('name');
    $ip = $request->input('ip');
    $action = $request->input('action');
    
    $detections = \Illuminate\Support\Facades\Cache::get('live_detections', []);
    if (empty($detections)) {
        app()->handle(Illuminate\Http\Request::create('/api/live-detections', 'GET'));
        $detections = \Illuminate\Support\Facades\Cache::get('live_detections', []);
    }
    
    // Manage IP restricted map in Cache
    $restrictedIps = \Illuminate\Support\Facades\Cache::get('restricted_ips', []);
    
    if ($ip) {
        if ($action === 'block') {
            $restrictedIps[$ip] = [
                'status' => 'restricted',
                'reason' => 'Access Restricted by Manager',
                'updated_at' => now()->toIso8601String()
            ];
        } elseif ($action === 'warn') {
            $restrictedIps[$ip] = [
                'status' => 'warning',
                'reason' => 'Compliance Warning Issued',
                'updated_at' => now()->toIso8601String()
            ];
        } elseif ($action === 'dismiss' || $action === 'allow') {
            unset($restrictedIps[$ip]);
        }
        \Illuminate\Support\Facades\Cache::put('restricted_ips', $restrictedIps, 86400);
    }
    
    foreach ($detections as &$detection) {
        if (($workerName && ($detection['name'] ?? '') === $workerName) || ($ip && ($detection['ip'] ?? '') === $ip)) {
            if ($action === 'warn') {
                $detection['riskLevel'] = 'medium';
                $detection['uploadStatus'] = 'Warning Issued';
                $detection['riskScore'] = max(($detection['riskScore'] ?? 80) - 20, 40);
            } elseif ($action === 'block') {
                $detection['riskLevel'] = 'low';
                $detection['uploadStatus'] = 'Access Restricted';
                $detection['riskScore'] = 0;
            }
        }
    }
    unset($detection);
    
    \Illuminate\Support\Facades\Cache::put('live_detections', $detections, 300);
    
    return response()->json([
        'status' => 'success',
        'ip' => $ip,
        'action' => $action,
        'ip_restricted' => isset($restrictedIps[$ip]) && $restrictedIps[$ip]['status'] === 'restricted'
    ]);
});

Route::post('live-detections/scan', function(Illuminate\Http\Request $request) {
    $ip = $request->input('ip', $request->ip());
    $tool = $request->input('tool', 'Unknown AI Tool');
    $file = $request->input('file', 'search_query.txt');
    $prompt = $request->input('prompt', '');
    $dataFound = $request->input('dataFound', []);

    // Check IP restriction status first
    $restrictedIps = \Illuminate\Support\Facades\Cache::get('restricted_ips', []);
    if (isset($restrictedIps[$ip]) && $restrictedIps[$ip]['status'] === 'restricted') {
        return response()->json([
            'status' => 'restricted',
            'action' => 'blocked',
            'message' => 'Workstation IP address has been restricted by Governance Policy.',
            'ip' => $ip
        ], 403);
    }

    $approvedTools = \Illuminate\Support\Facades\Cache::get('approved_tools_list', [
        'GitHub Copilot', 'ChatGPT Enterprise', 'Claude Team', 'Midjourney (Approved)', 'Llama-3 (Local)', 'Gemini 3.1 Pro', 'Claude Sonnet 5'
    ]);

    $isApproved = false;
    foreach ($approvedTools as $app) {
        if (strcasecmp($app, $tool) === 0 || stripos($tool, str_replace([' (Approved)', ' (Whitelisted)', ' (Enterprise)', ' (Corporate)', ' (Local)'], '', $app)) !== false) {
            $isApproved = true;
            break;
        }
    }

    $detections = \Illuminate\Support\Facades\Cache::get('live_detections', []);
    if (empty($detections)) {
        app()->handle(Illuminate\Http\Request::create('/api/live-detections', 'GET'));
        $detections = \Illuminate\Support\Facades\Cache::get('live_detections', []);
    }

    if (!$isApproved || !empty($dataFound)) {
        $newDetection = [
            'id' => 'scan-' . time() . '-' . rand(100, 999),
            'name' => 'Workstation (' . $ip . ')',
            'dept' => $request->input('dept', 'Engineering'),
            'tool' => $tool,
            'toolApproved' => $isApproved,
            'file' => $file,
            'uploadStatus' => 'Blocked — Confidential',
            'riskLevel' => 'high',
            'riskScore' => $isApproved ? 75 : 92,
            'ip' => $ip,
            'date' => now()->format('d M Y, H:i'),
            'fileType' => $request->input('fileType', 'Search Query / File'),
            'dataFound' => !empty($dataFound) ? (array)$dataFound : ['Unapproved AI Tool Detection', 'Potential confidential search prompt'],
            'prompt' => $prompt
        ];

        array_unshift($detections, $newDetection);
        \Illuminate\Support\Facades\Cache::put('live_detections', $detections, 300);

        return response()->json([
            'status' => 'blocked',
            'tool' => $tool,
            'toolApproved' => $isApproved,
            'ip' => $ip,
            'detection' => $newDetection,
            'message' => 'Upload / Search intercepted. 0 Bytes sent to unapproved AI tool.'
        ]);
    } else {
        $allowedRecord = [
            'id' => 'scan-' . time() . '-' . rand(100, 999),
            'name' => 'Workstation (' . $ip . ')',
            'dept' => $request->input('dept', 'Engineering'),
            'tool' => $tool,
            'toolApproved' => true,
            'file' => $file,
            'uploadStatus' => 'Allowed',
            'riskLevel' => 'low',
            'riskScore' => 10,
            'ip' => $ip,
            'date' => now()->format('d M Y, H:i'),
            'fileType' => 'Safe Search Query',
            'dataFound' => ['No confidential content detected'],
            'prompt' => $prompt
        ];

        array_unshift($detections, $allowedRecord);
        \Illuminate\Support\Facades\Cache::put('live_detections', $detections, 300);

        return response()->json([
            'status' => 'allowed',
            'tool' => $tool,
            'toolApproved' => true,
            'ip' => $ip,
            'message' => 'Search cleared by policy.'
        ]);
    }
});

Route::post('live-detections/approve-tool', function(Illuminate\Http\Request $request) {
    $tool = $request->input('tool');
    if (!$tool) {
        return response()->json(['status' => 'error', 'message' => 'Tool name required'], 400);
    }

    $approvedTools = \Illuminate\Support\Facades\Cache::get('approved_tools_list', [
        'GitHub Copilot', 'ChatGPT Enterprise', 'Claude Team', 'Midjourney (Approved)', 'Llama-3 (Local)', 'Gemini 3.1 Pro', 'Claude Sonnet 5'
    ]);

    if (!in_array($tool, $approvedTools)) {
        $approvedTools[] = $tool;
        \Illuminate\Support\Facades\Cache::put('approved_tools_list', $approvedTools, 86400);
    }

    // Update existing detections matching this tool
    $detections = \Illuminate\Support\Facades\Cache::get('live_detections', []);
    foreach ($detections as &$detection) {
        if (strcasecmp($detection['tool'] ?? '', $tool) === 0 || stripos($detection['tool'] ?? '', $tool) !== false) {
            $detection['toolApproved'] = true;
            $detection['riskLevel'] = 'low';
            $detection['uploadStatus'] = 'Approved by Manager';
            $detection['riskScore'] = 12;
        }
    }
    unset($detection);
    \Illuminate\Support\Facades\Cache::put('live_detections', $detections, 300);

    return response()->json([
        'status' => 'success',
        'message' => "AI Tool '{$tool}' has been approved and whitelisted by manager.",
        'approved_tools' => $approvedTools
    ]);
});

Route::post('live-detections/reject-tool', function(Illuminate\Http\Request $request) {
    $tool = $request->input('tool');
    if (!$tool) {
        return response()->json(['status' => 'error', 'message' => 'Tool name required'], 400);
    }

    $approvedTools = \Illuminate\Support\Facades\Cache::get('approved_tools_list', []);
    $approvedTools = array_values(array_filter($approvedTools, fn($t) => strcasecmp($t, $tool) !== 0));
    \Illuminate\Support\Facades\Cache::put('approved_tools_list', $approvedTools, 86400);

    return response()->json([
        'status' => 'success',
        'message' => "AI Tool '{$tool}' has been rejected and permanently blocked.",
        'approved_tools' => $approvedTools
    ]);
});

Route::get('live-detections/check-ip', function(Illuminate\Http\Request $request) {
    $ip = $request->query('ip', $request->ip());
    $restrictedIps = \Illuminate\Support\Facades\Cache::get('restricted_ips', []);
    $approvedTools = \Illuminate\Support\Facades\Cache::get('approved_tools_list', [
        'GitHub Copilot', 'ChatGPT Enterprise', 'Claude Team', 'Midjourney (Approved)', 'Llama-3 (Local)', 'Gemini 3.1 Pro', 'Claude Sonnet 5'
    ]);

    $ipStatus = $restrictedIps[$ip] ?? ['status' => 'clean', 'reason' => 'No active IP restrictions'];

    return response()->json([
        'status' => 'success',
        'ip' => $ip,
        'ip_policy_status' => $ipStatus,
        'approved_tools' => $approvedTools
    ]);
});

Route::get('bugs', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'BugHunters Backend API is online and actively scanning.'
    ]);
});


