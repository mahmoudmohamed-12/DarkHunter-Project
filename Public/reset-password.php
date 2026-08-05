<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$errors = [];
$success = false;
$token = $_GET['token'] ?? '';
$validToken = false;
$userId = null;

// Validate token
if (!empty($token)) {
  try {
    $stmt = $pdo->prepare("
SELECT pr.user_id, pr.expires_at, u.email, u.username
FROM password_resets pr
JOIN users u ON pr.user_id = u.id
WHERE pr.token = ?
");
    $stmt->execute([$token]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resetData) {
      if (strtotime($resetData['expires_at']) > time()) {
        $validToken = true;
        $userId = $resetData['user_id'];
      } else {
        $errors[] = "This reset link has expired. Please request a new one.";
      }
    } else {
      $errors[] = "Invalid reset link. Please request a new password reset.";
    }
  } catch (PDOException $e) {
    $errors[] = "Something went wrong. Please try again later.";
  }
} else {
  $errors[] = "No reset token provided. Please request a password reset.";
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if (empty($password)) {
    $errors[] = "Please enter a new password";
  } elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long";
  } elseif (!preg_match('/[A-Z]/', $password)) {
    $errors[] = "Password must contain at least one uppercase letter";
  } elseif (!preg_match('/[a-z]/', $password)) {
    $errors[] = "Password must contain at least one lowercase letter";
  } elseif (!preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one number";
  } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
    $errors[] = "Password must contain at least one special character";
  }

  if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match";
  }

  if (empty($errors)) {
    try {
      // Hash new password
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      // Update user password
      $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
      $stmt->execute([$hashedPassword, $userId]);

      // Delete used token
      $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
      $stmt->execute([$userId]);

      $success = true;
    } catch (PDOException $e) {
      $errors[] = "Failed to reset password. Please try again.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - DarkHunter 🔐</title>
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
        <i class="fas fa-lock-open"></i>
      </div>
      <h1 class="logo-title">Reset Password</h1>
      <p class="logo-subtitle">Create a new secure password</p>
    </div>

    <!-- Success Message -->
    <?php if ($success): ?>
      <div class="success-msg">
        <i class="fas fa-check-circle"></i>
        <p>Your password has been reset successfully! You can now login with your new password.</p>
      </div>
      <div style="text-align: center; margin-top: 20px;">
        <a href="login.php" class="auth-link" style="font-size: 1rem;">
          <i class="fas fa-sign-in-alt"></i> Go to Login
        </a>
      </div>
    <?php else: ?>

      <!-- Error Messages -->
      <?php if (!empty($errors)): ?>
        <div class="error-msg">
          <i class="fas fa-exclamation-circle"></i>
          <?php foreach ($errors as $err): ?>
            <p><?php echo htmlspecialchars($err); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($validToken): ?>
        <!-- Password Reset Form -->
        <form action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" id="resetForm">
          <div class="form-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="password" class="auth-input" placeholder="New Password" required id="passwordInput"
              minlength="8">
          </div>

          <div class="form-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="confirm_password" class="auth-input" placeholder="Confirm New Password" required
              id="confirmPasswordInput">
          </div>

          <!-- Password Requirements -->
          <div class="password-requirements"
            style="margin-bottom: 20px; padding: 12px; background: rgba(0,0,0,0.3); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
            <p
              style="color: rgba(255,255,255,0.6); font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">
              <i class="fas fa-shield-alt"></i> Password Requirements
            </p>
            <ul style="list-style: none; padding: 0; margin: 0;">
              <li id="req-length"
                style="color: #666; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; margin: 4px 0; transition: all 0.3s ease;">
                <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px;"></i> At least 8 characters
              </li>
              <li id="req-upper"
                style="color: #666; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; margin: 4px 0; transition: all 0.3s ease;">
                <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px;"></i> One uppercase letter
              </li>
              <li id="req-lower"
                style="color: #666; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; margin: 4px 0; transition: all 0.3s ease;">
                <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px;"></i> One lowercase letter
              </li>
              <li id="req-number"
                style="color: #666; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; margin: 4px 0; transition: all 0.3s ease;">
                <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px;"></i> One number
              </li>
              <li id="req-special"
                style="color: #666; font-size: 0.8rem; font-family: 'JetBrains Mono', monospace; margin: 4px 0; transition: all 0.3s ease;">
                <i class="fas fa-circle" style="font-size: 6px; margin-right: 8px;"></i> One special character
              </li>
            </ul>
          </div>

          <button type="submit" name="reset_btn" class="auth-btn">
            <i class="fas fa-key"></i> Reset Password
          </button>
        </form>
      <?php else: ?>
        <!-- Invalid Token -->
        <div style="text-align: center; margin-top: 20px;">
          <a href="forgot-password.php" class="auth-link" style="font-size: 1rem;">
            <i class="fas fa-redo"></i> Request New Reset Link
          </a>
        </div>
      <?php endif; ?>

      <!-- Footer -->
      <div class="auth-footer">
        <p>Remember your password?</p>
        <a href="login.php" class="auth-link">
          <i class="fas fa-sign-in-alt"></i> Back to Login
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // Password validation
    const passwordInput = document.getElementById('passwordInput');
    const confirmInput = document.getElementById('confirmPasswordInput');

    function validatePassword() {
      const password = passwordInput.value;

      // Length
      const lengthValid = password.length >= 8;
      updateRequirement('req-length', lengthValid);

      // Uppercase
      const upperValid = /[A-Z]/.test(password);
      updateRequirement('req-upper', upperValid);

      // Lowercase
      const lowerValid = /[a-z]/.test(password);
      updateRequirement('req-lower', lowerValid);

      // Number
      const numberValid = /[0-9]/.test(password);
      updateRequirement('req-number', numberValid);

      // Special
      const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);
      updateRequirement('req-special', specialValid);
    }

    function updateRequirement(id, valid) {
      const el = document.getElementById(id);
      if (valid) {
        el.style.color = '#00ff88';
        el.querySelector('i').className = 'fas fa-check-circle';
      } else {
        el.style.color = '#666';
        el.querySelector('i').className = 'fas fa-circle';
      }
    }

    if (passwordInput) {
      passwordInput.addEventListener('input', validatePassword);
    }

    // Form animation
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
      resetForm.addEventListener('submit', function() {
        const btn = this.querySelector('.auth-btn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
        btn.disabled = true;
      });
    }
  </script>
</body>

</html>