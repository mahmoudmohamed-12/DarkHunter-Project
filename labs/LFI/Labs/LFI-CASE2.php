<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['LFI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['lfi_medium1_attempts'])) {
  $_SESSION['lfi_medium1_attempts'] = 0;
}
if (!isset($_SESSION['lfi_medium1_solved'])) {
  $_SESSION['lfi_medium1_solved'] = false;
}

// ─── Simulated Application: SecureFile Manager ───────────────────────────
$allowed_files = [
  'report'   => 'reports/weekly_report.pdf',
  'invoice'  => 'invoices/invoice_001.pdf',
  'manual'   => 'docs/user_manual.pdf',
  'policy'   => 'docs/security_policy.pdf',
];

// ─── Vulnerable Logic: Weak sanitization with blacklist ──────────────────
$file = isset($_GET['file']) ? $_GET['file'] : 'report';

// Weak sanitization - only removes ../ but not other variants
$sanitized_file = str_replace('../', '', $file);
$sanitized_file = str_replace('..\\', '', $sanitized_file);

$is_blocked = false;
$block_reason = '';

// Check if still contains traversal after "sanitization"
if (strpos($sanitized_file, '../') !== false || strpos($sanitized_file, '..\\') !== false) {
  $is_blocked = true;
  $block_reason = "Path traversal detected!";
}

// Build the file path
$file_path = 'documents/' . $sanitized_file;

$file_content = null;
$file_error = null;

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['lfi_medium1_solved'];
$exploited = false;

// Detect bypass exploitation
$bypass_indicators = [
  // Double encoding
  '..%252f',
  '..%255c',
  '%252e%252e%252f',
  // Unicode/UTF-8 bypasses
  '%c0%af',
  '%c1%9c',
  // Null byte (PHP < 5.3.4)
  '%00',
  // Double dot variations
  '....//',
  '....\\',
  // Path truncation (PHP < 5.4)
  '.php%00',
  '.php/././',
  // Wrapper usage
  'php://filter',
  'php://input',
  'data://',
  'zip://',
  'phar://',
  // Absolute paths that got through
  '/etc/passwd',
  'C:\\',
  'boot.ini',
  // After successful bypass, accessing sensitive files
  'db.php',
  'config.php',
  '.env',
  'flag',
  'secret'
];

foreach ($bypass_indicators as $indicator) {
  if (stripos($file, $indicator) !== false || stripos($sanitized_file, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Detect double encoding bypass: ....// -> ../ after one replacement
if (preg_match('/\.\.\//', $sanitized_file) || preg_match('/\.\.\\/', $sanitized_file)) {
  $exploited = true;
}

// Detect successful file access outside documents/
if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['lfi_medium1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['lfi_medium1_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've bypassed weak input sanitization to exploit an LFI vulnerability. You used double encoding, path truncation, or recursive traversal to evade the naive blacklist filter. This proves why blacklist-based defenses are insufficient!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
  $_SESSION['lfi_medium1_attempts']++;
}

$attempts = $_SESSION['lfi_medium1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecureFile Manager - LFI Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/LFI-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to LFI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-folder"></i> SecureFile Manager</h1>
      <p class="lab-description">A corporate document management system with <strong>weak input sanitization</strong>.
        The developers tried to block path traversal with a simple string replacement. Can you bypass their naive
        filter?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this filter bypass. You can continue exploring, but no additional points will be
            awarded.</p>
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

    <!-- File Manager Interface -->
    <div class="filemanager-card">
      <div class="fm-header">
        <div class="fm-brand">
          <i class="fas fa-folder-open"></i>
          <span>SecureFile Manager</span>
        </div>
        <div class="fm-security-badge">
          <i class="fas fa-shield-alt"></i>
          <span>Protected</span>
        </div>
      </div>

      <div class="fm-body">
        <!-- Security Filter Status -->
        <div class="filter-panel">
          <div class="filter-title">
            <i class="fas fa-filter"></i>
            <span>Input Sanitization Engine</span>
          </div>
          <div class="filter-rules">
            <div class="filter-rule">
              <span class="rule-name">Original Input:</span>
              <code class="rule-value original"><?php echo htmlspecialchars($file); ?></code>
            </div>
            <div class="filter-rule">
              <span class="rule-name">After Sanitization:</span>
              <code class="rule-value sanitized"><?php echo htmlspecialchars($sanitized_file); ?></code>
            </div>
            <div class="filter-rule">
              <span class="rule-name">Resolved Path:</span>
              <code class="rule-value resolved"><?php echo htmlspecialchars($file_path); ?></code>
            </div>
          </div>
          <div class="filter-status <?php echo $is_blocked ? 'blocked' : 'passed'; ?>">
            <i class="fas fa-<?php echo $is_blocked ? 'times-circle' : 'check-circle'; ?>"></i>
            <span><?php echo $is_blocked ? 'Request Blocked' : 'Filter Passed'; ?></span>
          </div>
          <?php if ($is_blocked): ?>
            <div class="block-detail">
              <i class="fas fa-ban"></i>
              <span><?php echo htmlspecialchars($block_reason); ?></span>
            </div>
          <?php endif; ?>
        </div>

        <!-- File Browser -->
        <div class="browser-panel">
          <div class="browser-header">
            <i class="fas fa-list"></i>
            <span>Available Documents</span>
          </div>
          <div class="file-list">
            <?php foreach ($allowed_files as $key => $path): ?>
              <a href="?file=<?php echo $key; ?>" class="file-item <?php echo $file === $key ? 'active' : ''; ?>">
                <div class="file-icon"><i class="fas fa-file-pdf"></i></div>
                <div class="file-info">
                  <span class="file-name"><?php echo ucfirst($key); ?></span>
                  <span class="file-path"><?php echo $path; ?></span>
                </div>
                <div class="file-size">2.4 MB</div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- File Preview -->
        <div class="preview-panel">
          <div class="preview-header">
            <i class="fas fa-eye"></i>
            <span>File Preview</span>
          </div>
          <div class="preview-body">
            <?php if (isset($allowed_files[$file])): ?>
              <div class="preview-normal">
                <i class="fas fa-file-pdf fa-3x"></i>
                <p>Preview: <strong><?php echo ucfirst($file); ?></strong></p>
                <p class="preview-path"><?php echo $allowed_files[$file]; ?></p>
              </div>
            <?php elseif (!$is_blocked): ?>
              <div class="preview-custom">
                <i class="fas fa-file-code"></i>
                <p>Attempting to load: <code><?php echo htmlspecialchars($file_path); ?></code></p>
                <?php if ($exploited): ?>
                  <div class="exploit-detected">
                    <i class="fas fa-bug"></i>
                    <span>Exploitation pattern detected!</span>
                  </div>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="preview-blocked">
                <i class="fas fa-ban fa-3x"></i>
                <p>Access Denied</p>
                <p class="preview-reason">The security filter blocked this request.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Code Analysis -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Vulnerable Filter Implementation</span>
      </div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// WEAK SANITIZATION - Easily Bypassed!
$file = $_GET['file'];

// Only removes '../' once - not recursive!
$sanitized = str_replace('../', '', $file);
$sanitized = str_replace('..\\', '', $sanitized);

// No check for encoded variants
// No check for double dots: ....//
// No check for absolute paths
// No check for PHP wrappers

$file_path = 'documents/' . $sanitized;
include($file_path);</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> Single-pass string replacement. <code>....//</code> becomes
            <code>../</code> after one replacement. Also missing: URL decoding, wrapper blocking, and path
            canonicalization.</span>
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
        <code>GET /LFI-CASE2.php?file=<?php echo urlencode($file); ?></code>
      </div>
    </div>

    <!-- Bypass Techniques Reference -->
    <div class="techniques-panel">
      <div class="techniques-header">
        <i class="fas fa-book-open"></i>
        <span>Known Bypass Techniques</span>
      </div>
      <div class="techniques-body">
        <div class="technique-item">
          <span class="technique-name">Double Encoding</span>
          <code class="technique-payload">..%252f..%252fetc%252fpasswd</code>
        </div>
        <div class="technique-item">
          <span class="technique-name">Recursive Traversal</span>
          <code class="technique-payload">....//....//etc/passwd</code>
        </div>
        <div class="technique-item">
          <span class="technique-name">Null Byte Truncation</span>
          <code class="technique-payload">../../../etc/passwd%00</code>
        </div>
        <div class="technique-item">
          <span class="technique-name">PHP Wrappers</span>
          <code class="technique-payload">php://filter/read=convert.base64-encode/resource=../config.php</code>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The filter only removes <code>../</code> once. What happens if you nest traversal
          sequences? Try <code>?file=....//....//etc/passwd</code> - after removing <code>../</code> once, it becomes
          <code>../../etc/passwd</code>!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try double URL encoding: <code>?file=..%252f..%252f..%252fetc%252fpasswd</code>. The server
          decodes %25 to %, then %2f to /, resulting in <code>../../etc/passwd</code> after the first replacement!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?file=....//....//....//etc/passwd</code> for recursive bypass, or
          <code>?file=..%252f..%252fConfig/db.php</code> for double encoding. The filter only runs once - any bypass
          technique works!
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
    <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const file = urlParams.get('file') || '';

      // Check for bypass exploitation
      const bypassPatterns = [
        '..%252f', '..%255c', '%252e%252e%252f',
        '%c0%af', '%c1%9c', '%00',
        '....//', '....\\',
        '.php%00', '.php/././',
        'php://filter', 'php://input', 'data://', 'zip://', 'phar://',
        '/etc/passwd', 'C:\\', 'boot.ini',
        'db.php', 'config.php', '.env', 'flag', 'secret'
      ];

      const hasBypass = bypassPatterns.some(pattern =>
        file.toLowerCase().includes(pattern.toLowerCase())
      );

      // Detect recursive traversal after sanitization
      const sanitized = file.replace(/\.\.\//g, '').replace(/\.\.\\/g, '');
      const stillHasTraversal = /\.\.\//.test(sanitized) || /\.\.\\/.test(sanitized);

      if ((hasBypass || stillHasTraversal) && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>