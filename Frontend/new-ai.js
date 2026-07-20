// AegisAI — New AI Client Chat & Governance Endpoint Integration

const API_BASE_URL = (window.BUGHUNTERS_API_URL || (
    window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? 'http://127.0.0.1:8000/api'
        : 'https://bughunters-h0w4.onrender.com/api'
)).replace(/\/$/, '');

let currentClientIp = '10.0.12.99';
let selectedTool = 'Grok 4.3';
let selectedToolApproved = true;
let currentAttachedFile = null;

let ipRestricted = false;
let approvedToolsFromBackend = [];

document.addEventListener('DOMContentLoaded', async () => {
    lucide.createIcons();
    await detectIp();
    await checkIpPolicy();

    // Check policy status every 4 seconds to react instantly when Manager approves/restricts in manager.html
    setInterval(checkIpPolicy, 4000);

    const promptInput = document.getElementById('prompt-input');
    if (promptInput) {
        promptInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                submitChatPrompt();
            }
        });
    }
});

async function detectIp() {
    try {
        const res = await fetch('https://api.ipify.org?format=json');
        if (res.ok) {
            const data = await res.json();
            currentClientIp = data.ip;
        }
    } catch (e) {
        currentClientIp = '183.171.x.x';
    }

    const ipEl = document.getElementById('chat-user-ip');
    if (ipEl) ipEl.innerText = currentClientIp;
}

async function checkIpPolicy() {
    try {
        const res = await fetch(`${API_BASE_URL}/live-detections/check-ip?ip=${encodeURIComponent(currentClientIp)}`);
        if (res.ok) {
            const data = await res.json();
            if (data.approved_tools) {
                approvedToolsFromBackend = data.approved_tools;
            }

            if (data.ip_policy_status && data.ip_policy_status.status === 'restricted') {
                ipRestricted = true;
            } else {
                ipRestricted = false;
            }

            updateIpBadge();
        }
    } catch (e) { }
}

function updateIpBadge() {
    const badge = document.getElementById('chat-ip-status-badge');
    if (!badge) return;

    if (ipRestricted) {
        badge.className = 'badge badge-danger';
        badge.innerText = '🔴 WORKSTATION IP RESTRICTED';
    } else {
        badge.className = 'badge badge-success';
        badge.innerText = '🟢 IP Clean — Scanned';
    }
}

function selectModelPill(toolName, defaultApproved, el = null) {
    selectedTool = toolName;

    if (el) {
        document.querySelectorAll('.model-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
    }

    // Check if approved in backend
    const isApprovedBackend = approvedToolsFromBackend.some(app => 
        app.toLowerCase().trim() === toolName.toLowerCase().trim() ||
        toolName.toLowerCase().includes(app.toLowerCase().replace(/\s*\([^)]*\)/g, '').trim())
    );

    selectedToolApproved = defaultApproved || isApprovedBackend;

    const outputBox = document.getElementById('response-output');
    if (outputBox) outputBox.classList.add('hidden');
}

function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        currentAttachedFile = input.files[0].name;
        const label = document.getElementById('attached-filename-label');
        if (label) {
            label.innerText = `File: ${currentAttachedFile}`;
            label.style.display = 'inline';
        }
    }
}

async function submitChatPrompt() {
    const promptInput = document.getElementById('prompt-input');
    const promptText = promptInput ? promptInput.value.trim() : '';

    if (!promptText && !currentAttachedFile) {
        return;
    }

    const outputBox = document.getElementById('response-output');
    outputBox.classList.remove('hidden');

    // 1. Check IP restriction first
    if (ipRestricted) {
        outputBox.className = 'response-output-box ip-restricted';
        outputBox.innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;">
                <i data-lucide="shield-ban" style="width:24px;height:24px;color:#ef4444;"></i>
                <strong style="font-size:1.05rem;">🚫 ACCESS RESTRICTED BY GOVERNANCE POLICY</strong>
            </div>
            <p style="margin-top:6px;font-size:0.88rem;">Your workstation IP address (<strong>${currentClientIp}</strong>) has been restricted by your manager due to policy violations.</p>
            <p style="font-size:0.8rem;color:#fca5a5;">All outgoing searches and file uploads to unapproved AI tools are currently blocked. Please contact your IT Security / Compliance officer.</p>
        `;
        lucide.createIcons();
        return;
    }

    // Determine sensitive data tags if prompt contains keywords
    const lowerPrompt = promptText.toLowerCase();
    const dataFound = [];

    if (lowerPrompt.includes('salary') || lowerPrompt.includes('ic number') || lowerPrompt.includes('payroll')) {
        dataFound.push('Employee salary data & PII');
    }
    if (lowerPrompt.includes('auth') || lowerPrompt.includes('key') || lowerPrompt.includes('secret') || lowerPrompt.includes('code')) {
        dataFound.push('Proprietary source code & Secret Keys');
    }
    if (lowerPrompt.includes('revenue') || lowerPrompt.includes('financial') || lowerPrompt.includes('earning')) {
        dataFound.push('Confidential financial report');
    }

    const fileName = currentAttachedFile || (promptText.length > 30 ? promptText.substring(0, 30) + '...' : 'search_prompt.txt');

    try {
        const response = await fetch(`${API_BASE_URL}/live-detections/scan`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ip: currentClientIp,
                tool: selectedTool,
                file: fileName,
                prompt: promptText,
                dataFound: dataFound
            })
        });

        if (response.status === 403) {
            ipRestricted = true;
            updateIpBadge();
            outputBox.className = 'response-output-box ip-restricted';
            outputBox.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="shield-ban" style="width:24px;height:24px;color:#ef4444;"></i>
                    <strong style="font-size:1.05rem;">🚫 ACCESS RESTRICTED BY GOVERNANCE POLICY</strong>
                </div>
                <p style="margin-top:6px;font-size:0.88rem;">Workstation IP address <strong>${currentClientIp}</strong> restricted by Manager.</p>
            `;
            lucide.createIcons();
            return;
        }

        const resData = await response.json();

        if (resData.status === 'blocked') {
            outputBox.className = 'response-output-box blocked';
            outputBox.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="shield-alert" style="width:24px;height:24px;color:#ff3366;"></i>
                    <strong style="font-size:1.05rem;">🚫 PRE-UPLOAD INTERCEPTED (0 Bytes Sent to AI)</strong>
                </div>
                <p style="margin-top:6px;font-size:0.88rem;">Endpoint Agent AI blocked transmission to unapproved AI tool: <strong style="color:#fff;">${selectedTool}</strong>.</p>
                <div style="font-size:0.82rem;background:rgba(6,9,14,0.6);padding:8px 12px;border-radius:6px;margin-top:4px;">
                    <span>Workstation IP: <strong>${currentClientIp}</strong> &middot; Alert logged to Manager Approval Portal</span>
                </div>
            `;
        } else {
            outputBox.className = 'response-output-box allowed';
            outputBox.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="check-circle" style="width:24px;height:24px;color:#10b981;"></i>
                    <strong style="font-size:1.05rem;">🟢 Upload / Search Cleared by Enterprise Policy</strong>
                </div>
                <p style="margin-top:6px;font-size:0.88rem;">[${selectedTool}] Processing your query safely: "${promptText || fileName}"...</p>
                <p style="font-size:0.8rem;color:#a7f3d0;margin-top:4px;">Content cleared &middot; Whitelisted AI tool &middot; Workstation IP: ${currentClientIp}</p>
            `;
        }

    } catch (e) {
        // Fallback simulation mode
        if (!selectedToolApproved || dataFound.length > 0) {
            outputBox.className = 'response-output-box blocked';
            outputBox.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="shield-alert" style="width:24px;height:24px;color:#ff3366;"></i>
                    <strong style="font-size:1.05rem;">🚫 PRE-UPLOAD INTERCEPTED (0 Bytes Sent to AI)</strong>
                </div>
                <p style="margin-top:6px;font-size:0.88rem;">Endpoint Agent AI blocked search prompt to unapproved tool: <strong>${selectedTool}</strong> (IP: ${currentClientIp}).</p>
            `;
        } else {
            outputBox.className = 'response-output-box allowed';
            outputBox.innerHTML = `
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="check-circle" style="width:24px;height:24px;color:#10b981;"></i>
                    <strong style="font-size:1.05rem;">🟢 Upload / Search Cleared</strong>
                </div>
                <p style="margin-top:6px;font-size:0.88rem;">[${selectedTool}] Processing query...</p>
            `;
        }
    }

    lucide.createIcons();

    // Reset input
    if (promptInput) promptInput.value = '';
    currentAttachedFile = null;
    const label = document.getElementById('attached-filename-label');
    if (label) label.style.display = 'none';
}
