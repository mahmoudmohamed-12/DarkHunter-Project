<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case2_attempts'])) {
  $_SESSION['cache_case2_attempts'] = 0;
}
if (!isset($_SESSION['cache_case2_solved'])) {
  $_SESSION['cache_case2_solved'] = false;
}

// ─── Vulnerable Query String Cache Logic ───────────────────────────────────
$cache_poisoned = false;
$flag_triggered = false;
$detected_param = null;
$cache_status = 'MISS';

// VULNERABLE: Cache includes ALL query parameters in key
$query_string = $_SERVER['QUERY_STRING'] ?? '';
$cache_key = md5($_SERVER['REQUEST_URI']);
$cache_file = sys_get_temp_dir() . '/cache_qs_' . $cache_key . '.html';

// Check cache
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
  $cache_status = 'HIT';
  $cached_content = file_get_contents($cache_file);
  if (
    strpos($cached_content, 'POISONED') !== false ||
    strpos($cached_content, 'XSS') !== false
  ) {
    $cache_poisoned = true;
  }
} else {
  $cache_status = 'MISS';
}

// VULNERABLE: Reflected parameters without sanitization
$reflected_params = ['search', 'callback', 'redirect', 'next', 'return'];
foreach ($reflected_params as $param) {
  if (isset($_GET[$param])) {
    $value = $_GET[$param];

    // Check for poison patterns
    if (
      stripos($value, '<script>') !== false ||
      stripos($value, 'javascript:') !== false ||
      stripos($value, 'POISONED') !== false ||
      stripos($value, 'alert(') !== false
    ) {
      $cache_poisoned = true;
      $detected_param = $param;
      $flag_triggered = true;

      // VULNERABLE: Cache the poisoned response with reflected param
      $poisoned_content = "<!-- POISONED via query parameter: $param -->\n";
      $poisoned_content .= "<div class='reflected-param'>Value: " . $value . "</div>\n";
      file_put_contents($cache_file, $poisoned_content);
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case2_solved'] = true;
  $already_solved = true;
  $success_msg = "Great job! You've successfully exploited a Query String Cache Poisoning vulnerability. By injecting malicious payloads into reflected query parameters, you poisoned the cache and caused XSS to be served to all users!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if (!empty($_GET)) {
  $_SESSION['cache_case2_attempts']++;
}

$attempts = $_SESSION['cache_case2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Portal - CachePoison Case 2 (Query String)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-search"></i> Search Portal</h1>
      <p class="lab-description">Search our knowledge base. This easy Cache Poisoning challenge has
        <strong>reflected query parameters</strong> cached without sanitization. <strong>Poison the cache with XSS via
          URL parameters!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Query String Cache Poisoning vulnerability.</p>
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

    <!-- Cache Status -->
    <div class="cache-status-bar">
      <div class="cache-indicator <?php echo $cache_status === 'HIT' ? 'hit' : 'miss'; ?>">
        <i class="fas fa-<?php echo $cache_status === 'HIT' ? 'database' : 'cloud'; ?>"></i>
        <span>Cache <?php echo $cache_status; ?></span>
      </div>
      <?php if ($cache_poisoned): ?>
        <div class="poison-indicator">
          <i class="fas fa-skull-crossbones"></i>
          <span>CACHE POISONED!</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Search Form -->
    <div class="search-section">
      <form method="GET" action="" class="search-form">
        <div class="search-input-group">
          <i class="fas fa-search search-icon"></i>
          <input type="text" name="search" placeholder="Search articles..." class="search-input"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          <button type="submit" class="search-btn"><i class="fas fa-arrow-right"></i></button>
        </div>
      </form>

      <?php if (isset($_GET['search'])): ?>
        <div class="search-results">
          <div class="result-header">
            <i class="fas fa-file-alt"></i>
            <span>Results for: <code class="search-term"><?php echo $_GET['search']; ?></code></span>
          </div>
          <div class="result-item">
            <h4><i class="fas fa-book"></i> Article: Introduction to Cache Poisoning</h4>
            <p>Cache poisoning is a type of web attack where an attacker causes a web cache to store malicious content...
            </p>
          </div>
          <div class="result-item">
            <h4><i class="fas fa-shield-alt"></i> Article: Defending Against Cache Attacks</h4>
            <p>Learn how to properly configure cache keys and validate input to prevent cache poisoning attacks...</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Parameter Analysis -->
      <div class="cache-card params-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Reflected Parameters</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Cached & Reflected</span>
        </div>
        <div class="params-content">
          <?php foreach ($reflected_params as $param): ?>
            <div class="param-item <?php echo isset($_GET[$param]) ? 'active' : ''; ?>">
              <span class="param-name">?<?php echo $param; ?>=</span>
              <code class="param-example">value</code>
              <span class="param-status"><?php echo isset($_GET[$param]) ? 'REFLECTED' : 'Not Set'; ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Current Query -->
      <div class="cache-card query-card">
        <div class="card-header">
          <i class="fas fa-link"></i>
          <h3>Current Query String</h3>
        </div>
        <div class="query-content">
          <code class="query-string"><?php echo htmlspecialchars($query_string); ?></code>
          <div class="query-parsed">
            <?php foreach ($_GET as $key => $value): ?>
              <div class="query-param">
                <span class="query-key"><?php echo htmlspecialchars($key); ?>:</span>
                <code class="query-value"><?php echo htmlspecialchars($value); ?></code>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Cache Analysis -->
      <div class="cache-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Cache Key Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Cache Key:</span>
            <code class="analysis-code">md5("<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>")</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Includes ALL params</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Sanitization:</span>
            <code class="analysis-code">// None - raw reflection</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Approach:</span>
            <code class="analysis-code">htmlspecialchars($param) + strict key</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Required</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Query Poison Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">XSS via Search</div>
            <code class="payload-code">?search=&lt;script&gt;alert(1)&lt;/script&gt;</code>
            <span class="payload-target">Reflected in search results</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Redirect Poison</div>
            <code class="payload-code">?redirect=javascript:alert(1)</code>
            <span class="payload-target">Cached redirect payload</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Callback Hijack</div>
            <code class="payload-code">?callback=alert(1)</code>
            <span class="payload-target">JSONP-style abuse</span>
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
          <span>Cache Key: <?php echo substr($cache_key, 0, 16); ?>...</span>
          <span>Cache Status: <?php echo $cache_status; ?></span>
          <span>Poisoned: <?php echo $cache_poisoned ? 'YES' : 'NO'; ?></span>
          <span>Detected Param: <?php echo $detected_param ? htmlspecialchars($detected_param) : 'NONE'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The search parameter is reflected directly in the page without sanitization. Try adding
          <code>?search=&lt;script&gt;alert(1)&lt;/script&gt;</code> to the URL. The cache will store this XSS payload!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Any query parameter is included in the cache key and reflected raw. Try:
          <code>?search=&lt;script&gt;POISONED&lt;/script&gt;</code> or
          <code>?callback=alert(document.cookie)</code>. The response gets cached and served to everyone!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Simply add <code>?search=&lt;script&gt;alert('POISONED')&lt;/script&gt;</code> to the URL
          and press Enter. The page will reflect your input unsanitized, and the cache will store this malicious response
          for all future visitors!</div>
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