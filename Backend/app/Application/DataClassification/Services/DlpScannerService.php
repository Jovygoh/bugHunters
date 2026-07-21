<?php

namespace App\Application\DataClassification\Services;

class DlpScannerService
{
    /**
     * Maximum character length allowed before truncation to prevent ReDoS (Regex Denial of Service).
     */
    private const MAX_SCAN_LENGTH = 10000;

    /**
     * Regex patterns for sensitive enterprise data categories.
     */
    private array $patterns = [
        'Credit Card Number' => [
            'pattern' => '/\b(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|6(?:011|5[0-9]{2})[0-9]{12})\b/',
            'redaction' => '[REDACTED_CREDIT_CARD]',
            'risk_score' => 95
        ],
        'Email Address' => [
            'pattern' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/',
            'redaction' => '[REDACTED_EMAIL]',
            'risk_score' => 60
        ],
        'API Secret Key / Token' => [
            'pattern' => '/\b(?:sk-[a-zA-Z0-9]{20,}|AKIA[0-9A-Z]{16}|ghp_[a-zA-Z0-9]{36}|eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+)\b/',
            'redaction' => '[REDACTED_API_SECRET]',
            'risk_score' => 98
        ],
        'Database Connection URI' => [
            'pattern' => '/\b(?:postgres|mysql|mongodb(?:\+srv)?):\/\/[^\s"]+/i',
            'redaction' => '[REDACTED_DB_URI]',
            'risk_score' => 92
        ],
        'Personal Identity / SSN / NRIC' => [
            'pattern' => '/\b(?:\d{3}-\d{2}-\d{4}|[SSTFGM]\d{7}[A-Z])\b/i',
            'redaction' => '[REDACTED_PERSONAL_ID]',
            'risk_score' => 90
        ],
        'Proprietary Source Code' => [
            'pattern' => '/(?:function\s+\w+\s*\(|class\s+\w+|import\s+[\w{}]+\s+from|const\s+\w+\s*=\|def\s+\w+\s*\(|<\?php)/i',
            'redaction' => '[REDACTED_SOURCE_CODE]',
            'risk_score' => 85
        ]
    ];

    /**
     * Scan outgoing prompt/file payload for PII and sensitive data with ReDoS safeguards.
     * Zero-Storage Rule: Original user content is never stored.
     *
     * @param string $content
     * @return array
     */
    public function scan(string $content): array
    {
        // 1. ReDoS Safeguard: Truncate payload if exceeding maximum scan limit
        $truncated = false;
        if (mb_strlen($content) > self::MAX_SCAN_LENGTH) {
            $content = mb_substr($content, 0, self::MAX_SCAN_LENGTH);
            $truncated = true;
        }

        // 2. ReDoS Safeguard: Enforce strict PCRE backtrack & recursion limits
        @ini_set('pcre.backtrack_limit', '100000');
        @ini_set('pcre.recursion_limit', '100000');

        $detectedCategories = [];
        $maxRiskScore = 0;
        $maskedContent = $content;

        foreach ($this->patterns as $category => $rule) {
            $matches = [];
            $matchCount = @preg_match_all($rule['pattern'], $content, $matches);

            if ($matchCount !== false && $matchCount > 0) {
                $detectedCategories[] = $category;
                $maxRiskScore = max($maxRiskScore, $rule['risk_score']);
                $maskedContent = @preg_replace($rule['pattern'], $rule['redaction'], $maskedContent);
            }
        }

        $hasSensitiveData = !empty($detectedCategories);

        return [
            'has_sensitive_data' => $hasSensitiveData,
            'risk_score' => $hasSensitiveData ? $maxRiskScore : 10,
            'detected_categories' => $detectedCategories,
            'masked_content' => $maskedContent,
            'was_truncated_for_redos' => $truncated,
            'recommend_action' => $hasSensitiveData ? ($maxRiskScore >= 90 ? 'block' : 'mask_and_warn') : 'allow'
        ];
    }
}
