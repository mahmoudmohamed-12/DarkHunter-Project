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
$verification_error = false;
$resend_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $errors[] = "Please enter both username and password";
  } else {
    try {

      $stmt = $pdo->prepare("
        SELECT id, username, email, password, is_verified, total_xp, score 
        FROM users 
        WHERE username = ? OR email = ?
      ");
      $stmt->execute([$username, $username]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user['password'])) {

        // ❌ Not verified
        if ($user['is_verified'] == 0) {
          $verification_error = true;
          $resend_email = $user['email'];

          // ✅ Save email for resend
          $_SESSION['resend_email'] = $user['email'];
        } else {
          // ✅ Login
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['username'] = $user['username'];
          $_SESSION['email'] = $user['email'];

          header("Location: index.php");
          exit();
        }
      } else {
        $errors[] = "Invalid username or password";
      }
    } catch (PDOException $e) {
      $errors[] = "Something went wrong.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - DarkHunter 🔐</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/register.css">
  <link rel="stylesheet" type="text/css" href="css/verification-message.css">
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
  <a href="index.php" class="back-link">
    <i class="fas fa-arrow-left"></i> Back to Home
  </a>

  <!-- Auth Container -->
  <div class="auth-container">
    <!-- Logo -->
    <div class="logo-section">
      <div class="logo-icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <h1 class="logo-title">Welcome Back</h1>
      <p class="logo-subtitle">Login to continue your hunt</p>
    </div>

    <!-- Error Messages (General Errors) -->
    <?php if (!empty($errors)): ?>
      <div class="error-msg">
        <i class="fas fa-exclamation-circle"></i>
        <?php foreach ($errors as $err): ?>
          <p><?php echo htmlspecialchars($err); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- 🔥 EMAIL VERIFICATION WARNING MESSAGE -->
    <?php if ($verification_error): ?>
      <div class="verification-inline warning" id="verificationInline">
        <div class="verification-inline-icon">
          <i class="fas fa-envelope-open-text"></i>
        </div>
        <div class="verification-inline-content">
          <h3 class="verification-inline-title">Account Not Activated</h3>
          <p class="verification-inline-text">
            Your account is not activated. Please check your email to verify your account.
            If you didn't receive the email, check your spam folder or
            <a href="#" id="resendLink" class="resend-link" data-email="<?php echo htmlspecialchars($resend_email); ?>">
              click here to resend
            </a>.
          </p>
          <div id="resendTimer" class="resend-timer" style="display: none;">
            <i class="fas fa-clock"></i> Please wait <span id="timerCount">30</span>s before resending. Check your spam
            folder!
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Social Login -->
    <div class="social-login">
      <div class="social-btn" title="GitHub">
        <i class="fab fa-github"></i>
      </div>
      <div class="social-btn" title="Google">
        <i class="fab fa-google"></i>
      </div>
      <div class="social-btn" title="Discord">
        <i class="fab fa-discord"></i>
      </div>
    </div>

    <div class="divider">
      <span>OR LOGIN WITH EMAIL</span>
    </div>

    <!-- Login Form -->
    <form action="login.php" method="POST" id="loginForm">
      <div class="form-group">
        <i class="fas fa-user input-icon"></i>
        <input type="text" name="username" class="auth-input" placeholder="Username or Email" required
          value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      </div>

      <div class="form-group">
        <i class="fas fa-lock input-icon"></i>
        <input type="password" name="password" class="auth-input" placeholder="Password" required>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <label
          style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 0.85rem; cursor: pointer;">
          <input type="checkbox" name="remember" style="accent-color: #00f0ff;">
          Remember me
        </label>
        <a href="forgot-password.php" style="color: #00f0ff; font-size: 0.85rem; text-decoration: none;">
          Forgot Password?
        </a>
      </div>

      <button type="submit" name="login_btn" class="auth-btn">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>

    <!-- Footer -->
    <div class="auth-footer">
      <p>Don't have an account?</p>
      <a href="register.php" class="auth-link">
        <i class="fas fa-user-plus"></i> Create an account
      </a>
    </div>
  </div>

  <script>
    // ============================================
    // AJAX RESEND VERIFICATION WITH TOAST & TIMER
    // ============================================

    const resendLink = document.getElementById('resendLink');
    const resendTimer = document.getElementById('resendTimer');
    const timerCount = document.getElementById('timerCount');
    let countdownInterval;
    const COOLDOWN_SECONDS = 30;

    // Toast configuration matching DarkHunter dark theme
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      background: '#12121a',
      color: '#e8e8f0',
      iconColor: '#ff6b35',
      customClass: {
        popup: 'darkhunter-toast'
      },
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    });

    if (resendLink) {
      resendLink.addEventListener('click', function(e) {
        e.preventDefault();

        const email = this.getAttribute('data-email');
        if (!email) {
          Toast.fire({
            icon: 'error',
            title: 'Email not found. Please try logging in again.'
          });
          return;
        }

        // Disable link and show timer
        this.style.pointerEvents = 'none';
        this.style.opacity = '0.5';
        this.style.textDecoration = 'none';
        this.textContent = 'Resending...';

        if (resendTimer) {
          resendTimer.style.display = 'block';
        }

        // Start countdown
        let seconds = COOLDOWN_SECONDS;
        if (timerCount) timerCount.textContent = seconds;

        countdownInterval = setInterval(() => {
          seconds--;
          if (timerCount) timerCount.textContent = seconds;

          if (seconds <= 0) {
            clearInterval(countdownInterval);
            this.style.pointerEvents = 'auto';
            this.style.opacity = '1';
            this.style.textDecoration = 'underline';
            this.textContent = 'click here to resend';
            if (resendTimer) resendTimer.style.display = 'none';
          }
        }, 1000);

        // Send AJAX request
        fetch('resend-verification.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'action=resend'
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Toast.fire({
                icon: 'success',
                title: data.message || 'Verification email resent successfully!',
                iconColor: '#22c55e'
              });
            } else {
              Toast.fire({
                icon: 'error',
                title: data.message || 'Failed to resend email. Please try again.',
                iconColor: '#ff3c38'
              });
              // Reset timer on error
              clearInterval(countdownInterval);
              this.style.pointerEvents = 'auto';
              this.style.opacity = '1';
              this.style.textDecoration = 'underline';
              this.textContent = 'click here to resend';
              if (resendTimer) resendTimer.style.display = 'none';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Toast.fire({
              icon: 'error',
              title: 'Network error. Please check your connection.',
              iconColor: '#ff3c38'
            });
            // Reset timer on error
            clearInterval(countdownInterval);
            this.style.pointerEvents = 'auto';
            this.style.opacity = '1';
            this.style.textDecoration = 'underline';
            this.textContent = 'click here to resend';
            if (resendTimer) resendTimer.style.display = 'none';
          });
      });
    }

    // Form animation
    document.getElementById('loginForm').addEventListener('submit', function() {
      const btn = this.querySelector('.auth-btn');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
      btn.disabled = true;
    });
  </script>
</body>

</html>