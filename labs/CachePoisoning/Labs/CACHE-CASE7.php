<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case7_attempts'])) {
  $_SESSION['cache_case7_attempts'] = 0;
}
if (!isset($_SESSION['cache_case7_solved'])) {
  $_SESSION['cache_case7_solved'] = false;
}

// ─── Vulnerable Web Cache Deception Logic ─────────────────────────────────
$deception_detected = false;
$flag_triggered = false;
$fake_extension = null;
$cache_status = 'MISS';

// VULNERABLE: Cache based on URL pattern, ignores auth
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH) ?? $uri;

// VULNERABLE: Static-looking paths are cached even with dynamic content
$cache_extensions = ['.css', '.js', '.jpg', '.png', '.gif', '.woff', '.svg'];
$should_cache = false;

foreach ($cache_extensions as $ext) {
  if (stripos($path, $ext) !== false) {
    $should_cache = true;
    $fake_extension = $ext;
    break;
  }
}

// Also cache paths with "static", "assets", "cache"
if (
  stripos($path, 'static') !== false ||
  stripos($path, 'assets') !== false ||
  stripos($path, 'cache') !== false
) {
  $should_cache = true;
}

$cache_key = md5($path);
$cache_file = sys_get_temp_dir() . '/cache_deception_' . $cache_key . '.html';

// Check cache
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
  $cache_status = 'HIT';
  $cached_content = file_get_contents($cache_file);
  if (
    strpos($cached_content, 'DECEPTION') !== false ||
    strpos($cached_content, 'PRIVATE') !== false
  ) {
    $deception_detected = true;
  }
} else {
  $cache_status = 'MISS';
}

// VULNERABLE: Private data cached due to fake extension
if ($should_cache) {
  // Simulate private user data
  $user_data = [
    'username' => 'admin',
    'email' => 'admin@darkhunter.local',
    'role' => 'superuser',
    'api_key' => 'YOUR_BREVO_API_KEY',
    'ssn' => '123-45-6789',
    'credit_card' => '4532-1234-5678-9012'
  ];

  // Check for deception trigger
  if (isset($_GET['deceive']) || isset($_GET['poison'])) {
    $deception_detected = true;
    $flag_triggered = true;

    // VULNERABLE: Cache private data as static resource
    $poisoned_content = "/* DECEPTION: Private data cached as $fake_extension */\n";
    $poisoned_content .= json_encode($user_data);
    file_put_contents($cache_file, $poisoned_content);
  }

  // Also trigger on specific path patterns
  if (preg_match('/\.css\?.+/', $path) || preg_match('/\.js\?.+/', $path)) {
    if (isset($_GET['deceive']) || isset($_GET['bypass'])) {
      $deception_detected = true;
      $flag_triggered = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case7_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case7_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case7_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Web Cache Deception vulnerability. By appending fake extensions or paths to force caching, you tricked the cache into storing private authenticated content that is now accessible to everyone!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if (!empty($_GET)) {
  $_SESSION['cache_case7_attempts']++;
}

$attempts = $_SESSION['cache_case7_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - CachePoison Case 7 (Web Cache Deception)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-theater-masks"></i> User Dashboard</h1>
      <p class="lab-description">View your private account data. This hard Cache Poisoning challenge has
        <strong>web cache deception</strong>. Fake extensions trick the cache into storing private content.
        <strong>Deceive the cache to expose private data!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Web Cache Deception vulnerability.</p>
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

    <!-- Deception Status -->
    <div class="deception-status-bar">
      <div class="path-indicator">
        <i class="fas fa-route"></i>
        <span>Path: <code><?php echo htmlspecialchars($path); ?></code></span>
      </div>
      <div class="cache-indicator <?php echo $cache_status === 'HIT' ? 'hit' : 'miss'; ?>">
        <i class="fas fa-<?php echo $cache_status === 'HIT' ? 'database' : 'cloud'; ?>"></i>
        <span>Cache <?php echo $cache_status; ?></span>
      </div>
      <?php if ($deception_detected): ?>
      <div class="poison-indicator">
        <i class="fas fa-skull-crossbones"></i>
        <span>DECEPTION DETECTED!</span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Private Data Display -->
    <div class="private-data-section">
      <div class="data-header">
        <i class="fas fa-user-shield"></i>
        <h3>Private Account Data</h3>
        <span class="private-badge"><i class="fas fa-lock"></i> Confidential</span>
      </div>
      <div class="data-grid">
        <div class="data-item">
          <span class="data-label">Username</span>
          <code class="data-value">admin</code>
        </div>
        <div class="data-item">
          <span class="data-label">Email</span>
          <code class="data-value">admin@darkhunter.local</code>
        </div>
        <div class="data-item">
          <span class="data-label">Role</span>
          <code class="data-value danger">superuser</code>
        </div>
        <div class="data-item">
          <span class="data-label">API Key</span>
          <code class="data-value secret">'YOUR_BREVO_API_KEY'</code>
        </div>
        <div class="data-item">
          <span class="data-label">SSN</span>
          <code class="data-value secret">123-45-6789</code>
        </div>
        <div class="data-item">
          <span class="data-label">Credit Card</span>
          <code class="data-value secret">4532-1234-5678-9012</code>
        </div>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Deception Tester -->
      <div class="cache-card tester-card">
        <div class="card-header">
          <i class="fas fa-vial"></i>
          <h3>Deception Tester</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Extension Bypass</span>
        </div>

        <div class="tester-content">
          <p class="tester-info">Trick the cache with fake extensions:</p>

          <div class="deception-examples">
            <div class="deception-item">
              <span class="deception-label">CSS Deception:</span>
              <code class="deception-code">/dashboard.css?deceive=1</code>
            </div>
            <div class="deception-item">
              <span class="deception-label">JS Deception:</span>
              <code class="deception-code">/profile.js?bypass=1</code>
            </div>
            <div class="deception-item">
              <span class="deception-label">Image Deception:</span>
              <code class="deception-code">/data.jpg?poison=1</code>
            </div>
            <div class="deception-item">
              <span class="deception-label">Static Path:</span>
              <code class="deception-code">/static/private?deceive=1</code>
            </div>
          </div>
        </div>
      </div>

      <!-- Cache Rules -->
      <div class="cache-card rules-card">
        <div class="card-header">
          <i class="fas fa-list-check"></i>
          <h3>Cache Rules</h3>
        </div>
        <div class="rules-content">
          <div class="rule-item cached">
            <i class="fas fa-check-circle"></i>
            <span>*.css, *.js, *.jpg, *.png</span>
            <span class="rule-status">CACHED</span>
          </div>
          <div class="rule-item cached">
            <i class="fas fa-check-circle"></i>
            <span>/static/*, /assets/*, /cache/*</span>
            <span class="rule-status">CACHED</span>
          </div>
          <div class="rule-item not-cached">
            <i class="fas fa-times-circle"></i>
            <span>/dashboard, /profile, /settings</span>
            <span class="rule-status">NOT CACHED</span>
          </div>
          <div class="rule-item vuln">
            <i class="fas fa-exclamation-triangle"></i>
            <span>No auth check before cache!</span>
            <span class="rule-status">VULNERABLE</span>
          </div>
        </div>
      </div>

      <!-- Deception Analysis -->
      <div class="cache-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Deception Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Extension Check:</span>
            <code class="analysis-code">if (path matches *.css, *.js) cache</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Weak</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Auth Verification:</span>
            <code class="analysis-code">// No auth before caching!</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Content-Type Check:</span>
            <code class="analysis-code">// Ignores actual content</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Bypassed</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Deception Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">CSS Extension Trick</div>
            <code class="payload-code">/private/data.css?deceive=1</code>
            <span class="payload-target">Private data cached as CSS</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Static Path Abuse</div>
            <code class="payload-code">/static/../admin/profile</code>
            <span class="payload-target">Path traversal + cache</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Query String Bypass</div>
            <code class="payload-code">/user.jpg?real_path=/admin</code>
            <span class="payload-target">Fake image, real data</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Path: <?php echo htmlspecialchars($path); ?></span>
          <span>Should Cache: <?php echo $should_cache ? 'YES' : 'NO'; ?></span>
          <span>Extension: <?php echo $fake_extension ? htmlspecialchars($fake_extension) : 'NONE'; ?></span>
          <span>Cache Status: <?php echo $cache_status; ?></span>
          <span>Deceived: <?php echo $deception_detected ? 'YES' : 'NO'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The cache stores any URL ending in .css, .js, .jpg, etc. Try adding
        <code>.css?deceive=1</code> to the current URL. The cache will store the private dashboard content as if it
        were a CSS file!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try visiting <code>CURRENT_URL.css?deceive=1</code> or
        <code>CURRENT_URL.js?bypass=1</code>. The server sees the extension and caches the response, even though it
        contains private authenticated data!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Add <code>.css?deceive=1</code> to the end of the current URL path. For example:
        <code>/labs/CACHE-CASE7.php.css?deceive=1</code>. The cache sees .css and stores the private data, making it
        accessible to unauthenticated users!
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
  </form>

  <script>
  window.addEventListener('load', function() {
    const poisonIndicator = document.querySelector('.poison-indicator');
    const alreadySolved = document.querySelector('.solved-banner');

    if (poisonIndicator && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>