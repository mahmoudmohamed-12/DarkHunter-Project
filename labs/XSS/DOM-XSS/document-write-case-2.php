<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

if (!isset($_SESSION['dom_easy2_attempts'])) {
    $_SESSION['dom_easy2_attempts'] = 0;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        $success = solveLab($pdo, 5);
        
        if ($success) {
            $success_msg = "🎉 DOM XSS #2 Completed! +50 pts";
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
  <title>Document.Write - DOM XSS Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/document-write-case-2.css">


</head>

<body><?php include_once __DIR__ . '/../../../includes/navbar.php';
  ?><div class="container"><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>
    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i>Easy</div>
      <h1 class="lab-title">Document.Write Sink</h1>
      <div class="lab-tags"><span class="tag">DOM XSS</span><span class="tag">document.write</span><span class="tag">URL
          Parameter</span><span class="tag">No Filters</span><span class="tag">Client-Side</span></div>
      <p>The page uses document.write() to display URL parameters. Classic DOM XSS sink !</p>
    </div>
    <div class="challenge-box">
      <h3>Enter your name:</h3><input type="text" class="param-input" id="nameInput" placeholder="Your name..."><button
        class="action-btn" onclick="updatePage()"><i class="fas fa-play"></i>Update Page </button>
      <div class="output-box" id="output">Output will appear here... </div>
      <div style="margin-top: 20px; padding: 20px; background: rgba(255,204,0,0.1); border-radius: 10px;"><strong><i
            class="fas fa-lightbulb"></i>Hint:</strong>Try: <code>&lt;script&gt;alert(1)&lt;/script&gt;</code>or
        <code>&lt;img src=x onerror=alert(1)&gt;</code></div>
    </div>
  </div>
  <form id="success-form" method="GET" style="display: none;"><input type="hidden" name="check" value="true"><input
      type="hidden" name="solved" value="0" id="solved-flag"></form>
  <script>
  function updatePage() {
    var name = document.getElementById('nameInput').value;
    // VULNERABLE: Directly writing user input to page
    document.getElementById('output').innerHTML = '<h2>Welcome, ' + name + '!</h2>';
  }

  // Also vulnerable to URL parameter
  var urlParams = new URLSearchParams(window.location.search);
  var paramName = urlParams.get('name');

  if (paramName) {
    document.write(
      '<div style="padding:20px;background:rgba(255,0,0,0.2);border:2px solid #ff0040;border-radius:10px;margin:20px;"><h3>Welcome, ' +
      paramName + '!</h3></div>');
  }

  const originalAlert = window.alert;

  window.alert = function(msg) {
    if (msg == '1') {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }

    return originalAlert.apply(this, arguments);
  }

  ;
  </script>
</body>

</html>