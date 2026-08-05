<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case5_attempts'])) {
  $_SESSION['html_case5_attempts'] = 0;
}
if (!isset($_SESSION['html_case5_solved'])) {
  $_SESSION['html_case5_solved'] = false;
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case5_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case5_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case5_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a DOM-Based HTML Injection vulnerability. By manipulating the URL hash/fragment, you injected malicious HTML that executed entirely on the client-side without any server interaction!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fragment'])) {
  $_SESSION['html_case5_attempts']++;
}

$attempts = $_SESSION['html_case5_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Single Page App - HTML Injection Case 5 (DOM Based)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-globe"></i> Single Page Application</h1>
      <p class="lab-description">Explore our client-side rendered dashboard. This medium-difficulty HTML Injection
        challenge uses <strong>innerHTML</strong> to render content from URL fragments. <strong>No server
          validation!</strong> Inject HTML through the hash fragment.</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this DOM-Based HTML Injection vulnerability. You can continue exploring, but no
            additional points will be awarded.</p>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
      <div class="success-alert">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Completed!</h3>
          <p><?php echo $success_msg; ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- SPA Grid -->
    <div class="spa-grid">

      <!-- Navigation Panel -->
      <div class="spa-card nav-card">
        <div class="card-header">
          <i class="fas fa-compass"></i>
          <h3>Navigation</h3>
        </div>

        <nav class="spa-nav">
          <a href="#home" class="nav-link active" data-page="home">
            <i class="fas fa-home"></i> Home
          </a>
          <a href="#about" class="nav-link" data-page="about">
            <i class="fas fa-info-circle"></i> About
          </a>
          <a href="#services" class="nav-link" data-page="services">
            <i class="fas fa-cogs"></i> Services
          </a>
          <a href="#contact" class="nav-link" data-page="contact">
            <i class="fas fa-envelope"></i> Contact
          </a>
        </nav>

        <div class="fragment-demo">
          <span class="demo-label">Try these fragments:</span>
          <code class="demo-code">#home</code>
          <code class="demo-code">#&lt;h1&gt;Hacked&lt;/h1&gt;</code>
          <code class="demo-code">#&lt;img src=x onerror=alert(1)&gt;</code>
        </div>
      </div>

      <!-- Content Area (Vulnerable) -->
      <div class="spa-card content-card">
        <div class="card-header">
          <i class="fas fa-desktop"></i>
          <h3>Content Viewer</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> innerHTML Used</span>
        </div>

        <!-- VULNERABLE: Content rendered via innerHTML from hash -->
        <div id="spa-content" class="spa-content">
          <div class="content-placeholder">
            <i class="fas fa-mouse-pointer"></i>
            <p>Click a navigation link or modify the URL hash to load content</p>
          </div>
        </div>

        <div class="render-info">
          <div class="info-row">
            <span class="info-label">Current Hash:</span>
            <code id="current-hash" class="info-value">None</code>
          </div>
          <div class="info-row">
            <span class="info-label">Render Method:</span>
            <code class="info-value vuln">innerHTML (Dangerous!)</code>
          </div>
          <div class="info-row">
            <span class="info-label">HTML Injected:</span>
            <code id="injection-status" class="info-value">NO</code>
          </div>
        </div>
      </div>

      <!-- DOM Analysis -->
      <div class="spa-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>DOM Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Vulnerable Code:</span>
            <pre class="analysis-code"><code>// DANGEROUS: Direct innerHTML assignment
const hash = location.hash.substring(1);
contentDiv.innerHTML = decodeURIComponent(hash);</code></pre>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Alternative:</span>
            <pre class="analysis-code secure"><code>// SAFE: Use textContent instead
const hash = location.hash.substring(1);
contentDiv.textContent = decodeURIComponent(hash);</code></pre>
          </div>
        </div>
      </div>

      <!-- Attack Vectors -->
      <div class="spa-card vectors-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>DOM Injection Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Hash Fragment Injection</div>
            <code class="vector-code">#&lt;h1 style="color:red"&gt;PWNED&lt;/h1&gt;</code>
            <span class="vector-desc">Inject HTML directly through URL hash</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Event Handler Injection</div>
            <code class="vector-code">#&lt;div onmouseover="alert('XSS')"&gt;Hover me&lt;/div&gt;</code>
            <span class="vector-desc">Inject event handlers via hash</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Image Error Handler</div>
            <code class="vector-code">#&lt;img src="x" onerror="alert('DOM XSS')"&gt;</code>
            <span class="vector-desc">Trigger JavaScript through image error</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Client-Side Rendered: YES</span>
          <span>innerHTML Used: YES</span>
          <span>Server Validation: NONE</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">This page uses <code>innerHTML</code> to render content from the URL hash fragment. Try
          modifying the URL to include HTML tags after the <code>#</code> symbol, like
          <code>#&lt;h1&gt;Test&lt;/h1&gt;</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The application reads <code>location.hash</code> and renders it with
          <code>innerHTML</code>. Try URL-encoded payloads: <code>#%3Ch1%3EPWNED%3C/h1%3E</code> or direct HTML:
          <code>#&lt;div style="background:red"&gt;HACKED&lt;/div&gt;</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use this URL fragment:
          <code>#&lt;h1 style="color:#ff6b6b"&gt;DOM INJECTION SUCCESS&lt;/h1&gt;</code>. Or try:
          <code>#&lt;div style="position:fixed;top:0;left:0;width:100%;height:100%;background:black;color:lime;display:flex;align-items:center;justify-content:center;z-index:9999"&gt;&lt;h1&gt;PWNED&lt;/h1&gt;&lt;/div&gt;</code>.
          The innerHTML will render your HTML immediately!
        </div>
      </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <!-- Hidden form for solve detection -->
  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
    // VULNERABLE: DOM-Based HTML Injection
    function renderContent() {
      const contentDiv = document.getElementById('spa-content');
      const hashDisplay = document.getElementById('current-hash');
      const injectionStatus = document.getElementById('injection-status');

      // DANGEROUS: Direct innerHTML from URL hash
      const hash = window.location.hash.substring(1);

      if (hash) {
        // VULNERABLE: No sanitization before innerHTML
        contentDiv.innerHTML = decodeURIComponent(hash);
        hashDisplay.textContent = '#' + hash;

        // Check for HTML injection
        const hasHtml = /<[a-zA-Z][^>]*>/.test(decodeURIComponent(hash));
        if (hasHtml) {
          injectionStatus.textContent = 'YES';
          injectionStatus.classList.add('danger');

          // Trigger solve
          const alreadySolved = document.querySelector('.solved-banner');
          if (!alreadySolved) {
            document.getElementById('solved-flag').value = '1';
            document.getElementById('success-form').submit();
          }
        } else {
          injectionStatus.textContent = 'NO';
        }
      } else {
        // Default content
        contentDiv.innerHTML = `
        <div class="content-placeholder">
          <i class="fas fa-home"></i>
          <h3>Welcome to DarkHunter SPA</h3>
          <p>This is a client-side rendered application. The content is loaded from URL hash fragments using innerHTML.</p>
          <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> Warning: This application is vulnerable to DOM-Based HTML Injection!</p>
        </div>
      `;
        hashDisplay.textContent = '#home (default)';
      }
    }

    // Handle navigation clicks
    document.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.getAttribute('data-page');

        // Update active state
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');

        // Set hash (will trigger hashchange)
        window.location.hash = page;
      });
    });

    // Listen for hash changes
    window.addEventListener('hashchange', renderContent);

    // Initial render
    window.addEventListener('load', renderContent);
  </script>
</body>

</html>