<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_hard2_attempts'])) {
$_SESSION['idor_hard2_attempts'] = 0;
}
if (!isset($_SESSION['idor_hard2_solved'])) {
$_SESSION['idor_hard2_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'user', 'is_admin' => false, 'bio' => 'System admin'],
2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'user', 'is_admin' => false, 'bio' => 'Cybersec enthusiast'],
3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'user', 'is_admin' => false, 'bio' => 'CTF player'],
];

// ─── Vulnerable Mass Assignment + IDOR Logic ───────────────────────────────
// The app accepts ALL fields from POST and updates directly - including is_admin!
$update_msg = null;
$show_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
$_SESSION['idor_hard2_attempts']++;

// VULNERABLE: Mass assignment - accepts any field from POST
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 2;

if (isset($users[$user_id])) {
// Directly merge POST data into user record - no whitelist!
foreach ($_POST as $key => $value) {
if ($key !== 'update_profile' && isset($users[$user_id][$key])) {
  $users[$user_id][$key] = $value;
}
}
$update_msg = "Profile updated successfully!";
$show_success = true;
}
}

$current_user = $users[2]; // Default to alice

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_hard2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
$_SESSION['idor_hard2_attempts']++;

if (!$already_solved && isset($_SESSION['user_id'])) {
solveLab($pdo, $lab['id']);
}

$_SESSION['idor_hard2_solved'] = true;
$already_solved = true;
$success_msg = "Excellent! You've chained Mass Assignment with IDOR! You escalated privileges by injecting the admin flag while modifying another user's profile.";
}

$attempts = $_SESSION['idor_hard2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Editor - IDOR Hard 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-user-edit"></i> Profile Editor</h1>
      <p class="lab-description">Update your profile information. This challenge combines <strong>Mass
          Assignment</strong> with <strong>IDOR</strong>. The application accepts all fields from the form without
        validation. Can you escalate privileges? <strong>No field whitelist!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Mass Assignment + IDOR chain. You can continue exploring, but no additional
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

    <!-- Profile Form -->
    <div class="profile-editor">
      <div class="editor-header">
        <i class="fas fa-id-card"></i>
        <h2>Edit Profile</h2>
      </div>
      <form method="POST" action="" class="editor-form">
        <input type="hidden" name="user_id" value="2">
        <div class="form-group">
          <label><i class="fas fa-user"></i> Username</label>
          <input type="text" name="username" value="alice" class="form-control">
        </div>
        <div class="form-group">
          <label><i class="fas fa-envelope"></i> Email</label>
          <input type="email" name="email" value="alice@darkhunter.local" class="form-control">
        </div>
        <div class="form-group">
          <label><i class="fas fa-tag"></i> Role</label>
          <input type="text" name="role" value="user" class="form-control" disabled>
          <small class="form-hint">Role is managed by the system</small>
        </div>
        <div class="form-group">
          <label><i class="fas fa-align-left"></i> Bio</label>
          <textarea name="bio" class="form-control" rows="3">Cybersec enthusiast</textarea>
        </div>
        <button type="submit" name="update_profile" class="update-btn">
          <i class="fas fa-save"></i> Update Profile
        </button>
      </form>

      <?php if ($show_success): ?>
      <div class="update-feedback">
        <i class="fas fa-check-circle"></i>
        <p><?php echo $update_msg; ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- User Preview -->
    <div class="user-preview">
      <h3><i class="fas fa-eye"></i> Current Profile Data</h3>
      <div class="preview-card">
        <div class="preview-row">
          <span class="preview-label">User ID:</span>
          <span class="preview-value"><?php echo $current_user['id']; ?></span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Username:</span>
          <span class="preview-value"><?php echo htmlspecialchars($current_user['username']); ?></span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Email:</span>
          <span class="preview-value"><?php echo htmlspecialchars($current_user['email']); ?></span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Role:</span>
          <span class="preview-value <?php echo $current_user['role'] === 'admin' ? 'role-admin' : ''; ?>">
            <?php echo htmlspecialchars($current_user['role']); ?>
          </span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Admin Flag:</span>
          <span class="preview-value <?php echo $current_user['is_admin'] ? 'flag-true' : 'flag-false'; ?>">
            <?php echo $current_user['is_admin'] ? 'true' : 'false'; ?>
          </span>
        </div>
        <div class="preview-row">
          <span class="preview-label">Bio:</span>
          <span class="preview-value"><?php echo htmlspecialchars($current_user['bio']); ?></span>
        </div>
      </div>
    </div>

    <!-- Request Inspector -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Inspector</span>
      </div>
      <div class="debug-body">
        <p><strong>Method:</strong> POST</p>
        <p><strong>Endpoint:</strong> /IDOR-CASE6.php</p>
        <p><strong>Content-Type:</strong> application/x-www-form-urlencoded</p>
        <code>user_id=2&username=alice&email=alice@darkhunter.local&role=user&bio=Cybersec+enthusiast</code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The form has a hidden <code>user_id</code> field. What happens if you change it? Also, the
        backend might accept fields that aren't shown in the UI.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try using browser DevTools to add extra fields to the form before submitting. Fields like
        <code>is_admin</code>, <code>role</code>, or <code>permissions</code> might be accepted by the server.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Intercept the POST request and add <code>is_admin=true</code> to the body. The server
        accepts all fields without a whitelist, granting you admin privileges!</div>
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
    // Check if admin flag is true
    const adminFlag = document.querySelector('.flag-true');
    if (adminFlag && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>