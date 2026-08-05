<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['LFI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['lfi_hard1_attempts'])) {
  $_SESSION['lfi_hard1_attempts'] = 0;
}
if (!isset($_SESSION['lfi_hard1_solved'])) {
  $_SESSION['lfi_hard1_solved'] = false;
}
if (!isset($_SESSION['lfi_hard1_stage'])) {
  $_SESSION['lfi_hard1_stage'] = 1;
}

// ─── Simulated Application: DarkHunter File Converter ────────────────────
$converter_name = "FileMagic Converter";
$supported_formats = ['txt', 'log', 'xml', 'json', 'csv'];

// ─── Vulnerable Logic: PHP wrapper inclusion ──────────────────────────────
$file = isset($_GET['file']) ? $_GET['file'] : 'sample.txt';

// "Security" check - only allows certain "safe" extensions
$has_safe_ext = false;
foreach ($supported_formats as $ext) {
  if (substr($file, -strlen($ext)) === $ext) {
    $has_safe_ext = true;
    break;
  }
}

// Also check for wrapper prefixes that might bypass
$wrapper_prefixes = ['php://', 'data://', 'file://', 'zip://', 'phar://', 'expect://'];
$detected_wrapper = null;
foreach ($wrapper_prefixes as $wrapper) {
  if (stripos($file, $wrapper) === 0) {
    $detected_wrapper = $wrapper;
    break;
  }
}

$is_blocked = false;
$block_reason = '';

if (!$has_safe_ext && !$detected_wrapper) {
  $is_blocked = true;
  $block_reason = "Unsupported file format. Allowed: " . implode(', ', $supported_formats);
}

// ─── Stage Logic for Multi-Step Attack ───────────────────────────────────
$current_stage = $_SESSION['lfi_hard1_stage'];
$stage_messages = [
  1 => "Stage 1: Identify PHP wrapper support and filter weaknesses.",
  2 => "Stage 2: Use php://filter to read source code or data:// to inject code.",
  3 => "Stage 3: Chain wrappers with file upload or SSRF to achieve RCE."
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['lfi_hard1_solved'];
$exploited = false;

// Detect wrapper exploitation
$wrapper_indicators = [
  'php://filter',
  'php://input',
  'php://memory',
  'php://temp',
  'data://text/plain',
  'data://application/x-httpd-php',
  'file:///etc/passwd',
  'file:///var/www',
  'zip://',
  'phar://',
  'expect://',
  'convert.base64-encode',
  'convert.base64-decode',
  'resource=',
  'read=',
  // SSRF via file parameter
  'http://',
  'https://',
  'ftp://',
  'gopher://',
  // File upload abuse
  'uploads/',
  'tmp/',
  'temp/',
];

foreach ($wrapper_indicators as $indicator) {
  if (stripos($file, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Detect base64 encoded payloads (common with php://filter)
if (preg_match('/[A-Za-z0-9+\/]{20,}={0,2}/', $file) && stripos($file, 'php://') !== false) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['lfi_hard1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['lfi_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've executed an advanced LFI chain attack using PHP wrappers. You leveraged php://filter for source code disclosure, data:// for code injection, or combined LFI with SSRF/file upload to achieve full Remote Code Execution. This is expert-level exploitation!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
  $_SESSION['lfi_hard1_attempts']++;
}

$attempts = $_SESSION['lfi_hard1_attempts'];

// Update stage based on attempts
if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['lfi_hard1_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['lfi_hard1_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FileMagic Converter - LFI Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/LFI-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to LFI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-magic"></i> FileMagic Converter</h1>
      <p class="lab-description">An advanced file conversion service that processes files using PHP stream wrappers. The
        application enforces extension checks but <strong>allows PHP wrappers</strong>. Can you chain multiple
        techniques to achieve full server compromise?</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this advanced LFI chain. You can continue exploring, but no additional points will
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
            <span class="stage-desc">Map wrapper support</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info">
            <span class="stage-title">Wrapper Abuse</span>
            <span class="stage-desc">Bypass extension checks</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info">
            <span class="stage-title">Full RCE</span>
            <span class="stage-desc">Server compromise</span>
          </div>
        </div>
      </div>
      <div class="stage-message">
        <i class="fas fa-info-circle"></i>
        <span><?php echo $stage_messages[$current_stage]; ?></span>
      </div>
    </div>

    <!-- Converter Interface -->
    <div class="converter-card">
      <div class="converter-header">
        <div class="converter-brand">
          <i class="fas fa-cogs"></i>
          <span>FileMagic Converter</span>
        </div>
        <div class="converter-badge">
          <i class="fas fa-shield-alt"></i>
          <span>Advanced Protection</span>
        </div>
      </div>

      <div class="converter-body">
        <!-- File Input -->
        <div class="input-panel">
          <div class="input-header">
            <i class="fas fa-file-import"></i>
            <span>Source File</span>
          </div>
          <div class="input-field">
            <span class="input-prefix">file://</span>
            <input type="text" class="input-value" value="<?php echo htmlspecialchars($file); ?>" readonly>
          </div>
          <div class="input-meta">
            <span class="meta-item"><i class="fas fa-check-circle"></i> Extension:
              <?php echo $has_safe_ext ? 'Valid' : 'Invalid'; ?></span>
            <span class="meta-item"><i
                class="fas fa-<?php echo $detected_wrapper ? 'exclamation-triangle' : 'check-circle'; ?>"></i> Wrapper:
              <?php echo $detected_wrapper ? 'Detected (' . htmlspecialchars($detected_wrapper) . ')' : 'None'; ?></span>
          </div>
        </div>

        <!-- Validation Panel -->
        <div class="validation-panel">
          <div class="validation-header">
            <i class="fas fa-filter"></i>
            <span>Security Validation</span>
          </div>
          <div class="validation-rules">
            <div class="v-rule <?php echo $has_safe_ext ? 'pass' : ($detected_wrapper ? 'warning' : 'fail'); ?>">
              <i
                class="fas fa-<?php echo $has_safe_ext ? 'check-circle' : ($detected_wrapper ? 'exclamation-circle' : 'times-circle'); ?>"></i>
              <span>Extension Check</span>
              <span
                class="v-status"><?php echo $has_safe_ext ? 'PASS' : ($detected_wrapper ? 'BYPASSED' : 'FAIL'); ?></span>
            </div>
            <div class="v-rule <?php echo !$is_blocked ? 'pass' : 'fail'; ?>">
              <i class="fas fa-<?php echo !$is_blocked ? 'check-circle' : 'times-circle'; ?>"></i>
              <span>Access Control</span>
              <span class="v-status"><?php echo !$is_blocked ? 'ALLOWED' : 'BLOCKED'; ?></span>
            </div>
          </div>
          <?php if ($is_blocked): ?>
          <div class="validation-error">
            <i class="fas fa-ban"></i>
            <span><?php echo htmlspecialchars($block_reason); ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Conversion Preview -->
        <div class="preview-panel">
          <div class="preview-header">
            <i class="fas fa-eye"></i>
            <span>Conversion Preview</span>
          </div>
          <div class="preview-body">
            <?php if (!$is_blocked): ?>
            <div class="preview-success">
              <i class="fas fa-file-alt"></i>
              <p>Processing: <code><?php echo htmlspecialchars($file); ?></code></p>
              <?php if ($exploited): ?>
              <div class="exploit-alert">
                <i class="fas fa-bug"></i>
                <span>Wrapper exploitation detected!</span>
              </div>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="preview-blocked">
              <i class="fas fa-ban"></i>
              <p>Conversion blocked by security policy.</p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="converter-actions">
          <?php if (!$is_blocked): ?>
          <a href="?file=<?php echo urlencode($file); ?>&convert=1" class="action-btn convert">
            <i class="fas fa-play"></i>
            <span>Convert File</span>
          </a>
          <?php else: ?>
          <button class="action-btn disabled" disabled>
            <i class="fas fa-ban"></i>
            <span>Blocked</span>
          </button>
          <?php endif; ?>
          <a href="?file=sample.txt" class="action-btn reset">
            <i class="fas fa-undo"></i>
            <span>Reset</span>
          </a>
        </div>
      </div>
    </div>

    <!-- PHP Wrappers Reference -->
    <div class="wrappers-panel">
      <div class="wrappers-header">
        <i class="fas fa-book"></i>
        <span>Available PHP Stream Wrappers</span>
      </div>
      <div class="wrappers-body">
        <div class="wrapper-item">
          <code class="wrapper-name">php://filter</code>
          <span class="wrapper-desc">Apply filters to streams - read source code as base64</span>
          <code class="wrapper-example">php://filter/read=convert.base64-encode/resource=config.php</code>
        </div>
        <div class="wrapper-item">
          <code class="wrapper-name">php://input</code>
          <span class="wrapper-desc">Access raw POST data - execute code via request body</span>
          <code class="wrapper-example">php://input + POST: &lt;?php system('id'); ?&gt;</code>
        </div>
        <div class="wrapper-item">
          <code class="wrapper-name">data://</code>
          <span class="wrapper-desc">Embed data inline - inject PHP code directly</span>
          <code class="wrapper-example">data://text/plain,&lt;?php phpinfo(); ?&gt;</code>
        </div>
        <div class="wrapper-item">
          <code class="wrapper-name">zip:// / phar://</code>
          <span class="wrapper-desc">Access compressed archives - bypass extension checks</span>
          <code class="wrapper-example">zip://uploads/file.zip%23shell.php</code>
        </div>
      </div>
    </div>

    <!-- Attack Chain Visualization -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-project-diagram"></i>
        <span>Advanced Attack Chain</span>
      </div>
      <div class="debug-body">
        <div class="advanced-chain">
          <div class="chain-path">
            <div class="path-node">
              <i class="fas fa-bug"></i>
              <span>LFI</span>
            </div>
            <div class="path-arrow"><i class="fas fa-plus"></i></div>
            <div class="path-node">
              <i class="fas fa-filter"></i>
              <span>php://filter</span>
            </div>
            <div class="path-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="path-node">
              <i class="fas fa-code"></i>
              <span>Source Disclosure</span>
            </div>
          </div>
          <div class="path-separator">OR</div>
          <div class="chain-path">
            <div class="path-node">
              <i class="fas fa-bug"></i>
              <span>LFI</span>
            </div>
            <div class="path-arrow"><i class="fas fa-plus"></i></div>
            <div class="path-node">
              <i class="fas fa-upload"></i>
              <span>File Upload</span>
            </div>
            <div class="path-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="path-node">
              <i class="fas fa-skull"></i>
              <span>RCE</span>
            </div>
          </div>
        </div>
        <div class="vuln-note critical">
          <i class="fas fa-radiation"></i>
          <span><strong>Critical:</strong> PHP wrappers can transform a simple file read into full code execution.
            Real-world attacks often chain LFI with other vulnerabilities.</span>
        </div>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-terminal"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /LFI-CASE4.php?file=<?php echo urlencode($file); ?></code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The application allows PHP wrappers! Try
        <code>?file=php://filter/read=convert.base64-encode/resource=../Config/db.php</code> to read database
        credentials as base64.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 5): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try <code>?file=data://text/plain,&lt;?php phpinfo(); ?&gt;</code> for direct code
        execution, or <code>?file=php://input</code> with a POST request containing PHP code.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 8): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?file=php://filter/read=convert.base64-encode/resource=../../Config/db.php</code>
        for source disclosure, or <code>?file=data://text/plain,&lt;?php system(\$_GET['cmd']); ?&gt;</code> for RCE.
        Any wrapper exploitation solves this challenge!</div>
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
    <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const file = urlParams.get('file') || '';

    // Check for wrapper exploitation
    const wrapperPatterns = [
      'php://filter', 'php://input', 'php://memory', 'php://temp',
      'data://text/plain', 'data://application/x-httpd-php',
      'file:///etc/passwd', 'file:///var/www',
      'zip://', 'phar://', 'expect://',
      'convert.base64-encode', 'convert.base64-decode',
      'resource=', 'read=',
      'http://', 'https://', 'ftp://', 'gopher://',
      'uploads/', 'tmp/', 'temp/'
    ];

    const hasWrapper = wrapperPatterns.some(pattern =>
      file.toLowerCase().includes(pattern.toLowerCase())
    );

    // Check for base64 in php://filter
    const hasBase64Filter = file.includes('php://') && file.includes('base64');

    if ((hasWrapper || hasBase64Filter) && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>