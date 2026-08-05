<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case4_attempts'])) {
  $_SESSION['cache_case4_attempts'] = 0;
}
if (!isset($_SESSION['cache_case4_solved'])) {
  $_SESSION['cache_case4_solved'] = false;
}

// ─── Vulnerable HTTP Method Cache Logic ────────────────────────────────────
$method_confused = false;
$flag_triggered = false;
$detected_method = null;
$cache_status = 'MISS';

// VULNERABLE: POST/PUT responses can be cached as GET
$method = $_SERVER['REQUEST_METHOD'];
$cache_key = md5($_SERVER['REQUEST_URI'] . '_GET'); // Always keys as GET
$cache_file = sys_get_temp_dir() . '/cache_method_' . $cache_key . '.html';

// Check cache
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
  $cache_status = 'HIT';
  $cached_content = file_get_contents($cache_file);
  if (
    strpos($cached_content, 'POISONED') !== false ||
    strpos($cached_content, 'METHOD_CONFUSION') !== false
  ) {
    $method_confused = true;
  }
} else {
  $cache_status = 'MISS';
}

// VULNERABLE: Non-GET methods can poison GET cache
if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
  $input = file_get_contents('php://input');
  parse_str($input, $body_params);

  // Check for poison in body
  $body_str = json_encode($body_params) . $input;
  if (
    stripos($body_str, 'POISONED') !== false ||
    stripos($body_str, 'METHOD_CONFUSION') !== false ||
    stripos($body_str, '<script>') !== false
  ) {
    $method_confused = true;
    $detected_method = $method;
    $flag_triggered = true;

    // VULNERABLE: Cache POST response as GET
    $poisoned_content = "<!-- METHOD_CONFUSION: $method response cached as GET -->\n";
    $poisoned_content .= "<div class='method-alert'>Poisoned via $method body</div>\n";
    file_put_contents($cache_file, $poisoned_content);
  }
}

// Also check query params for GET poisoning
if ($method === 'GET') {
  foreach ($_GET as $key => $value) {
    if (stripos($value, 'METHOD_CONFUSION') !== false || stripos($value, 'POISONED') !== false) {
      $method_confused = true;
      $flag_triggered = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case4_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case4_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case4_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited an HTTP Method Cache Confusion vulnerability. By sending a POST/PUT request with a malicious body, you poisoned the GET cache. All subsequent GET requests now receive your poisoned response!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
$_SESSION['cache_case4_attempts']++;

$attempts = $_SESSION['cache_case4_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Endpoint - CachePoison Case 4 (HTTP Method)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-satellite-dish"></i> API Endpoint</h1>
      <p class="lab-description">Test our REST API endpoints. This medium Cache Poisoning challenge has
        <strong>method-based cache confusion</strong>. POST/PUT responses are cached as GET responses!
        <strong>Poison the cache with the wrong HTTP method!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this HTTP Method Cache Confusion vulnerability.</p>
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

    <!-- Method Status -->
    <div class="method-status-bar">
      <div class="method-badge method-<?php echo strtolower($method); ?>">
        <i class="fas fa-<?php echo $method === 'GET' ? 'arrow-down' : 'arrow-up'; ?>"></i>
        <span><?php echo $method; ?></span>
      </div>
      <div class="cache-indicator <?php echo $cache_status === 'HIT' ? 'hit' : 'miss'; ?>">
        <i class="fas fa-<?php echo $cache_status === 'HIT' ? 'database' : 'cloud'; ?>"></i>
        <span>Cache <?php echo $cache_status; ?></span>
      </div>
      <?php if ($method_confused): ?>
      <div class="poison-indicator">
        <i class="fas fa-skull-crossbones"></i>
        <span>METHOD CONFUSED!</span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Method Tester -->
      <div class="cache-card tester-card">
        <div class="card-header">
          <i class="fas fa-vial"></i>
          <h3>Method Tester</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Method Confusion</span>
        </div>

        <div class="tester-content">
          <p class="tester-info">Send requests with different methods using curl:</p>

          <div class="curl-examples">
            <div class="curl-item">
              <span class="curl-label">GET (Normal):</span>
              <code
                class="curl-code">curl -X GET "<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"</code>
            </div>
            <div class="curl-item">
              <span class="curl-label">POST Poison:</span>
              <code
                class="curl-code">curl -X POST -d "data=POISONED" "<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>"</code>
            </div>
            <div class="curl-item">
              <span class="curl-label">PUT Poison:</span>
              <code
                class="curl-code">curl -X PUT -d "content=<script>
                  METHOD_CONFUSION
                </script>" "<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>"</code>
            </div>
          </div>
        </div>
      </div>

      <!-- Vary Header Analysis -->
      <div class="cache-card vary-card">
        <div class="card-header">
          <i class="fas fa-random"></i>
          <h3>Vary Header Analysis</h3>
        </div>
        <div class="vary-content">
          <div class="vary-item">
            <span class="vary-label">Current Vary:</span>
            <code class="vary-value">Vary: Accept-Encoding</code>
            <span class="vary-status vuln"><i class="fas fa-times-circle"></i> Missing Method</span>
          </div>
          <div class="vary-item">
            <span class="vary-label">Secure Vary:</span>
            <code class="vary-value">Vary: Accept-Encoding, Origin, Access-Control-Request-Method</code>
            <span class="vary-status safe"><i class="fas fa-check-circle"></i> Proper</span>
          </div>
          <div class="vary-item">
            <span class="vary-label">Cache Behavior:</span>
            <code class="vary-value">POST body cached as GET response</code>
            <span class="vary-status vuln"><i class="fas fa-times-circle"></i> Confused</span>
          </div>
        </div>
      </div>

      <!-- Method Matrix -->
      <div class="cache-card matrix-card">
        <div class="card-header">
          <i class="fas fa-table"></i>
          <h3>Method Cache Matrix</h3>
        </div>
        <div class="matrix-content">
          <div class="matrix-row header-row">
            <span class="matrix-cell">Method</span>
            <span class="matrix-cell">Should Cache?</span>
            <span class="matrix-cell">Actually Caches?</span>
            <span class="matrix-cell">Status</span>
          </div>
          <div class="matrix-row">
            <span class="matrix-cell"><i class="fas fa-arrow-down"></i> GET</span>
            <span class="matrix-cell safe">YES</span>
            <span class="matrix-cell">YES</span>
            <span class="matrix-cell safe"><i class="fas fa-check"></i></span>
          </div>
          <div class="matrix-row">
            <span class="matrix-cell"><i class="fas fa-arrow-up"></i> POST</span>
            <span class="matrix-cell safe">NO</span>
            <span class="matrix-cell vuln">YES</span>
            <span class="matrix-cell vuln"><i class="fas fa-times"></i></span>
          </div>
          <div class="matrix-row">
            <span class="matrix-cell"><i class="fas fa-edit"></i> PUT</span>
            <span class="matrix-cell safe">NO</span>
            <span class="matrix-cell vuln">YES</span>
            <span class="matrix-cell vuln"><i class="fas fa-times"></i></span>
          </div>
          <div class="matrix-row">
            <span class="matrix-cell"><i class="fas fa-trash"></i> DELETE</span>
            <span class="matrix-cell safe">NO</span>
            <span class="matrix-cell safe">NO</span>
            <span class="matrix-cell safe"><i class="fas fa-check"></i></span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Method Confusion Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">POST Body Poison</div>
            <code class="payload-code">POST /api -d "&lt;script&gt;alert(1)&lt;/script&gt;"</code>
            <span class="payload-target">Cached and served on GET</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">PUT Override</div>
            <code class="payload-code">PUT /page -d "content=POISONED"</code>
            <span class="payload-target">Replaces GET cache entry</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Method Override Header</div>
            <code class="payload-code">X-HTTP-Method-Override: PUT</code>
            <span class="payload-target">Bypass method restrictions</span>
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
          <span>Method: <?php echo $method; ?></span>
          <span>Cache Key: <?php echo substr($cache_key, 0, 16); ?>...</span>
          <span>Cache Status: <?php echo $cache_status; ?></span>
          <span>Confused: <?php echo $method_confused ? 'YES' : 'NO'; ?></span>
          <span>Detected: <?php echo $detected_method ? htmlspecialchars($detected_method) : 'NONE'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The cache keys all responses as GET, regardless of the actual HTTP method. Try sending a
        POST request with a malicious body to the same URL. The POST response will be cached and served on subsequent
        GET requests!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Use curl to send a POST request: <code>curl -X POST -d "data=POISONED"
            URL</code>. Then visit the same URL in your browser (GET). You'll see the POST response was cached and
        served
        instead!</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Send: <code>curl -X POST -d "content=&lt;script&gt;METHOD_CONFUSION&lt;/script&gt;"
            "URL"</code>. The server caches this POST response as a GET response. When anyone visits the page with GET,
        they receive your poisoned content!</div>
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