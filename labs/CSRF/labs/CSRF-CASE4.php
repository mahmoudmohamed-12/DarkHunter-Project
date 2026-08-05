<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_medium4_attempts'])) {
  $_SESSION['csrf_medium4_attempts'] = 0;
}
if (!isset($_SESSION['csrf_medium4_solved'])) {
  $_SESSION['csrf_medium4_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'api_keys' => ['sk-admin-prod-1', 'sk-admin-dev-1']],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'Security Analyst', 'api_keys' => ['sk-alice-prod-1']],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'Developer', 'api_keys' => ['sk-bob-dev-1', 'sk-bob-test-1']],
];

$current_user_id = isset($_SESSION['lab_user_id']) ? $_SESSION['lab_user_id'] : 2;
$current_user = $users[$current_user_id];

// ─── VULNERABLE: Token Reuse Logic ────────────────────────────────────────
// The application uses the SAME token across all sessions and it never expires!
function getReusableToken()
{
  // VULNERABLE: Static token that never changes and is shared across all users!
  return 'dh-csrf-token-2024-static';
}

function validateReusableToken($token)
{
  return $token === getReusableToken();
}

$csrf_token = getReusableToken();

// ─── Vulnerable Logic ─────────────────────────────────────────────────────
$success_msg = null;
$error_msg = null;
$already_solved = $_SESSION['csrf_medium4_solved'];
$key_revoked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $_SESSION['csrf_medium4_attempts']++;

  $submitted_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (validateReusableToken($submitted_token)) {
    $action = $_POST['action'];

    switch ($action) {
      case 'revoke_key':
        $key_index = isset($_POST['key_index']) ? (int)$_POST['key_index'] : 0;
        if (isset($current_user['api_keys'][$key_index])) {
          $revoked_key = $current_user['api_keys'][$key_index];
          array_splice($users[$current_user_id]['api_keys'], $key_index, 1);
          $current_user = $users[$current_user_id];
          $key_revoked = true;

          // Detect CSRF exploitation: revoking someone else's key or all keys
          if (count($current_user['api_keys']) === 0 && !$already_solved) {
            solveLab($pdo, $lab['id']);
            $_SESSION['csrf_medium4_solved'] = true;
            $already_solved = true;
            $success_msg = "VULNERABILITY EXPLOITED! You successfully exploited CSRF Token Reuse. The token is static, never expires, and shared across all users/sessions. An attacker who captures this token once can reuse it indefinitely across different sessions and endpoints.";
          }
        }
        break;

      case 'generate_key':
        $new_key = 'sk-' . $current_user['username'] . '-' . uniqid();
        $users[$current_user_id]['api_keys'][] = $new_key;
        $current_user = $users[$current_user_id];
        $key_revoked = true; // Using same flag for notification
        break;
    }
  } else {
    $error_msg = "Invalid CSRF token. The reusable token validation failed.";
  }
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['view'])) {
  $_SESSION['csrf_medium4_attempts']++;
}

$attempts = $_SESSION['csrf_medium4_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Key Manager - CSRF Medium 4</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-key"></i> API Key Manager</h1>
      <p class="lab-description">Manage your API credentials. This medium-difficulty CSRF challenge uses a token, but
        the token is static, never expires, and shared across all users. Capture it once, use it forever. <strong>Token
          reuse vulnerability!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this token reuse CSRF vulnerability. You can continue exploring, but no additional
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

    <?php if ($error_msg): ?>
    <div class="error-alert">
      <i class="fas fa-exclamation-triangle"></i>
      <div class="error-content">
        <h3>Error</h3>
        <p><?php echo $error_msg; ?></p>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($key_revoked && !$success_msg): ?>
    <div class="info-alert">
      <i class="fas fa-info-circle"></i>
      <div class="info-content">
        <h3>Action Completed</h3>
        <p>API key operation completed successfully.</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- User Info Bar -->
    <div class="user-info-bar">
      <div class="user-info-item">
        <i class="fas fa-user"></i>
        <span>User: <strong><?php echo htmlspecialchars($current_user['username']); ?></strong></span>
      </div>
      <div class="user-info-item">
        <i class="fas fa-id-badge"></i>
        <span>Role: <strong><?php echo htmlspecialchars($current_user['role']); ?></strong></span>
      </div>
    </div>

    <!-- Token Display Panel -->
    <div class="token-panel">
      <div class="token-panel-header">
        <i class="fas fa-shield-halved"></i>
        <h3>CSRF Token Inspector</h3>
        <span class="vuln-badge reuse"><i class="fas fa-recycle"></i> Reusable Token</span>
      </div>
      <div class="token-panel-body">
        <div class="token-display-box">
          <label>Current Token Value</label>
          <code class="token-value"><?php echo $csrf_token; ?></code>
        </div>
        <div class="token-properties">
          <div class="token-prop">
            <span class="prop-name">Expires</span>
            <span class="prop-value danger">Never</span>
          </div>
          <div class="token-prop">
            <span class="prop-name">Per-Session</span>
            <span class="prop-value danger">No</span>
          </div>
          <div class="token-prop">
            <span class="prop-name">Per-User</span>
            <span class="prop-value danger">No</span>
          </div>
          <div class="token-prop">
            <span class="prop-name">Randomness</span>
            <span class="prop-value danger">None</span>
          </div>
        </div>
      </div>
    </div>

    <!-- API Keys Table -->
    <div class="keys-card">
      <div class="keys-header">
        <i class="fas fa-key"></i>
        <h3>Active API Keys</h3>
        <span class="key-count"><?php echo count($current_user['api_keys']); ?> keys</span>
      </div>

      <div class="keys-list">
        <?php foreach ($current_user['api_keys'] as $index => $key): ?>
        <div class="key-item">
          <div class="key-info">
            <i class="fas fa-key"></i>
            <code class="key-value"><?php echo htmlspecialchars($key); ?></code>
          </div>
          <form method="POST" action="" class="key-action-form">
            <input type="hidden" name="action" value="revoke_key">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="key_index" value="<?php echo $index; ?>">
            <button type="submit" class="revoke-btn">
              <i class="fas fa-trash"></i> Revoke
            </button>
          </form>
        </div>
        <?php endforeach; ?>

        <?php if (empty($current_user['api_keys'])): ?>
        <div class="no-keys">
          <i class="fas fa-exclamation-circle"></i>
          <p>No active API keys found.</p>
        </div>
        <?php endif; ?>
      </div>

      <form method="POST" action="" class="generate-form">
        <input type="hidden" name="action" value="generate_key">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <button type="submit" class="generate-btn">
          <i class="fas fa-plus"></i> Generate New API Key
        </button>
      </form>
    </div>

    <!-- Attack Scenario Panel -->
    <div class="attack-panel">
      <div class="attack-header">
        <i class="fas fa-crosshairs"></i>
        <h3>Token Reuse Attack Scenario</h3>
      </div>
      <div class="attack-body">
        <div class="attack-timeline">
          <div class="timeline-item">
            <div class="timeline-dot">1</div>
            <div class="timeline-content">
              <h4>Attacker Visits Site</h4>
              <p>Attacker creates an account or visits any page and extracts the static token:
                <code>dh-csrf-token-2024-static</code>
              </p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot">2</div>
            <div class="timeline-content">
              <h4>Token Never Changes</h4>
              <p>The token remains the same across all sessions, users, and time periods. It's hardcoded in the
                application.</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot">3</div>
            <div class="timeline-content">
              <h4>Attacker Crafts Payload</h4>
              <p>Using the known token, attacker creates a malicious form that revokes all victim's API keys:</p>
              <pre class="attack-code"><code>&lt;form action="http://target.com/CSRF-CASE4.php" method="POST"&gt;
  &lt;input type="hidden" name="action" value="revoke_key"&gt;
  &lt;input type="hidden" name="csrf_token" 
         value="dh-csrf-token-2024-static"&gt;
  &lt;input type="hidden" name="key_index" value="0"&gt;
&lt;/form&gt;
&lt;script&gt;document.forms[0].submit();&lt;/script&gt;</code></pre>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot">4</div>
            <div class="timeline-content">
              <h4>Victim's Keys Revoked</h4>
              <p>When victim visits the attacker's page, all their API keys are silently revoked, causing denial of
                service.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Look at the CSRF token value. Does it change when you refresh the page? Does it change when
        you log out and back in? A proper CSRF token should be unique per session and unpredictable.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The token appears to be a static string. If an attacker knows this token, they can forge
        requests from any origin. Try to revoke all API keys using the same token - the application doesn't check if the
        token belongs to the current session.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">The token is literally hardcoded as <code>dh-csrf-token-2024-static</code>. You can use
        this exact token in any forged request. To solve the challenge, revoke all API keys by submitting multiple
        revoke requests with this static token until no keys remain. The vulnerability is that tokens should be unique
        per session and rotated regularly!</div>
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