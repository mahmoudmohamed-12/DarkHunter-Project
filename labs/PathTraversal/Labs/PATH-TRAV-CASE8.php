<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_hard3_attempts'])) {
  $_SESSION['pt_hard3_attempts'] = 0;
}
if (!isset($_SESSION['pt_hard3_solved'])) {
  $_SESSION['pt_hard3_solved'] = false;
}
if (!isset($_SESSION['pt_hard3_stage'])) {
  $_SESSION['pt_hard3_stage'] = 1;
}

// ─── Simulated Application: DarkHunter API Gateway ───────────────────────
$api_endpoints = [
  'health'    => ['name' => 'Health Check', 'url' => 'http://localhost:8080/health', 'method' => 'GET'],
  'metrics'   => ['name' => 'Prometheus Metrics', 'url' => 'http://localhost:9090/metrics', 'method' => 'GET'],
  'config'    => ['name' => 'Service Config', 'url' => 'http://localhost:8080/api/config', 'method' => 'GET'],
  'internal'  => ['name' => 'Internal Status', 'url' => 'http://127.0.0.1:5000/status', 'method' => 'GET'],
];

// ─── Vulnerable Logic: URL fetch without protocol validation ─────────────
$target = isset($_GET['target']) ? $_GET['target'] : 'health';

// VULNERABLE: Accepts ANY protocol handler!
// file://, jar://, gopher://, ftp://, dict://, ldap://, tftp://
// The "security" only checks for http/https but doesn't block alternative protocols

$is_blocked = false;
$block_reason = '';

// Weak check - only validates URL format, not protocol
if (!filter_var($target, FILTER_VALIDATE_URL) && !isset($api_endpoints[$target])) {
  // Allow non-URL values to pass (like file paths)
}

// ─── Simulated Response Content ──────────────────────────────────────────
$response_content = '';
$response_headers = [];
$protocol_used = '';
$exploited = false;

// Check for SSRF/Protocol abuse
$ssrf_patterns = [
  // file:// protocol - Local File Read
  'file:///etc/passwd' => [
    'protocol' => 'file',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain', 'Content-Length: 1245'],
    'body' => "root:x:0:0:root:/root:/bin/bash\ndaemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin\nbin:x:2:2:bin:/bin:/usr/sbin/nologin\nsys:x:3:3:sys:/dev:/usr/sbin/nologin\n\n# Flag: DH{pt_ssrf_file_protocol_pwned}",
  ],
  'file:///etc/shadow' => [
    'protocol' => 'file',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain', 'Content-Length: 892'],
    'body' => "root:\$6\$rounds=5000\$...:19000:0:99999:7:::\ndaemon:*:19000:0:99999:7:::\n\n# Flag: DH{pt_ssrf_shadow_access}",
  ],
  'file:///proc/self/environ' => [
    'protocol' => 'file',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\nHOME=/var/www\nUSER=www-data\nAPACHE_RUN_USER=www-data\nAPACHE_RUN_GROUP=www-data\n\n# Flag: DH{pt_ssrf_proc_environ}",
  ],
  'file:///var/www/html/DarkHunter/Config/db.php' => [
    'protocol' => 'file',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/x-php'],
    'body' => "<?php\n\$host = 'localhost';\n\$db   = 'darkhunter_db';\n\$user = 'dh_admin';\n\$pass = 'DH_S3cur3_P@ssw0rd_2026!';\n\n// Flag: DH{pt_ssrf_db_config_leaked}",
  ],
  // jar:// protocol - JAR archive access
  'jar:file:///etc/passwd!/META-INF' => [
    'protocol' => 'jar',
    'status' => '200 OK',
    'headers' => ['Content-Type: application/java-archive'],
    'body' => "PK\x03\x04\n\x00\x00\x00\x00\x00\x00\x00!META-INF/MANIFEST.MF\n\n# Flag: DH{pt_ssrf_jar_protocol}",
  ],
  // gopher:// protocol - Internal service access
  'gopher://localhost:22/' => [
    'protocol' => 'gopher',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "SSH-2.0-OpenSSH_8.2p1 Ubuntu-4ubuntu0.5\n\n# Flag: DH{pt_ssrf_gopher_ssh}",
  ],
  'gopher://127.0.0.1:3306/' => [
    'protocol' => 'gopher',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "5.7.38-log\n\n# Flag: DH{pt_ssrf_gopher_mysql}",
  ],
  'gopher://127.0.0.1:6379/' => [
    'protocol' => 'gopher',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "+PONG\r\n\n# Flag: DH{pt_ssrf_gopher_redis}",
  ],
  // ftp:// protocol
  'ftp://anonymous@ftp.darkhunter.local/' => [
    'protocol' => 'ftp',
    'status' => '220 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "220 Welcome to DarkHunter FTP Server\n230 Anonymous access granted\n\n# Flag: DH{pt_ssrf_ftp_access}",
  ],
  // dict:// protocol
  'dict://localhost:11211/' => [
    'protocol' => 'dict',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "220 localhost dictd 1.12.1\n\n# Flag: DH{pt_ssrf_dict_protocol}",
  ],
  // ldap:// protocol
  'ldap://localhost:389/' => [
    'protocol' => 'ldap',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "LDAP://localhost:389\n\n# Flag: DH{pt_ssrf_ldap_protocol}",
  ],
  // tftp:// protocol
  'tftp://localhost:69/config.txt' => [
    'protocol' => 'tftp',
    'status' => '200 OK',
    'headers' => ['Content-Type: text/plain'],
    'body' => "TFTP config file contents...\n\n# Flag: DH{pt_ssrf_tftp_protocol}",
  ],
];

// Check for SSRF exploitation
foreach ($ssrf_patterns as $pattern => $data) {
  if (stripos($target, $pattern) !== false || $target === $pattern) {
    $response_content = $data['body'];
    $response_headers = $data['headers'];
    $protocol_used = $data['protocol'];
    $exploited = true;
    break;
  }
}

// Also check for partial matches (protocol handler detection)
$protocol_handlers = ['file://', 'jar:', 'gopher://', 'ftp://', 'dict://', 'ldap://', 'tftp://', 'http://127.', 'http://localhost', 'https://127.', 'https://localhost'];
foreach ($protocol_handlers as $handler) {
  if (stripos($target, $handler) !== false && !$exploited) {
    $protocol_used = explode(':', $handler)[0];
    $response_content = "SSRF attempt detected using $protocol_used protocol!\n\n# Flag: DH{pt_ssrf_chain_master}";
    $exploited = true;
    break;
  }
}

// Normal API responses
if (!$exploited && isset($api_endpoints[$target])) {
  $endpoint = $api_endpoints[$target];
  $protocol_used = 'http';
  $response_headers = ['Content-Type: application/json', 'X-Powered-By: DarkHunter-API/2.1'];
  $response_content = json_encode([
    'status' => 'ok',
    'service' => $endpoint['name'],
    'timestamp' => time(),
    'version' => '2.1.0',
  ], JSON_PRETTY_PRINT);
} elseif (!$exploited) {
  $response_headers = ['Content-Type: text/plain'];
  $response_content = "Error: Unable to fetch target. The endpoint may be unreachable or the protocol is not supported.\n\nAttempted: " . htmlspecialchars($target);
}

// ─── Stage Logic ─────────────────────────────────────────────────────────
$current_stage = $_SESSION['pt_hard3_stage'];
$stage_messages = [
  1 => "Stage 1: Identify how the API Gateway processes URLs and which protocols are accepted.",
  2 => "Stage 2: Discover alternative protocol handlers (file://, gopher://, jar://) that bypass HTTP restrictions.",
  3 => "Stage 3: Chain SSRF with Path Traversal to read arbitrary files and access internal services.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_hard3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_hard3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_hard3_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've chained SSRF with Path Traversal to exploit the API Gateway. By using alternative protocol handlers (file://, gopher://, jar://, ftp://, dict://), you bypassed HTTP restrictions and accessed internal files and services. This is an advanced technique used in real-world attacks against API gateways, webhooks, and URL fetchers!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['target'])) {
  $_SESSION['pt_hard3_attempts']++;
}

$attempts = $_SESSION['pt_hard3_attempts'];

// Update stage
if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['pt_hard3_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['pt_hard3_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Gateway - Path Traversal Hard 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-network-wired"></i> DarkHunter API Gateway</h1>
      <p class="lab-description">An API Gateway that fetches data from internal services. The application accepts URLs
        without validating protocol handlers. <strong>Can you chain SSRF with Path Traversal</strong> to read arbitrary
        files and access internal services?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this SSRF chained with Path Traversal. You can continue exploring, but no
            additional points will be awarded.</p>
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

    <!-- Stage Progress Tracker -->
    <div class="stage-tracker">
      <div class="stage-header">
        <i class="fas fa-layer-group"></i>
        <span>Attack Chain Progress</span>
      </div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info">
            <span class="stage-title">Reconnaissance</span>
            <span class="stage-desc">Map API Gateway behavior</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info">
            <span class="stage-title">Protocol Discovery</span>
            <span class="stage-desc">Find bypass protocols</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info">
            <span class="stage-title">Exploitation</span>
            <span class="stage-desc">Chain SSRF + Path Traversal</span>
          </div>
        </div>
      </div>
      <div class="stage-message">
        <i class="fas fa-info-circle"></i>
        <span><?php echo $stage_messages[$current_stage]; ?></span>
      </div>
    </div>

    <!-- API Gateway Interface -->
    <div class="gateway-card">
      <div class="gateway-header">
        <div class="gateway-brand">
          <i class="fas fa-server"></i>
          <span>API Gateway</span>
        </div>
        <div class="gateway-badge">
          <i class="fas fa-shield-alt"></i>
          <span>URL Fetcher</span>
        </div>
      </div>

      <div class="gateway-body">
        <!-- Request Builder -->
        <div class="request-panel">
          <div class="request-header">
            <i class="fas fa-paper-plane"></i>
            <span>Request Builder</span>
          </div>
          <div class="request-form">
            <div class="form-group">
              <label>Target URL / Endpoint:</label>
              <div class="input-group">
                <span class="input-prefix">GET</span>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars($target); ?>"
                  placeholder="http://localhost:8080/health" readonly>
              </div>
            </div>
            <div class="form-group">
              <label>Protocol Handler:</label>
              <div class="protocol-display">
                <span class="protocol-badge <?php echo $protocol_used; ?>">
                  <i
                    class="fas fa-<?php echo $protocol_used === 'file' ? 'file' : ($protocol_used === 'gopher' ? 'network-wired' : ($protocol_used === 'jar' ? 'archive' : 'globe')); ?>"></i>
                  <?php echo strtoupper($protocol_used); ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Endpoint Selector -->
        <div class="endpoint-panel">
          <div class="endpoint-header">
            <i class="fas fa-plug"></i>
            <span>Registered Endpoints</span>
          </div>
          <div class="endpoint-list">
            <?php foreach ($api_endpoints as $key => $ep): ?>
              <a href="?target=<?php echo urlencode($key); ?>"
                class="endpoint-item <?php echo $target === $key ? 'active' : ''; ?>">
                <div class="endpoint-method"><?php echo $ep['method']; ?></div>
                <div class="endpoint-info">
                  <span class="endpoint-name"><?php echo $ep['name']; ?></span>
                  <code class="endpoint-url"><?php echo $ep['url']; ?></code>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Response Viewer -->
        <div class="response-panel">
          <div class="response-header">
            <i class="fas fa-reply"></i>
            <span>Response</span>
            <span class="response-status <?php echo $exploited ? 'status-exploit' : 'status-normal'; ?>">
              <i class="fas fa-<?php echo $exploited ? 'bug' : 'check'; ?>"></i>
              <?php echo $exploited ? 'SSRF DETECTED' : '200 OK'; ?>
            </span>
          </div>
          <div class="response-body">
            <div class="response-meta">
              <div class="meta-row">
                <span class="meta-label">Protocol:</span>
                <span
                  class="meta-value protocol-<?php echo $protocol_used; ?>"><?php echo strtoupper($protocol_used); ?></span>
              </div>
              <div class="meta-row">
                <span class="meta-label">Target:</span>
                <code class="meta-value"><?php echo htmlspecialchars($target); ?></code>
              </div>
            </div>
            <div class="response-headers">
              <span class="headers-label">Response Headers:</span>
              <?php foreach ($response_headers as $header): ?>
                <code class="header-line"><?php echo htmlspecialchars($header); ?></code>
              <?php endforeach; ?>
            </div>
            <pre class="response-content"><?php echo htmlspecialchars($response_content); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Protocol Handler Reference -->
    <div class="protocol-panel">
      <div class="protocol-header">
        <i class="fas fa-book"></i>
        <span>Protocol Handler Attack Matrix</span>
      </div>
      <div class="protocol-body">
        <div class="protocol-grid">
          <div class="protocol-card">
            <div class="protocol-icon"><i class="fas fa-file"></i></div>
            <div class="protocol-info">
              <span class="protocol-name">file://</span>
              <span class="protocol-desc">Read local files</span>
              <code class="protocol-example">file:///etc/passwd</code>
            </div>
          </div>
          <div class="protocol-card">
            <div class="protocol-icon"><i class="fas fa-archive"></i></div>
            <div class="protocol-info">
              <span class="protocol-name">jar://</span>
              <span class="protocol-desc">Access JAR contents</span>
              <code class="protocol-example">jar:file:///etc/passwd!/META-INF</code>
            </div>
          </div>
          <div class="protocol-card">
            <div class="protocol-icon"><i class="fas fa-network-wired"></i></div>
            <div class="protocol-info">
              <span class="protocol-name">gopher://</span>
              <span class="protocol-desc">Internal network access</span>
              <code class="protocol-example">gopher://127.0.0.1:3306/</code>
            </div>
          </div>
          <div class="protocol-card">
            <div class="protocol-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="protocol-info">
              <span class="protocol-name">ftp://</span>
              <span class="protocol-desc">FTP protocol access</span>
              <code class="protocol-example">ftp://anonymous@ftp.local/</code>
            </div>
          </div>
          <div class="protocol-card">
            <div class="protocol-icon"><i class="fas fa-book"></i></div>
            <div class="protocol-info">
              <span class="protocol-name">dict://</span>
              <span class="protocol-desc">Dictionary protocol</span>
              <code class="protocol-example">dict://localhost:11211/</code>
            </div>
          </div>
          <div class="protocol-card">
            <div class="protocol-icon"><i class="fas fa-address-book"></i></div>
            <div class="protocol-info">
              <span class="protocol-name">ldap://</span>
              <span class="protocol-desc">LDAP directory access</span>
              <code class="protocol-example">ldap://localhost:389/</code>
            </div>
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
        <code>GET /PATH-TRAV-CASE8.php?target=<?php echo urlencode($target); ?></code>
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
          <pre>// VULNERABLE: No protocol validation
$target = $_GET['target'];

// Only checks URL format, not protocol!
if (!filter_var($target, FILTER_VALIDATE_URL)) {
    // Even this check is bypassed for non-URLs
}

// Fetches ANY URL including file://, gopher://, etc.
$response = file_get_contents($target);

// DANGEROUS: Can read local files, access internal services,
// and chain with path traversal for arbitrary file access!</pre>
        </div>
        <div class="vuln-note critical">
          <i class="fas fa-radiation"></i>
          <span><strong>Critical:</strong> SSRF chained with Path Traversal is a devastating combination. By using
            <code>file:///etc/passwd</code>, attackers read arbitrary files. Using <code>gopher://</code>, they scan
            internal networks. Using <code>jar://</code>, they access archive contents. Always validate and whitelist
            allowed protocols!</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The API Gateway accepts any URL scheme. Try using <code>file://</code> to read local
          files. For example: <code>?target=file:///etc/passwd</code> or
          <code>?target=file:///var/www/html/DarkHunter/Config/db.php</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Other powerful protocols: <code>gopher://127.0.0.1:3306/</code> (MySQL),
          <code>gopher://127.0.0.1:6379/</code> (Redis), <code>jar:file:///etc/passwd!/META-INF</code> (JAR access).
          Each protocol provides different attack capabilities.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?target=file:///etc/passwd</code> for local file read, or
          <code>?target=gopher://127.0.0.1:3306/</code> for internal service access. Any alternative protocol handler
          combined with path traversal will solve this challenge!
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
    <input type="hidden" name="target" value="<?php echo htmlspecialchars($target); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const target = urlParams.get('target') || '';

      const ssrfPatterns = [
        'file://', 'jar:', 'gopher://', 'ftp://',
        'dict://', 'ldap://', 'tftp://',
        'http://127.', 'http://localhost',
        'https://127.', 'https://localhost'
      ];

      const hasSSRF = ssrfPatterns.some(pattern =>
        target.toLowerCase().includes(pattern.toLowerCase())
      );

      if (hasSSRF && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>