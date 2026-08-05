<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case6_attempts'])) {
  $_SESSION['cache_case6_attempts'] = 0;
}
if (!isset($_SESSION['cache_case6_solved'])) {
  $_SESSION['cache_case6_solved'] = false;
}

// ─── Vulnerable GraphQL Cache Logic ──────────────────────────────────────
$query_poisoned = false;
$flag_triggered = false;
$normalized_query = null;
$cache_status = 'MISS';

// VULNERABLE: Query normalization allows different queries to share cache
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $query = $input['query'] ?? '';

  // VULNERABLE: Simple normalization - just lowercase and strip spaces
  $normalized = strtolower(preg_replace('/\s+/', '', $query));
  $normalized_query = md5($normalized);

  $cache_file = sys_get_temp_dir() . '/cache_gql_' . $normalized_query . '.json';

  // Check cache
  if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
    $cache_status = 'HIT';
    $cached = json_decode(file_get_contents($cache_file), true);
    if (isset($cached['poisoned']) && $cached['poisoned']) {
      $query_poisoned = true;
    }
  }

  // Check for poison in query
  if (
    stripos($query, 'POISONED') !== false ||
    stripos($query, '__schema') !== false && stripos($query, 'malicious') !== false ||
    stripos($query, 'admin') !== false && stripos($query, 'users') !== false
  ) {
    $query_poisoned = true;
    $flag_triggered = true;

    // VULNERABLE: Cache poisoned response
    $poisoned_response = [
      'data' => ['result' => 'POISONED'],
      'poisoned' => true,
      'via' => 'graphql_cache'
    ];
    file_put_contents($cache_file, json_encode($poisoned_response));
  }

  // Persisted query abuse
  if (isset($input['extensions']['persistedQuery'])) {
    $pq = $input['extensions']['persistedQuery']['sha256Hash'] ?? '';
    if (stripos($pq, 'POISON') !== false || stripos($pq, 'ADMIN') !== false) {
      $query_poisoned = true;
      $flag_triggered = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case6_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case6_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case6_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a GraphQL Cache Poisoning vulnerability. By abusing query normalization and persisted queries, you poisoned the API response cache and caused malicious data to be served to all API consumers!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['cache_case6_attempts']++;
}

$attempts = $_SESSION['cache_case6_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GraphQL API - CachePoison Case 6 (GraphQL Cache)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-project-diagram"></i> GraphQL API</h1>
      <p class="lab-description">Query our GraphQL endpoint. This hard Cache Poisoning challenge has
        <strong>vulnerable query normalization</strong> and persisted query caching. <strong>Poison the API cache with
          query manipulation!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this GraphQL Cache Poisoning vulnerability.</p>
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

    <!-- Cache Status -->
    <div class="cache-status-bar">
      <div class="cache-indicator <?php echo $cache_status === 'HIT' ? 'hit' : 'miss'; ?>">
        <i class="fas fa-<?php echo $cache_status === 'HIT' ? 'database' : 'cloud'; ?>"></i>
        <span>Cache <?php echo $cache_status; ?></span>
      </div>
      <?php if ($query_poisoned): ?>
        <div class="poison-indicator">
          <i class="fas fa-skull-crossbones"></i>
          <span>QUERY POISONED!</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Query Editor -->
      <div class="cache-card editor-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Query Editor</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Normalization Bug</span>
        </div>

        <div class="editor-content">
          <p class="editor-info">Send GraphQL queries via POST:</p>

          <div class="query-examples">
            <div class="query-item">
              <span class="query-label">Normal Query:</span>
              <code class="query-code">{"query": "{ me { name } }"}</code>
            </div>
            <div class="query-item">
              <span class="query-label">Poison via Query:</span>
              <code class="query-code">{"query": "{ admin { users { name } } POISONED"}</code>
            </div>
            <div class="query-item">
              <span class="query-label">Persisted Query Abuse:</span>
              <code class="query-code">{"extensions": {"persistedQuery": {"sha256Hash": "POISON_ADMIN"}}}</code>
            </div>
          </div>

          <div class="curl-section">
            <p class="curl-label">Example curl:</p>
            <code
              class="curl-code">curl -X POST -H "Content-Type: application/json" -d '{"query":"{ me { name } }"}' "<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"</code>
          </div>
        </div>
      </div>

      <!-- Normalization Viewer -->
      <div class="cache-card normal-card">
        <div class="card-header">
          <i class="fas fa-compress-alt"></i>
          <h3>Query Normalization</h3>
        </div>
        <div class="normal-content">
          <div class="normal-item">
            <span class="normal-label">Original Query:</span>
            <code class="normal-original">{ me { name } }</code>
          </div>
          <div class="normal-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="normal-item">
            <span class="normal-label">Normalized:</span>
            <code class="normal-result">{me{name}}</code>
          </div>
          <div class="normal-item">
            <span class="normal-label">Cache Key:</span>
            <code class="normal-key">md5("{me{name}}")</code>
          </div>
          <div class="normal-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Different queries with same normalization share cache!</span>
          </div>
        </div>
      </div>

      <!-- Cache Analysis -->
      <div class="cache-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>GraphQL Cache Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Normalization:</span>
            <code class="analysis-code">lowercase + strip spaces</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Too Simple</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Persisted Queries:</span>
            <code class="analysis-code">// No hash validation</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Abusable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Query Whitelist:</span>
            <code class="analysis-code">// No operation validation</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>GraphQL Poison Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Query Comment Poison</div>
            <code class="payload-code">{ me { name } # POISONED }</code>
            <span class="payload-target">Same normalization, different behavior</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Alias Confusion</div>
            <code class="payload-code">{ a: me { name } b: admin { secret } }</code>
            <span class="payload-target">Abuse query structure</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Persisted Query Hash</div>
            <code class="payload-code">sha256Hash: "POISON_ADMIN_QUERY"</code>
            <span class="payload-target">Fake persisted query hash</span>
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
          <span>Normalized Key:
            <?php echo $normalized_query ? substr($normalized_query, 0, 16) . '...' : 'N/A'; ?></span>
          <span>Cache Status: <?php echo $cache_status; ?></span>
          <span>Poisoned: <?php echo $query_poisoned ? 'YES' : 'NO'; ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">GraphQL queries are normalized by lowercasing and removing spaces. This means
          <code>{ me { name } }</code> and <code>{me{name}}</code> share the same cache entry. Try adding poison content
          that doesn't change the normalization!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try sending a POST request with JSON body:
          <code>{"query":"{ me { name } } POISONED"}</code>. The "POISONED" text is outside the query structure but
          still gets cached due to weak normalization!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Send: <code>curl -X POST -d '{"query":"{ admin { users } } POISONED"}' -H
            "Content-Type: application/json" URL</code>. The query contains "POISONED" which triggers the flag, and the
          weak normalization ensures it poisons the cache for similar queries!</div>
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