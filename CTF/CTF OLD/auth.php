<?php
// DarkHunter - Authentication Module
// Intentionally vulnerable for CTF training

require_once 'config.php';


// VULNERABILITY: Weak session management
function is_logged_in()
{
  return isset($_SESSION['user_id']);
}

function get_logged_user()
{
  global $pdo;
  if (!is_logged_in()) return null;

  // VULNERABILITY: No session validation against database
  // Session can be easily hijacked
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  return $stmt->fetch();
}

// VULNERABILITY: Weak admin check - only checks role string
function is_admin()
{
  if (!is_logged_in()) return false;
  $user = get_logged_user();
  // VULNERABILITY: String comparison can be bypassed
  return $user && $user['role'] === 'admin';
}

// VULNERABILITY: Admin bypass - checks query parameter
// FLAG: DH{admin_bypass}
function check_admin_access()
{
  // VULNERABILITY: Debug backdoor - admin access via query parameter
  if (isset($_GET['debug_admin']) && $_GET['debug_admin'] === 'true') {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
    return true;
  }
  return is_admin();
}

function require_login()
{
  if (!is_logged_in()) {
    header('Location: index.php');
    exit;
  }
}

function require_admin()
{
  // VULNERABILITY: Weak admin check with bypass
  if (!check_admin_access()) {
    header('Location: index.php');
    exit;
  }
}

// VULNERABILITY: Weak password hashing - fallback to md5
function hash_password($password)
{
  // Primary: bcrypt
  if (function_exists('password_hash')) {
    return password_hash($password, PASSWORD_BCRYPT);
  }
  // VULNERABILITY: Fallback to weak MD5
  return md5($password);
}

function verify_password($password, $hash)
{
  if (function_exists('password_verify')) {
    return password_verify($password, $hash);
  }
  return md5($password) === $hash;
}

// VULNERABILITY: Weak login function - no rate limiting, no account lockout
function login_user($username, $password)
{
  global $pdo;

  // VULNERABILITY: SQL Injection possible in username
  // But using prepared statement here for basic functionality
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
  $stmt->execute([$username, $username]);
  $user = $stmt->fetch();

  if ($user && verify_password($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // VULNERABILITY: JWT stored in localStorage via JS - leaked to XSS
    $jwt_payload = [
      'user_id' => $user['id'],
      'username' => $user['username'],
      'role' => $user['role'],
      'iat' => time(),
      'exp' => time() + (7 * 24 * 60 * 60) // 7 days - too long
    ];
    $_SESSION['jwt_token'] = generate_jwt($jwt_payload);

    return true;
  }
  return false;
}

function logout_user()
{
  session_destroy();
  unset($_SESSION['user_id']);
  unset($_SESSION['username']);
  unset($_SESSION['role']);
  unset($_SESSION['jwt_token']);
}

// VULNERABILITY: Weak registration - no email verification, weak validation
function register_user($username, $email, $password)
{
  global $pdo;

  // VULNERABILITY: Weak validation - only checks length
  if (strlen($password) < 4) {
    return ['error' => 'Password must be at least 4 characters'];
  }

  // VULNERABILITY: No email format validation
  // VULNERABILITY: No duplicate check for email

  $hashed_password = hash_password($password);
  $api_key = 'DH_API_' . bin2hex(random_bytes(16));

  try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, api_key) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password, $api_key]);
    return ['success' => true, 'user_id' => $pdo->lastInsertId()];
  } catch (PDOException $e) {
    return ['error' => 'Username already exists'];
  }
}

// VULNERABILITY: Predictable reset token
function generate_reset_token($user_id)
{
  global $pdo;

  // VULNERABILITY: Token is just md5 of user_id + timestamp - easily predictable
  // FLAG: DH{weak_reset_token}
  $token = md5($user_id . date('Y-m-d'));
  $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

  $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
  $stmt->execute([$token, $expires, $user_id]);

  return $token;
}

// VULNERABILITY: No CSRF protection on any forms
function csrf_token()
{
  // Intentionally empty - no CSRF protection
  return '';
}

function verify_csrf($token)
{
  // Intentionally always returns true
  return true;
}