<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['OpenRedirect']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['or_easy1_attempts'])) {
  $_SESSION['or_easy1_attempts'] = 0;
}
if (!isset($_SESSION['or_easy1_solved'])) {
  $_SESSION['or_easy1_solved'] = false;
}

// ─── Simulated Application State ─────────────────────────────────────────
$is_authenticated = isset($_SESSION['user_id']);
$redirect_target = isset($_GET['redirect']) ? $_GET['redirect'] : '/DarkHunter/Public/index.php';

// ─── Vulnerable Logic: No validation on redirect parameter ───────────────
// This simulates a post-login redirect where the app blindly trusts the URL

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['or_easy1_solved'];
$exploited = false;

// Check if user was redirected from an external domain (simulated)
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

// Detect exploitation: redirect contains external domain or javascript
$external_indicators = [
  'evil.com', 'attacker.com', 'phishing.com', 'malicious.site',
  'javascript:', 'data:', 'file://', '//evil', '//attacker'
];

foreach ($external_indicators as $indicator) {
  if (stripos($redirect_target, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Also check for @ symbol bypass (user@evil.com)
if (preg_match('/https?:\/\/[^\/]+@/', $redirect_target)) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['or_easy1_attempts']++;
  
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  
  $_SESSION['or_easy1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited an Open Redirect vulnerability. You manipulated the redirect parameter to send users to an attacker-controlled destination. This is a classic phishing vector!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['redirect'])) {
  $_SESSION['or_easy1_attempts']++;
}

$attempts = $_SESSION['or_easy1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecureAuth Portal - Open Redirect Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/case1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Open Redirect Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-door-open"></i> SecureAuth Redirect Portal</h1>
      <p class="lab-description">You've just logged into the SecureAuth portal. The application redirects you based on
        the <code>redirect</code> parameter. <strong>No URL validation applied!</strong> Can you manipulate the redirect
        to send users to a malicious domain?</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Open Redirect vulnerability. You can continue exploring, but no additional
          points will be awarded.</p>
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

    <!-- Authentication Status Card -->
    <div class="auth-card">
      <div class="auth-header">
        <i class="fas fa-shield-alt"></i>
        <h2>Authentication Successful</h2>
      </div>
      <div class="auth-body">
        <div class="auth-status">
          <span class="status-icon"><i class="fas fa-check-circle"></i></span>
          <span class="status-text">You are authenticated as
            <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest_user'; ?></strong></span>
        </div>
        <p class="auth-message">Welcome back! You will be redirected to your destination shortly.</p>

        <!-- Redirect Display -->
        <div class="redirect-box">
          <div class="redirect-label">
            <i class="fas fa-location-arrow"></i>
            <span>Redirect Destination</span>
          </div>
          <div class="redirect-url">
            <code><?php echo htmlspecialchars($redirect_target); ?></code>
          </div>
        </div>

        <!-- The Vulnerable Button -->
        <a href="<?php echo htmlspecialchars($redirect_target); ?>" class="redirect-btn">
          <span class="btn-text">Continue to Application</span>
          <i class="fas fa-arrow-right"></i>
          <div class="btn-glow"></div>
        </a>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /CASE1.php?redirect=<?php echo urlencode($redirect_target); ?></code>
      </div>
    </div>

    <!-- Parameter Analysis Panel -->
    <div class="analysis-panel">
      <div class="analysis-header">
        <i class="fas fa-microscope"></i>
        <span>Parameter Analysis</span>
      </div>
      <div class="analysis-body">
        <div class="param-row">
          <span class="param-name">redirect</span>
          <span class="param-value"><?php echo htmlspecialchars($redirect_target); ?></span>
          <span class="param-status vulnerable"><i class="fas fa-exclamation-triangle"></i> Unvalidated</span>
        </div>
        <div class="param-row">
          <span class="param-name">validation</span>
          <span class="param-value">none</span>
          <span class="param-status vulnerable"><i class="fas fa-times-circle"></i> Missing</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Look at the URL parameter <code>redirect</code>. The application accepts any URL without
        validation. Try changing it to an external domain like <code>?redirect=https://evil.com</code> or
        <code>?redirect=//attacker.com</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The application doesn't check if the redirect URL belongs to the same domain. Try using
        protocol-relative URLs like <code>//evil.com</code> or even <code>javascript:alert(1)</code> to demonstrate the
        vulnerability.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Craft a URL with <code>?redirect=https://evil.com</code> and click the "Continue" button.
        The application will redirect to the external domain. Once the redirect contains a known malicious indicator,
        the challenge is solved!</div>
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
    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_target); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const redirect = urlParams.get('redirect') || '';

    // Check for exploitation indicators
    const maliciousPatterns = [
      'evil.com', 'attacker.com', 'phishing.com', 'malicious.site',
      'javascript:', 'data:', 'file://'
    ];

    const hasMalicious = maliciousPatterns.some(pattern =>
      redirect.toLowerCase().includes(pattern.toLowerCase())
    );

    // Check for @ bypass
    const hasAtBypass = /https?:\/\/[^\/]+@/.test(redirect);

    if ((hasMalicious || hasAtBypass) && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>