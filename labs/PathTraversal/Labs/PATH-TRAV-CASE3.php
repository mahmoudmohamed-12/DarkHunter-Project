<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_medium1_attempts'])) {
  $_SESSION['pt_medium1_attempts'] = 0;
}
if (!isset($_SESSION['pt_medium1_solved'])) {
  $_SESSION['pt_medium1_solved'] = false;
}

// ─── Simulated Application: DarkHunter SecureDocs ────────────────────────
$documents = [
  'report_q1.pdf' => ['title' => 'Q1 Security Report', 'size' => '2.4 MB'],
  'audit_2025.pdf' => ['title' => 'Annual Security Audit', 'size' => '5.1 MB'],
  'policy_v2.pdf' => ['title' => 'Security Policy v2.0', 'size' => '1.8 MB'],
  'incident_001.pdf' => ['title' => 'Incident Report #001', 'size' => '890 KB'],
];

// ─── Vulnerable Logic: Single-layer URL decode ───────────────────────────
$doc = isset($_GET['doc']) ? $_GET['doc'] : 'report_q1.pdf';

// "Security": URL decode once and check for traversal
$decoded_once = urldecode($doc);

$is_blocked = false;
$block_reason = '';

if (strpos($decoded_once, '../') !== false || strpos($decoded_once, '..\\') !== false) {
  $is_blocked = true;
  $block_reason = "Path traversal detected after URL decoding!";
}

// Build path
$doc_path = 'secure_docs/' . $doc;

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_medium1_solved'];
$exploited = false;

// Detect double encoding exploitation
$double_encode_indicators = [
  '%252f',
  '%255c',
  '%252e',
  '%252F',
  '%255C',
  '%252E',
  '%25%32%66',
  '%25%32%65',
];

foreach ($double_encode_indicators as $indicator) {
  if (stripos($doc, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check if decoded path contains traversal (bypass successful)
if (preg_match('/\.\.\//', $decoded_once) || preg_match('/\.\.\\/', $decoded_once)) {
  // Only count as exploit if original had encoded sequences
  if (preg_match('/%25|%2e|%2f|%5c/i', $doc)) {
    $exploited = true;
  }
}

// Also check for direct system file access attempts
$system_indicators = [
  'etc/passwd',
  'etc/shadow',
  'windows/win.ini',
  'proc/self',
  'var/log',
  'var/www',
  'Config/db',
  'includes/score',
];

foreach ($system_indicators as $indicator) {
  if (stripos($decoded_once, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_medium1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_medium1_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've bypassed single-layer URL decoding to exploit a Path Traversal vulnerability. By double-encoding traversal sequences (%252e%252e%252f), the first decode produces %2e%2e%2f which passes the filter, but PHP decodes again to ../. This is a classic filter bypass technique!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['doc'])) {
  $_SESSION['pt_medium1_attempts']++;
}

$attempts = $_SESSION['pt_medium1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecureDocs Portal - Path Traversal Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-file-shield"></i> DarkHunter SecureDocs</h1>
      <p class="lab-description">A secure document portal with <strong>single-layer URL decoding</strong>. The
        developers decode the input once and check for traversal. Can you use double encoding to slip past their filter?
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this double encoding bypass. You can continue exploring, but no additional points
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

    <!-- SecureDocs Interface -->
    <div class="securedocs-card">
      <div class="sd-header">
        <div class="sd-brand">
          <i class="fas fa-folder-shield"></i>
          <span>SecureDocs Portal</span>
        </div>
        <div class="sd-badge">
          <i class="fas fa-lock"></i>
          <span>URL Decode Protected</span>
        </div>
      </div>

      <div class="sd-body">
        <!-- Decode Analysis Panel -->
        <div class="decode-panel">
          <div class="decode-header">
            <i class="fas fa-exchange-alt"></i>
            <span>URL Decode Analysis</span>
          </div>
          <div class="decode-steps">
            <div class="decode-step">
              <span class="step-label">Original Input:</span>
              <code class="step-value original"><?php echo htmlspecialchars($doc); ?></code>
            </div>
            <div class="decode-arrow"><i class="fas fa-arrow-down"></i> urldecode()</div>
            <div class="decode-step">
              <span class="step-label">After Decode:</span>
              <code class="step-value decoded"><?php echo htmlspecialchars($decoded_once); ?></code>
            </div>
            <div class="decode-arrow"><i class="fas fa-arrow-down"></i> filter check</div>
            <div class="decode-step">
              <span class="step-label">Filter Result:</span>
              <span class="step-value result <?php echo $is_blocked ? 'blocked' : 'passed'; ?>">
                <i class="fas fa-<?php echo $is_blocked ? 'times-circle' : 'check-circle'; ?>"></i>
                <?php echo $is_blocked ? 'BLOCKED' : 'PASSED'; ?>
              </span>
            </div>
          </div>
          <?php if ($is_blocked): ?>
          <div class="block-reason">
            <i class="fas fa-info-circle"></i>
            <span><?php echo htmlspecialchars($block_reason); ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Document Grid -->
        <div class="doc-grid">
          <?php foreach ($documents as $key => $info): ?>
          <a href="?doc=<?php echo $key; ?>" class="doc-card <?php echo $doc === $key ? 'active' : ''; ?>">
            <div class="doc-icon"><i class="fas fa-file-pdf"></i></div>
            <div class="doc-info">
              <span class="doc-title"><?php echo $info['title']; ?></span>
              <span class="doc-size"><?php echo $info['size']; ?></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Document Preview -->
        <div class="doc-preview">
          <div class="preview-header">
            <i class="fas fa-eye"></i>
            <span>Document Preview</span>
          </div>
          <div class="preview-body">
            <?php if (isset($documents[$doc])): ?>
            <div class="preview-normal">
              <i class="fas fa-file-pdf fa-3x"></i>
              <p><strong><?php echo $documents[$doc]['title']; ?></strong></p>
              <p class="preview-meta">Size: <?php echo $documents[$doc]['size']; ?> | Format: PDF</p>
            </div>
            <?php elseif (!$is_blocked && $exploited): ?>
            <div class="preview-exploit">
              <i class="fas fa-bug fa-2x"></i>
              <h3>Bypass Successful!</h3>
              <p>The filter was bypassed using double encoding.</p>
              <p>Attempting to access: <code><?php echo htmlspecialchars($doc_path); ?></code></p>
            </div>
            <?php elseif ($is_blocked): ?>
            <div class="preview-blocked">
              <i class="fas fa-ban fa-2x"></i>
              <h3>Access Blocked</h3>
              <p>The security filter detected traversal patterns.</p>
            </div>
            <?php else: ?>
            <div class="preview-unknown">
              <i class="fas fa-question-circle fa-2x"></i>
              <h3>Unknown Document</h3>
              <p>Path: <code><?php echo htmlspecialchars($doc_path); ?></code></p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Encoding Reference -->
    <div class="encoding-panel">
      <div class="encoding-header">
        <i class="fas fa-code"></i>
        <span>Encoding Reference Table</span>
      </div>
      <div class="encoding-body">
        <div class="encoding-row header">
          <span>Character</span>
          <span>Single Encode</span>
          <span>Double Encode</span>
        </div>
        <div class="encoding-row">
          <code>.</code>
          <code>%2e</code>
          <code>%252e</code>
        </div>
        <div class="encoding-row">
          <code>/</code>
          <code>%2f</code>
          <code>%252f</code>
        </div>
        <div class="encoding-row">
          <code>\</code>
          <code>%5c</code>
          <code>%255c</code>
        </div>
        <div class="encoding-row">
          <code>..</code>
          <code>%2e%2e</code>
          <code>%252e%252e</code>
        </div>
        <div class="encoding-row">
          <code>../</code>
          <code>%2e%2e%2f</code>
          <code>%252e%252e%252f</code>
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
        <code>GET /PATH-TRAV-CASE3.php?doc=<?php echo urlencode($doc); ?></code>
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
          <pre>// VULNERABLE: Only decodes once
$doc = $_GET['doc'];

// Single decode - easily bypassed
$decoded = urldecode($doc);

if (strpos($decoded, '../') !== false) {
    die("Traversal detected!");
}

// PHP auto-decodes $_GET again!
$file_path = 'secure_docs/' . $doc;
$content = file_get_contents($file_path);</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> The filter decodes once and checks, but PHP's <code>$_GET</code> already
            decodes once. Double-encoded input passes the filter but gets fully decoded by PHP.
            <code>%252e%252e%252f</code> → filter sees <code>%2e%2e%2f</code> (safe) → PHP sees <code>../</code>
            (dangerous!).</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The application URL-decodes once and checks for <code>../</code>. If you double-encode the
        traversal sequences, the first decode produces <code>%2e%2e%2f</code> which doesn't match <code>../</code>. Try
        <code>?doc=%252e%252e%252fetc%252fpasswd</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Use the encoding table above. <code>%252e</code> = double-encoded dot, <code>%252f</code> =
        double-encoded slash. Try <code>?doc=%252e%252e%252f%252e%252e%252fetc%252fpasswd</code> to go up two
        directories.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?doc=%252e%252e%252f%252e%252e%252fetc%252fpasswd</code> for double-encoded
        traversal. The filter sees <code>%2e%2e%2f%2e%2e%2fetc%2fpasswd</code> (no ../), but PHP decodes it to
        <code>../../etc/passwd</code>!
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
    <input type="hidden" name="doc" value="<?php echo htmlspecialchars($doc); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const doc = urlParams.get('doc') || '';

    const doubleEncodePatterns = [
      '%252f', '%255c', '%252e',
      '%252F', '%255C', '%252E',
      '%25%32%66', '%25%32%65'
    ];

    const hasDoubleEncode = doubleEncodePatterns.some(pattern =>
      doc.toLowerCase().includes(pattern.toLowerCase())
    );

    // Check for traversal in decoded path with encoded sequences
    const decoded = decodeURIComponent(doc);
    const hasTraversal = /\.\.\//.test(decoded) || /\.\.\\/.test(decoded);
    const hasEncoded = /%25|%2e|%2f|%5c/i.test(doc);

    if ((hasDoubleEncode || (hasTraversal && hasEncoded)) && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>