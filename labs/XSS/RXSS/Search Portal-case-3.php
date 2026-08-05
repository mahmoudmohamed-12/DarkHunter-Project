<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();


$query = isset($_GET['q']) ? $_GET['q'] : '';

$filtered_query = preg_replace('/script/i', '', $query);

if (!isset($_SESSION['rxss_attempts'])) {
    $_SESSION['rxss_attempts'] = 0;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($query)) {
    $_SESSION['rxss_attempts']++;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        if (solveLab($pdo, 17)) {
            $success_msg = "🎉 Search Bypass Completed! +100 pts";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Portal - RXSS Lab 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/xss-vuln-case-3.css">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to XSS Labs</a>

    <!-- Lab Header -->
    <div class="lab-header">
      <div class="difficulty-badge">
        <i class="fas fa-fire"></i> Medium Difficulty
      </div>
      <h1 class="lab-title"><i class="fas fa-search"></i> Search Portal Vulnerability</h1>
      <p class="lab-description">
        This search portal reflects user input directly in the results page.
        Your goal is to execute a JavaScript alert(1) by exploiting the reflected XSS vulnerability.
        <strong>Note: Some basic filters are in place.</strong>
      </p>
    </div>

    <!-- Challenge Box -->
    <div class="challenge-box">
      <div class="challenge-icon">
        <i class="fas fa-bug"></i>
      </div>

      <form class="search-form" method="GET" action="">
        <div class="search-input-wrapper">
          <input type="text" name="q" class="search-input" placeholder="Search for anything..."
            value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
          <button type="submit" class="search-btn">
            <i class="fas fa-search"></i> Search
          </button>
        </div>
      </form>

      <!-- Results -->
      <?php if (!empty($query)): ?>
      <div class="results-section">
        <div class="results-header">
          <i class="fas fa-terminal"></i> Search Results for:
        </div>
        <div class="search-query-display" id="search-result">
          <?php 
                    // MEDIUM: Basic filter - remove script tags but keep everything else
                    $filtered = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $query);
                    // Also filter some event handlers but not all
                    $filtered = preg_replace('/on\w+\s*=/i', '', $filtered);
                    echo $filtered; 
                    ?>
        </div>

        <?php if (isset($success_msg)): ?>
        <div class="success-alert">
          <i class="fas fa-trophy"></i>
          <div>
            <h3>Challenge Completed!</h3>
            <p><?php echo $success_msg; ?></p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div class="results-section">
        <div class="empty-state">
          <i class="fas fa-search"></i>
          <p>Enter a search query to see results...</p>
        </div>
      </div>
      <?php endif; ?>

      <!-- Hint System -->
      <?php if ($_SESSION['rxss_attempts'] >= 3): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">
          The filter removes <code>&lt;script&gt;</code> tags and event handlers like <code>onclick</code>.
          Try using HTML tags that don't require scripts to execute JavaScript.
        </div>
      </div>
      <?php endif; ?>

      <?php if ($_SESSION['rxss_attempts'] >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">
          Some HTML tags can execute JavaScript automatically without event handlers.
          Research: <code>&lt;img&gt;</code>, <code>&lt;svg&gt;</code>, or <code>&lt;iframe&gt;</code> tags.
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Hidden form for success validation -->
  <form id="success-form" method="GET" action="" style="display: none;">
    <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
  // Success detection
  window.addEventListener('load', function() {
    // Check if alert was triggered
    const originalAlert = window.alert;
    window.alert = function(msg) {
      if (msg === '1' || msg === 1) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
      originalAlert(msg);
    };
  });
  </script>
</body>

</html>