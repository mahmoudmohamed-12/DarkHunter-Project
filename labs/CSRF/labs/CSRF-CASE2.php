<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_easy2_attempts'])) {
  $_SESSION['csrf_easy2_attempts'] = 0;
}
if (!isset($_SESSION['csrf_easy2_solved'])) {
  $_SESSION['csrf_easy2_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'status' => 'active', 'api_key' => 'sk-dh-admin-7f8a9b2c3d4e5f6a'],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'Security Analyst', 'status' => 'active', 'api_key' => 'sk-dh-alice-1a2b3c4d5e6f7g8h'],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'Developer', 'status' => 'active', 'api_key' => 'sk-dh-bob-9i8u7y6t5r4e3w2q'],
  4 => ['id' => 4, 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'role' => 'Penetration Tester', 'status' => 'suspended', 'api_key' => 'sk-dh-charlie-0p9o8i7u6y5t'],
  5 => ['id' => 5, 'username' => 'dave', 'email' => 'dave@darkhunter.local', 'role' => 'DevOps Engineer', 'status' => 'active', 'api_key' => 'sk-dh-dave-1q2w3e4r5t6y7u8i'],
];

$current_user_id = isset($_SESSION['lab_user_id']) ? $_SESSION['lab_user_id'] : 2;
$current_user = $users[$current_user_id];

// ─── Vulnerable CSRF Logic: GET-based State Changes ───────────────────────
$success_msg = null;
$already_solved = $_SESSION['csrf_easy2_solved'];
$action_performed = null;

// VULNERABLE: State-changing actions via GET requests!
if (isset($_GET['action'])) {
  $_SESSION['csrf_easy2_attempts']++;

  $action = $_GET['action'];
  $target_user = isset($_GET['user']) ? (int)$_GET['user'] : $current_user_id;

  switch ($action) {
    case 'delete':
      if (isset($users[$target_user])) {
        $action_performed = "User '{$users[$target_user]['username']}' has been deleted.";
        $users[$target_user]['status'] = 'deleted';
      }
      break;

    case 'promote':
      if (isset($users[$target_user]) && $target_user !== 1) {
        $users[$target_user]['role'] = 'Administrator';
        $action_performed = "User '{$users[$target_user]['username']}' has been promoted to Administrator.";

        // CSRF Exploitation Detection: If admin promotes themselves or another user via GET
        if (!$already_solved && $target_user !== $current_user_id) {
          solveLab($pdo, $lab['id']);
          $_SESSION['csrf_easy2_solved'] = true;
          $already_solved = true;
          $success_msg = "CRITICAL VULNERABILITY EXPLOITED! You successfully performed a GET-based Cross-Site Request Forgery attack. State-changing operations (user promotion) were executed via a simple GET request without any CSRF protection, allowing attackers to embed malicious URLs in image tags or links.";
        }
      }
      break;

    case 'suspend':
      if (isset($users[$target_user])) {
        $users[$target_user]['status'] = 'suspended';
        $action_performed = "User '{$users[$target_user]['username']}' has been suspended.";
      }
      break;

    case 'reset_api':
      if (isset($users[$target_user])) {
        $new_key = 'sk-dh-' . $users[$target_user]['username'] . '-' . bin2hex(random_bytes(8));
        $users[$target_user]['api_key'] = $new_key;
        $action_performed = "API key for '{$users[$target_user]['username']}' has been reset.";
      }
      break;
  }
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['action'])) {
  $_SESSION['csrf_easy2_attempts']++;
}

$attempts = $_SESSION['csrf_easy2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management Panel - CSRF Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-users-cog"></i> User Management Panel</h1>
      <p class="lab-description">Manage user accounts and permissions. This CSRF challenge exposes a critical
        vulnerability: all administrative actions (delete, promote, suspend, reset API keys) are performed via GET
        requests without any CSRF token validation. <strong>State-changing operations via GET!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this GET-based CSRF vulnerability. You can continue exploring, but no additional
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

    <?php if ($action_performed && !$success_msg): ?>
    <div class="info-alert">
      <i class="fas fa-info-circle"></i>
      <div class="info-content">
        <h3>Action Performed</h3>
        <p><?php echo $action_performed; ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Current User Info -->
    <div class="user-info-bar">
      <div class="user-info-item">
        <i class="fas fa-user"></i>
        <span>Logged in as: <strong><?php echo htmlspecialchars($current_user['username']); ?></strong></span>
      </div>
      <div class="user-info-item">
        <i class="fas fa-id-badge"></i>
        <span>Role: <strong><?php echo htmlspecialchars($current_user['role']); ?></strong></span>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-card">
      <div class="table-header">
        <i class="fas fa-users"></i>
        <h3>System Users</h3>
        <span class="vuln-badge"><i class="fas fa-unlock"></i> GET Actions - No CSRF</span>
      </div>

      <div class="table-responsive">
        <table class="users-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr class="<?php echo $user['status'] === 'deleted' ? 'deleted-row' : ''; ?>">
              <td class="mono"><?php echo $user['id']; ?></td>
              <td>
                <div class="user-cell">
                  <div class="user-avatar-small"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                  <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
              </td>
              <td class="mono"><?php echo htmlspecialchars($user['email']); ?></td>
              <td>
                <span class="role-badge <?php echo $user['role'] === 'Administrator' ? 'role-admin' : 'role-user'; ?>">
                  <?php echo $user['role']; ?>
                </span>
              </td>
              <td>
                <span class="status-badge status-<?php echo $user['status']; ?>">
                  <i class="fas fa-circle"></i>
                  <?php echo ucfirst($user['status']); ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <!-- VULNERABLE: All these are GET links that change state! -->
                  <a href="?action=promote&user=<?php echo $user['id']; ?>" class="action-btn promote"
                    title="Promote to Admin">
                    <i class="fas fa-arrow-up"></i>
                  </a>
                  <a href="?action=suspend&user=<?php echo $user['id']; ?>" class="action-btn suspend"
                    title="Suspend User">
                    <i class="fas fa-ban"></i>
                  </a>
                  <a href="?action=reset_api&user=<?php echo $user['id']; ?>" class="action-btn reset"
                    title="Reset API Key">
                    <i class="fas fa-key"></i>
                  </a>
                  <a href="?action=delete&user=<?php echo $user['id']; ?>" class="action-btn delete"
                    title="Delete User">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Attack Vector Demo -->
    <div class="attack-demo-card">
      <div class="attack-demo-header">
        <i class="fas fa-crosshairs"></i>
        <h3>Attack Vector Demonstration</h3>
      </div>
      <div class="attack-demo-body">
        <p class="attack-desc">An attacker can embed these malicious URLs anywhere. When an authenticated admin visits
          the page, the browser automatically sends the request with cookies:</p>

        <div class="attack-examples">
          <div class="attack-example">
            <h4><i class="fas fa-image"></i> Hidden Image Tag</h4>
            <pre
              class="attack-code"><code>&lt;img src="http://target.com/CSRF-CASE2.php?action=promote&user=2" width="0" height="0"&gt;</code></pre>
            <p class="attack-note">The browser loads the image URL, sending cookies. The server processes the promotion
              without knowing it wasn't intentional.</p>
          </div>

          <div class="attack-example">
            <h4><i class="fas fa-link"></i> Disguised Link</h4>
            <pre
              class="attack-code"><code>&lt;a href="http://target.com/CSRF-CASE2.php?action=delete&user=3"&gt;View Cute Cats!&lt;/a&gt;</code></pre>
            <p class="attack-note">Social engineering trick: user clicks what looks like a fun link, but actually
              deletes a user account.</p>
          </div>

          <div class="attack-example">
            <h4><i class="fas fa-envelope"></i> Email HTML</h4>
            <pre class="attack-code"><code>&lt;!-- In an email HTML body --&gt;
&lt;img src="http://target.com/CSRF-CASE2.php?action=reset_api&user=1"&gt;</code></pre>
            <p class="attack-note">Many email clients load images automatically. The admin's API key gets reset when
              they open the email.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Request Analysis Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Security Analysis</span>
      </div>
      <div class="debug-body">
        <div class="security-checks">
          <div class="check-item fail">
            <i class="fas fa-times-circle"></i>
            <div class="check-content">
              <strong>HTTP Method Violation</strong>
              <span>State-changing actions use GET instead of POST</span>
            </div>
          </div>
          <div class="check-item fail">
            <i class="fas fa-times-circle"></i>
            <div class="check-content">
              <strong>Missing CSRF Token</strong>
              <span>No anti-CSRF token on any action</span>
            </div>
          </div>
          <div class="check-item fail">
            <i class="fas fa-times-circle"></i>
            <div class="check-content">
              <strong>No Origin Validation</strong>
              <span>Server does not check Referer or Origin headers</span>
            </div>
          </div>
          <div class="check-item fail">
            <i class="fas fa-times-circle"></i>
            <div class="check-content">
              <strong>SameSite Cookie Not Set</strong>
              <span>Cookies sent with all cross-site requests</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Notice that all user management actions (promote, suspend, delete, reset API) are triggered
        by simple <code>&lt;a href&gt;</code> links with GET parameters. The server processes these without any
        confirmation.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">GET requests can be triggered without user interaction through <code>&lt;img&gt;</code>
        tags, CSS backgrounds, or iframes. An attacker only needs the victim to visit a page containing the malicious
        URL.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">To exploit this vulnerability, trigger a GET request that promotes a user to Administrator.
        Try visiting <code>?action=promote&user=2</code> directly, or simulate an attacker embedding this URL in an
        image tag on another site. The challenge is solved when a user is promoted via a GET request!</div>
    </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>
</body>

</html>