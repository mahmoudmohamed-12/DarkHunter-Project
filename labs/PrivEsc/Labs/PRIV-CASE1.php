<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case1_attempts'])) {
  $_SESSION['priv_case1_attempts'] = 0;
}
if (!isset($_SESSION['priv_case1_solved'])) {
  $_SESSION['priv_case1_solved'] = false;
}

// ─── Simulated User Data ─────────────────────────────────────────────────
$users = [
  ['id' => 1, 'username' => 'admin', 'role' => 'administrator', 'email' => 'admin@darkhunter.local'],
  ['id' => 2, 'username' => 'moderator', 'role' => 'moderator', 'email' => 'mod@darkhunter.local'],
  ['id' => 3, 'username' => 'user123', 'role' => 'user', 'email' => 'user@darkhunter.local'],
];

// ─── Vulnerable Role Update Logic ────────────────────────────────────────
$update_success = false;
$role_changed = false;
$flag_triggered = false;
$current_role = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  // VULNERABLE: Role parameter is accepted from user input without validation
  $username = $_POST['username'] ?? 'user123';
  $email = $_POST['email'] ?? 'user@darkhunter.local';
  $role = $_POST['role'] ?? 'user'; // VULNERABLE: User can set any role!

  $current_role = $role;
  $update_success = true;

  // Check if role was changed from default
  if ($role !== 'user') {
    $role_changed = true;
  }

  // Check for admin escalation
  if ($role === 'administrator' || $role === 'admin') {
    $flag_triggered = true;
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited an IDOR Role Bypass vulnerability. By manipulating the role parameter in the profile update form, you escalated from a regular user to administrator privileges!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $_SESSION['priv_case1_attempts']++;
}

$attempts = $_SESSION['priv_case1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile - PrivEsc Case 1 (IDOR Role Bypass)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-user-shield"></i> User Profile Settings</h1>
      <p class="lab-description">Update your profile information. This easy Privilege Escalation challenge accepts role
        parameters from user input. <strong>No server-side validation!</strong> Change your role to escalate privileges.
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this IDOR Role Bypass vulnerability. You can continue exploring, but no additional
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

    <?php if ($update_success): ?>
    <div class="update-alert">
      <i class="fas fa-check-circle"></i>
      <span>Profile updated successfully! Current role:
        <strong><?php echo htmlspecialchars($current_role); ?></strong></span>
    </div>
    <?php endif; ?>

    <!-- Profile Grid -->
    <div class="profile-grid">

      <!-- Profile Preview -->
      <div class="profile-card preview-card">
        <div class="card-header">
          <i class="fas fa-eye"></i>
          <h3>Profile Preview</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Vulnerable</span>
        </div>

        <div class="profile-preview">
          <div class="preview-avatar">
            <i class="fas fa-user-circle"></i>
          </div>
          <div class="preview-info">
            <h2 class="preview-name">user123</h2>
            <p class="preview-role">
              <i class="fas fa-id-badge"></i>
              Role: <span
                class="role-badge <?php echo $current_role; ?>"><?php echo htmlspecialchars($current_role); ?></span>
            </p>
            <p class="preview-email">
              <i class="fas fa-envelope"></i>
              user@darkhunter.local
            </p>
          </div>
        </div>

        <?php if ($role_changed): ?>
        <div class="escalation-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Privilege escalation detected! Role changed from <strong>user</strong> to
            <strong><?php echo htmlspecialchars($current_role); ?></strong></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Profile Edit Form (Vulnerable) -->
      <div class="profile-card edit-card">
        <div class="card-header">
          <i class="fas fa-edit"></i>
          <h3>Edit Profile</h3>
        </div>

        <form method="POST" action="" class="edit-form" id="edit-form">
          <input type="hidden" name="update_profile" value="1">

          <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" value="user123" class="form-input" readonly>
          </div>

          <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" value="user@darkhunter.local" class="form-input">
          </div>

          <!-- VULNERABLE: Role can be modified by user -->
          <div class="form-group">
            <label><i class="fas fa-id-badge"></i> Role</label>
            <select name="role" class="form-select">
              <option value="user" <?php echo $current_role === 'user' ? 'selected' : ''; ?>>User</option>
              <option value="moderator" <?php echo $current_role === 'moderator' ? 'selected' : ''; ?>>Moderator
              </option>
              <option value="administrator" <?php echo $current_role === 'administrator' ? 'selected' : ''; ?>>
                Administrator</option>
            </select>
            <span class="field-hint">Vulnerable: User can select any role!</span>
          </div>

          <button type="submit" class="btn-update">
            <i class="fas fa-save"></i> Update Profile
          </button>
        </form>
      </div>

      <!-- Role Analysis -->
      <div class="profile-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Role Validation Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="context-item">
            <span class="context-label">Server-Side Validation:</span>
            <code class="context-code">// NONE - Role accepted directly from POST</code>
            <span class="context-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="context-item">
            <span class="context-label">Client-Side Restriction:</span>
            <code class="context-code">&lt;select name="role"&gt;...&lt;/select&gt;</code>
            <span class="context-status vuln"><i class="fas fa-times-circle"></i> Bypassable</span>
          </div>
          <div class="context-item">
            <span class="context-label">Secure Alternative:</span>
            <code class="context-code">if ($role !== 'user') { reject(); }</code>
            <span class="context-status safe"><i class="fas fa-check-circle"></i> Enforced</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="profile-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Role Bypass Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Select Admin from Dropdown</div>
            <code class="payload-code">role=administrator</code>
            <span class="payload-target">Target: Role select field</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Burp Suite Modification</div>
            <code class="payload-code">POST /profile { "role": "administrator" }</code>
            <span class="payload-target">Target: Intercepted request</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Browser DevTools Edit</div>
            <code class="payload-code">document.querySelector('[name=role]').value='administrator'</code>
            <span class="payload-target">Target: DOM manipulation</span>
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
          <span>Current Role: <?php echo htmlspecialchars($current_role); ?></span>
          <span>Role Changed: <?php echo $role_changed ? 'YES' : 'NO'; ?></span>
          <span>Admin Escalation: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The role dropdown allows you to select any role including <strong>Administrator</strong>.
        Try changing the role and updating your profile. The server does not validate if you are allowed to have that
        role!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">You can also use browser developer tools or Burp Suite to modify the request. The server
        accepts any value for the <code>role</code> parameter without checking your current privileges.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Simply select <strong>Administrator</strong> from the role dropdown and click "Update
        Profile". The server will accept the new role without any validation, granting you admin privileges!</div>
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
    const escalationAlert = document.querySelector('.escalation-alert');
    const alreadySolved = document.querySelector('.solved-banner');

    if (escalationAlert && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>