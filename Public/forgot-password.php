<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/api/sendResetPasswordEmail.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  if (empty($email)) {
    $errors[] = "Please enter your email address";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address";
  } else {
    try {
      $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Delete old tokens for this user
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$user['id']]);

        // Store new token in database
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expires]);

        // Send reset email
        $emailResult = sendResetPasswordEmail($user['email'], $token);

        if (!$emailResult['success']) {
          $errors[] = "Failed to send reset email. Please try again later.";
        } else {
          $success = true;
        }
      } else {
        // Don't reveal if email exists or not for security
        $success = true;
      }
    } catch (PDOException $e) {
      $errors[] = "Something went wrong. Please try again later.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - DarkHunter 🔐</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/forgot-password.css">
  <!-- SweetAlert2 for Toast Notifications -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <!-- Floating Particles -->
  <script>
  for (let i = 0; i < 10; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.animationDelay = Math.random() * 20 + 's';
    particle.style.animationDuration = (15 + Math.random() * 10) + 's';
    document.body.appendChild(particle);
  }
  </script>

  <!-- Back Link -->
  <a href="login.php" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Login
  </a>

  <!-- Auth Container -->
  <div class="auth-container">
    <!-- Logo -->
    <div class="logo-section">
      <div class="logo-icon">
        <i class="fas fa-key"></i>
      </div>
      <h1 class="logo-title">Forgot Password?</h1>
      <p class="logo-subtitle">Enter your email to reset your password</p>
    </div>

    <!-- Success Message -->
    <?php if ($success): ?>
    <div class="success-msg">
      <i class="fas fa-check-circle"></i>
      <p>If an account exists with that email, we have sent a password reset link. Please check your inbox and spam
        folder.</p>
    </div>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="error-msg">
      <i class="fas fa-exclamation-circle"></i>
      <?php foreach ($errors as $err): ?>
      <p><?php echo htmlspecialchars($err); ?></p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <!-- Forgot Password Form -->
    <form action="forgot-password.php" method="POST" id="forgotForm">
      <div class="form-group">
        <i class="fas fa-envelope input-icon"></i>
        <input type="email" name="email" class="auth-input" placeholder="Enter your email address" required
          value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>

      <button type="submit" name="reset_btn" class="auth-btn">
        <i class="fas fa-paper-plane"></i> Send Reset Link
      </button>
    </form>
    <?php endif; ?>

    <!-- Footer -->
    <div class="auth-footer">
      <p>Remember your password?</p>
      <a href="login.php" class="auth-link">
        <i class="fas fa-sign-in-alt"></i> Back to Login
      </a>
    </div>
  </div>

  <script>
  // Form animation
  document.getElementById('forgotForm').addEventListener('submit', function() {
    const btn = this.querySelector('.auth-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
  });
  </script>
</body>

</html>