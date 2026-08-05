<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SSRF']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['ssrf_hard1_attempts'])) {
  $_SESSION['ssrf_hard1_attempts'] = 0;
}
if (!isset($_SESSION['ssrf_hard1_solved'])) {
  $_SESSION['ssrf_hard1_solved'] = false;
}
if (!isset($_SESSION['ssrf_hard1_stage'])) {
  $_SESSION['ssrf_hard1_stage'] = 1;
}

// ─── Simulated Application: DarkHunter URL Shortener ─────────────────────
$shortened = [
  'abc123' => ['url' => 'https://www.google.com', 'clicks' => 15420],
  'def456' => ['url' => 'https://github.com', 'clicks' => 8930],
  'ghi789' => ['url' => 'https://darkhunter.local/docs', 'clicks' => 3200],
];

// ─── Vulnerable Logic: Follows redirects without validation ──────────────
$link = isset($_GET['link']) ? $_GET['link'] : 'https://bit.ly/abc123';

$redirect_chain = [];
$exploited = false;
$final_response = '';

// Simulate redirect following
$redirect_indicators = [
  'bit.ly',
  'tinyurl',
  't.co',
  'goo.gl',
  'ow.ly',
  'redirect',
  'r.php',
  'goto',
  'forward',
  'open',
  '127.0.0.1',
  'localhost',
  '192.168.',
  '10.0.',
  '169.254.',
];

foreach ($redirect_indicators as $ind) {
  if (stripos($link, $ind) !== false) {
    $exploited = true;
    break;
  }
}

if ($exploited) {
  $redirect_chain = [
    ['url' => $link, 'status' => '302 Found', 'location' => 'https://darkhunter.local/api/health'],
    ['url' => 'https://darkhunter.local/api/health', 'status' => '302 Found', 'location' => 'http://127.0.0.1:8080/admin'],
    ['url' => 'http://127.0.0.1:8080/admin', 'status' => '200 OK', 'location' => null],
  ];
  $final_response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><head><title>Admin Panel</title></head><body><h1>DarkHunter Internal Admin</h1><p>Status: <span style='color:green'>Online</span></p><p>Server: localhost:8080</p><p>Uptime: 45 days</p><hr><h2>System Controls</h2><ul><li>User Management</li><li>Database Admin</li><li>Log Viewer</li><li>Configuration</li></ul><hr><p><strong>Flag: DH{ssrf_redirect_chain_pwned}</strong></p></body></html>";
} else {
  $redirect_chain = [
    ['url' => $link, 'status' => '200 OK', 'location' => null],
  ];
  $final_response = "HTTP/1.1 200 OK\nContent-Type: text/html\n\n<html><body><h1>External Resource</h1><p>URL: " . htmlspecialchars($link) . "</p></body></html>";
}

$current_stage = $_SESSION['ssrf_hard1_stage'];
$stage_messages = [
  1 => "Stage 1: Understand how the URL shortener follows redirects without validating destinations.",
  2 => "Stage 2: Identify open redirect endpoints that can chain to internal addresses.",
  3 => "Stage 3: Exploit the redirect chain to reach internal admin panels.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['ssrf_hard1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['ssrf_hard1_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['ssrf_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've exploited an SSRF vulnerability through redirect chains. The application followed multiple redirects without validating each hop, allowing you to reach internal admin panels through seemingly innocent URLs. This is a sophisticated bypass technique!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['link'])) $_SESSION['ssrf_hard1_attempts']++;
$attempts = $_SESSION['ssrf_hard1_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['ssrf_hard1_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['ssrf_hard1_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>URL Shortener - SSRF Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght=300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SSRF-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SSRF Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-link"></i> DarkHunter URL Shortener</h1>
      <p class="lab-description">A URL shortening service that follows redirects to resolve final destinations.
        <strong>The application validates the initial URL but follows all redirects blindly.</strong> Can you chain
        redirects to reach internal services?
      </p>
    </div>
    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this redirect chain vulnerability.</p>
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
          <div class="stage-info"><span class="stage-title">Reconnaissance</span><span class="stage-desc">Map redirect
              behavior</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">Chain Crafting</span><span class="stage-desc">Build redirect
              chain</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Exploitation</span><span class="stage-desc">Reach internal
              admin</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="shortener-card">
      <div class="shortener-header">
        <div class="shortener-brand"><i class="fas fa-compress-alt"></i><span>URL Shortener</span></div>
        <div class="shortener-badge"><i class="fas fa-route"></i><span>Redirect Follower</span></div>
      </div>
      <div class="shortener-body">
        <div class="redirect-panel">
          <div class="redirect-header"><i class="fas fa-route"></i><span>Redirect Chain</span></div>
          <div class="redirect-chain">
            <?php foreach ($redirect_chain as $i => $step): ?>
            <div class="chain-step">
              <div class="step-num"><?php echo $i + 1; ?></div>
              <div class="step-content">
                <code class="step-url"><?php echo htmlspecialchars($step['url']); ?></code>
                <span
                  class="step-status <?php echo strpos($step['status'], '200') !== false ? 'success' : 'redirect'; ?>"><i
                    class="fas fa-<?php echo strpos($step['status'], '200') !== false ? 'check' : 'arrow-right'; ?>"></i>
                  <?php echo $step['status']; ?></span>
                <?php if ($step['location']): ?><code
                  class="step-location">→ <?php echo htmlspecialchars($step['location']); ?></code><?php endif; ?>
              </div>
            </div>
            <?php if ($i < count($redirect_chain) - 1): ?><div class="chain-arrow"><i class="fas fa-arrow-down"></i>
            </div><?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="final-panel">
          <div class="final-header"><i class="fas fa-flag-checkered"></i><span>Final Response</span></div>
          <div class="final-body">
            <pre class="final-content"><?php echo htmlspecialchars($final_response); ?></pre>
          </div>
        </div>
      </div>
    </div>

    <div class="chain-explanation">
      <div class="chain-header"><i class="fas fa-graduation-cap"></i><span>Redirect Chain Attack</span></div>
      <div class="chain-body">
        <div class="chain-flow">
          <div class="flow-step">
            <div class="flow-icon"><i class="fas fa-user"></i></div>
            <div class="flow-content"><span class="flow-title">Attacker</span><code>bit.ly/evil → redirect</code></div>
          </div>
          <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="flow-step">
            <div class="flow-icon"><i class="fas fa-server"></i></div>
            <div class="flow-content"><span class="flow-title">App Validates</span><code>bit.ly = PUBLIC ✓</code></div>
          </div>
          <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="flow-step">
            <div class="flow-icon"><i class="fas fa-sync"></i></div>
            <div class="flow-content"><span class="flow-title">Follows Redirect</span><code>→ 127.0.0.1:8080</code>
            </div>
          </div>
          <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
          <div class="flow-step">
            <div class="flow-icon"><i class="fas fa-skull"></i></div>
            <div class="flow-content"><span class="flow-title">Internal Access</span><code>Admin panel exposed!</code>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body"><code>GET /SSRF-CASE5.php?link=<?php echo urlencode($link); ?></code></div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Follows all redirects blindly
$link = $_GET['link'];

// Validates INITIAL URL only
if (!isValidURL($link)) {
    die("Invalid URL!");
}

// But follows ALL redirects without checking!
$ch = curl_init($link);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // DANGEROUS!
$response = curl_exec($ch);</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong>
            CURLOPT_FOLLOWLOCATION follows all redirects without validating intermediate destinations. An attacker
            chains redirects: public → internal → admin panel. The application validates step 1 but executes step
            3!</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The application follows redirects. Try using URL shorteners or redirect services:
        <code>?link=https://bit.ly/something</code> or any link containing <code>redirect</code>, <code>r.php</code>,
        <code>goto</code>.
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The key is the redirect chain. Try
        <code>?link=https://darkhunter.local/redirect?url=http://127.0.0.1</code> or any URL that triggers internal
        redirect following.
      </div>
    </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use <code>?link=https://bit.ly/internal</code> or any URL containing <code>bit.ly</code>,
        <code>tinyurl</code>, <code>redirect</code>, <code>127.0.0.1</code>, or <code>192.168</code> to trigger the
        redirect chain exploitation!
      </div>
    </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="link" value="<?php echo htmlspecialchars($link); ?>">
  </form>

  <script>
  window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const link = urlParams.get('link') || '';
    const redirectPatterns = ['bit.ly', 'tinyurl', 't.co', 'goo.gl', 'ow.ly', 'redirect', 'r.php', 'goto',
      'forward', 'open', '127.0.0.1', 'localhost', '192.168.', '10.0.', '169.254.'
    ];
    const hasRedirect = redirectPatterns.some(p => link.toLowerCase().includes(p.toLowerCase()));
    if (hasRedirect && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>