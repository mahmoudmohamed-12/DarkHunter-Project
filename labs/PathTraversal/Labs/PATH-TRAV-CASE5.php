<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_medium3_attempts'])) {
  $_SESSION['pt_medium3_attempts'] = 0;
}
if (!isset($_SESSION['pt_medium3_solved'])) {
  $_SESSION['pt_medium3_solved'] = false;
}

// ─── Simulated Application: DarkHunter Text Viewer ──────────────────────
$texts = [
  'readme'    => ['title' => 'README', 'content' => 'Welcome to DarkHunter Text Viewer. All files are stored as .txt for security.'],
  'changelog' => ['title' => 'Changelog', 'content' => 'v1.0 - Initial release with forced .txt extension.'],
  'license'   => ['title' => 'License', 'content' => 'MIT License - DarkHunter Text Viewer.'],
  'todo'      => ['title' => 'TODO', 'content' => '- Add more security features\n- Fix path validation\n- Update documentation'],
];

// ─── Vulnerable Logic: Appends .txt but vulnerable to null byte ────────
$name = isset($_GET['name']) ? $_GET['name'] : 'readme';

// "Security": Check for traversal before appending extension
$is_blocked = false;
$block_reason = '';

if (preg_match('/\.\.+/', $name)) {
  $is_blocked = true;
  $block_reason = "Path traversal sequences are not allowed!";
}

// Append .txt extension (vulnerable to null byte truncation in PHP < 5.3.4)
$file_path = 'texts/' . $name . '.txt';

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_medium3_solved'];
$exploited = false;

// Detect null byte exploitation
$null_indicators = [
  '%00',
  '%2500',
  '\x00',
  '\0',
  '.txt%00',
  '.php%00',
  '.jpg%00',
  'readme%00',
  'config%00',
  'db%00',
];

foreach ($null_indicators as $indicator) {
  if (stripos($name, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check if name contains null byte patterns that would truncate
if (preg_match('/%00/', $name) || preg_match('/%2500/', $name)) {
  $exploited = true;
}

// Also check for system file access attempts after truncation
$system_indicators = [
  'etc/passwd',
  'windows/win.ini',
  'boot.ini',
  'Config/db',
  'includes/score',
  '.env',
  'proc/self',
  'var/log',
  'var/www',
];
foreach ($system_indicators as $indicator) {
  if (stripos($name, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_medium3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_medium3_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited a Null Byte Injection vulnerability to bypass forced file extensions. By injecting %00 (null byte) into the filename, you truncated the appended .txt extension, allowing access to arbitrary files. This classic vulnerability affects PHP versions before 5.3.4!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['name'])) {
  $_SESSION['pt_medium3_attempts']++;
}

$attempts = $_SESSION['pt_medium3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Text Viewer - Path Traversal Medium 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-file-alt"></i> DarkHunter Text Viewer</h1>
      <p class="lab-description">A text file viewer that <strong>forces a .txt extension</strong> on all files. The
        application checks for traversal then appends .txt. Can you use null byte injection to truncate the extension
        and read arbitrary files?</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this null byte truncation. You can continue exploring, but no additional points
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

    <!-- Text Viewer Interface -->
    <div class="textviewer-card">
      <div class="tv-header">
        <div class="tv-brand">
          <i class="fas fa-align-left"></i>
          <span>Text Viewer</span>
        </div>
        <div class="tv-badge">
          <i class="fas fa-lock"></i>
          <span>.txt Enforced</span>
        </div>
      </div>

      <div class="tv-body">
        <!-- Extension Analysis -->
        <div class="ext-panel">
          <div class="ext-header">
            <i class="fas fa-cogs"></i>
            <span>File Path Construction</span>
          </div>
          <div class="ext-flow">
            <div class="ext-step">
              <span class="ext-label">Input:</span>
              <code class="ext-value"><?php echo htmlspecialchars($name); ?></code>
            </div>
            <div class="ext-plus"><i class="fas fa-plus"></i></div>
            <div class="ext-step">
              <span class="ext-label">Extension:</span>
              <code class="ext-value">.txt</code>
            </div>
            <div class="ext-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="ext-step">
              <span class="ext-label">Result:</span>
              <code class="ext-value result">texts/<?php echo htmlspecialchars($name); ?>.txt</code>
            </div>
          </div>
          <div class="ext-note">
            <i class="fas fa-info-circle"></i>
            <span>The application always appends <code>.txt</code> to prevent accessing non-text files.</span>
          </div>
        </div>

        <!-- Navigation -->
        <div class="tv-nav">
          <?php foreach ($texts as $key => $info): ?>
          <a href="?name=<?php echo $key; ?>" class="tv-nav-item <?php echo $name === $key ? 'active' : ''; ?>">
            <i class="fas fa-file-alt"></i>
            <span><?php echo $info['title']; ?></span>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- Content -->
        <div class="tv-content">
          <div class="content-header">
            <i class="fas fa-file-alt"></i>
            <span><?php echo isset($texts[$name]) ? $texts[$name]['title'] : 'Custom File'; ?></span>
          </div>
          <div class="content-body">
            <?php if (isset($texts[$name])): ?>
            <pre class="content-text"><?php echo htmlspecialchars($texts[$name]['content']); ?></pre>
            <?php elseif ($exploited && !$is_blocked): ?>
            <div class="content-exploit">
              <i class="fas fa-bug fa-2x"></i>
              <h3>Null Byte Truncation Successful!</h3>
              <p>The null byte truncated the forced .txt extension.</p>
              <code
                class="exploit-path">Effective path: texts/<?php echo htmlspecialchars(preg_replace('/%00.*/', '', $name)); ?></code>
            </div>
            <?php elseif ($is_blocked): ?>
            <div class="content-blocked">
              <i class="fas fa-ban fa-2x"></i>
              <h3>Access Blocked</h3>
              <p><?php echo htmlspecialchars($block_reason); ?></p>
            </div>
            <?php else: ?>
            <div class="content-unknown">
              <i class="fas fa-question-circle fa-2x"></i>
              <h3>File Not Found</h3>
              <p>texts/<?php echo htmlspecialchars($name); ?>.txt</p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Null Byte Explanation -->
    <div class="nullbyte-panel">
      <div class="nullbyte-header">
        <i class="fas fa-graduation-cap"></i>
        <span>How Null Byte Truncation Works</span>
      </div>
      <div class="nullbyte-body">
        <div class="nullbyte-step">
          <div class="step-num">1</div>
          <div class="step-content">
            <span class="step-title">Input with Null Byte</span>
            <code>name=../../../etc/passwd%00</code>
          </div>
        </div>
        <div class="nullbyte-arrow"><i class="fas fa-arrow-down"></i></div>
        <div class="nullbyte-step">
          <div class="step-num">2</div>
          <div class="step-content">
            <span class="step-title">PHP String Processing</span>
            <code>"texts/" + "../../../etc/passwd%00" + ".txt"</code>
          </div>
        </div>
        <div class="nullbyte-arrow"><i class="fas fa-arrow-down"></i></div>
        <div class="nullbyte-step">
          <div class="step-num">3</div>
          <div class="step-content">
            <span class="step-title">C-String Truncation</span>
            <code>texts/../../../etc/passwd [NULL terminates string]</code>
            <span class="step-note">.txt is ignored!</span>
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
        <code>GET /PATH-TRAV-CASE5.php?name=<?php echo urlencode($name); ?></code>
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
          <pre>// VULNERABLE: Null byte truncates extension
$name = $_GET['name'];

// Check for traversal (but not null bytes!)
if (preg_match('/\.\.+/', $name)) {
    die("Traversal blocked!");
}

// Force .txt extension... but null byte truncates!
$file_path = 'texts/' . $name . '.txt';
// If $name = "../../../etc/passwd%00"
// Result: "texts/../../../etc/passwd" [NULL] ".txt"
// C sees: "texts/../../../etc/passwd" (stops at NULL)

$content = file_get_contents($file_path);</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> PHP uses C-style strings internally. A null byte (%00) terminates the
            string, so <code>file.txt%00.php</code> becomes <code>file.txt</code>. This bypasses extension checks and
            allows arbitrary file access.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The application appends <code>.txt</code> to every filename. In PHP, the null byte
        <code>%00</code> terminates strings. Try <code>?name=../../../etc/passwd%00</code> - the .txt will be ignored!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">You can also use null byte to access PHP source files:
        <code>?name=../../Config/db%00</code> or <code>?name=../index%00</code>. The null byte truncates before .txt is
        appended!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?name=../../../etc/passwd%00</code> to read the password file. The %00 null byte
        truncates the string, so the application reads <code>../../../etc/passwd</code> instead of
        <code>../../../etc/passwd.txt</code>!
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
    <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const name = urlParams.get('name') || '';

    const nullPatterns = [
      '%00', '%2500', '\\x00', '\\0',
      '.txt%00', '.php%00', '.jpg%00',
      'readme%00', 'config%00', 'db%00'
    ];

    const hasNull = nullPatterns.some(pattern =>
      name.toLowerCase().includes(pattern.toLowerCase())
    );

    const hasNullByte = /%00/.test(name) || /%2500/.test(name);

    if ((hasNull || hasNullByte) && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>