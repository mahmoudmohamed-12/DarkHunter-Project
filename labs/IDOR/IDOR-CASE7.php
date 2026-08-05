<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_hard3_attempts'])) {
  $_SESSION['idor_hard3_attempts'] = 0;
}
if (!isset($_SESSION['idor_hard3_solved'])) {
  $_SESSION['idor_hard3_solved'] = false;
}

// ─── Simulated UUID Mapping (IDOR via Indirect Reference) ──────────────────
// In real apps, UUIDs are used to hide direct DB IDs. But if leaked or predictable...
$uuid_map = [
  '550e8400-e29b-41d4-a716-446655440001' => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'secret' => 'Flag: DH{idor_uuid_bypass_pwned}'],
  '550e8400-e29b-41d4-a716-446655440002' => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'secret' => 'My favorite color is blue.'],
  '550e8400-e29b-41d4-a716-446655440003' => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'secret' => 'I love pizza.'],
  '550e8400-e29b-41d4-a716-446655440004' => ['id' => 4, 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'secret' => 'Password hint: my dog name.'],
];

// ─── Information Disclosure Leak (Simulated) ───────────────────────────────
// This "leak" appears in page source or error messages
$leaked_uuids = [
  '550e8400-e29b-41d4-a716-446655440002',
  '550e8400-e29b-41d4-a716-446655440003',
];

// ─── Vulnerable UUID Logic ─────────────────────────────────────────────────
$uuid = isset($_GET['uuid']) ? $_GET['uuid'] : '550e8400-e29b-41d4-a716-446655440002';
$user = isset($uuid_map[$uuid]) ? $uuid_map[$uuid] : null;

if (!$user) {
  $uuid = '550e8400-e29b-41d4-a716-446655440002';
  $user = $uuid_map[$uuid];
}

$is_admin = ($user['id'] === 1);

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_hard3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_hard3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['idor_hard3_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've bypassed UUID-based indirect references! You exploited information disclosure to access the admin account protected by UUIDs.";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['uuid'])) {
  $_SESSION['idor_hard3_attempts']++;
}

$attempts = $_SESSION['idor_hard3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure Profile - IDOR Hard 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-fingerprint"></i> Secure Profile Viewer</h1>
      <p class="lab-description">View your profile using UUID-based references. This challenge uses <strong>indirect
          reference maps</strong> (UUIDs) instead of numeric IDs. Can you bypass them? <strong>Look for information
          disclosure leaks!</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this UUID bypass vulnerability. You can continue exploring, but no additional
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

    <!-- UUID Profile Card -->
    <div class="uuid-profile <?php echo $is_admin ? 'admin-profile' : ''; ?>">
      <div class="uuid-header">
        <div class="uuid-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
        <div class="uuid-meta">
          <h2><?php echo htmlspecialchars($user['username']); ?></h2>
          <span class="uuid-badge"><?php echo $is_admin ? 'Administrator' : 'Standard User'; ?></span>
          <code class="uuid-display"><?php echo htmlspecialchars($uuid); ?></code>
        </div>
      </div>
      <div class="uuid-details">
        <div class="detail-row">
          <i class="fas fa-envelope"></i>
          <span><?php echo htmlspecialchars($user['email']); ?></span>
        </div>
        <div class="detail-row">
          <i class="fas fa-lock"></i>
          <span class="secret-text"><?php echo htmlspecialchars($user['secret']); ?></span>
        </div>
      </div>
    </div>

    <!-- UUID Navigator -->
    <div class="uuid-navigator">
      <h3><i class="fas fa-random"></i> Profile Navigator</h3>
      <div class="uuid-list">
        <?php foreach ($uuid_map as $uid => $u): ?>
          <a href="?uuid=<?php echo urlencode($uid); ?>"
            class="uuid-item <?php echo $uid === $uuid ? 'active' : ''; ?> <?php echo $u['id'] === 1 ? 'admin-item' : ''; ?>">
            <span class="uuid-short"><?php echo substr($uid, 0, 8); ?>...</span>
            <span class="uuid-user"><?php echo htmlspecialchars($u['username']); ?></span>
            <?php if ($u['id'] === 1): ?>
              <i class="fas fa-crown"></i>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Error Log Panel (Information Disclosure) -->
    <div class="error-log">
      <div class="log-header">
        <i class="fas fa-bug"></i>
        <span>System Logs</span>
        <span class="log-badge">Debug Mode</span>
      </div>
      <div class="log-body">
        <div class="log-line">
          <span class="log-ts">[<?php echo date('Y-m-d H:i:s'); ?>]</span>
          <span class="log-lvl info">INFO</span>
          <span class="log-msg">Profile accessed: uuid=<?php echo htmlspecialchars($uuid); ?></span>
        </div>
        <div class="log-line">
          <span class="log-ts">[<?php echo date('Y-m-d H:i:s', time() - 60); ?>]</span>
          <span class="log-lvl warn">WARN</span>
          <span class="log-msg">Failed login attempt for uuid=550e8400-e29b-41d4-a716-446655440001</span>
        </div>
        <div class="log-line">
          <span class="log-ts">[<?php echo date('Y-m-d H:i:s', time() - 120); ?>]</span>
          <span class="log-lvl debug">DEBUG</span>
          <span class="log-msg">Cache miss for user alice (uuid: 550e8400-e29b-41d4-a716-446655440002)</span>
        </div>
        <!-- Hidden leak in comments -->
        <!-- 
          Internal UUID Reference Map:
          admin -> 550e8400-e29b-41d4-a716-446655440001
          alice -> 550e8400-e29b-41d4-a716-446655440002
          bob -> 550e8400-e29b-41d4-a716-446655440003
          charlie -> 550e8400-e29b-41d4-a716-446655440004
        -->
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">UUIDs are supposed to be unguessable, but applications often leak them in logs, error
          messages, or HTML comments. Check the page source and system logs carefully.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Look at the System Logs section. Debug mode might reveal sensitive information. Also try
          viewing the page source (Ctrl+U) — developers sometimes leave comments with internal mappings.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">View the page source to find the hidden UUID mapping comment. The admin UUID is
          <code>550e8400-e29b-41d4-a716-446655440001</code>. Use it in the URL to access admin secrets!
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
      const urlParams = new URLSearchParams(window.location.search);
      const uuid = urlParams.get('uuid');

      if (uuid === '550e8400-e29b-41d4-a716-446655440001' && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>