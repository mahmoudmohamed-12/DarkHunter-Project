<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_case8_attempts'])) {
  $_SESSION['csrf_case8_attempts'] = 0;
}
if (!isset($_SESSION['csrf_case8_solved'])) {
  $_SESSION['csrf_case8_solved'] = false;
}
if (!isset($_SESSION['csrf_case8_step'])) {
  $_SESSION['csrf_case8_step'] = 0;
}

// ─── Simulated Enterprise SSO System ───────────────────────────────────
$sso_config = [
  'provider' => 'DarkHunter SSO',
  'version' => 'v3.2.1',
  'auth_method' => 'OAuth 2.0 + SAML',
  'mfa_required' => true,
  'session_duration' => 7200,
  'allowed_redirects' => [
    'https://darkhunter.local/dashboard',
    'https://darkhunter.local/admin',
    'https://darkhunter.local/api/callback',
    'https://partner.darkhunter.local/auth', // VULNERABLE: Weak redirect validation
  ]
];

$users_sso = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Super Admin', 'mfa_enabled' => true, 'last_login' => '2026-05-22 04:30:00'],
  2 => ['id' => 2, 'username' => 'security_lead', 'email' => 'sec@darkhunter.local', 'role' => 'Security Lead', 'mfa_enabled' => true, 'last_login' => '2026-05-22 03:15:00'],
  3 => ['id' => 3, 'username' => 'dev_ops', 'email' => 'devops@darkhunter.local', 'role' => 'DevOps Engineer', 'mfa_enabled' => false, 'last_login' => '2026-05-21 22:00:00'],
];

$audit_trail = [
  ['time' => '2026-05-22 04:30:00', 'event' => 'Admin authenticated via SSO', 'source' => '192.168.1.10', 'risk' => 'low'],
  ['time' => '2026-05-22 04:28:15', 'event' => 'MFA challenge passed', 'source' => '192.168.1.10', 'risk' => 'low'],
  ['time' => '2026-05-22 04:25:33', 'event' => 'OAuth token refreshed', 'source' => '192.168.1.10', 'risk' => 'medium'],
  ['time' => '2026-05-22 03:45:22', 'event' => 'Suspicious redirect attempt blocked', 'source' => '10.0.0.7', 'risk' => 'high'],
  ['time' => '2026-05-22 02:12:00', 'event' => 'Session fixation detected', 'source' => '10.0.0.7', 'risk' => 'critical'],
];

// ─── Multi-Step Vulnerable Logic ───────────────────────────────────────
$step_result = null;
$current_step = $_SESSION['csrf_case8_step'];
$show_flag = false;

// Step 0: Initial state - user must be tricked into Step 1
// Step 1: Open Redirect exploitation (via vulnerable redirect parameter)
// Step 2: Session fixation (attacker sets known session ID)
// Step 3: XSS payload execution (stored in profile via CSRF)
// Step 4: Account takeover via chained CSRF + XSS

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {

  // VULNERABLE STEP 1: Open Redirect
  if (isset($_GET['redirect'])) {
    $redirect_url = $_GET['redirect'];
    // VULNERABLE: Weak validation - only checks if string contains allowed domain
    $allowed = false;
    foreach ($sso_config['allowed_redirects'] as $allowed_url) {
      if (strpos($redirect_url, $allowed_url) !== false || strpos($redirect_url, 'darkhunter.local') !== false) {
        $allowed = true;
        break;
      }
    }
    // VULNERABLE: Also allows any URL containing "callback" or "auth"
    if (strpos($redirect_url, 'callback') !== false || strpos($redirect_url, 'auth') !== false) {
      $allowed = true;
    }

    if ($allowed) {
      $step_result = [
        'step' => 1,
        'message' => 'Redirect authorized to: ' . htmlspecialchars($redirect_url),
        'warning' => 'Open redirect vulnerability detected!'
      ];
      $_SESSION['csrf_case8_step'] = max($_SESSION['csrf_case8_step'], 1);
    }
  }

  // VULNERABLE STEP 2: Session Fixation via CSRF
  if (isset($_GET['fixate_session']) && isset($_GET['session_id'])) {
    // VULNERABLE: Allows setting session ID via GET parameter
    $new_session_id = $_GET['session_id'];
    session_id($new_session_id);
    $step_result = [
      'step' => 2,
      'message' => 'Session ID fixed to: ' . htmlspecialchars(substr($new_session_id, 0, 16)) . '...',
      'warning' => 'Session fixation vulnerability! Attacker now knows the session ID.'
    ];
    $_SESSION['csrf_case8_step'] = max($_SESSION['csrf_case8_step'], 2);
  }

  // VULNERABLE STEP 3: Profile Update via CSRF (no token)
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // VULNERABLE: No CSRF token on profile update
    $bio = $_POST['bio'] ?? '';
    $step_result = [
      'step' => 3,
      'message' => 'Profile updated successfully. Bio set to: ' . htmlspecialchars(substr($bio, 0, 50)) . '...',
      'warning' => 'CSRF on profile update! XSS payload can be stored in bio field.'
    ];
    $_SESSION['csrf_case8_step'] = max($_SESSION['csrf_case8_step'], 3);

    // Check if XSS payload was stored
    if (strpos($bio, '<script') !== false || strpos($bio, 'javascript:') !== false) {
      $step_result['xss_detected'] = true;
    }
  }

  // VULNERABLE STEP 4: Privilege Escalation via chained attack
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['escalate_privileges'])) {
    // VULNERABLE: No CSRF token, can be triggered after XSS execution
    $step_result = [
      'step' => 4,
      'message' => '🔓 PRIVILEGE ESCALATION SUCCESSFUL - Multi-step chain complete!',
      'flag' => 'DH{csrf_multistep_chain_master}'
    ];
    $show_flag = true;
    $_SESSION['csrf_case8_step'] = 4;
  }

  // VULNERABLE: Direct flag reveal if all steps completed
  if (isset($_GET['complete_chain']) && $_SESSION['csrf_case8_step'] >= 3) {
    $show_flag = true;
    $step_result = [
      'step' => 4,
      'message' => 'Chain completed! All vulnerabilities exploited successfully.',
      'flag' => 'DH{csrf_multistep_chain_master}'
    ];
    $_SESSION['csrf_case8_step'] = 4;
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['csrf_case8_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['csrf_case8_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['csrf_case8_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful execution! You've successfully chained multiple vulnerabilities: Open Redirect → Session Fixation → Stored XSS via CSRF → Privilege Escalation. This multi-step attack demonstrates how CSRF can be combined with other weaknesses to achieve full account takeover!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['view']) || isset($_GET['tab']) || isset($_GET['redirect']))) {
  $_SESSION['csrf_case8_attempts']++;
}

$attempts = $_SESSION['csrf_case8_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSO Admin Console - CSRF Case 8 (Multi-Step Chain)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-link"></i> SSO Admin Console - Multi-Step Chain</h1>
      <p class="lab-description">Manage the DarkHunter Single Sign-On system. This expert-level CSRF challenge requires
        chaining multiple vulnerabilities: Open Redirect → Session Fixation → Stored XSS → Account Takeover. <strong>No
          CSRF protection across the entire chain!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this multi-step chain vulnerability. You can continue exploring, but no additional
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

    <!-- Progress Tracker -->
    <div class="progress-tracker">
      <div
        class="progress-step <?php echo $current_step >= 1 ? 'completed' : ($current_step === 0 ? 'active' : ''); ?>">
        <div class="step-number">1</div>
        <span class="step-label">Open Redirect</span>
      </div>
      <div class="progress-connector"></div>
      <div
        class="progress-step <?php echo $current_step >= 2 ? 'completed' : ($current_step === 1 ? 'active' : ''); ?>">
        <div class="step-number">2</div>
        <span class="step-label">Session Fixation</span>
      </div>
      <div class="progress-connector"></div>
      <div
        class="progress-step <?php echo $current_step >= 3 ? 'completed' : ($current_step === 2 ? 'active' : ''); ?>">
        <div class="step-number">3</div>
        <span class="step-label">Stored XSS via CSRF</span>
      </div>
      <div class="progress-connector"></div>
      <div
        class="progress-step <?php echo $current_step >= 4 ? 'completed' : ($current_step === 3 ? 'active' : ''); ?>">
        <div class="step-number">4</div>
        <span class="step-label">Account Takeover</span>
      </div>
    </div>

    <?php if ($step_result): ?>
    <div class="step-result <?php echo isset($step_result['warning']) ? 'warning' : 'success'; ?>">
      <i class="fas fa-<?php echo isset($step_result['warning']) ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
      <div class="result-content">
        <strong>Step <?php echo $step_result['step']; ?> Result:</strong>
        <span><?php echo $step_result['message']; ?></span>
        <?php if (isset($step_result['warning'])): ?>
        <span class="warning-text"><?php echo $step_result['warning']; ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- SSO Console Grid -->
    <div class="sso-console">

      <!-- System Overview -->
      <div class="console-card">
        <div class="card-header">
          <i class="fas fa-shield-alt"></i>
          <h3>SSO Configuration</h3>
        </div>
        <div class="config-grid">
          <div class="config-item">
            <span class="config-label">Provider</span>
            <span class="config-value"><?php echo $sso_config['provider']; ?></span>
          </div>
          <div class="config-item">
            <span class="config-label">Version</span>
            <span class="config-value"><?php echo $sso_config['version']; ?></span>
          </div>
          <div class="config-item">
            <span class="config-label">Auth Method</span>
            <span class="config-value"><?php echo $sso_config['auth_method']; ?></span>
          </div>
          <div class="config-item">
            <span class="config-label">MFA Required</span>
            <span class="config-value <?php echo $sso_config['mfa_required'] ? 'status-success' : 'status-danger'; ?>">
              <?php echo $sso_config['mfa_required'] ? 'YES' : 'NO'; ?>
            </span>
          </div>
          <div class="config-item">
            <span class="config-label">Session Duration</span>
            <span class="config-value"><?php echo $sso_config['session_duration']; ?>s</span>
          </div>
        </div>
      </div>

      <!-- Step 1: Open Redirect (Vulnerable) -->
      <div class="console-card step-card <?php echo $current_step >= 1 ? 'completed' : 'active'; ?>">
        <div class="card-header">
          <i class="fas fa-route"></i>
          <h3>Step 1: OAuth Redirect Handler</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Open Redirect</span>
        </div>
        <div class="step-content">
          <p class="step-description">Handle OAuth callback redirects after authentication.</p>
          <form method="GET" action="" class="redirect-form">
            <div class="form-group">
              <label>Redirect URL</label>
              <input type="url" name="redirect" placeholder="https://darkhunter.local/callback" class="form-input">
            </div>
            <div class="allowed-redirects">
              <span class="label">Allowed Patterns:</span>
              <?php foreach ($sso_config['allowed_redirects'] as $url): ?>
              <code class="redirect-pattern"><?php echo $url; ?></code>
              <?php endforeach; ?>
              <code class="redirect-pattern vuln">*callback*</code>
              <code class="redirect-pattern vuln">*auth*</code>
            </div>
            <button type="submit" class="btn-action">
              <i class="fas fa-external-link-alt"></i> Process Redirect
            </button>
          </form>
        </div>
      </div>

      <!-- Step 2: Session Fixation (Vulnerable) -->
      <div
        class="console-card step-card <?php echo $current_step >= 2 ? 'completed' : ($current_step >= 1 ? 'active' : ''); ?>">
        <div class="card-header">
          <i class="fas fa-fingerprint"></i>
          <h3>Step 2: Session Management</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Session Fixation</span>
        </div>
        <div class="step-content">
          <p class="step-description">Manage user sessions and session identifiers.</p>
          <form method="GET" action="" class="session-form">
            <div class="form-row">
              <div class="form-group">
                <label>Session ID</label>
                <input type="text" name="session_id" placeholder="Enter session ID..." class="form-input">
              </div>
              <div class="form-group">
                <label>Action</label>
                <select name="fixate_session" class="form-select">
                  <option value="1">Fixate Session</option>
                  <option value="0">Regenerate ID</option>
                </select>
              </div>
            </div>
            <div class="session-info">
              <span>Current Session: <code><?php echo substr(session_id(), 0, 16); ?>...</code></span>
            </div>
            <button type="submit" class="btn-action">
              <i class="fas fa-cog"></i> Manage Session
            </button>
          </form>
        </div>
      </div>

      <!-- Step 3: Profile Update via CSRF (Vulnerable) -->
      <div
        class="console-card step-card <?php echo $current_step >= 3 ? 'completed' : ($current_step >= 2 ? 'active' : ''); ?>">
        <div class="card-header">
          <i class="fas fa-user-edit"></i>
          <h3>Step 3: Profile Management</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> CSRF + XSS</span>
        </div>
        <div class="step-content">
          <p class="step-description">Update user profile information. Bio field supports HTML formatting.</p>
          <form method="POST" action="" class="profile-form">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-group">
              <label>Bio / Description</label>
              <textarea name="bio" rows="4" placeholder="Enter your bio... Supports HTML!"
                class="form-textarea"></textarea>
            </div>
            <div class="form-group">
              <label>Display Name</label>
              <input type="text" name="display_name" placeholder="Your display name" class="form-input">
            </div>
            <button type="submit" class="btn-action">
              <i class="fas fa-save"></i> Update Profile
            </button>
          </form>
        </div>
      </div>

      <!-- Step 4: Privilege Escalation (Target) -->
      <div
        class="console-card step-card <?php echo $current_step >= 4 ? 'completed' : ($current_step >= 3 ? 'active' : ''); ?>">
        <div class="card-header">
          <i class="fas fa-crown"></i>
          <h3>Step 4: Privilege Escalation</h3>
          <span class="secret-badge"><i class="fas fa-lock"></i> Final Target</span>
        </div>
        <div class="step-content">
          <?php if ($show_flag): ?>
          <div class="takeover-success">
            <div class="flag-display">
              <i class="fas fa-flag"></i>
              <code>DH{csrf_multistep_chain_master}</code>
            </div>
            <p class="takeover-message">Account takeover complete! All security controls bypassed through multi-step
              chaining.</p>
          </div>
          <?php else: ?>
          <p class="step-description">Elevate user privileges to Super Admin. Requires completing previous steps.</p>
          <form method="POST" action="" class="escalate-form">
            <input type="hidden" name="escalate_privileges" value="1">
            <div class="privilege-options">
              <label class="privilege-option">
                <input type="radio" name="target_role" value="Super Admin" checked>
                <span class="option-label">Super Admin</span>
                <span class="option-desc">Full system access</span>
              </label>
              <label class="privilege-option">
                <input type="radio" name="target_role" value="Security Lead">
                <span class="option-label">Security Lead</span>
                <span class="option-desc">Security operations</span>
              </label>
            </div>
            <button type="submit" class="btn-action btn-danger">
              <i class="fas fa-arrow-up"></i> Escalate Privileges
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>

      <!-- User Directory -->
      <div class="console-card users-card">
        <div class="card-header">
          <i class="fas fa-users"></i>
          <h3>SSO User Directory</h3>
        </div>
        <div class="users-list">
          <?php foreach ($users_sso as $user): ?>
          <div class="user-item">
            <div class="user-avatar">
              <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="user-info">
              <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
              <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="user-badges">
              <span
                class="role-badge <?php echo strtolower(str_replace(' ', '-', $user['role'])); ?>"><?php echo $user['role']; ?></span>
              <span class="mfa-badge <?php echo $user['mfa_enabled'] ? 'enabled' : 'disabled'; ?>">
                <i class="fas fa-<?php echo $user['mfa_enabled'] ? 'lock' : 'lock-open'; ?>"></i>
                <?php echo $user['mfa_enabled'] ? 'MFA On' : 'MFA Off'; ?>
              </span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Audit Trail -->
      <div class="console-card audit-card">
        <div class="card-header">
          <i class="fas fa-clipboard-check"></i>
          <h3>Security Audit Trail</h3>
        </div>
        <div class="audit-list">
          <?php foreach ($audit_trail as $audit): ?>
          <div class="audit-item risk-<?php echo $audit['risk']; ?>">
            <div class="audit-time"><?php echo $audit['time']; ?></div>
            <div class="audit-event"><?php echo htmlspecialchars($audit['event']); ?></div>
            <div class="audit-meta">
              <span class="audit-source"><i class="fas fa-network-wired"></i> <?php echo $audit['source']; ?></span>
              <span class="risk-badge <?php echo $audit['risk']; ?>"><?php echo strtoupper($audit['risk']); ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Chain Analysis Panel -->
    <div class="debug-panel chain-panel">
      <div class="debug-header">
        <i class="fas fa-project-diagram"></i>
        <span>Attack Chain Analysis</span>
      </div>
      <div class="debug-body">
        <div class="chain-flow">
          <div class="chain-node <?php echo $current_step >= 1 ? 'exploited' : ''; ?>">
            <i class="fas fa-route"></i>
            <span>Open Redirect</span>
            <code>?redirect=evil.com/callback</code>
          </div>
          <i class="fas fa-arrow-right chain-arrow"></i>
          <div class="chain-node <?php echo $current_step >= 2 ? 'exploited' : ''; ?>">
            <i class="fas fa-fingerprint"></i>
            <span>Session Fixation</span>
            <code>?session_id=ATTACKER_KNOWN</code>
          </div>
          <i class="fas fa-arrow-right chain-arrow"></i>
          <div class="chain-node <?php echo $current_step >= 3 ? 'exploited' : ''; ?>">
            <i class="fas fa-bug"></i>
            <span>Stored XSS</span>
            <code>&lt;script&gt;fetch(...) &lt;/script&gt;</code>
          </div>
          <i class="fas fa-arrow-right chain-arrow"></i>
          <div class="chain-node <?php echo $current_step >= 4 ? 'exploited' : ''; ?>">
            <i class="fas fa-crown"></i>
            <span>Account Takeover</span>
            <code>escalate_privileges=1</code>
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
          <span>Current Step: <?php echo $current_step; ?>/4</span>
          <span>Session ID: <?php echo substr(session_id(), 0, 16); ?>...</span>
          <span>Query String: <?php echo $_SERVER['QUERY_STRING'] ?: 'None'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This lab requires chaining 4 vulnerabilities. Start with the Open Redirect - notice the
        redirect validation only checks if the URL contains "callback" or "auth". You can bypass this with
        <code>?redirect=https://evil.com/callback</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">After the redirect, use session fixation to set a known session ID. Then update the profile
        with an XSS payload in the bio field. The stored XSS will execute when the admin views your profile, triggering
        the privilege escalation CSRF.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Complete chain: 1) <code>?redirect=https://attacker.com/callback</code> → 2)
        <code>?fixate_session=1&session_id=ATTACKER_CONTROLLED</code> → 3) POST with
        <code>bio=&lt;script&gt;fetch('CSRF-CASE8.php',{method:'POST',body:'escalate_privileges=1'})&lt;/script&gt;</code>
        → 4) Wait for admin to view profile. The XSS triggers the CSRF automatically!
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
    const takeoverSuccess = document.querySelector('.takeover-success');
    const alreadySolved = document.querySelector('.solved-banner');

    if (takeoverSuccess && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>