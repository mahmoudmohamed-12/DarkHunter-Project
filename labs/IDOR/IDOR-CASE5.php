<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);
// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_hard1_attempts'])) {
  $_SESSION['idor_hard1_attempts'] = 0;
}
if (!isset($_SESSION['idor_hard1_solved'])) {
  $_SESSION['idor_hard1_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'balance' => '$50,000'],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'User', 'balance' => '$1,250'],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'User', 'balance' => '$3,400'],
  4 => ['id' => 4, 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'role' => 'User', 'balance' => '$780'],
  5 => ['id' => 5, 'username' => 'dave', 'email' => 'dave@darkhunter.local', 'role' => 'User', 'balance' => '$5,600'],
];

// ─── Vulnerable Blind IDOR Logic ─────────────────────────────────────────
// This endpoint processes transfers but returns NO feedback about success/failure
// The vulnerability: no ownership check on the recipient account
$recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null;
$amount = isset($_POST['amount']) ? $_POST['amount'] : '';
$transfer_msg = null;
$show_feedback = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
  $_SESSION['idor_hard1_attempts']++;
  
  // Vulnerable: no check if recipient_id belongs to current user
  // No visible feedback - action succeeds silently
  if ($recipient_id && isset($users[$recipient_id]) && $amount) {
    $transfer_msg = "Transfer request processed. Transaction ID: TRX-" . strtoupper(substr(md5(time()), 0, 8));
    $show_feedback = true;
    
    // If targeting admin (id=1), this is the exploit
    if ($recipient_id === 1) {
      // Silent success - no data returned, but action happened
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_hard1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_hard1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['idor_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited a Blind IDOR vulnerability. You performed an unauthorized action (transfer to admin) with no visible feedback — a silent but deadly exploit!";
}

$attempts = $_SESSION['idor_hard1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bank Transfer - IDOR Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-university"></i> Secure Bank Transfer</h1>
      <p class="lab-description">Transfer funds between accounts. This challenge demonstrates a <strong>Blind
          IDOR</strong> — actions succeed without visible feedback. You must use out-of-band techniques or infer success
        from behavior changes. <strong>No authorization on recipient validation!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Blind IDOR vulnerability. You can continue exploring, but no additional
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

    <!-- Transfer Form -->
    <div class="transfer-panel">
      <div class="transfer-header">
        <i class="fas fa-paper-plane"></i>
        <h2>New Transfer</h2>
      </div>
      <form method="POST" action="" class="transfer-form">
        <div class="form-group">
          <label><i class="fas fa-user"></i> From Account</label>
          <input type="text" value="alice (ID: 2)" disabled class="form-control disabled">
        </div>
        <div class="form-group">
          <label><i class="fas fa-user-plus"></i> Recipient ID</label>
          <input type="number" name="recipient_id" class="form-control" placeholder="Enter recipient user ID"
            value="<?php echo isset($_POST['recipient_id']) ? htmlspecialchars($_POST['recipient_id']) : ''; ?>">
          <small class="form-hint">Enter the numeric ID of the recipient</small>
        </div>
        <div class="form-group">
          <label><i class="fas fa-dollar-sign"></i> Amount</label>
          <input type="text" name="amount" class="form-control" placeholder="0.00"
            value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
        </div>
        <button type="submit" name="transfer" class="transfer-btn">
          <i class="fas fa-lock"></i> Process Transfer
        </button>
      </form>

      <?php if ($show_feedback): ?>
      <div class="transfer-feedback">
        <i class="fas fa-check-circle"></i>
        <p><?php echo $transfer_msg; ?></p>
        <small>Note: For security reasons, balance updates are not displayed in real-time.</small>
      </div>
      <?php endif; ?>
    </div>

    <!-- Account List -->
    <div class="accounts-panel">
      <h3><i class="fas fa-users"></i> Known Accounts</h3>
      <div class="accounts-grid">
        <?php foreach ($users as $uid => $u): ?>
        <div class="account-card <?php echo $uid === 1 ? 'admin-card' : ''; ?>">
          <div class="account-avatar"><?php echo strtoupper(substr($u['username'], 0, 1)); ?></div>
          <div class="account-info">
            <span class="account-name"><?php echo htmlspecialchars($u['username']); ?></span>
            <span class="account-role <?php echo $uid === 1 ? 'role-admin' : 'role-user'; ?>">
              <?php echo $u['role']; ?>
            </span>
            <span class="account-id">ID: <?php echo $uid; ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Email Log Simulation -->
    <div class="email-log">
      <div class="log-header">
        <i class="fas fa-envelope"></i>
        <span>Notification Center</span>
        <span class="log-status"><i class="fas fa-circle"></i> Live</span>
      </div>
      <div class="log-body">
        <div class="log-entry">
          <span class="log-time"><?php echo date('H:i:s', time() - 300); ?></span>
          <span class="log-text">System: Daily backup completed successfully</span>
        </div>
        <div class="log-entry">
          <span class="log-time"><?php echo date('H:i:s', time() - 180); ?></span>
          <span class="log-text">Security: New login from 192.168.1.105</span>
        </div>
        <?php if ($show_feedback && $recipient_id === 1): ?>
        <div class="log-entry highlight">
          <span class="log-time"><?php echo date('H:i:s'); ?></span>
          <span class="log-text">Alert: Admin account received unauthorized transfer attempt</span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This is a <strong>Blind IDOR</strong>. The application gives you a generic success message
        regardless of the recipient. Try transferring to different user IDs and observe if anything changes.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Check the Notification Center after each transfer. Some actions trigger alerts even when
        the main response looks the same. Try transferring to the admin account (ID: 1).</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Submit a transfer with <code>recipient_id=1</code> (admin) and any amount. Watch the
        Notification Center — an alert will appear confirming the unauthorized action succeeded!</div>
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
    // Check if there's a highlighted log entry (indicates admin transfer)
    const highlightedLog = document.querySelector('.log-entry.highlight');
    if (highlightedLog && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>