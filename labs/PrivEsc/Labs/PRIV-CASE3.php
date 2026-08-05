<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case3_attempts'])) {
  $_SESSION['priv_case3_attempts'] = 0;
}
if (!isset($_SESSION['priv_case3_solved'])) {
  $_SESSION['priv_case3_solved'] = false;
}

// ─── Simulated User Registration ─────────────────────────────────────────
$registered = false;
$mass_assigned = false;
$flag_triggered = false;
$user_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
  // VULNERABLE: Mass assignment - accepts all fields without filtering
  $user_data = [
    'username' => $_POST['username'] ?? '',
    'email' => $_POST['email'] ?? '',
    'password' => $_POST['password'] ?? '',
    // VULNERABLE: These fields should not be user-controllable!
    'role' => $_POST['role'] ?? 'user',
    'is_admin' => $_POST['is_admin'] ?? '0',
    'is_superuser' => $_POST['is_superuser'] ?? '0',
    'credits' => $_POST['credits'] ?? '0',
    'verified' => $_POST['verified'] ?? '0',
  ];

  $registered = true;

  // Check for mass assignment attack
  if ($user_data['role'] !== 'user' || $user_data['is_admin'] === '1' || $user_data['is_superuser'] === '1') {
    $mass_assigned = true;
  }

  if ($user_data['role'] === 'administrator' || $user_data['is_admin'] === '1') {
    $flag_triggered = true;
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case3_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a Mass Assignment vulnerability. By injecting hidden admin fields into the registration form, you auto-escalated your privileges during account creation!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
  $_SESSION['priv_case3_attempts']++;
}

$attempts = $_SESSION['priv_case3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration - PrivEsc Case 3 (Mass Assignment)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-user-plus"></i> User Registration</h1>
      <p class="lab-description">Create your DarkHunter account. This medium Privilege Escalation challenge uses
        <strong>overly permissive model binding</strong>. <strong>No field filtering!</strong> Inject hidden admin
        fields to auto-escalate.
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Mass Assignment vulnerability. You can continue exploring, but no additional
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

    <!-- Registration Grid -->
    <div class="register-grid">

      <!-- Registration Form (Vulnerable) -->
      <div class="register-card form-card">
        <div class="card-header">
          <i class="fas fa-user-plus"></i>
          <h3>Create Account</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Mass Assignment</span>
        </div>

        <?php if ($registered): ?>
        <div class="register-alert">
          <i class="fas fa-check-circle"></i>
          <span>Account created successfully!</span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="register-form" id="register-form">
          <input type="hidden" name="register" value="1">

          <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" placeholder="Choose username..." class="form-input" required>
          </div>

          <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" placeholder="your@email.com" class="form-input" required>
          </div>

          <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" placeholder="Choose password..." class="form-input" required>
          </div>

          <!-- VULNERABLE: Hidden fields that can be mass assigned -->
          <div class="hidden-fields-hint">
            <i class="fas fa-eye-slash"></i>
            <span>Hidden fields exist! Use browser dev tools or intercept the request.</span>
          </div>

          <button type="submit" class="btn-register">
            <i class="fas fa-user-plus"></i> Create Account
          </button>
        </form>

        <div class="form-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <span>The server accepts ALL fields without filtering!</span>
        </div>
      </div>

      <!-- Registration Result -->
      <div class="register-card result-card">
        <div class="card-header">
          <i class="fas fa-id-card"></i>
          <h3>Account Details</h3>
        </div>

        <?php if ($registered && $user_data): ?>
        <div class="user-details">
          <div class="detail-item">
            <span class="detail-label">Username:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user_data['username']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Role:</span>
            <span
              class="detail-value role-badge <?php echo $user_data['role']; ?>"><?php echo htmlspecialchars($user_data['role']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Is Admin:</span>
            <span
              class="detail-value <?php echo $user_data['is_admin'] === '1' ? 'danger' : ''; ?>"><?php echo $user_data['is_admin'] === '1' ? 'YES' : 'NO'; ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Is Superuser:</span>
            <span
              class="detail-value <?php echo $user_data['is_superuser'] === '1' ? 'danger' : ''; ?>"><?php echo $user_data['is_superuser'] === '1' ? 'YES' : 'NO'; ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Credits:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user_data['credits']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Verified:</span>
            <span class="detail-value"><?php echo $user_data['verified'] === '1' ? 'YES' : 'NO'; ?></span>
          </div>
        </div>

        <?php if ($mass_assigned): ?>
        <div class="escalation-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <div class="escalation-content">
            <strong>Mass Assignment Detected!</strong>
            <span>Hidden fields were injected during registration!</span>
          </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="result-placeholder">
          <i class="fas fa-user-circle"></i>
          <p>Register an account to see the details</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Mass Assignment Analysis -->
      <div class="register-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Model Binding Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Vulnerable Code:</span>
            <pre class="analysis-code"><code>// DANGEROUS: Accepts ALL POST fields
$user = new User();
foreach ($_POST as $key => $value) {
  $user->$key = $value; // No filtering!
}
$user->save();</code></pre>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Code:</span>
            <pre class="analysis-code secure"><code>// SAFE: Whitelist allowed fields
$allowed = ['username', 'email', 'password'];
foreach ($allowed as $field) {
  $user->$field = $_POST[$field];
}</code></pre>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="register-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Mass Assignment Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Hidden Role Field</div>
            <code class="payload-code">&lt;input type="hidden" name="role" value="administrator"&gt;</code>
            <span class="payload-target">Inject admin role</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Admin Flag</div>
            <code class="payload-code">&lt;input type="hidden" name="is_admin" value="1"&gt;</code>
            <span class="payload-target">Set admin boolean</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Superuser Flag</div>
            <code class="payload-code">&lt;input type="hidden" name="is_superuser" value="1"&gt;</code>
            <span class="payload-target">Set superuser boolean</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Free Credits</div>
            <code class="payload-code">&lt;input type="hidden" name="credits" value="99999"&gt;</code>
            <span class="payload-target">Modify credit balance</span>
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
          <span>Registered: <?php echo $registered ? 'YES' : 'NO'; ?></span>
          <span>Mass Assigned: <?php echo $mass_assigned ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The registration form only shows username, email, and password fields. But the server
        accepts <strong>ALL</strong> POST parameters. Try adding hidden fields like <code>role</code> or
        <code>is_admin</code> using browser developer tools or Burp Suite.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Use browser DevTools (F12) → Elements tab. Find the form and add:
        <code>&lt;input type="hidden" name="role" value="administrator"&gt;</code> before submitting. The server will
        save this field without validation!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Right-click the form → Inspect. Add these hidden inputs:
        <code>&lt;input type="hidden" name="role" value="administrator"&gt;</code> and
        <code>&lt;input type="hidden" name="is_admin" value="1"&gt;</code>. Submit the form and watch your account get
        admin privileges automatically!
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