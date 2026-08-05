<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case8_attempts'])) {
  $_SESSION['jsonp_case8_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case8_solved'])) {
  $_SESSION['jsonp_case8_solved'] = false;
}

// VULNERABLE: Complex chain - JSONP → Prototype Pollution → Gadget Chain → RCE
$callback = $_GET['callback'] ?? 'callback';
$pollute = $_GET['pollute'] ?? '';
$gadget = $_GET['gadget'] ?? '';

// Simulated vulnerable library (like lodash, jQuery, etc.)
$library_version = 'lodash@4.17.15';
$vulnerable_functions = ['merge', 'defaultsDeep', 'set', 'setWith'];

$data = [
  'library' => $library_version,
  'functions' => $vulnerable_functions,
  'status' => 'vulnerable',
  'pollution_detected' => false,
  'gadget_triggered' => false,
  'rce_achieved' => false
];

// Check for prototype pollution
if (!empty($pollute)) {
  $data['pollution_detected'] = true;
  if (
    stripos($pollute, '__proto__') !== false ||
    stripos($pollute, 'constructor') !== false ||
    stripos($pollute, 'prototype') !== false
  ) {
    $data['pollution_detected'] = true;
  }
}

// Check for gadget chain
if (!empty($gadget)) {
  if (
    stripos($gadget, 'shell') !== false ||
    stripos($gadget, 'exec') !== false ||
    stripos($gadget, 'eval') !== false ||
    stripos($gadget, 'Function') !== false ||
    stripos($gadget, 'require') !== false ||
    stripos($gadget, 'child_process') !== false
  ) {
    $data['gadget_triggered'] = true;
    $data['rce_achieved'] = true;
  }
}

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'Function') !== false ||
    stripos($cb, 'constructor') !== false ||
    stripos($cb, '__proto__') !== false
  ) {
    $flag_triggered = true;
  }
}

if ($data['pollution_detected'] && $data['gadget_triggered']) {
  $flag_triggered = true;
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case8_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case8_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case8_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully chained JSONP callback injection to prototype pollution, then to a gadget chain, achieving effective remote code execution. This is one of the most advanced JSONP attack vectors possible!";
}

if (isset($_GET['callback']) || isset($_GET['pollute']) || isset($_GET['gadget'])) {
  $_SESSION['jsonp_case8_attempts']++;
}

$attempts = $_SESSION['jsonp_case8_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Advanced Chain - JSONP Case 8 (JSONP to RCE Chain)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-link"></i> Advanced Exploit Chain</h1>
      <p class="lab-description">Test our vulnerable library integration. This hard challenge chains <strong>JSONP →
          Prototype Pollution → Gadget Chain → RCE</strong>. Build the complete attack chain!</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this JSONP to RCE Chain vulnerability.</p>
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
      <div class="api-indicator"><i class="fas fa-code-branch"></i><span>Chain:
          <strong><?php echo $data['rce_achieved'] ? 'RCE ACHIEVED' : 'Stage 1'; ?></strong></span></div>
      <div class="stage-indicator">
        <span class="stage <?php echo $flag_triggered ? 'active' : ''; ?>">1.JSONP</span>
        <i class="fas fa-arrow-right"></i>
        <span class="stage <?php echo $data['pollution_detected'] ? 'active' : ''; ?>">2.Pollute</span>
        <i class="fas fa-arrow-right"></i>
        <span class="stage <?php echo $data['gadget_triggered'] ? 'active' : ''; ?>">3.Gadget</span>
        <i class="fas fa-arrow-right"></i>
        <span class="stage <?php echo $data['rce_achieved'] ? 'active danger' : ''; ?>">4.RCE</span>
      </div>
      <?php if ($flag_triggered): ?>
      <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>CHAIN COMPLETE!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card chain-card">
        <div class="card-header">
          <i class="fas fa-link"></i>
          <h3>Attack Chain</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Multi-Stage</span>
        </div>
        <div class="chain-content">
          <div class="chain-link">
            <span class="link-num">1</span>
            <div class="link-details">
              <span class="link-title">JSONP Callback Injection</span>
              <code class="link-code">?callback=__proto__</code>
              <span class="link-desc">Inject prototype reference via callback</span>
            </div>
          </div>
          <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="chain-link">
            <span class="link-num">2</span>
            <div class="link-details">
              <span class="link-title">Prototype Pollution</span>
              <code class="link-code">?pollute=__proto__.shell=true</code>
              <span class="link-desc">Pollute Object.prototype with malicious properties</span>
            </div>
          </div>
          <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="chain-link">
            <span class="link-num">3</span>
            <div class="link-details">
              <span class="link-title">Gadget Chain Trigger</span>
              <code class="link-code">?gadget=shell.exec("id")</code>
              <span class="link-desc">Trigger vulnerable library function</span>
            </div>
          </div>
          <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="chain-link danger-link">
            <span class="link-num">4</span>
            <div class="link-details">
              <span class="link-title">Remote Code Execution</span>
              <code class="link-code">RCE ACHIEVED!</code>
              <span class="link-desc">Arbitrary code execution on server</span>
            </div>
          </div>
        </div>
      </div>

      <div class="jsonp-card library-card">
        <div class="card-header">
          <i class="fas fa-book"></i>
          <h3>Vulnerable Library</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> CVE-2019-10744</span>
        </div>
        <div class="library-content">
          <div class="lib-info">
            <span class="lib-name"><?php echo $library_version; ?></span>
            <span class="lib-cve">CVE-2019-10744</span>
          </div>
          <div class="lib-functions">
            <span class="functions-label">Vulnerable Functions:</span>
            <div class="functions-list">
              <?php foreach ($vulnerable_functions as $func): ?>
              <code class="function-tag"><?php echo $func; ?></code>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="lib-status">
            <div class="status-row">
              <span>Prototype Pollution:</span>
              <span
                class="status-badge <?php echo $data['pollution_detected'] ? 'danger' : 'safe'; ?>"><?php echo $data['pollution_detected'] ? 'DETECTED' : 'Clean'; ?></span>
            </div>
            <div class="status-row">
              <span>Gadget Chain:</span>
              <span
                class="status-badge <?php echo $data['gadget_triggered'] ? 'danger' : 'safe'; ?>"><?php echo $data['gadget_triggered'] ? 'TRIGGERED' : 'Clean'; ?></span>
            </div>
            <div class="status-row">
              <span>RCE Status:</span>
              <span
                class="status-badge <?php echo $data['rce_achieved'] ? 'danger' : 'safe'; ?>"><?php echo $data['rce_achieved'] ? 'ACHIEVED' : 'Not Yet'; ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="jsonp-card analysis-card">
        <div class="card-header"><i class="fas fa-microscope"></i>
          <h3>Chain Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">JSONP Vector:</span>
            <code class="analysis-code">// Callback = __proto__</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Dangerous</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Prototype Pollution:</span>
            <code class="analysis-code">// Object.prototype polluted</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Possible</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Gadget Chain:</span>
            <code class="analysis-code">// shell.exec available</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Exploitable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">RCE Impact:</span>
            <code class="analysis-code">// Full server compromise</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Critical</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>Chain Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Stage 1: JSONP</div>
            <code class="payload-code">?callback=constructor</code>
            <span class="payload-target">Access Function constructor</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Stage 2: Pollute</div>
            <code class="payload-code">?pollute=__proto__.shell=true</code>
            <span class="payload-target">Pollute prototype with shell property</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Stage 3: Gadget</div>
            <code class="payload-code">?gadget=shell.exec("cat /etc/passwd")</code>
            <span class="payload-target">Trigger shell execution gadget</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Full Chain</div>
            <code class="payload-code">?callback=constructor&pollute=__proto__&gadget=shell.exec</code>
            <span class="payload-target">Complete chain in one request</span>
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
          <span>Pollute: <?php echo $pollute ? htmlspecialchars($pollute) : 'NONE'; ?></span>
          <span>Gadget: <?php echo $gadget ? htmlspecialchars($gadget) : 'NONE'; ?></span>
          <span>RCE: <?php echo $data['rce_achieved'] ? 'YES' : 'NO'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This lab requires a multi-stage attack. Start with <code>?callback=constructor</code> to
        access the Function constructor. Then use <code>?pollute=__proto__</code> to pollute the prototype!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">After polluting the prototype, trigger the gadget chain with
        <code>?gadget=shell.exec</code> or <code>?gadget=eval</code>. The vulnerable lodash functions will execute your
        payload!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use the full chain: <code>?callback=constructor&pollute=__proto__&gadget=shell.exec</code>.
        This combines all stages: JSONP injection → prototype pollution → gadget chain → RCE!</div>
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