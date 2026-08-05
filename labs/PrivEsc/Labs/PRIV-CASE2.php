<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case2_attempts'])) {
  $_SESSION['priv_case2_attempts'] = 0;
}
if (!isset($_SESSION['priv_case2_solved'])) {
  $_SESSION['priv_case2_solved'] = false;
}

// ─── Simulated JWT Token Logic ───────────────────────────────────────────
function base64UrlEncode($data)
{
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data)
{
  return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

// VULNERABLE: Weak JWT that accepts "none" algorithm
$default_header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
$default_payload = json_encode([
  'sub' => 'user123',
  'username' => 'user123',
  'role' => 'user',
  'iat' => time(),
  'exp' => time() + 3600
]);

$jwt_token = base64UrlEncode($default_header) . '.' . base64UrlEncode($default_payload) . '.fake_signature';

// ─── Handle Token Submission ─────────────────────────────────────────────
$token_submitted = false;
$token_valid = false;
$role_escalated = false;
$flag_triggered = false;
$decoded_token = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_token'])) {
  $submitted_token = $_POST['jwt_token'] ?? '';
  $token_submitted = true;

  // VULNERABLE: Weak validation - just checks structure, not signature
  $parts = explode('.', $submitted_token);
  if (count($parts) === 3) {
    $token_valid = true;
    $header = json_decode(base64UrlDecode($parts[0]), true);
    $payload = json_decode(base64UrlDecode($parts[1]), true);
    $decoded_token = $payload;

    // Check for algorithm confusion (alg: none)
    if (isset($header['alg']) && strtolower($header['alg']) === 'none') {
      $role_escalated = true;
    }

    // Check for role escalation in payload
    if (isset($payload['role']) && ($payload['role'] === 'admin' || $payload['role'] === 'administrator')) {
      $role_escalated = true;
      $flag_triggered = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case2_solved'] = true;
  $already_solved = true;
  $success_msg = "Great job! You've successfully exploited a JWT Token Tampering vulnerability. By modifying the JWT payload to change your role to admin, or by using the 'none' algorithm, you bypassed weak signature validation and escalated your privileges!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_token'])) {
  $_SESSION['priv_case2_attempts']++;
}

$attempts = $_SESSION['priv_case2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JWT Auth - PrivEsc Case 2 (JWT Token Tampering)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-key"></i> JWT Authentication</h1>
      <p class="lab-description">Test your JWT token against our authentication system. This easy Privilege Escalation
        challenge uses <strong>weak JWT validation</strong>. Modify the token payload to escalate your role!</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this JWT Token Tampering vulnerability. You can continue exploring, but no
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

    <!-- JWT Grid -->
    <div class="jwt-grid">

      <!-- Token Input -->
      <div class="jwt-card input-card">
        <div class="card-header">
          <i class="fas fa-edit"></i>
          <h3>JWT Token</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Weak Validation</span>
        </div>

        <form method="POST" action="" class="jwt-form" id="jwt-form">
          <input type="hidden" name="submit_token" value="1">

          <div class="form-group">
            <label><i class="fas fa-fingerprint"></i> Your JWT Token</label>
            <textarea name="jwt_token" rows="6" class="form-textarea"
              placeholder="Paste your JWT token here..."><?php echo isset($_POST['jwt_token']) ? htmlspecialchars($_POST['jwt_token']) : $jwt_token; ?></textarea>
          </div>

          <button type="submit" class="btn-verify">
            <i class="fas fa-shield-alt"></i> Verify Token
          </button>
        </form>

        <div class="token-info">
          <span class="info-label">Default Token:</span>
          <code class="token-preview"><?php echo htmlspecialchars($jwt_token); ?></code>
        </div>
      </div>

      <!-- Token Decoder -->
      <div class="jwt-card decoder-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Token Decoder</h3>
        </div>

        <?php if ($token_submitted && $decoded_token): ?>
        <div class="decoded-content">
          <div class="decoded-section">
            <span class="section-label">Header</span>
            <pre
              class="section-code"><code><?php echo json_encode(json_decode(base64UrlDecode(explode('.', $_POST['jwt_token'])[0]), true), JSON_PRETTY_PRINT); ?></code></pre>
          </div>
          <div class="decoded-section">
            <span class="section-label">Payload</span>
            <pre class="section-code"><code><?php echo json_encode($decoded_token, JSON_PRETTY_PRINT); ?></code></pre>
          </div>
        </div>

        <?php if ($role_escalated): ?>
        <div class="escalation-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Privilege escalation detected in token payload!</span>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="decoder-placeholder">
          <i class="fas fa-lock-open"></i>
          <p>Submit a token to see the decoded header and payload</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- JWT Analysis -->
      <div class="jwt-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>JWT Validation Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Signature Validation:</span>
            <code class="analysis-code">// Weak - accepts any signature</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Weak</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Algorithm Check:</span>
            <code class="analysis-code">// Accepts "none" algorithm</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Vulnerable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Role Validation:</span>
            <code class="analysis-code">// No role verification</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="jwt-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>JWT Tampering Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Change Role to Admin</div>
            <code class="payload-code">{ "role": "admin", "sub": "user123" }</code>
            <span class="payload-target">Modify payload claims</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Algorithm Confusion (none)</div>
            <code class="payload-code">{ "alg": "none", "typ": "JWT" }</code>
            <span class="payload-target">Remove signature requirement</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Add Admin Permissions</div>
            <code class="payload-code">{ "permissions": ["admin", "read", "write"] }</code>
            <span class="payload-target">Inject permission array</span>
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
          <span>Token Submitted: <?php echo $token_submitted ? 'YES' : 'NO'; ?></span>
          <span>Token Valid: <?php echo $token_valid ? 'YES' : 'NO'; ?></span>
          <span>Role Escalated: <?php echo $role_escalated ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The JWT token consists of three parts: header.payload.signature. Try decoding the payload
        with base64, changing the <code>role</code> from "user" to "admin", and re-encoding it.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The server accepts the "none" algorithm! Try changing the header to
        <code>{"alg":"none","typ":"JWT"}</code> and remove the signature (just keep header.payload. with the trailing
        dot).
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Decode the default token payload, change <code>"role":"user"</code> to
        <code>"role":"admin"</code>, base64url encode it back, and submit. Or use
        <code>eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.</code> as header (alg:none) followed by your modified payload and a
        trailing dot.
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