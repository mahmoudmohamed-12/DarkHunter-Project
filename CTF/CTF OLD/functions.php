<?php
// DarkHunter - Helper Functions (CTF Module)

require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php';

/**
 * VULNERABILITY: No output encoding - XSS possible
 */
function display_message($message)
{
  echo $message;
}

/**
 * VULNERABILITY: LFI possible
 */
function load_template($template_name)
{
  include __DIR__ . '/../templates/' . $template_name . '.php';
}

/**
 * VULNERABILITY: Command Injection
 */
function ping_host($host)
{
  return shell_exec("ping -c 1 " . $host);
}

/**
 * VULNERABILITY: SSRF
 */
function fetch_url($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);

  $response = curl_exec($ch);
  curl_close($ch);

  return $response;
}

/**
 * VULNERABILITY: Insecure deserialization
 */
function process_import($data)
{
  return unserialize($data);
}

/**
 * System info disclosure
 */
function get_system_info()
{
  return [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    'database_host' => 'localhost',
    'internal_api' => 'http://localhost/internal/',
    'admin_panel' => 'http://localhost/admin.php'
  ];
}

/**
 * File upload (weak)
 */
function upload_file($file, $directory = 'uploads/')
{
  $target_dir = __DIR__ . '/../' . $directory;

  // Create folder if not exists
  if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
  }

  // Prevent errors if no file uploaded
  if (
    !isset($file['name']) ||
    empty($file['name']) ||
    !isset($file['tmp_name'])
  ) {
    return ['error' => 'No file selected'];
  }

  $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'php', 'txt'];
  $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  if (!in_array($file_extension, $allowed_extensions)) {
    return ['error' => 'Invalid file type'];
  }

  $new_filename = time() . '_' . basename($file['name']);
  $target_file = $target_dir . $new_filename;

  if (move_uploaded_file($file['tmp_name'], $target_file)) {
    return [
      'success' => true,
      'filename' => $new_filename,
      'path' => $target_file
    ];
  }

  return ['error' => 'Upload failed'];
}

/**
 * Open redirect
 */
function redirect($url)
{
  header("Location: $url");
  exit;
}

/**
 * Weak encryption (XOR)
 */
function encrypt_data($data, $key = 'default_key')
{
  $result = '';

  for ($i = 0; $i < strlen($data); $i++) {
    $result .= $data[$i] ^ $key[$i % strlen($key)];
  }

  return base64_encode($result);
}

/**
 * Weak random string
 */
function generate_random_string($length = 10)
{
  $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $result = '';

  for ($i = 0; $i < $length; $i++) {
    $result .= $chars[rand(0, strlen($chars) - 1)];
  }

  return $result;
}

/**
 * SQL Injection (CTF flag)
 */
function search_products_raw($query)
{
  global $pdo;

  $sql = "SELECT * FROM products 
            WHERE name LIKE '%" . $query . "%' 
            OR description LIKE '%" . $query . "%'";

  return $pdo->query($sql)->fetchAll();
}

/**
 * Mass assignment
 */
function update_user_profile($user_id, $data)
{
  global $pdo;

  // Prevent crash if empty data
  if (empty($data) || !is_array($data)) {
    return ['error' => 'No data provided'];
  }

  $fields = [];
  $values = [];

  foreach ($data as $key => $value) {
    $fields[] = "$key = ?";
    $values[] = $value;
  }

  // Prevent invalid SQL
  if (empty($fields)) {
    return ['error' => 'No fields to update'];
  }

  $values[] = $user_id;

  $sql = "UPDATE users SET " . implode(',', $fields) . " WHERE id = ?";
  $stmt = $pdo->prepare($sql);

  if ($stmt->execute($values)) {
    return ['success' => true];
  }

  return ['error' => 'Update failed'];
}

/**
 * IDOR
 */
function get_order($order_id)
{
  global $pdo;

  $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
  $stmt->execute([$order_id]);

  return $stmt->fetch();
}

/**
 * Weak password reset
 */
function reset_password($token, $new_password)
{
  global $pdo;

  $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
  $stmt->execute([$token]);

  $user = $stmt->fetch();

  if ($user) {
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
      "UPDATE users SET password = ?, reset_token = NULL WHERE id = ?"
    );

    $stmt->execute([$hashed, $user['id']]);
    return true;
  }

  return false;
}

/**
 * Add review (XSS vulnerability)
 */
function add_review($product_id, $user_id, $username, $rating, $comment)
{
  global $pdo;

  $stmt = $pdo->prepare(
    "INSERT INTO reviews 
        (product_id, user_id, username, rating, comment) 
        VALUES (?, ?, ?, ?, ?)"
  );

  $stmt->execute([$product_id, $user_id, $username, $rating, $comment]);

  return [
    'success' => true,
    'review_id' => $pdo->lastInsertId()
  ];
}

/**
 * Get user by ID (IDOR)
 */
function get_user_by_id($user_id)
{
  global $pdo;

  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);

  return $stmt->fetch();
}