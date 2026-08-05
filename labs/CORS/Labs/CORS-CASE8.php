<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_case8_attempts'])) {
  $_SESSION['cors_case8_attempts'] = 0;
}
if (!isset($_SESSION['cors_case8_solved'])) {
  $_SESSION['cors_case8_solved'] = false;
}

// ─── Vulnerable CORS Logic: Trusted Origin with SSRF Proxy ────────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Server has a whitelist of trusted origins
// But it also acts as a proxy to internal APIs based on the 'proxy' parameter
$trusted_origins = ['https://app.darkhunter.local', 'https://portal.darkhunter.local'];

if (in_array($origin, $trusted_origins) || empty($origin)) {
  header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
  header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Proxy-Target");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ─── Simulated Internal API Proxy Data ────────────────────────────────────
$internal_apis = [
  'admin' => [
    'endpoint' => 'http://admin-panel.internal:8080/api/users',
    'data' => [
      ['id' => 1, 'username' => 'superadmin', 'role' => 'Super Administrator', 'last_login' => '2026-05-22 04:00:00'],
      ['id' => 2, 'username' => 'security_admin', 'role' => 'Security Admin', 'last_login' => '2026-05-22 03:30:00'],
      ['id' => 3, 'username' => 'devops', 'role' => 'DevOps Engineer', 'last_login' => '2026-05-21 22:00:00'],
    ],
    'flag' => 'DH{c0rs_t0_ssrf_ch41n_1nt3rn4l_4cc3ss}'
  ],
  'billing' => [
    'endpoint' => 'http://billing-api.internal:9000/api/invoices',
    'data' => [
      ['id' => 'INV-001', 'client' => 'Acme Corp', 'amount' => 15000, 'status' => 'paid'],
      ['id' => 'INV-002', 'client' => 'TechStart Inc', 'amount' => 8500, 'status' => 'pending'],
      ['id' => 'INV-003', 'client' => 'GlobalSec', 'amount' => 22000, 'status' => 'paid'],
    ]
  ],
  'monitoring' => [
    'endpoint' => 'http://monitoring.internal:3000/api/metrics',
    'data' => [
      ['metric' => 'cpu_usage', 'value' => '45%', 'alert' => false],
      ['metric' => 'memory_usage', 'value' => '78%', 'alert' => true],
      ['metric' => 'disk_io', 'value' => '120 MB/s', 'alert' => false],
      ['metric' => 'network_latency', 'value' => '250ms', 'alert' => true],
    ]
  ],
  'secrets' => [
    'endpoint' => 'http://vault.internal:8200/v1/secret/data/admin',
    'data' => [
      'api_key' => ' "YOUR_BREVO_API_KEY";',
      'db_password' => 'SuperSecretDBPass123!',
      'jwt_secret' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
      'flag' => 'DH{c0rs_t0_ssrf_ch41n_1nt3rn4l_4cc3ss}'
    ]
  ]
];

// ─── Vulnerable Proxy Logic ──────────────────────────────────────────────
$endpoint = $_GET['endpoint'] ?? 'admin';
$proxy_target = $_GET['proxy'] ?? null;
$response_data = [];
$html_injected = false;
$flag_triggered = false;
$ssrf_triggered = false;

// VULNERABLE: Proxy functionality - fetches data from internal APIs
if ($proxy_target) {
  $allowed_proxies = ['admin', 'billing', 'monitoring', 'secrets'];

  if (in_array($proxy_target, $allowed_proxies) && isset($internal_apis[$proxy_target])) {
    $response_data = [
      'status' => 'success',
      'proxy_target' => $internal_apis[$proxy_target]['endpoint'],
      'data' => $internal_apis[$proxy_target]['data'],
      'message' => 'Proxied request successful',
      'source' => 'internal_proxy'
    ];
    $ssrf_triggered = true;
  } else {
    $response_data = [
      'status' => 'success',
      'proxy_target' => $proxy_target,
      'data' => ['error' => 'Proxy target not in whitelist but request was processed'],
      'message' => 'Proxy request processed',
      'warning' => 'Open proxy vulnerability - any URL can be accessed!'
    ];
    $ssrf_triggered = true;
  }

  if ($ssrf_triggered) {
    $html_injected = true;
    $flag_triggered = true;
  }
} else {
  switch ($endpoint) {
    case 'admin':
      $response_data = [
        'status' => 'success',
        'data' => $internal_apis['admin']['data'],
        'message' => 'Admin users retrieved',
        'flag' => $internal_apis['admin']['flag']
      ];
      break;
    case 'billing':
      $response_data = [
        'status' => 'success',
        'data' => $internal_apis['billing']['data'],
        'message' => 'Billing invoices retrieved'
      ];
      break;
    case 'monitoring':
      $response_data = [
        'status' => 'success',
        'data' => $internal_apis['monitoring']['data'],
        'message' => 'Monitoring metrics retrieved'
      ];
      break;
    case 'secrets':
      $response_data = [
        'status' => 'success',
        'data' => $internal_apis['secrets']['data'],
        'message' => 'Secrets retrieved',
        'warning' => 'Contains sensitive credentials'
      ];
      break;
    default:
      $response_data = [
        'status' => 'error',
        'message' => 'Unknown endpoint. Available: admin, billing, monitoring, secrets'
      ];
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_case8_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_case8_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_case8_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully chained a CORS misconfiguration with an SSRF vulnerability. You used the proxy functionality to access internal APIs and exfiltrate sensitive secrets from the internal vault!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['proxy']) || isset($_GET['endpoint']))) {
  $_SESSION['cors_case8_attempts']++;
}

$attempts = $_SESSION['cors_case8_attempts'];

// API response for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($response_data, JSON_PRETTY_PRINT);
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Gateway - CORS Case 8 (CORS to SSRF Chain)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-network-wired"></i> API Gateway Proxy</h1>
      <p class="lab-description">Enterprise API gateway that proxies requests to internal microservices. CORS is enabled
        for "trusted partners". <strong>Can you chain the CORS misconfiguration with the proxy functionality to perform
          SSRF and access internal vault secrets?</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this CORS-to-SSRF chain. You can continue exploring, but no additional points will
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

    <!-- CORS Info Banner -->
    <div class="cors-banner">
      <i class="fas fa-lock"></i>
      <div class="cors-content">
        <strong>CORS Configuration Active</strong>
        <code>Access-Control-Allow-Origin: <?php echo $origin ?: '*'; ?> | Access-Control-Allow-Credentials: true</code>
      </div>
    </div>

    <!-- Gateway Grid -->
    <div class="gateway-grid">

      <!-- Endpoint Selector -->
      <div class="gateway-card selector-card">
        <div class="card-header">
          <i class="fas fa-th-list"></i>
          <h3>Internal Services</h3>
        </div>

        <nav class="service-nav">
          <a href="?endpoint=admin"
            class="service-link <?php echo $endpoint === 'admin' && !$proxy_target ? 'active' : ''; ?>"
            data-endpoint="admin">
            <i class="fas fa-users-cog"></i> Admin Panel
          </a>
          <a href="?endpoint=billing"
            class="service-link <?php echo $endpoint === 'billing' && !$proxy_target ? 'active' : ''; ?>"
            data-endpoint="billing">
            <i class="fas fa-file-invoice-dollar"></i> Billing API
          </a>
          <a href="?endpoint=monitoring"
            class="service-link <?php echo $endpoint === 'monitoring' && !$proxy_target ? 'active' : ''; ?>"
            data-endpoint="monitoring">
            <i class="fas fa-chart-line"></i> Monitoring
          </a>
          <a href="?endpoint=secrets"
            class="service-link <?php echo $endpoint === 'secrets' && !$proxy_target ? 'active' : ''; ?>"
            data-endpoint="secrets">
            <i class="fas fa-lock"></i> Vault Secrets
          </a>
        </nav>

        <div class="proxy-demo">
          <span class="demo-label">Try SSRF via proxy:</span>
          <code class="demo-code">?proxy=admin</code>
          <code class="demo-code">?proxy=secrets</code>
          <code class="demo-code">?proxy=billing</code>
        </div>
      </div>

      <!-- Content Display (Vulnerable) -->
      <div class="gateway-card display-card">
        <div class="card-header">
          <i class="fas fa-eye"></i>
          <h3>API Response</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Proxy Enabled</span>
        </div>

        <div class="response-display">
          <?php if ($proxy_target): ?>
            <div class="proxy-response">
              <div class="response-header">
                <i class="fas fa-server"></i>
                <span>Proxy Target:
                  <?php echo htmlspecialchars($internal_apis[$proxy_target]['endpoint'] ?? $proxy_target); ?></span>
              </div>
              <pre class="response-code"><code><?php echo json_encode($response_data, JSON_PRETTY_PRINT); ?></code></pre>
            </div>
          <?php else: ?>
            <div class="default-response">
              <i class="fas fa-shield-alt"></i>
              <h3>API Gateway Response</h3>
              <p>Select an internal service or use the <code>?proxy=</code> parameter to access internal APIs through the
                gateway.</p>
              <div class="response-preview">
                <pre><code>{
  "status": "success",
  "endpoint": "<?php echo $endpoint; ?>",
  "cors_enabled": true,
  "credentials_allowed": true
}</code></pre>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($ssrf_triggered): ?>
          <div class="ssrf-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="ssrf-content">
              <strong>SSRF Detected!</strong>
              <span>You accessed an internal API through the proxy!</span>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- CORS Analysis -->
      <div class="gateway-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>CORS Configuration Analysis</h3>
        </div>
        <div class="cors-rules">
          <div class="rule-item allowed">
            <i class="fas fa-check-circle"></i>
            <div class="rule-text">
              <strong>Access-Control-Allow-Origin</strong>
              <span>Reflects trusted origins: <?php echo implode(', ', $trusted_origins); ?></span>
            </div>
          </div>
          <div class="rule-item allowed">
            <i class="fas fa-check-circle"></i>
            <div class="rule-text">
              <strong>Access-Control-Allow-Credentials</strong>
              <span>true - Cookies and auth headers are sent cross-origin</span>
            </div>
          </div>
          <div class="rule-item vuln">
            <i class="fas fa-times-circle"></i>
            <div class="rule-text">
              <strong>Proxy Validation</strong>
              <span>No URL validation on proxy parameter - SSRF possible!</span>
            </div>
          </div>
          <div class="rule-item vuln">
            <i class="fas fa-times-circle"></i>
            <div class="rule-text">
              <strong>Internal API Exposure</strong>
              <span>Internal services accessible through gateway proxy</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Attack Vectors -->
      <div class="gateway-card vectors-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>CORS-to-SSRF Chain Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Direct Proxy Access</div>
            <code class="vector-code">?proxy=secrets</code>
            <span class="vector-desc">Access vault secrets directly through proxy</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Cross-Origin with Credentials</div>
            <code class="vector-code">fetch(url, {credentials: 'include'})</code>
            <span class="vector-desc">Send authenticated requests from malicious origin</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Internal Network Scanning</div>
            <code class="vector-code">?proxy=http://192.168.1.1:8080</code>
            <span class="vector-desc">Scan internal network through open proxy</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Cloud Metadata Access</div>
            <code class="vector-code">?proxy=http://169.254.169.254/latest/meta-data</code>
            <span class="vector-desc">Access cloud instance metadata (AWS/Azure/GCP)</span>
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
          <span>Origin: <?php echo htmlspecialchars($origin ?: 'None'); ?></span>
          <span>Endpoint: <?php echo htmlspecialchars($endpoint); ?></span>
          <span>Proxy: <?php echo $proxy_target ?: 'None'; ?></span>
          <span>SSRF Triggered: <?php echo $ssrf_triggered ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Notice the <code>?proxy=</code> parameter in the URL. This parameter forwards requests to
          internal services. Try accessing <code>?proxy=secrets</code> to reach the internal vault API.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The CORS configuration allows trusted origins with credentials. Combine this with the proxy
          functionality: from a malicious origin, make a cross-origin request with credentials to
          <code>?proxy=secrets</code> and exfiltrate the data.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use the proxy parameter to access internal APIs: <code>?proxy=admin</code> for admin users,
          <code>?proxy=secrets</code> for vault secrets. The flag is <code>DH{c0rs_t0_ssrf_ch41n_1nt3rn4l_4cc3ss}</code>.
          Any proxy access triggers the vulnerability!
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
      const ssrfAlert = document.querySelector('.ssrf-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (ssrfAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>