<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

function waf_filter($input) {
    $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);
    $input = preg_replace('/on\w+\s*=/i', '', $input);
    $input = preg_replace('/javascript:/i', '', $input);
    $input = preg_replace('/data:/i', '', $input);
    $input = preg_replace('/<(img|svg|iframe|object|embed|form|input|button)\b[^>]*>/i', '', $input);
    $input = preg_replace('/[()]/', '', $input);
    $input = str_replace(['"', "'", '`', '\\'], '', $input);
    $input = preg_replace('/&#x?[0-9a-f]+;/i', '', $input);

    $input = preg_replace('/(alert|confirm|prompt|eval|Function|setTimeout|setInterval)/i', '', $input);
    
    return $input;
}

$input = isset($_GET['input']) ? $_GET['input'] : '';
$filtered = waf_filter($input);
$submitted = !empty($input);


if (!isset($_SESSION['rxss_hard_attempts'])) {
    $_SESSION['rxss_hard_attempts'] = 0;
}
if ($submitted) {
    $_SESSION['rxss_hard_attempts']++;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {

        if (solveLab($pdo, 13)) {
            $success_msg = "🏆 LEGENDARY! WAF Bypassed! +300 pts";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WAF Bypass Challenge - RXSS Hard</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/xss-vuln-case-5.css">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to XSS Labs</a>

    <!-- Warning Banner -->
    <div class="warning-banner">
      <i class="fas fa-shield-alt warning-icon"></i>
      <div class="warning-content">
        <h2><i class="fas fa-exclamation-triangle"></i> WAF Protected Environment</h2>
        <p>This challenge is protected by an advanced Web Application Firewall. Multiple security layers are active.</p>
      </div>
    </div>

    <!-- Lab Card -->
    <div class="lab-card">
      <div class="lab-header">
        <div class="difficulty-badge">
          <i class="fas fa-skull"></i> Hard / Expert
        </div>
        <h1 class="lab-title">WAF Bypass Challenge</h1>

        <!-- Tags -->
        <div class="lab-tags">
          <span class="tag">WAF Bypass</span>
          <span class="tag">Polyglot Payloads</span>
          <span class="tag">Double Encoding</span>
          <span class="tag">Context Switching</span>
          <span class="tag">Filter Evasion</span>
          <span class="tag">No Quotes</span>
          <span class="tag">No Parentheses</span>
          <span class="tag">HTML Entities</span>
        </div>

        <p class="lab-description">
          Bypass our enterprise-grade WAF to execute alert(1). All common vectors are blocked.
          You'll need advanced techniques like polyglot payloads, encoding tricks, and context switching.
        </p>
      </div>

      <!-- WAF Rules -->
      <div class="waf-status">
        <div class="waf-rule"><i class="fas fa-check-circle"></i> <span>Script Tags Blocked</span></div>
        <div class="waf-rule"><i class="fas fa-check-circle"></i> <span>Event Handlers Removed</span></div>
        <div class="waf-rule"><i class="fas fa-check-circle"></i> <span>javascript: Stripped</span></div>
        <div class="waf-rule"><i class="fas fa-check-circle"></i> <span>HTML Entities Filtered</span></div>
        <div class="waf-rule"><i class="fas fa-check-circle"></i> <span>Quotes Removed</span></div>
        <div class="waf-rule"><i class="fas fa-check-circle"></i> <span>Parentheses Stripped</span></div>
      </div>

      <!-- Challenge Area -->
      <div class="challenge-area">
        <form class="input-form" method="GET" action="">
          <input type="text" name="input" class="challenge-input" placeholder="Try to bypass the WAF..."
            value="<?php echo htmlspecialchars($input); ?>" autocomplete="off">
          <button type="submit" class="submit-btn">
            <i class="fas fa-bolt"></i> Inject
          </button>
        </form>

        <!-- Output -->
        <?php if ($submitted): ?>
        <div class="output-section">
          <div class="output-label">
            <i class="fas fa-terminal"></i> Filtered Output:
          </div>
          <div class="output-content" id="output">
            <?php echo $filtered; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Success -->
        <?php if (isset($success_msg)): ?>
        <div class="success-message">
          <i class="fas fa-crown"></i>
          <h2>LEGENDARY!</h2>
          <p><?php echo $success_msg; ?></p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Hints -->
      <div class="hints-section">
        <?php if ($_SESSION['rxss_hard_attempts'] >= 5): ?>
        <div class="hint-card">
          <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1 - Encoding</div>
          <div class="hint-text">
            The WAF filters are case-sensitive and don't handle multiple encodings.
            Try: <code>%3C%73%63%72%69%70%74%3E</code> or mixed case.
          </div>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['rxss_hard_attempts'] >= 10): ?>
        <div class="hint-card">
          <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2 - Alternative Vectors</div>
          <div class="hint-text">
            If all tags are blocked, try breaking out of the current HTML context.
            Look at where your input appears in the page source. Can you inject into an attribute?
          </div>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['rxss_hard_attempts'] >= 15): ?>
        <div class="hint-card">
          <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #3 - Polyglot</div>
          <div class="hint-text">
            Try a polyglot payload that works in multiple contexts:
            <code>'-alert(1)-'</code> or <code>\"-alert(1)-\"</code> (but quotes are filtered... find another way!)
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Success Detection -->
  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
  window.addEventListener('load', function() {
    const originalAlert = window.alert;
    window.alert = function(msg) {
      if (msg == '1') {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
      return originalAlert.apply(this, arguments);
    };
  });
  </script>
</body>

</html>