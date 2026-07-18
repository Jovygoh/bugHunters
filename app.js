// AegisAI — Core Application Logic
// System flow: Employee uploads file → background scan → block if confidential → alert manager

// ─────────────────────────────────────────────────────────────────────
// DEPARTMENT DATA
// ─────────────────────────────────────────────────────────────────────
const departments = {
    "Engineering": {
        users: 42, uploads: 320, blocked: 0, alerts: 0,
        risk: "Low Risk", riskClass: "badge-success",
        compliance: "All uploads scanned. Approved tools only: GitHub Copilot, Claude Team, ChatGPT Enterprise.",
        tools: [
            { name: "GitHub Copilot", pct: 50, approved: true },
            { name: "Claude Team", pct: 35, approved: true },
            { name: "ChatGPT Enterprise", pct: 15, approved: true }
        ]
    },
    "Marketing": {
        users: 28, uploads: 185, blocked: 1, alerts: 1,
        risk: "Medium Risk", riskClass: "badge-warning",
        compliance: "1 upload blocked: employee attempted to upload campaign brief to unregistered AI image tool.",
        tools: [
            { name: "Midjourney (Approved)", pct: 40, approved: true },
            { name: "ChatGPT Enterprise", pct: 35, approved: true },
            { name: "PromptBase (Undefined)", pct: 25, approved: false }
        ]
    },
    "Sales": {
        users: 35, uploads: 210, blocked: 0, alerts: 0,
        risk: "Low Risk", riskClass: "badge-success",
        compliance: "No violations. Customer data masking active on all uploads.",
        tools: [
            { name: "ChatGPT Enterprise", pct: 70, approved: true },
            { name: "Claude Team", pct: 30, approved: true }
        ]
    },
    "Finance": {
        users: 15, uploads: 94, blocked: 2, alerts: 2,
        risk: "High Risk", riskClass: "badge-danger",
        compliance: "2 uploads blocked: employees uploaded raw financial spreadsheets to unapproved summarizer tools.",
        tools: [
            { name: "ChatGPT Enterprise", pct: 50, approved: true },
            { name: "PDFSummarize.ai (Undefined)", pct: 35, approved: false },
            { name: "Claude Team", pct: 15, approved: true }
        ]
    },
    "Human Resources": {
        users: 18, uploads: 72, blocked: 0, alerts: 0,
        risk: "Low Risk", riskClass: "badge-success",
        compliance: "Clean record. PII masking active. All tools whitelisted.",
        tools: [
            { name: "ChatGPT Enterprise", pct: 80, approved: true },
            { name: "Llama-3 (Local)", pct: 20, approved: true }
        ]
    }
};

// ─────────────────────────────────────────────────────────────────────
// WORKER RECORDS
// Each worker: riskLevel = "low" (approved tool, safe)
//                         "medium" (approved tool, but warning)
//                         "high"   = uses UNDEFINED AI tool → ROW TURNS RED
// ─────────────────────────────────────────────────────────────────────
let workers = [
    {
        id: "w1", name: "Alexander Wright", dept: "Engineering",
        tool: "GitHub Copilot", toolApproved: true,
        file: "api_routes.js (code refactor)",
        uploadStatus: "Allowed", riskLevel: "low", riskScore: 8,
        ip: "10.0.12.34", date: "18 Jul 2026, 08:14",
        fileType: "Source Code (.js)",
        dataFound: ["No confidential content detected"],
        prompt: "Refactor the request handler for auth token validation to use caching."
    },
    {
        id: "w2", name: "Sophia Martinez", dept: "Marketing",
        tool: "ChatGPT Enterprise", toolApproved: true,
        file: "campaign_brief.docx",
        uploadStatus: "Allowed", riskLevel: "low", riskScore: 14,
        ip: "10.0.15.89", date: "18 Jul 2026, 09:02",
        fileType: "Document (.docx)",
        dataFound: ["No confidential content detected"],
        prompt: "Summarise this campaign brief and suggest 5 social media post ideas."
    },
    {
        id: "w3", name: "Marcus Vance", dept: "Finance",
        tool: "PDFSummarize.ai", toolApproved: false,
        file: "Q2_Financial_Report.xlsx",
        uploadStatus: "Blocked — Confidential", riskLevel: "high", riskScore: 91,
        ip: "10.0.8.12", date: "18 Jul 2026, 09:47",
        fileType: "Spreadsheet (.xlsx)",
        dataFound: ["Revenue figures", "Unreleased earnings data", "Internal cost projections"],
        prompt: "Summarise the key metrics from this quarterly financial report."
    },
    {
        id: "w4", name: "Emma Watson", dept: "Sales",
        tool: "Claude Team", toolApproved: true,
        file: "client_proposal_draft.pdf",
        uploadStatus: "Allowed", riskLevel: "low", riskScore: 19,
        ip: "10.0.21.104", date: "18 Jul 2026, 10:05",
        fileType: "Document (.pdf)",
        dataFound: ["No confidential content detected"],
        prompt: "Polish the tone of this client proposal to sound more executive-facing."
    },
    {
        id: "w5", name: "David Kim", dept: "Engineering",
        tool: "Claude Team", toolApproved: true,
        file: "payment_gateway.test.js",
        uploadStatus: "Allowed", riskLevel: "low", riskScore: 11,
        ip: "10.0.12.72", date: "18 Jul 2026, 10:31",
        fileType: "Test File (.js)",
        dataFound: ["No confidential content detected"],
        prompt: "Generate unit tests for the checkout flow covering success, decline, and timeout."
    },
    {
        id: "w6", name: "Rachel Lim", dept: "Marketing",
        tool: "PromptBase.com", toolApproved: false,
        file: "brand_guidelines_v3_CONFIDENTIAL.pdf",
        uploadStatus: "Blocked — Confidential", riskLevel: "high", riskScore: 78,
        ip: "10.0.15.44", date: "18 Jul 2026, 11:18",
        fileType: "Document (.pdf)",
        dataFound: ["Confidential brand strategy", "Unreleased product roadmap", "Internal pricing tiers"],
        prompt: "Help me generate AI image prompts using our brand guidelines PDF as reference."
    },
    {
        id: "w7", name: "Jessica Taylor", dept: "Human Resources",
        tool: "ChatGPT Enterprise", toolApproved: true,
        file: "handbook_section_12.docx",
        uploadStatus: "Allowed", riskLevel: "low", riskScore: 7,
        ip: "10.0.4.55", date: "18 Jul 2026, 11:55",
        fileType: "Document (.docx)",
        dataFound: ["No confidential content detected"],
        prompt: "Rewrite this remote work policy section to be clearer and more encouraging."
    }
];

// ─────────────────────────────────────────────────────────────────────
// SIMULATION POOL — Workers that appear during live simulation
// ─────────────────────────────────────────────────────────────────────
const simulationPool = [
    {
        id: "", name: "Brian Tan", dept: "Finance",
        tool: "ChatPDF.com", toolApproved: false,
        file: "employee_salary_matrix_2026.xlsx",
        uploadStatus: "Blocked — Confidential", riskLevel: "high", riskScore: 95,
        ip: "10.0.8.77",
        fileType: "Spreadsheet (.xlsx)",
        dataFound: ["Employee salary data", "Personal IC numbers", "Bank account references"],
        prompt: "Summarise this salary matrix by department and identify the top earners."
    },
    {
        id: "", name: "Clara Ng", dept: "Engineering",
        tool: "Phind.com", toolApproved: false,
        file: "auth_service_src.zip",
        uploadStatus: "Blocked — Confidential", riskLevel: "high", riskScore: 89,
        ip: "10.0.12.55",
        fileType: "Archive (.zip)",
        dataFound: ["Proprietary source code", "API secret keys", "Database connection strings"],
        prompt: "Debug this authentication service and find any security vulnerabilities."
    },
    {
        id: "", name: "Henry Loh", dept: "Sales",
        tool: "AskAI.so", toolApproved: false,
        file: "client_list_Q3_2026.csv",
        uploadStatus: "Blocked — Confidential", riskLevel: "high", riskScore: 82,
        ip: "10.0.21.99",
        fileType: "CSV (.csv)",
        dataFound: ["Customer names and contacts", "Deal values", "Internal CRM data"],
        prompt: "Analyse this client list and suggest upselling strategies for each account."
    },
    {
        id: "", name: "Priya Nair", dept: "Human Resources",
        tool: "Writesonic.com", toolApproved: false,
        file: "performance_reviews_Q2.docx",
        uploadStatus: "Blocked — Confidential", riskLevel: "high", riskScore: 87,
        ip: "10.0.4.88",
        fileType: "Document (.docx)",
        dataFound: ["Employee performance ratings", "Personal employment notes", "Confidential HR records"],
        prompt: "Rewrite these performance reviews to sound more professionally worded."
    }
];

// ─────────────────────────────────────────────────────────────────────
// STATE
// ─────────────────────────────────────────────────────────────────────
let chart = null;
let simulationInterval = null;
let timeOffset = 0;
let totalBlocked = 0;

// ─────────────────────────────────────────────────────────────────────
// DOM REFS
// ─────────────────────────────────────────────────────────────────────
const $ = id => document.getElementById(id);

const dom = {
    tableBody:        $('worker-table-body'),
    searchInput:      $('search-workers'),
    filterSelect:     $('filter-risk'),
    toggleSim:        $('toggle-simulation'),
    btnReset:         $('btn-reset-data'),
    valMonitored:     $('val-monitored'),
    valApproved:      $('val-approved-tools'),
    valBlocked:       $('val-blocked'),
    valAlerts:        $('val-alerts'),
    cardAlerts:       $('card-alerts'),
    alertDeltaText:   $('alert-delta-text'),
    sidebarBadge:     $('sidebar-badge'),
    logContainer:     $('log-container'),

    // Inspector
    deptPlaceholder:  $('dept-placeholder'),
    deptContent:      $('dept-content'),
    inspectName:      $('inspect-name'),
    inspectRisk:      $('inspect-risk-badge'),
    inspectUsers:     $('inspect-users'),
    inspectUploads:   $('inspect-uploads'),
    inspectBlocked:   $('inspect-blocked'),
    inspectAlerts:    $('inspect-alerts'),
    inspectTools:     $('inspect-tools'),
    inspectCompliance: $('inspect-compliance'),
    inspectPolicyNote: $('inspect-policy-note'),

    // Modal
    modal:            $('risk-modal'),
    btnCloseModal:    $('btn-close-modal'),
    btnDismiss:       $('btn-modal-dismiss'),
    btnWarn:          $('btn-modal-warn'),
    btnBlock:         $('btn-modal-block'),
    modalAvatar:      $('modal-avatar'),
    modalName:        $('modal-name'),
    modalDept:        $('modal-dept'),
    modalIp:          $('modal-ip'),
    modalTool:        $('modal-tool'),
    modalFiletype:    $('modal-filetype'),
    modalDatatype:    $('modal-datatype'),
    modalRiskScore:   $('modal-risk-score'),
    modalTags:        $('modal-detected-tags')
};

// ─────────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    initChart();
    renderTable();
    updateCounters();
    bindEvents();
    bindTooltip();
    startSimulation();
    addLog("BugHunters detection engine started. Monitoring file upload channels for all AI tools.", "system");
    addLog("7 approved AI tools whitelisted. Unknown tools will trigger an alert.", "system");
});

// ─────────────────────────────────────────────────────────────────────
// EVENT BINDING
// ─────────────────────────────────────────────────────────────────────
function bindEvents() {
    dom.searchInput.addEventListener('input', renderTable);
    dom.filterSelect.addEventListener('change', renderTable);
    dom.toggleSim.addEventListener('change', e => {
        if (e.target.checked) { startSimulation(); addLog("Live simulation resumed.", "system"); }
        else { stopSimulation(); addLog("Live simulation paused.", "system"); }
    });
    dom.btnReset.addEventListener('click', resetDashboard);
    dom.btnCloseModal.addEventListener('click', closeModal);
    dom.btnDismiss.addEventListener('click', handleDismiss);
    dom.btnWarn.addEventListener('click', handleWarn);
    dom.btnBlock.addEventListener('click', handleBlock);
}

// ─────────────────────────────────────────────────────────────────────
// TOOLTIP — JS-driven positioning (avoids overflow clipping)
// ─────────────────────────────────────────────────────────────────────
function bindTooltip() {
    const card    = document.getElementById('card-blocked');
    const tooltip = document.getElementById('blocked-files-tooltip');
    if (!card || !tooltip) return;

    card.addEventListener('mouseenter', () => {
        updateBlockedTooltip();
        const rect = card.getBoundingClientRect();
        tooltip.style.display = 'block';
        // Position below the card, centred
        let left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2;
        // Clamp so it doesn't go off-screen
        left = Math.max(8, Math.min(left, window.innerWidth - tooltip.offsetWidth - 8));
        tooltip.style.left = left + 'px';
        tooltip.style.top  = (rect.bottom + window.scrollY + 10) + 'px';
    });

    card.addEventListener('mouseleave', (e) => {
        // Keep open if mouse moves into the tooltip itself
        if (!tooltip.contains(e.relatedTarget)) {
            tooltip.style.display = 'none';
        }
    });

    tooltip.addEventListener('mouseleave', () => {
        tooltip.style.display = 'none';
    });
}

// ─────────────────────────────────────────────────────────────────────
// CHART
// ─────────────────────────────────────────────────────────────────────
function initChart() {
    const ctx = document.getElementById('departmentChart').getContext('2d');

    chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(departments),
            datasets: [{
                data: [42, 28, 35, 15, 18],
                backgroundColor: ['#00f0ff', '#3b82f6', '#a855f7', '#f59e0b', '#10b981'],
                borderWidth: 2,
                borderColor: '#06090e',
                hoverOffset: 14
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#9ca3af', font: { family: 'Outfit', size: 11 }, padding: 14, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} users`
                    }
                }
            },
            onHover: (event, elements) => {
                if (elements && elements.length > 0) {
                    const label = chart.data.labels[elements[0].index];
                    showDeptDetails(label);
                }
            }
        }
    });
}

// ─────────────────────────────────────────────────────────────────────
// DEPARTMENT INSPECTOR
// ─────────────────────────────────────────────────────────────────────
function showDeptDetails(name) {
    const d = departments[name];
    if (!d) return;

    dom.deptPlaceholder.classList.add('hidden');
    dom.deptContent.classList.remove('hidden');

    dom.inspectName.innerText = name;
    dom.inspectRisk.innerText = d.risk;
    dom.inspectRisk.className = `badge ${d.riskClass}`;
    dom.inspectUsers.innerText = d.users;
    dom.inspectUploads.innerText = d.uploads;
    dom.inspectBlocked.innerText = d.blocked;
    dom.inspectAlerts.innerText = d.alerts;
    dom.inspectCompliance.innerText = d.compliance;

    // Policy note colour
    dom.inspectPolicyNote.className = d.alerts > 0 ? 'policy-note danger-note' : 'policy-note';

    // Tool progress bars
    dom.inspectTools.innerHTML = d.tools.map(t => `
        <div class="tool-progress-item">
            <div class="tool-progress-info">
                <span class="tool-name">
                    ${t.approved
                        ? `<i data-lucide="check-circle" style="width:12px;height:12px;color:#10b981;display:inline;vertical-align:middle;"></i>`
                        : `<i data-lucide="alert-triangle" style="width:12px;height:12px;color:#f59e0b;display:inline;vertical-align:middle;"></i>`
                    }
                    ${t.name}
                    ${!t.approved ? '<span class="undefined-tag">Undefined</span>' : ''}
                </span>
                <span class="tool-percentage">${t.pct}%</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill ${t.approved ? '' : 'danger'}" style="width:${t.pct}%"></div>
            </div>
        </div>
    `).join('');

    lucide.createIcons();
}

// ─────────────────────────────────────────────────────────────────────
// WORKER TABLE RENDERER
// ─────────────────────────────────────────────────────────────────────
function renderTable() {
    const query  = dom.searchInput.value.toLowerCase();
    const filter = dom.filterSelect.value;

    // Sort: high risk (undefined AI tool alerts) float to top
    const sorted = [...workers].sort((a, b) => {
        const w = { high: 3, medium: 2, low: 1 };
        return (w[b.riskLevel] || 0) - (w[a.riskLevel] || 0);
    });

    dom.tableBody.innerHTML = '';

    sorted.forEach(worker => {
        const matchQ = [worker.name, worker.dept, worker.tool, worker.file]
            .join(' ').toLowerCase().includes(query);
        const matchF = filter === 'all'
            || (filter === 'high'   && worker.riskLevel === 'high')
            || (filter === 'medium' && worker.riskLevel === 'medium')
            || (filter === 'low'    && worker.riskLevel === 'low');

        if (!matchQ || !matchF) return;

        // Status badge
        let statusClass = 'badge-success';
        if (worker.uploadStatus.startsWith('Blocked')) statusClass = 'badge-danger';
        else if (worker.uploadStatus === 'Warning') statusClass = 'badge-warning';

        // Risk score colour
        let riskClass = 'risk-low';
        if (worker.riskLevel === 'high')   riskClass = 'risk-high';
        if (worker.riskLevel === 'medium') riskClass = 'risk-med';

        // Action button
        let actionHTML = `<span class="text-muted" style="font-size:0.8rem;">Safe</span>`;
        if (worker.riskLevel === 'high') {
            actionHTML = `<button class="btn-action-danger" onclick="openModal('${worker.id}')">
                <i data-lucide="shield-alert"></i> Take Action
            </button>`;
        } else if (worker.riskLevel === 'medium') {
            actionHTML = `<button class="btn-action-warning" onclick="warnWorker('${worker.id}')">
                <i data-lucide="send"></i> Send Warning
            </button>`;
        }

        const initials = worker.name.split(' ').map(n => n[0]).join('');
        const toolApprovedIcon = worker.toolApproved
            ? `<i data-lucide="check-circle" class="tool-approved-icon"></i>`
            : `<i data-lucide="circle-x" class="tool-undefined-icon"></i>`;

        const row = document.createElement('tr');
        row.id = `row-${worker.id}`;

        // ← RED ROW for workers using undefined/unapproved AI tools
        if (worker.riskLevel === 'high') row.classList.add('threat-active');

        // Date
        const dateStr = worker.date || nowTimestamp();

        row.innerHTML = `
            <td>
                <div class="worker-identity">
                    <div class="worker-avatar ${worker.riskLevel === 'high' ? 'avatar-danger' : ''}">${initials}</div>
                    <div>
                        <div class="worker-name">${worker.name}</div>
                        <div class="worker-meta">IP: ${worker.ip}</div>
                    </div>
                </div>
            </td>
            <td>${worker.dept}</td>
            <td>
                <div class="tool-cell">
                    ${toolApprovedIcon}
                    <span class="${worker.toolApproved ? '' : 'tool-undefined'}">${worker.tool}</span>
                </div>
            </td>
            <td>
                <div class="file-cell" title="${worker.file}">
                    <i data-lucide="file" style="width:13px;height:13px;"></i>
                    ${worker.file}
                </div>
            </td>
            <td><span class="badge ${statusClass}">${worker.uploadStatus}</span></td>
            <td><span class="date-cell">${dateStr}</span></td>
            <td><span class="risk-score ${riskClass}">${worker.riskScore}/100</span></td>
            <td class="text-center">${actionHTML}</td>
        `;

        dom.tableBody.appendChild(row);
    });

    lucide.createIcons();
}

// ─────────────────────────────────────────────────────────────────────
// COUNTERS
// ─────────────────────────────────────────────────────────────────────
function updateCounters() {
    const alertCount = workers.filter(w => w.riskLevel === 'high').length;

    dom.valMonitored.innerText = 148;
    dom.valApproved.innerText = 7;
    dom.valBlocked.innerText = totalBlocked;
    dom.valAlerts.innerText = alertCount;

    if (alertCount > 0) {
        dom.cardAlerts.classList.add('active');
        dom.alertDeltaText.innerText = `${alertCount} worker${alertCount > 1 ? 's' : ''} require attention`;
        dom.sidebarBadge.innerText = alertCount;
        dom.sidebarBadge.classList.remove('hidden');
    } else {
        dom.cardAlerts.classList.remove('active');
        dom.alertDeltaText.innerText = 'No active alerts';
        dom.sidebarBadge.classList.add('hidden');
    }

    updateBlockedTooltip();
}

function updateBlockedTooltip() {
    const list = document.getElementById('blocked-files-list');
    if (!list) return;

    const blockedWorkers = workers.filter(w => w.uploadStatus.startsWith('Blocked'));

    if (blockedWorkers.length === 0) {
        list.innerHTML = '<li class="tooltip-empty">No files blocked yet</li>';
        return;
    }

    list.innerHTML = blockedWorkers.map(w => `
        <li class="tooltip-file-item">
            <div class="tooltip-file-icon"><i data-lucide="file-x"></i></div>
            <div class="tooltip-file-details">
                <span class="tooltip-file-name">${w.file}</span>
                <span class="tooltip-file-meta">${w.name} &middot; ${w.dept} &middot; <em>${w.tool}</em></span>
            </div>
        </li>
    `).join('');

    lucide.createIcons();
}

// ─────────────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────────────
function nowTimestamp() {
    const d = new Date(Date.now() + timeOffset);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const pad = n => String(n).padStart(2,'0');
    return `${pad(d.getDate())} ${months[d.getMonth()]} ${d.getFullYear()}, ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

// ─────────────────────────────────────────────────────────────────────
// LOGGER
// ─────────────────────────────────────────────────────────────────────
function getTime() {
    const d = new Date(Date.now() + timeOffset);
    const p = n => String(n).padStart(2, '0');
    return `${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
}

function addLog(msg, type = 'system') {
    const el = document.createElement('div');
    el.className = `log-entry log-${type}`;
    el.innerHTML = `<span class="log-time">${getTime()}</span><span class="log-msg">${msg}</span>`;
    dom.logContainer.appendChild(el);
    dom.logContainer.scrollTop = dom.logContainer.scrollHeight;
}

// ─────────────────────────────────────────────────────────────────────
// MODAL
// ─────────────────────────────────────────────────────────────────────
window.openModal = function(id) {
    const w = workers.find(x => x.id === id);
    if (!w) return;

    dom.modal.dataset.workerId = id;
    dom.modalAvatar.innerText = w.name.split(' ').map(n => n[0]).join('');
    dom.modalName.innerText = w.name;
    dom.modalDept.innerText = `${w.dept} Department`;
    dom.modalIp.innerText = `Workstation: ${w.ip}`;
    dom.modalTool.innerText = `${w.tool} (Unapproved / Undefined)`;
    dom.modalFiletype.innerText = w.fileType || '—';
    dom.modalDatatype.innerText = w.dataFound ? w.dataFound[0] : '—';
    dom.modalRiskScore.innerText = `${w.riskScore}/100`;

    dom.modalTags.innerHTML = (w.dataFound || []).map(tag =>
        `<span class="detected-tag">${tag}</span>`
    ).join('');

    dom.modal.classList.remove('hidden');
    lucide.createIcons();
};

function closeModal() { dom.modal.classList.add('hidden'); }

function handleDismiss() {
    const id = dom.modal.dataset.workerId;
    const w = workers.find(x => x.id === id);
    if (w) {
        addLog(`Alert dismissed for ${w.name} (${w.ip}). Monitoring continues.`, "system");
    }
    closeModal();
}

function handleWarn() {
    const id = dom.modal.dataset.workerId;
    const w = workers.find(x => x.id === id);
    if (w) {
        w.riskLevel = 'medium';
        w.uploadStatus = 'Warning Issued';
        w.riskScore = Math.max(w.riskScore - 20, 40);
        addLog(`Compliance warning sent to ${w.name} regarding use of undefined AI tool: ${w.tool}.`, "system");
        departments[w.dept] && (departments[w.dept].alerts = Math.max(0, departments[w.dept].alerts - 1));
    }
    closeModal();
    renderTable();
    updateCounters();
}

function handleBlock() {
    const id = dom.modal.dataset.workerId;
    const w = workers.find(x => x.id === id);
    if (w) {
        w.riskLevel = 'low';
        w.uploadStatus = 'Access Restricted';
        w.riskScore = 0;
        addLog(`[ACCESS RESTRICTED] Worker ${w.name} blocked from all AI tool uploads pending HR review.`, "threat");
        departments[w.dept] && (departments[w.dept].alerts = Math.max(0, departments[w.dept].alerts - 1));
    }
    closeModal();
    renderTable();
    updateCounters();
}

window.warnWorker = function(id) {
    const w = workers.find(x => x.id === id);
    if (!w) return;
    w.uploadStatus = 'Warning Issued';
    addLog(`Warning sent to ${w.name} for activity on ${w.tool}.`, "system");
    renderTable();
    updateCounters();
};

// ─────────────────────────────────────────────────────────────────────
// SIMULATION — Random safe uploads + periodic undefined AI tool alerts
// ─────────────────────────────────────────────────────────────────────
const safeActivities = [
    { dept: "Engineering",    tool: "GitHub Copilot",       approved: true,  file: "utils_helper.js",         msg: "[ALLOWED] Engineering upload to GitHub Copilot — no confidential content detected." },
    { dept: "Sales",          tool: "ChatGPT Enterprise",  approved: true,  file: "client_email_draft.docx", msg: "[ALLOWED] Sales upload to ChatGPT Enterprise — content cleared." },
    { dept: "Human Resources",tool: "ChatGPT Enterprise",  approved: true,  file: "policy_update.docx",      msg: "[ALLOWED] HR upload to ChatGPT Enterprise — no sensitive data found." },
    { dept: "Marketing",      tool: "Midjourney (Approved)",approved: true, file: "ad_banner_prompt.txt",    msg: "[ALLOWED] Marketing upload to Midjourney — safe content." },
];

function startSimulation() {
    stopSimulation();
    simulationInterval = setInterval(() => {
        timeOffset += 5000;
        const roll = Math.random();
        if (roll > 0.25) {
            simulateSafeUpload();
        } else {
            simulateUndefinedAIAlert();
        }
        updateCounters();
    }, 5000);
}

function stopSimulation() {
    if (simulationInterval) clearInterval(simulationInterval);
}

function simulateSafeUpload() {
    const act = safeActivities[Math.floor(Math.random() * safeActivities.length)];
    const d = departments[act.dept];
    if (d) d.uploads++;
    addLog(act.msg, "approved");
}

function simulateUndefinedAIAlert() {
    // Pick from pool, avoid duplicates
    const existingNames = workers.filter(w => w.riskLevel === 'high').map(w => w.name);
    const available = simulationPool.filter(p => !existingNames.includes(p.name));
    if (!available.length) { simulateSafeUpload(); return; }

    const template = available[Math.floor(Math.random() * available.length)];
    const newWorker = { ...template, id: 'sim-' + Date.now() };
    workers.unshift(newWorker);
    totalBlocked++;

    const d = departments[newWorker.dept];
    if (d) { d.blocked++; d.alerts++; d.risk = "High Risk"; d.riskClass = "badge-danger"; }

    addLog(
        `[BLOCKED] ${newWorker.name} (${newWorker.ip}) attempted to upload <strong>${newWorker.file}</strong> to undefined tool: <strong>${newWorker.tool}</strong>. Upload intercepted.`,
        "threat"
    );
    renderTable();
    updateBlockedTooltip();
}

// ─────────────────────────────────────────────────────────────────────
// RESET
// ─────────────────────────────────────────────────────────────────────
function resetDashboard() {
    workers = workers.filter(w => !w.id.startsWith('sim-'));
    workers.forEach(w => {
        if (w.riskLevel === 'high') return; // keep real high risks as-is
    });

    Object.keys(departments).forEach(k => {
        departments[k].risk = ['Marketing','Finance'].includes(k)
            ? (k === 'Finance' ? "High Risk" : "Medium Risk")
            : "Low Risk";
        departments[k].riskClass = ['Finance'].includes(k)
            ? "badge-danger"
            : (['Marketing'].includes(k) ? "badge-warning" : "badge-success");
    });

    totalBlocked = workers.filter(w => w.uploadStatus.startsWith('Blocked')).length;
    addLog("Dashboard metrics reset by administrator.", "system");
    renderTable();
    updateCounters();
}
