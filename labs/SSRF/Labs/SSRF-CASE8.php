<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_hard4_attempts'])) {
  $_SESSION['ssrf_hard4_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_hard4_solved'])) {
  $_SESSION['ssrf_hard4_solved'] = false;
}
if (!isset($_SESSION['ssrf_hard4_stage'])) {
  $_SESSION['ssrf_hard4_stage'] = 1;
}
if (!isset($_SESSION['ssrf_oob_log'])) {
  $_SESSION['ssrf_oob_log'] = [];
}

// ─── Simulated Webhook / Notification Service ────────────────────────────
// This service sends webhook notifications when certain events occur
// The webhook URL is controlled via a header - making it blind SSRF

$webhook_url = isset($_GET['webhook']) ? $_GET['webhook'] : null;
$trigger_event = isset($_GET['event']) ? $_GET['event'] : 'user_login';
$target_user = isset($_GET['user']) ? $_GET['user'] : 'admin';

$exploited = false;
$detection_method = '';
$dns_lookup_detected = false;
$http_callback_detected = false;
$time_delay_detected = false;
$collaborator_hit = false;

// Simulated internal network for port scanning detection
$internal_ports = [22, 80, 443, 3306, 6379, 8080, 8443, 9000];
$scan_results = [];

// ─── Blind SSRF Detection Logic ─────────────────────────────────────────
if ($webhook_url) {
  $parsed = parse_url($webhook_url);
  $host = isset($parsed['host']) ? $parsed['host'] : '';

  // Detect OOB callbacks (Burp Collaborator, RequestBin, webhook.site, etc.)
  $oob_indicators = [
    'burpcollaborator.net',
    'interact.sh',
    'oastify.com',
    'oast.me',
    'requestbin.net',
    'webhook.site',
    'ngrok.io',
    'serveo.net',
    'dnslog.cn',
    'ceye.io',
    'xss.ht',
    'bxss.me'
  ];

  foreach ($oob_indicators as $indicator) {
    if (stripos($host, $indicator) !== false) {
      $collaborator_hit = true;
      $detection_method = "OOB Callback Detected: $indicator";
      $dns_lookup_detected = true;
      $http_callback_detected = true;
      break;
    }
  }

  // Detect DNS-based exfiltration (subdomain enumeration)
  if (preg_match('/^[a-z0-9]{8,}\./i', $host)) {
    $dns_lookup_detected = true;
    if (empty($detection_method)) {
      $detection_method = "DNS Exfiltration Pattern Detected";
    }
  }

  // Detect internal IP scanning attempts
  $internal_patterns = [
    '/^127\./',
    '/^10\./',
    '/^172\.(1[6-9]|2[0-9]|3[01])\./',
    '/^192\.168\./',
    '/^169\.254\./',
    '/^0\.0\.0\.0/',
    '/^::1/',
    '/^fc00:/',
    '/^fe80:/'
  ];

  foreach ($internal_patterns as $pattern) {
    if (preg_match($pattern, $host)) {
      $dns_lookup_detected = true;
      if (empty($detection_method)) {
        $detection_method = "Internal Network Probe Detected: $host";
      }
      break;
    }
  }

  // Detect time-delay / sleep attempts
  if (
    stripos($webhook_url, 'sleep(') !== false ||
    stripos($webhook_url, 'delay') !== false ||
    preg_match('/\?.*(?:sleep|wait|timeout|delay)=\d+/i', $webhook_url)
  ) {
    $time_delay_detected = true;
    if (empty($detection_method)) {
      $detection_method = "Time-Delay Probe Detected";
    }
  }

  // Simulate port scanning via different response times
  if (preg_match('/:(\d+)$/', $webhook_url, $port_match)) {
    $tested_port = intval($port_match[1]);
    if (in_array($tested_port, $internal_ports)) {
      $scan_results[] = "Port $tested_port: OPEN (fast response)";
      $dns_lookup_detected = true;
    } else {
      $scan_results[] = "Port $tested_port: CLOSED (timeout)";
    }
  }

  // Log the OOB attempt
  $_SESSION['ssrf_oob_log'][] = [
    'time' => date('H:i:s'),
    'url' => $webhook_url,
    'method' => $detection_method ?: 'Unknown probe',
    'dns' => $dns_lookup_detected,
    'http' => $http_callback_detected
  ];

  // Determine if exploited
  if ($collaborator_hit || ($dns_lookup_detected && count($_SESSION['ssrf_oob_log']) >= 2)) {
    $exploited = true;
  }
}

$current_stage = $_SESSION['ssrf_hard4_stage'];
$stage_messages = [
  1 => "Stage 1: Understand that responses are NOT returned. You must use indirect detection methods.",
  2 => "Stage 2: Use Out-of-Band (OOB) techniques - DNS callbacks, Burp Collaborator, or time delays to confirm SSRF.",
  3 => "Stage 3: Leverage blind SSRF to port scan, hit internal webhooks, or chain with other vulnerabilities for RCE.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_hard4_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['ssrf_hard4_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['ssrf_hard4_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've mastered Blind SSRF exploitation. Without seeing responses directly, you used out-of-band detection (DNS callbacks / Collaborator) to confirm the server made requests to your controlled endpoint. This is the most elusive SSRF variant - invisible in responses but devastating for internal reconnaissance and vulnerability chaining!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['webhook'])) $_SESSION['ssrf_hard4_attempts']++;
$attempts = $_SESSION['ssrf_hard4_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['ssrf_hard4_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['ssrf_hard4_stage'] = 3;
  $current_stage = 3;
}

// OOB tools reference
$oob_tools = [
  ['name' => 'Burp Collaborator', 'url' => 'https://burpcollaborator.net', 'icon' => 'fa-user-secret'],
  ['name' => 'Interact.sh', 'url' => 'https://interact.sh', 'icon' => 'fa-broadcast-tower'],
  ['name' => 'Webhook.site', 'url' => 'https://webhook.site', 'icon' => 'fa-globe'],
  ['name' => 'RequestBin', 'url' => 'https://requestbin.net', 'icon' => 'fa-inbox'],
  ['name' => 'DNSLog.cn', 'url' => 'http://dnslog.cn', 'icon' => 'fa-dns'],
  ['name' => 'Ceye.io', 'url' => 'http://ceye.io', 'icon' => 'fa-eye'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blind OOB - SSRF Hard 4</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-eye-slash"></i> DarkHunter Blind Webhook</h1>
      <p class="lab-description">A notification service that sends webhooks to user-supplied URLs.
        <strong>You cannot see the response</strong>. Use out-of-band techniques to detect and exploit this blind SSRF!
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already mastered this blind SSRF vulnerability.</p>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
      <div class="success-alert"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Completed!</h3>
          <p><?php echo $success_msg; ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="stage-tracker">
      <div class="stage-header"><i class="fas fa-layer-group"></i><span>Attack Chain Progress</span></div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info"><span class="stage-title">Reconnaissance</span><span class="stage-desc">No response
              visible</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">OOB Detection</span><span class="stage-desc">DNS/HTTP
              callbacks</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Blind Exploitation</span><span class="stage-desc">Internal
              recon & chaining</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="webhook-card">
      <div class="webhook-header">
        <div class="webhook-brand"><i class="fas fa-bell"></i><span>Notification Service</span></div>
        <div class="webhook-badge"><i class="fas fa-ghost"></i><span>Blind SSRF Target</span></div>
      </div>
      <div class="webhook-body">
        <div class="trigger-panel">
          <div class="trigger-header"><i class="fas fa-bolt"></i><span>Event Trigger</span></div>
          <div class="trigger-form">
            <div class="form-row">
              <label>Event Type:</label>
              <code class="form-value"><?php echo htmlspecialchars($trigger_event); ?></code>
            </div>
            <div class="form-row">
              <label>Target User:</label>
              <code class="form-value"><?php echo htmlspecialchars($target_user); ?></code>
            </div>
            <div class="form-row">
              <label>Webhook URL:</label>
              <code
                class="form-value webhook-url-display"><?php echo $webhook_url ? htmlspecialchars($webhook_url) : '<span class="muted">Not configured (default: internal logging)</span>'; ?></code>
            </div>
          </div>
          <div class="trigger-status">
            <div class="status-item <?php echo $dns_lookup_detected ? 'hit' : ''; ?>">
              <i class="fas fa-<?php echo $dns_lookup_detected ? 'check' : 'times'; ?>"></i>
              <span>DNS Lookup</span>
            </div>
            <div class="status-item <?php echo $http_callback_detected ? 'hit' : ''; ?>">
              <i class="fas fa-<?php echo $http_callback_detected ? 'check' : 'times'; ?>"></i>
              <span>HTTP Callback</span>
            </div>
            <div class="status-item <?php echo $time_delay_detected ? 'hit' : ''; ?>">
              <i class="fas fa-<?php echo $time_delay_detected ? 'check' : 'times'; ?>"></i>
              <span>Time Delay</span>
            </div>
          </div>
        </div>

        <div class="detection-panel">
          <div class="detection-header"><i class="fas fa-fingerprint"></i><span>Detection Analysis</span></div>
          <?php if ($detection_method): ?>
            <div class="detection-alert <?php echo $collaborator_hit ? 'critical' : 'warning'; ?>">
              <i class="fas fa-<?php echo $collaborator_hit ? 'exclamation-circle' : 'info-circle'; ?>"></i>
              <div class="detection-content">
                <strong><?php echo htmlspecialchars($detection_method); ?></strong>
                <p>The server attempted an outbound connection to your supplied URL.</p>
              </div>
            </div>
          <?php else: ?>
            <div class="detection-placeholder">
              <i class="fas fa-search"></i>
              <p>No detection events yet. Supply a webhook URL to trigger the notification service.</p>
            </div>
          <?php endif; ?>

          <?php if (!empty($scan_results)): ?>
            <div class="scan-results">
              <h4><i class="fas fa-network-wired"></i> Port Scan Inference</h4>
              <?php foreach ($scan_results as $result): ?>
                <div class="scan-row"><i
                    class="fas fa-<?php echo strpos($result, 'OPEN') !== false ? 'door-open' : 'door-closed'; ?>"></i>
                  <?php echo $result; ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="oob-log-panel">
          <div class="oob-log-header"><i class="fas fa-history"></i><span>OOB Request Log</span></div>
          <div class="oob-log-body">
            <?php if (empty($_SESSION['ssrf_oob_log'])): ?>
              <div class="log-empty"><i class="fas fa-inbox"></i><span>No OOB requests logged yet</span></div>
            <?php else: ?>
              <?php foreach (array_reverse($_SESSION['ssrf_oob_log']) as $log): ?>
                <div class="log-entry">
                  <span class="log-time"><?php echo $log['time']; ?></span>
                  <span class="log-method"><?php echo htmlspecialchars($log['method']); ?></span>
                  <code
                    class="log-url"><?php echo htmlspecialchars(substr($log['url'], 0, 60)) . (strlen($log['url']) > 60 ? '...' : ''); ?></code>
                  <div class="log-indicators">
                    <span class="indicator <?php echo $log['dns'] ? 'active' : ''; ?>" title="DNS"><i
                        class="fas fa-dns"></i></span>
                    <span class="indicator <?php echo $log['http'] ? 'active' : ''; ?>" title="HTTP"><i
                        class="fas fa-globe"></i></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="oob-tools-panel">
      <div class="oob-tools-header"><i class="fas fa-toolbox"></i><span>OOB Detection Arsenal</span></div>
      <div class="oob-tools-body">
        <p class="tools-intro">Use these external services to detect blind SSRF. When the vulnerable server contacts
          them, you'll see the interaction in their logs.</p>
        <div class="tools-grid">
          <?php foreach ($oob_tools as $tool): ?>
            <div class="tool-card">
              <div class="tool-icon"><i class="fas <?php echo $tool['icon']; ?>"></i></div>
              <div class="tool-info">
                <span class="tool-name"><?php echo $tool['name']; ?></span>
                <span class="tool-url"><?php echo $tool['url']; ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="techniques-panel">
      <div class="techniques-header"><i class="fas fa-book-open"></i><span>Blind SSRF Techniques</span></div>
      <div class="techniques-body">
        <div class="technique-row">
          <div class="technique-icon"><i class="fas fa-broadcast-tower"></i></div>
          <div class="technique-info">
            <h4>DNS Callback (OAST)</h4>
            <p>Use unique subdomains: <code>123abc.burpcollaborator.net</code>. DNS resolution confirms the server
              processed your input.</p>
          </div>
        </div>
        <div class="technique-row">
          <div class="technique-icon"><i class="fas fa-clock"></i></div>
          <div class="technique-info">
            <h4>Time-Delay Analysis</h4>
            <p>Compare response times: <code>?webhook=http://127.0.0.1:22</code> (fast=open) vs <code>:9999</code>
              (slow=closed).</p>
          </div>
        </div>
        <div class="technique-row">
          <div class="technique-icon"><i class="fas fa-link"></i></div>
          <div class="technique-info">
            <h4>HTTP Callback</h4>
            <p>Use webhook.site or RequestBin. If you see an HTTP request in logs, the server is vulnerable to SSRF.</p>
          </div>
        </div>
        <div class="technique-row">
          <div class="technique-icon"><i class="fas fa-server"></i></div>
          <div class="technique-info">
            <h4>Internal Service Chaining</h4>
            <p>Blindly sweep internal IPs: <code>?webhook=http://192.168.1.x:8080/api</code> and monitor for DNS hits
              per IP.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code>GET /SSRF-CASE8.php?webhook=<?php echo $webhook_url ? urlencode($webhook_url) : '...'; ?>&event=<?php echo urlencode($trigger_event); ?></code>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Blind SSRF via webhook parameter
$webhook = $_GET['webhook'];
$event = $_GET['event'];

// Server sends notification WITHOUT returning response to user
$ch = curl_init($webhook);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['event' => $event]));
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);  // Result is DISCARDED - user never sees it!

// Attacker uses: ?webhook=http://attacker.com/callback
// Server makes request to attacker.com, confirming SSRF
// But response is NOT shown to attacker - it's BLIND!</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> Blind SSRF is
            invisible in responses but equally dangerous. Attackers use OOB (Out-of-Band) techniques like Burp
            Collaborator, DNS callbacks, and time-delay analysis to confirm and exploit. This enables internal port
            scanning, service enumeration, and chaining with other vulnerabilities for RCE.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The response from the webhook request is NEVER shown. Try using an OOB service like
          <code>?webhook=http://[unique-id].burpcollaborator.net</code> to detect if the server makes a request.
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try time-based detection: <code>?webhook=http://127.0.0.1:22</code> vs
          <code>?webhook=http://127.0.0.1:9999</code>. Different response times indicate open vs closed ports.
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use ANY URL containing a known OOB domain (burpcollaborator.net, interact.sh, webhook.site,
          etc.) OR make 2+ attempts with different internal IPs to trigger detection!</div>
      </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="webhook" value="<?php echo $webhook_url ? htmlspecialchars($webhook_url) : ''; ?>">
    <input type="hidden" name="event" value="<?php echo htmlspecialchars($trigger_event); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const webhook = urlParams.get('webhook') || '';

      // Detect OOB indicators
      const oobPatterns = [
        'burpcollaborator.net', 'interact.sh', 'oastify.com', 'oast.me',
        'requestbin.net', 'webhook.site', 'ngrok.io', 'serveo.net',
        'dnslog.cn', 'ceye.io', 'xss.ht', 'bxss.me'
      ];

      const isOOB = oobPatterns.some(p => webhook.toLowerCase().includes(p));

      // Also check if we have multiple logged attempts (internal scanning)
      const logCount = document.querySelectorAll('.log-entry').length;
      const isScanning = logCount >= 2;

      if ((isOOB || isScanning) && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>