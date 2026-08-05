<?php
/**
 * DarkHunter Registration Page
 * With Email Verification Flow
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/brevo_mailer.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors = [];
$registration_success = false;
$registered_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Basic sanitize
    $user = htmlspecialchars($user);
    $email = htmlspecialchars($email);

    // Validation
    if (strlen($user) < 3 || strlen($user) > 20) {
        $errors[] = "Username must be 3-20 characters";
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $user)) {
        $errors[] = "Username can only contain letters, numbers and underscore";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // 🔥 Strong Password Validation
    if (strlen($pass) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if (!preg_match('/[A-Z]/', $pass)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }

    if (!preg_match('/[a-z]/', $pass)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }

    if (!preg_match('/[0-9]/', $pass)) {
        $errors[] = "Password must contain at least one number";
    }

    if (!preg_match('/[\W]/', $pass)) {
        $errors[] = "Password must contain at least one special character";
    }

    if (empty($user) || empty($email) || empty($pass)) {
        $errors[] = "All fields are required";
    }

    if (empty($errors)) {
        try {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$user, $email]);

            if ($stmt->rowCount() > 0) {
                $errors[] = "Username or email already exists!";
            } else {

                $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
                
                // 🔥 Generate verification token
                $verification_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $sql = "INSERT INTO users (username, email, password, is_verified, verification_token, token_expiry, total_xp, score) 
                        VALUES (?, ?, ?, 0, ?, ?, 0, 0)";
                $stmt = $pdo->prepare($sql);

                $result = $stmt->execute([$user, $email, $hashed_pass, $verification_token, $token_expiry]);

                if ($result) {
                      sendVerificationEmail($email, $verification_token);
                    
                    $registration_success = true;
                    $registered_email = $email;
                    
                    // DO NOT auto-login user - they must verify email first
                    // DO NOT set session variables
                    
                } else {
                    $errors[] = "Insert failed!";
                }
            }

        } catch (PDOException $e) {
            $errors[] = "Something went wrong, try again later";
            // Log error: error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Join DarkHunter 👾</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/register.css">
  <link rel="stylesheet" type="text/css" href="css/verification-message.css">
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
        <i class="fas fa-user-astronaut"></i>
      </div>
      <h1 class="logo-title">Join DarkHunter</h1>
      <p class="logo-subtitle">Become an elite cybersecurity hunter</p>
    </div>

    <!-- 🔥 SUCCESS MESSAGE - Check Email to Activate -->
    <?php if ($registration_success): ?>
    <div class="verification-inline success" style="margin-bottom: 25px;">
      <div class="verification-inline-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="verification-inline-content">
        <h3 class="verification-inline-title">Registration Successful!</h3>
        <p class="verification-inline-text">
          Check your email <strong><?php echo htmlspecialchars($registered_email); ?></strong> to activate your account
          before logging in.
        </p>
      </div>
    </div>

    <div style="text-align: center; margin-top: 20px;">
      <a href="login.php" class="auth-link"
        style="display: inline-flex; padding: 12px 24px; background: linear-gradient(135deg, #00d084, #00a868); color: #fff; border-radius: 12px; text-decoration: none; font-weight: 600;">
        <i class="fas fa-sign-in-alt"></i> Go to Login
      </a>
    </div>

    <!-- Hide form after successful registration -->
    <style>
    .social-login,
    .divider,
    #registerForm,
    .auth-footer {
      display: none !important;
    }
    </style>

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
      <span>OR REGISTER WITH EMAIL</span>
    </div>

    <!-- Form -->
    <form action="register.php" method="POST" id="registerForm">
      <div class="form-group">
        <i class="fas fa-user input-icon"></i>
        <input type="text" name="username" class="auth-input" placeholder="Username" required
          pattern="[a-zA-Z0-9_]{3,20}" title="3-20 characters, alphanumeric only"
          value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      </div>

      <div class="form-group">
        <i class="fas fa-envelope input-icon"></i>
        <input type="email" name="email" class="auth-input" placeholder="Email Address" required
          value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>

      <div class="form-group">
        <i class="fas fa-lock input-icon"></i>
        <input type="password" name="password" class="auth-input" placeholder="Password" required id="password"
          minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[\W]).{8,}"
          title="Must contain uppercase, lowercase, number, and special character">

        <div class="password-strength" id="strengthBar">
          <div class="strength-bar"></div>
        </div>
      </div>

      <div class="checkbox-group">
        <input type="checkbox" id="terms" required>
        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
      </div>

      <button type="submit" name="register_btn" class="auth-btn">
        <i class="fas fa-rocket"></i> Create Account
      </button>
    </form>

    <!-- Footer -->
    <div class="auth-footer">
      <p>Already have an account?</p>
      <a href="login.php" class="auth-link">
        <i class="fas fa-sign-in-alt"></i> Login to your account
      </a>
    </div>

    <?php endif; ?>
  </div>

  <script>
  // Password strength indicator
  const passwordInput = document.getElementById('password');
  const strengthBar = document.getElementById('strengthBar');
  const strengthBarInner = strengthBar.querySelector('.strength-bar');

  passwordInput.addEventListener('input', function() {
    const val = this.value;
    if (val.length > 0) {
      strengthBar.classList.add('show');

      let strength = 0;
      if (val.length >= 8) strength++;
      if (val.match(/[a-z]/) && val.match(/[A-Z]/)) strength++;
      if (val.match(/[0-9]/)) strength++;
      if (val.match(/[^a-zA-Z0-9]/)) strength++;

      strengthBarInner.className = 'strength-bar';
      if (strength <= 1) {
        strengthBarInner.classList.add('strength-weak');
      } else if (strength === 2) {
        strengthBarInner.classList.add('strength-medium');
      } else {
        strengthBarInner.classList.add('strength-strong');
      }
    } else {
      strengthBar.classList.remove('show');
    }
  });

  // Form animation
  document.getElementById('registerForm').addEventListener('submit', function() {
    const btn = this.querySelector('.auth-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    btn.disabled = true;
  });
  </script>
</body>

</html>