<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_case5_attempts'])) {
  $_SESSION['csrf_case5_attempts'] = 0;
}
if (!isset($_SESSION['csrf_case5_solved'])) {
  $_SESSION['csrf_case5_solved'] = false;
}

// ─── Simulated Admin Panel Data ──────────────────────────────────────────
$settings = [
  'site_name' => 'DarkHunter Training Platform',
  'maintenance_mode' => false,
  'allow_registration' => true,
  'api_enabled' => true,
  'webhook_url' => 'https://darkhunter.local/api/v1/webhooks/primary',
  'session_timeout' => 3600,
  'max_login_attempts' => 5,
  'flag' => 'DH{csrf_samesite_bypass_master}'
];

$admin_logs = [
  ['time' => '2026-05-22 03:14:22', 'action' => 'Admin login from 192.168.1.45', 'severity' => 'info'],
  ['time' => '2026-05-22 02:58:11', 'action' => 'User role changed: alice -> Admin', 'severity' => 'warning'],
  ['time' => '2026-05-22 02:30:45', 'action' => 'System backup completed', 'severity' => 'info'],
  ['time' => '2026-05-22 01:15:33', 'action' => 'Failed login attempt from 10.0.0.7', 'severity' => 'danger'],
  ['time' => '2026-05-21 23:42:18', 'action' => 'Webhook configuration updated', 'severity' => 'warning'],
];

// ─── Vulnerable SameSite Configuration ──────────────────────────────────
// Cookie is set with SameSite=None (or missing) allowing cross-site requests
$cookie_config = [
  'name' => 'admin_session',
  'value' => 'a3f7c9e2d1b8a4f5c6e7d8b9a0c1d2e3',
  'samesite' => 'None', // VULNERABLE: Should be Strict or Lax
  'secure' => false,   // VULNERABLE: Not requiring HTTPS
  'httponly' => false  // VULNERABLE: Accessible via JavaScript
];

// ─── State-Changing Action Handler (Vulnerable) ──────────────────────────
$action_result = null;
$show_flag = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  // VULNERABLE: No CSRF token, no origin validation, no SameSite protection
  // The request could come from any origin due to SameSite=None

  if ($_POST['action'] === 'update_webhook') {
    $settings['webhook_url'] = $_POST['webhook_url'] ?? $settings['webhook_url'];
    $action_result = [
      'type' => 'success',
      'message' => 'Webhook URL updated successfully to: ' . htmlspecialchars($settings['webhook_url'])
    ];
    array_unshift($admin_logs, [
      'time' => date('Y-m-d H:i:s'),
      'action' => 'Webhook URL modified via admin panel',
      'severity' => 'warning'
    ]);
  }

  if ($_POST['action'] === 'toggle_maintenance') {
    $settings['maintenance_mode'] = !$settings['maintenance_mode'];
    $action_result = [
      'type' => 'success',
      'message' => 'Maintenance mode ' . ($settings['maintenance_mode'] ? 'ENABLED' : 'DISABLED')
    ];
    array_unshift($admin_logs, [
      'time' => date('Y-m-d H:i:s'),
      'action' => 'Maintenance mode toggled via admin panel',
      'severity' => 'danger'
    ]);
  }

  if ($_POST['action'] === 'promote_user') {
    $action_result = [
      'type' => 'success',
      'message' => 'User ' . htmlspecialchars($_POST['username'] ?? 'unknown') . ' promoted to Administrator'
    ];
    array_unshift($admin_logs, [
      'time' => date('Y-m-d H:i:s'),
      'action' => 'User promoted to admin: ' . ($_POST['username'] ?? 'unknown'),
      'severity' => 'danger'
    ]);
  }

  if ($_POST['action'] === 'reveal_secrets') {
    $show_flag = true;
    $action_result = [
      'type' => 'danger',
      'message' => '🔓 SECRETS REVEALED - Unauthorized access detected!'
    ];
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['csrf_case5_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['csrf_case5_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['csrf_case5_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully bypassed SameSite cookie protections and performed a Cross-Site Request Forgery attack. The admin panel accepted your forged request because the session cookie was sent with SameSite=None, allowing cross-origin POST requests!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['view']) || isset($_GET['tab']))) {
  $_SESSION['csrf_case5_attempts']++;
}

$attempts = $_SESSION['csrf_case5_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - CSRF Case 5 (SameSite Bypass)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-cookie-bite"></i> Admin Panel - SameSite Bypass</h1>
      <p class="lab-description">Manage the DarkHunter platform settings. This advanced CSRF challenge requires
        bypassing SameSite cookie restrictions. <strong>The session cookie is configured with SameSite=None!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this SameSite bypass vulnerability. You can continue exploring, but no additional
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

    <?php if ($action_result): ?>
      <div class="action-result <?php echo $action_result['type']; ?>">
        <i
          class="fas fa-<?php echo $action_result['type'] === 'danger' ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
        <span><?php echo $action_result['message']; ?></span>
      </div>
    <?php endif; ?>

    <!-- Admin Dashboard Grid -->
    <div class="admin-dashboard">

      <!-- Settings Panel -->
      <div class="dashboard-card">
        <div class="card-header">
          <i class="fas fa-cogs"></i>
          <h3>Platform Settings</h3>
        </div>
        <div class="settings-list">
          <div class="setting-item">
            <span class="setting-name">Site Name</span>
            <span class="setting-value"><?php echo htmlspecialchars($settings['site_name']); ?></span>
          </div>
          <div class="setting-item">
            <span class="setting-name">Maintenance Mode</span>
            <span
              class="setting-value <?php echo $settings['maintenance_mode'] ? 'status-danger' : 'status-success'; ?>">
              <?php echo $settings['maintenance_mode'] ? 'ENABLED' : 'DISABLED'; ?>
            </span>
          </div>
          <div class="setting-item">
            <span class="setting-name">Registration</span>
            <span
              class="setting-value <?php echo $settings['allow_registration'] ? 'status-success' : 'status-danger'; ?>">
              <?php echo $settings['allow_registration'] ? 'OPEN' : 'CLOSED'; ?>
            </span>
          </div>
          <div class="setting-item">
            <span class="setting-name">API Status</span>
            <span class="setting-value <?php echo $settings['api_enabled'] ? 'status-success' : 'status-danger'; ?>">
              <?php echo $settings['api_enabled'] ? 'ACTIVE' : 'INACTIVE'; ?>
            </span>
          </div>
          <div class="setting-item">
            <span class="setting-name">Session Timeout</span>
            <span class="setting-value"><?php echo $settings['session_timeout']; ?>s</span>
          </div>
        </div>
      </div>

      <!-- Webhook Configuration (Vulnerable Action) -->
      <div class="dashboard-card vulnerable-card">
        <div class="card-header">
          <i class="fas fa-webhook"></i>
          <h3>Webhook Configuration</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No CSRF Protection</span>
        </div>
        <form method="POST" action="" class="action-form" id="webhook-form">
          <input type="hidden" name="action" value="update_webhook">
          <div class="form-group">
            <label>Webhook URL</label>
            <input type="url" name="webhook_url" value="<?php echo htmlspecialchars($settings['webhook_url']); ?>"
              class="form-input">
          </div>
          <button type="submit" class="btn-action">
            <i class="fas fa-save"></i> Update Webhook
          </button>
        </form>
      </div>

      <!-- User Management (Vulnerable Action) -->
      <div class="dashboard-card vulnerable-card">
        <div class="card-header">
          <i class="fas fa-users-cog"></i>
          <h3>User Management</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No CSRF Protection</span>
        </div>
        <form method="POST" action="" class="action-form" id="promote-form">
          <input type="hidden" name="action" value="promote_user">
          <div class="form-group">
            <label>Username to Promote</label>
            <input type="text" name="username" placeholder="Enter username..." class="form-input">
          </div>
          <button type="submit" class="btn-action btn-danger">
            <i class="fas fa-crown"></i> Promote to Admin
          </button>
        </form>
      </div>

      <!-- System Controls (Vulnerable Action) -->
      <div class="dashboard-card vulnerable-card">
        <div class="card-header">
          <i class="fas fa-power-off"></i>
          <h3>System Controls</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No CSRF Protection</span>
        </div>
        <form method="POST" action="" class="action-form" id="maintenance-form">
          <input type="hidden" name="action" value="toggle_maintenance">
          <p class="form-description">Toggle maintenance mode for the entire platform.</p>
          <button type="submit" class="btn-action btn-warning">
            <i class="fas fa-toggle-<?php echo $settings['maintenance_mode'] ? 'on' : 'off'; ?>"></i>
            Toggle Maintenance Mode
          </button>
        </form>
      </div>

      <!-- Secrets Panel (Target for Exploitation) -->
      <div class="dashboard-card secrets-card">
        <div class="card-header">
          <i class="fas fa-key"></i>
          <h3>Secrets Vault</h3>
          <span class="secret-badge"><i class="fas fa-lock"></i> Restricted</span>
        </div>
        <?php if ($show_flag): ?>
          <div class="secrets-revealed">
            <div class="flag-display">
              <i class="fas fa-flag"></i>
              <code><?php echo $settings['flag']; ?></code>
            </div>
            <p class="secret-note">This flag proves you successfully performed a CSRF attack bypassing SameSite
              protections!</p>
          </div>
        <?php else: ?>
          <form method="POST" action="" class="action-form" id="secrets-form">
            <input type="hidden" name="action" value="reveal_secrets">
            <p class="form-description">Access restricted system secrets and configuration flags.</p>
            <button type="submit" class="btn-action btn-secret">
              <i class="fas fa-unlock-alt"></i> Reveal Secrets
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Admin Logs -->
      <div class="dashboard-card logs-card">
        <div class="card-header">
          <i class="fas fa-clipboard-list"></i>
          <h3>Admin Activity Logs</h3>
        </div>
        <div class="logs-list">
          <?php foreach ($admin_logs as $log): ?>
            <div class="log-item severity-<?php echo $log['severity']; ?>">
              <span class="log-time"><?php echo $log['time']; ?></span>
              <span class="log-action"><?php echo htmlspecialchars($log['action']); ?></span>
              <span class="log-badge <?php echo $log['severity']; ?>"><?php echo strtoupper($log['severity']); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Cookie Configuration Display -->
    <div class="debug-panel cookie-panel">
      <div class="debug-header">
        <i class="fas fa-cookie"></i>
        <span>Session Cookie Configuration</span>
      </div>
      <div class="debug-body">
        <div class="cookie-config">
          <div class="cookie-item">
            <span class="cookie-prop">Name</span>
            <code><?php echo $cookie_config['name']; ?></code>
          </div>
          <div class="cookie-item">
            <span class="cookie-prop">SameSite</span>
            <code class="vuln-value"><?php echo $cookie_config['samesite']; ?></code>
          </div>
          <div class="cookie-item">
            <span class="cookie-prop">Secure</span>
            <code class="vuln-value"><?php echo $cookie_config['secure'] ? 'true' : 'false'; ?></code>
          </div>
          <div class="cookie-item">
            <span class="cookie-prop">HttpOnly</span>
            <code class="vuln-value"><?php echo $cookie_config['httponly'] ? 'true' : 'false'; ?></code>
          </div>
        </div>
      </div>
    </div>

    <!-- Request Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Origin: <?php echo $_SERVER['HTTP_ORIGIN'] ?? 'Not Set'; ?></span>
          <span>Referer: <?php echo $_SERVER['HTTP_REFERER'] ?? 'Not Set'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Examine the cookie configuration panel. The SameSite attribute is set to <code>None</code>,
          which means the browser will send this cookie with cross-site requests. This is the key vulnerability.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Create a malicious HTML page on a different origin that submits a POST form to the
          <code>reveal_secrets</code> action. Since SameSite=None, the victim's session cookie will be included
          automatically.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Host a page with an auto-submitting form:
          <code>&lt;form action="https://target.com/CSRF-CASE5.php" method="POST"&gt;&lt;input type="hidden" name="action" value="reveal_secrets"&gt;&lt;/form&gt;&lt;script&gt;document.forms[0].submit();&lt;/script&gt;</code>.
          Trick an admin into visiting it. The cookie will be sent and the secrets revealed!
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
      // Auto-detect if secrets were revealed (CSRF successful)
      const secretsRevealed = document.querySelector('.secrets-revealed');
      const alreadySolved = document.querySelector('.solved-banner');

      if (secretsRevealed && !alreadySolved) {
        // Check if this was a POST request (indicating form submission from external origin)
        if (document.querySelector('.action-result')) {
          document.getElementById('solved-flag').value = '1';
          document.getElementById('success-form').submit();
        }
      }
    });
  </script>
</body>

</html>