<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_medium2_attempts'])) {
  $_SESSION['pt_medium2_attempts'] = 0;
}
if (!isset($_SESSION['pt_medium2_solved'])) {
  $_SESSION['pt_medium2_solved'] = false;
}

// ─── Simulated Application: DarkHunter Unicode File Server ─────────────
$files = [
  'report_日本語.txt' => ['name' => 'Japanese Report', 'content' => 'これは日本語のテストファイルです。'],
  'document_中文.txt' => ['name' => 'Chinese Document', 'content' => '这是一个中文测试文件。'],
  'fichier_français.txt' => ['name' => 'French File', 'content' => 'Ceci est un fichier de test en français.'],
  'file_العربية.txt' => ['name' => 'Arabic File', 'content' => 'هذا ملف اختبار باللغة العربية.'],
];

// ─── Vulnerable Logic: Unicode normalization before check ──────────────
$filename = isset($_GET['file']) ? $_GET['file'] : 'report_日本語.txt';

// "Security": Normalize unicode then check
// VULNERABLE: Uses NFKC normalization which can change byte sequences
$normalized = normalizer_normalize($filename, Normalizer::FORM_KC);

$is_blocked = false;
$block_reason = '';

if (strpos($normalized, '../') !== false || strpos($normalized, '..\\') !== false) {
  $is_blocked = true;
  $block_reason = "Path traversal detected after Unicode normalization!";
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_medium2_solved'];
$exploited = false;

// Detect Unicode bypass exploitation
$unicode_indicators = [
  // Overlong UTF-8 sequences for dot
  '%c0%ae',
  '%c0%AE',
  // Unicode fullwidth characters
  '%ef%bc%8e',
  '%EF%BC%8E',
  '%ef%bc%8f',
  '%EF%BC%8F',
  '%ef%bc%bc',
  '%EF%BC%BC',
  // Other unicode variants
  '%e0%80%ae',
  '%E0%80%AE',
  '%f0%80%80%ae',
  '%F0%80%80%AE',
  // Homoglyphs
  '..%ef%bc%8f',
  '..%EF%BC%8F',
  '%ef%bc%8e%ef%bc%8e%ef%bc%8f'
];

foreach ($unicode_indicators as $indicator) {
  if (stripos($filename, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check if normalized path contains traversal (bypass successful)
if (preg_match('#\.\./#', $normalized) || preg_match('#\.\.\\\\#', $normalized)) {
  // Only count if original used unicode tricks
  if (preg_match('/%[c-fC-F][0-9a-fA-F]|%ef|%e0|%f0/i', $filename)) {
    $exploited = true;
  }
}

// Also detect system file access
$system_indicators = [
  'etc/passwd',
  'etc/shadow',
  'windows/win.ini',
  'proc/self',
  'var/log',
  'var/www',
];
foreach ($system_indicators as $indicator) {
  if (stripos($normalized, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_medium2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_medium2_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've exploited Unicode normalization to bypass path traversal filters. By using overlong UTF-8 sequences or Unicode homoglyphs, you tricked the NFKC normalizer into converting your input to standard traversal sequences after the security check. This is an advanced bypass technique used in real-world attacks!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) {
  $_SESSION['pt_medium2_attempts']++;
}

$attempts = $_SESSION['pt_medium2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unicode File Server - Path Traversal Medium 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-globe"></i> DarkHunter Unicode File Server</h1>
      <p class="lab-description">A multi-language file server with <strong>Unicode NFKC normalization</strong>. The
        developers normalize input before checking for traversal. Can you abuse Unicode equivalence to bypass their
        filter?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Unicode normalization bypass. You can continue exploring, but no additional
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

    <div class="unicode-card">
      <div class="uc-header">
        <div class="uc-brand">
          <i class="fas fa-language"></i>
          <span>Unicode File Server</span>
        </div>
        <div class="uc-badge">
          <i class="fas fa-shield-alt"></i>
          <span>NFKC Normalized</span>
        </div>
      </div>

      <div class="uc-body">
        <div class="norm-panel">
          <div class="norm-header">
            <i class="fas fa-magic"></i>
            <span>Unicode Normalization Engine</span>
          </div>
          <div class="norm-steps">
            <div class="norm-step">
              <span class="step-label">Original Input (bytes):</span>
              <code class="step-value original"><?php echo htmlspecialchars($filename); ?></code>
            </div>
            <div class="norm-arrow"><i class="fas fa-arrow-down"></i> normalizer_normalize(FORM_KC)</div>
            <div class="norm-step">
              <span class="step-label">After NFKC:</span>
              <code class="step-value normalized"><?php echo htmlspecialchars($normalized); ?></code>
            </div>
            <div class="norm-arrow"><i class="fas fa-arrow-down"></i> filter check</div>
            <div class="norm-step">
              <span class="step-label">Filter Result:</span>
              <span class="step-value result <?php echo $is_blocked ? 'blocked' : 'passed'; ?>">
                <i class="fas fa-<?php echo $is_blocked ? 'times-circle' : 'check-circle'; ?>"></i>
                <?php echo $is_blocked ? 'BLOCKED' : 'PASSED'; ?>
              </span>
            </div>
          </div>
        </div>

        <div class="file-list-panel">
          <div class="file-list-header">
            <i class="fas fa-folder-open"></i>
            <span>International Files</span>
          </div>
          <div class="file-grid">
            <?php foreach ($files as $key => $info): ?>
              <a href="?file=<?php echo urlencode($key); ?>"
                class="file-item <?php echo $filename === $key ? 'active' : ''; ?>">
                <div class="file-icon"><i class="fas fa-file-alt"></i></div>
                <div class="file-details">
                  <span class="file-name"><?php echo $info['name']; ?></span>
                  <code class="file-filename"><?php echo htmlspecialchars($key); ?></code>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="content-panel">
          <div class="content-header">
            <i class="fas fa-eye"></i>
            <span>File Content</span>
          </div>
          <div class="content-body">
            <?php if (isset($files[$filename])): ?>
              <div class="content-normal">
                <p class="content-text"><?php echo htmlspecialchars($files[$filename]['content']); ?></p>
              </div>
            <?php elseif ($exploited && !$is_blocked): ?>
              <div class="content-exploit">
                <i class="fas fa-bug fa-2x"></i>
                <h3>Unicode Bypass Successful!</h3>
                <p>The NFKC normalizer converted your Unicode input to standard ASCII traversal sequences.</p>
                <code class="exploit-path">Normalized to: <?php echo htmlspecialchars($normalized); ?></code>
              </div>
            <?php elseif ($is_blocked): ?>
              <div class="content-blocked">
                <i class="fas fa-ban fa-2x"></i>
                <h3>Access Blocked</h3>
                <p>Traversal detected after normalization.</p>
              </div>
            <?php else: ?>
              <div class="content-unknown">
                <i class="fas fa-question-circle fa-2x"></i>
                <h3>File Not Found</h3>
                <p>The requested file does not exist.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="vectors-panel">
      <div class="vectors-header">
        <i class="fas fa-book-open"></i>
        <span>Known Unicode Attack Vectors</span>
      </div>
      <div class="vectors-body">
        <div class="vector-item">
          <span class="vector-name">Overlong UTF-8 Dot</span>
          <code class="vector-payload">%c0%ae</code>
          <span class="vector-desc">2-byte overlong encoding of '.'</span>
        </div>
        <div class="vector-item">
          <span class="vector-name">Fullwidth Dot</span>
          <code class="vector-payload">%ef%bc%8e</code>
          <span class="vector-desc">Fullwidth full stop '．' (U+FF0E)</span>
        </div>
        <div class="vector-item">
          <span class="vector-name">Fullwidth Slash</span>
          <code class="vector-payload">%ef%bc%8f</code>
          <span class="vector-desc">Fullwidth solidus '／' (U+FF0F)</span>
        </div>
        <div class="vector-item">
          <span class="vector-name">3-byte Overlong Dot</span>
          <code class="vector-payload">%e0%80%ae</code>
          <span class="vector-desc">3-byte overlong encoding of '.'</span>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /PATH-TRAV-CASE4.php?file=<?php echo urlencode($filename); ?></code>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-bug"></i>
        <span>Vulnerable Code Snippet</span>
      </div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Unicode normalization before check
$filename = $_GET['file'];

// NFKC normalization can convert Unicode to ASCII!
$normalized = normalizer_normalize($filename, Normalizer::FORM_KC);

// Check AFTER normalization - too late!
if (strpos($normalized, '../') !== false) {
    die("Traversal detected!");
}

// But the original $filename still contains the payload!
$file_path = 'files/' . $filename;</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> NFKC normalization converts Unicode characters to their canonical
            equivalents. <code>%c0%ae</code> (overlong '.') becomes <code>.</code> after normalization. The check runs
            on normalized input but the file operation uses original input - or vice versa!</span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The application uses NFKC normalization. Overlong UTF-8 sequences like <code>%c0%ae</code>
          normalize to <code>.</code> (dot). Try constructing traversal using these sequences:
          <code>?file=%c0%ae%c0%ae%2fetc%2fpasswd</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Fullwidth characters also normalize! <code>%ef%bc%8e</code> (fullwidth dot) becomes
          <code>.</code> and <code>%ef%bc%8f</code> (fullwidth slash) becomes <code>/</code>. Try
          <code>?file=%ef%bc%8e%ef%bc%8e%ef%bc%8fetc%ef%bc%8fpasswd</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?file=%c0%ae%c0%ae%2fetc%2fpasswd</code> (overlong UTF-8 dots with regular slash)
          or <code>?file=%ef%bc%8e%ef%bc%8e%ef%bc%8fetc%ef%bc%8fpasswd</code> (fullwidth characters). Both bypass NFKC
          normalization!</div>
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
    <input type="hidden" name="file" value="<?php echo htmlspecialchars($filename); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const file = urlParams.get('file') || '';

      const unicodePatterns = [
        '%c0%ae', '%c0%AE',
        '%ef%bc%8e', '%EF%BC%8E',
        '%ef%bc%8f', '%EF%BC%8F',
        '%ef%bc%bc', '%EF%BC%BC',
        '%e0%80%ae', '%E0%80%AE',
        '%f0%80%80%ae', '%F0%80%80%AE'
      ];

      const hasUnicode = unicodePatterns.some(pattern =>
        file.toLowerCase().includes(pattern.toLowerCase())
      );

      const hasEncoded = /%[c-fC-F][0-9a-fA-F]|%ef|%e0|%f0/i.test(file);

      if ((hasUnicode || hasEncoded) && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>