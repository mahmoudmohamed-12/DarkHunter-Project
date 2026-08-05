<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

if (!isset($_SESSION['dom_easy1_attempts'])) {
    $_SESSION['dom_easy1_attempts'] = 0;
}

// Check success
if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        $success = solveLab($pdo, 7);
        
        if ($success) {
            $success_msg = "🎉 DOM XSS #1 Completed! +50 pts";
        } else {
            $error_msg = "Error saving progress!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hash Fragment - DOM XSS Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/hash-case-1.css">

</head>

<body>
  <?php include_once __DIR__ . '/../../../includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy</div>
      <h1 class="lab-title">Hash Fragment Injection</h1>

      <div class="lab-tags">
        <span class="tag">DOM XSS</span>
        <span class="tag">location.hash</span>
        <span class="tag">innerHTML</span>
        <span class="tag">No Filters</span>
        <span class="tag">Client-Side</span>
      </div>

      <p>The page reads from window.location.hash and displays it using innerHTML. No server involved!</p>
    </div>

    <div class="challenge-box">
      <h3><i class="fas fa-link"></i> Current URL Hash:</h3>
      <div class="url-display" id="url-display">
        <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?><span>#</span>
      </div>

      <p>Add something after the # in the URL and see what happens!</p>

      <div id="content">
        Waiting for hash fragment...
      </div>

      <div class="hint-box">
        <strong><i class="fas fa-lightbulb"></i> Hint:</strong> Try adding
        <code>#&lt;img src=x onerror=alert(1)&gt;</code> to the URL
      </div>
    </div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
  // Vulnerable code - reads hash and inserts into DOM
  function displayHash() {
    var hash = window.location.hash.substring(1); // Remove the # 
    if (hash) {
      document.getElementById('content').innerHTML = 'Hash content: ' + decodeURIComponent(hash);
      document.getElementById('url-display').innerHTML = window.location.href;
    }
  }

  // Run on load and when hash changes
  window.addEventListener('load', displayHash);
  window.addEventListener('hashchange', displayHash);

  // Detect success
  const originalAlert = window.alert;
  window.alert = function(msg) {
    if (msg == '1') {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
    return originalAlert.apply(this, arguments);
  };
  </script>
</body>

</html>