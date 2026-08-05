<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_hard1_attempts'])) {
  $_SESSION['pt_hard1_attempts'] = 0;
}
if (!isset($_SESSION['pt_hard1_solved'])) {
  $_SESSION['pt_hard1_solved'] = false;
}
if (!isset($_SESSION['pt_hard1_stage'])) {
  $_SESSION['pt_hard1_stage'] = 1;
}

// ─── Simulated Application: DarkHunter Archive Manager ──────────────────
$uploaded_archives = [
  'backup_2026.zip' => ['size' => '1.2 MB', 'files' => 15, 'date' => '2026-05-15'],
  'documents.zip' => ['size' => '450 KB', 'files' => 8, 'date' => '2026-05-18'],
  'logs_export.zip' => ['size' => '890 KB', 'files' => 30, 'date' => '2026-05-19'],
];

// ─── Vulnerable Logic: Extracts ZIP without validating entry paths ──────
$archive = isset($_GET['archive']) ? $_GET['archive'] : 'documents.zip';
$extract = isset($_GET['extract']) ? true : false;

// Weak validation - only checks file extension
$is_valid = (substr($archive, -4) === '.zip');

// ─── Stage Logic for Multi-Step Attack ─────────────────────────────────
$current_stage = $_SESSION['pt_hard1_stage'];
$stage_messages = [
  1 => "Stage 1: Understand how ZIP extraction works and identify the vulnerability.",
  2 => "Stage 2: Craft a malicious ZIP with directory traversal in entry names.",
  3 => "Stage 3: Extract the archive and overwrite arbitrary files on the server.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_hard1_solved'];
$exploited = false;

// Detect ZipSlip exploitation
$zipslip_indicators = [
  '../',
  '..\\',
  '..%2f',
  '..%5c',
  '..%252f',
  '..%255c',
  '%2e%2e%2f',
  '%252e%252e%252f',
  '....//',
  '....\\',
  // Common ZipSlip payload patterns
  '../../',
  '..\\..\\',
  '../../../',
  '..\\..\\..\\',
  '\\x2e\\x2e\\x2f',
  // File overwrite targets
  'etc/passwd',
  'windows/system32',
  'var/www',
  'home/',
  '.htaccess',
  'web.config',
];

foreach ($zipslip_indicators as $indicator) {
  if (stripos($archive, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check for traversal in any context
if (preg_match('/\.\.+/', $archive)) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_hard1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've exploited a ZipSlip vulnerability to achieve arbitrary file overwrite via path traversal. By crafting a ZIP archive with directory traversal sequences in entry names, you escaped the extraction directory and overwrote critical system files. This is a dangerous vulnerability affecting thousands of applications!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['archive']) || isset($_GET['extract']))) {
  $_SESSION['pt_hard1_attempts']++;
}

$attempts = $_SESSION['pt_hard1_attempts'];

// Update stage
if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['pt_hard1_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['pt_hard1_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Archive Manager - Path Traversal Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-archive"></i> DarkHunter Archive Manager</h1>
      <p class="lab-description">An archive extraction service that processes ZIP files. The application extracts
        entries without validating their paths. <strong>Can you craft a malicious ZIP</strong> that escapes the
        extraction directory and overwrites arbitrary files?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this ZipSlip vulnerability. You can continue exploring, but no additional points
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

    <!-- Stage Progress Tracker -->
    <div class="stage-tracker">
      <div class="stage-header">
        <i class="fas fa-layer-group"></i>
        <span>Attack Chain Progress</span>
      </div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info">
            <span class="stage-title">Reconnaissance</span>
            <span class="stage-desc">Analyze ZIP extraction</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info">
            <span class="stage-title">Craft Payload</span>
            <span class="stage-desc">Build malicious ZIP</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info">
            <span class="stage-title">Exploit</span>
            <span class="stage-desc">Overwrite files</span>
          </div>
        </div>
      </div>
      <div class="stage-message">
        <i class="fas fa-info-circle"></i>
        <span><?php echo $stage_messages[$current_stage]; ?></span>
      </div>
    </div>

    <!-- Archive Manager Interface -->
    <div class="archive-card">
      <div class="archive-header">
        <div class="archive-brand">
          <i class="fas fa-box-open"></i>
          <span>Archive Manager</span>
        </div>
        <div class="archive-badge">
          <i class="fas fa-shield-alt"></i>
          <span>ZIP Processing</span>
        </div>
      </div>

      <div class="archive-body">
        <!-- Upload Simulation -->
        <div class="upload-panel">
          <div class="upload-header">
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Upload Archive</span>
          </div>
          <div class="upload-zone">
            <i class="fas fa-file-archive fa-3x"></i>
            <p>Drop ZIP files here or click to browse</p>
            <span class="upload-hint">Only .zip files are accepted</span>
          </div>
        </div>

        <!-- Archive List -->
        <div class="archive-list">
          <div class="list-header">
            <i class="fas fa-list"></i>
            <span>Stored Archives</span>
          </div>
          <?php foreach ($uploaded_archives as $name => $info): ?>
            <div class="archive-item <?php echo $archive === $name ? 'active' : ''; ?>">
              <div class="archive-icon"><i class="fas fa-file-archive"></i></div>
              <div class="archive-info">
                <span class="archive-name"><?php echo $name; ?></span>
                <span class="archive-meta"><?php echo $info['size']; ?> | <?php echo $info['files']; ?> files |
                  <?php echo $info['date']; ?></span>
              </div>
              <a href="?archive=<?php echo $name; ?>&extract=1" class="extract-btn">
                <i class="fas fa-expand-alt"></i>
                <span>Extract</span>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Extraction Preview -->
        <div class="extract-preview">
          <div class="extract-header">
            <i class="fas fa-eye"></i>
            <span>Extraction Preview</span>
          </div>
          <div class="extract-body">
            <?php if ($extract): ?>
              <div class="extract-simulate">
                <h4>Simulating extraction of: <?php echo htmlspecialchars($archive); ?></h4>
                <div class="extract-log">
                  <div class="log-entry">
                    <i class="fas fa-check-circle"></i>
                    <span>Validating archive format... OK</span>
                  </div>
                  <div class="log-entry">
                    <i class="fas fa-check-circle"></i>
                    <span>Reading ZIP entries...
                      <?php echo isset($uploaded_archives[$archive]) ? $uploaded_archives[$archive]['files'] : '???'; ?>
                      files found</span>
                  </div>
                  <div class="log-entry">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Extracting to: <code>uploads/extracted/</code></span>
                  </div>
                  <?php if ($exploited): ?>
                    <div class="log-entry danger">
                      <i class="fas fa-skull-crossbones"></i>
                      <span>WARNING: Directory traversal detected in ZIP entry!</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php else: ?>
              <div class="extract-placeholder">
                <i class="fas fa-hand-pointer fa-2x"></i>
                <p>Click "Extract" on an archive to see the extraction process.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ZipSlip Attack Explanation -->
    <div class="zipslip-panel">
      <div class="zipslip-header">
        <i class="fas fa-book-open"></i>
        <span>ZipSlip Attack Vector</span>
      </div>
      <div class="zipslip-body">
        <div class="zipslip-flow">
          <div class="zs-step">
            <div class="zs-icon"><i class="fas fa-file-archive"></i></div>
            <div class="zs-content">
              <span class="zs-title">Malicious ZIP</span>
              <code>evil.zip contains: ../../etc/passwd</code>
            </div>
          </div>
          <div class="zs-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="zs-step">
            <div class="zs-icon"><i class="fas fa-expand"></i></div>
            <div class="zs-content">
              <span class="zs-title">Extraction</span>
              <code>extractTo("uploads/extracted/")</code>
            </div>
          </div>
          <div class="zs-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="zs-step">
            <div class="zs-icon"><i class="fas fa-skull"></i></div>
            <div class="zs-content">
              <span class="zs-title">File Overwrite</span>
              <code>/etc/passwd is overwritten!</code>
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
        <code>GET /PATH-TRAV-CASE6.php?archive=<?php echo urlencode($archive); ?><?php echo $extract ? '&extract=1' : ''; ?></code>
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
          <pre>// VULNERABLE: No entry path validation
$zip = new ZipArchive();
$zip->open($archive);

// Dangerous: extracts ALL entries without checking paths!
$zip->extractTo('uploads/extracted/');
$zip->close();

// Safe approach would validate each entry:
// foreach ($zip entries) {
//   if (strpos($entry, '..') !== false) skip;
//   if (!startsWith($entry, 'safe_prefix/')) skip;
// }</pre>
        </div>
        <div class="vuln-note critical">
          <i class="fas fa-radiation"></i>
          <span><strong>Critical:</strong> ZipSlip affects thousands of libraries and applications. The vulnerability
            exists because developers trust ZIP entry names and don't validate them before extraction. A single
            malicious archive can compromise the entire server.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">ZipSlip works by putting directory traversal sequences in ZIP entry names. Try crafting an
          archive parameter with traversal: <code>?archive=../../etc/passwd.zip</code> or simulate with
          <code>?archive=evil.zip&extract=1</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The application only checks the file extension (.zip) but doesn't validate entry names
          inside the archive. Try <code>?archive=../../../etc/passwd.zip&extract=1</code> or any path containing traversal
          sequences.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?archive=../../etc/passwd.zip&extract=1</code> or any parameter containing
          <code>../</code> with <code>extract=1</code>. The vulnerability is in the extraction logic, not the archive name
          itself!
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
    <input type="hidden" name="archive" value="<?php echo htmlspecialchars($archive); ?>">
    <?php if ($extract): ?><input type="hidden" name="extract" value="1"><?php endif; ?>
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const archive = urlParams.get('archive') || '';
      const extract = urlParams.has('extract');

      const slipPatterns = [
        '../', '..\\', '..%2f', '..%5c',
        '..%252f', '..%255c',
        '%2e%2e%2f', '%252e%252e%252f',
        '....//', '....\\',
        '../../', '..\\..\\',
        '../../../', '..\\..\\..\\'
      ];

      const hasTraversal = slipPatterns.some(pattern =>
        archive.toLowerCase().includes(pattern.toLowerCase())
      );

      const hasDots = /\.\.+/.test(archive);

      if ((hasTraversal || hasDots) && extract && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>