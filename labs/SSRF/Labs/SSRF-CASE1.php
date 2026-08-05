<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_easy1_attempts'])) {
$_SESSION['ssrf_easy1_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_easy1_solved'])) {
$_SESSION['ssrf_easy1_solved'] = false;
}

// ─── Simulated Application: DarkHunter URL Fetcher ───────────────────────
$services = [
'google'    => ['name' => 'Google', 'url' => 'https://www.google.com', 'status' => '200 OK'],
'github'    => ['name' => 'GitHub', 'url' => 'https://api.github.com', 'status' => '200 OK'],
'darkhunter' => ['name' => 'DarkHunter API', 'url' => 'https://api.darkhunter.local/v1/status', 'status' => '200 OK'],
];

// ─── Vulnerable Logic: Unvalidated URL parameter ─────────────────────────
$url = isset($_GET['url']) ? $_GET['url'] : 'https://www.google.com';

$response = '';
$is_internal = false;
$exploited = false;

// Simulate fetching the URL
$internal_patterns = [
'127.0.0.1',
'localhost',
'0.0.0.0',
'::1',
'192.168.',
'10.0.',
'10.1.',
'172.16.',
'169.254.169.254',
'metadata.google.internal',
'http://internal',
'https://internal',
'file://',
'ftp://',
'gopher://',
'dict://',
];

foreach ($internal_patterns as $pattern) {
if (stripos($url, $pattern) !== false) {
  $is_internal = true;
  $exploited = true;
  break;
}
}

if ($is_internal) {
if (stripos($url, '127.0.0.1') !== false || stripos($url, 'localhost') !== false) {
  $response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><body><h1>Apache/2.4.41 Server Status</h1><p>Server Version: Apache/2.4.41 (Ubuntu)</p><p>Server Built: 2026-01-15</p><hr><p>Current Time: " . date('r') . "</p><p>Restart Time: 2026-05-20 08:00:00</p><p>Parent Server Generation: 0</p><p>Server uptime: 3 days 12 hours 45 minutes</p><hr><p>Total accesses: 15420 | Total Traffic: 45.2 MB</p><p>CPU Usage: u12.5 s3.2 cu0 cs0</p><p>0.05 requests/sec - 1.5 kB/second - 30.1 kB/request</p><p>1 requests currently being processed, 4 idle workers</p><hr><pre>W___............................................................</pre><hr><p>Scoreboard Key: \"_\" Waiting for Connection</p></body></html>\n\nFlag: DH{ssrf_basic_internal_access}";
} elseif (stripos($url, '169.254.169.254') !== false) {
  $response = "HTTP/1.1 200 OK\nContent-Type: text/plain\n\nami-id\nami-launch-index\nami-manifest-path\nblock-device-mapping/\nevents/\nhostname\nidentity-credentials/\ninstance-action\ninstance-id\ninstance-life-cycle\ninstance-type\nlocal-hostname\nlocal-ipv4\nmac\nmetrics/\nnetwork/\nplacement/\nprofile\npublic-keys/\nreservation-id\nsecurity-groups\nservices/\n\nFlag: DH{ssrf_metadata_access}";
} else {
  $response = "HTTP/1.1 200 OK\nContent-Type: text/plain\n\nInternal service response detected.\nAccess to internal network confirmed.\n\nFlag: DH{ssrf_internal_network}";
}
} else {
$response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><body><h1>External Resource</h1><p>Successfully fetched: " . htmlspecialchars($url) . "</p><p>Content length: 1245 bytes</p></body></html>";
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_easy1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
$_SESSION['ssrf_easy1_attempts']++;

if (!$already_solved && isset($_SESSION['user_id'])) {
  solveLab($pdo, $lab['id']);
}

$_SESSION['ssrf_easy1_solved'] = true;
$already_solved = true;
$success_msg = "Excellent! You've successfully exploited a basic SSRF vulnerability. By supplying an internal address (127.0.0.1 or localhost), you forced the server to make requests to its own internal services. This is the foundation of all SSRF attacks!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['url'])) {
$_SESSION['ssrf_easy1_attempts']++;
}

$attempts = $_SESSION['ssrf_easy1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>URL Fetcher - SSRF Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-globe"></i> DarkHunter URL Fetcher</h1>
      <p class="lab-description">Fetch external resources through our secure proxy service. The application fetches URLs
        on behalf of users. <strong>No URL validation applied!</strong> Can you access internal services?</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this SSRF vulnerability. You can continue exploring, but no additional points will
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

    <!-- Fetcher Interface -->
    <div class="fetcher-card">
      <div class="fetcher-header">
        <div class="fetcher-brand">
          <i class="fas fa-cloud-download-alt"></i>
          <span>URL Fetcher</span>
        </div>
        <div class="fetcher-status">
          <span class="status-dot live"></span>
          <span>Active</span>
        </div>
      </div>

      <div class="fetcher-body">
        <!-- URL Input -->
        <div class="url-panel">
          <div class="url-header">
            <i class="fas fa-link"></i>
            <span>Target URL</span>
          </div>
          <div class="url-form">
            <div class="input-group">
              <span class="input-prefix">GET</span>
              <input type="text" class="form-input" value="<?php echo htmlspecialchars($url); ?>"
                placeholder="https://example.com" readonly>
            </div>
          </div>
        </div>

        <!-- Service Presets -->
        <div class="presets-panel">
          <div class="presets-header">
            <i class="fas fa-bookmark"></i>
            <span>Quick Presets</span>
          </div>
          <div class="presets-grid">
            <?php foreach ($services as $key => $svc): ?>
            <a href="?url=<?php echo urlencode($svc['url']); ?>"
              class="preset-card <?php echo $url === $svc['url'] ? 'active' : ''; ?>">
              <div class="preset-icon"><i class="fas fa-globe"></i></div>
              <div class="preset-info">
                <span class="preset-name"><?php echo $svc['name']; ?></span>
                <code class="preset-url"><?php echo $svc['url']; ?></code>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Response Viewer -->
        <div class="response-panel">
          <div class="response-header">
            <i class="fas fa-reply"></i>
            <span>Server Response</span>
            <span class="response-badge <?php echo $is_internal ? 'internal' : 'external'; ?>">
              <i class="fas fa-<?php echo $is_internal ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
              <?php echo $is_internal ? 'INTERNAL ACCESS' : 'External'; ?>
            </span>
          </div>
          <div class="response-body">
            <pre class="response-content"><?php echo htmlspecialchars($response); ?></pre>
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
        <code>GET /SSRF-CASE1.php?url=<?php echo urlencode($url); ?></code>
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
          <pre>// VULNERABLE: No URL validation
$url = $_GET['url'];

// Directly fetches ANY URL including internal addresses
$response = file_get_contents($url);

// No checks for:
// - Private IP ranges (127.0.0.1, 192.168.x.x, 10.x.x.x)
// - localhost
// - Internal hostnames
// - Alternative protocols</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> The application accepts any URL from user input and fetches it
            server-side. This allows attackers to make the server request internal resources that are not accessible
            from the outside.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The <code>url</code> parameter is used directly without validation. Try supplying internal
        addresses like <code>127.0.0.1</code> or <code>localhost</code> instead of external URLs.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try accessing the local web server: <code>?url=http://127.0.0.1/</code> or
        <code>?url=http://localhost/server-status</code>. You can also try cloud metadata at
        <code>http://169.254.169.254/</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?url=http://127.0.0.1/</code> to access the local Apache server, or
        <code>?url=http://169.254.169.254/latest/meta-data/</code> to access cloud metadata. Any internal address will
        solve this challenge!
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
    <input type="hidden" name="url" value="<?php echo htmlspecialchars($url); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const url = urlParams.get('url') || '';

    const internalPatterns = [
      '127.0.0.1', 'localhost', '0.0.0.0', '::1',
      '192.168.', '10.0.', '10.1.', '172.16.',
      '169.254.169.254', 'metadata.google.internal',
      'file://', 'ftp://', 'gopher://', 'dict://'
    ];

    const isInternal = internalPatterns.some(pattern =>
      url.toLowerCase().includes(pattern.toLowerCase())
    );

    if (isInternal && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>