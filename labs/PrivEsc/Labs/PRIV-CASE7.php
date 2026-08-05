<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case7_attempts'])) {
  $_SESSION['priv_case7_attempts'] = 0;
}
if (!isset($_SESSION['priv_case7_solved'])) {
  $_SESSION['priv_case7_solved'] = false;
}
if (!isset($_SESSION['priv_case7_balance'])) {
  $_SESSION['priv_case7_balance'] = 100;
}
if (!isset($_SESSION['priv_case7_role'])) {
  $_SESSION['priv_case7_role'] = 'user';
}
if (!isset($_SESSION['priv_case7_transactions'])) {
  $_SESSION['priv_case7_transactions'] = [];
}

// ─── Simulated Race Condition Logic ──────────────────────────────────────
$action_result = null;
$race_detected = false;
$flag_triggered = false;
$concurrent_requests = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perform_action'])) {
  $action = $_POST['action'] ?? '';
  $amount = intval($_POST['amount'] ?? 0);

  // VULNERABLE: No locking mechanism - classic TOCTOU
  $current_balance = $_SESSION['priv_case7_balance'];
  $current_role = $_SESSION['priv_case7_role'];

  if ($action === 'withdraw') {
    // VULNERABLE: Check-then-act without synchronization
    if ($current_balance >= $amount) {
      // Simulate processing delay (race window)
      usleep(100000); // 100ms delay

      // Check again (TOCTOU - time of check vs time of use)
      if ($_SESSION['priv_case7_balance'] >= $amount) {
        $_SESSION['priv_case7_balance'] -= $amount;
        $_SESSION['priv_case7_transactions'][] = [
          'type' => 'withdraw',
          'amount' => $amount,
          'time' => date('Y-m-d H:i:s')
        ];
        $action_result = "Withdrawn $amount credits. Balance: " . $_SESSION['priv_case7_balance'];
      } else {
        $race_detected = true;
        $action_result = "Race condition detected! Balance changed during transaction!";
      }
    } else {
      $action_result = "Insufficient balance!";
    }
  }

  if ($action === 'upgrade_role') {
    // VULNERABLE: Race condition in role upgrade
    $upgrade_cost = 500;

    if ($current_balance >= $upgrade_cost) {
      usleep(100000); // 100ms delay - race window

      // Double-check (another TOCTOU)
      if ($_SESSION['priv_case7_balance'] >= $upgrade_cost) {
        $_SESSION['priv_case7_balance'] -= $upgrade_cost;
        $_SESSION['priv_case7_role'] = 'admin';
        $_SESSION['priv_case7_transactions'][] = [
          'type' => 'role_upgrade',
          'from' => 'user',
          'to' => 'admin',
          'time' => date('Y-m-d H:i:s')
        ];
        $action_result = "Role upgraded to ADMIN! Cost: $upgrade_cost credits";
        $flag_triggered = true;
      } else {
        $race_detected = true;
        $action_result = "Race condition! Balance insufficient after delay!";
      }
    } else {
      $action_result = "Need $upgrade_cost credits to upgrade!";
    }
  }

  if ($action === 'concurrent_requests') {
    // Simulate concurrent request detection
    $concurrent_requests = intval($_POST['concurrent_count'] ?? 1);

    if ($concurrent_requests >= 3) {
      $race_detected = true;
      // VULNERABLE: With enough concurrent requests, bypass balance check
      $_SESSION['priv_case7_balance'] += 50; // Exploit: duplicate credit generation
      $action_result = "Concurrent requests detected! Exploited race condition - gained bonus credits!";

      if ($_SESSION['priv_case7_role'] !== 'admin') {
        $_SESSION['priv_case7_role'] = 'admin';
        $flag_triggered = true;
        $action_result .= " Role escalated to ADMIN!";
      }
    } else {
      $action_result = "Sent $concurrent_requests concurrent requests. Need 3+ to exploit.";
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case7_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case7_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case7_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've successfully exploited a Race Condition vulnerability. By sending concurrent requests that raced through the authorization logic, you bypassed the privilege check before the state updated, achieving privilege escalation!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perform_action'])) {
  $_SESSION['priv_case7_attempts']++;
}

$attempts = $_SESSION['priv_case7_attempts'];
$current_balance = $_SESSION['priv_case7_balance'];
$current_role = $_SESSION['priv_case7_role'];
$transactions = $_SESSION['priv_case7_transactions'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Banking System - PrivEsc Case 7 (Race Condition)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-stopwatch"></i> Banking System</h1>
      <p class="lab-description">Manage your account and credits. This hard Privilege Escalation challenge has a
        <strong>race condition vulnerability</strong> in privilege checks. <strong>Race the authorization logic by
          sending simultaneous requests!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Race Condition vulnerability. You can continue exploring, but no additional
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

    <!-- Banking Grid -->
    <div class="banking-grid">

      <!-- Account Status -->
      <div class="banking-card status-card">
        <div class="card-header">
          <i class="fas fa-wallet"></i>
          <h3>Account Status</h3>
        </div>

        <div class="status-content">
          <div class="status-item">
            <span class="status-label"><i class="fas fa-coins"></i> Balance</span>
            <span class="status-value"><?php echo $current_balance; ?> Credits</span>
          </div>
          <div class="status-item">
            <span class="status-label"><i class="fas fa-id-badge"></i> Role</span>
            <span
              class="status-value role-badge <?php echo $current_role; ?>"><?php echo htmlspecialchars($current_role); ?></span>
          </div>
          <div class="status-item">
            <span class="status-label"><i class="fas fa-exchange-alt"></i> Transactions</span>
            <span class="status-value"><?php echo count($transactions); ?></span>
          </div>
        </div>
      </div>

      <!-- Action Panel (Vulnerable) -->
      <div class="banking-card action-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Actions</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Race Condition</span>
        </div>

        <?php if ($action_result): ?>
          <div class="action-alert <?php echo $race_detected ? 'race-detected' : ($flag_triggered ? 'success' : ''); ?>">
            <i
              class="fas fa-<?php echo $race_detected ? 'exclamation-triangle' : ($flag_triggered ? 'check-circle' : 'info-circle'); ?>"></i>
            <span><?php echo htmlspecialchars($action_result); ?></span>
          </div>
        <?php endif; ?>

        <div class="actions-list">
          <!-- Withdraw -->
          <form method="POST" action="" class="action-form">
            <input type="hidden" name="perform_action" value="1">
            <input type="hidden" name="action" value="withdraw">
            <div class="action-row">
              <span class="action-name"><i class="fas fa-minus-circle"></i> Withdraw</span>
              <input type="number" name="amount" value="50" min="1" class="action-input">
              <button type="submit" class="btn-action">
                <i class="fas fa-paper-plane"></i> Send
              </button>
            </div>
          </form>

          <!-- Upgrade Role -->
          <form method="POST" action="" class="action-form">
            <input type="hidden" name="perform_action" value="1">
            <input type="hidden" name="action" value="upgrade_role">
            <div class="action-row">
              <span class="action-name"><i class="fas fa-arrow-up"></i> Upgrade to Admin</span>
              <span class="action-cost">500 Credits</span>
              <button type="submit" class="btn-action btn-upgrade">
                <i class="fas fa-crown"></i> Upgrade
              </button>
            </div>
          </form>

          <!-- Concurrent Requests -->
          <form method="POST" action="" class="action-form">
            <input type="hidden" name="perform_action" value="1">
            <input type="hidden" name="action" value="concurrent_requests">
            <div class="action-row">
              <span class="action-name"><i class="fas fa-sync-alt"></i> Concurrent Requests</span>
              <input type="number" name="concurrent_count" value="1" min="1" max="10" class="action-input">
              <button type="submit" class="btn-action btn-race">
                <i class="fas fa-bolt"></i> Race!
              </button>
            </div>
          </form>
        </div>

        <div class="race-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <span>The system has a 100ms processing delay - perfect for race conditions!</span>
        </div>
      </div>

      <!-- Transaction Log -->
      <div class="banking-card log-card">
        <div class="card-header">
          <i class="fas fa-history"></i>
          <h3>Transaction Log</h3>
        </div>
        <div class="log-content">
          <?php if (empty($transactions)): ?>
            <div class="log-placeholder">
              <i class="fas fa-receipt"></i>
              <p>No transactions yet</p>
            </div>
          <?php else: ?>
            <?php foreach (array_reverse($transactions) as $tx): ?>
              <div class="log-item">
                <div class="log-icon">
                  <i
                    class="fas fa-<?php echo $tx['type'] === 'withdraw' ? 'minus' : ($tx['type'] === 'role_upgrade' ? 'crown' : 'sync'); ?>"></i>
                </div>
                <div class="log-details">
                  <span class="log-type"><?php echo htmlspecialchars($tx['type']); ?></span>
                  <?php if (isset($tx['amount'])): ?>
                    <span class="log-amount"><?php echo $tx['amount']; ?> Credits</span>
                  <?php endif; ?>
                  <?php if (isset($tx['from'])): ?>
                    <span class="log-role"><?php echo htmlspecialchars($tx['from']); ?> →
                      <?php echo htmlspecialchars($tx['to']); ?></span>
                  <?php endif; ?>
                  <span class="log-time"><?php echo $tx['time']; ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Race Condition Analysis -->
      <div class="banking-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>TOCTOU Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Vulnerable Code:</span>
            <pre class="analysis-code"><code>// DANGEROUS: Check-then-act without lock
if ($balance >= $amount) {
  usleep(100000); // Race window!
  $balance -= $amount; // State may have changed!
}</code></pre>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Code:</span>
            <pre class="analysis-code secure"><code>// SAFE: Atomic operation with lock
$lock->acquire();
if ($balance >= $amount) {
  $balance -= $amount; // Atomic!
}
$lock->release();</code></pre>
          </div>
        </div>
      </div>

      <!-- Attack Techniques -->
      <div class="banking-card techniques-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>Race Condition Techniques</h3>
        </div>
        <div class="techniques-list">
          <div class="technique-item">
            <div class="technique-name">Double Spending</div>
            <code class="technique-code">Send 2+ withdraw requests simultaneously</code>
            <span class="technique-desc">Exploit the 100ms window between check and act</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Role Upgrade Race</div>
            <code class="technique-code">Send upgrade + withdraw concurrently</code>
            <span class="technique-desc">Race the balance check for role upgrade</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Concurrent Flood</div>
            <code class="technique-code">Send 3+ requests at once</code>
            <span class="technique-desc">Overload the system to bypass checks</span>
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
          <span>Balance: <?php echo $current_balance; ?></span>
          <span>Role: <?php echo htmlspecialchars($current_role); ?></span>
          <span>Race Detected: <?php echo $race_detected ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Concurrent: <?php echo $concurrent_requests; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The system has a 100ms delay between checking your balance and actually deducting credits.
          Try sending multiple requests at the same time to exploit this window!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Use the "Concurrent Requests" feature and send 3 or more requests simultaneously. The
          system can't handle the race condition and may grant you admin privileges or bonus credits!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Set "Concurrent Requests" to 3 or more and click "Race!". The system will detect the
          concurrent requests and trigger the vulnerability, escalating your role to admin automatically!</div>
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
      const raceAlert = document.querySelector('.race-detected');
      const alreadySolved = document.querySelector('.solved-banner');

      if (raceAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>