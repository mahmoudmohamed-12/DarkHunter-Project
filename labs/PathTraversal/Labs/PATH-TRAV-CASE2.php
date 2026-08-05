<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_easy2_attempts'])) {
  $_SESSION['pt_easy2_attempts'] = 0;
}
if (!isset($_SESSION['pt_easy2_solved'])) {
  $_SESSION['pt_easy2_solved'] = false;
}

// ─── Simulated Application: DarkHunter System Monitor ──────────────────
$log_files = [
  'system'  => ['name' => 'System Log', 'path' => '/var/log/syslog'],
  'apache'  => ['name' => 'Apache Access', 'path' => '/var/log/apache2/access.log'],
  'auth'    => ['name' => 'Auth Log', 'path' => '/var/log/auth.log'],
  'darkhunter' => ['name' => 'DarkHunter App', 'path' => '/var/log/darkhunter/app.log'],
];

// ─── Vulnerable Logic: Only checks for ../ but misses absolute paths ───
$log = isset($_GET['log']) ? $_GET['log'] : 'system';

// Weak validation - only blocks relative traversal
$is_blocked = false;
$block_reason = '';

if (strpos($log, '../') !== false || strpos($log, '..\\') !== false) {
  $is_blocked = true;
  $block_reason = "Relative path traversal is not allowed!";
}

// Build path
if (isset($log_files[$log])) {
  $log_path = $log_files[$log]['path'];
  $log_name = $log_files[$log]['name'];
} else {
  $log_path = $log;
  $log_name = 'Custom Log';
}

// ─── Simulated Log Content ───────────────────────────────────────────────
$simulated_content = [
  'system' => [
    'May 20 10:00:01 darkhunter kernel: [    0.000000] Linux version 5.15.0',
    'May 20 10:00:02 darkhunter systemd[1]: Starting system management daemon...',
    'May 20 10:00:05 darkhunter cron[1234]: (root) CMD (   cd / && run-parts --report /etc/cron.hourly)',
    'May 20 10:15:30 darkhunter kernel: [  902.341] usb 1-1: new high-speed USB device number 2',
  ],
  'apache' => [
    '192.168.1.100 - - [20/May/2026:10:15:30 +0000] "GET / HTTP/1.1" 200 1234',
    '192.168.1.101 - - [20/May/2026:10:16:45 +0000] "GET /login.php HTTP/1.1" 200 567',
    '10.0.0.50 - - [20/May/2026:10:20:15 +0000] "GET /admin HTTP/1.1" 403 234',
    '192.168.1.200 - - [20/May/2026:10:25:00 +0000] "POST /api/data HTTP/1.1" 500 89',
  ],
  'auth' => [
    'May 20 10:15:30 darkhunter sshd[1234]: Accepted password for admin from 192.168.1.100',
    'May 20 10:16:45 darkhunter sshd[1235]: Failed password for root from 10.0.0.99',
    'May 20 10:17:12 darkhunter sudo: admin : TTY=pts/0 ; USER=root ; COMMAND=/bin/bash',
    'May 20 10:18:00 darkhunter sshd[1236]: Connection closed by 192.168.1.103',
  ],
  'darkhunter' => [
    '[2026-05-20 10:00:00] INFO: DarkHunter application started',
    '[2026-05-20 10:15:30] WARN: Failed login attempt for user "admin"',
    '[2026-05-20 10:16:45] ERROR: Database connection timeout',
    '[2026-05-20 10:20:15] INFO: User "alice" logged in successfully',
  ],
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_easy2_solved'];
$exploited = false;

// Detect absolute path exploitation
$absolute_indicators = [
  '/etc/passwd',
  '/etc/shadow',
  '/etc/hosts',
  '/etc/hostname',
  '/proc/self/',
  '/proc/version',
  '/proc/cmdline',
  '/proc/environ',
  '/var/www/',
  '/var/log/',
  '/var/spool/',
  '/home/',
  '/root/',
  '/opt/',
  '/usr/',
  '/tmp/',
  '/dev/',
  '/sys/',
  'C:\\',
  'D:\\',
  'E:\\',
  'C:/',
  'D:/',
  'E:/',
  '\\windows\\',
  '\\winnt\\',
  '/windows/',
  '/winnt/',
  'boot.ini',
  'system.ini',
  'win.ini',
  'autoexec.bat',
  'config.sys',
  'pagefile.sys',
  'hiberfil.sys',
];

foreach ($absolute_indicators as $indicator) {
  if (stripos($log, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check for absolute path starting with / or \
if (preg_match('/^[\\/]/', $log) || preg_match('/^[A-Za-z]:[\\/]/', $log)) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_easy2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_easy2_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited a Path Traversal vulnerability using absolute paths. The application only blocked relative traversal (../) but completely missed absolute paths like /etc/passwd or C:\\Windows\\win.ini. This is a common mistake in path validation!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['log'])) {
  $_SESSION['pt_easy2_attempts']++;
}

$attempts = $_SESSION['pt_easy2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Monitor - Path Traversal Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-heartbeat"></i> DarkHunter System Monitor</h1>
      <p class="lab-description">Monitor system logs in real-time. The application validates against <code>../</code>
        sequences but <strong>allows absolute paths</strong>. Can you read system files using absolute path references?
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this absolute path traversal. You can continue exploring, but no additional points
          will be awarded.</p>
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

    <!-- Monitor Interface -->
    <div class="monitor-card">
      <div class="monitor-header">
        <div class="monitor-brand">
          <i class="fas fa-terminal"></i>
          <span>System Monitor</span>
        </div>
        <div class="monitor-status">
          <span class="status-dot live"></span>
          <span>Live</span>
        </div>
      </div>

      <div class="monitor-body">
        <!-- Security Banner -->
        <div class="security-banner">
          <div class="banner-icon"><i class="fas fa-shield-alt"></i></div>
          <div class="banner-content">
            <span class="banner-title">Security Filter Active</span>
            <span class="banner-desc">Relative path traversal (../) is blocked. Absolute paths are allowed for system
              log access.</span>
          </div>
        </div>

        <!-- Filter Status -->
        <div class="filter-status-panel">
          <div class="filter-row">
            <span class="filter-label">Input:</span>
            <code class="filter-input"><?php echo htmlspecialchars($log); ?></code>
          </div>
          <div class="filter-row">
            <span class="filter-label">Check for ../:</span>
            <span class="filter-result <?php echo $is_blocked ? 'blocked' : 'passed'; ?>">
              <i class="fas fa-<?php echo $is_blocked ? 'times-circle' : 'check-circle'; ?>"></i>
              <?php echo $is_blocked ? 'BLOCKED' : 'PASSED'; ?>
            </span>
          </div>
          <div class="filter-row">
            <span class="filter-label">Resolved Path:</span>
            <code class="filter-path"><?php echo htmlspecialchars($log_path); ?></code>
          </div>
        </div>

        <?php if ($is_blocked): ?>
        <div class="access-denied">
          <i class="fas fa-ban fa-3x"></i>
          <h3>Access Denied</h3>
          <p><?php echo htmlspecialchars($block_reason); ?></p>
        </div>
        <?php else: ?>
        <!-- Log Selector -->
        <div class="log-selector">
          <span class="selector-label">Select Log Source:</span>
          <div class="selector-tabs">
            <?php foreach ($log_files as $key => $info): ?>
            <a href="?log=<?php echo $key; ?>" class="selector-tab <?php echo $log === $key ? 'active' : ''; ?>">
              <i class="fas fa-file-alt"></i>
              <span><?php echo $info['name']; ?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Log Content -->
        <div class="log-display">
          <div class="log-header">
            <div class="log-title">
              <i class="fas fa-stream"></i>
              <span><?php echo $log_name; ?></span>
            </div>
            <code class="log-path-display"><?php echo htmlspecialchars($log_path); ?></code>
          </div>
          <div class="log-lines">
            <?php if (isset($simulated_content[$log])): ?>
            <?php foreach ($simulated_content[$log] as $line): ?>
            <div class="log-line">
              <span class="log-timestamp">[<?php echo date('H:i:s'); ?>]</span>
              <span class="log-text"><?php echo htmlspecialchars($line); ?></span>
            </div>
            <?php endforeach; ?>
            <?php elseif ($exploited): ?>
            <div class="log-line exploit">
              <span class="log-timestamp">[EXPLOIT]</span>
              <span class="log-text">Absolute path traversal detected! Attempting to read:
                <code><?php echo htmlspecialchars($log_path); ?></code></span>
            </div>
            <?php else: ?>
            <div class="log-line error">
              <span class="log-timestamp">[ERROR]</span>
              <span class="log-text">Log file not found or not accessible:
                <code><?php echo htmlspecialchars($log_path); ?></code></span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- OS Detection Panel -->
    <div class="os-panel">
      <div class="os-header">
        <i class="fas fa-server"></i>
        <span>Target System Information</span>
      </div>
      <div class="os-body">
        <div class="os-item">
          <i class="fab fa-linux"></i>
          <div class="os-info">
            <span class="os-name">Linux Paths</span>
            <code class="os-path">/etc/passwd, /etc/shadow, /proc/self/environ, /var/log/syslog</code>
          </div>
        </div>
        <div class="os-item">
          <i class="fab fa-windows"></i>
          <div class="os-info">
            <span class="os-name">Windows Paths</span>
            <code class="os-path">C:\Windows\win.ini, C:\Windows\System32\drivers\etc\hosts</code>
          </div>
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
        <code>GET /PATH-TRAV-CASE2.php?log=<?php echo urlencode($log); ?></code>
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
          <pre>// VULNERABLE: Only blocks relative traversal
$log = $_GET['log'];

if (strpos($log, '../') !== false) {
    die("Relative path traversal blocked!");
}

// But absolute paths are allowed!
$log_path = $log;
$content = file_get_contents($log_path);</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> The filter only checks for <code>../</code> but completely misses
            absolute paths like <code>/etc/passwd</code> or <code>C:\\Windows\\win.ini</code>. A proper fix would
            validate against an allowlist of permitted paths.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The filter blocks <code>../</code> but allows absolute paths. Try using a full path
        directly: <code>?log=/etc/passwd</code> (Linux) or <code>?log=C:\\Windows\\win.ini</code> (Windows).</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Other interesting absolute paths: <code>/proc/self/environ</code> (process environment),
        <code>/etc/hosts</code> (network config), <code>/var/www/html/DarkHunter/Config/db.php</code> (app config).
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Simply use <code>?log=/etc/passwd</code> to read the Linux password file. No traversal
        sequences needed - the application accepts absolute paths directly!</div>
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
    <input type="hidden" name="log" value="<?php echo htmlspecialchars($log); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const log = urlParams.get('log') || '';

    const absolutePatterns = [
      '/etc/passwd', '/etc/shadow', '/etc/hosts', '/etc/hostname',
      '/proc/self/', '/proc/version', '/proc/cmdline', '/proc/environ',
      '/var/www/', '/var/log/', '/var/spool/',
      '/home/', '/root/', '/opt/', '/usr/',
      '/tmp/', '/dev/', '/sys/',
      'C:\\', 'D:\\', 'E:\\',
      'C:/', 'D:/', 'E:/',
      '\\windows\\', '\\winnt\\',
      '/windows/', '/winnt/',
      'boot.ini', 'system.ini', 'win.ini',
      'autoexec.bat', 'config.sys',
      'pagefile.sys', 'hiberfil.sys'
    ];

    const hasAbsolute = absolutePatterns.some(pattern =>
      log.toLowerCase().includes(pattern.toLowerCase())
    );

    const isAbsolutePath = /^[\\/]/.test(log) || /^[A-Za-z]:[\\/]/.test(log);

    if ((hasAbsolute || isAbsolutePath) && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>