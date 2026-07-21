<?php

namespace App\Application\AiTools\Services;

use Illuminate\Support\Facades\Cache;

class AiToolApprovalWorkflowService
{
    private const APPROVED_CACHE_KEY = 'approved_tools_list';
    private const PENDING_REQUESTS_KEY = 'pending_ai_tool_requests';

    /**
     * Process employee 3-question survey for new AI tool request.
     * Fast-Path Compliance Engine: Auto-approves low-risk tools instantly.
     */
    public function evaluateRequest(array $data): array
    {
        $toolName = trim($data['tool_name'] ?? 'Unknown AI Tool');
        $domain = trim($data['domain'] ?? strtolower(preg_replace('/[^a-zA-Z0-9.]/', '', $toolName)) . '.com');
        $requestedBy = trim($data['requested_by'] ?? 'Employee');
        $department = trim($data['department'] ?? 'Engineering');

        $storesCompanyData = filter_var($data['stores_company_data'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $handlesPiiFinancial = filter_var($data['handles_pii_financial'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $enterpriseCertified = filter_var($data['enterprise_certified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Fast-Path Rule: Low risk = No company data storage + No PII/Financial + SOC-2/ISO Certified
        $isLowRisk = (!$storesCompanyData && !$handlesPiiFinancial && $enterpriseCertified);

        $requestId = 'req-' . time() . '-' . rand(100, 999);

        if ($isLowRisk) {
            // Auto-Approve instantly
            $approvedTools = Cache::get(self::APPROVED_CACHE_KEY, [
                'GitHub Copilot', 'ChatGPT Enterprise', 'Claude Team', 'Midjourney (Approved)', 'Llama-3 (Local)', 'Gemini 3.1 Pro', 'Claude Sonnet 5'
            ]);

            if (!in_array($toolName, $approvedTools)) {
                $approvedTools[] = $toolName;
                Cache::put(self::APPROVED_CACHE_KEY, $approvedTools, 86400);
            }

            return [
                'status' => 'approved',
                'auto_approved' => true,
                'request_id' => $requestId,
                'tool_name' => $toolName,
                'risk_level' => 'low',
                'risk_score' => 12,
                'message' => "AI Tool '{$toolName}' has been AUTO-APPROVED instantly under Fast-Path Compliance!"
            ];
        } else {
            // Route to Pending Manager Review
            $pendingRequests = Cache::get(self::PENDING_REQUESTS_KEY, []);

            $pendingRequest = [
                'id' => $requestId,
                'tool_name' => $toolName,
                'domain' => $domain,
                'requested_by' => $requestedBy,
                'department' => $department,
                'risk_level' => 'high',
                'risk_score' => ($storesCompanyData ? 40 : 0) + ($handlesPiiFinancial ? 45 : 0) + (!$enterpriseCertified ? 15 : 0),
                'survey' => [
                    'stores_company_data' => $storesCompanyData,
                    'handles_pii_financial' => $handlesPiiFinancial,
                    'enterprise_certified' => $enterpriseCertified
                ],
                'status' => 'pending_manager_review',
                'created_at' => now()->format('d M Y, H:i')
            ];

            array_unshift($pendingRequests, $pendingRequest);
            Cache::put(self::PENDING_REQUESTS_KEY, $pendingRequests, 86400);

            return [
                'status' => 'pending',
                'auto_approved' => false,
                'request_id' => $requestId,
                'tool_name' => $toolName,
                'risk_level' => 'high',
                'risk_score' => $pendingRequest['risk_score'],
                'message' => "Tool request for '{$toolName}' submitted for Manager Review due to high-risk assessment answers."
            ];
        }
    }

    public function getPendingRequests(): array
    {
        return Cache::get(self::PENDING_REQUESTS_KEY, []);
    }
}
