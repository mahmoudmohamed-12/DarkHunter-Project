<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$email = isset($_GET['email']) ? $_GET['email'] : '';
$submitted = isset($_GET['subscribe']) && $_GET['subscribe'] === '1';

if (!isset($_SESSION['rxss_easy2_attempts'])) {
    $_SESSION['rxss_easy2_attempts'] = 0;
}
if ($submitted && !empty($email)) {
    $_SESSION['rxss_easy2_attempts']++;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        if (solveLab($pdo, 15)) {
            $success_msg = "🎉 Newsletter XSS Exploited! +50 pts";
        }
    }
}

$confirmation_msg = '';
if ($submitted) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $confirmation_msg = "Thank you! Confirmation sent to: " . $email;
    } else {
        $confirmation_msg = "Error: Invalid email format -> " . $email;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Newsletter - RXSS Easy 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/xss-vuln-case-2.css">


  </style>
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to XSS Labs</a>

    <!-- Lab Header -->
    <div class="lab-header">
      <div class="difficulty-badge">
        <i class="fas fa-bolt"></i> Easy Difficulty
      </div>
      <h1 class="lab-title">Newsletter</h1>
      <p class="lab-subtitle">Subscribe to our security newsletter</p>
    </div>

    <!-- Main Card -->
    <div class="main-card">
      <div class="newsletter-icon">
        <i class="fas fa-envelope-open-text"></i>
      </div>

      <h2 class="card-title">Stay Updated</h2>
      <p class="card-description">
        Join 10,000+ security professionals receiving weekly updates.
        <br>Enter your email to subscribe. <strong>No validation on error messages!</strong>
      </p>

      <!-- Subscribe Form -->
      <form class="subscribe-form" method="GET" action="">
        <div class="input-wrapper">
          <input type="text" name="email" class="email-input" placeholder="Enter your email address..."
            value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
          <button type="submit" name="subscribe" value="1" class="subscribe-btn">
            <i class="fas fa-paper-plane"></i> Subscribe
          </button>
        </div>
      </form>

      <!-- Confirmation Message (Vulnerable) -->
      <?php if ($submitted): ?>
      <div class="confirmation-box">
        <i class="fas fa-info-circle confirmation-icon"></i>
        <div class="confirmation-text">
          <?php 
                    // EASY: No filtering on error message - direct reflection!
                    echo $confirmation_msg; 
                    ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Success -->
      <?php if (isset($success_msg)): ?>
      <div class="success-box">
        <i class="fas fa-trophy"></i>
        <h3>Challenge Completed!</h3>
        <p><?php echo $success_msg; ?></p>
      </div>
      <?php endif; ?>

      <!-- Hint -->
      <?php if ($_SESSION['rxss_easy2_attempts'] >= 2): ?>
      <div class="hint-section">
        <div class="hint-header">
          <i class="fas fa-lightbulb"></i> Hint
        </div>
        <div class="hint-text">
          The error message displays your input directly. Try entering something like
          <code>&lt;img src=x onerror=alert(1)&gt;</code> instead of an email address.
          The validation fails, but your input is still reflected!
        </div>
      </div>
      <?php endif; ?>

      <!-- Features -->
      <div class="features">
        <div class="feature"><i class="fas fa-check"></i> Weekly Security Tips</div>
        <div class="feature"><i class="fas fa-check"></i> Zero-day Alerts</div>
        <div class="feature"><i class="fas fa-check"></i> CTF Updates</div>
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