<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case1_attempts'])) {
  $_SESSION['cache_case1_attempts'] = 0;
}
if (!isset($_SESSION['cache_case1_solved'])) {
  $_SESSION['cache_case1_solved'] = false;
}

// ─── Vulnerable Cache Logic ──────────────────────────────────────────────
$cache_poisoned = false;
$flag_triggered = false;
$detected_header = null;
$cache_status = 'MISS';

// VULNERABLE: Cache keys only on URL, ignores headers
$cache_key = md5($_SERVER['REQUEST_URI']);
$cache_file = sys_get_temp_dir() . '/cache_' . $cache_key . '.html';

// Check if we have a cached response
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
  $cache_status = 'HIT';
  $cached_content = file_get_contents($cache_file);

  // Check if cache was poisoned
  if (
    strpos($cached_content, 'POISONED') !== false ||
    strpos($cached_content, 'XSS') !== false ||
    strpos($cached_content, 'MALICIOUS') !== false
  ) {
    $cache_poisoned = true;
  }
} else {
  $cache_status = 'MISS';
}

// Process headers that can poison cache
$headers = getallheaders();
$poison_headers = ['X-Forwarded-Host', 'X-HTTP-Host-Override', 'User-Agent'];

foreach ($poison_headers as $header) {
  if (isset($headers[$header])) {
    $header_value = $headers[$header];

    // Check for poison patterns
    if (
      stripos($header_value, '<script>') !== false ||
      stripos($header_value, 'javascript:') !== false ||
      stripos($header_value, 'POISONED') !== false ||
      stripos($header_value, 'MALICIOUS') !== false
    ) {
      $cache_poisoned = true;
      $detected_header = $header;
      $flag_triggered = true;

      // VULNERABLE: Cache the poisoned response
      $poisoned_content = "<!-- POISONED via $header -->\n";
      $poisoned_content .= "<div class='poison-indicator'>Cache Poisoned via: $header</div>\n";
      file_put_contents($cache_file, $poisoned_content);
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Basic Header Cache Poisoning vulnerability. By manipulating unkeyed headers like X-Forwarded-Host, you poisoned the cache and caused malicious content to be served to all users!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && count(array_intersect_key(array_flip($poison_headers), $headers)) > 0) {
  $_SESSION['cache_case1_attempts']++;
}

$attempts = $_SESSION['cache_case1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cache Test - CachePoison Case 1 (Basic Header)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-flask"></i> Cache Header Tester</h1>
      <p class="lab-description">Test how our CDN caches responses. This easy Cache Poisoning challenge uses
        <strong>unkeyed headers</strong> in cache keys. <strong>Poison the cache with malicious headers!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Basic Header Cache Poisoning vulnerability.</p>
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

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Header Tester -->
      <div class="cache-card tester-card">
        <div class="card-header">
          <i class="fas fa-vial"></i>
          <h3>Header Injection</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Unkeyed Headers</span>
        </div>

        <div class="tester-content">
          <p class="tester-info">Send requests with malicious headers using curl or Burp Suite:</p>

          <div class="curl-examples">
            <div class="curl-item">
              <span class="curl-label">X-Forwarded-Host Poison:</span>
              <code
                class="curl-code">curl -H "X-Forwarded-Host: evil.com<script>
                  alert(1)
                </script>" <?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?></code>
            </div>
            <div class="curl-item">
              <span class="curl-label">User-Agent Poison:</span>
              <code
                class="curl-code">curl -H "User-Agent: <script>
                  POISONED
                </script>" <?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?></code>
            </div>
            <div class="curl-item">
              <span class="curl-label">X-HTTP-Host-Override:</span>
              <code
                class="curl-code">curl -H "X-HTTP-Host-Override: MALICIOUS" <?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?></code>
            </div>
          </div>
        </div>
      </div>

      <!-- Detected Headers -->
      <div class="cache-card headers-card">
        <div class="card-header">
          <i class="fas fa-headers"></i>
          <h3>Your Headers</h3>
        </div>
        <div class="headers-content">
          <?php foreach ($headers as $name => $value): ?>
            <div class="header-item <?php echo in_array($name, $poison_headers) ? 'unkeyed' : ''; ?>">
              <span class="header-name"><?php echo htmlspecialchars($name); ?></span>
              <code class="header-value"><?php echo htmlspecialchars($value); ?></code>
              <?php if (in_array($name, $poison_headers)): ?>
                <span class="header-badge">UNKEYED</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
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
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Only URL</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Headers Ignored:</span>
            <code class="analysis-code">X-Forwarded-Host, User-Agent, X-HTTP-Host-Override</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Unkeyed</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Key:</span>
            <code class="analysis-code">md5(URL + Host + UA + Session)</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Proper</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Poison Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Basic XSS via Header</div>
            <code class="payload-code">X-Forwarded-Host: evil.com&lt;script&gt;alert(1)&lt;/script&gt;</code>
            <span class="payload-target">Reflected in cached page</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Cache Key Poison</div>
            <code class="payload-code">User-Agent: &lt;script&gt;POISONED&lt;/script&gt;</code>
            <span class="payload-target">Stored in cache for all users</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Host Override</div>
            <code class="payload-code">X-HTTP-Host-Override: attacker.com</code>
            <span class="payload-target">Redirects to malicious domain</span>
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
          <span>Detected Header: <?php echo $detected_header ? htmlspecialchars($detected_header) : 'NONE'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The cache only uses the URL as the cache key. Headers like <code>X-Forwarded-Host</code>
          and <code>User-Agent</code> are NOT included in the key. Try sending a request with a malicious header value!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Use curl or Burp Suite to add the header: <code>X-Forwarded-Host:
            evil.com&lt;script&gt;alert(1)&lt;/script&gt;</code>. The cache will store this poisoned response and serve
          it to all users!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Send a request with: <code>User-Agent: &lt;script&gt;POISONED&lt;/script&gt;</code> or
          <code>X-Forwarded-Host: MALICIOUS</code>. The server will cache this response without validating the header,
          poisoning the cache for all subsequent users!
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