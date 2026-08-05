<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['LFI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['lfi_easy1_attempts'])) {
  $_SESSION['lfi_easy1_attempts'] = 0;
}
if (!isset($_SESSION['lfi_easy1_solved'])) {
  $_SESSION['lfi_easy1_solved'] = false;
}

// ─── Simulated Application: DarkHunter Document Viewer ───────────────────
$pages = [
  'home'    => ['title' => 'Home', 'content' => 'Welcome to DarkHunter Document Viewer. Browse secure documents.'],
  'about'   => ['title' => 'About', 'content' => 'DarkHunter is a cybersecurity training platform.'],
  'contact' => ['title' => 'Contact', 'content' => 'Contact security team at security@darkhunter.local'],
  'terms'   => ['title' => 'Terms of Service', 'content' => 'By using this service you agree to all security policies.'],
];

// ─── Vulnerable Logic: Direct file inclusion without validation ────────────
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// VULNERABLE: No path sanitization - directly includes user input
$page_file = 'pages/' . $page . '.php';

$file_content = null;
$file_error = null;

// Check if it's a known page first (for normal flow)
if (isset($pages[$page])) {
  $current_page = $pages[$page];
} else {
  // Try to include the file anyway (vulnerable behavior)
  $current_page = ['title' => 'Loading...', 'content' => ''];

  // For educational display, we show what the include WOULD do
  if (file_exists($page_file)) {
    $file_content = file_get_contents($page_file);
  } else {
    $file_error = "File not found: " . htmlspecialchars($page_file);
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['lfi_easy1_solved'];
$exploited = false;

// Detect LFI exploitation - looking for path traversal patterns
$lfi_indicators = [
  '../',
  '..\\',
  '..%2f',
  '..%5c',
  '%2e%2e%2f',
  '%252e%252e%252f',
  'etc/passwd',
  'windows/system32',
  'boot.ini',
  '.env',
  'config.php',
  'db.php',
  'score_manager.php',
  'flag',
  'secret',
  'password',
  'php://filter',
  'php://input',
  'data://',
  'file://',
  '/etc/',
  'C:\\',
  'D:\\',
  'proc/self',
  'var/log'
];

foreach ($lfi_indicators as $indicator) {
  if (stripos($page, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['lfi_easy1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['lfi_easy1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Local File Inclusion (LFI) vulnerability. You used path traversal sequences to access files outside the intended directory. This is a critical vulnerability that can lead to information disclosure and even Remote Code Execution!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
  $_SESSION['lfi_easy1_attempts']++;
}

$attempts = $_SESSION['lfi_easy1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document Viewer - LFI Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/LFI-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to LFI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-file-alt"></i> DarkHunter Document Viewer</h1>
      <p class="lab-description">Browse internal documents securely. This application loads pages dynamically based on
        the <code>page</code> parameter. <strong>No path validation applied!</strong> Can you access files outside the
        pages directory?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this LFI vulnerability. You can continue exploring, but no additional points will
            be awarded.</p>
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

    <!-- Document Viewer Interface -->
    <div class="viewer-card">
      <div class="viewer-toolbar">
        <div class="toolbar-brand">
          <i class="fas fa-folder-open"></i>
          <span>Document Viewer</span>
        </div>
        <div class="toolbar-actions">
          <button class="toolbar-btn" title="Print"><i class="fas fa-print"></i></button>
          <button class="toolbar-btn" title="Download"><i class="fas fa-download"></i></button>
          <button class="toolbar-btn" title="Share"><i class="fas fa-share-alt"></i></button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="viewer-nav">
        <?php foreach ($pages as $key => $p): ?>
          <a href="?page=<?php echo $key; ?>" class="nav-tab <?php echo $page === $key ? 'active' : ''; ?>">
            <i class="fas fa-file"></i>
            <span><?php echo $p['title']; ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Content Area -->
      <div class="viewer-content">
        <div class="content-header">
          <h2 class="content-title"><?php echo isset($pages[$page]) ? $pages[$page]['title'] : 'Custom Document'; ?>
          </h2>
          <span class="content-badge"><i class="fas fa-shield-alt"></i> Internal</span>
        </div>
        <div class="content-body">
          <?php if (isset($pages[$page])): ?>
            <p><?php echo $pages[$page]['content']; ?></p>
          <?php elseif ($file_error): ?>
            <div class="file-error">
              <i class="fas fa-exclamation-triangle"></i>
              <p><?php echo $file_error; ?></p>
              <p class="error-hint">The application tried to load:
                <code>pages/<?php echo htmlspecialchars($page); ?>.php</code>
              </p>
            </div>
          <?php else: ?>
            <div class="file-loaded">
              <i class="fas fa-check-circle"></i>
              <p>File loaded successfully! The application included:
                <code>pages/<?php echo htmlspecialchars($page); ?>.php</code>
              </p>
              <?php if ($file_content): ?>
                <pre class="file-preview"><code><?php echo htmlspecialchars(substr($file_content, 0, 2000)); ?></code></pre>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- File System Visualization -->
    <div class="filesystem-panel">
      <div class="filesystem-header">
        <i class="fas fa-sitemap"></i>
        <span>Target File System</span>
      </div>
      <div class="filesystem-body">
        <div class="fs-tree">
          <div class="fs-item root">
            <i class="fas fa-hdd"></i>
            <span>/var/www/html/DarkHunter/</span>
          </div>
          <div class="fs-children">
            <div class="fs-item">
              <i class="fas fa-folder"></i>
              <span>Config/</span>
              <span class="fs-file">db.php</span>
            </div>
            <div class="fs-item">
              <i class="fas fa-folder"></i>
              <span>includes/</span>
              <span class="fs-file">score_manager.php</span>
            </div>
            <div class="fs-item target">
              <i class="fas fa-folder-open"></i>
              <span>labs/LFI/</span>
            </div>
            <div class="fs-children">
              <div class="fs-item current">
                <i class="fas fa-folder-open"></i>
                <span>pages/</span>
                <span class="fs-hint">(current directory)</span>
              </div>
              <div class="fs-children">
                <div class="fs-item file">
                  <i class="fas fa-file-code"></i>
                  <span>home.php</span>
                </div>
                <div class="fs-item file">
                  <i class="fas fa-file-code"></i>
                  <span>about.php</span>
                </div>
                <div class="fs-item file">
                  <i class="fas fa-file-code"></i>
                  <span>contact.php</span>
                </div>
                <div class="fs-item file">
                  <i class="fas fa-file-code"></i>
                  <span>terms.php</span>
                </div>
              </div>
            </div>
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
        <code>GET /LFI-CASE1.php?page=<?php echo urlencode($page); ?></code>
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
          <pre>// VULNERABLE: Direct file inclusion
$page = $_GET['page'];
$page_file = 'pages/' . $page . '.php';
include($page_file);  // No validation!</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> User input is concatenated directly into a file path without
            sanitization. Path traversal sequences like <code>../</code> can escape the pages directory.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Look at the URL parameter <code>page</code>. The application builds a file path:
          <code>pages/{page}.php</code>. What if you use <code>../</code> to go up directories? Try
          <code>?page=../index</code> or <code>?page=../../Config/db</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The file system shows sensitive files in <code>Config/db.php</code> and
          <code>includes/score_manager.php</code>. Try <code>?page=../../Config/db</code> to read database credentials, or
          <code>?page=../../../../etc/passwd</code> for system files.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?page=../../../../etc/passwd</code> to read the Linux password file, or
          <code>?page=../../Config/db</code> to access application configuration. Any path traversal attempt will solve
          this challenge!
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
    <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const page = urlParams.get('page') || '';

      // Check for LFI exploitation indicators
      const lfiPatterns = [
        '../', '..\\', '..%2f', '..%5c', '%2e%2e%2f', '%252e%252e%252f',
        'etc/passwd', 'windows/system32', 'boot.ini', '.env', 'config.php',
        'db.php', 'score_manager.php', 'flag', 'secret', 'password',
        'php://filter', 'php://input', 'data://', 'file://',
        '/etc/', 'C:\\', 'D:\\', 'proc/self', 'var/log'
      ];

      const hasLFI = lfiPatterns.some(pattern =>
        page.toLowerCase().includes(pattern.toLowerCase())
      );

      if (hasLFI && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>