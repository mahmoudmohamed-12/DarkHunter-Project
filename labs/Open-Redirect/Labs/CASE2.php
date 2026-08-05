<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['OpenRedirect']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['or_medium1_attempts'])) {
  $_SESSION['or_medium1_attempts'] = 0;
}

if (!isset($_SESSION['or_medium1_solved'])) {
  $_SESSION['or_medium1_solved'] = false;
}

// ─── Simulated Application: Corporate Document Portal ────────────────────
$portal_name = "CorpDoc Portal";
$portal_domain = "corpdoc.darkhunter.local";

// ─── Vulnerable Logic: Weak blacklist filtering ──────────────────────────
$redirect_target = isset($_GET['next']) ? $_GET['next'] : '/dashboard';

// Weak validation - only checks for "http://" and "https://" at start
$is_blocked = false;
$block_reason = '';

// Blacklist check (easily bypassed)
$blacklist = [
  'http://',
  'https://',
  'javascript:',
  'data:'
];

foreach ($blacklist as $item) {
  if (stripos($redirect_target, $item) === 0) {
    $is_blocked = true;
    $block_reason = "Blacklisted protocol detected: " . $item;
    break;
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['or_medium1_solved'];
$exploited = false;

// Detect successful bypass
$bypass_indicators = [
  '//evil.com',
  '///evil.com',
  '\\evil.com',
  'corpdoc.darkhunter.local.evil.com',
  'evil.com/corpdoc.darkhunter.local',
  'corpdoc.darkhunter.local@evil.com',
  'evil.com@corpdoc.darkhunter.local',
  'corpdoc.darkhunter.local%40evil.com',
  '%2F%2Fevil.com',
  'javascript%3A',
  'data%3A',
  'file%3A'
];

foreach ($bypass_indicators as $indicator) {
  if (stripos($redirect_target, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

// Protocol-relative bypass detection (starts with //)
if (preg_match('/^\\/\\/[^\\/]+/', $redirect_target)) {
  $exploited = true;
}

// @ symbol bypass
if (preg_match('/https?:\\/\\/[^\\/]+@/', $redirect_target)) {
  $exploited = true;
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['or_medium1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['or_medium1_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've bypassed weak blacklist filtering to exploit an Open Redirect. You used advanced URL parsing tricks to evade the naive protocol checks. This demonstrates why blacklist-based validation is insufficient!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['next'])) {
  $_SESSION['or_medium1_attempts']++;
}

$attempts = $_SESSION['or_medium1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CorpDoc Portal - Open Redirect Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/case2.css">
</head>

<body><?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php';
      ?><div class="container"><a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back to Open
      Redirect
      Labs</a>
    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i>Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-building"></i>CorpDoc Portal</h1>
      <p class="lab-description">A corporate document sharing portal that redirects users after actions. The
        developers implemented a <strong>weak blacklist</strong>to block external redirects,
        but it's easily bypassed using URL parsing tricks!</p>
    </div><?php if ($already_solved): ?><div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved !</h3>
        <p>You have already exploited this filter bypass. You can continue exploring,
          but no additional points will be awarded.</p>
      </div>
    </div><?php endif;
            ?><?php if ($success_msg): ?><div class="success-alert"><i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Completed !</h3>
        <p><?php echo $success_msg;
              ?></p>
      </div>
    </div><?php endif;
            ?>
    <div class="portal-card">
      <div class="portal-header">
        <div class="portal-brand"><i class="fas fa-file-alt"></i><span>CorpDoc Portal</span></div>
        <div class="portal-domain"><i class="fas fa-globe"></i><?php echo $portal_domain;
                                                                ?></div>
      </div>
      <div class="portal-body">
        <h2 class="portal-title">Document Access Gateway</h2>
        <p class="portal-desc">You are accessing confidential corporate documents. Please verify your redirect
          destination before proceeding.</p>
        <div class="security-panel">
          <div class="security-title"><i class="fas fa-shield-alt"></i><span>Security Filter Status</span></div>
          <div class="security-rules">
            <div class="rule-item <?php echo $is_blocked ? 'blocked' : 'passed'; ?>"><i
                class="fas fa-<?php echo $is_blocked ? 'times-circle' : 'check-circle'; ?>"></i><span>Protocol
                Blacklist Check</span><span class="rule-status"><?php echo $is_blocked ? 'BLOCKED' : 'PASSED';
                                                                ?></span></div><?php if ($is_blocked): ?><div
              class="block-reason"><i class="fas fa-info-circle"></i><span><?php echo htmlspecialchars($block_reason);
                                                                              ?></span>
            </div><?php endif;
                    ?>
          </div>
        </div>
        <div class="redirect-section">
          <div class="redirect-label"><i class="fas fa-route"></i><span>Navigation Target</span></div>
          <div class="redirect-display"><code class="redirect-url"><?php echo htmlspecialchars($redirect_target);
                                                                    ?></code><?php if ($is_blocked): ?><span
              class="redirect-blocked"><i class="fas fa-ban"></i>Blocked</span><?php else: ?><span
              class="redirect-allowed"><i class="fas fa-check"></i>Allowed</span><?php endif;
                                                                                    ?></div>
        </div>
        <div class="action-buttons"><?php if (!$is_blocked): ?><a
            href="<?php echo htmlspecialchars($redirect_target); ?>" class="action-btn primary"><span>Proceed
              to Document</span><i class="fas fa-external-link-alt"></i></a><?php else: ?><button
            class="action-btn disabled" disabled><span>Redirect Blocked</span><i
              class="fas fa-ban"></i></button><?php endif;
                                                                                                                    ?><a href="?next=/dashboard" class="action-btn secondary"><span>Return
              to Dashboard</span><i class="fas fa-home"></i></a></div>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Filter Logic Analysis</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre> // Vulnerable Filter Implementation
$blacklist=['http://',
'https://',
'javascript:',
'data:'];

foreach ($blacklist as $item) {
  if (stripos($redirect, $item)===0) {
    block_redirect();
  }
}

</pre>
        </div>
        <div class="vuln-note"><i class="fas fa-exclamation-triangle"></i><span><strong>Vulnerability:</strong>Only
            checks start of
            string. Easily bypassed with protocol-relative URLs,
            @ symbols,
            and encoding tricks.</span></div>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-terminal"></i><span>Current Request</span></div>
      <div class="debug-body"><code>GET /CASE2.php?next=<?php echo urlencode($redirect_target);
                                                                      ?></code></div>
    </div><?php if ($attempts >= 2): ?><div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i>Hint #1</div>
      <div class="hint-text">The filter only blocks URLs that <strong>start with</strong>blacklisted
        protocols. What if you don't use a protocol at all? Try <code>?next=//evil.com</code>
        (protocol-relative URL).</div>
    </div><?php endif;
            ?><?php if ($attempts >= 4): ?><div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i>Hint #2</div>
      <div class="hint-text">Another bypass technique: use the <code>@</code>symbol to confuse URL parsers.
        Try <code>?next=https: //corpdoc.darkhunter.local@evil.com</code> or
        <code>?next=https://evil.com/corpdoc.darkhunter.local</code>.
      </div>
    </div><?php endif;
            ?><?php if ($attempts >= 6): ?><div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i>Final Hint</div>
      <div class="hint-text">Try <code>?next= //evil.com</code> for a protocol-relative bypass, or
        <code>?next=corpdoc.darkhunter.local.evil.com</code> for subdomain confusion. The filter only checks
        the beginning of the string!
      </div>

    </div><?php endif;
            ?>
    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts: <strong><?php echo $attempts;

                                                                                        ?></strong></span></div>
  </div>
  <form id="success-form" method="GET" style="display: none;"><input type="hidden" name="check" value="true"><input
      type="hidden" name="solved" value="0" id="solved-flag"><input type="hidden" name="next"
      value="<?php echo htmlspecialchars($redirect_target); ?>"></form>
  <script>
  window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const next = urlParams.get('next') || '';

        // Check for bypass indicators
        const bypassPatterns = ['//evil.com', '///evil.com', '\\\\evil.com',
          'corpdoc.darkhunter.local.evil.com',
          'evil.com/corpdoc.darkhunter.local',
          'corpdoc.darkhunter.local@evil.com',
          'evil.com@corpdoc.darkhunter.local',
          '%2F%2Fevil.com', 'javascript%3A', 'data%3A'
        ];

        const hasBypass = bypassPatterns.some(pattern => next.toLowerCase().includes(pattern.toLowerCase()));

        // Protocol-relative bypass
        const protocolRelative = /^\\/\\ / [ ^ \\/]+/.test(next);

          // @ symbol bypass
          const atBypass = /https?:\\/\\ / [ ^ \\/]+@/.test(next);

            if ((hasBypass || protocolRelative || atBypass) && !document.querySelector('.solved-banner')) {
              document.getElementById('solved-flag').value = '1';
              document.getElementById('success-form').submit();
            }
          }

        );
  </script>
</body>

</html>