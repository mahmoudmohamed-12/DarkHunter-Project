<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case7_attempts'])) {
  $_SESSION['jsonp_case7_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case7_solved'])) {
  $_SESSION['jsonp_case7_solved'] = false;
}

// VULNERABLE: JSONP through service workers
$callback = $_GET['callback'] ?? 'callback';
$sw_action = $_GET['sw_action'] ?? '';

// Simulated service worker cache
$sw_cache_file = sys_get_temp_dir() . '/sw_cache_' . md5($_SERVER['REQUEST_URI']) . '.json';
$sw_cached = false;
if (file_exists($sw_cache_file)) {
  $sw_cached = true;
  $sw_data = json_decode(file_get_contents($sw_cache_file), true);
}

$data = [
  'status' => 'ok',
  'cached' => $sw_cached,
  'sw_version' => '1.0.0',
  'cache_strategy' => 'CacheFirst'
];

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'fetch') !== false ||
    stripos($cb, 'caches') !== false ||
    stripos($cb, 'navigator') !== false ||
    stripos($cb, 'serviceWorker') !== false ||
    stripos($cb, 'registration') !== false ||
    stripos($cb, 'install') !== false
  ) {
    $flag_triggered = true;
  }
}

if ($sw_action === 'poison' || $sw_action === 'install_malicious') {
  $flag_triggered = true;
  $poisoned_sw = [
    'poisoned' => true,
    'payload' => 'malicious_service_worker',
    'scope' => '/',
    'persistent' => true
  ];
  file_put_contents($sw_cache_file, json_encode($poisoned_sw));
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case7_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case7_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case7_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've successfully abused JSONP through service workers. By caching malicious callback responses in the service worker, you created persistent XSS that survives page reloads and affects all users across sessions!";
}

if (isset($_GET['callback']) || isset($_GET['sw_action'])) {
  $_SESSION['jsonp_case7_attempts']++;
}

$attempts = $_SESSION['jsonp_case7_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SW Manager - JSONP Case 7 (Service Worker Abuse)</title>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-cog"></i> Service Worker Manager</h1>
      <p class="lab-description">Manage our service workers. This hard challenge has <strong>vulnerable JSONP integration with service workers</strong>. Cache malicious responses for persistent XSS across sessions!</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Service Worker Abuse vulnerability.</p>
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

    <div class="api-status-bar">
      <div class="api-indicator"><i class="fas fa-cog"></i><span>SW: <strong><?php echo $sw_cached ? 'Cached' : 'Active'; ?></strong></span></div>
      <div class="cache-indicator <?php echo $sw_cached ? 'hit' : 'miss'; ?>">
        <i class="fas fa-<?php echo $sw_cached ? 'database' : 'cloud'; ?>"></i>
        <span>Cache: <strong><?php echo $sw_cached ? 'POISONED' : 'Clean'; ?></strong></span>
      </div>
      <?php if ($flag_triggered): ?>
        <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>SW ABUSE DETECTED!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card sw-card">
        <div class="card-header">
          <i class="fas fa-memory"></i>
          <h3>Service Worker Cache</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Poisonable</span>
        </div>
        <div class="sw-content">
          <div class="sw-status">
            <div class="status-item">
              <span class="status-label">Cache Strategy:</span>
              <code class="status-value">CacheFirst</code>
            </div>
            <div class="status-item">
              <span class="status-label">Scope:</span>
              <code class="status-value">/</code>
            </div>
            <div class="status-item">
              <span class="status-label">Version:</span>
              <code class="status-value">1.0.0</code>
            </div>
            <div class="status-item">
              <span class="status-label">Persistent:</span>
              <code class="status-value <?php echo $sw_cached ? 'danger' : 'safe'; ?>"><?php echo $sw_cached ? 'YES' : 'NO'; ?></code>
            </div>
          </div>
          <div class="sw-actions">
            <a href="?sw_action=poison" class="sw-btn <?php echo $sw_cached ? 'active' : ''; ?>">
              <i class="fas fa-<?php echo $sw_cached ? 'check' : 'skull'; ?>"></i>
              <?php echo $sw_cached ? 'Cache Poisoned' : 'Poison Cache'; ?>
            </a>
            <a href="?sw_action=install_malicious" class="sw-btn malicious">
              <i class="fas fa-bug"></i> Install Malicious SW
            </a>
          </div>
          <div class="sw-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Service workers persist across sessions. Poisoned cache affects ALL future visits!</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card attack-card">
        <div class="card-header">
          <i class="fas fa-crosshairs"></i>
          <h3>Persistent XSS Attack</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Cross-Session</span>
        </div>
        <div class="attack-content">
          <div class="attack-step">
            <span class="step-num">1</span>
            <span class="step-text">Attacker poisons JSONP callback response</span>
          </div>
          <div class="attack-step">
            <span class="step-num">2</span>
            <span class="step-text">Service worker caches the malicious response</span>
          </div>
          <div class="attack-step">
            <span class="step-num">3</span>
            <span class="step-text">All users receive cached malicious content</span>
          </div>
          <div class="attack-step vuln-step">
            <span class="step-num">4</span>
            <span class="step-text">XSS persists even after server-side fixes!</span>
          </div>
          <div class="exploit-code">
            <div class="code-label">Poisoned Response:</div>
            <pre class="code-block"><code>// Service Worker intercepts request
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      // Returns POISONED cached response!
      return response || fetch(event.request);
    })
  );
});

// Cached: alert(1)({"status":"ok"...});</code></pre>
          </div>
        </div>
      </div>

      <div class="jsonp-card analysis-card">
        <div class="card-header"><i class="fas fa-microscope"></i>
          <h3>Vulnerability Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">SW Registration:</span>
            <code class="analysis-code">// No scope validation</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Unrestricted</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Cache Validation:</span>
            <code class="analysis-code">// No integrity checks</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Update Frequency:</span>
            <code class="analysis-code">// Manual only</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Stale</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Fix:</span>
            <code class="analysis-code">Cache validation + CSP</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Required</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>SW Abuse Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Cache Poison</div>
            <code class="payload-code">?sw_action=poison</code>
            <span class="payload-target">Poison service worker cache</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Malicious SW</div>
            <code class="payload-code">?sw_action=install_malicious</code>
            <span class="payload-target">Install backdoor service worker</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Callback + SW</div>
            <code class="payload-code">?callback=alert&sw_action=poison</code>
            <span class="payload-target">Combine JSONP with SW abuse</span>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>SW Cached: <?php echo $sw_cached ? 'YES' : 'NO'; ?></span>
          <span>SW Action: <?php echo $sw_action ? htmlspecialchars($sw_action) : 'NONE'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Service workers cache responses persistently. Try <code>?sw_action=poison</code> to poison the cache. The malicious content will be served to all users even after the server is fixed!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try combining JSONP callback with SW actions: <code>?callback=alert&sw_action=poison</code>. The service worker will cache the malicious JSONP response and serve it on every request!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?sw_action=poison</code> or <code>?sw_action=install_malicious</code> to trigger the flag. The service worker has no validation and will cache/install anything you request!</div>
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
  </form>

  <script>
    window.addEventListener('load', function() {
      const exploitIndicator = document.querySelector('.exploit-indicator');
      const alreadySolved = document.querySelector('.solved-banner');
      if (exploitIndicator && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>