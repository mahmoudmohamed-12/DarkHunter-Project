<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['csrf_case6_attempts'])) {
  $_SESSION['csrf_case6_attempts'] = 0;
}
if (!isset($_SESSION['csrf_case6_solved'])) {
  $_SESSION['csrf_case6_solved'] = false;
}

// ─── Simulated API User Data ─────────────────────────────────────────────
$api_users = [
  1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Administrator', 'api_key' => 'dh_api_7a3f9c2e1d8b4a5f6e7d', '2fa_enabled' => true],
  2 => ['id' => 2, 'username' => 'analyst', 'email' => 'analyst@darkhunter.local', 'role' => 'Security Analyst', 'api_key' => 'dh_api_9b2e4c6d8a0f1e3b5c7', '2fa_enabled' => false],
  3 => ['id' => 3, 'username' => 'developer', 'email' => 'dev@darkhunter.local', 'role' => 'Developer', 'api_key' => 'dh_api_1c3d5e7f9a2b4c6d8e', '2fa_enabled' => false],
];

$api_endpoints = [
  '/api/v1/users' => ['method' => 'GET', 'auth' => 'Bearer', 'description' => 'List all users'],
  '/api/v1/users/update' => ['method' => 'POST', 'auth' => 'Bearer', 'description' => 'Update user profile'],
  '/api/v1/users/delete' => ['method' => 'DELETE', 'auth' => 'Bearer', 'description' => 'Delete user account'],
  '/api/v1/admin/config' => ['method' => 'PUT', 'auth' => 'Bearer', 'description' => 'Update system configuration'],
  '/api/v1/admin/keys' => ['method' => 'GET', 'auth' => 'Bearer', 'description' => 'View API keys and secrets'],
];

$api_logs = [
  ['timestamp' => '2026-05-22T04:15:32Z', 'endpoint' => '/api/v1/users', 'method' => 'GET', 'status' => 200, 'ip' => '192.168.1.45'],
  ['timestamp' => '2026-05-22T03:42:18Z', 'endpoint' => '/api/v1/users/update', 'method' => 'POST', 'status' => 200, 'ip' => '192.168.1.45'],
  ['timestamp' => '2026-05-22T02:30:11Z', 'endpoint' => '/api/v1/admin/config', 'method' => 'PUT', 'status' => 403, 'ip' => '10.0.0.7'],
  ['timestamp' => '2026-05-22T01:58:45Z', 'endpoint' => '/api/v1/admin/keys', 'method' => 'GET', 'status' => 401, 'ip' => '10.0.0.7'],
];

// ─── Vulnerable API Handler ──────────────────────────────────────────────
$api_response = null;
$show_secrets = false;

// VULNERABLE: Accepts JSON/XML without proper CSRF protection
// CORS is misconfigured to allow all origins for "testing purposes"
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
  $input = file_get_contents('php://input');

  // VULNERABLE: No CSRF token for API requests
  // VULNERABLE: CORS allows any origin with credentials
  if (strpos($content_type, 'application/json') !== false) {
    $data = json_decode($input, true);

    if (isset($data['action'])) {
      if ($data['action'] === 'update_profile') {
        $api_response = [
          'status' => 'success',
          'message' => 'Profile updated via JSON API',
          'data' => $data
        ];
      }

      if ($data['action'] === 'delete_account') {
        $api_response = [
          'status' => 'success',
          'message' => 'Account deletion initiated via JSON API',
          'warning' => 'This action is irreversible!'
        ];
      }

      if ($data['action'] === 'reveal_api_keys') {
        $show_secrets = true;
        $api_response = [
          'status' => 'success',
          'message' => 'API keys revealed - CSRF vulnerability exploited!',
          'flag' => 'DH{csrf_api_json_pwned}'
        ];
      }
    }
  }

  // VULNERABLE: Also accepts XML with no CSRF protection
  if (strpos($content_type, 'application/xml') !== false || strpos($content_type, 'text/xml') !== false) {
    $api_response = [
      'status' => 'success',
      'message' => 'XML request processed - No CSRF validation!',
      'received_xml' => substr($input, 0, 200)
    ];
  }
}

// ─── CORS Headers (VULNERABLE) ──────────────────────────────────────────
// VULNERABLE: Reflects any origin and allows credentials
header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['csrf_case6_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['csrf_case6_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['csrf_case6_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've successfully exploited a CSRF vulnerability against a JSON API endpoint. The misconfigured CORS policy allowed your cross-origin request with credentials, and the API accepted the forged JSON payload without CSRF tokens!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['view']) || isset($_GET['endpoint']))) {
  $_SESSION['csrf_case6_attempts']++;
}

$attempts = $_SESSION['csrf_case6_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Console - CSRF Case 6 (JSON/XML Request)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CSRF-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-satellite-dish"></i> API Management Console</h1>
      <p class="lab-description">Manage DarkHunter API endpoints and monitor requests. This advanced CSRF challenge
        targets JSON/XML API endpoints with misconfigured CORS. <strong>No CSRF tokens on API state changes!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this API CSRF vulnerability. You can continue exploring, but no additional points
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

    <!-- API Response Display -->
    <?php if ($api_response): ?>
      <div class="api-response-panel">
        <div class="response-header">
          <i class="fas fa-terminal"></i>
          <span>API Response</span>
          <span
            class="response-status <?php echo $api_response['status']; ?>"><?php echo strtoupper($api_response['status']); ?></span>
        </div>
        <pre class="response-body"><code><?php echo json_encode($api_response, JSON_PRETTY_PRINT); ?></code></pre>
      </div>
    <?php endif; ?>

    <!-- API Console Grid -->
    <div class="api-console">

      <!-- Endpoint Explorer -->
      <div class="console-card">
        <div class="card-header">
          <i class="fas fa-route"></i>
          <h3>API Endpoints</h3>
        </div>
        <div class="endpoints-list">
          <?php foreach ($api_endpoints as $path => $endpoint): ?>
            <div class="endpoint-item">
              <div class="endpoint-method <?php echo strtolower($endpoint['method']); ?>">
                <?php echo $endpoint['method']; ?>
              </div>
              <div class="endpoint-info">
                <code class="endpoint-path"><?php echo $path; ?></code>
                <span class="endpoint-desc"><?php echo $endpoint['description']; ?></span>
              </div>
              <span class="endpoint-auth">
                <i class="fas fa-shield-alt"></i> <?php echo $endpoint['auth']; ?>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- API Tester (Vulnerable) -->
      <div class="console-card vulnerable-card">
        <div class="card-header">
          <i class="fas fa-flask"></i>
          <h3>API Request Tester</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No CSRF Token</span>
        </div>
        <form method="POST" action="" class="api-form" id="api-tester">
          <div class="form-row">
            <div class="form-group">
              <label>Content-Type</label>
              <select name="content_type" class="form-select">
                <option value="application/json">application/json</option>
                <option value="application/xml">application/xml</option>
                <option value="text/plain">text/plain</option>
              </select>
            </div>
            <div class="form-group">
              <label>Action</label>
              <select name="api_action" class="form-select">
                <option value="update_profile">Update Profile</option>
                <option value="delete_account">Delete Account</option>
                <option value="reveal_api_keys">Reveal API Keys</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Request Body (JSON)</label>
            <textarea name="request_body" class="form-textarea" rows="6"
              placeholder='{"action": "update_profile", "data": {}}'>{"action": "update_profile", "data": {"email": "new@darkhunter.local"}}</textarea>
          </div>
          <button type="submit" class="btn-action">
            <i class="fas fa-paper-plane"></i> Send Request
          </button>
        </form>
      </div>

      <!-- CORS Configuration (Vulnerable) -->
      <div class="console-card config-card">
        <div class="card-header">
          <i class="fas fa-globe"></i>
          <h3>CORS Configuration</h3>
          <span class="vuln-badge"><i class="fas fa-exclamation-triangle"></i> Misconfigured</span>
        </div>
        <div class="config-list">
          <div class="config-item">
            <span class="config-name">Access-Control-Allow-Origin</span>
            <code class="config-value vuln"><?php echo $origin ?: '*'; ?> (Reflects Origin)</code>
          </div>
          <div class="config-item">
            <span class="config-name">Access-Control-Allow-Credentials</span>
            <code class="config-value vuln">true</code>
          </div>
          <div class="config-item">
            <span class="config-name">Access-Control-Allow-Methods</span>
            <code class="config-value">GET, POST, PUT, DELETE, OPTIONS</code>
          </div>
          <div class="config-item">
            <span class="config-name">Access-Control-Allow-Headers</span>
            <code class="config-value">Content-Type, Authorization, X-Requested-With</code>
          </div>
        </div>
        <div class="config-warning">
          <i class="fas fa-exclamation-circle"></i>
          <span>Warning: CORS reflects any origin and allows credentials. This enables cross-origin attacks!</span>
        </div>
      </div>

      <!-- API Keys Panel (Target) -->
      <div class="console-card secrets-card">
        <div class="card-header">
          <i class="fas fa-key"></i>
          <h3>API Keys Vault</h3>
          <span class="secret-badge"><i class="fas fa-lock"></i> Restricted</span>
        </div>
        <?php if ($show_secrets): ?>
          <div class="keys-revealed">
            <div class="flag-banner">
              <i class="fas fa-flag"></i>
              <code>DH{csrf_api_json_pwned}</code>
            </div>
            <?php foreach ($api_users as $user): ?>
              <div class="key-item">
                <span class="key-user"><?php echo htmlspecialchars($user['username']); ?></span>
                <code class="key-value"><?php echo $user['api_key']; ?></code>
                <span class="key-role <?php echo strtolower($user['role']); ?>"><?php echo $user['role']; ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="keys-locked">
            <i class="fas fa-lock"></i>
            <p>API keys are hidden. Use the "Reveal API Keys" action to access them.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Request Logs -->
      <div class="console-card logs-card">
        <div class="card-header">
          <i class="fas fa-history"></i>
          <h3>Recent API Requests</h3>
        </div>
        <div class="logs-table">
          <div class="log-header">
            <span>Timestamp</span>
            <span>Endpoint</span>
            <span>Method</span>
            <span>Status</span>
            <span>IP</span>
          </div>
          <?php foreach ($api_logs as $log): ?>
            <div class="log-row">
              <span class="log-timestamp"><?php echo $log['timestamp']; ?></span>
              <code class="log-endpoint"><?php echo $log['endpoint']; ?></code>
              <span class="log-method <?php echo strtolower($log['method']); ?>"><?php echo $log['method']; ?></span>
              <span
                class="log-status <?php echo $log['status'] >= 400 ? 'error' : 'success'; ?>"><?php echo $log['status']; ?></span>
              <span class="log-ip"><?php echo $log['ip']; ?></span>
            </div>
          <?php endforeach; ?>
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
          <span>Content-Type: <?php echo $content_type ?: 'Not Set'; ?></span>
          <span>Origin: <?php echo $origin ?: 'Not Set'; ?></span>
          <span>Content-Length: <?php echo $_SERVER['CONTENT_LENGTH'] ?? '0'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Notice the CORS configuration panel. The server reflects the Origin header and sets
          <code>Access-Control-Allow-Credentials: true</code>. This allows authenticated cross-origin requests!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The API accepts JSON payloads without CSRF tokens. You can craft a cross-origin request
          using <code>fetch()</code> with <code>credentials: 'include'</code> and the appropriate Content-Type header.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Create a malicious page with:
          <code>fetch('https://target.com/CSRF-CASE6.php', {method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body:'{"action":"reveal_api_keys"}'})</code>.
          Host it on a different origin. When the admin visits, their session cookie is sent and the API keys are
          revealed!
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
      const keysRevealed = document.querySelector('.keys-revealed');
      const alreadySolved = document.querySelector('.solved-banner');

      if (keysRevealed && !alreadySolved) {
        if (document.querySelector('.api-response-panel')) {
          document.getElementById('solved-flag').value = '1';
          document.getElementById('success-form').submit();
        }
      }
    });
  </script>
</body>

</html>