<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;
$pageTitle = "Insecure Direct Object Reference (IDOR) - Complete Guide | DarkHunter";
$currentPage = "idor-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master IDOR vulnerabilities - Understanding insecure direct object references and implementing robust access controls. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/idor-info.css?v=1.1">

</head>

<body>
  <div class="grid-bg"></div>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <!-- Mobile Menu Button -->
  <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

  <div class="app-container">
    <a href="/DarkHunter/Public/Learning.php" class="modern-back-btn">
      <i>←</i>
      <span>Back to Modules</span>
    </a>
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          👾 <span>DARK</span>HUNTER
        </div>
      </div>

      <div class="nav-section">
        <div class="nav-title">Navigation</div>
        <ul class="nav-links">
          <li>
            <a href="#overview" class="active">
              <i>📚</i> Overview
            </a>
          </li>
          <li>
            <a href="#mechanism">
              <i>⚙️</i> How It Works
            </a>
          </li>
          <li>
            <a href="#exploitation">
              <i>🎯</i> Exploitation Steps
            </a>
          </li>
          <li>
            <a href="#impact">
              <i>💥</i> Real-World Impact
            </a>
          </li>
          <li>
            <a href="#labs">
              <i>💻</i> Code Labs
            </a>
          </li>
          <li>
            <a href="#bypass">
              <i>🚧</i> Bypass Techniques
            </a>
          </li>
          <li>
            <a href="#mitigation">
              <i>🛡️</i> Prevention
            </a>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <div class="nav-title">Related Modules</div>
        <ul class="nav-links">
          <li>
            <a href="/DarkHunter/learningBugs/xss-info.php">
              <i>💻</i> XSS
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/sqli-info.php">
              <i>🗃️</i> SQL Injection
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/csrf-info.php">
              <i>🧬</i> CSRF
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/ssrf-info.php">
              <i>🌐</i> SSRF
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/ssti-info.php">
              <i>🧪</i> SSTI
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/cors-info.php">
              <i>🔗</i> CORS
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/file-upload-info.php">
              <i>📤</i> File Upload
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/cache-poisoning-info.php">
              <i>🧃</i> Cache Poisoning
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/host-header-info.php">
              <i>🖥️</i> Host Header Injection
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/oauth-info.php">
              <i>🔑</i> OAUTH
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/http-smuggling-info.php">
              <i>📦</i> HTTP Smuggling
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/html-injection-info.php">
              <i>📝</i> HTML Injection
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/lfi-info.php">
              <i>📁</i> LFI
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/jwt-info.php">
              <i>🎫</i> JWT
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/open-redirect-info.php">
              <i>↪️</i> Open Redirect
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/rce-info.php">
              <i>💻</i> RCE
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/race-condition-info.php">
              <i>⚡</i> Race Condition
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Insecure Direct Object Reference (IDOR)</h1>
        <p class="page-subtitle">
          Master the art of identifying and exploiting insecure direct object references. Learn how attackers manipulate
          object identifiers to access unauthorized data and how to implement robust access controls.
        </p>
      </div>

      <!-- Table of Contents -->
      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is IDOR?</a></li>
            <li><a href="#mechanism">2. Technical Mechanism</a></li>
            <li><a href="#exploitation">3. Exploitation Steps</a></li>
            <li><a href="#impact">4. Real-World Impact</a></li>
            <li><a href="#labs">5. Code Labs: Vulnerable vs Secure</a></li>
            <li><a href="#bypass">6. Bypass Techniques</a></li>
            <li><a href="#mitigation">7. Prevention Checklist</a></li>
          </ul>
        </div>
      </div>

      <!-- Section 1: Overview -->
      <div id="overview" class="content-card">
        <h2 class="card-title">
          <i>📚</i> What is Insecure Direct Object Reference (IDOR)?
        </h2>

        <div class="highlight-box">
          <strong>Definition:</strong> IDOR (Insecure Direct Object Reference) is an access control vulnerability
          that occurs when an application exposes a direct reference to an internal implementation object (such as a
          database key, file, or directory) without proper authorization checks. Attackers can manipulate these
          references to access unauthorized data.
        </div>

        <p class="text-content">
          IDOR is one of the most common and dangerous vulnerabilities in modern web applications. It stems from the
          assumption that users will only access objects through the intended UI flow, ignoring the fact that attackers
          can directly manipulate parameters to access other objects. Unlike SQL Injection which exploits query
          construction, IDOR exploits <strong>missing or broken authorization logic</strong>.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> IDOR can lead to unauthorized access to sensitive data (PII, financial
          records, medical data), account takeover, privilege escalation, data modification/deletion, and complete
          compromise of user accounts. It is listed in the OWASP Top 10 under "Broken Access Control" (A01:2021).
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 5.3 - 8.6 (Medium to High)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low (often just changing a number)</li>
            <li><strong>Privileges Required:</strong> Low (any authenticated user)</li>
            <li><strong>User Interaction:</strong> None (direct API manipulation)</li>
            <li><strong>Scope:</strong> Unchanged (affects vulnerable application)</li>
            <li><strong>Impact:</strong> High on Confidentiality and Integrity</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of IDOR Vulnerabilities</h3>
        <p class="text-content">
          IDOR manifests in various forms depending on the object type being referenced:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Description</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Example</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Database Records</td>
              <td style="padding: 0.75rem;">Direct reference to database IDs</td>
              <td style="padding: 0.75rem;"><code>/api/users/123/profile</code></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Files/Directories</td>
              <td style="padding: 0.75rem;">Direct file path exposure</td>
              <td style="padding: 0.75rem;"><code>/download?file=report_2024.pdf</code></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Function/Methods</td>
              <td style="padding: 0.75rem;">Direct function invocation</td>
              <td style="padding: 0.75rem;"><code>/admin/deleteUser?id=456</code></td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Reference Objects</td>
              <td style="padding: 0.75rem;">Indirect object references</td>
              <td style="padding: 0.75rem;"><code>/invoice?ref=INV-2024-001</code></td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 IDOR Attack Flow Diagram</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: User Login → Access Own Resource → Modify ID Parameter → Access Others' Data]
          </div>
        </div>
      </div>

      <!-- Section 2: Mechanism -->
      <div id="mechanism" class="content-card">
        <h2 class="card-title">
          <i>⚙️</i> How IDOR Works: Technical Deep Dive
        </h2>

        <h3 class="subsection-title">The Root Cause: Missing Authorization</h3>
        <p class="text-content">
          IDOR vulnerabilities exist when applications use direct object references (like database primary keys) in
          URLs or parameters but fail to verify if the authenticated user actually owns or has permission to access
          the requested object.
        </p>

        <div class="highlight-box">
          <strong>Vulnerability Pattern:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Application uses predictable identifiers (sequential integers, UUIDs)</li>
            <li>User requests resource using identifier (e.g., <code>/invoice?id=1001</code>)</li>
            <li>Application retrieves object based on ID alone</li>
            <li><strong>Missing Step:</strong> Verify ownership/permissions</li>
            <li>Data is returned regardless of who requested it</li>
          </ol>
        </div>

        <h3 class="subsection-title">Common IDOR Patterns</h3>

        <h4>1. URL Parameter Manipulation</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Vulnerable URL Structure</span>
          </div>
          <pre><code><span class="code-comment">-- User accesses their own profile</span>
<span class="code-string">GET /profile.php?user_id=42</span>

<span class="code-comment">-- Attacker changes user_id to access another user's data</span>
<span class="code-string">GET /profile.php?user_id=43</span>  <span class="code-comment">-- Accesses user 43's data!</span>

<span class="code-comment">-- Common patterns to test</span>
<span class="code-string">/api/users/1</span> → <span class="code-string">/api/users/2</span>
<span class="code-string">/documents?id=100</span> → <span class="code-string">/documents?id=101</span>
<span class="code-string">/download?file=user_123_report.pdf</span> → <span class="code-string">/download?file=user_124_report.pdf</span></code></pre>
        </div>

        <h4>2. POST Body Manipulation</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">POST Request IDOR</span>
          </div>
          <pre><code><span class="code-comment">-- Legitimate request to update own profile</span>
<span class="code-keyword">POST</span> /api/update-profile <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Content-Type</span>: <span class="code-string">application/json</span>

{
    <span class="code-attr">"user_id"</span>: <span class="code-string">42</span>,
    <span class="code-attr">"email"</span>: <span class="code-string">"attacker@evil.com"</span>
}

<span class="code-comment">-- Attacker changes user_id to takeover another account</span>
{
    <span class="code-attr">"user_id"</span>: <span class="code-string">43</span>,  <span class="code-comment">-- Target victim</span>
    <span class="code-attr">"email"</span>: <span class="code-string">"attacker@evil.com"</span>  <span class="code-comment">-- Attacker's email</span>
}</code></pre>
        </div>

        <h4>3. Mass Assignment IDOR</h4>
        <p class="text-content">
          Occurs when applications automatically bind client-provided data (e.g., JSON) to internal objects without
          filtering sensitive fields like <code>role</code>, <code>is_admin</code>, or <code>user_id</code>.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Mass Assignment Attack</span>
          </div>
          <pre><code><span class="code-comment">-- Normal registration</span>
<span class="code-keyword">POST</span> /api/register <span class="code-keyword">HTTP/1.1</span>
{
    <span class="code-attr">"username"</span>: <span class="code-string">"john"</span>,
    <span class="code-attr">"password"</span>: <span class="code-string">"secret123"</span>
}

<span class="code-comment">-- Attacker adds admin field</span>
{
    <span class="code-attr">"username"</span>: <span class="code-string">"attacker"</span>,
    <span class="code-attr">"password"</span>: <span class="code-string">"secret123"</span>,
    <span class="code-attr">"role"</span>: <span class="code-string">"admin"</span>,  <span class="code-comment">-- Privilege escalation!</span>
    <span class="code-attr">"is_verified"</span>: <span class="code-keyword">true</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">IDOR in Modern APIs</h3>
        <p class="text-content">
          RESTful APIs are particularly susceptible to IDOR due to their resource-oriented nature and predictable
          URL patterns.
        </p>

        <div class="highlight-box">
          <strong>REST API IDOR Examples:</strong>
          <ul style="margin-left: 2rem;">
            <li><code>GET /api/v1/users/{id}/orders</code> - View other users' orders</li>
            <li><code>PUT /api/v1/users/{id}/address</code> - Modify another user's address</li>
            <li><code>DELETE /api/v1/posts/{id}</code> - Delete others' posts</li>
            <li><code>GET /api/v1/invoices/{id}/download</code> - Download any invoice</li>
          </ul>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon victim">👤</div>
            <div class="flow-label">User Login</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Authenticates as user ID 42</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">📄</div>
            <div class="flow-label">Access Resource</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">GET /profile?id=42</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">✏️</div>
            <div class="flow-label">Modify ID</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Change to id=43</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🔓</div>
            <div class="flow-label">No Auth Check</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Server returns data</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">💰</div>
            <div class="flow-label">Data Exposed</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">User 43's data leaked</p>
          </div>
        </div>
      </div>

      <!-- Section 3: Exploitation -->
      <div id="exploitation" class="content-card">
        <h2 class="card-title">
          <i>🎯</i> Exploitation Steps: Finding and Exploiting IDOR
        </h2>

        <h3 class="subsection-title">Step 1: Map the Application</h3>
        <p class="text-content">
          Identify all endpoints that accept object identifiers. Use tools like Burp Suite, OWASP ZAP, or browser
          DevTools to capture and analyze all API requests.
        </p>

        <div class="highlight-box">
          <strong>Look For:</strong>
          <ul style="margin-left: 2rem;">
            <li>Sequential numeric IDs in URLs (<code>/user/1</code>, <code>/user/2</code>)</li>
            <li>GUID/UUID parameters that might be predictable</li>
            <li>File names and paths in download/view endpoints</li>
            <li>Hidden form fields containing object IDs</li>
            <li>JSON/XML parameters in API requests</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Identify Predictable Patterns</h3>
        <p class="text-content">
          Analyze the identifiers to determine if they follow predictable patterns:
        </p>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">ID Pattern Analysis</span>
          </div>
          <pre><code><span class="code-comment">-- Sequential IDs (Easiest to exploit)</span>
<span class="code-string">/api/orders/1001</span>
<span class="code-string">/api/orders/1002</span>
<span class="code-string">/api/orders/1003</span>

<span class="code-comment">-- Timestamp-based (Predictable if time-based)</span>
<span class="code-string">/files/doc_1640995200.pdf</span>  <span class="code-comment">-- Unix timestamp</span>

<span class="code-comment">-- Hash-based (Harder but sometimes reversible)</span>
<span class="code-string">/download?id=a1b2c3d4e5f6</span>  <span class="code-comment">-- MD5 hash of sequential ID?</span>

<span class="code-comment">-- Encoded (Base64, URL encoding, etc.)</span>
<span class="code-string">/view?doc=MTAwMQ==</span>  <span class="code-comment">-- Base64 for "1001"</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Automated IDOR Discovery</h3>
        <p class="text-content">
          Use Burp Suite Intruder or custom scripts to automate testing of ID ranges:
        </p>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Burp Suite Intruder Setup</span>
          </div>
          <pre><code><span class="code-comment">-- Target: /api/users/§id§/profile</span>
<span class="code-comment">-- Positions: Mark 'id' as insertion point</span>
<span class="code-comment">-- Payloads: Numbers 1-1000</span>

<span class="code-comment">-- Attack Results Analysis:</span>
<span class="code-comment">-- Status 200 + Content-Length change = Valid ID</span>
<span class="code-comment">-- Status 403/404 = Invalid/Unauthorized</span>
<span class="code-comment">-- Status 500 = Potential server error (might indicate valid but broken access)</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Manual Verification</h3>
        <p class="text-content">
          For each potentially vulnerable endpoint, manually verify IDOR by:
        </p>

        <ol class="text-content">
          <li>Create two test accounts (User A and User B)</li>
          <li>As User A, access a resource and note the ID</li>
          <li>Logout and login as User B</li>
          <li>Attempt to access User A's resource using the noted ID</li>
          <li>If successful, vulnerability confirmed</li>
        </ol>

        <h3 class="subsection-title">Step 5: Advanced Exploitation</h3>

        <h4>Parameter Pollution</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">HTTP Parameter Pollution</span>
          </div>
          <pre><code><span class="code-comment">-- Some frameworks parse multiple parameters differently</span>
<span class="code-string">GET /transfer?from=123&to=456&amount=100</span>

<span class="code-comment">-- Attacker adds second 'from' parameter</span>
<span class="code-string">GET /transfer?from=123&to=456&amount=100&from=999</span>
<span class="code-comment">-- Backend might use last 'from' value (999) instead of authenticated user</span></code></pre>
        </div>

        <h4>Method Switching</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">HTTP Method Tampering</span>
          </div>
          <pre><code><span class="code-comment">-- GET might be protected but POST/PUT/DELETE not</span>
<span class="code-keyword">GET</span> /api/admin/users <span class="code-keyword">HTTP/1.1</span>
<span class="code-comment">-- Returns: 403 Forbidden</span>

<span class="code-keyword">POST</span> /api/admin/users <span class="code-keyword">HTTP/1.1</span>
<span class="code-comment">-- Returns: 200 OK with user list!</span></code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: IDOR Exploitation with Burp Suite</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step IDOR exploitation using Burp Suite Intruder]
          </div>
        </div>
      </div>

      <!-- Section 4: Impact -->
      <div id="impact" class="content-card">
        <h2 class="card-title">
          <i>💥</i> Real-World Impact: Notorious IDOR Breaches
        </h2>

        <h3 class="subsection-title">Case Study 1: Facebook View As Feature (2018)</h3>
        <p class="text-content">
          A critical IDOR vulnerability in Facebook's "View As" feature allowed attackers to steal access tokens by
          manipulating the video upload ID parameter. This affected 50 million accounts and was one of the largest
          security breaches in Facebook's history.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> 50 million accounts compromised, complete account takeover possible, forced
          logout of 90 million users as precautionary measure.
        </div>

        <h3 class="subsection-title">Case Study 2: Uber Account Takeover (2014)</h3>
        <p class="text-content">
          Researchers discovered that Uber's API allowed any authenticated user to access any other user's trip
          history and personal information by simply changing the user UUID in the API endpoint. The UUIDs were
          not sufficiently random and could be enumerated.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Attacker creates account → Extracts UUID pattern → Enumerates other UUIDs
          → Accesses complete trip history and personal data of any Uber user.
        </div>

        <h3 class="subsection-title">Case Study 3: HackerOne IDOR on HackerOne (2019)</h3>
        <p class="text-content">
          Ironically, the bug bounty platform HackerOne itself had an IDOR vulnerability that allowed researchers
          to view other users' private reports by manipulating the report ID in the API endpoint. This exposed
          sensitive vulnerability reports from other companies.
        </p>
        <div class="highlight-box">
          <strong>Financial Impact:</strong> $20,000 bounty paid, potential exposure of thousands of undisclosed
          security vulnerabilities affecting major corporations.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">IDOR Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Banking/Finance</td>
              <td style="padding: 0.75rem;">Access other customers' statements, modify transfer recipients</td>
              <td style="padding: 0.75rem;">Financial theft, regulatory violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Healthcare</td>
              <td style="padding: 0.75rem;">View patient records by changing medical record ID</td>
              <td style="padding: 0.75rem;">HIPAA violations, privacy breach</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Access order details, modify shipping addresses</td>
              <td style="padding: 0.75rem;">Theft, fraud, data harvesting</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Social Media</td>
              <td style="padding: 0.75rem;">Access private messages, delete others' content</td>
              <td style="padding: 0.75rem;">Privacy violation, harassment</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">SaaS/Enterprise</td>
              <td style="padding: 0.75rem;">Access other tenants' data (multi-tenant bypass)</td>
              <td style="padding: 0.75rem;">Complete data breach, business compromise</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Section 5: Labs -->
      <div id="labs" class="content-card">
        <h2 class="card-title">
          <i>💻</i> Code Labs: Vulnerable vs Secure Implementation
        </h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how missing authorization checks enable IDOR attacks, then
          implement proper ownership verification and access controls.
        </div>

        <h3 class="subsection-title">Lab 1: Basic IDOR in Profile View</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct database query using user-provided ID without ownership check.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: No authorization check!</span>
<span class="code-keyword">session_start</span>();
<span class="code-keyword">$user_id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];  <span class="code-comment">// Directly from user input</span>

<span class="code-comment">// DANGEROUS: No verification if current user owns this profile</span>
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM users WHERE id = ?"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$user_id</span>]);
<span class="code-keyword">$user</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();

<span class="code-keyword">echo</span> <span class="code-string">"Name: "</span> . <span class="code-keyword">$user</span>[<span class="code-string">'name'</span>];
<span class="code-keyword">echo</span> <span class="code-string">"Email: "</span> . <span class="code-keyword">$user</span>[<span class="code-string">'email'</span>];
<span class="code-keyword">echo</span> <span class="code-string">"SSN: "</span> . <span class="code-keyword">$user</span>[<span class="code-string">'ssn'</span>];  <span class="code-comment">// Sensitive data exposed!</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Implementation</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">session_start</span>();
<span class="code-keyword">$requested_id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];
<span class="code-keyword">$current_user_id</span> = <span class="code-keyword">$_SESSION</span>[<span class="code-string">'user_id'</span>];

<span class="code-comment">// CRITICAL: Verify ownership before accessing data</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$requested_id</span> != <span class="code-keyword">$current_user_id</span>) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
    <span class="code-function">die</span>(<span class="code-string">"Access denied: You can only view your own profile"</span>);
}

<span class="code-comment">// Additional check: Verify in database query</span>
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM users WHERE id = ? AND id = ?"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$requested_id</span>, <span class="code-keyword">$current_user_id</span>]);
<span class="code-keyword">$user</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();

<span class="code-keyword">if</span> (!<span class="code-keyword">$user</span>) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">404</span>);
    <span class="code-function">die</span>(<span class="code-string">"User not found"</span>);
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: IDOR in File Download</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct file access without checking if user owns the file.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable File Download</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct file path from user input</span>
<span class="code-keyword">$filename</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'file'</span>];
<span class="code-keyword">$filepath</span> = <span class="code-string">"/var/www/uploads/"</span> . <span class="code-keyword">$filename</span>;

<span class="code-comment">// No check if current user should access this file</span>
<span class="code-keyword">if</span> (<span class="code-function">file_exists</span>(<span class="code-keyword">$filepath</span>)) {
    <span class="code-function">readfile</span>(<span class="code-keyword">$filepath</span>);  <span class="code-comment">// Serves any file!</span>
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure File Download</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">session_start</span>();
<span class="code-keyword">$file_id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];  <span class="code-comment">// Use ID, not filename</span>
<span class="code-keyword">$user_id</span> = <span class="code-keyword">$_SESSION</span>[<span class="code-string">'user_id'</span>];

<span class="code-comment">// Verify ownership in database</span>
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"
    SELECT filename FROM user_files 
    WHERE id = ? AND user_id = ?
"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$file_id</span>, <span class="code-keyword">$user_id</span>]);
<span class="code-keyword">$file</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();

<span class="code-keyword">if</span> (<span class="code-keyword">$file</span>) {
    <span class="code-keyword">$filepath</span> = <span class="code-string">"/var/www/uploads/"</span> . <span class="code-keyword">$file</span>[<span class="code-string">'filename'</span>];
    <span class="code-function">readfile</span>(<span class="code-keyword">$filepath</span>);
} <span class="code-keyword">else</span> {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
    <span class="code-function">die</span>(<span class="code-string">"Access denied"</span>);
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Mass Assignment Prevention</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Binding all input data to model without filtering sensitive fields.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable Mass Assignment</span>
          </div>
          <pre><code><span class="code-comment">// Vulnerable: Direct assignment of all input</span>
<span class="code-keyword">app.post</span>(<span class="code-string">'/api/users'</span>, <span class="code-keyword">async</span> (<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-comment">// DANGEROUS: req.body might contain role: 'admin'</span>
    <span class="code-keyword">const</span> user = <span class="code-keyword">await</span> User.<span class="code-function">create</span>(req.body);
    res.<span class="code-function">json</span>(user);
});

<span class="code-comment">// Attacker sends:</span>
<span class="code-comment">// POST /api/users</span>
<span class="code-comment">// { "name": "attacker", "role": "admin", "is_verified": true }</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Mass Assignment</span>
          </div>
          <pre><code><span class="code-comment">// Secure: Whitelist allowed fields</span>
<span class="code-keyword">app.post</span>(<span class="code-string">'/api/users'</span>, <span class="code-keyword">async</span> (<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-keyword">const</span> allowedFields = [<span class="code-string">'name'</span>, <span class="code-string">'email'</span>, <span class="code-string">'password'</span>];
    
    <span class="code-comment">// Filter input to only allowed fields</span>
    <span class="code-keyword">const</span> filteredData = {};
    allowedFields.<span class="code-function">forEach</span>(<span class="code-attr">field</span> => {
        <span class="code-keyword">if</span> (req.body[field]) {
            filteredData[field] = req.body[field];
        }
    });
    
    <span class="code-comment">// Or use library feature (e.g., Sequelize)</span>
    <span class="code-keyword">const</span> user = <span class="code-keyword">await</span> User.<span class="code-function">create</span>(filteredData, {
        <span class="code-attr">fields</span>: allowedFields  <span class="code-comment">// Explicit field whitelist</span>
    });
    res.<span class="code-function">json</span>(user);
});</code></pre>
        </div>
      </div>

      <!-- Section 6: Bypass -->
      <div id="bypass" class="content-card">
        <h2 class="card-title">
          <i>🚧</i> IDOR Bypass Techniques
        </h2>

        <p class="text-content">
          Attackers employ various techniques to bypass weak or improperly implemented access controls. Understanding
          these helps in building robust defenses.
        </p>

        <h3 class="subsection-title">1. Parameter Pollution</h3>
        <p class="text-content">
          Supplying multiple parameters with the same name to exploit differences in how frameworks parse input.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Parameter Pollution Attack</span>
          </div>
          <pre><code><span class="code-comment">-- PHP parses last occurrence, J2EE parses first</span>
<span class="code-string">GET /transfer?amount=100&to=123&to=456</span>

<span class="code-comment">-- If validation uses first 'to' (123) but transfer uses second (456)</span>
<span class="code-comment">-- Money goes to attacker (456) instead of intended recipient (123)</span>

<span class="code-comment">-- JSON parameter pollution</span>
{
    <span class="code-attr">"user_id"</span>: <span class="code-string">"42"</span>,
    <span class="code-attr">"user_id"</span>: <span class="code-string">"43"</span>  <span class="code-comment">-- Some parsers use last value</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">2. HTTP Method Switching</h3>
        <p class="text-content">
          Some endpoints have access controls on one HTTP method but not others.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Method Switching</span>
          </div>
          <pre><code><span class="code-comment">-- GET requires authentication</span>
<span class="code-keyword">GET</span> /api/admin/users <span class="code-keyword">HTTP/1.1</span>
<span class="code-comment">-- 403 Forbidden</span>

<span class="code-comment">-- But POST doesn't (vulnerable)</span>
<span class="code-keyword">POST</span> /api/admin/users <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Content-Length</span>: <span class="code-string">0</span>
<span class="code-comment">-- 200 OK with full user list!</span>

<span class="code-comment">-- Or use alternative methods</span>
<span class="code-keyword">PUT</span> /api/admin/users
<span class="code-keyword">PATCH</span> /api/admin/users
<span class="code-keyword">DELETE</span> /api/admin/users</code></pre>
        </div>

        <h3 class="subsection-title">3. Path Traversal in IDs</h3>
        <p class="text-content">
          Using path traversal sequences to bypass pattern-based access controls.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Path Traversal Bypass</span>
          </div>
          <pre><code><span class="code-comment">-- Blocked by regex /api/users/\d+</span>
<span class="code-string">GET /api/users/123/profile</span>  <span class="code-comment">-- 403 Forbidden</span>

<span class="code-comment">-- Bypass using path traversal</span>
<span class="code-string">GET /api/users/123/../456/profile</span>  <span class="code-comment">-- Might bypass regex</span>
<span class="code-string">GET /api/users/123/..%2F456/profile</span>  <span class="code-comment">-- URL encoded</span>

<span class="code-comment">-- Or unicode normalization</span>
<span class="code-string">GET /api/users/123/../456/profile</span>  <span class="code-comment">-- Using Unicode /</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Encoding and Case Variation</h3>
        <p class="text-content">
          Bypassing filters that don't properly normalize input before authorization checks.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Encoding Bypasses</span>
          </div>
          <pre><code><span class="code-comment">-- Case variation</span>
<span class="code-string">/API/users/123</span>  <span class="code-keyword">vs</span>  <span class="code-string">/api/USERS/123</span>

<span class="code-comment">-- URL encoding</span>
<span class="code-string">/api/users/%31%32%33</span>  <span class="code-comment">-- 123 encoded</span>

<span class="code-comment">-- Double URL encoding</span>
<span class="code-string">/api/users/%2531%2532%2533</span>

<span class="code-comment">-- Unicode equivalents</span>
<span class="code-string">/api/users/１２３</span>  <span class="code-comment">-- Full-width Unicode numbers</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Wildcard/NULL Byte Injection</h3>
        <p class="text-content">
          Using wildcards or null bytes to match unintended records.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Wildcard Attacks</span>
          </div>
          <pre><code><span class="code-comment">-- SQL LIKE wildcards</span>
<span class="code-string">GET /api/search?user=admin%25</span>  <span class="code-comment">-- % is wildcard</span>
<span class="code-comment">-- Might return all users starting with 'admin'</span>

<span class="code-comment">-- NULL byte in filename (legacy PHP/C)</span>
<span class="code-string">GET /download?file=secret.txt%00.jpg</span>
<span class="code-comment">-- If validation checks .jpg extension but null byte truncates</span>
<span class="code-comment">-- System reads secret.txt instead</span></code></pre>
        </div>

        <h3 class="subsection-title">6. JWT/Token Manipulation</h3>
        <p class="text-content">
          Modifying JWT claims or using algorithm confusion to change user context.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">JWT IDOR</span>
          </div>
          <pre><code><span class="code-comment">-- Original JWT payload</span>
{
    <span class="code-attr">"user_id"</span>: <span class="code-string">"42"</span>,
    <span class="code-attr">"role"</span>: <span class="code-string">"user"</span>
}

<span class="code-comment">-- Modified payload (if signature not verified)</span>
{
    <span class="code-attr">"user_id"</span>: <span class="code-string">"1"</span>,  <span class="code-comment">-- Changed to admin</span>
    <span class="code-attr">"role"</span>: <span class="code-string">"admin"</span>
}

<span class="code-comment">-- Or algorithm confusion (alg: none)</span>
{
    <span class="code-attr">"alg"</span>: <span class="code-string">"none"</span>,
    <span class="code-attr">"typ"</span>: <span class="code-string">"JWT"</span>
}</code></pre>
        </div>
      </div>

      <!-- Section 7: Mitigation -->
      <div id="mitigation" class="content-card">
        <h2 class="card-title">
          <i>🛡️</i> IDOR Prevention Checklist: Defense in Depth
        </h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never trust user-supplied object references. Always verify that the
          authenticated user has explicit authorization to access the requested resource. Implement defense in depth
          with multiple validation layers.
        </div>

        <h3 class="subsection-title">Layer 1: Indirect Object References (IOR)</h3>
        <p class="text-content">
          Replace direct database IDs with indirect, random, and unpredictable references. Map these to actual IDs
          server-side.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Indirect Reference Implementation</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Map sensitive IDs to random, temporary references</span>
<span class="code-keyword">class</span> <span class="code-function">ReferenceMapper</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$map</span> = [];
    
    <span class="code-keyword">public function</span> <span class="code-function">createReference</span>(<span class="code-keyword">$real_id</span>, <span class="code-keyword">$user_id</span>) {
        <span class="code-keyword">$ref</span> = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">16</span>));  <span class="code-comment">// 32 char random string</span>
        <span class="code-keyword">$this</span>-><span class="code-attr">map</span>[<span class="code-keyword">$ref</span>] = [
            <span class="code-string">'real_id'</span> => <span class="code-keyword">$real_id</span>,
            <span class="code-string">'user_id'</span> => <span class="code-keyword">$user_id</span>,
            <span class="code-string">'expires'</span> => <span class="code-function">time</span>() + <span class="code-keyword">3600</span>
        ];
        <span class="code-keyword">return</span> <span class="code-keyword">$ref</span>;
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">getRealId</span>(<span class="code-keyword">$ref</span>, <span class="code-keyword">$current_user_id</span>) {
        <span class="code-keyword">if</span> (!<span class="code-function">isset</span>(<span class="code-keyword">$this</span>-><span class="code-attr">map</span>[<span class="code-keyword">$ref</span>])) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        <span class="code-keyword">$data</span> = <span class="code-keyword">$this</span>-><span class="code-attr">map</span>[<span class="code-keyword">$ref</span>];
        
        <span class="code-comment">// Verify ownership and expiration</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$data</span>[<span class="code-string">'user_id'</span>] !== <span class="code-keyword">$current_user_id</span> || 
            <span class="code-keyword">$data</span>[<span class="code-string">'expires'</span>] < <span class="code-function">time</span>()) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-keyword">return</span> <span class="code-keyword">$data</span>[<span class="code-string">'real_id'</span>];
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Strict Authorization Checks</h3>
        <p class="text-content">
          Implement authorization checks at every endpoint that accesses resources. Use a centralized authorization
          service or middleware.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Centralized Authorization Middleware</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">AuthorizationMiddleware</span> {
    <span class="code-keyword">public function</span> <span class="code-function">checkOwnership</span>(<span class="code-keyword">$resource_type</span>, <span class="code-keyword">$resource_id</span>, <span class="code-keyword">$user_id</span>) {
        <span class="code-keyword">switch</span> (<span class="code-keyword">$resource_type</span>) {
            <span class="code-keyword">case</span> <span class="code-string">'profile'</span>:
                <span class="code-keyword">return</span> <span class="code-keyword">$this</span>-><span class="code-function">checkProfileOwnership</span>(<span class="code-keyword">$resource_id</span>, <span class="code-keyword">$user_id</span>);
            <span class="code-keyword">case</span> <span class="code-string">'document'</span>:
                <span class="code-keyword">return</span> <span class="code-keyword">$this</span>-><span class="code-function">checkDocumentOwnership</span>(<span class="code-keyword">$resource_id</span>, <span class="code-keyword">$user_id</span>);
            <span class="code-keyword">case</span> <span class="code-string">'order'</span>:
                <span class="code-keyword">return</span> <span class="code-keyword">$this</span>-><span class="code-function">checkOrderOwnership</span>(<span class="code-keyword">$resource_id</span>, <span class="code-keyword">$user_id</span>);
            <span class="code-keyword">default</span>:
                <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
    }
    
    <span class="code-keyword">private function</span> <span class="code-function">checkProfileOwnership</span>(<span class="code-keyword">$profile_id</span>, <span class="code-keyword">$user_id</span>) {
        <span class="code-comment">// Admins can view all profiles</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$this</span>-><span class="code-function">isAdmin</span>(<span class="code-keyword">$user_id</span>)) <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
        
        <span class="code-comment">// Users can only view their own</span>
        <span class="code-keyword">return</span> <span class="code-keyword">$profile_id</span> === <span class="code-keyword">$user_id</span>;
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Input Validation and Sanitization</h3>
        <p class="text-content">
          Validate and sanitize all input parameters before processing. Reject unexpected formats or values.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Input Validation</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">function</span> <span class="code-function">validateResourceId</span>(<span class="code-keyword">$id</span>, <span class="code-keyword">$type</span> = <span class="code-string">'numeric'</span>) {
    <span class="code-keyword">switch</span> (<span class="code-keyword">$type</span>) {
        <span class="code-keyword">case</span> <span class="code-string">'numeric'</span>:
            <span class="code-keyword">if</span> (!<span class="code-function">filter_var</span>(<span class="code-keyword">$id</span>, <span class="code-function">FILTER_VALIDATE_INT</span>)) {
                <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
            }
            <span class="code-keyword">return</span> <span class="code-keyword">$id</span> > <span class="code-keyword">0</span>;
            
        <span class="code-keyword">case</span> <span class="code-string">'uuid'</span>:
            <span class="code-keyword">return</span> <span class="code-function">preg_match</span>(<span class="code-string">'/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i'</span>, <span class="code-keyword">$id</span>);
            
        <span class="code-keyword">case</span> <span class="code-string">'filename'</span>:
            <span class="code-comment">// Prevent path traversal</span>
            <span class="code-keyword">if</span> (<span class="code-function">strpos</span>(<span class="code-keyword">$id</span>, <span class="code-string">'..'</span>) !== <span class="code-keyword">false</span> || 
                <span class="code-function">strpos</span>(<span class="code-keyword">$id</span>, <span class="code-string">'/'</span>) !== <span class="code-keyword">false</span>) {
                <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
            }
            <span class="code-keyword">return</span> <span class="code-function">preg_match</span>(<span class="code-string">'/^[a-zA-Z0-9_\-\.]+$/'</span>, <span class="code-keyword">$id</span>);
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Rate Limiting and Monitoring</h3>
        <p class="text-content">
          Implement rate limiting to prevent automated enumeration attacks and monitor for suspicious access patterns.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Rate Limiting Implementation</span>
          </div>
          <pre><code><span class="code-comment">// Using Redis for distributed rate limiting</span>
<span class="code-keyword">const</span> rateLimit = <span class="code-keyword">async</span> (<span class="code-attr">userId</span>, <span class="code-attr">resource</span>) => {
    <span class="code-keyword">const</span> key = <span class="code-string">`ratelimit:${userId}:${resource}`</span>;
    <span class="code-keyword">const</span> limit = <span class="code-keyword">100</span>;  <span class="code-comment">// requests per hour</span>
    <span class="code-keyword">const</span> window = <span class="code-keyword">3600</span>;  <span class="code-comment">// 1 hour</span>
    
    <span class="code-keyword">const</span> current = <span class="code-keyword">await</span> redis.<span class="code-function">incr</span>(key);
    <span class="code-keyword">if</span> (current === <span class="code-keyword">1</span>) {
        <span class="code-keyword">await</span> redis.<span class="code-function">expire</span>(key, window);
    }
    
    <span class="code-keyword">if</span> (current > limit) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Rate limit exceeded'</span>);
    }
};

<span class="code-comment">// Monitor for enumeration attempts</span>
<span class="code-keyword">const</span> detectEnumeration = (<span class="code-attr">userId</span>, <span class="code-attr">requestedIds</span>) => {
    <span class="code-keyword">const</span> uniqueIds = <span class="code-keyword">new</span> <span class="code-function">Set</span>(requestedIds).<span class="code-attr">size</span>;
    <span class="code-keyword">if</span> (uniqueIds > <span class="code-keyword">50</span>) {  <span class="code-comment">// Threshold</span>
        <span class="code-function">alertSecurityTeam</span>(<span class="code-string">`Possible IDOR enumeration by user ${userId}`</span>);
    }
};</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Testing and Auditing</h3>
        <p class="text-content">
          Regularly test for IDOR vulnerabilities using both automated tools and manual testing methodologies.
        </p>

        <div class="highlight-box">
          <strong>Testing Checklist:</strong>
          <ul style="margin-left: 2rem;">
            <li>Create multiple test accounts with different privilege levels</li>
            <li>Document all endpoints that accept object identifiers</li>
            <li>Test each endpoint with cross-account access attempts</li>
            <li>Verify that indirect references cannot be reverse-engineered</li>
            <li>Test with various HTTP methods (GET, POST, PUT, DELETE, PATCH)</li>
            <li>Attempt parameter pollution and encoding bypasses</li>
            <li>Review access logs for enumeration patterns</li>
          </ul>
        </div>

        <h3 class="subsection-title">Security Checklist Summary</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Defense Layer</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Implementation</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary);">Priority</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Indirect References</td>
              <td style="padding: 0.75rem;">Random, unguessable object references</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Authorization Checks</td>
              <td style="padding: 0.75rem;">Verify ownership on every request</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Input Validation</td>
              <td style="padding: 0.75rem;">Whitelist allowed formats and values</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Rate Limiting</td>
              <td style="padding: 0.75rem;">Prevent automated enumeration</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Logging & Monitoring</td>
              <td style="padding: 0.75rem;">Detect suspicious access patterns</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Regular Testing</td>
              <td style="padding: 0.75rem;">Automated and manual IDOR testing</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for IDOR</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete IDOR protection implementation walkthrough]
          </div>
        </div>
      </div>

    </main>
  </div>

  <script>
  // Simple sidebar toggle for mobile
  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.style.transform = sidebar.style.transform === 'translateX(0%)' ? 'translateX(-100%)' : 'translateX(0%)';
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Copy code functionality
  function copyCode(btn) {
    const codeBlock = btn.closest('.code-block').querySelector('pre');
    const text = codeBlock.textContent;
    navigator.clipboard.writeText(text).then(() => {
      btn.textContent = '✅ Copied!';
      setTimeout(() => btn.textContent = '📋 Copy', 2000);
    });
  }
  </script>
  <?php if (!$isLoggedIn): ?>
  <script>
  window.addEventListener("load", function() {
    if (typeof LoginModal !== "undefined") {
      LoginModal.show();
    }
  });
  </script>
  <?php endif; ?>

</body>


</html>