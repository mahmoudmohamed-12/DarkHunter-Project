<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_medium1_attempts'])) {
$_SESSION['ssrf_medium1_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_medium1_solved'])) {
$_SESSION['ssrf_medium1_solved'] = false;
}

// ─── Simulated Application: DarkHunter Webhook Processor ─────────────────
$webhooks = [
'slack'     => ['name' => 'Slack Notifications', 'url' => 'https://hooks.slack.com/services/'],
'discord'   => ['name' => 'Discord Webhook', 'url' => 'https://discord.com/api/webhooks/'],
'teams'     => ['name' => 'MS Teams', 'url' => 'https://outlook.office.com/webhook/'],
];

// ─── Vulnerable Logic: Accepts any protocol handler ──────────────────────
$hook = isset($_GET['hook']) ? $_GET['hook'] : 'https://hooks.slack.com/services/test';

$protocol = parse_url($hook, PHP_URL_SCHEME) ?: 'https';
$response = '';
$exploited = false;
$protocol_used = strtoupper($protocol);

// Check for alternative protocol abuse
$alt_protocols = ['file', 'jar', 'gopher', 'ftp', 'dict', 'ldap', 'tftp', 'php', 'data', 'expect'];
if (in_array(strtolower($protocol), $alt_protocols)) {
$exploited = true;
}

// Simulate protocol-specific responses
if ($exploited) {
$protocol_responses = [
'file' => "HTTP/1.1 200 OK\nContent-Type: text/plain\n\nroot:x:0:0:root:/root:/bin/bash\ndaemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin\n\nFlag: DH{ssrf_file_protocol_pwned}",
'jar' => "HTTP/1.1 200 OK\nContent-Type: application/java-archive\n\nPK\x03\x04\n\x00\x00\x00\x00\x00\x00\x00!META-INF/MANIFEST.MF\n\nFlag: DH{ssrf_jar_protocol}",
'gopher' => "HTTP/1.1 200 OK\nContent-Type: text/plain\n\n+OK POP3 server ready\nUSER admin\n+OK\nPASS secret123\n+OK Logged in.\n\nFlag: DH{ssrf_gopher_protocol}",
'ftp' => "220 Welcome to DarkHunter FTP\n230 Anonymous access granted\n\nFlag: DH{ssrf_ftp_protocol}",
'dict' => "220 localhost dictd 1.12.1\n\nFlag: DH{ssrf_dict_protocol}",
'ldap' => "LDAP://localhost:389\n\nFlag: DH{ssrf_ldap_protocol}",
'tftp' => "TFTP config file contents...\n\nFlag: DH{ssrf_tftp_protocol}",
'php' => "<?php system('id'); ?>\n\nFlag: DH{ssrf_php_filter}",
'data' => "data://text/plain;base64,SGVsbG8gV29ybGQ=\n\nFlag: DH{ssrf_data_protocol}",
'expect' => "expect://id\n\nuid=33(www-data) gid=33(www-data) groups=33(www-data)\n\nFlag: DH{ssrf_expect_rce}",
];
$response = $protocol_responses[strtolower($protocol)] ?? "HTTP/1.1 200 OK\n\nAlternative protocol detected.\n\nFlag:
DH{ssrf_protocol_abuse}";
} else {
$response = "HTTP/1.1 200 OK\nContent-Type: application/json\n\n{\"status\": \"delivered\", \"webhook\": \"" .
htmlspecialchars($hook) . "\"}";
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_medium1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
$_SESSION['ssrf_medium1_attempts']++;
if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
$_SESSION['ssrf_medium1_solved'] = true;
$already_solved = true;
$success_msg = "Outstanding! You've exploited an SSRF vulnerability through protocol abuse. By using alternative
protocols (file://, gopher://, jar://, ftp://, dict://), you bypassed HTTP-only restrictions and accessed internal
resources or achieved RCE!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['hook'])) $_SESSION['ssrf_medium1_attempts']++;
$attempts = $_SESSION['ssrf_medium1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Webhook Processor - SSRF Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-satellite-dish"></i> DarkHunter Webhook Processor</h1>
      <p class="lab-description">Process webhooks for third-party integrations. The application accepts URLs without
        protocol validation. <strong>Can you abuse alternative protocols</strong> to read files or access internal
        services?</p>
    </div>
    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this protocol abuse vulnerability.</p>
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

    <div class="webhook-card">
      <div class="webhook-header">
        <div class="webhook-brand"><i class="fas fa-plug"></i><span>Webhook Processor</span></div>
        <div class="webhook-badge"><i class="fas fa-code-branch"></i><span>v2.4</span></div>
      </div>
      <div class="webhook-body">
        <div class="protocol-panel">
          <div class="protocol-header"><i class="fas fa-exchange-alt"></i><span>Protocol Analysis</span></div>
          <div class="protocol-info">
            <div class="info-row"><span class="info-label">Input URL:</span><code
                class="info-value"><?php echo htmlspecialchars($hook); ?></code></div>
            <div class="info-row"><span class="info-label">Detected Protocol:</span><span
                class="protocol-badge <?php echo strtolower($protocol); ?>"><?php echo $protocol_used; ?></span></div>
            <div class="info-row"><span class="info-label">Validation:</span><span
                class="info-value <?php echo $exploited ? 'blocked' : 'passed'; ?>"><i
                  class="fas fa-<?php echo $exploited ? 'times-circle' : 'check-circle'; ?>"></i>
                <?php echo $exploited ? 'ALTERNATIVE PROTOCOL' : 'HTTP/HTTPS ONLY'; ?></span></div>
          </div>
        </div>
        <div class="endpoints-panel">
          <div class="endpoints-header"><i class="fas fa-list"></i><span>Configured Endpoints</span></div>
          <div class="endpoints-grid">
            <?php foreach ($webhooks as $key => $wh): ?>
            <a href="?hook=<?php echo urlencode($wh['url']); ?>"
              class="endpoint-card <?php echo strpos($hook, $wh['url']) === 0 ? 'active' : ''; ?>">
              <div class="endpoint-icon"><i class="fas fa-bell"></i></div>
              <div class="endpoint-info"><span class="endpoint-name"><?php echo $wh['name']; ?></span><code
                  class="endpoint-url"><?php echo $wh['url']; ?></code></div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="response-panel">
          <div class="response-header"><i class="fas fa-reply"></i><span>Server Response</span><span
              class="response-badge <?php echo $exploited ? 'exploit' : 'success'; ?>"><i
                class="fas fa-<?php echo $exploited ? 'bug' : 'check'; ?>"></i>
              <?php echo $exploited ? 'PROTOCOL ABUSE' : '200 OK'; ?></span></div>
          <div class="response-body">
            <pre class="response-content"><?php echo htmlspecialchars($response); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <div class="protocol-matrix">
      <div class="matrix-header"><i class="fas fa-th"></i><span>Protocol Attack Matrix</span></div>
      <div class="matrix-body">
        <div class="matrix-row header"><span>Protocol</span><span>Purpose</span><span>Example Payload</span></div>
        <div class="matrix-row"><code>file://</code><span>Read local files</span><code>file:///etc/passwd</code></div>
        <div class="matrix-row"><code>gopher://</code><span>Internal network
            access</span><code>gopher://127.0.0.1:3306/</code></div>
        <div class="matrix-row"><code>jar://</code><span>Access JAR
            contents</span><code>jar:file:///etc/passwd!/META-INF</code></div>
        <div class="matrix-row"><code>ftp://</code><span>FTP protocol
            access</span><code>ftp://anonymous@ftp.local/</code></div>
        <div class="matrix-row"><code>dict://</code><span>Dictionary protocol</span><code>dict://localhost:11211/</code>
        </div>
        <div class="matrix-row"><code>ldap://</code><span>LDAP directory access</span><code>ldap://localhost:389/</code>
        </div>
        <div class="matrix-row"><code>php://</code><span>PHP filters</span><code>php://filter/read=...</code></div>
        <div class="matrix-row"><code>data://</code><span>Data URI</span><code>data://text/plain;base64,...</code></div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body"><code>GET /SSRF-CASE3.php?hook=<?php echo urlencode($hook); ?></code></div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: No protocol validation
$hook = $_GET['hook'];

// Only checks URL format, not protocol!
if (filter_var($hook, FILTER_VALIDATE_URL)) {
    // Accepts ANY protocol: file://, gopher://, jar://, etc.
    $response = file_get_contents($hook);
}</pre>
        </div>
        <div class="vuln-note"><i class="fas fa-exclamation-triangle"></i><span><strong>Vulnerability:</strong> The
            application validates URL format but doesn't restrict allowed protocols. Alternative protocols like file://,
            gopher://, and jar:// bypass HTTP-only restrictions completely.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The application accepts any valid URL. Try using <code>file://</code> to read local files:
        <code>?hook=file:///etc/passwd</code>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Other powerful protocols: <code>gopher://127.0.0.1:3306/</code> (MySQL),
        <code>jar:file:///etc/passwd!/META-INF</code> (JAR), <code>ftp://anonymous@ftp.local/</code> (FTP).
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?hook=file:///etc/passwd</code> for local file read, or
        <code>?hook=gopher://127.0.0.1:3306/</code> for internal service access. Any non-HTTP protocol will solve this!
      </div>
    </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="hook" value="<?php echo htmlspecialchars($hook); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const hook = urlParams.get('hook') || '';
    const protocols = ['file://', 'jar:', 'gopher://', 'ftp://', 'dict://', 'ldap://', 'tftp://', 'php://',
      'data://', 'expect://'
    ];
    const hasAltProtocol = protocols.some(p => hook.toLowerCase().startsWith(p.toLowerCase()));
    if (hasAltProtocol && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>