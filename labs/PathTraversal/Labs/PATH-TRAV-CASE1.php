<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_easy1_attempts'])) {
  $_SESSION['pt_easy1_attempts'] = 0;
}
if (!isset($_SESSION['pt_easy1_solved'])) {
  $_SESSION['pt_easy1_solved'] = false;
}

// ─── Simulated Application: DarkHunter File Browser ──────────────────────
$allowed_files = [
  'readme.txt'    => ['name' => 'README', 'content' => "Welcome to DarkHunter File Browser v2.1\n\nThis application allows browsing of public documents.\nAll files are stored in the /documents directory.\n\nFor support contact: admin@darkhunter.local"],
  'changelog.txt' => ['name' => 'Changelog', 'content' => "Version 2.1 - Security Update\n- Added file type validation\n- Improved error handling\n- Fixed minor UI bugs\n\nVersion 2.0 - Initial Release\n- File browsing functionality\n- Download support\n- Search capability"],
  'license.txt'   => ['name' => 'License', 'content' => "DarkHunter File Browser - MIT License\n\nCopyright (c) 2026 DarkHunter Inc.\n\nPermission is hereby granted, free of charge, to any person obtaining a copy\nof this software and associated documentation files."],
  'config.txt'    => ['name' => 'Configuration', 'content' => "# DarkHunter File Browser Config\n\nDOCUMENT_ROOT=/var/www/html/DarkHunter\nUPLOAD_DIR=/var/www/html/DarkHunter/uploads\nMAX_FILE_SIZE=10485760\nALLOWED_EXTENSIONS=txt,pdf,doc,docx\n\n# Database Connection (DO NOT EXPOSE)\nDB_HOST=localhost\nDB_NAME=darkhunter_db\nDB_USER=dh_admin\nDB_PASS=DH_S3cur3_P@ssw0rd_2026!"],
];

// ─── Vulnerable Logic: Unsanitized filename parameter ────────────────────
$filename = isset($_GET['file']) ? $_GET['file'] : 'readme.txt';

// VULNERABLE: Direct path construction without validation
$file_path = 'documents/' . $filename;

$file_content = null;
$file_error = null;
$file_found = false;

// Check if it's an allowed file first
if (isset($allowed_files[$filename])) {
  $file_content = $allowed_files[$filename]['content'];
  $file_found = true;
} else {
  // Simulate trying to read the file (vulnerable behavior)
  $file_error = "Attempting to access: documents/" . htmlspecialchars($filename);
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_easy1_solved'];
$exploited = false;

// Detect path traversal exploitation
$traversal_indicators = [
  '../',
  '..\\',
  '..%2f',
  '..%5c',
  '%2e%2e%2f',
  '%252e%252e%252f',
  '%2e%2e/',
  '%2e%2e\\',
  '..%252f',
  'etc/passwd',
  'windows/win.ini',
  'windows/system32',
  'boot.ini',
  'autoexec.bat',
  'config.sys',
  '.env',
  '.htaccess',
  '.htpasswd',
  'proc/self',
  'proc/version',
  'proc/cmdline',
  'var/log',
  'var/www',
  'var/spool',
  'home/',
  'root/',
  'opt/',
];

foreach ($traversal_indicators as $indicator) {
  if (stripos($filename, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Also check for multiple dots patterns
if (preg_match('/\.\.+/', $filename)) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_easy1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_easy1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Path Traversal vulnerability using basic dot-dot-slash sequences. You escaped the documents directory and attempted to access files outside the intended scope. This is the foundation of all directory traversal attacks!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
  $_SESSION['pt_easy1_attempts']++;
}

$attempts = $_SESSION['pt_easy1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Browser - Path Traversal Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-folder-open"></i> DarkHunter File Browser</h1>
      <p class="lab-description">Browse public documents securely. The application reads files from the
        <code>documents/</code> directory based on the <code>file</code> parameter. <strong>No path validation
          applied!</strong> Can you escape the documents folder?
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Path Traversal vulnerability. You can continue exploring, but no additional
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

    <!-- File Browser Interface -->
    <div class="browser-card">
      <div class="browser-toolbar">
        <div class="toolbar-path">
          <i class="fas fa-home"></i>
          <span class="path-separator">/</span>
          <span>documents</span>
          <span class="path-separator">/</span>
          <span class="path-current"><?php echo htmlspecialchars($filename); ?></span>
        </div>
        <div class="toolbar-actions">
          <button class="toolbar-btn"><i class="fas fa-arrow-up"></i></button>
          <button class="toolbar-btn"><i class="fas fa-sync"></i></button>
        </div>
      </div>

      <!-- Sidebar + Content -->
      <div class="browser-layout">
        <!-- File List Sidebar -->
        <div class="file-sidebar">
          <div class="sidebar-header">
            <i class="fas fa-list"></i>
            <span>Files</span>
          </div>
          <div class="sidebar-files">
            <?php foreach ($allowed_files as $key => $info): ?>
            <a href="?file=<?php echo $key; ?>" class="sidebar-file <?php echo $filename === $key ? 'active' : ''; ?>">
              <i class="fas fa-file-alt"></i>
              <span><?php echo $info['name']; ?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Content Viewer -->
        <div class="content-viewer">
          <div class="viewer-header">
            <div class="viewer-title">
              <i class="fas fa-file-alt"></i>
              <span><?php echo isset($allowed_files[$filename]) ? $allowed_files[$filename]['name'] : 'Unknown File'; ?></span>
            </div>
            <div class="viewer-meta">
              <span
                class="meta-size"><?php echo isset($allowed_files[$filename]) ? strlen($allowed_files[$filename]['content']) . ' bytes' : '??? bytes'; ?></span>
              <span class="meta-type">TXT</span>
            </div>
          </div>
          <div class="viewer-body">
            <?php if ($file_found): ?>
            <pre class="file-content"><?php echo htmlspecialchars($file_content); ?></pre>
            <?php elseif ($exploited): ?>
            <div class="exploit-detected">
              <i class="fas fa-bug"></i>
              <h3>Path Traversal Detected!</h3>
              <p>The application attempted to access:</p>
              <code class="exploit-path">documents/<?php echo htmlspecialchars($filename); ?></code>
              <p class="exploit-hint">This path escapes the intended directory!</p>
            </div>
            <?php else: ?>
            <div class="file-error">
              <i class="fas fa-exclamation-circle"></i>
              <h3>File Not Found</h3>
              <p><?php echo $file_error; ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Directory Structure Visualization -->
    <div class="directory-panel">
      <div class="directory-header">
        <i class="fas fa-sitemap"></i>
        <span>Server Directory Structure</span>
      </div>
      <div class="directory-body">
        <div class="dir-tree">
          <div class="dir-item root">
            <i class="fas fa-server"></i>
            <span>/var/www/html/DarkHunter/</span>
          </div>
          <div class="dir-children">
            <div class="dir-item">
              <i class="fas fa-folder"></i>
              <span>Config/</span>
              <span class="dir-file">db.php</span>
            </div>
            <div class="dir-item">
              <i class="fas fa-folder"></i>
              <span>includes/</span>
              <span class="dir-file">score_manager.php</span>
            </div>
            <div class="dir-item target">
              <i class="fas fa-folder-open"></i>
              <span>labs/PathTraversal/</span>
            </div>
            <div class="dir-children">
              <div class="dir-item current">
                <i class="fas fa-folder-open"></i>
                <span>documents/</span>
                <span class="dir-hint">(restricted scope)</span>
              </div>
              <div class="dir-children">
                <div class="dir-item file">
                  <i class="fas fa-file-alt"></i>
                  <span>readme.txt</span>
                </div>
                <div class="dir-item file">
                  <i class="fas fa-file-alt"></i>
                  <span>changelog.txt</span>
                </div>
                <div class="dir-item file">
                  <i class="fas fa-file-alt"></i>
                  <span>license.txt</span>
                </div>
                <div class="dir-item file">
                  <i class="fas fa-file-alt"></i>
                  <span>config.txt</span>
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
        <code>GET /PATH-TRAV-CASE1.php?file=<?php echo urlencode($filename); ?></code>
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
          <pre>// VULNERABLE: No path validation
$filename = $_GET['file'];
$file_path = 'documents/' . $filename;

// Directly reads file without checking path
$content = file_get_contents($file_path);</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> User input is concatenated directly into a file path. Using
            <code>../</code> sequences allows escaping the documents directory to access arbitrary files.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Look at the URL parameter <code>file</code>. The application builds a path:
        <code>documents/{file}</code>. Try using <code>../</code> to go up one directory. For example:
        <code>?file=../index.php</code> or <code>?file=../../Config/db.php</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The directory structure shows sensitive files in <code>Config/db.php</code>. Try escaping
        multiple directories: <code>?file=../../../etc/passwd</code> (Linux) or
        <code>?file=..\\..\\windows\\win.ini</code> (Windows).
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?file=../../../etc/passwd</code> to read the Linux password file, or
        <code>?file=../../Config/db.php</code> to access the database configuration. Any path traversal sequence will
        solve this challenge!
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
    <input type="hidden" name="file" value="<?php echo htmlspecialchars($filename); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const file = urlParams.get('file') || '';

    const traversalPatterns = [
      '../', '..\\', '..%2f', '..%5c', '%2e%2e%2f', '%252e%252e%252f',
      '%2e%2e/', '%2e%2e\\', '..%252f',
      'etc/passwd', 'windows/win.ini', 'windows/system32',
      'boot.ini', 'autoexec.bat', 'config.sys',
      '.env', '.htaccess', '.htpasswd',
      'proc/self', 'proc/version', 'proc/cmdline',
      'var/log', 'var/www', 'var/spool',
      'home/', 'root/', 'opt/'
    ];

    const hasTraversal = traversalPatterns.some(pattern =>
      file.toLowerCase().includes(pattern.toLowerCase())
    );

    const hasDots = /\.\.+/.test(file);

    if ((hasTraversal || hasDots) && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>