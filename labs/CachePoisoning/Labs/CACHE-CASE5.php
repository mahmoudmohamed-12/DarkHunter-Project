<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case5_attempts'])) {
  $_SESSION['cache_case5_attempts'] = 0;
}
if (!isset($_SESSION['cache_case5_solved'])) {
  $_SESSION['cache_case5_solved'] = false;
}

// ─── Simulated CDN Edge Logic ────────────────────────────────────────────
$edge_poisoned = false;
$flag_triggered = false;
$target_region = null;
$cache_status = 'MISS';

// VULNERABLE: Geographic cache without proper isolation
$regions = [
  'us-east' => ['edge' => 'iad-01', 'status' => 'active'],
  'us-west' => ['edge' => 'sfo-01', 'status' => 'active'],
  'eu-west' => ['edge' => 'lon-01', 'status' => 'active'],
  'ap-south' => ['edge' => 'bom-01', 'status' => 'active'],
  'admin' => ['edge' => 'master-01', 'status' => 'admin'],
];

// Get region from header or param
$region = $_SERVER['HTTP_X_REGION'] ?? $_GET['region'] ?? 'us-east';
$edge_server = $regions[$region]['edge'] ?? 'unknown';

// VULNERABLE: Cache keys don't include region properly
$cache_key = md5($_SERVER['REQUEST_URI']);
$cache_file = sys_get_temp_dir() . '/cache_cdn_' . $cache_key . '.html';

// Check cache
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
  $cache_status = 'HIT';
  $cached_content = file_get_contents($cache_file);
  if (
    strpos($cached_content, 'EDGE_POISONED') !== false ||
    strpos($cached_content, 'CDN') !== false
  ) {
    $edge_poisoned = true;
  }
} else {
  $cache_status = 'MISS';
}

// VULNERABLE: Region-based poisoning
if (isset($_GET['poison_edge']) || isset($_SERVER['HTTP_X_POISON'])) {
  $payload = $_GET['poison_edge'] ?? $_SERVER['HTTP_X_POISON'] ?? '';

  if (
    stripos($payload, 'EDGE_POISONED') !== false ||
    stripos($payload, 'CDN') !== false ||
    stripos($payload, '<script>') !== false ||
    $region === 'admin'
  ) {
    $edge_poisoned = true;
    $target_region = $region;
    $flag_triggered = true;

    // VULNERABLE: Poison propagates to all regions
    $poisoned_content = "<!-- EDGE_POISONED via $edge_server ($region) -->\n";
    $poisoned_content .= "<div class='edge-alert'>CDN Edge Poisoned: $edge_server</div>\n";
    file_put_contents($cache_file, $poisoned_content);
  }
}

// Admin region bypass
if ($region === 'admin' || $edge_server === 'master-01') {
  $edge_poisoned = true;
  $flag_triggered = true;
  $target_region = 'admin';
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case5_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case5_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case5_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've successfully exploited a CDN Edge Cache Poisoning vulnerability. By targeting specific edge servers or bypassing geographic isolation, you poisoned the CDN cache and caused malicious content to be distributed globally!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if (!empty($_GET) || isset($_SERVER['HTTP_X_REGION']) || isset($_SERVER['HTTP_X_POISON'])) {
  $_SESSION['cache_case5_attempts']++;
}

$attempts = $_SESSION['cache_case5_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CDN Dashboard - CachePoison Case 5 (CDN Edge)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-globe-americas"></i> CDN Dashboard</h1>
      <p class="lab-description">Monitor our global CDN. This hard Cache Poisoning challenge has
        <strong>geographic cache distribution vulnerabilities</strong>. <strong>Poison specific regions or bypass edge
          isolation!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this CDN Edge Cache Poisoning vulnerability.</p>
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

    <!-- Edge Status -->
    <div class="edge-status-bar">
      <div class="edge-indicator">
        <i class="fas fa-server"></i>
        <span>Edge: <strong><?php echo htmlspecialchars($edge_server); ?></strong></span>
      </div>
      <div class="region-indicator">
        <i class="fas fa-map-marker-alt"></i>
        <span>Region: <strong><?php echo htmlspecialchars($region); ?></strong></span>
      </div>
      <div class="cache-indicator <?php echo $cache_status === 'HIT' ? 'hit' : 'miss'; ?>">
        <i class="fas fa-<?php echo $cache_status === 'HIT' ? 'database' : 'cloud'; ?>"></i>
        <span>Cache <?php echo $cache_status; ?></span>
      </div>
      <?php if ($edge_poisoned): ?>
        <div class="poison-indicator">
          <i class="fas fa-skull-crossbones"></i>
          <span>EDGE POISONED!</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Edge Map -->
      <div class="cache-card map-card">
        <div class="card-header">
          <i class="fas fa-globe"></i>
          <h3>Edge Servers</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Region Bypass</span>
        </div>
        <div class="map-content">
          <?php foreach ($regions as $reg => $data): ?>
            <div
              class="server-item <?php echo $reg === $region ? 'active' : ''; ?> <?php echo $data['status'] === 'admin' ? 'admin' : ''; ?>">
              <div class="server-icon">
                <i class="fas fa-<?php echo $data['status'] === 'admin' ? 'shield-alt' : 'server'; ?>"></i>
              </div>
              <div class="server-info">
                <span class="server-name"><?php echo htmlspecialchars($data['edge']); ?></span>
                <span class="server-region"><?php echo htmlspecialchars($reg); ?></span>
              </div>
              <span
                class="server-status <?php echo $data['status']; ?>"><?php echo htmlspecialchars($data['status']); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Region Selector -->
      <div class="cache-card selector-card">
        <div class="card-header">
          <i class="fas fa-mouse-pointer"></i>
          <h3>Region Selector</h3>
        </div>
        <div class="selector-content">
          <p class="selector-info">Select a region to test:</p>
          <div class="region-buttons">
            <?php foreach ($regions as $reg => $data): ?>
              <a href="?region=<?php echo $reg; ?>"
                class="region-btn <?php echo $reg === $region ? 'active' : ''; ?> <?php echo $data['status'] === 'admin' ? 'admin' : ''; ?>">
                <i class="fas fa-<?php echo $data['status'] === 'admin' ? 'shield-alt' : 'server'; ?>"></i>
                <span><?php echo htmlspecialchars($reg); ?></span>
              </a>
            <?php endforeach; ?>
          </div>

          <div class="header-test">
            <p class="test-info">Or test via header:</p>
            <code class="header-example">curl -H "X-Region: admin" URL</code>
          </div>
        </div>
      </div>

      <!-- CDN Analysis -->
      <div class="cache-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>CDN Isolation Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Region Key:</span>
            <code class="analysis-code">// Not included in cache key!</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Edge Propagation:</span>
            <code class="analysis-code">// Poison spreads globally</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Vulnerable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Admin Access:</span>
            <code class="analysis-code">// Admin region not protected</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Bypassable</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>CDN Edge Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Region Header Poison</div>
            <code class="payload-code">curl -H "X-Region: us-west" -H "X-Poison: EDGE_POISONED" URL</code>
            <span class="payload-target">Poison specific region</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Admin Region Bypass</div>
            <code class="payload-code">?region=admin&poison_edge=CDN</code>
            <span class="payload-target">Access admin edge server</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Cross-Region Propagation</div>
            <code class="payload-code">curl -H "X-Region: ap-south" URL</code>
            <span class="payload-target">Propagate poison globally</span>
          </div>
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
          <span>Region: <?php echo htmlspecialchars($region); ?></span>
          <span>Edge: <?php echo htmlspecialchars($edge_server); ?></span>
          <span>Cache Status: <?php echo $cache_status; ?></span>
          <span>Poisoned: <?php echo $edge_poisoned ? 'YES' : 'NO'; ?></span>
          <span>Target: <?php echo $target_region ? htmlspecialchars($target_region) : 'NONE'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The CDN doesn't properly isolate regions in cache keys. Try changing the region to
          <code>admin</code> or sending <code>X-Region: admin</code> header. The admin edge server has no additional
          protection!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try adding <code>?region=admin&poison_edge=EDGE_POISONED</code> to the URL. The admin
          edge server will process your request without authorization, and the poison will propagate to all regions!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Click the "admin" region button or add <code>?region=admin</code> to the URL. Then add
          <code>&poison_edge=CDN</code> to trigger the poison. The admin edge server has no access controls, and the
          poison spreads across all CDN nodes!
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
      const poisonIndicator = document.querySelector('.poison-indicator');
      const alreadySolved = document.querySelector('.solved-banner');

      if (poisonIndicator && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>