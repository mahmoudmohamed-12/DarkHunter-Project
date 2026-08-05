<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case4_attempts'])) {
  $_SESSION['priv_case4_attempts'] = 0;
}
if (!isset($_SESSION['priv_case4_solved'])) {
  $_SESSION['priv_case4_solved'] = false;
}

// VULNERABLE: Predictable session ID generation
function generatePredictableSessionId()
{
  return 'sess_' . date('Ymd') . '_' . rand(1000, 9999);
}

// ─── Handle Session Actions ──────────────────────────────────────────────
$session_fixed = false;
$session_hijacked = false;
$flag_triggered = false;
$current_session = session_id();
$predicted_session = generatePredictableSessionId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['fixate_session'])) {
    // VULNERABLE: Accept user-provided session ID
    $provided_sid = $_POST['session_id'] ?? '';
    if (!empty($provided_sid)) {
      session_write_close();
      session_id($provided_sid);
      session_start();
      $session_fixed = true;
      $current_session = $provided_sid;

      // Check if they predicted the admin session
      if (strpos($provided_sid, 'sess_') === 0 && strlen($provided_sid) === 19) {
        $session_hijacked = true;
      }

      // Check for admin session pattern
      if ($provided_sid === 'sess_admin_0001' || $provided_sid === 'sess_' . date('Ymd') . '_0001') {
        $flag_triggered = true;
      }
    }
  }

  if (isset($_POST['predict_session'])) {
    // VULNERABLE: Predictable session ID
    $predicted_sid = generatePredictableSessionId();
    $session_fixed = true;
    $session_hijacked = true;
    $current_session = $predicted_sid;
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case4_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case4_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case4_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Session Fixation vulnerability. By predicting or fixing the admin's session ID, you hijacked their authenticated session and gained administrator privileges!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['priv_case4_attempts']++;
}

$attempts = $_SESSION['priv_case4_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Session Manager - PrivEsc Case 4 (Session Fixation)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-fingerprint"></i> Session Manager</h1>
      <p class="lab-description">Manage your session settings. This medium Privilege Escalation challenge has
        <strong>predictable session IDs</strong> and accepts user-provided session identifiers. <strong>Hijack the admin
          session!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Session Fixation vulnerability. You can continue exploring, but no additional
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

    <!-- Session Grid -->
    <div class="session-grid">

      <!-- Session Info -->
      <div class="session-card info-card">
        <div class="card-header">
          <i class="fas fa-info-circle"></i>
          <h3>Current Session</h3>
        </div>

        <div class="session-info">
          <div class="info-item">
            <span class="info-label">Session ID:</span>
            <code class="info-value"><?php echo htmlspecialchars($current_session); ?></code>
          </div>
          <div class="info-item">
            <span class="info-label">Predicted Pattern:</span>
            <code class="info-value">sess_YYYYMMDD_XXXX</code>
          </div>
          <div class="info-item">
            <span class="info-label">Today's Prediction:</span>
            <code class="info-value vuln"><?php echo htmlspecialchars($predicted_session); ?></code>
          </div>
        </div>
      </div>

      <!-- Session Fixation (Vulnerable) -->
      <div class="session-card fixate-card">
        <div class="card-header">
          <i class="fas fa-lock-open"></i>
          <h3>Fixate Session</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Accepts Any SID</span>
        </div>

        <form method="POST" action="" class="session-form" id="session-form">
          <input type="hidden" name="fixate_session" value="1">

          <div class="form-group">
            <label><i class="fas fa-fingerprint"></i> Session ID</label>
            <input type="text" name="session_id" placeholder="Enter session ID..." class="form-input">
            <span class="field-hint">Try: sess_admin_0001 or predict the pattern</span>
          </div>

          <button type="submit" class="btn-fixate">
            <i class="fas fa-exchange-alt"></i> Set Session ID
          </button>
        </form>

        <div class="predict-section">
          <span class="predict-label">Or predict admin session:</span>
          <form method="POST" action="" class="predict-form">
            <input type="hidden" name="predict_session" value="1">
            <button type="submit" class="btn-predict">
              <i class="fas fa-brain"></i> Predict & Hijack
            </button>
          </form>
        </div>

        <?php if ($session_fixed): ?>
          <div class="fixate-alert">
            <i class="fas fa-check-circle"></i>
            <span>Session fixed to: <code><?php echo htmlspecialchars($current_session); ?></code></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Session Analysis -->
      <div class="session-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Session Handling Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">ID Generation:</span>
            <code class="analysis-code">'sess_' . date('Ymd') . '_' . rand(1000, 9999)</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Predictable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">ID Validation:</span>
            <code class="analysis-code">// Accepts any user-provided SID</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> None</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Regeneration:</span>
            <code class="analysis-code">// No regeneration on login</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
        </div>
      </div>

      <!-- Hijack Techniques -->
      <div class="session-card payloads-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>Session Hijack Techniques</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Predictable ID Guessing</div>
            <code class="payload-code">sess_20260522_0001</code>
            <span class="payload-target">Guess admin session based on pattern</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Session Fixation</div>
            <code class="payload-code">?PHPSESSID=sess_admin_0001</code>
            <span class="payload-target">Force specific session ID</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Brute Force Range</div>
            <code class="payload-code">for ($i = 1000; $i <= 9999; $i++) { ... }</code>
                <span class="payload-target">Brute force the 4-digit suffix</span>
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
          <span>Session ID: <?php echo htmlspecialchars(substr($current_session, 0, 30)); ?></span>
          <span>Session Fixed: <?php echo $session_fixed ? 'YES' : 'NO'; ?></span>
          <span>Session Hijacked: <?php echo $session_hijacked ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The session ID follows a predictable pattern: <code>sess_YYYYMMDD_XXXX</code>. Try entering
          a session ID that looks like an admin session, such as <code>sess_admin_0001</code> or today's date with a low
          number.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The server accepts ANY session ID you provide without validation. Try using
          <code>sess_admin_0001</code> or predict today's session using the pattern
          <code>sess_<?php echo date('Ymd'); ?>_0001</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Enter <code>sess_admin_0001</code> in the Session ID field and click "Set Session ID". The
          server will accept this as your session ID, effectively hijacking the admin session. Or use the "Predict &
          Hijack" button!</div>
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
      const hijackAlert = document.querySelector('.fixate-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (hijackAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>