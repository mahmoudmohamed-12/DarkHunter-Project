<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case5_attempts'])) {
  $_SESSION['priv_case5_attempts'] = 0;
}
if (!isset($_SESSION['priv_case5_solved'])) {
  $_SESSION['priv_case5_solved'] = false;
}

// ─── Simulated OAuth2 Flow ───────────────────────────────────────────────
$oauth_step = $_GET['step'] ?? 'authorize';
$client_id = $_GET['client_id'] ?? 'darkhunter_app';
$redirect_uri = $_GET['redirect_uri'] ?? 'https://app.darkhunter.local/callback';
$scope = $_GET['scope'] ?? 'read_profile';
$state = $_GET['state'] ?? bin2hex(random_bytes(16));

$escalated = false;
$flag_triggered = false;

// VULNERABLE: Scope validation is weak - allows scope escalation
$allowed_scopes = ['read_profile', 'read_email', 'write_profile'];
$admin_scopes = ['admin_access', 'delete_users', 'manage_system', 'read_all_data'];

// Check if admin scopes are requested
$requested_scopes = explode(' ', $scope);
$has_admin_scope = false;
foreach ($requested_scopes as $s) {
  if (in_array(trim($s), $admin_scopes)) {
    $has_admin_scope = true;
    break;
  }
}

if ($has_admin_scope) {
  $escalated = true;
  $flag_triggered = true;
}

// ─── Handle OAuth Authorization ──────────────────────────────────────────
$auth_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['authorize'])) {
  $auth_success = true;

  // VULNERABLE: Grants any requested scope without user consent validation
  $granted_scope = $_POST['granted_scope'] ?? $scope;

  // Check for escalation in granted scope
  $granted_scopes = explode(' ', $granted_scope);
  foreach ($granted_scopes as $s) {
    if (in_array(trim($s), $admin_scopes)) {
      $escalated = true;
      $flag_triggered = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case5_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case5_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case5_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've successfully exploited an OAuth2 Scope Escalation vulnerability. By manipulating the scope parameter in the authorization flow, you gained elevated permissions beyond what was originally intended!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['scope'])) {
  $_SESSION['priv_case5_attempts']++;
}

$attempts = $_SESSION['priv_case5_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OAuth Authorization - PrivEsc Case 5 (OAuth Scope Escalation)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-link"></i> OAuth2 Authorization</h1>
      <p class="lab-description">Authorize third-party applications to access your DarkHunter account. This hard
        Privilege Escalation challenge has <strong>weak scope validation</strong>. <strong>Escalate permissions beyond
          user consent!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this OAuth Scope Escalation vulnerability. You can continue exploring, but no
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

    <!-- OAuth Grid -->
    <div class="oauth-grid">

      <!-- Authorization Request -->
      <div class="oauth-card request-card">
        <div class="card-header">
          <i class="fas fa-paper-plane"></i>
          <h3>Authorization Request</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Weak Scope Check</span>
        </div>

        <div class="request-details">
          <div class="detail-row">
            <span class="detail-label">Client ID:</span>
            <code class="detail-value"><?php echo htmlspecialchars($client_id); ?></code>
          </div>
          <div class="detail-row">
            <span class="detail-label">Redirect URI:</span>
            <code class="detail-value"><?php echo htmlspecialchars($redirect_uri); ?></code>
          </div>
          <div class="detail-row">
            <span class="detail-label">Requested Scope:</span>
            <code
              class="detail-value <?php echo $has_admin_scope ? 'danger' : ''; ?>"><?php echo htmlspecialchars($scope); ?></code>
          </div>
          <div class="detail-row">
            <span class="detail-label">State:</span>
            <code class="detail-value"><?php echo htmlspecialchars(substr($state, 0, 20)); ?>...</code>
          </div>
        </div>

        <div class="scope-manual">
          <span class="manual-label">Modify scope via URL:</span>
          <code class="manual-code">?scope=read_profile+admin_access</code>
        </div>
      </div>

      <!-- Consent Screen (Vulnerable) -->
      <div class="oauth-card consent-card">
        <div class="card-header">
          <i class="fas fa-hand-paper"></i>
          <h3>User Consent</h3>
        </div>

        <div class="consent-app">
          <div class="app-icon">
            <i class="fas fa-robot"></i>
          </div>
          <div class="app-info">
            <h4>DarkHunter App</h4>
            <p>wants to access your account</p>
          </div>
        </div>

        <div class="scope-list">
          <span class="scope-title">Requested Permissions:</span>
          <?php foreach ($requested_scopes as $s): ?>
          <div class="scope-item <?php echo in_array(trim($s), $admin_scopes) ? 'admin-scope' : ''; ?>">
            <i
              class="fas fa-<?php echo in_array(trim($s), $admin_scopes) ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
            <span><?php echo htmlspecialchars(trim($s)); ?></span>
            <?php if (in_array(trim($s), $admin_scopes)): ?>
            <span class="admin-badge">ADMIN</span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (!$auth_success): ?>
        <form method="POST" action="" class="consent-form">
          <input type="hidden" name="authorize" value="1">
          <input type="hidden" name="granted_scope" value="<?php echo htmlspecialchars($scope); ?>">

          <div class="consent-buttons">
            <button type="submit" class="btn-allow">
              <i class="fas fa-check"></i> Allow
            </button>
            <button type="button" class="btn-deny" onclick="alert('Access denied')">
              <i class="fas fa-times"></i> Deny
            </button>
          </div>
        </form>
        <?php else: ?>
        <div class="auth-success">
          <i class="fas fa-check-circle"></i>
          <span>Authorization granted with scope: <code><?php echo htmlspecialchars($scope); ?></code></span>
        </div>
        <?php endif; ?>

        <?php if ($escalated): ?>
        <div class="escalation-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <div class="escalation-content">
            <strong>Scope Escalation Detected!</strong>
            <span>Admin scopes were granted without proper validation!</span>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Scope Analysis -->
      <div class="oauth-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Scope Validation Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Allowed Scopes:</span>
            <code class="analysis-code">read_profile, read_email, write_profile</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> User</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Admin Scopes:</span>
            <code class="analysis-code">admin_access, delete_users, manage_system</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Not Protected</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Validation Logic:</span>
            <code class="analysis-code">// No scope validation on grant!</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
        </div>
      </div>

      <!-- Attack Vectors -->
      <div class="oauth-card vectors-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>Scope Escalation Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">URL Scope Injection</div>
            <code class="vector-code">?scope=read_profile+admin_access+delete_users</code>
            <span class="vector-desc">Add admin scopes to authorization URL</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Consent Bypass</div>
            <code class="vector-code">POST /authorize { "granted_scope": "admin_access" }</code>
            <span class="vector-desc">Modify granted scope in POST body</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Token Scope Tampering</div>
            <code class="vector-code">{ "scope": "admin_access read_all_data" }</code>
            <span class="vector-desc">Modify scope in issued token</span>
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
          <span>Client ID: <?php echo htmlspecialchars($client_id); ?></span>
          <span>Scope: <?php echo htmlspecialchars($scope); ?></span>
          <span>Admin Scope: <?php echo $has_admin_scope ? 'YES' : 'NO'; ?></span>
          <span>Escalated: <?php echo $escalated ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The authorization URL contains a <code>scope</code> parameter. Try modifying it to include
        admin scopes like <code>admin_access</code> or <code>delete_users</code>. The server doesn't validate if you're
        allowed to request these scopes!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try this URL: <code>?scope=read_profile+admin_access+manage_system</code>. The consent
        screen will show these admin permissions, and clicking "Allow" will grant them without proper validation!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Add <code>+admin_access</code> to the scope parameter in the URL. For example:
        <code>?client_id=darkhunter_app&scope=read_profile+admin_access+delete_users</code>. Then click "Allow" - the
        server grants these admin scopes without checking if you're an admin!
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
    const escalationAlert = document.querySelector('.escalation-alert');
    const alreadySolved = document.querySelector('.solved-banner');

    if (escalationAlert && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>