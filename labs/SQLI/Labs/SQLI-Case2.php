<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SQLI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['sqli_case2_attempts'])) {
  $_SESSION['sqli_case2_attempts'] = 0;
}
if (!isset($_SESSION['sqli_case2_solved'])) {
  $_SESSION['sqli_case2_solved'] = false;
}

// ─── Simulated Product Database ──────────────────────────────────────────
$products = [
  ['id' => 1, 'name' => 'DarkHunter Pro License', 'category' => 'Software', 'price' => 299.99, 'stock' => 150, 'sku' => 'DH-PRO-001'],
  ['id' => 2, 'name' => 'Web Security Scanner', 'category' => 'Tools', 'price' => 149.99, 'stock' => 89, 'sku' => 'DH-SCAN-002'],
  ['id' => 3, 'name' => 'Penetration Testing Kit', 'category' => 'Hardware', 'price' => 599.99, 'stock' => 45, 'sku' => 'DH-PENTEST-003'],
  ['id' => 4, 'name' => 'CTF Challenge Pack', 'category' => 'Training', 'price' => 49.99, 'stock' => 999, 'sku' => 'DH-CTF-004'],
  ['id' => 5, 'name' => 'Bug Bounty Handbook', 'category' => 'Books', 'price' => 39.99, 'stock' => 230, 'sku' => 'DH-BOOK-005'],
];

$secret_data = [
  'flag' => 'DH{sqli_blind_extraction}',
  'db_version' => 'MySQL 8.0.32',
  'admin_email' => 'admin@darkhunter.local',
  'internal_api_key' => 'dh_internal_api_7a3f9c2e1d8b4a5f',
];

// ─── Vulnerable Search Logic ─────────────────────────────────────────────
$search_results = [];
$search_query = null;
$search_error = null;
$injection_detected = false;
$time_based_hint = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
  $search = $_GET['search'];
  
  // VULNERABLE: Direct concatenation in WHERE clause
  // No error messages returned - blind injection only
  $query = "SELECT * FROM products WHERE name LIKE '%" . $search . "%'";
  $search_query = $query;
  
  // Simulate vulnerable behavior
  // Check for time-based injection
  if (stripos($search, 'SLEEP(') !== false || stripos($search, 'BENCHMARK(') !== false) {
    $time_based_hint = true;
    $injection_detected = true;
    // Simulate delay
    usleep(2000000); // 2 second delay
  }
  
  // Check for boolean-based injection (tautology)
  if (stripos($search, "' OR '") !== false || stripos($search, "' AND '") !== false || stripos($search, '1=1') !== false) {
    $injection_detected = true;
    // Return all products as "true" condition
    $search_results = $products;
  }
  
  // Check for SUBSTRING-based extraction (blind data extraction)
  if (stripos($search, 'SUBSTRING') !== false || stripos($search, 'SUBSTR') !== false || stripos($search, 'MID') !== false) {
    $injection_detected = true;
    // If extracting flag, show special result
    if (stripos($search, 'flag') !== false || stripos($search, 'DH{') !== false) {
      $search_results = [
        array_merge($products[0], ['name' => 'FLAG FOUND: DH{sqli_blind_extraction}'])
      ];
    } else {
      $search_results = $products;
    }
  }
  
  // Normal search (no injection)
  if (!$injection_detected) {
    foreach ($products as $product) {
      if (stripos($product['name'], $search) !== false || stripos($product['category'], $search) !== false) {
        $search_results[] = $product;
      }
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['sqli_case2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['sqli_case2_attempts']++;
  
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  
  $_SESSION['sqli_case2_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a Blind SQL Injection vulnerability. Without error messages, you used boolean-based and time-based techniques to extract sensitive data character by character!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
  $_SESSION['sqli_case2_attempts']++;
}

$attempts = $_SESSION['sqli_case2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Search - SQLI Case 2 (Blind Injection)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SQLI-Case2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SQLI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-search"></i> DarkHunter Store Search</h1>
      <p class="lab-description">Search our product catalog. This medium-difficulty SQL Injection challenge uses a blind
        vulnerability - <strong>no error messages are returned!</strong> Use boolean-based and time-based techniques to
        extract the hidden flag.</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Blind SQL Injection vulnerability. You can continue exploring, but no
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

    <!-- Search Section -->
    <div class="store-grid">

      <!-- Search Form (Vulnerable) -->
      <div class="store-card search-card">
        <div class="card-header">
          <i class="fas fa-search"></i>
          <h3>Product Search</h3>
          <span class="vuln-badge"><i class="fas fa-eye-slash"></i> Blind SQLI</span>
        </div>

        <form method="GET" action="" class="search-form" id="search-form">
          <div class="form-group">
            <label><i class="fas fa-keyboard"></i> Search Query</label>
            <input type="text" name="search" placeholder="Search products..." class="form-input"
              value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          </div>

          <button type="submit" class="btn-search">
            <i class="fas fa-search"></i> Search
          </button>
        </form>

        <div class="search-info">
          <i class="fas fa-info-circle"></i>
          <span>Search returns products matching your query. No error messages displayed.</span>
        </div>
      </div>

      <!-- Query Inspector -->
      <?php if ($search_query): ?>
      <div class="store-card query-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Query Analysis</h3>
        </div>
        <div class="query-display">
          <div class="query-label">Constructed Query:</div>
          <code class="query-code"><?php echo htmlspecialchars($search_query); ?></code>

          <div class="query-metrics">
            <div class="metric">
              <span class="metric-label">Results Found:</span>
              <span class="metric-value"><?php echo count($search_results); ?></span>
            </div>
            <div class="metric">
              <span class="metric-label">Response Time:</span>
              <span class="metric-value <?php echo $time_based_hint ? 'time-delay' : ''; ?>">
                <?php echo $time_based_hint ? '~2000ms' : '~50ms'; ?>
              </span>
            </div>
            <div class="metric">
              <span class="metric-label">Injection:</span>
              <span class="metric-value <?php echo $injection_detected ? 'detected' : 'clean'; ?>">
                <?php echo $injection_detected ? 'DETECTED' : 'None'; ?>
              </span>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Search Results -->
      <div class="store-card results-card">
        <div class="card-header">
          <i class="fas fa-list"></i>
          <h3>Search Results</h3>
          <span class="result-count"><?php echo count($search_results); ?> items</span>
        </div>
        <div class="results-list">
          <?php if (empty($search_results)): ?>
          <div class="no-results">
            <i class="fas fa-inbox"></i>
            <span>No products found. Try a different search term.</span>
          </div>
          <?php else: ?>
          <?php foreach ($search_results as $product): ?>
          <div class="product-item">
            <div class="product-icon">
              <i class="fas fa-box"></i>
            </div>
            <div class="product-info">
              <span class="product-name"><?php echo htmlspecialchars($product['name']); ?></span>
              <span class="product-meta">
                <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                <span class="product-sku"><?php echo htmlspecialchars($product['sku']); ?></span>
              </span>
            </div>
            <div class="product-price">
              <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
              <span class="stock <?php echo $product['stock'] > 50 ? 'in-stock' : 'low-stock'; ?>">
                <?php echo $product['stock']; ?> in stock
              </span>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Extraction Target -->
      <div class="store-card target-card">
        <div class="card-header">
          <i class="fas fa-bullseye"></i>
          <h3>Extraction Target</h3>
          <span class="secret-badge"><i class="fas fa-lock"></i> Hidden</span>
        </div>
        <div class="target-content">
          <div class="target-item">
            <span class="target-label">Flag</span>
            <span
              class="target-value masked"><?php echo ($injection_detected && (stripos($_GET['search'] ?? '', 'flag') !== false || stripos($_GET['search'] ?? '', 'DH{') !== false)) ? 'DH{sqli_blind_extraction}' : '***********************'; ?></span>
          </div>
          <div class="target-item">
            <span class="target-label">DB Version</span>
            <span
              class="target-value masked"><?php echo $injection_detected ? $secret_data['db_version'] : '************'; ?></span>
          </div>
          <div class="target-item">
            <span class="target-label">Admin Email</span>
            <span
              class="target-value masked"><?php echo $injection_detected ? $secret_data['admin_email'] : '*******************'; ?></span>
          </div>
          <div class="target-item">
            <span class="target-label">API Key</span>
            <span
              class="target-value masked"><?php echo $injection_detected ? $secret_data['internal_api_key'] : '************************'; ?></span>
          </div>
        </div>
      </div>

      <!-- Blind Injection Techniques -->
      <div class="store-card techniques-card">
        <div class="card-header">
          <i class="fas fa-book"></i>
          <h3>Injection Techniques</h3>
        </div>
        <div class="techniques-list">
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-toggle-on"></i>
              <span class="tech-name">Boolean-Based</span>
            </div>
            <code class="tech-example">' OR 1=1-- </code>
            <p class="tech-desc">Returns all results when condition is true, nothing when false.</p>
          </div>
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-clock"></i>
              <span class="tech-name">Time-Based</span>
            </div>
            <code class="tech-example">' OR SLEEP(2)-- </code>
            <p class="tech-desc">Delays response to confirm injection when no visible output changes.</p>
          </div>
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-filter"></i>
              <span class="tech-name">Error-Based</span>
            </div>
            <code class="tech-example">' AND EXTRACTVALUE(1, CONCAT(0x7e, (SELECT @@version)))-- </code>
            <p class="tech-desc">Forces database errors that leak information (not applicable here - errors suppressed).
            </p>
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
          <span>Query String: <?php echo $_SERVER['QUERY_STRING'] ?: 'None'; ?></span>
          <span>Response Time: <?php echo $time_based_hint ? '~2000ms (DELAYED)' : '~50ms'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This is a <strong>blind</strong> SQL injection - the application never shows database
        errors. You must infer information from the application's behavior. Try boolean-based techniques:
        <code>' OR '1'='1</code> should return all products, while <code>' AND '1'='2</code> should return none.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Use time-based detection to confirm injection: <code>' OR SLEEP(2)-- </code>. If the
        response takes ~2 seconds longer, the injection works. Then use boolean-based extraction with SUBSTRING to read
        the flag character by character:
        <code>' OR SUBSTRING((SELECT notes FROM employees WHERE username='admin'),1,1)='D'-- </code>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Automate character extraction: For each position, test
        <code>' OR SUBSTRING((SELECT notes FROM employees WHERE username='admin'),POS,1)='CHAR'-- </code>. If products
        return, the character matches. Build the flag string position by position. The flag format is
        <code>DH{...}</code>.
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
    const flagRevealed = document.querySelector('.target-value:not(.masked)');
    const alreadySolved = document.querySelector('.solved-banner');

    // Check if flag is visible (not masked)
    const targetValues = document.querySelectorAll('.target-value');
    let flagVisible = false;
    targetValues.forEach(tv => {
      if (tv.textContent.includes('DH{')) {
        flagVisible = true;
      }
    });

    if (flagVisible && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>