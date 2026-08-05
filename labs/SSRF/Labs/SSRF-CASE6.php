<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_hard2_attempts'])) {
  $_SESSION['ssrf_hard2_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_hard2_solved'])) {
  $_SESSION['ssrf_hard2_solved'] = false;
}
if (!isset($_SESSION['ssrf_hard2_stage'])) {
  $_SESSION['ssrf_hard2_stage'] = 1;
}

// ─── Simulated Application: DarkHunter HTTP Proxy ────────────────────────
$proxy_targets = [
  'api'   => ['name' => 'Internal API', 'host' => 'api.darkhunter.local', 'port' => 8080],
  'cache' => ['name' => 'Redis Cache', 'host' => 'cache.darkhunter.local', 'port' => 6379],
  'queue' => ['name' => 'Message Queue', 'host' => 'queue.darkhunter.local', 'port' => 5672],
];

// ─── Vulnerable Logic: URL parameter injected into HTTP headers ──────────
$host = isset($_GET['host']) ? $_GET['host'] : 'api.darkhunter.local';

$exploited = false;
$response = '';
$injected_headers = [];

// Check for CRLF injection
$crlf_patterns = ['%0d%0a', '%0a', '%0d', '\\r\\n', '\\n', '\\r'];
foreach ($crlf_patterns as $crlf) {
  if (stripos($host, $crlf) !== false) {
    $exploited = true;
    break;
  }
}

if ($exploited) {
  $injected_headers = [
    'Injected: X-Forwarded-For: 127.0.0.1',
    'Injected: X-Custom-Auth: admin',
    'Injected: Content-Length: 0',
    'Injected: Connection: close',
  ];
  $response = "HTTP/1.1 200 OK\nServer: nginx/1.18.0\nContent-Type: text/html\nX-Injected-By: CRLF_Attack\n\n<html><body><h1>CRLF Injection Successful</h1><p>The proxy accepted injected headers!</p><p>Backend processed:</p><ul><li>X-Forwarded-For: 127.0.0.1 (bypass IP filter)</li><li>X-Custom-Auth: admin (privilege escalation)</li></ul><hr><p><strong>Flag: DH{ssrf_crlf_injection_pwned}</strong></p></body></html>";
} else {
  $response = "HTTP/1.1 200 OK\nContent-Type: application/json\n\n{\"status\": \"connected\", \"host\": \"" . htmlspecialchars($host) . "\", \"port\": 8080}";
}

$current_stage = $_SESSION['ssrf_hard2_stage'];
$stage_messages = [
  1 => "Stage 1: Understand how the proxy constructs HTTP requests from user input.",
  2 => "Stage 2: Identify CRLF sequences that can split HTTP requests and inject headers.",
  3 => "Stage 3: Inject malicious headers to bypass authentication and access internal services.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_hard2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['ssrf_hard2_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['ssrf_hard2_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've exploited an SSRF vulnerability through CRLF injection. By injecting carriage return and line feed sequences (%0d%0a) into the host parameter, you split the HTTP request and injected arbitrary headers. This allowed you to bypass IP-based authentication and access internal services!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['host'])) $_SESSION['ssrf_hard2_attempts']++;
$attempts = $_SESSION['ssrf_hard2_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['ssrf_hard2_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['ssrf_hard2_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HTTP Proxy - SSRF Hard 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght=400;700;900&family=Inter:wght=300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-exchange-alt"></i> DarkHunter HTTP Proxy</h1>
      <p class="lab-description">An internal HTTP proxy that forwards requests to backend services. The application
        injects the host parameter directly into HTTP headers. <strong>Can you inject CRLF sequences</strong> to split
        requests and inject malicious headers?</p>
    </div>
    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this CRLF injection vulnerability.</p>
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
          <div class="stage-info"><span class="stage-title">Reconnaissance</span><span class="stage-desc">Map proxy
              behavior</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">Header Injection</span><span class="stage-desc">Craft CRLF
              payload</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Auth Bypass</span><span class="stage-desc">Access internal
              services</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="proxy-card">
      <div class="proxy-header">
        <div class="proxy-brand"><i class="fas fa-server"></i><span>HTTP Proxy</span></div>
        <div class="proxy-badge"><i class="fas fa-project-diagram"></i><span>Request Forwarder</span></div>
      </div>
      <div class="proxy-body">
        <div class="request-panel">
          <div class="request-header"><i class="fas fa-paper-plane"></i><span>Outgoing Request</span></div>
          <div class="request-preview">
            <pre class="request-content">GET /api/data HTTP/1.1
Host: <?php echo htmlspecialchars($host); ?>
User-Agent: DarkHunter-Proxy/2.1
Accept: */*
Connection: keep-alive

<?php if ($exploited): ?><span class="injected-headers">
[INJECTED HEADERS DETECTED]
<?php foreach ($injected_headers as $h): ?><?php echo $h; ?>
<?php endforeach; ?></span><?php endif; ?></pre>
          </div>
        </div>
        <div class="targets-panel">
          <div class="targets-header"><i class="fas fa-bullseye"></i><span>Backend Targets</span></div>
          <div class="targets-grid">
            <?php foreach ($proxy_targets as $key => $pt): ?>
            <a href="?host=<?php echo urlencode($pt['host']); ?>"
              class="target-card <?php echo $host === $pt['host'] ? 'active' : ''; ?>">
              <div class="target-icon"><i class="fas fa-database"></i></div>
              <div class="target-info"><span class="target-name"><?php echo $pt['name']; ?></span><code
                  class="target-addr"><?php echo $pt['host']; ?>:<?php echo $pt['port']; ?></code></div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="response-panel">
          <div class="response-header"><i class="fas fa-reply"></i><span>Backend Response</span><span
              class="response-badge <?php echo $exploited ? 'exploit' : 'success'; ?>"><i
                class="fas fa-<?php echo $exploited ? 'bug' : 'check'; ?>"></i>
              <?php echo $exploited ? 'CRLF INJECTED' : '200 OK'; ?></span></div>
          <div class="response-body">
            <pre class="response-content"><?php echo htmlspecialchars($response); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <div class="crlf-panel">
      <div class="crlf-header"><i class="fas fa-code"></i><span>CRLF Injection Reference</span></div>
      <div class="crlf-body">
        <div class="crlf-row"><span class="crlf-name">URL-encoded CRLF</span><code class="crlf-payload">%0d%0a</code>
        </div>
        <div class="crlf-row"><span class="crlf-name">URL-encoded LF</span><code class="crlf-payload">%0a</code></div>
        <div class="crlf-row"><span class="crlf-name">URL-encoded CR</span><code class="crlf-payload">%0d</code></div>
        <div class="crlf-row"><span class="crlf-name">Double-encoded CRLF</span><code
            class="crlf-payload">%250d%250a</code></div>
        <div class="crlf-row"><span class="crlf-name">Unicode newline</span><code class="crlf-payload">%u000a</code>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body"><code>GET /SSRF-CASE6.php?host=<?php echo urlencode($host); ?></code></div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Direct header injection
$host = $_GET['host'];

// Injects user input directly into HTTP headers!
$request = "GET /api/data HTTP/1.1\r\n";
$request .= "Host: $host\r\n";  // INJECTION POINT!
$request .= "User-Agent: Proxy/2.1\r\n";
$request .= "\r\n";

// If $host = "api.darkhunter.local%0d%0aX-Auth: admin"
// Result: Injected X-Auth header!</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> CRLF injection
            allows attackers to split HTTP requests and inject arbitrary headers. This can bypass IP-based auth
            (X-Forwarded-For), escalate privileges (X-Admin: true), or perform HTTP request smuggling.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The host parameter is injected directly into HTTP headers. Try adding CRLF sequences:
        <code>?host=api.darkhunter.local%0d%0aX-Forwarded-For:%20127.0.0.1</code>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try injecting authentication headers: <code>%0d%0aX-Custom-Auth:%20admin</code> or
        <code>%0d%0aX-Real-IP:%20127.0.0.1</code>. Each %0d%0a creates a new line in the HTTP request.
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?host=api.darkhunter.local%0d%0aX-Injected:%20test</code> or any host containing
        <code>%0d%0a</code>, <code>%0a</code>, or <code>%0d</code> to trigger CRLF injection and header splitting!
      </div>
    </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="host" value="<?php echo htmlspecialchars($host); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const host = urlParams.get('host') || '';
    const crlfPatterns = ['%0d%0a', '%0a', '%0d', '\\r\\n', '\\n', '\\r'];
    const hasCRLF = crlfPatterns.some(p => host.toLowerCase().includes(p.toLowerCase()));
    if (hasCRLF && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>