<?php

namespace Database\Seeders;

use App\Models\AiToolCatalog;
use App\Models\AiToolVendor;
use App\Models\AuditLog;
use App\Models\DashboardDailyMetric;
use App\Models\Department;
use App\Models\DiscoveryFinding;
use App\Models\Employee;
use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\Organization;
use App\Models\OrganizationAiTool;
use App\Models\Policy;
use App\Models\PolicyRule;
use App\Models\PolicyVersion;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Organization
        $org = Organization::create([
            'name' => 'Acme Enterprise Governance Corp',
            'slug' => 'acme-corp',
            'status' => 'active',
            'default_timezone' => 'UTC',
            'default_locale' => 'en',
        ]);

        // 2. Create Departments
        $depts = [
            'Engineering' => Department::create(['organization_id' => $org->id, 'name' => 'Engineering', 'code' => 'ENG', 'status' => 'active']),
            'Finance' => Department::create(['organization_id' => $org->id, 'name' => 'Finance', 'code' => 'FIN', 'status' => 'active']),
            'Marketing' => Department::create(['organization_id' => $org->id, 'name' => 'Marketing', 'code' => 'MKT', 'status' => 'active']),
            'Human Resources' => Department::create(['organization_id' => $org->id, 'name' => 'Human Resources', 'code' => 'HR', 'status' => 'active']),
            'Sales' => Department::create(['organization_id' => $org->id, 'name' => 'Sales', 'code' => 'SALES', 'status' => 'active']),
        ];

        // 3. Create System Roles
        $roles = [
            'super_admin' => Role::create(['organization_id' => $org->id, 'name' => 'Super Administrator', 'code' => 'super_admin', 'is_system' => true]),
            'security_admin' => Role::create(['organization_id' => $org->id, 'name' => 'Security Administrator', 'code' => 'security_admin', 'is_system' => true]),
            'manager' => Role::create(['organization_id' => $org->id, 'name' => 'Department Manager', 'code' => 'manager', 'is_system' => true]),
            'employee' => Role::create(['organization_id' => $org->id, 'name' => 'Standard Employee', 'code' => 'employee', 'is_system' => true]),
        ];

        // 4. Create Users & Employees with known credentials
        $userCreds = [
            [
                'name' => 'Alice Admin',
                'email' => 'admin@bughunters.io',
                'role' => 'security_admin',
                'dept' => 'Engineering',
                'title' => 'Chief Information Security Officer',
            ],
            [
                'name' => 'Clara Compliance',
                'email' => 'compliance@bughunters.io',
                'role' => 'security_admin',
                'dept' => 'Human Resources',
                'title' => 'Compliance Lead Officer',
            ],
            [
                'name' => 'Marcus Manager',
                'email' => 'manager@bughunters.io',
                'role' => 'manager',
                'dept' => 'Engineering',
                'title' => 'VP of Engineering',
            ],
            [
                'name' => 'Evan Employee',
                'email' => 'employee@bughunters.io',
                'role' => 'employee',
                'dept' => 'Engineering',
                'title' => 'Software Engineer',
            ],
        ];

        $users = [];
        $employees = [];

        foreach ($userCreds as $u) {
            $user = User::create([
                'organization_id' => $org->id,
                'name' => $u['name'],
                'email' => $u['email'],
                'normalized_email' => strtolower($u['email']),
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $roles[$u['role']]->id,
                'organization_id' => $org->id,
            ]);

            $emp = Employee::create([
                'organization_id' => $org->id,
                'user_id' => $user->id,
                'department_id' => $depts[$u['dept']]->id,
                'employee_number' => 'EMP-' . rand(1000, 9999),
                'first_name' => explode(' ', $u['name'])[0],
                'last_name' => explode(' ', $u['name'])[1] ?? 'User',
                'display_name' => $u['name'],
                'work_email' => $u['email'],
                'normalized_work_email' => strtolower($u['email']),
                'job_title' => $u['title'],
                'employment_type' => 'full_time',
                'status' => 'active',
                'risk_level' => 'low',
                'hired_at' => now()->subYears(2),
            ]);

            $users[$u['email']] = $user;
            $employees[$u['email']] = $emp;
        }

        // Add 10 additional demo employees across departments
        $extraNames = [
            ['Brian', 'Tan', 'Finance', 'Financial Analyst'],
            ['Priya', 'Nair', 'Human Resources', 'HR Specialist'],
            ['Henry', 'Loh', 'Sales', 'Account Executive'],
            ['Rachel', 'Lim', 'Marketing', 'Content Strategist'],
            ['David', 'Wong', 'Engineering', 'Backend Developer'],
            ['Samantha', 'Chen', 'Engineering', 'Frontend Developer'],
            ['Kevin', 'Vance', 'Finance', 'Risk Auditor'],
            ['Laura', 'Smith', 'Marketing', 'SEO Lead'],
            ['Daniel', 'Kim', 'Sales', 'Sales Development Rep'],
            ['Jessica', 'Taylor', 'Engineering', 'DevOps Specialist'],
        ];

        foreach ($extraNames as $idx => $n) {
            $email = strtolower($n[0] . '.' . $n[1]) . '@bughunters.io';
            $user = User::create([
                'organization_id' => $org->id,
                'name' => $n[0] . ' ' . $n[1],
                'email' => $email,
                'normalized_email' => strtolower($email),
                'password' => Hash::make('password123'),
                'status' => 'active',
            ]);

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $roles['employee']->id,
                'organization_id' => $org->id,
            ]);

            Employee::create([
                'organization_id' => $org->id,
                'user_id' => $user->id,
                'department_id' => $depts[$n[2]]->id,
                'employee_number' => 'EMP-' . (2000 + $idx),
                'first_name' => $n[0],
                'last_name' => $n[1],
                'display_name' => $n[0] . ' ' . $n[1],
                'work_email' => $email,
                'normalized_work_email' => strtolower($email),
                'job_title' => $n[3],
                'employment_type' => 'full_time',
                'status' => 'active',
                'risk_level' => $idx % 3 === 0 ? 'medium' : 'low',
                'hired_at' => now()->subMonths(6 + $idx),
            ]);
        }

        // 5. Seed Vendors and AI Tool Catalog
        $vendorOpenAI = AiToolVendor::create(['name' => 'OpenAI Inc.', 'normalized_name' => 'openai inc.', 'website_url' => 'https://openai.com']);
        $vendorGitHub = AiToolVendor::create(['name' => 'GitHub Inc.', 'normalized_name' => 'github inc.', 'website_url' => 'https://github.com']);
        $vendorAnthropic = AiToolVendor::create(['name' => 'Anthropic PBC', 'normalized_name' => 'anthropic pbc', 'website_url' => 'https://anthropic.com']);
        $vendorShadow = AiToolVendor::create(['name' => 'Shadow AI Web Services', 'normalized_name' => 'shadow ai web services', 'website_url' => 'https://unapproved.ai']);

        $toolsData = [
            // Approved Tools
            ['name' => 'ChatGPT Enterprise', 'vendor' => $vendorOpenAI, 'domain' => 'chatgpt.com', 'category' => 'LLM Chatbot', 'approved' => true, 'status' => 'approved', 'risk' => 'low'],
            ['name' => 'GitHub Copilot', 'vendor' => $vendorGitHub, 'domain' => 'copilot.github.com', 'category' => 'Code Assistant', 'approved' => true, 'status' => 'approved', 'risk' => 'low'],
            ['name' => 'Claude Team', 'vendor' => $vendorAnthropic, 'domain' => 'claude.ai', 'category' => 'LLM Chatbot', 'approved' => true, 'status' => 'approved', 'risk' => 'low'],
            ['name' => 'Gemini 3.1 Pro', 'vendor' => $vendorOpenAI, 'domain' => 'gemini.google.com', 'category' => 'LLM Chatbot', 'approved' => true, 'status' => 'approved', 'risk' => 'low'],

            // Unapproved Shadow AI Tools
            ['name' => 'Phind.com', 'vendor' => $vendorShadow, 'domain' => 'phind.com', 'category' => 'Code Search', 'approved' => false, 'status' => 'unapproved', 'risk' => 'high'],
            ['name' => 'ChatPDF.com', 'vendor' => $vendorShadow, 'domain' => 'chatpdf.com', 'category' => 'Document Analyzer', 'approved' => false, 'status' => 'unapproved', 'risk' => 'high'],
            ['name' => 'Writesonic.com', 'vendor' => $vendorShadow, 'domain' => 'writesonic.com', 'category' => 'Content Generator', 'approved' => false, 'status' => 'unapproved', 'risk' => 'high'],
            ['name' => 'AskAI.so', 'vendor' => $vendorShadow, 'domain' => 'askai.so', 'category' => 'Knowledge Base', 'approved' => false, 'status' => 'unapproved', 'risk' => 'high'],
        ];

        $orgAiTools = [];

        foreach ($toolsData as $td) {
            $catalog = AiToolCatalog::create([
                'vendor_id' => $td['vendor']->id,
                'name' => $td['name'],
                'slug' => Str::slug($td['name']),
                'category' => $td['category'],
                'delivery_model' => 'saas',
                'default_risk_level' => $td['risk'],
                'stores_prompts' => !$td['approved'],
                'trains_on_customer_data' => !$td['approved'],
                'supports_enterprise_controls' => $td['approved'],
            ]);

            $orgTool = OrganizationAiTool::create([
                'organization_id' => $org->id,
                'catalog_ai_tool_id' => $catalog->id,
                'display_name' => $td['name'],
                'primary_domain' => $td['domain'],
                'category' => $td['category'],
                'status' => 'active',
                'approval_status' => $td['status'],
                'risk_level' => $td['risk'],
                'risk_score' => $td['approved'] ? 15.00 : 88.50,
                'approved_at' => $td['approved'] ? now()->subMonths(3) : null,
                'blocked_at' => !$td['approved'] ? now()->subDays(5) : null,
            ]);

            $orgAiTools[$td['name']] = $orgTool;
        }

        // 6. Seed Policies
        $policyDlp = Policy::create([
            'organization_id' => $org->id,
            'name' => 'Enterprise Data Loss Prevention (DLP) & PII Protection',
            'code' => 'POL-DLP-001',
            'description' => 'Enforces automatic interception and redaction of PII, financial data, and secret keys.',
            'category' => 'data_loss_prevention',
            'status' => 'published',
            'priority' => 100,
            'is_mandatory' => true,
            'default_effect' => 'block',
            'created_by' => $users['admin@bughunters.io']->id,
        ]);

        $policyVersion = PolicyVersion::create([
            'organization_id' => $org->id,
            'policy_id' => $policyDlp->id,
            'version_number' => '1.0.0',
            'status' => 'published',
            'definition' => ['rules' => ['block_pii' => true]],
            'definition_hash' => hash('sha256', json_encode(['rules' => ['block_pii' => true]])),
            'published_at' => now()->subMonths(1),
            'published_by' => $users['admin@bughunters.io']->id,
        ]);

        $policyDlp->update(['active_version_id' => $policyVersion->id]);

        PolicyRule::create([
            'organization_id' => $org->id,
            'policy_version_id' => $policyVersion->id,
            'name' => 'Intercept PII and Financial Payload',
            'sequence' => 1,
            'effect' => 'block',
            'reason_code' => 'DLP_PII_BLOCK',
            'is_enabled' => true,
        ]);

        $policyShadow = Policy::create([
            'organization_id' => $org->id,
            'name' => 'Unapproved Shadow AI Interception',
            'code' => 'POL-SAI-002',
            'description' => 'Blocks outbound HTTP requests and prompt submissions to unverified shadow AI tools.',
            'category' => 'shadow_ai',
            'status' => 'published',
            'priority' => 90,
            'is_mandatory' => true,
            'default_effect' => 'block',
            'created_by' => $users['admin@bughunters.io']->id,
        ]);

        // 7. Seed Discovery Findings & Incidents
        $findingPhind = DiscoveryFinding::create([
            'organization_id' => $org->id,
            'organization_ai_tool_id' => $orgAiTools['Phind.com']->id,
            'title' => 'Unapproved Shadow AI Detected: Phind.com',
            'detected_domain' => 'phind.com',
            'finding_type' => 'unapproved_shadow_ai',
            'severity' => 'high',
            'status' => 'open',
            'risk_score' => 89.00,
            'first_observed_at' => now()->subDays(2),
            'last_observed_at' => now(),
        ]);

        $incident1 = Incident::create([
            'organization_id' => $org->id,
            'incident_number' => 1001,
            'title' => 'Unapproved Shadow AI Interception: Phind.com',
            'description' => 'Employee attempted outbound prompt search containing source code snippet to unapproved tool Phind.com.',
            'incident_type' => 'shadow_ai_usage',
            'severity' => 'high',
            'status' => 'under_review',
            'priority' => 10,
            'source' => 'dlp_agent',
            'employee_id' => $employees['employee@bughunters.io']->id,
            'organization_ai_tool_id' => $orgAiTools['Phind.com']->id,
            'policy_id' => $policyShadow->id,
            'discovery_finding_id' => $findingPhind->id,
            'assigned_to' => $users['compliance@bughunters.io']->id,
            'reported_by' => $users['admin@bughunters.io']->id,
            'action' => 'blocked',
            'risk_score' => 89.00,
            'detected_at' => now()->subHours(4),
        ]);

        // Add Redressal Appeal Comment to Incident 1
        IncidentComment::create([
            'organization_id' => $org->id,
            'incident_id' => $incident1->id,
            'author_user_id' => $users['employee@bughunters.io']->id,
            'body' => 'Security Redressal Appeal: Requesting temporary exception to use Phind for debugging legacy codebase.',
            'visibility' => 'public',
        ]);

        IncidentComment::create([
            'organization_id' => $org->id,
            'incident_id' => $incident1->id,
            'author_user_id' => $users['compliance@bughunters.io']->id,
            'body' => 'Under compliance review. Advised employee to use approved GitHub Copilot instance instead.',
            'visibility' => 'internal',
        ]);

        $incident2 = Incident::create([
            'organization_id' => $org->id,
            'incident_number' => 1002,
            'title' => 'Sensitive Salary Matrix Intercepted: ChatPDF.com',
            'description' => 'DLP Scanner intercepted attempt to upload employee_salary_matrix_2026.xlsx to ChatPDF.com.',
            'incident_type' => 'dlp_violation',
            'severity' => 'critical',
            'status' => 'open',
            'priority' => 1,
            'source' => 'dlp_agent',
            'employee_id' => $employees['employee@bughunters.io']->id,
            'organization_ai_tool_id' => $orgAiTools['ChatPDF.com']->id,
            'policy_id' => $policyDlp->id,
            'action' => 'blocked',
            'risk_score' => 95.00,
            'detected_at' => now()->subHours(1),
        ]);

        // 8. Seed Audit Logs & Dashboard Metrics
        for ($i = 0; $i < 10; $i++) {
            AuditLog::create([
                'organization_id' => $org->id,
                'actor_user_id' => $users['admin@bughunters.io']->id,
                'actor_type' => 'user',
                'actor_identifier' => 'admin@bughunters.io',
                'action' => 'policy.evaluate',
                'module' => 'security',
                'auditable_type' => 'App\Models\Incident',
                'auditable_id' => $incident1->id,
                'outcome' => 'blocked',
                'description' => 'Intercepted prompt upload to unapproved AI tool.',
                'source' => 'system',
                'ip_address' => '127.0.0.1',
                'occurred_at' => now()->subHours($i * 2),
            ]);
        }

        $surgeInterceptions = [3, 5, 8, 24, 42, 68, 85];
        for ($d = 6; $d >= 0; $d--) {
            DashboardDailyMetric::create([
                'organization_id' => $org->id,
                'metric_date' => now()->subDays($d)->format('Y-m-d'),
                'active_employees' => 14,
                'registered_devices' => 18,
                'noncompliant_devices' => 2,
                'discovered_ai_tools' => 8,
                'blocked_ai_tools' => 4,
                'policy_evaluations' => 140 + (6 - $d) * 10,
                'blocked_evaluations' => $surgeInterceptions[6 - $d],
                'open_incidents' => 5,
                'critical_incidents' => 2,
                'risk_score' => 42.50,
            ]);
        }
    }
}
