<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case4_attempts'])) {
  $_SESSION['jsonp_case4_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case4_solved'])) {
  $_SESSION['jsonp_case4_solved'] = false;
}

// VULNERABLE: Weak callback whitelist - only checks alphanumeric but allows dangerous patterns
$callback = $_GET['callback'] ?? 'callback';
$blocked = false;
$whitelist_passed = false;

// Weak validation: only checks if callback starts with a letter and contains alphanumeric
if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $callback)) {
  $whitelist_passed = true;
} else {
  $blocked = true;
}

// But we can bypass using: constructor, __proto__, prototype, etc.
$data = ['status' => 'ok', 'data' => 'secret_info'];

$flag_triggered = false;
if (isset($_GET['callback']) && $whitelist_passed) {
  $cb = $_GET['callback'];
  // Check for bypass patterns
  if (
    stripos($cb, 'constructor') !== false ||
    stripos($cb, '__proto__') !== false ||
    stripos($cb, 'prototype') !== false ||
    stripos($cb, 'window') !== false ||
    stripos($cb, 'self') !== false ||
    stripos($cb, 'top') !== false ||
    stripos($cb, 'parent') !== false ||
    stripos($cb, 'location') !== false ||
    stripos($cb, 'document') !== false
  ) {
    $flag_triggered = true;
  }
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case4_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case4_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case4_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've bypassed the callback whitelist restriction. By using prototype pollution vectors, window object references, or constructor chains, you executed code despite the alphanumeric-only filter!";
}

if (isset($_GET['callback'])) {
  $_SESSION['jsonp_case4_attempts']++;
}

$attempts = $_SESSION['jsonp_case4_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure API - JSONP Case 4 (Callback Restriction Bypass)</title>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-shield-alt"></i> "Secure" JSONP API</h1>
      <p class="lab-description">Test our "secure" JSONP endpoint. This medium challenge has a <strong>weak callback whitelist</strong>. Bypass the alphanumeric-only check using prototype pollution and window object manipulation!</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Callback Restriction Bypass vulnerability.</p>
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
      <div class="api-indicator <?php echo $whitelist_passed ? 'pass' : 'block'; ?>">
        <i class="fas fa-<?php echo $whitelist_passed ? 'check-circle' : 'ban'; ?>"></i>
        <span>Whitelist: <strong><?php echo $whitelist_passed ? 'PASSED' : 'BLOCKED'; ?></strong></span>
      </div>
      <div class="callback-indicator">
        <i class="fas fa-code"></i>
        <span>Callback: <code><?php echo htmlspecialchars($callback); ?></code></span>
      </div>
      <?php if ($flag_triggered): ?>
        <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>BYPASS DETECTED!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card whitelist-card">
        <div class="card-header">
          <i class="fas fa-list-check"></i>
          <h3>Whitelist Rules</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Bypassable</span>
        </div>
        <div class="whitelist-content">
          <div class="rule-item">
            <span class="rule-check"><i class="fas fa-check"></i></span>
            <span class="rule-text">Must start with a letter</span>
          </div>
          <div class="rule-item">
            <span class="rule-check"><i class="fas fa-check"></i></span>
            <span class="rule-text">Only alphanumeric + underscore</span>
          </div>
          <div class="rule-item">
            <span class="rule-check"><i class="fas fa-times"></i></span>
            <span class="rule-text">No special characters: ()[]{}<>./</span>
          </div>
          <div class="rule-item vuln-rule">
            <span class="rule-check"><i class="fas fa-exclamation-triangle"></i></span>
            <span class="rule-text">BUT: Object properties are valid!</span>
          </div>
          <div class="regex-display">
            <span class="regex-label">Validation Regex:</span>
            <code class="regex-code">/^[a-zA-Z][a-zA-Z0-9_]*$/</code>
          </div>
        </div>
      </div>

      <div class="jsonp-card bypass-card">
        <div class="card-header">
          <i class="fas fa-unlock-alt"></i>
          <h3>Bypass Techniques</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Working</span>
        </div>
        <div class="bypass-content">
          <div class="bypass-item">
            <span class="bypass-name">Window Object</span>
            <code class="bypass-code">?callback=window</code>
            <span class="bypass-desc">Access global window object</span>
          </div>
          <div class="bypass-item">
            <span class="bypass-name">Location Hijack</span>
            <code class="bypass-code">?callback=location</code>
            <span class="bypass-desc">Redirect to attacker site</span>
          </div>
          <div class="bypass-item">
            <span class="bypass-name">Constructor Chain</span>
            <code class="bypass-code">?callback=constructor</code>
            <span class="bypass-desc">Access Function constructor</span>
          </div>
          <div class="bypass-item">
            <span class="bypass-name">Prototype Pollution</span>
            <code class="bypass-code">?callback=__proto__</code>
            <span class="bypass-desc">Pollute object prototype</span>
          </div>
          <div class="bypass-item">
            <span class="bypass-name">Self Reference</span>
            <code class="bypass-code">?callback=self</code>
            <span class="bypass-desc">Reference to window.self</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card response-card">
        <div class="card-header"><i class="fas fa-terminal"></i>
          <h3>Live Response</h3>
        </div>
        <div class="response-content">
          <div class="response-label">Current JSONP Response:</div>
          <?php if ($blocked): ?>
            <pre class="response-code blocked"><code>// BLOCKED: Callback does not match whitelist pattern
// Your callback: <?php echo htmlspecialchars($callback); ?>

// Allowed format: /^[a-zA-Z][a-zA-Z0-9_]*$/</code></pre>
          <?php else: ?>
            <pre class="response-code"><code><?php echo htmlspecialchars($callback); ?>(<?php echo json_encode($data, JSON_PRETTY_PRINT); ?>);</code></pre>
          <?php endif; ?>
          <div class="response-status <?php echo $blocked ? 'blocked' : 'allowed'; ?>">
            <i class="fas fa-<?php echo $blocked ? 'ban' : 'check-circle'; ?>"></i>
            <span><?php echo $blocked ? 'Request blocked by whitelist' : 'Request passed whitelist validation'; ?></span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>Bypass Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Window Alert</div>
            <code class="payload-code">?callback=window</code>
            <span class="payload-target">window({"status":"ok"...}) - executes on global</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Location Redirect</div>
            <code class="payload-code">?callback=location</code>
            <span class="payload-target">location({...}) - may cause navigation</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Prototype Chain</div>
            <code class="payload-code">?callback=constructor</code>
            <span class="payload-target">constructor({...}) - Function constructor</span>
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
          <span>Whitelist: <?php echo $whitelist_passed ? 'PASSED' : 'BLOCKED'; ?></span>
          <span>Bypass: <?php echo $flag_triggered ? 'DETECTED' : 'NONE'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The whitelist only allows alphanumeric characters. But JavaScript object properties like <code>window</code>, <code>location</code>, <code>constructor</code>, and <code>__proto__</code> are all valid function names that match the pattern!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try <code>?callback=window</code> or <code>?callback=location</code>. These are valid identifiers that pass the regex but reference powerful global objects. The JSONP response becomes <code>window({"status":"ok"...})</code>!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?callback=constructor</code> or <code>?callback=__proto__</code> to trigger the flag. These bypass the whitelist by using valid JavaScript identifiers that reference dangerous object properties!</div>
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