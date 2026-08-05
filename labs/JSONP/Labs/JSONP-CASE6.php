<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case6_attempts'])) {
  $_SESSION['jsonp_case6_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case6_solved'])) {
  $_SESSION['jsonp_case6_solved'] = false;
}

// VULNERABLE: JSONP used as CSP bypass vector
$callback = $_GET['callback'] ?? 'callback';

// Strict CSP header that would normally block inline scripts
$csp_header = "default-src 'self'; script-src 'self' 'unsafe-eval' *.darkhunter.local; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';";

// The JSONP endpoint is whitelisted in CSP, making it a bypass vector
$data = [
  'message' => 'CSP Bypass via JSONP',
  'vector' => 'callback injection',
  'impact' => 'arbitrary code execution'
];

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'Function') !== false ||
    stripos($cb, 'setTimeout') !== false ||
    stripos($cb, 'setInterval') !== false ||
    stripos($cb, 'location') !== false ||
    stripos($cb, 'document') !== false ||
    stripos($cb, 'window') !== false
  ) {
    $flag_triggered = true;
  }
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case6_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case6_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case6_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully used JSONP as a CSP bypass vector. By injecting code through the whitelisted JSONP callback, you executed arbitrary JavaScript despite the strict Content Security Policy!";
}

if (isset($_GET['callback'])) {
  $_SESSION['jsonp_case6_attempts']++;
}

$attempts = $_SESSION['jsonp_case6_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CSP Test Page - JSONP Case 6 (CSP Bypass Vector)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE6.css">
  <!-- VULNERABLE: CSP allows this JSONP endpoint -->
  <meta http-equiv="Content-Security-Policy" content="<?php echo $csp_header; ?>">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-shield-alt"></i> CSP Test Page</h1>
      <p class="lab-description">Test our Content Security Policy. This hard challenge has a <strong>strict CSP that
          whitelists a JSONP endpoint</strong>. Use the whitelisted JSONP to bypass CSP and execute code!</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CSP Bypass Vector vulnerability.</p>
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
      <div class="api-indicator"><i class="fas fa-shield-alt"></i><span>CSP: <strong>Strict</strong></span></div>
      <div class="csp-indicator"><i class="fas fa-check-circle"></i><span>JSONP: <strong>Whitelisted</strong></span>
      </div>
      <?php if ($flag_triggered): ?>
      <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>CSP BYPASSED!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card csp-card">
        <div class="card-header">
          <i class="fas fa-file-shield"></i>
          <h3>Current CSP Policy</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Bypassable</span>
        </div>
        <div class="csp-content">
          <pre class="csp-code"><code><?php echo wordwrap($csp_header, 60, "
", true); ?></code></pre>
          <div class="csp-breakdown">
            <div class="csp-rule">
              <span class="rule-directive">script-src</span>
              <span class="rule-value">'self' 'unsafe-eval' *.darkhunter.local</span>
              <span class="rule-status vuln"><i class="fas fa-exclamation-triangle"></i> JSONP allowed</span>
            </div>
            <div class="csp-rule">
              <span class="rule-directive">default-src</span>
              <span class="rule-value">'self'</span>
              <span class="rule-status safe"><i class="fas fa-check-circle"></i> Restrictive</span>
            </div>
            <div class="csp-rule">
              <span class="rule-directive">connect-src</span>
              <span class="rule-value">'self'</span>
              <span class="rule-status safe"><i class="fas fa-check-circle"></i> Restrictive</span>
            </div>
          </div>
          <div class="csp-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>The JSONP endpoint is whitelisted in script-src, making it a perfect CSP bypass vector!</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card bypass-card">
        <div class="card-header">
          <i class="fas fa-unlock-alt"></i>
          <h3>CSP Bypass via JSONP</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Whitelisted</span>
        </div>
        <div class="bypass-content">
          <div class="bypass-step">
            <span class="step-num">1</span>
            <span class="step-text">CSP blocks all inline scripts</span>
          </div>
          <div class="bypass-step">
            <span class="step-num">2</span>
            <span class="step-text">But JSONP endpoint is whitelisted in script-src</span>
          </div>
          <div class="bypass-step vuln-step">
            <span class="step-num">3</span>
            <span class="step-text">Attacker injects code through JSONP callback</span>
          </div>
          <div class="bypass-step">
            <span class="step-num">4</span>
            <span class="step-text">CSP allows it because source is whitelisted</span>
          </div>
          <div class="exploit-code">
            <div class="code-label">Bypass Code:</div>
            <pre class="code-block"><code>&lt;!-- CSP blocks this: --&gt;
&lt;script&gt;alert(1)&lt;/script&gt;  ❌ BLOCKED

&lt;!-- But allows this: --&gt;
&lt;script src="<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>?callback=alert"&gt;&lt;/script&gt;  ✅ ALLOWED

&lt;!-- Result: alert(1)({...}); executes! --&gt;</code></pre>
          </div>
        </div>
      </div>

      <div class="jsonp-card analysis-card">
        <div class="card-header"><i class="fas fa-microscope"></i>
          <h3>Vulnerability Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">script-src:</span>
            <code class="analysis-code">*.darkhunter.local</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Too Broad</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Callback Validation:</span>
            <code class="analysis-code">// None on JSONP endpoint</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Nonce/Hash:</span>
            <code class="analysis-code">// Not used</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Fix:</span>
            <code class="analysis-code">strict-dynamic + nonce</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Required</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>CSP Bypass Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Alert Bypass</div>
            <code class="payload-code">?callback=alert</code>
            <span class="payload-target">Execute alert despite CSP</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Eval Chain</div>
            <code class="payload-code">?callback=eval</code>
            <span class="payload-target">Use eval (allowed by unsafe-eval)</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Function Constructor</div>
            <code class="payload-code">?callback=Function</code>
            <span class="payload-target">Create new Function objects</span>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>CSP: Strict (with JSONP whitelist)</span>
          <span>Callback: <?php echo htmlspecialchars($callback); ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The CSP policy allows scripts from *.darkhunter.local, which includes this JSONP endpoint.
        Try <code>?callback=alert</code> - CSP will allow it because the source is whitelisted!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The policy includes 'unsafe-eval', so eval and Function constructor are allowed. Try
        <code>?callback=eval</code> or <code>?callback=Function</code> to bypass CSP restrictions!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?callback=alert</code> or <code>?callback=eval</code> to trigger the flag. The
        JSONP endpoint is whitelisted in CSP, allowing arbitrary code execution that would normally be blocked!</div>
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