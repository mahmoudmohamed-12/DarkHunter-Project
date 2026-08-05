<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$username = isset($_GET['user']) ? $_GET['user'] : 'Guest';
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

if (!isset($_SESSION['rxss2_attempts'])) {
    $_SESSION['rxss2_attempts'] = 0;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user']) && $_GET['user'] !== 'Guest') {
    $_SESSION['rxss2_attempts']++;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        if (solveLab($pdo, 16)) {
            $success_msg = "🎉 Profile XSS Exploited! +100 pts";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile - RXSS Lab 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/xss-vuln-case-4.css">


</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to XSS Labs</a>

    <!-- Lab Header -->
    <div class="lab-header">
      <div class="lab-info">
        <h1><i class="fas fa-user-circle"></i> Profile Viewer</h1>
        <p>Reflected XSS in URL parameter - Different context, different challenge</p>
      </div>
      <div class="difficulty-badge">
        <i class="fas fa-fire"></i> Medium
      </div>
    </div>

    <!-- Profile Card -->
    <div class="profile-card">
      <div class="profile-header">
        <div class="avatar">
          <i class="fas fa-user-secret"></i>
        </div>
        <div class="profile-name" id="username-display">
          <?php 
                    // MEDIUM: Different filtering - removes < and > but keeps other chars
                    $filtered_name = str_replace(['<', '>'], '', $username);
                    echo $filtered_name; 
                    ?>
        </div>
        <span class="profile-role">Security Researcher</span>
      </div>

      <div class="profile-body">
        <!-- Current URL -->
        <div class="url-display">
          <div class="url-label"><i class="fas fa-link"></i> Current URL Parameter</div>
          <div class="url-value"><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></div>
        </div>

        <!-- Warning -->
        <div class="warning-box">
          <i class="fas fa-exclamation-triangle"></i>
          <p><strong>Challenge:</strong> Execute alert(1) without using &lt; or &gt; characters.
            The profile name is reflected in the page. Find another way to inject JavaScript!</p>
        </div>

        <!-- Quick Actions -->
        <div class="actions">
          <a href="?user=Admin" class="action-btn"><i class="fas fa-user-shield"></i> View Admin</a>
          <a href="?user=TestUser" class="action-btn"><i class="fas fa-user"></i> View User</a>
          <a href="?user=Guest" class="action-btn primary"><i class="fas fa-redo"></i> Reset</a>
        </div>

        <!-- Raw Input Display -->
        <div class="input-display">
          <div class="input-label"><i class="fas fa-code"></i> Raw Parameter Value (Reflected)</div>
          <div class="input-value" id="raw-input">
            <?php 
                        // MEDIUM: Only strips < and >, allows everything else including quotes
                        $raw_display = str_replace(['<', '>'], '', $_GET['user'] ?? 'Guest');
                        echo $raw_display;
                        ?>
          </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($success_msg)): ?>
        <div class="success-box">
          <i class="fas fa-trophy"></i>
          <h2>Challenge Completed!</h2>
          <p><?php echo $success_msg; ?></p>
        </div>
        <?php endif; ?>

        <!-- Hints -->
        <?php if ($_SESSION['rxss2_attempts'] >= 3): ?>
        <div class="hint-panel">
          <div class="hint-header">
            <i class="fas fa-lightbulb"></i> Hint #1
          </div>
          <div class="hint-content">
            Angle brackets are filtered, but quotes are not. Think about where your input appears
            in the HTML. Check the page source! Look for <code>id="username-display"</code>.
          </div>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['rxss2_attempts'] >= 5): ?>
        <div class="hint-panel">
          <div class="hint-header">
            <i class="fas fa-lightbulb"></i> Hint #2
          </div>
          <div class="hint-content">
            Try to break out of the HTML context. If your input appears inside a tag attribute,
            you might be able to inject an event handler like
            <code>" onmouseover="alert(1)</code> or close the tag and open a new one.
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Success Detection -->
  <form id="success-check" method="GET" style="display: none;">
    <input type="hidden" name="user" value="<?php echo htmlspecialchars($username); ?>">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-input">
  </form>

  <script>
  // Detect successful XSS
  const originalAlert = window.alert;
  window.alert = function(msg) {
    if (msg == '1') {
      document.getElementById('solved-input').value = '1';
      document.getElementById('success-check').submit();
    }
    return originalAlert.apply(this, arguments);
  };
  </script>
</body>

</html>