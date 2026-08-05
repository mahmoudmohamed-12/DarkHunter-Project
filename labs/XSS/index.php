<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;


$userData = null;
if (isset($_SESSION['user_id'])) {
  $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmtUser->execute([$_SESSION['user_id']]);
  $userData = $stmtUser->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XSS Labs - DarkHunter</title>
  <link rel="stylesheet" type="text/css" href="/DarkHunter/labs/XSS/css/index.css">

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


</head>

<body>
  <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>

  <!-- Floating Particles -->
  <script>
    for (let i = 0; i < 15; i++) {
      const particle = document.createElement('div');
      particle.className = 'particle';
      particle.style.left = Math.random() * 100 + '%';
      particle.style.animationDelay = Math.random() * 15 + 's';
      particle.style.animationDuration = (10 + Math.random() * 10) + 's';
      document.body.appendChild(particle);
    }
  </script>

  <!-- Back Button -->
  <a href="../../Public/index.php" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Dashboard
  </a>

  <div class="container">
    <!-- Hero -->
    <section class="hero fade-in">
      <h1><i class="fas fa-code"></i> XSS Labs</h1>
      <p>Choose your attack vector: DOM-based or Reflected XSS vulnerabilities</p>
    </section>

    <!-- Lab Types Grid -->
    <div class="lab-types-grid">
      <!-- DOM XSS Card -->
      <a href="DOM-XSS/" class="lab-type-card dom-xss fade-in delay-1">
        <div class="icon-container">
          <i class="fas fa-sitemap"></i>
        </div>
        <h2>DOM XSS</h2>
        <p class="description">
          DOM-based Cross-Site Scripting occurs when the application's client-side JavaScript processes data from an
          untrusted source in an unsafe way.
        </p>
        <ul class="features-list">
          <li><i class="fas fa-check-circle"></i> Client-side vulnerability</li>
          <li><i class="fas fa-check-circle"></i> No server interaction</li>
          <li><i class="fas fa-check-circle"></i> Hash/Fragment based</li>
          <li><i class="fas fa-check-circle"></i> document.write sinks</li>
          <li><i class="fas fa-check-circle"></i> innerHTML manipulation</li>
        </ul>
        <span class="btn-enter">
          <i class="fas fa-play"></i> Enter Labs
        </span>
      </a>

      <!-- Reflected XSS Card -->
      <a href="RXSS/" class="lab-type-card reflected-xss fade-in delay-2">
        <div class="icon-container">
          <i class="fas fa-exchange-alt"></i>
        </div>
        <h2>Reflected XSS</h2>
        <p class="description">
          Reflected XSS occurs when malicious scripts are reflected off a web server, such as in an error message or
          search result.
        </p>
        <ul class="features-list">
          <li><i class="fas fa-check-circle"></i> Server-side reflection</li>
          <li><i class="fas fa-check-circle"></i> URL parameters</li>
          <li><i class="fas fa-check-circle"></i> Social engineering</li>
          <li><i class="fas fa-check-circle"></i> GET/POST requests</li>
          <li><i class="fas fa-check-circle"></i> Immediate response</li>
        </ul>
        <span class="btn-enter">
          <i class="fas fa-play"></i> Enter Labs
        </span>
      </a>
    </div>

    <!-- Info Section -->
    <div class="info-section fade-in delay-2">
      <h3><i class="fas fa-info-circle"></i> About XSS Vulnerabilities</h3>
      <p>
        <strong>Cross-Site Scripting (XSS)</strong> attacks are a type of injection where malicious scripts are injected
        into trusted websites.
        XSS attacks occur when an attacker uses a web application to send malicious code, generally in the form of a
        browser side script,
        to a different end user. Flaws that allow these attacks to succeed are quite widespread and occur anywhere a web
        application
        uses input from a user within the output it generates without validating or encoding it.
      </p>
    </div>
  </div>

  <script src="../../assets/js/main.js"></script>
  </script>
  <?php if (!$isLoggedIn): ?>
    <script>
      window.addEventListener("load", function() {
        if (typeof LoginModal !== "undefined") {
          LoginModal.show();
        }
      });
    </script>
  <?php endif; ?>

</body>

</html>