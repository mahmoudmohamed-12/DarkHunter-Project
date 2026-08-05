<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case8_attempts'])) {
  $_SESSION['cache_case8_attempts'] = 0;
}
if (!isset($_SESSION['cache_case8_solved'])) {
  $_SESSION['cache_case8_solved'] = false;
}

// ─── Multi-Layer Cache Chain Logic ───────────────────────────────────────
$chain_poisoned = false;
$flag_triggered = false;
$poisoned_layers = [];

// Layer 1: Browser Cache (ETag/Last-Modified)
$browser_cached = false;
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) || isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
  $browser_cached = true;
}

// Layer 2: CDN Cache (X-Cache header simulation)
$cdn_cached = false;
if (isset($_GET['cdn_poison']) || isset($_SERVER['HTTP_X_CDN'])) {
  $cdn_cached = true;
  $poisoned_layers[] = 'CDN';
}

// Layer 3: Origin Cache (Application-level)
$origin_cached = false;
$cache_key = md5($_SERVER['REQUEST_URI']);
$origin_cache_file = sys_get_temp_dir() . '/cache_origin_' . $cache_key . '.html';

if (file_exists($origin_cache_file) && (time() - filemtime($origin_cache_file)) < 60) {
  $origin_cached = true;
  $cached = file_get_contents($origin_cache_file);
  if (strpos($cached, 'CHAIN_POISONED') !== false) {
    $chain_poisoned = true;
  }
}

// Layer 4: Service Worker Cache (simulated)
$sw_cached = false;
if (isset($_GET['sw_poison']) || isset($_SERVER['HTTP_X_SW'])) {
  $sw_cached = true;
  $poisoned_layers[] = 'ServiceWorker';
}

// VULNERABLE: Multi-layer poisoning triggers
if (isset($_GET['chain_poison'])) {
  $payload = $_GET['chain_poison'];

  if (
    stripos($payload, 'CHAIN_POISONED') !== false ||
    stripos($payload, 'MULTI_LAYER') !== false ||
    stripos($payload, 'ALL_LAYERS') !== false
  ) {

    $chain_poisoned = true;
    $flag_triggered = true;
    $poisoned_layers = ['Browser', 'CDN', 'Origin', 'ServiceWorker'];

    // Poison all layers
    $poisoned_content = "<!-- CHAIN_POISONED: Multi-layer cache attack -->\n";
    $poisoned_content .= "<div class='chain-alert'>All cache layers poisoned!</div>\n";
    file_put_contents($origin_cache_file, $poisoned_content);
  }
}

// Cascade trigger: poisoning one layer affects others
if ($cdn_cached && $sw_cached) {
  $chain_poisoned = true;
  $flag_triggered = true;
  $poisoned_layers = ['CDN', 'ServiceWorker', 'Origin'];
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case8_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case8_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case8_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a Multi-Layer Cache Chain vulnerability. By combining browser cache, CDN edge cache, origin cache, and service worker poisoning, you created a persistent cross-site attack that survives cache purges and affects all users across all layers!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if (!empty($_GET)) {
  $_SESSION['cache_case8_attempts']++;
}

$attempts = $_SESSION['cache_case8_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multi-Layer Cache - CachePoison Case 8 (Multi-Layer Chain)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-link"></i> Multi-Layer Cache Chain</h1>
      <p class="lab-description">Test our complete caching infrastructure. This hard Cache Poisoning challenge combines
        <strong>browser, CDN, origin, and service worker caches</strong>. <strong>Create a persistent multi-layer
          poisoning chain!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Multi-Layer Cache Chain vulnerability.</p>
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

    <!-- Chain Status -->
    <div class="chain-status-bar">
      <div class="layer-indicator <?php echo $browser_cached ? 'poisoned' : 'clean'; ?>">
        <i class="fas fa-<?php echo $browser_cached ? 'exclamation-triangle' : 'chrome'; ?>"></i>
        <span>Browser</span>
      </div>
      <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>
      <div class="layer-indicator <?php echo $cdn_cached ? 'poisoned' : 'clean'; ?>">
        <i class="fas fa-<?php echo $cdn_cached ? 'exclamation-triangle' : 'cloud'; ?>"></i>
        <span>CDN</span>
      </div>
      <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>
      <div class="layer-indicator <?php echo $origin_cached ? 'poisoned' : 'clean'; ?>">
        <i class="fas fa-<?php echo $origin_cached ? 'exclamation-triangle' : 'server'; ?>"></i>
        <span>Origin</span>
      </div>
      <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>
      <div class="layer-indicator <?php echo $sw_cached ? 'poisoned' : 'clean'; ?>">
        <i class="fas fa-<?php echo $sw_cached ? 'exclamation-triangle' : 'cog'; ?>"></i>
        <span>SW</span>
      </div>
      <?php if ($chain_poisoned): ?>
        <div class="poison-indicator">
          <i class="fas fa-skull-crossbones"></i>
          <span>CHAIN POISONED!</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Layer Controls -->
      <div class="cache-card controls-card">
        <div class="card-header">
          <i class="fas fa-sliders-h"></i>
          <h3>Layer Controls</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Cascade Effect</span>
        </div>

        <div class="controls-content">
          <p class="controls-info">Poison individual layers or the entire chain:</p>

          <div class="layer-controls">
            <div class="layer-control">
              <span class="layer-name"><i class="fas fa-cloud"></i> CDN Layer</span>
              <a href="?cdn_poison=1" class="layer-btn <?php echo $cdn_cached ? 'active' : ''; ?>">
                <i class="fas fa-<?php echo $cdn_cached ? 'check' : 'bolt'; ?>"></i>
                <?php echo $cdn_cached ? 'Poisoned' : 'Poison CDN'; ?>
              </a>
            </div>
            <div class="layer-control">
              <span class="layer-name"><i class="fas fa-cog"></i> Service Worker</span>
              <a href="?sw_poison=1" class="layer-btn <?php echo $sw_cached ? 'active' : ''; ?>">
                <i class="fas fa-<?php echo $sw_cached ? 'check' : 'bolt'; ?>"></i>
                <?php echo $sw_cached ? 'Poisoned' : 'Poison SW'; ?>
              </a>
            </div>
            <div class="layer-control">
              <span class="layer-name"><i class="fas fa-link"></i> Full Chain</span>
              <a href="?chain_poison=CHAIN_POISONED"
                class="layer-btn chain-btn <?php echo $chain_poisoned ? 'active' : ''; ?>">
                <i class="fas fa-<?php echo $chain_poisoned ? 'check' : 'skull'; ?>"></i>
                <?php echo $chain_poisoned ? 'All Poisoned' : 'Poison All'; ?>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Poisoned Layers -->
      <div class="cache-card layers-card">
        <div class="card-header">
          <i class="fas fa-layer-group"></i>
          <h3>Poisoned Layers</h3>
        </div>
        <div class="layers-content">
          <?php if (empty($poisoned_layers)): ?>
            <div class="layers-empty">
              <i class="fas fa-shield-alt"></i>
              <p>No layers poisoned yet</p>
            </div>
          <?php else: ?>
            <?php foreach ($poisoned_layers as $layer): ?>
              <div class="layer-status poisoned">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo htmlspecialchars($layer); ?> Layer Poisoned</span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Chain Analysis -->
      <div class="cache-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Chain Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Browser Cache:</span>
            <code class="analysis-code">ETag + Last-Modified</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> No validation</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">CDN Propagation:</span>
            <code class="analysis-code">// Poison spreads to all edges</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Uncontrolled</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Service Worker:</span>
            <code class="analysis-code">// Persists across sessions</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Persistent</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Cascade Effect:</span>
            <code class="analysis-code">// One layer affects all</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Vulnerable</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Multi-Layer Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">CDN + SW Cascade</div>
            <code class="payload-code">?cdn_poison=1&sw_poison=1</code>
            <span class="payload-target">Trigger cascade poisoning</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Full Chain Poison</div>
            <code class="payload-code">?chain_poison=CHAIN_POISONED</code>
            <span class="payload-target">Poison all layers at once</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Persistent SW</div>
            <code class="payload-code">navigator.serviceWorker.register('evil-sw.js')</code>
            <span class="payload-target">Survives cache purge</span>
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
          <span>Browser: <?php echo $browser_cached ? 'POISONED' : 'Clean'; ?></span>
          <span>CDN: <?php echo $cdn_cached ? 'POISONED' : 'Clean'; ?></span>
          <span>Origin: <?php echo $origin_cached ? 'POISONED' : 'Clean'; ?></span>
          <span>SW: <?php echo $sw_cached ? 'POISONED' : 'Clean'; ?></span>
          <span>Chain: <?php echo $chain_poisoned ? 'POISONED' : 'Clean'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">This lab has multiple cache layers that interact. Try poisoning the CDN layer first, then
          the Service Worker. The cascade effect will trigger when multiple layers are poisoned!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try combining parameters: <code>?cdn_poison=1&sw_poison=1</code>. When both CDN and
          Service Worker are poisoned, the cascade triggers and poisons the origin cache too!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Click "Poison All" or visit <code>?chain_poison=CHAIN_POISONED</code>. This triggers the
          full multi-layer attack, poisoning Browser, CDN, Origin, and Service Worker caches simultaneously. The attack
          persists even if individual caches are purged!</div>
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