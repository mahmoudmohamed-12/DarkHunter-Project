<?php

$db_host = 'localhost';
$db_name = 'darkhunter_ctf';
$db_user = 'root';
$db_pass = '';

// VULNERABILITY: Debug mode enabled in production
$debug_mode = true;

// JWT Secret - intentionally weak for CTF
// VULNERABILITY: Weak JWT secret
$jwt_secret = 'darkhunter_secret_key_123';

// Admin credentials (for debug purposes)
// VULNERABILITY: Hardcoded admin credentials
$admin_user = 'admin';
$admin_pass = 'admin123';

// API Configuration
$api_base_url = 'http://localhost:8080/api/';
$internal_api_key = 'DH_INTERNAL_API_9f8e7d6c5b4a3210';

// CORS settings - intentionally permissive
// VULNERABILITY: CORS misconfiguration
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key");
header("Access-Control-Allow-Credentials: true");

// VULNERABILITY: Missing security headers
// No X-Frame-Options (Clickjacking)
// No X-Content-Type-Options
// No CSP

// Connect to database
try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  if ($debug_mode) {
    die("Database connection failed: " . $e->getMessage());
  } else {
    die("System error. Please try again later.");
  }
}

// Helper function for JWT generation (intentionally weak)
function generate_jwt($payload)
{
  global $jwt_secret;
  $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
  $payload = json_encode($payload);
  $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
  $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
  $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $jwt_secret, true);
  $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
  return $base64Header . "." . $base64Payload . "." . $base64Signature;
}

// Helper function to verify JWT
function verify_jwt($jwt)
{
  global $jwt_secret;
  $parts = explode('.', $jwt);
  if (count($parts) !== 3) return false;
  $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $jwt_secret, true);
  $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
  return hash_equals($base64Signature, $parts[2]);
}

// VULNERABILITY: Weak random token generation
function generate_token()
{
  return md5(time() . rand(1, 1000));
}

// VULNERABILITY: Information disclosure in error handling
function log_error($message)
{
  global $debug_mode;
  $log_file = __DIR__ . '/../logs/error.log';
  $timestamp = date('Y-m-d H:i:s');
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $log_entry = "[$timestamp] [$ip] $message\\n";
  file_put_contents($log_file, $log_entry, FILE_APPEND);
  if ($debug_mode) {
    echo "<!-- DEBUG: $message -->";
  }
}

// VULNERABILITY: Insecure deserialization helper
function unserialize_data($data)
{
  return unserialize($data);
}

// VULNERABILITY: Weak input sanitization
function sanitize_input($input)
{
  // Intentionally weak - only strips tags, doesn't prevent SQLi/XSS
  return strip_tags($input);
}

// VULNERABILITY: Predictable file naming
function generate_filename($original_name)
{
  return time() . '_' . $original_name;
}