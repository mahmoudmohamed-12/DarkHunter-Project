<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['idor_easy2_attempts'])) {
  $_SESSION['idor_easy2_attempts'] = 0;
}
if (!isset($_SESSION['idor_easy2_solved'])) {
  $_SESSION['idor_easy2_solved'] = false;
}

$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'balance' => '$50,000', 'secret' => 'Flag: DH{idor_hidden_fields_exposed}'],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'balance' => '$1,200', 'secret' => 'Bought coffee today.'],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'balance' => '$3,400', 'secret' => 'Saving for new laptop.'],
];

$current_user_id = 2;
$target_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $current_user_id;

if (!isset($users[$target_user_id])) {
  $target_user_id = $current_user_id;
}

$current_user = $users[$target_user_id];
$is_admin = ($target_user_id === 1);

$success_msg = null;
$already_solved = $_SESSION['idor_easy2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_easy2_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['idor_easy2_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You exploited the hidden form field IDOR by modifying the user_id in the POST request. The application blindly trusted client-side input!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['idor_easy2_attempts']++;
}

$attempts = $_SESSION['idor_easy2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bank Transfer - IDOR Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-2.css">
</head>

<body>
  <div class="bg-grid"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-university"></i> Secure Bank Transfer</h1>
      <p class="lab-description">Transfer funds between accounts. This lab has a hidden vulnerability in its form
        fields. <strong>Inspect and modify hidden inputs!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this hidden field IDOR. Continue exploring for practice.</p>
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

    <!-- Account Info Card -->
    <div class="profile-card <?php echo $is_admin ? 'admin-profile' : ''; ?>">
      <div class="profile-banner"></div>
      <div class="profile-avatar"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></div>
      <div class="profile-info">
        <h2 class="profile-name"><?php echo htmlspecialchars($current_user['username']); ?></h2>
        <span class="profile-role <?php echo $is_admin ? 'role-admin' : 'role-user'; ?>">
          <i class="fas fa-<?php echo $is_admin ? 'crown' : 'user'; ?>"></i>
          <?php echo $is_admin ? 'Administrator' : 'Standard User'; ?>
        </span>
        <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($current_user['email']); ?>
        </p>
        <p class="profile-bio"
          style="font-size: 1.5rem; color: var(--accent-cyan); font-family: 'Orbitron', sans-serif;">
          <i class="fas fa-wallet"></i> Balance: <?php echo $current_user['balance']; ?>
        </p>
      </div>
    </div>

    <!-- Transfer Form -->
    <div class="form-card">
      <h2 class="form-title"><i class="fas fa-paper-plane"></i> Transfer Funds</h2>
      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Recipient Username</label>
          <input type="text" name="recipient" class="form-input" placeholder="Enter recipient username..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Amount ($)</label>
          <input type="number" name="amount" class="form-input" placeholder="0.00" required>
        </div>
        <!-- VULNERABLE: Hidden field without server-side validation -->
        <input type="hidden" name="user_id" value="<?php echo $current_user_id; ?>">
        <input type="hidden" name="from_account" value="<?php echo $current_user_id; ?>">
        <button type="submit" class="submit-btn"><i class="fas fa-check-circle"></i> Confirm Transfer</button>
      </form>
    </div>

    <!-- Admin Secret Panel (visible when user_id=1) -->
    <?php if ($is_admin): ?>
    <div class="notes-card" style="border-color: rgba(239, 68, 68, 0.3);">
      <div class="notes-header">
        <i class="fas fa-user-shield" style="color: var(--accent-red);"></i>
        <h3 style="color: var(--accent-red);">🔒 Admin Secret Vault</h3>
        <span class="secret-badge"><i class="fas fa-exclamation-triangle"></i> TOP SECRET</span>
      </div>
      <div class="notes-body" style="color: var(--accent-red); font-size: 1.1rem;">
        <?php echo htmlspecialchars($current_user['secret']); ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Request Inspector</span></div>
      <div class="debug-body">
        <code>POST /IDOR-CASE2.php<br>Content-Type: application/x-www-form-urlencoded<br><br>recipient=...&amount=...&user_id=<?php echo $current_user_id; ?>&from_account=<?php echo $current_user_id; ?></code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Use your browser's DevTools (F12) → Elements tab. Look for
        <code>&lt;input type="hidden"&gt;</code> fields in the form.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The form contains a hidden <code>user_id</code> field. Try changing its value to
        <code>1</code> before submitting. You can use Burp Suite or browser DevTools.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Right-click the form → Inspect → find
        <code>&lt;input type="hidden" name="user_id" value="2"&gt;</code> → change value to <code>1</code> → submit. The
        admin secret vault will appear!
      </div>
    </div>
    <?php endif; ?>

    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
  window.addEventListener('load', function() {
    const roleBadge = document.querySelector('.role-admin');
    if (roleBadge && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>