<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_medium2_attempts'])) {
  $_SESSION['idor_medium2_attempts'] = 0;
}
if (!isset($_SESSION['idor_medium2_solved'])) {
  $_SESSION['idor_medium2_solved'] = false;
}

// ─── Simulated File Storage ──────────────────────────────────────────────
$files = [
  'receipt_001.pdf' => ['owner' => 'alice', 'size' => '24KB', 'type' => 'PDF', 'content' => 'Receipt #001 - Coffee Shop - $5.50'],
  'receipt_002.pdf' => ['owner' => 'bob', 'size' => '31KB', 'type' => 'PDF', 'content' => 'Receipt #002 - Electronics Store - $249.99'],
  'receipt_003.pdf' => ['owner' => 'charlie', 'size' => '18KB', 'type' => 'PDF', 'content' => 'Receipt #003 - Gas Station - $45.00'],
  'transcript_admin.pdf' => ['owner' => 'admin', 'size' => '156KB', 'type' => 'PDF', 'content' => 'CONFIDENTIAL: Admin Access Log. Flag: DH{idor_file_access_pwned}'],
  'invoice_004.pdf' => ['owner' => 'dave', 'size' => '42KB', 'type' => 'PDF', 'content' => 'Invoice #004 - Web Hosting - $120.00'],
];

// ─── Vulnerable File Access Logic ──────────────────────────────────────────
$filename = isset($_GET['file']) ? basename($_GET['file']) : 'receipt_001.pdf';
$file = isset($files[$filename]) ? $files[$filename] : null;

if (!$file) {
  $filename = 'receipt_001.pdf';
  $file = $files[$filename];
}

$is_admin_file = ($filename === 'transcript_admin.pdf');

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_medium2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_medium2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['idor_medium2_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited a file access IDOR vulnerability. You accessed unauthorized files by manipulating predictable filenames in the URL!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
  $_SESSION['idor_medium2_attempts']++;
}

$attempts = $_SESSION['idor_medium2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Viewer - IDOR Medium 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-file-pdf"></i> Document Viewer</h1>
      <p class="lab-description">View your uploaded documents. This challenge demonstrates file access IDOR with
        predictable filenames. Try to access other users documents by guessing filename patterns. <strong>No file
          ownership verification!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this file access IDOR vulnerability. You can continue exploring, but no additional
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

    <!-- File Viewer -->
    <div class="file-viewer <?php echo $is_admin_file ? 'admin-file' : ''; ?>">
      <div class="file-toolbar">
        <div class="file-info">
          <i class="fas fa-file-<?php echo strtolower($file['type']); ?>"></i>
          <span class="file-name"><?php echo htmlspecialchars($filename); ?></span>
          <span class="file-size"><?php echo $file['size']; ?></span>
        </div>
        <div class="file-actions">
          <button class="action-btn"><i class="fas fa-download"></i> Download</button>
          <button class="action-btn"><i class="fas fa-print"></i> Print</button>
        </div>
      </div>
      <div class="file-preview">
        <div class="pdf-page">
          <div class="pdf-header">
            <span class="pdf-logo">DarkHunter</span>
            <span class="pdf-date"><?php echo date('Y-m-d'); ?></span>
          </div>
          <div class="pdf-content">
            <h2><?php echo htmlspecialchars($file['content']); ?></h2>
            <p class="pdf-owner">Owner: <?php echo htmlspecialchars($file['owner']); ?></p>
            <?php if ($is_admin_file): ?>
            <div class="classified-stamp">CLASSIFIED</div>
            <?php endif; ?>
          </div>
          <div class="pdf-footer">
            <span>Page 1 of 1</span>
            <span>Confidential</span>
          </div>
        </div>
      </div>
    </div>

    <!-- File List -->
    <div class="file-list">
      <h3><i class="fas fa-folder-open"></i> My Documents</h3>
      <?php foreach ($files as $fname => $f): ?>
      <a href="?file=<?php echo urlencode($fname); ?>"
        class="file-item <?php echo $fname === $filename ? 'active' : ''; ?> <?php echo $fname === 'transcript_admin.pdf' ? 'hidden-file' : ''; ?>">
        <i class="fas fa-file-<?php echo strtolower($f['type']); ?>"></i>
        <span class="file-item-name"><?php echo htmlspecialchars($fname); ?></span>
        <span class="file-item-size"><?php echo $f['size']; ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- URL Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /IDOR-CASE4.php?file=<?php echo urlencode($filename); ?></code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Look at the URL parameter <code>file</code>. The filenames follow a pattern:
        <code>receipt_XXX.pdf</code>. What other files might exist on the server with different naming patterns?
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try common filename patterns like <code>transcript</code>, <code>admin</code>,
        <code>secret</code>, or <code>flag</code> combined with file extensions. The server does not check file
        ownership.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Access the admin transcript by changing the URL to <code>?file=transcript_admin.pdf</code>.
        This file is not listed in the UI but exists on the server!</div>
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
    const urlParams = new URLSearchParams(window.location.search);
    const file = urlParams.get('file');

    if (file === 'transcript_admin.pdf' && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>