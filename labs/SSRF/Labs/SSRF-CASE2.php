<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_easy2_attempts'])) {
  $_SESSION['ssrf_easy2_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_easy2_solved'])) {
  $_SESSION['ssrf_easy2_solved'] = false;
}

// ─── Simulated Application: DarkHunter DNS Validator ─────────────────────
// This app "validates" URLs by resolving DNS first, then checking if IP is public
$domains = [
  'google.com'    => ['ip' => '142.250.80.46', 'type' => 'public'],
  'github.com'    => ['ip' => '140.82.121.4', 'type' => 'public'],
  'darkhunter.local' => ['ip' => '10.0.1.50', 'type' => 'private'],
];

// ─── Vulnerable Logic: DNS validation with TOCTOU vulnerability ──────────
$target = isset($_GET['target']) ? $_GET['target'] : 'https://google.com';

// Parse the URL
$parsed = parse_url($target);
$hostname = $parsed['host'] ?? $target;

// "Security": Resolve DNS and check if IP is private
$resolved_ip = '';
$is_private = false;
$validation_passed = false;
$exploited = false;

// Simulate DNS resolution
if (isset($domains[$hostname])) {
  $resolved_ip = $domains[$hostname]['ip'];
  $is_private = ($domains[$hostname]['type'] === 'private');
} else {
  // For attacker-controlled domains, simulate the rebinding attack
  // First resolve to public IP, then after TTL expires, resolve to private
  $resolved_ip = '93.184.216.34'; // Default "public" IP

  // Check for DNS rebinding indicators
  $rebind_indicators = [
    'rebind',
    'evil',
    'attacker',
    'internal',
    'metadata',
    '169.254.169.254',
    '127.0.0.1',
    'localhost',
    '192.168.',
    '10.0.',
    '172.16.'
  ];

  foreach ($rebind_indicators as $ind) {
    if (stripos($hostname, $ind) !== false || stripos($target, $ind) !== false) {
      // Simulate: validation sees public IP, but fetch sees private IP
      $is_private = false; // Validation passes (TOCTOU)
      $exploited = true;
      break;
    }
  }
}

// Validation check (TOCTOU - Time of Check Time of Use)
if (!$is_private) {
  $validation_passed = true;
}

// Simulate the fetch response
$response = '';
if ($exploited) {
  $response = "HTTP/1.1 200 OK\nContent-Type: text/plain\n\n[DNS REBINDING ATTACK SUCCESSFUL]\n\nValidation Phase:\n- Resolved IP: 93.184.216.34 (PUBLIC)\n- Validation: PASSED\n\nFetch Phase (after TTL expiry):\n- Resolved IP: 192.168.1.1 (PRIVATE)\n- Access: GRANTED\n\nInternal Service Response:\nHTTP/1.1 200 OK\nServer: nginx/1.18.0\nContent-Type: application/json\n\n{\"status\": \"internal_api\", \"version\": \"2.1.0\", \"admin\": true}\n\nFlag: DH{ssrf_dns_rebinding_pwned}";
} elseif ($validation_passed) {
  if (isset($domains[$hostname]) && $domains[$hostname]['type'] === 'private') {
    $response = "HTTP/1.1 403 Forbidden\nContent-Type: text/plain\n\nAccess denied: Private IP range detected.";
  } else {
    $response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><body><h1>External Resource</h1><p>Successfully fetched: " . htmlspecialchars($target) . "</p></body></html>";
  }
} else {
  $response = "HTTP/1.1 403 Forbidden\nContent-Type: text/plain\n\nAccess denied: Private IP range detected.";
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_easy2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['ssrf_easy2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['ssrf_easy2_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited a DNS Rebinding SSRF vulnerability. The application validated the IP at check-time (public), but the DNS record changed before fetch-time (private). This TOCTOU (Time-of-Check-Time-of-Use) bypass is a classic SSRF technique!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['target'])) {
  $_SESSION['ssrf_easy2_attempts']++;
}

$attempts = $_SESSION['ssrf_easy2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DNS Validator - SSRF Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-sync-alt"></i> DarkHunter DNS Validator</h1>
      <p class="lab-description">A secure URL fetcher that validates DNS resolution before fetching. The application
        resolves the hostname and rejects private IPs. <strong>But what if the DNS changes between validation and
          fetch?</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this DNS Rebinding vulnerability. You can continue exploring, but no additional
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

    <!-- DNS Validator Interface -->
    <div class="validator-card">
      <div class="validator-header">
        <div class="validator-brand">
          <i class="fas fa-shield-alt"></i>
          <span>DNS Security Validator</span>
        </div>
        <div class="validator-badge">
          <i class="fas fa-lock"></i>
          <span>IP Whitelist</span>
        </div>
      </div>

      <div class="validator-body">
        <!-- Validation Flow -->
        <div class="validation-panel">
          <div class="validation-header">
            <i class="fas fa-tasks"></i>
            <span>Validation Process</span>
          </div>
          <div class="validation-flow">
            <div class="flow-step">
              <div class="step-num">1</div>
              <div class="step-content">
                <span class="step-title">URL Parsing</span>
                <code class="step-detail">Target: <?php echo htmlspecialchars($target); ?></code>
              </div>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>
            <div class="flow-step">
              <div class="step-num">2</div>
              <div class="step-content">
                <span class="step-title">DNS Resolution</span>
                <code class="step-detail">Resolved IP: <?php echo $resolved_ip; ?></code>
              </div>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>
            <div class="flow-step">
              <div class="step-num">3</div>
              <div class="step-content">
                <span class="step-title">IP Validation</span>
                <span class="step-detail <?php echo $is_private ? 'blocked' : 'passed'; ?>">
                  <i class="fas fa-<?php echo $is_private ? 'times-circle' : 'check-circle'; ?>"></i>
                  <?php echo $is_private ? 'BLOCKED (Private IP)' : 'PASSED (Public IP)'; ?>
                </span>
              </div>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>
            <div class="flow-step">
              <div class="step-num">4</div>
              <div class="step-content">
                <span class="step-title">HTTP Fetch</span>
                <span class="step-detail <?php echo $validation_passed ? 'passed' : 'blocked'; ?>">
                  <i class="fas fa-<?php echo $validation_passed ? 'check-circle' : 'times-circle'; ?>"></i>
                  <?php echo $validation_passed ? 'FETCH EXECUTED' : 'FETCH BLOCKED'; ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Response -->
        <div class="response-panel">
          <div class="response-header">
            <i class="fas fa-reply"></i>
            <span>Server Response</span>
            <span
              class="response-badge <?php echo $exploited ? 'exploit' : ($validation_passed ? 'success' : 'blocked'); ?>">
              <i class="fas fa-<?php echo $exploited ? 'bug' : ($validation_passed ? 'check' : 'ban'); ?>"></i>
              <?php echo $exploited ? 'REBINDING DETECTED' : ($validation_passed ? '200 OK' : '403 BLOCKED'); ?>
            </span>
          </div>
          <div class="response-body">
            <pre class="response-content"><?php echo htmlspecialchars($response); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- DNS Rebinding Explanation -->
    <div class="rebind-panel">
      <div class="rebind-header">
        <i class="fas fa-graduation-cap"></i>
        <span>How DNS Rebinding Works</span>
      </div>
      <div class="rebind-body">
        <div class="rebind-flow">
          <div class="rb-step">
            <div class="rb-icon"><i class="fas fa-globe"></i></div>
            <div class="rb-content">
              <span class="rb-title">Attacker Setup</span>
              <code>evil.com → 93.184.216.34 (public)</code>
            </div>
          </div>
          <div class="rb-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="rb-step">
            <div class="rb-icon"><i class="fas fa-check-circle"></i></div>
            <div class="rb-content">
              <span class="rb-title">Validation</span>
              <code>App checks: 93.184.216.34 = PUBLIC ✓</code>
            </div>
          </div>
          <div class="rb-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="rb-step">
            <div class="rb-icon"><i class="fas fa-sync"></i></div>
            <div class="rb-content">
              <span class="rb-title">TTL Expires</span>
              <code>evil.com → 192.168.1.1 (private)</code>
            </div>
          </div>
          <div class="rb-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="rb-step">
            <div class="rb-icon"><i class="fas fa-bug"></i></div>
            <div class="rb-content">
              <span class="rb-title">Exploit</span>
              <code>App fetches: 192.168.1.1 = INTERNAL!</code>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /SSRF-CASE2.php?target=<?php echo urlencode($target); ?></code>
      </div>
    </div>

    <!-- Vulnerable Code Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-bug"></i>
        <span>Vulnerable Code Snippet</span>
      </div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: TOCTOU in DNS validation
$target = $_GET['target'];
$host = parse_url($target, PHP_URL_HOST);

// TIME OF CHECK: Resolve DNS
$ip = gethostbyname($host);

// Check if private
if (isPrivateIP($ip)) {
    die("Private IP blocked!");
}

// TIME OF USE: Fetch URL (DNS may have changed!)
// Attacker changes DNS record between check and use
$response = file_get_contents($target);</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> The application resolves DNS and validates the IP, but then fetches the
            URL separately. An attacker can change the DNS record between these two operations (TOCTOU -
            Time-of-Check-Time-of-Use).</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The application validates DNS before fetching. But DNS records can change! Try using a
        domain name that simulates DNS rebinding behavior.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try domains containing keywords that trigger the rebinding simulation: <code>rebind</code>,
        <code>evil</code>, <code>internal</code>, <code>metadata</code>. Or try
        <code>?target=http://rebind.darkhunter.local</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?target=http://rebind.internal</code> or any target containing
        <code>rebind</code>, <code>evil</code>, <code>internal</code>, or <code>metadata</code> keywords to simulate a
        successful DNS rebinding attack!
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
    <input type="hidden" name="target" value="<?php echo htmlspecialchars($target); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const target = urlParams.get('target') || '';

    const rebindPatterns = [
      'rebind', 'evil', 'attacker', 'internal', 'metadata',
      '169.254.169.254', '127.0.0.1', 'localhost',
      '192.168.', '10.0.', '172.16.'
    ];

    const isRebind = rebindPatterns.some(pattern =>
      target.toLowerCase().includes(pattern.toLowerCase())
    );

    if (isRebind && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>