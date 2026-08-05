<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case7_attempts'])) {
  $_SESSION['html_case7_attempts'] = 0;
}
if (!isset($_SESSION['html_case7_solved'])) {
  $_SESSION['html_case7_solved'] = false;
}

// ─── Vulnerable Filter Function (Simulated WAF) ──────────────────────────
function naiveFilter($input)
{
  // This filter tries to block common HTML tags but is easily bypassed
  $blocked = ['<script', '</script>', 'javascript:', 'onerror=', 'onload=', 'onmouseover='];
  $filtered = str_ireplace($blocked, '', $input);
  return $filtered;
}

// ─── Handle User Input ───────────────────────────────────────────────────
$user_input = $_POST['content'] ?? '';
$filtered_input = naiveFilter($user_input);
$html_injected = false;
$filter_bypassed = false;
$flag_triggered = false;

if (!empty($user_input)) {
  // Check if HTML still exists after filtering
  if (preg_match('/<[a-zA-Z][^>]*>/', $filtered_input)) {
    $html_injected = true;

    // Check if user bypassed the filter (input has HTML but different from filtered)
    if ($user_input !== $filtered_input && preg_match('/<[a-zA-Z][^>]*>/', $filtered_input)) {
      $filter_bypassed = true;

      // Check for flag-triggering payloads
      if (preg_match('/<(h1|h2|div|span|a|img|iframe|form|input|button|marquee|svg|math)\s*[^>]*>/i', $filtered_input)) {
        $flag_triggered = true;
      }
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case7_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case7_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case7_solved'] = true;
  $already_solved = true;
  $success_msg = "Impressive! You've successfully bypassed a naive HTML filter using encoding tricks and parser confusion. You demonstrated that simple blacklists are insufficient against determined attackers!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
  $_SESSION['html_case7_attempts']++;
}

$attempts = $_SESSION['html_case7_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Content Filter Test - HTML Injection Case 7 (Filter Evasion)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-filter"></i> Content Filter Test</h1>
      <p class="lab-description">Test your content against our security filter. This hard HTML Injection challenge uses
        a <strong>naive blacklist filter</strong> that attempts to block dangerous tags. <strong>Bypass the
          filter</strong> using encoding, case variations, and parser tricks!</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Filter Evasion HTML Injection vulnerability. You can continue exploring, but
            no additional points will be awarded.</p>
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

    <!-- Filter Info Banner -->
    <div class="filter-banner">
      <i class="fas fa-shield-alt"></i>
      <div class="filter-content">
        <strong>Security Filter Active</strong>
        <span>Blocking: &lt;script, javascript:, onerror=, onload=, onmouseover=</span>
      </div>
    </div>

    <!-- Filter Grid -->
    <div class="filter-grid">

      <!-- Input Panel -->
      <div class="filter-card input-card">
        <div class="card-header">
          <i class="fas fa-keyboard"></i>
          <h3>Your Input</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Filter Bypassable</span>
        </div>

        <form method="POST" action="" class="filter-form" id="filter-form">
          <div class="form-group">
            <label><i class="fas fa-edit"></i> Content</label>
            <textarea name="content" rows="8" class="form-textarea"
              placeholder="Enter content to test against the filter..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
          </div>

          <button type="submit" class="btn-test">
            <i class="fas fa-vial"></i> Test Filter
          </button>
        </form>

        <div class="filter-status">
          <div class="status-row">
            <span class="status-label">Filter Applied:</span>
            <span class="status-value <?php echo $user_input !== $filtered_input ? 'triggered' : ''; ?>">
              <?php echo $user_input !== $filtered_input ? 'YES (Blocked Some)' : 'NO (Clean)'; ?>
            </span>
          </div>
          <div class="status-row">
            <span class="status-label">HTML Still Present:</span>
            <span class="status-value <?php echo $html_injected ? 'danger' : ''; ?>">
              <?php echo $html_injected ? 'YES' : 'NO'; ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Filtered Output -->
      <div class="filter-card output-card">
        <div class="card-header">
          <i class="fas fa-filter"></i>
          <h3>Filtered Output</h3>
        </div>

        <div class="output-display">
          <?php if (!empty($filtered_input)): ?>
            <!-- VULNERABLE: Filtered output still rendered without encoding -->
            <div class="rendered-content">
              <?php echo $filtered_input; ?>
            </div>
          <?php else: ?>
            <div class="output-placeholder">
              <i class="fas fa-eye-slash"></i>
              <p>Submit content to see the filtered result</p>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($filter_bypassed): ?>
          <div class="bypass-alert">
            <i class="fas fa-check-circle"></i>
            <div class="bypass-content">
              <strong>Filter Bypassed!</strong>
              <span>You successfully bypassed the naive filter!</span>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($html_injected && !$filter_bypassed): ?>
          <div class="injection-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <span>HTML detected but filter didn't block anything!</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Raw Comparison -->
      <?php if (!empty($user_input)): ?>
        <div class="filter-card comparison-card">
          <div class="card-header">
            <i class="fas fa-code-compare"></i>
            <h3>Before / After Filter</h3>
          </div>
          <div class="comparison-content">
            <div class="comparison-column">
              <span class="column-label">Original Input</span>
              <pre class="column-code"><code><?php echo htmlspecialchars($user_input); ?></code></pre>
            </div>
            <div class="comparison-arrow">
              <i class="fas fa-arrow-right"></i>
            </div>
            <div class="comparison-column">
              <span class="column-label">After Filter</span>
              <pre class="column-code"><code><?php echo htmlspecialchars($filtered_input); ?></code></pre>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Bypass Techniques -->
      <div class="filter-card techniques-card">
        <div class="card-header">
          <i class="fas fa-magic"></i>
          <h3>Bypass Techniques</h3>
        </div>
        <div class="techniques-list">
          <div class="technique-item">
            <div class="technique-name">HTML Entities</div>
            <code class="technique-code">&amp;lt;h1&amp;gt;Test&amp;lt;/h1&amp;gt;</code>
            <span class="technique-desc">Use &amp;lt; instead of &lt;</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Case Variation</div>
            <code class="technique-code">&lt;ScRiPt&gt;alert(1)&lt;/ScRiPt&gt;</code>
            <span class="technique-desc">Mixed case to bypass case-sensitive filters</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Double Encoding</div>
            <code class="technique-code">%253Cdiv%253E</code>
            <span class="technique-desc">URL encode the percent sign</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Alternative Tags</div>
            <code class="technique-code">&lt;svg onload=alert(1)&gt;</code>
            <span class="technique-desc">Use tags not in the blacklist</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Null Bytes</div>
            <code class="technique-code">&lt;scr%00ipt&gt;</code>
            <span class="technique-desc">Insert null bytes to break filter</span>
          </div>
          <div class="technique-item">
            <div class="technique-name">Unicode Normalization</div>
            <code class="technique-code">&lt;img src=x onerror=alert(1)&gt;</code>
            <span class="technique-desc">Use Unicode variants of characters</span>
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
          <span>Input Length: <?php echo strlen($user_input); ?> chars</span>
          <span>Filter Modified: <?php echo $user_input !== $filtered_input ? 'YES' : 'NO'; ?></span>
          <span>HTML Injected: <?php echo $html_injected ? 'YES' : 'NO'; ?></span>
          <span>Filter Bypassed: <?php echo $filter_bypassed ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The filter only blocks exact strings like <code>&lt;script</code> and
          <code>javascript:</code>. Try using HTML tags that are <strong>NOT</strong> in the blacklist, like
          <code>&lt;h1&gt;</code>, <code>&lt;div&gt;</code>, or <code>&lt;marquee&gt;</code>.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try case variations! The filter might be case-sensitive. Also try HTML entities:
          <code>&amp;lt;h1&amp;gt;PWNED&amp;lt;/h1&amp;gt;</code> - sometimes the browser decodes entities after the
          filter runs.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use simple tags not in the blacklist:
          <code>&lt;h1 style="color:red"&gt;FILTER BYPASSED&lt;/h1&gt;</code> or
          <code>&lt;div style="background:black;color:lime;padding:20px"&gt;&lt;h1&gt;PWNED&lt;/h1&gt;&lt;/div&gt;</code>.
          The filter only blocks script-related patterns, so basic HTML tags pass through!
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
      const bypassAlert = document.querySelector('.bypass-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (bypassAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>