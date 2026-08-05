<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_easy1_attempts'])) {
  $_SESSION['idor_easy1_attempts'] = 0;
}
if (!isset($_SESSION['idor_easy1_solved'])) {
  $_SESSION['idor_easy1_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'bio' => 'System administrator with full privileges.', 'notes' => 'Flag: DH{idor_admin_pwned}'],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'User', 'bio' => 'Cybersecurity enthusiast.', 'notes' => 'Personal notes: Working on XSS labs.'],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'User', 'bio' => 'CTF player and bug hunter.', 'notes' => 'Personal notes: Found a cool SQLi.'],
  4 => ['id' => 4, 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'role' => 'User', 'bio' => 'Learning web security.', 'notes' => 'Personal notes: Need to practice IDOR.'],
  5 => ['id' => 5, 'username' => 'dave', 'email' => 'dave@darkhunter.local', 'role' => 'User', 'bio' => 'Penetration tester.', 'notes' => 'Personal notes: Preparing for OSCP.'],
];

// ─── Vulnerable IDOR Logic ────────────────────────────────────────────────
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 2;

if (!isset($users[$user_id])) {
  $user_id = 2;
}

$current_user = $users[$user_id];
$is_admin = ($user_id === 1);

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_easy1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_easy1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['idor_easy1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited an Insecure Direct Object Reference (IDOR) vulnerability. You accessed unauthorized user data by manipulating the user_id parameter!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
  $_SESSION['idor_easy1_attempts']++;
}

$attempts = $_SESSION['idor_easy1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile - IDOR Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-user-shield"></i> User Profile Viewer</h1>
      <p class="lab-description">View your public profile. This is a beginner-friendly IDOR challenge. Try to access
        other users profiles by manipulating the URL parameter. <strong>No access control applied!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this IDOR vulnerability. You can continue exploring, but no additional points will
          be awarded.</p>
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

    <!-- Profile Card -->
    <div class="profile-card <?php echo $is_admin ? 'admin-profile' : ''; ?>">
      <div class="profile-banner"></div>
      <div class="profile-avatar">
        <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
      </div>
      <div class="profile-info">
        <h2 class="profile-name"><?php echo htmlspecialchars($current_user['username']); ?></h2>
        <span class="profile-role <?php echo $is_admin ? 'role-admin' : 'role-user'; ?>">
          <i class="fas fa-<?php echo $is_admin ? 'crown' : 'user'; ?>"></i>
          <?php echo $current_user['role']; ?>
        </span>
        <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($current_user['email']); ?>
        </p>
        <p class="profile-bio"><?php echo htmlspecialchars($current_user['bio']); ?></p>
      </div>
    </div>

    <!-- Notes Section -->
    <div class="notes-card">
      <div class="notes-header">
        <i class="fas fa-sticky-note"></i>
        <h3>Private Notes</h3>
        <?php if ($is_admin): ?>
        <span class="secret-badge"><i class="fas fa-lock-open"></i> Admin Access</span>
        <?php endif; ?>
      </div>
      <div class="notes-body">
        <p><?php echo htmlspecialchars($current_user['notes']); ?></p>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /IDOR-CASE1.php?user_id=<?php echo $user_id; ?></code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Look at the URL parameter <code>user_id</code>. What happens if you change its value to
        another number? Try <code>?user_id=1</code> or <code>?user_id=3</code>.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The application does not verify if the requested user_id belongs to the currently logged-in
        user. Try enumerating user IDs to find the admin account.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Access the admin profile by changing the URL to <code>?user_id=1</code>. The admin notes
        contain the flag. Once you see the admin data, the challenge is solved!</div>
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
    const userId = urlParams.get('user_id');

    if (userId === '1' && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>