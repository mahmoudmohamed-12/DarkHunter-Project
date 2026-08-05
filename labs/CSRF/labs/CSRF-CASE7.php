<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_case7_attempts'])) {
  $_SESSION['csrf_case7_attempts'] = 0;
}
if (!isset($_SESSION['csrf_case7_solved'])) {
  $_SESSION['csrf_case7_solved'] = false;
}

// ─── Simulated Banking Application ─────────────────────────────────────
$accounts = [
  ['id' => 'ACC-7842', 'type' => 'Checking', 'balance' => 12547.85, 'currency' => 'USD', 'status' => 'Active'],
  ['id' => 'ACC-9103', 'type' => 'Savings', 'balance' => 45230.00, 'currency' => 'USD', 'status' => 'Active'],
  ['id' => 'ACC-5561', 'type' => 'Investment', 'balance' => 89300.50, 'currency' => 'USD', 'status' => 'Locked'],
];

$transactions = [
  ['id' => 'TXN-9921', 'from' => 'ACC-7842', 'to' => 'ACC-9103', 'amount' => 500.00, 'date' => '2026-05-22 08:30:00', 'status' => 'Completed'],
  ['id' => 'TXN-9922', 'from' => 'ACC-7842', 'to' => 'EXT-4451', 'amount' => 1200.00, 'date' => '2026-05-21 14:15:00', 'status' => 'Completed'],
  ['id' => 'TXN-9923', 'from' => 'ACC-9103', 'to' => 'ACC-7842', 'amount' => 2500.00, 'date' => '2026-05-20 09:45:00', 'status' => 'Completed'],
];

// ─── Double Submit Cookie Implementation (Vulnerable) ───────────────────
// Generate or retrieve the double-submit token
$cookie_token = $_COOKIE['csrf_double_token'] ?? bin2hex(random_bytes(16));
if (!isset($_COOKIE['csrf_double_token'])) {
  setcookie('csrf_double_token', $cookie_token, [
    'expires' => time() + 3600,
    'path' => '/',
    'samesite' => 'None', // VULNERABLE: Allows cross-site cookie sending
    'secure' => false,
    'httponly' => false  // VULNERABLE: JavaScript can read/write this cookie
  ]);
}

// VULNERABLE: The form token is sent as a parameter, but the cookie token
// can be manipulated independently by an attacker
$form_token = $_POST['csrf_token'] ?? $cookie_token;

// ─── Vulnerable Validation Logic ─────────────────────────────────────────
$transfer_result = null;
$validation_error = null;
$show_flag = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

  // VULNERABLE: Double-submit validation that can be bypassed
  // The cookie token and form token are compared, but both can be controlled
  $submitted_cookie_token = $_COOKIE['csrf_double_token'] ?? '';
  $submitted_form_token = $_POST['csrf_token'] ?? '';

  // VULNERABLE: The validation checks if they match, but an attacker
  // can set both to the same value from a different origin!
  $token_valid = ($submitted_cookie_token === $submitted_form_token && !empty($submitted_cookie_token));

  if (!$token_valid) {
    $validation_error = 'CSRF token validation failed. Cookie token and form token do not match.';
  } else {

    if ($_POST['action'] === 'transfer') {
      $from_acc = $_POST['from_account'] ?? '';
      $to_acc = $_POST['to_account'] ?? '';
      $amount = floatval($_POST['amount'] ?? 0);

      $transfer_result = [
        'status' => 'success',
        'message' => "Transfer of $" . number_format($amount, 2) . " from $from_acc to $to_acc initiated successfully.",
        'txn_id' => 'TXN-' . rand(1000, 9999),
        'timestamp' => date('Y-m-d H:i:s')
      ];

      array_unshift($transactions, [
        'id' => $transfer_result['txn_id'],
        'from' => $from_acc,
        'to' => $to_acc,
        'amount' => $amount,
        'date' => $transfer_result['timestamp'],
        'status' => 'Pending'
      ]);
    }

    if ($_POST['action'] === 'change_password') {
      $transfer_result = [
        'status' => 'success',
        'message' => 'Password changed successfully via double-submit protected form.',
        'warning' => 'If you did not initiate this, contact support immediately!'
      ];
    }

    if ($_POST['action'] === 'reveal_secrets') {
      $show_flag = true;
      $transfer_result = [
        'status' => 'success',
        'message' => '🔓 SECURITY BREACH - Double-submit cookie pattern bypassed!',
        'flag' => 'DH{csrf_double_submit_broken}'
      ];
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['csrf_case7_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['csrf_case7_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['csrf_case7_solved'] = true;
  $already_solved = true;
  $success_msg = "Exceptional work! You've broken the double-submit cookie pattern by independently manipulating the cookie and form token values. Since the cookie was accessible via JavaScript (httponly=false) and SameSite=None, you could forge both tokens from a malicious site!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['view']) || isset($_GET['tab']))) {
  $_SESSION['csrf_case7_attempts']++;
}

$attempts = $_SESSION['csrf_case7_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecureBank Transfer - CSRF Case 7 (Double Submit)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-university"></i> SecureBank Transfer Portal</h1>
      <p class="lab-description">Transfer funds between accounts securely. This advanced CSRF challenge uses the
        double-submit cookie pattern - but with critical implementation flaws. <strong>Can you break both tokens
          independently?</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this double-submit cookie vulnerability. You can continue exploring, but no
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

    <?php if ($validation_error): ?>
      <div class="error-alert">
        <i class="fas fa-times-circle"></i>
        <span><?php echo $validation_error; ?></span>
      </div>
    <?php endif; ?>

    <?php if ($transfer_result): ?>
      <div class="result-alert <?php echo $transfer_result['status']; ?>">
        <i
          class="fas fa-<?php echo $transfer_result['status'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <div class="result-content">
          <strong><?php echo $transfer_result['message']; ?></strong>
          <?php if (isset($transfer_result['txn_id'])): ?>
            <span>Transaction ID: <?php echo $transfer_result['txn_id']; ?></span>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Banking Dashboard -->
    <div class="banking-dashboard">

      <!-- Account Overview -->
      <div class="dashboard-card accounts-card">
        <div class="card-header">
          <i class="fas fa-wallet"></i>
          <h3>Your Accounts</h3>
        </div>
        <div class="accounts-grid">
          <?php foreach ($accounts as $acc): ?>
            <div class="account-item <?php echo strtolower($acc['status']); ?>">
              <div class="account-header">
                <span class="account-type"><?php echo $acc['type']; ?></span>
                <span class="account-status"><?php echo $acc['status']; ?></span>
              </div>
              <div class="account-balance">
                <span class="currency"><?php echo $acc['currency']; ?></span>
                <span class="amount"><?php echo number_format($acc['balance'], 2); ?></span>
              </div>
              <code class="account-id"><?php echo $acc['id']; ?></code>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Transfer Form (Vulnerable - Double Submit) -->
      <div class="dashboard-card transfer-card vulnerable-card">
        <div class="card-header">
          <i class="fas fa-exchange-alt"></i>
          <h3>Fund Transfer</h3>
          <span class="vuln-badge"><i class="fas fa-shield-alt"></i> Double Submit</span>
        </div>
        <form method="POST" action="" class="transfer-form" id="transfer-form">
          <input type="hidden" name="action" value="transfer">
          <input type="hidden" name="csrf_token" value="<?php echo $form_token; ?>" id="csrf-token-field">

          <div class="form-row">
            <div class="form-group">
              <label>From Account</label>
              <select name="from_account" class="form-select">
                <?php foreach ($accounts as $acc): ?>
                  <option value="<?php echo $acc['id']; ?>"><?php echo $acc['id']; ?> - <?php echo $acc['type']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>To Account</label>
              <input type="text" name="to_account" placeholder="ACC-XXXX or External" class="form-input">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Amount</label>
              <div class="amount-input">
                <span class="currency-symbol">$</span>
                <input type="number" name="amount" step="0.01" placeholder="0.00" class="form-input">
              </div>
            </div>
            <div class="form-group">
              <label>Description</label>
              <input type="text" name="description" placeholder="Transfer description..." class="form-input">
            </div>
          </div>

          <div class="token-display">
            <span class="token-label"><i class="fas fa-fingerprint"></i> CSRF Token:</span>
            <code class="token-value"><?php echo substr($form_token, 0, 16); ?>...</code>
          </div>

          <button type="submit" class="btn-transfer">
            <i class="fas fa-paper-plane"></i> Initiate Transfer
          </button>
        </form>
      </div>

      <!-- Security Settings (Vulnerable) -->
      <div class="dashboard-card security-card vulnerable-card">
        <div class="card-header">
          <i class="fas fa-user-shield"></i>
          <h3>Security Settings</h3>
          <span class="vuln-badge"><i class="fas fa-shield-alt"></i> Double Submit</span>
        </div>
        <form method="POST" action="" class="security-form" id="security-form">
          <input type="hidden" name="action" value="change_password">
          <input type="hidden" name="csrf_token" value="<?php echo $form_token; ?>">

          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="••••••••" class="form-input">
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="••••••••" class="form-input">
          </div>
          <button type="submit" class="btn-action">
            <i class="fas fa-key"></i> Change Password
          </button>
        </form>
      </div>

      <!-- Secrets Vault (Target) -->
      <div class="dashboard-card secrets-card">
        <div class="card-header">
          <i class="fas fa-vault"></i>
          <h3>Vault Access</h3>
          <span class="secret-badge"><i class="fas fa-lock"></i> Restricted</span>
        </div>
        <?php if ($show_flag): ?>
          <div class="vault-opened">
            <div class="flag-reveal">
              <i class="fas fa-flag"></i>
              <code>DH{csrf_double_submit_broken}</code>
            </div>
            <p class="vault-message">The double-submit cookie pattern has been successfully bypassed!</p>
          </div>
        <?php else: ?>
          <form method="POST" action="" class="vault-form" id="vault-form">
            <input type="hidden" name="action" value="reveal_secrets">
            <input type="hidden" name="csrf_token" value="<?php echo $form_token; ?>">
            <p class="vault-description">Access the secure vault containing system secrets and flags.</p>
            <button type="submit" class="btn-vault">
              <i class="fas fa-unlock-alt"></i> Access Vault
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Transaction History -->
      <div class="dashboard-card history-card">
        <div class="card-header">
          <i class="fas fa-history"></i>
          <h3>Recent Transactions</h3>
        </div>
        <div class="transactions-list">
          <?php foreach ($transactions as $txn): ?>
            <div class="transaction-item">
              <div class="txn-icon">
                <i class="fas fa-<?php echo $txn['status'] === 'Completed' ? 'check' : 'clock'; ?>"></i>
              </div>
              <div class="txn-details">
                <span class="txn-id"><?php echo $txn['id']; ?></span>
                <span class="txn-accounts"><?php echo $txn['from']; ?> → <?php echo $txn['to']; ?></span>
              </div>
              <div class="txn-amount">
                <span class="amount">$<?php echo number_format($txn['amount'], 2); ?></span>
                <span class="status <?php echo strtolower($txn['status']); ?>"><?php echo $txn['status']; ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Token Analysis Panel -->
    <div class="debug-panel token-panel">
      <div class="debug-header">
        <i class="fas fa-fingerprint"></i>
        <span>Double-Submit Token Analysis</span>
      </div>
      <div class="debug-body">
        <div class="token-comparison">
          <div class="token-box">
            <span class="token-label">Cookie Token (csrf_double_token)</span>
            <code class="token-value"><?php echo substr($cookie_token, 0, 20); ?>...</code>
            <div class="token-props">
              <span class="prop vuln">SameSite: None</span>
              <span class="prop vuln">HttpOnly: false</span>
              <span class="prop vuln">Secure: false</span>
            </div>
          </div>
          <div class="token-compare">
            <i class="fas fa-equals"></i>
            <span>Compared server-side</span>
          </div>
          <div class="token-box">
            <span class="token-label">Form Token (csrf_token)</span>
            <code class="token-value"><?php echo substr($form_token, 0, 20); ?>...</code>
            <div class="token-props">
              <span class="prop">Sent as POST parameter</span>
              <span class="prop vuln">Attacker can set both</span>
            </div>
          </div>
        </div>
        <div class="vulnerability-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Both tokens can be controlled by an attacker because the cookie lacks HttpOnly and SameSite
            protections!</span>
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
          <span>Cookie Token Present: <?php echo isset($_COOKIE['csrf_double_token']) ? 'Yes' : 'No'; ?></span>
          <span>Form Token Present: <?php echo isset($_POST['csrf_token']) ? 'Yes' : 'No'; ?></span>
          <span>Tokens Match:
            <?php echo (isset($_COOKIE['csrf_double_token']) && isset($_POST['csrf_token']) && $_COOKIE['csrf_double_token'] === $_POST['csrf_token']) ? 'Yes' : 'N/A'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Examine the token analysis panel. The double-submit cookie is missing <code>HttpOnly</code>
          and has <code>SameSite=None</code>. This means JavaScript from any origin can read AND write this cookie!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The double-submit pattern relies on the attacker not knowing both tokens. But if the cookie
          is readable via JavaScript, you can capture its value, then create a form with a matching hidden input. Both
          values will match and pass validation.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Create a malicious page that: 1) Uses <code>document.cookie</code> to read the
          <code>csrf_double_token</code> value, 2) Creates a form with a hidden input named <code>csrf_token</code> with
          the SAME value, 3) Submits the form to <code>reveal_secrets</code>. Since both tokens match, validation passes!
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
      const vaultOpened = document.querySelector('.vault-opened');
      const alreadySolved = document.querySelector('.solved-banner');

      if (vaultOpened && !alreadySolved) {
        if (document.querySelector('.result-alert')) {
          document.getElementById('solved-flag').value = '1';
          document.getElementById('success-form').submit();
        }
      }
    });
  </script>
</body>

</html>