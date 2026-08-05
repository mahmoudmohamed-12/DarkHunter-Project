<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_medium3_attempts'])) {
  $_SESSION['csrf_medium3_attempts'] = 0;
}
if (!isset($_SESSION['csrf_medium3_solved'])) {
  $_SESSION['csrf_medium3_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'backup_email' => 'admin-backup@darkhunter.local'],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'Security Analyst', 'backup_email' => 'alice-personal@gmail.com'],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'Developer', 'backup_email' => 'bob-dev@protonmail.com'],
];

$current_user_id = isset($_SESSION['lab_user_id']) ? $_SESSION['lab_user_id'] : 2;
$current_user = $users[$current_user_id];

// ─── WEAK CSRF TOKEN GENERATION ─────────────────────────────────────────
// VULNERABLE: Token is based on user_id and timestamp - predictable!
function generateWeakToken($user_id)
{
  // Weak token: user_id + current hour + static salt
  $hour = date('H');
  $token = md5($user_id . $hour . 'darkhunter2024');
  return substr($token, 0, 8); // Only 8 characters - easily brute-forced
}

function validateWeakToken($token, $user_id)
{
  $expected = generateWeakToken($user_id);
  return $token === $expected;
}

$csrf_token = generateWeakToken($current_user_id);

// ─── Vulnerable Logic ─────────────────────────────────────────────────────
$success_msg = null;
$error_msg = null;
$already_solved = $_SESSION['csrf_medium3_solved'];
$email_changed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_email') {
  $_SESSION['csrf_medium3_attempts']++;

  $submitted_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  // VULNERABLE: Weak token validation - can be guessed or brute-forced!
  if (validateWeakToken($submitted_token, $current_user_id)) {
    $new_email = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';

    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
      $users[$current_user_id]['email'] = $new_email;
      $current_user = $users[$current_user_id];
      $email_changed = true;

      // Check for CSRF exploitation: email changed to attacker domain
      if (strpos($new_email, '@evil.com') !== false || strpos($new_email, '@attacker') !== false) {
        if (!$already_solved) {
          solveLab($pdo, $lab['id']);
          $_SESSION['csrf_medium3_solved'] = true;
          $already_solved = true;
          $success_msg = "VULNERABILITY EXPLOITED! You bypassed weak CSRF token protection. The token was predictable (based on user_id + hour + static salt) and only 8 characters long, making it susceptible to brute-force attacks.";
        }
      }
    } else {
      $error_msg = "Invalid email format.";
    }
  } else {
    $error_msg = "Invalid CSRF token. Token validation failed.";
  }
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['view'])) {
  $_SESSION['csrf_medium3_attempts']++;
}

$attempts = $_SESSION['csrf_medium3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Security - CSRF Medium 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-envelope-circle-check"></i> Email Change Portal</h1>
      <p class="lab-description">Update your primary email address. This medium-difficulty CSRF challenge implements
        token protection, but the token generation is weak and predictable. Analyze the token pattern and exploit the
        weakness. <strong>Weak token generation algorithm!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this weak token CSRF vulnerability. You can continue exploring, but no additional
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

    <?php if ($email_changed && !$success_msg): ?>
    <div class="info-alert">
      <i class="fas fa-info-circle"></i>
      <div class="info-content">
        <h3>Email Updated</h3>
        <p>Your primary email address has been changed successfully.</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Account Info Card -->
    <div class="account-card">
      <div class="account-header">
        <div class="account-avatar"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></div>
        <div class="account-details">
          <h2><?php echo htmlspecialchars($current_user['username']); ?></h2>
          <span
            class="account-role <?php echo $current_user['role'] === 'Administrator' ? 'role-admin' : 'role-user'; ?>">
            <?php echo $current_user['role']; ?>
          </span>
        </div>
      </div>
      <div class="account-info">
        <div class="info-row">
          <label>Primary Email</label>
          <value><?php echo htmlspecialchars($current_user['email']); ?></value>
        </div>
        <div class="info-row">
          <label>Backup Email</label>
          <value><?php echo htmlspecialchars($current_user['backup_email']); ?></value>
        </div>
      </div>
    </div>

    <!-- Email Change Form (With WEAK Token) -->
    <div class="form-card">
      <div class="form-header">
        <i class="fas fa-pen-to-square"></i>
        <h3>Change Primary Email</h3>
        <span class="vuln-badge weak"><i class="fas fa-unlock-alt"></i> Weak Token</span>
      </div>

      <form method="POST" action="" id="email-form">
        <input type="hidden" name="action" value="change_email">

        <!-- WEAK CSRF TOKEN - Can be predicted! -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" id="csrf-token-field">

        <div class="token-display">
          <label><i class="fas fa-shield-halved"></i> Current CSRF Token</label>
          <code class="token-value"><?php echo $csrf_token; ?></code>
          <span class="token-warning"><i class="fas fa-triangle-exclamation"></i> Analyze this token!</span>
        </div>

        <div class="form-group">
          <label for="new_email"><i class="fas fa-envelope"></i> New Email Address</label>
          <input type="email" id="new_email" name="new_email" placeholder="Enter new email address" class="form-input"
            required>
        </div>

        <button type="submit" class="submit-btn">
          <i class="fas fa-paper-plane"></i> Change Email
          <div class="btn-glow"></div>
        </button>
      </form>
    </div>

    <!-- Token Analysis Panel -->
    <div class="analysis-panel">
      <div class="analysis-header">
        <i class="fas fa-microscope"></i>
        <span>Token Analysis</span>
      </div>
      <div class="analysis-body">
        <div class="token-properties">
          <div class="property">
            <span class="prop-label">Token Length</span>
            <span class="prop-value danger">8 characters</span>
          </div>
          <div class="property">
            <span class="prop-label">Character Set</span>
            <span class="prop-value danger">Hexadecimal (MD5)</span>
          </div>
          <div class="property">
            <span class="prop-label">Entropy</span>
            <span class="prop-value danger">~32 bits</span>
          </div>
          <div class="property">
            <span class="prop-label">Brute-force Time</span>
            <span class="prop-value danger">~4.2 billion combinations</span>
          </div>
        </div>
        <div class="token-hint">
          <i class="fas fa-lightbulb"></i>
          <p>The token changes every hour. Try refreshing the page at different times and compare tokens. Look for
            patterns in how the token is generated.</p>
        </div>
      </div>
    </div>

    <!-- Exploit Simulation Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>Attack Simulation</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">An attacker could:</p>
        <ol class="exploit-steps">
          <li>Observe the token pattern and deduce the generation algorithm</li>
          <li>Calculate the current token using the same weak formula</li>
          <li>Craft a malicious form with the predicted token</li>
          <li>Trick the victim into submitting the form</li>
        </ol>
        <pre class="exploit-code"><code>// Predictable token generation:
$token = md5($user_id . date('H') . 'darkhunter2024');
$token = substr($token, 0, 8); // Only first 8 chars!

// Attacker can calculate this and include in forged request:
&lt;form action="http://target.com/CSRF-CASE3.php" method="POST"&gt;
  &lt;input type="hidden" name="action" value="change_email"&gt;
  &lt;input type="hidden" name="csrf_token" value="[PREDICTED_TOKEN]"&gt;
  &lt;input type="hidden" name="new_email" value="attacker@evil.com"&gt;
&lt;/form&gt;</code></pre>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The CSRF token is only 8 characters long and appears to be hexadecimal. Short tokens have
        low entropy and can be brute-forced. Try analyzing how the token changes over time.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Refresh the page at different times and compare tokens. Notice the token changes every hour
        but follows a predictable pattern. The token might be derived from the user ID and current hour.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">The token is generated using: <code>md5(user_id + current_hour + 'darkhunter2024')</code>,
        truncated to 8 characters. You can predict it! Calculate the token for user_id=<?php echo $current_user_id; ?>
        and current hour (<?php echo date('H'); ?>), then forge a request to change the email to an attacker-controlled
        address like <code>@evil.com</code>.</div>
    </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <script>
  // Show token generation hint after analysis
  document.addEventListener('DOMContentLoaded', function() {
    const tokenField = document.getElementById('csrf-token-field');
    if (tokenField) {
      console.log('CSRF Token Analysis:');
      console.log('Current token:', tokenField.value);
      console.log('Token length:', tokenField.value.length);
      console.log('Hint: Try md5(user_id + hour + \"darkhunter2024\")');
    }
  });
  </script>
</body>

</html>