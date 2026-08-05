<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['OpenRedirect']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['or_medium2_attempts'])) {
  $_SESSION['or_medium2_attempts'] = 0;
}
if (!isset($_SESSION['or_medium2_solved'])) {
  $_SESSION['or_medium2_solved'] = false;
}

// ─── Simulated Application: DarkHunter SPA Dashboard ───────────────────────
$spa_routes = [
  '/dashboard' => 'User Dashboard',
  '/profile' => 'User Profile',
  '/settings' => 'Account Settings',
  '/reports' => 'Security Reports',
  '/admin' => 'Admin Panel (Restricted)'
];

// ─── Vulnerable Logic: Client-side redirect via hash fragment ────────────
// The SPA uses hash-based routing which is vulnerable to DOM manipulation
$hash_route = isset($_GET['route']) ? $_GET['route'] : '/dashboard';

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['or_medium2_solved'];
$exploited = false;

// Detect DOM-based exploitation
$dom_exploit_indicators = [
  'javascript:',
  'data:',
  'vbscript:',
  'mocha:',
  'livescript:',
  '<script',
  'onload=',
  'onerror=',
  'onclick=',
  'onmouseover=',
  'alert(',
  'confirm(',
  'prompt(',
  'eval(',
  'document.location',
  'window.location',
  'innerHTML',
  'document.write'
];

foreach ($dom_exploit_indicators as $indicator) {
  if (stripos($hash_route, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Check for URL-encoded variants
$encoded_indicators = [
  '%3Cscript',
  '%6A%61%76%61',
  'javascript%3A',
  'data%3A',
  '%6F%6E%6C%6F%61%64',
  '%6F%6E%65%72%72%6F%72'
];

foreach ($encoded_indicators as $indicator) {
  if (stripos($hash_route, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['or_medium2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['or_medium2_solved'] = true;
  $already_solved = true;
  $success_msg = "Impressive! You've exploited a DOM-based Open Redirect vulnerability. By injecting JavaScript into the hash-based router, you demonstrated how client-side redirects can lead to XSS and phishing attacks. The server never saw the payload!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['route'])) {
  $_SESSION['or_medium2_attempts']++;
}

$attempts = $_SESSION['or_medium2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DarkHunter SPA - Open Redirect Medium 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/case3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Open Redirect Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-code"></i> DarkHunter SPA Router</h1>
      <p class="lab-description">A modern Single Page Application using hash-based routing. The client-side router
        blindly trusts the hash fragment to navigate between views. <strong>No sanitization on the route
          parameter!</strong> Can you inject malicious JavaScript?</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this DOM-based vulnerability. You can continue exploring, but no additional points
          will be awarded.</p>
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

    <!-- SPA Interface Simulation -->
    <div class="spa-container">
      <!-- Sidebar -->
      <div class="spa-sidebar">
        <div class="sidebar-header">
          <i class="fas fa-shield-alt"></i>
          <span>DarkHunter</span>
        </div>
        <nav class="sidebar-nav">
          <?php foreach ($spa_routes as $route => $label): ?>
          <a href="?route=<?php echo urlencode($route); ?>"
            class="nav-item <?php echo $hash_route === $route ? 'active' : ''; ?>">
            <i class="fas fa-<?php echo $route === '/admin' ? 'lock' : 'chevron-right'; ?>"></i>
            <span><?php echo $label; ?></span>
          </a>
          <?php endforeach; ?>
        </nav>
      </div>

      <!-- Main Content Area -->
      <div class="spa-main">
        <div class="main-header">
          <div class="breadcrumb">
            <i class="fas fa-home"></i>
            <span> / </span>
            <span id="current-route"><?php echo htmlspecialchars($hash_route); ?></span>
          </div>
          <div class="user-menu">
            <i class="fas fa-user-circle"></i>
            <span><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?></span>
          </div>
        </div>

        <!-- Content View (Vulnerable to DOM injection) -->
        <div class="content-view" id="content-view">
          <div class="view-header">
            <h2 id="view-title">Loading...</h2>
            <span class="view-badge" id="view-badge">Client-Side Rendered</span>
          </div>
          <div class="view-body" id="view-body">
            <div class="loading-spinner">
              <i class="fas fa-circle-notch fa-spin"></i>
              <span>Loading route content...</span>
            </div>
          </div>
        </div>

        <!-- Route Info Panel -->
        <div class="route-info">
          <div class="route-info-header">
            <i class="fas fa-info-circle"></i>
            <span>Route Information</span>
          </div>
          <div class="route-info-body">
            <div class="info-row">
              <span class="info-label">Current Hash:</span>
              <code class="info-value">#<?php echo htmlspecialchars($hash_route); ?></code>
            </div>
            <div class="info-row">
              <span class="info-label">Router Type:</span>
              <span class="info-value">Client-Side (Hash)</span>
            </div>
            <div class="info-row">
              <span class="info-label">Sanitization:</span>
              <span class="info-value vulnerable">None</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DOM Analysis Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-bug"></i>
        <span>Client-Side Vulnerability Analysis</span>
      </div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// Vulnerable Router Implementation
function handleRoute(route) {
  // No sanitization!
  document.getElementById('view-title').innerHTML = route;
  document.getElementById('current-route').innerHTML = route;
  
  // Dangerous eval-like behavior
  if (route.startsWith('javascript:')) {
    eval(route.substring(11));
  }
}</pre>
        </div>
        <div class="vuln-note critical">
          <i class="fas fa-radiation"></i>
          <span><strong>Critical:</strong> innerHTML assignment without sanitization allows XSS. The router also
            dangerously evaluates javascript: protocols!</span>
        </div>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-terminal"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /CASE3.php?route=<?php echo urlencode($hash_route); ?></code>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This is a DOM-based vulnerability. The route parameter is rendered directly into the page
        using <code>innerHTML</code>. Try injecting HTML/JavaScript through the <code>route</code> parameter.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try <code>?route=&lt;img src=x onerror=alert(1)&gt;</code> for an XSS vector, or
        <code>?route=javascript:alert(1)</code> for a JavaScript protocol redirect. The router evaluates javascript:
        URLs!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?route=javascript:alert('DOM-Based Open Redirect')</code> or
        <code>?route=&lt;script&gt;alert(1)&lt;/script&gt;</code>. The application executes this client-side without
        server validation!
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
    <input type="hidden" name="route" value="<?php echo htmlspecialchars($hash_route); ?>">
  </form>

  <script>
  // Vulnerable Client-Side Router Simulation
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const route = urlParams.get('route') || '/dashboard';

    // Simulate vulnerable routing logic
    const viewTitle = document.getElementById('view-title');
    const currentRoute = document.getElementById('current-route');
    const viewBody = document.getElementById('view-body');

    // VULNERABLE: Direct innerHTML assignment without sanitization
    viewTitle.innerHTML = route;
    currentRoute.innerHTML = route;

    // Simulate route content loading
    const routes = {
      '/dashboard': '<p>Welcome to your security dashboard. Monitor threats and track your progress.</p>',
      '/profile': '<p>Manage your profile settings and account information.</p>',
      '/settings': '<p>Configure application preferences and security options.</p>',
      '/reports': '<p>View generated security reports and vulnerability assessments.</p>',
      '/admin': '<p class="restricted"><i class="fas fa-lock"></i> Access Denied: Admin privileges required.</p>'
    };

    // VULNERABLE: If route starts with javascript:, execute it
    if (route.toLowerCase().startsWith('javascript:')) {
      viewBody.innerHTML =
        '<div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> JavaScript protocol detected and executed!</div>';
      // In a real vulnerable app, this would execute:
      // eval(decodeURIComponent(route.substring(11)));
    } else if (route.includes('<script') || route.includes('onload=') || route.includes('onerror=') || route
      .includes('alert(')) {
      viewBody.innerHTML =
        '<div class="alert-danger"><i class="fas fa-bug"></i> Potential XSS payload detected in route!</div>';
    } else {
      viewBody.innerHTML = routes[route] || '<p>Route not found. The application loaded: <code>' + route +
        '</code></p>';
    }

    // Check for exploitation indicators
    const exploitPatterns = [
      'javascript:', 'data:', 'vbscript:', 'mocha:', 'livescript:',
      '<script', 'onload=', 'onerror=', 'onclick=', 'onmouseover=',
      'alert(', 'confirm(', 'prompt(', 'eval(', 'document.location',
      'window.location', 'innerHTML', 'document.write',
      '%3Cscript', '%6A%61%76%61', 'javascript%3A', 'data%3A'
    ];

    const hasExploit = exploitPatterns.some(pattern =>
      route.toLowerCase().includes(pattern.toLowerCase())
    );

    if (hasExploit && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>