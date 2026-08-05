<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_medium2_attempts'])) {
  $_SESSION['ssrf_medium2_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_medium2_solved'])) {
  $_SESSION['ssrf_medium2_solved'] = false;
}

// ─── Simulated Application: DarkHunter Cloud Configurator ────────────────
$cloud_providers = [
  'aws'   => ['name' => 'AWS EC2', 'metadata' => 'http://169.254.169.254/latest/meta-data/'],
  'gcp'   => ['name' => 'Google Cloud', 'metadata' => 'http://metadata.google.internal/computeMetadata/v1/'],
  'azure' => ['name' => 'Azure VM', 'metadata' => 'http://169.254.169.254/metadata/instance?api-version=2021-02-01'],
];

// ─── Vulnerable Logic: URL fetcher with weak IP blacklist ────────────────
$url = isset($_GET['url']) ? $_GET['url'] : 'http://169.254.169.254/latest/meta-data/';

$is_blocked = false;
$block_reason = '';
$exploited = false;
$response = '';

// Weak blacklist - only blocks exact matches
$blacklist = ['127.0.0.1', 'localhost', '0.0.0.0', '::1'];
foreach ($blacklist as $blocked) {
  if (stripos($url, $blocked) !== false) {
    $is_blocked = true;
    $block_reason = "Access to $blocked is forbidden!";
    break;
  }
}

// But 169.254.169.254 is NOT in the blacklist!
if (!$is_blocked) {
  $metadata_patterns = [
    '169.254.169.254',
    'metadata.google.internal',
    'metadata.azure.internal',
    'alibaba-cloud.internal',
    '100.100.100.200',
  ];
  foreach ($metadata_patterns as $pattern) {
    if (stripos($url, $pattern) !== false) {
      $exploited = true;
      break;
    }
  }
}

if ($exploited) {
  if (stripos($url, '169.254.169.254') !== false) {
    if (stripos($url, 'iam') !== false || stripos($url, 'security-credentials') !== false) {
      $response = "HTTP/1.1 200 OK\nContent-Type: application/json\n\n{\n  \"Code\": \"Success\",\n  \"LastUpdated\": \"2026-05-20T12:00:00Z\",\n  \"Type\": \"AWS-HMAC\",\n  \"AccessKeyId\": \"AKIAIOSFODNN7EXAMPLE\",\n  \"SecretAccessKey\": \"wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY\",\n  \"Token\": \"IQoJb3JpZ2luX2VjEHYaCXVzLWVhc3QtMSJHMEUCIQDTG...\",\n  \"Expiration\": \"2026-05-20T18:00:00Z\"\n}\n\nFlag: DH{ssrf_cloud_iam_pwned}";
    } else {
      $response = "HTTP/1.1 200 OK\nContent-Type: text/plain\n\nami-id\nami-launch-index\nami-manifest-path\nblock-device-mapping/\nevents/\nhostname\nidentity-credentials/\ninstance-action\ninstance-id\ninstance-life-cycle\ninstance-type\nlocal-hostname\nlocal-ipv4\nmac\nmetrics/\nnetwork/\nplacement/\nprofile\npublic-keys/\nreservation-id\nsecurity-groups\nservices/\n\nFlag: DH{ssrf_metadata_access}";
    }
  } elseif (stripos($url, 'metadata.google.internal') !== false) {
    $response = "HTTP/1.1 200 OK\nContent-Type: application/json\nMetadata-Flavor: Google\n\n{\n  \"instance\": {\n    \"id\": 123456789,\n    \"name\": \"darkhunter-server\",\n    \"zone\": \"us-central1-a\"\n  },\n  \"project\": {\n    \"projectId\": \"darkhunter-project\",\n    \"numericProjectId\": 987654321\n  }\n}\n\nFlag: DH{ssrf_gcp_metadata}";
  } else {
    $response = "HTTP/1.1 200 OK\nContent-Type: text/plain\n\nCloud metadata endpoint accessed.\n\nFlag: DH{ssrf_cloud_metadata}";
  }
} elseif ($is_blocked) {
  $response = "HTTP/1.1 403 Forbidden\n\nAccess denied: " . htmlspecialchars($block_reason);
} else {
  $response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><body><h1>External Resource</h1><p>URL: " . htmlspecialchars($url) . "</p></body></html>";
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_medium2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['ssrf_medium2_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['ssrf_medium2_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've exploited an SSRF vulnerability to access cloud metadata endpoints. The application failed to blacklist the link-local address 169.254.169.254, allowing you to steal IAM credentials, instance metadata, and cloud configuration. This is a critical vulnerability in cloud environments!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['url'])) $_SESSION['ssrf_medium2_attempts']++;
$attempts = $_SESSION['ssrf_medium2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cloud Configurator - SSRF Medium 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght=300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-cloud"></i> DarkHunter Cloud Configurator</h1>
      <p class="lab-description">Configure cloud integrations by fetching metadata endpoints. The application blocks
        localhost but <strong>misses cloud metadata IPs</strong>. Can you steal IAM credentials from 169.254.169.254?
      </p>
    </div>
    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this cloud metadata vulnerability.</p>
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

    <div class="cloud-card">
      <div class="cloud-header">
        <div class="cloud-brand"><i class="fas fa-server"></i><span>Cloud Configurator</span></div>
        <div class="cloud-badge"><i class="fas fa-shield-alt"></i><span>IP Blacklist</span></div>
      </div>
      <div class="cloud-body">
        <div class="blacklist-panel">
          <div class="blacklist-header"><i class="fas fa-ban"></i><span>Active Blacklist</span></div>
          <div class="blacklist-items">
            <span class="bl-item blocked"><i class="fas fa-times-circle"></i> 127.0.0.1</span>
            <span class="bl-item blocked"><i class="fas fa-times-circle"></i> localhost</span>
            <span class="bl-item blocked"><i class="fas fa-times-circle"></i> 0.0.0.0</span>
            <span class="bl-item blocked"><i class="fas fa-times-circle"></i> ::1</span>
            <span class="bl-item allowed"><i class="fas fa-check-circle"></i> 169.254.169.254</span>
            <span class="bl-item allowed"><i class="fas fa-check-circle"></i> metadata.google.internal</span>
          </div>
        </div>
        <div class="providers-panel">
          <div class="providers-header"><i class="fas fa-list"></i><span>Cloud Providers</span></div>
          <div class="providers-grid">
            <?php foreach ($cloud_providers as $key => $cp): ?>
              <a href="?url=<?php echo urlencode($cp['metadata']); ?>"
                class="provider-card <?php echo strpos($url, $cp['metadata']) === 0 ? 'active' : ''; ?>">
                <div class="provider-icon"><i class="fas fa-cloud"></i></div>
                <div class="provider-info"><span class="provider-name"><?php echo $cp['name']; ?></span><code
                    class="provider-url"><?php echo $cp['metadata']; ?></code></div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="response-panel">
          <div class="response-header"><i class="fas fa-reply"></i><span>Server Response</span><span
              class="response-badge <?php echo $exploited ? 'exploit' : ($is_blocked ? 'blocked' : 'success'); ?>"><i
                class="fas fa-<?php echo $exploited ? 'bug' : ($is_blocked ? 'ban' : 'check'); ?>"></i>
              <?php echo $exploited ? 'METADATA ACCESS' : ($is_blocked ? '403 BLOCKED' : '200 OK'); ?></span></div>
          <div class="response-body">
            <pre class="response-content"><?php echo htmlspecialchars($response); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <div class="metadata-panel">
      <div class="metadata-header"><i class="fas fa-book-open"></i><span>Cloud Metadata Endpoints</span></div>
      <div class="metadata-body">
        <div class="meta-item"><span class="meta-provider">AWS EC2</span><code
            class="meta-endpoint">http://169.254.169.254/latest/meta-data/</code><span class="meta-desc">Instance
            metadata, IAM credentials</span></div>
        <div class="meta-item"><span class="meta-provider">AWS IAM</span><code
            class="meta-endpoint">http://169.254.169.254/latest/meta-data/iam/security-credentials/</code><span
            class="meta-desc">IAM role credentials</span></div>
        <div class="meta-item"><span class="meta-provider">GCP</span><code
            class="meta-endpoint">http://metadata.google.internal/computeMetadata/v1/</code><span
            class="meta-desc">Project info, instance data</span></div>
        <div class="meta-item"><span class="meta-provider">Azure</span><code
            class="meta-endpoint">http://169.254.169.254/metadata/instance?api-version=2021-02-01</code><span
            class="meta-desc">VM metadata, network info</span></div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body"><code>GET /SSRF-CASE4.php?url=<?php echo urlencode($url); ?></code></div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Incomplete IP blacklist
$url = $_GET['url'];

// Only blocks localhost, misses cloud metadata!
$blacklist = ['127.0.0.1', 'localhost', '0.0.0.0'];
foreach ($blacklist as $blocked) {
    if (strpos($url, $blocked) !== false) {
        die("Access denied!");
    }
}

// Cloud metadata (169.254.169.254) passes through!
$response = file_get_contents($url);</pre>
        </div>
        <div class="vuln-note"><i class="fas fa-exclamation-triangle"></i><span><strong>Vulnerability:</strong> The
            blacklist only covers localhost but misses link-local addresses used by cloud providers (169.254.169.254 for
            AWS/Azure, metadata.google.internal for GCP). These endpoints expose sensitive credentials and
            configuration.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The blacklist blocks localhost but allows other IPs. Cloud providers use
          <code>169.254.169.254</code> for metadata. Try <code>?url=http://169.254.169.254/latest/meta-data/</code>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">For AWS IAM credentials:
          <code>?url=http://169.254.169.254/latest/meta-data/iam/security-credentials/</code>. For GCP:
          <code>?url=http://metadata.google.internal/computeMetadata/v1/</code>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?url=http://169.254.169.254/latest/meta-data/</code> for AWS metadata, or
          <code>?url=http://169.254.169.254/latest/meta-data/iam/security-credentials/</code> for IAM credentials. Any
          cloud metadata endpoint will solve this!
        </div>
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
      const metadataPatterns = ['169.254.169.254', 'metadata.google.internal', 'metadata.azure.internal',
        'alibaba-cloud.internal', '100.100.100.200'
      ];
      const hasMetadata = metadataPatterns.some(p => url.toLowerCase().includes(p.toLowerCase()));
      if (hasMetadata && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>