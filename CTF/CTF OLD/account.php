<?php

/**
 * DarkHunter CTF - Account System
 * 
 * VULNERABILITIES PRESERVED:
 * - IDOR (user_id parameter)
 * - Mass Assignment (no field whitelist)
 * - No CSRF Protection
 * - Weak Password Policy (min 4 chars)
 * - Predictable Reset Token (md5 based)
 * - File Upload Vulnerability
 * - HTML Injection in Bio
 * - Debug Info Exposure
 * - API Key Exposure
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/CTF/ctf_session.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db_ctf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/functions.php');


// FIX: Ensure $pdo exists before use
if (!isset($pdo) || !($pdo instanceof PDO)) {
  error_log("[DarkHunter] FATAL: PDO connection not available");
  die("Database connection error. Please check configuration.");
}

// ===================== SESSION & AUTH DEBUG =====================
error_log("[DarkHunter] Account page loaded. Session ID: " . session_id());
error_log("[DarkHunter] User logged in: " . (is_logged_in() ? 'YES (ID: ' . $_SESSION['user_id'] . ')' : 'NO'));

// ===================== IDOR VULNERABILITY =====================
// VULNERABILITY: IDOR - can view other users' profiles via user_id parameter
// FLAG: DH{idor_order_access}
$view_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (is_logged_in() ? intval($_SESSION['user_id']) : 0);

// Get user data with error handling
try {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$view_user_id]);
  $user = $stmt->fetch();

  if (!$user) {
    error_log("[DarkHunter] User not found for ID: " . $view_user_id);
    $user = [
      'id' => 0,
      'username' => 'Guest',
      'email' => '',
      'bio' => '',
      'avatar' => 'default.png',
      'api_key' => '',
      'points' => 0,
      'role' => 'guest',
      'created_at' => date('Y-m-d H:i:s')
    ];
  } else {
    error_log("[DarkHunter] Loaded user: " . $user['username'] . " (ID: " . $user['id'] . ")");
  }
} catch (PDOException $e) {
  error_log("[DarkHunter] DB Error loading user: " . $e->getMessage());
  $user = ['id' => 0, 'username' => 'Guest', 'email' => '', 'bio' => '', 'avatar' => 'default.png', 'api_key' => '', 'points' => 0, 'role' => 'guest', 'created_at' => date('Y-m-d H:i:s')];
}

// ===================== LOGIN HANDLING =====================
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  error_log("[DarkHunter] Login attempt for: " . $username);

  if (empty($username) || empty($password)) {
    $login_error = 'Please enter both username and password.';
  } else {
    try {
      if (login_user($username, $password)) {
        error_log("[DarkHunter] Login successful for: " . $username);
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        header('Location: account.php');
        exit;
      } else {
        error_log("[DarkHunter] Login failed for: " . $username);
        $login_error = 'Invalid username or password.';
      }
    } catch (Exception $e) {
      error_log("[DarkHunter] Login error: " . $e->getMessage());
      $login_error = 'Login system error. Please try again.';
    }
  }
}

// ===================== REGISTRATION HANDLING =====================
$register_error = '';
$register_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  error_log("[DarkHunter] Registration attempt: username=" . $username . ", email=" . $email);

  // Validation
  if (empty($username) || empty($email) || empty($password)) {
    $register_error = 'All fields are required.';
  } elseif (strlen($username) < 3 || strlen($username) > 30) {
    $register_error = 'Username must be between 3 and 30 characters.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $register_error = 'Please enter a valid email address.';
  } elseif (strlen($password) < 4) {
    // VULNERABILITY: Weak password policy - minimum 4 chars (intentional for CTF)
    $register_error = 'Password must be at least 4 characters.';
  } else {
    try {
      $result = register_user($username, $email, $password);
      if (isset($result['success'])) {
        error_log("[DarkHunter] Registration successful: " . $username);
        $register_success = 'Account created successfully! Please log in.';

        // Auto-login after registration
        if (login_user($username, $password)) {
          $_SESSION['login_time'] = date('Y-m-d H:i:s');
          header('Location: account.php');
          exit;
        }
      } else {
        error_log("[DarkHunter] Registration failed: " . ($result['error'] ?? 'Unknown error'));
        $register_error = $result['error'] ?? 'Registration failed. Please try again.';
      }
    } catch (Exception $e) {
      error_log("[DarkHunter] Registration exception: " . $e->getMessage());
      $register_error = 'Registration system error. Please try again.';
    }
  }
}

// ===================== PROFILE UPDATE (MASS ASSIGNMENT) =====================
// VULNERABILITY: Mass assignment - any field can be updated
// VULNERABILITY: No CSRF protection
// FLAG: DH{mass_assignment}
// FLAG: DH{csrf_profile_change}
$profile_message = '';
$upload_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $update_data = [];

  // VULNERABILITY: No field whitelist - attackers can update any field
  foreach ($_POST as $key => $value) {
    if ($key !== 'update_profile' && $key !== 'tab' && $key !== 'avatar') {
      $update_data[$key] = $value;
    }
  }

  // VULNERABILITY: Password can be updated directly without old password verification
  if (isset($update_data['new_password']) && !empty($update_data['new_password'])) {
    if (isset($update_data['confirm_password']) && $update_data['new_password'] === $update_data['confirm_password']) {
      $update_data['password'] = hash_password($update_data['new_password']);
      error_log("[DarkHunter] Password updated for user ID: " . $view_user_id);
    }
    unset($update_data['new_password'], $update_data['confirm_password']);
  }

  try {
    update_user_profile($view_user_id, $update_data);
    error_log("[DarkHunter] Profile updated for user ID: " . $view_user_id);

    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$view_user_id]);
    $user = $stmt->fetch();

    $profile_message = '<div class="toast-notification toast-success"><i class="fas fa-check-circle"></i> Profile updated successfully!</div>';
  } catch (Exception $e) {
    error_log("[DarkHunter] Profile update error: " . $e->getMessage());
    $profile_message = '<div class="toast-notification toast-error"><i class="fas fa-times-circle"></i> Update failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
  }
}

// ===================== AVATAR UPLOAD =====================
// VULNERABILITY: Insecure file upload
// FLAG: DH{file_upload_rce}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
  try {
    // FIX: Ensure uploads directory exists
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/uploads/';
    if (!is_dir($upload_dir)) {
      @mkdir($upload_dir, 0755, true);
    }

    $result = upload_file($_FILES['avatar'], 'uploads/');
    if (isset($result['success'])) {
      $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
      $stmt->execute([$result['filename'], $view_user_id]);
      $upload_message = '<div class="toast-notification toast-success"><i class="fas fa-check-circle"></i> Avatar uploaded successfully!</div>';
      error_log("[DarkHunter] Avatar uploaded for user ID: " . $view_user_id);

      // Refresh user data
      $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
      $stmt->execute([$view_user_id]);
      $user = $stmt->fetch();
    } else {
      $upload_message = '<div class="toast-notification toast-error"><i class="fas fa-times-circle"></i> Upload failed: ' . htmlspecialchars($result['error'] ?? 'Unknown error') . '</div>';
    }
  } catch (Exception $e) {
    error_log("[DarkHunter] Avatar upload error: " . $e->getMessage());
    $upload_message = '<div class="toast-notification toast-error"><i class="fas fa-times-circle"></i> Upload error: ' . htmlspecialchars($e->getMessage()) . '</div>';
  }
}

// ===================== PASSWORD RESET =====================
// VULNERABILITY: Predictable reset token
$reset_message = '';
$reset_user_data = null;  // FIX: Initialize to prevent undefined variable
$token = '';  // FIX: Initialize to prevent undefined variable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
  $reset_username = trim($_POST['reset_username'] ?? '');

  if (!empty($reset_username)) {
    try {
      $stmt = $pdo->prepare("SELECT id, email FROM users WHERE username = ?");
      $stmt->execute([$reset_username]);
      $reset_user_data = $stmt->fetch();

      if ($reset_user_data) {
        // VULNERABILITY: Token is predictable (md5 of user_id + date)
        $token = generate_reset_token($reset_user_data['id']);
        $reset_message = '<div class="toast-notification toast-info"><i class="fas fa-info-circle"></i> Reset token generated. Check your email or debug console.</div>';
        error_log("[DarkHunter] Reset token for user " . $reset_username . ": " . $token);
      } else {
        // Don't reveal if user exists or not
        $reset_message = '<div class="toast-notification toast-info"><i class="fas fa-info-circle"></i> If the user exists, a reset token has been generated.</div>';
      }
    } catch (Exception $e) {
      error_log("[DarkHunter] Reset error: " . $e->getMessage());
    }
  }
}

// ===================== CART INTEGRATION =====================
// Get real cart items from session with product details
$cart_items = [];
$cart_total = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
  $cart_counts = array_count_values($_SESSION['cart']);
  foreach ($cart_counts as $product_id => $qty) {
    try {
      $stmt = $pdo->prepare("SELECT id, name, price, category, description FROM products WHERE id = ?");
      $stmt->execute([$product_id]);
      $product = $stmt->fetch();
      if ($product) {
        $item_total = ($product['price'] ?? 0) * $qty;
        $cart_total += $item_total;
        $cart_items[] = [
          'product' => $product,
          'qty' => $qty,
          'total' => $item_total
        ];
      }
    } catch (Exception $e) {
      error_log("[DarkHunter] Cart item load error: " . $e->getMessage());
    }
  }
}
$cart_count = count($cart_items);

// ===================== ORDERS INTEGRATION =====================
// Get real orders for this user
$orders = [];
try {
  // FIX: Check if orders table exists before querying
  $table_exists = false;
  try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    $table_exists = ($stmt->rowCount() > 0);
  } catch (Exception $e) {
    $table_exists = false;
  }

  if ($table_exists) {
    // Check if orders table has the right structure
    $stmt = $pdo->query("SHOW COLUMNS FROM orders");
    $order_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("[DarkHunter] Orders table columns: " . implode(', ', $order_columns));

    // Flexible query based on available columns
    if (in_array('product_id', $order_columns) && in_array('user_id', $order_columns)) {
      $stmt = $pdo->prepare("
            SELECT o.*, p.name as product_name, p.description as product_description, p.price as product_price 
            FROM orders o 
            LEFT JOIN products p ON o.product_id = p.id 
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC
        ");
      $stmt->execute([$view_user_id]);
      $orders = $stmt->fetchAll();
      error_log("[DarkHunter] Loaded " . count($orders) . " orders for user ID: " . $view_user_id);
    } else {
      // Fallback if table structure is different
      $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
      $stmt->execute([$view_user_id]);
      $orders = $stmt->fetchAll();
    }
  } else {
    error_log("[DarkHunter] Orders table does not exist");
  }
} catch (Exception $e) {
  error_log("[DarkHunter] Orders load error: " . $e->getMessage());
  $orders = [];
}

// ===================== SUBMISSIONS =====================
$submissions = [];
try {
  $stmt = $pdo->prepare("
        SELECT s.*, f.flag_code, f.points as flag_points, f.vulnerability_type, f.name as flag_name 
        FROM submissions s 
        JOIN flags f ON s.flag_id = f.id 
        WHERE s.user_id = ? 
        ORDER BY s.submitted_at DESC
    ");
  $stmt->execute([$view_user_id]);
  $submissions = $stmt->fetchAll();

  if (!is_array($submissions)) {
    $submissions = [];
  }
} catch (Exception $e) {
  error_log("[DarkHunter] Submissions load error: " . $e->getMessage());
  $submissions = [];
}


// ===================== NOTIFICATIONS =====================
$notifications = [];
try {

  $table_exists = false;
  try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $table_exists = ($stmt->rowCount() > 0);
  } catch (Exception $e) {
    $table_exists = false;
  }

  if ($table_exists) {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$view_user_id]);
    $notifications = $stmt->fetchAll();

    if (!is_array($notifications)) {
      $notifications = [];
    }
  }
} catch (Exception $e) {
  error_log("[DarkHunter] Notifications load error: " . $e->getMessage());
  $notifications = [];
}


// ===================== POINTS CALCULATION =====================
$total_points = 0;

if (is_array($submissions)) {
  foreach ($submissions as $sub) {
    if (!is_array($sub)) continue;
    $total_points += intval($sub['flag_points'] ?? 0);
  }
}

// safe user points access (FIX for bool warning)
$total_points += (is_array($user) ? intval($user['points'] ?? 0) : 0);


// ===================== SAFETY NET (IMPORTANT FIX) =====================
// Prevent: Trying to access array offset on bool
if (!is_array($user)) {
  $user = [
    'id' => 0,
    'username' => 'Guest',
    'email' => '',
    'bio' => '',
    'avatar' => 'default.png',
    'api_key' => '',
    'points' => 0,
    'role' => 'guest',
    'created_at' => date('Y-m-d H:i:s')
  ];
}


// ===================== API KEY =====================
$api_key = $user['api_key'] ?? '';

if (empty($api_key) && ($user['id'] ?? 0) > 0) {
  $api_key = 'dh_' . bin2hex(random_bytes(16));

  try {
    $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?");
    $stmt->execute([$api_key, $user['id']]);

    $user['api_key'] = $api_key;
  } catch (Exception $e) {
    error_log("[DarkHunter] API key generation error: " . $e->getMessage());
  }
}
// ===================== TAB HANDLING =====================
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// ===================== LOGOUT =====================
if (isset($_GET['logout'])) {
  logout_user();
  header('Location: account.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account | DarkHunter CTF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/CTF/css/account.css">
</head>

<body>
  <div class="bg-grid"></div>
  <div class="bg-glow glow-1"></div>
  <div class="bg-glow glow-2"></div>

  <!-- Toast Container -->
  <div class="toast-container" id="toastContainer">
    <?php
    if (!empty($profile_message)) echo $profile_message;
    if (!empty($upload_message)) echo $upload_message;
    if (!empty($reset_message)) echo $reset_message;
    ?>
  </div>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-bug"></i> DarkHunter</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
          <li class="nav-item"><a class="nav-link active" href="account.php">Account</a></li>
          <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="submit.php">Submit Flag</a></li>
          <?php if (is_logged_in()): ?>
          <li class="nav-item">
            <a class="nav-link" href="cart.php">
              <i class="fas fa-shopping-cart"></i> Cart
              <?php if ($cart_count > 0): ?>
              <span class="cart-badge"><?php echo $cart_count; ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="account-hero">
    <div class="container">
      <h1><i class="fas fa-user-circle"></i> Account Dashboard</h1>
      <p>Manage your profile, track orders, monitor submissions, and control your security settings.</p>
    </div>
  </section>

  <div class="container" style="padding: 2rem 0; position: relative; z-index: 1;">

    <?php if (!is_logged_in() && $view_user_id == 0): ?>
    <!-- ===================== LOGIN / REGISTER ===================== -->
    <div class="row justify-content-center">
      <div class="col-md-5">
        <!-- Login Card -->
        <div class="card-dark">
          <h3><i class="fas fa-sign-in-alt"></i> Login</h3>

          <?php if ($login_error): ?>
          <div class="toast-notification toast-error" style="margin-bottom: 1rem;">
            <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($login_error); ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="account.php">
            <div class="form-group">
              <label class="form-label">Username or Email</label>
              <input type="text" name="username" class="form-control-dark" placeholder="Enter username or email"
                required>
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control-dark" placeholder="Enter password" required>
            </div>
            <button type="submit" name="login" class="btn-darkhunter w-100 justify-content-center">
              <i class="fas fa-sign-in-alt"></i> Login
            </button>
          </form>

          <div class="mt-3 text-center">
            <a href="?tab=register" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.9rem;">
              <i class="fas fa-user-plus"></i> Don't have an account? Register
            </a>
          </div>
          <div class="mt-2 text-center">
            <a href="?tab=reset" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">
              <i class="fas fa-key"></i> Forgot password?
            </a>
          </div>
        </div>
      </div>

      <?php if (isset($_GET['tab']) && $_GET['tab'] === 'register'): ?>
      <div class="col-md-5">
        <!-- Registration Card -->
        <div class="card-dark">
          <h3><i class="fas fa-user-plus"></i> Create Account</h3>

          <?php if ($register_error): ?>
          <div class="toast-notification toast-error" style="margin-bottom: 1rem;">
            <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($register_error); ?>
          </div>
          <?php endif; ?>

          <?php if ($register_success): ?>
          <div class="toast-notification toast-success" style="margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($register_success); ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="account.php?tab=register">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control-dark" placeholder="Choose a username (3-30 chars)"
                required minlength="3" maxlength="30">
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control-dark" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control-dark" placeholder="Min 4 characters" required
                minlength="4">
              <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.35rem;">
                <i class="fas fa-exclamation-triangle"></i> Weak password policy (min 4 chars) - intentional for CTF
              </small>
            </div>
            <button type="submit" name="register" class="btn-darkhunter w-100 justify-content-center">
              <i class="fas fa-user-plus"></i> Create Account
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <?php if (isset($_GET['tab']) && $_GET['tab'] === 'reset'): ?>
      <div class="col-md-5">
        <!-- Password Reset Card -->
        <div class="card-dark">
          <h3><i class="fas fa-key"></i> Password Reset</h3>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Enter your username to receive a password reset token.
          </p>

          <form method="POST" action="account.php?tab=reset">
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="reset_username" class="form-control-dark" placeholder="Enter your username"
                required>
            </div>
            <button type="submit" name="request_reset" class="btn-darkhunter w-100 justify-content-center">
              <i class="fas fa-paper-plane"></i> Request Reset Token
            </button>
          </form>

          <?php
              if (isset($_POST['request_reset']) && isset($reset_user_data) && $reset_user_data) {
                echo '<div class="card-dark" style="margin-top: 1rem; background: var(--bg-secondary);">';
                echo '<h5 style="color: var(--accent-cyan); font-family: JetBrains Mono; margin-bottom: 0.75rem;"><i class="fas fa-code"></i> Reset Token</h5>';
                echo '<div class="api-key-box" style="margin-bottom: 0.75rem;">';
                echo '<i class="fas fa-key"></i> ' . htmlspecialchars($token);
                echo '</div>';
                echo '<small style="color: var(--text-muted);">';
                echo '<i class="fas fa-bug"></i> VULNERABILITY: Token is predictable (md5 of user_id + date)<br>';
                echo 'Try to guess tokens for other users!';
                echo '</small>';
                echo '</div>';
              }
              ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- ===================== LOGGED IN DASHBOARD ===================== -->
    <div class="row g-4">
      <!-- Profile Sidebar -->
      <div class="col-lg-4">
        <div class="profile-card">
          <div class="avatar-container">
            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.png'): ?>
            <img src="uploads/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
            <?php else: ?>
            <i class="fas fa-user"></i>
            <?php endif; ?>
          </div>
          <div class="profile-username"><?php echo htmlspecialchars($user['username'] ?? 'Guest'); ?></div>
          <span class="profile-role role-<?php echo $user['role'] ?? 'guest'; ?>">
            <?php echo strtoupper($user['role'] ?? 'GUEST'); ?>
          </span>
          <div class="profile-points"><?php echo number_format($total_points); ?></div>
          <div class="profile-points-label">Points Earned</div>

          <!-- Stats -->
          <div class="stats-grid">
            <div class="stat-box">
              <div class="number"><?php echo count($orders); ?></div>
              <div class="label">Orders</div>
            </div>
            <div class="stat-box">
              <div class="number"><?php echo count($submissions); ?></div>
              <div class="label">Flags</div>
            </div>
            <div class="stat-box">
              <div class="number"><?php echo count($notifications); ?></div>
              <div class="label">Alerts</div>
            </div>
            <div class="stat-box">
              <div class="number"><?php echo count($cart_items); ?></div>
              <div class="label">Cart</div>
            </div>
          </div>

          <!-- Meta Info -->
          <div class="profile-meta">
            <div class="profile-meta-row">
              <span class="profile-meta-label"><i class="fas fa-id-card"></i> User ID</span>
              <span class="profile-meta-value"><?php echo $user['id'] ?? 0; ?></span>
            </div>
            <div class="profile-meta-row">
              <span class="profile-meta-label"><i class="fas fa-envelope"></i> Email</span>
              <span class="profile-meta-value"
                style="font-size: 0.75rem;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
            </div>
            <div class="profile-meta-row">
              <span class="profile-meta-label"><i class="fas fa-key"></i> API Key</span>
              <span class="profile-meta-value"
                style="font-size: 0.7rem;"><?php echo substr($api_key, 0, 20); ?>...</span>
            </div>
            <div class="profile-meta-row">
              <span class="profile-meta-label"><i class="fas fa-calendar"></i> Joined</span>
              <span class="profile-meta-value"
                style="font-size: 0.8rem;"><?php echo isset($user['created_at']) && !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></span>
            </div>
          </div>

          <?php if (is_logged_in() && $_SESSION['user_id'] == $view_user_id): ?>
          <a href="?logout=1" class="btn-danger-custom w-100 d-block text-center mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
          <?php endif; ?>
        </div>

        <!-- Cart Summary (if items exist) -->
        <?php if (!empty($cart_items)): ?>
        <div class="card-dark" style="margin-top: 1.5rem;">
          <h3 style="font-size: 1rem;"><i class="fas fa-shopping-cart"></i> Cart (<?php echo count($cart_items); ?>)
          </h3>
          <?php foreach (array_slice($cart_items, 0, 3) as $item): ?>
          <div class="cart-mini-item">
            <div class="cart-mini-img"><i class="fas fa-box"></i></div>
            <div class="cart-mini-info">
              <div class="cart-mini-name"><?php echo htmlspecialchars($item['product']['name'] ?? 'Unknown'); ?></div>
              <div class="cart-mini-meta">Qty: <?php echo $item['qty'] ?? 0; ?></div>
            </div>
            <div class="cart-mini-price">$<?php echo number_format($item['total'], 2); ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (count($cart_items) > 3): ?>
          <div style="text-align: center; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
            <a href="cart.php" style="color: var(--accent-cyan); text-decoration: none; font-size: 0.85rem;">
              +<?php echo count($cart_items) - 3; ?> more items
            </a>
          </div>
          <?php endif; ?>
          <div style="border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.75rem;">
            <div style="display: flex; justify-content: space-between; font-weight: 600;">
              <span>Total</span>
              <span
                style="color: var(--accent-green); font-family: JetBrains Mono;">$<?php echo number_format($cart_total, 2); ?></span>
            </div>
            <a href="cart.php" class="btn-darkhunter w-100 justify-content-center mt-2" style="padding: 0.5rem;">
              <i class="fas fa-credit-card"></i> Checkout
            </a>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Main Content -->
      <div class="col-lg-8">
        <!-- Tabs -->
        <div class="account-tabs">
          <a href="?tab=profile<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            class="account-tab <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> Profile
          </a>
          <a href="?tab=orders<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            class="account-tab <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i> Orders
          </a>
          <a href="?tab=security<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            class="account-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">
            <i class="fas fa-shield-alt"></i> Security
          </a>
          <a href="?tab=api<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            class="account-tab <?php echo $active_tab === 'api' ? 'active' : ''; ?>">
            <i class="fas fa-key"></i> API
          </a>
          <a href="?tab=submissions<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            class="account-tab <?php echo $active_tab === 'submissions' ? 'active' : ''; ?>">
            <i class="fas fa-flag"></i> Flags
          </a>
          <a href="?tab=notifications<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            class="account-tab <?php echo $active_tab === 'notifications' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i> Alerts
          </a>
        </div>

        <!-- ===================== PROFILE TAB ===================== -->
        <?php if ($active_tab === 'profile'): ?>
        <div class="card-dark">
          <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>

          <form method="POST"
            action="account.php?tab=profile<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>"
            enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" class="form-control-dark"
                    value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control-dark"
                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Bio</label>
              <textarea name="bio" class="form-control-dark"
                placeholder="Tell us about yourself... HTML allowed for formatting"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
              <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.35rem;">
                <i class="fas fa-bug"></i> VULNERABILITY: HTML injection possible in bio field
              </small>
            </div>

            <!-- VULNERABILITY: Mass Assignment - hidden fields -->
            <div style="display: none;">
              <input type="text" name="role" value="<?php echo htmlspecialchars($user['role'] ?? 'guest'); ?>">
              <input type="text" name="points" value="<?php echo intval($user['points'] ?? 0); ?>">
              <input type="text" name="api_key" value="<?php echo htmlspecialchars($user['api_key'] ?? ''); ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Avatar</label>
              <input type="file" name="avatar" class="form-control-dark" accept="image/*,.php,.txt,.html,.js">
              <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.35rem;">
                <i class="fas fa-exclamation-triangle"></i> VULNERABILITY: Weak file upload validation - try uploading
                PHP files
              </small>
            </div>

            <button type="submit" name="update_profile" class="btn-darkhunter">
              <i class="fas fa-save"></i> Save Changes
            </button>
          </form>
        </div>

        <!-- Public Profile Preview -->
        <div class="card-dark">
          <h3><i class="fas fa-id-card"></i> Public Profile Preview</h3>
          <div
            style="background: var(--bg-secondary); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
              <div
                style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                <?php echo strtoupper(substr($user['username'] ?? 'G', 0, 1)); ?>
              </div>
              <div>
                <div style="font-weight: 700; color: var(--text-primary);">
                  <?php echo htmlspecialchars($user['username'] ?? 'Guest'); ?></div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                  <?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
              </div>
            </div>
            <!-- VULNERABILITY: HTML Injection - no htmlspecialchars on bio -->
            <div style="color: var(--text-secondary); line-height: 1.6;">
              <?php echo $user['bio'] ?? '<em style="color: var(--text-muted);">No bio yet. Write something about yourself...</em>'; ?>
              <!-- FLAG: DH{html_injection_bio} -->
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ===================== ORDERS TAB ===================== -->
        <?php if ($active_tab === 'orders'): ?>
        <div class="card-dark">
          <h3><i class="fas fa-shopping-bag"></i> Order History</h3>

          <!-- VULNERABILITY: IDOR indicator -->
          <div
            style="background: var(--bg-secondary); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; border-left: 3px solid var(--accent-orange);">
            <small style="color: var(--text-muted);">
              <i class="fas fa-eye"></i> Viewing orders for user ID: <span
                style="color: var(--accent-cyan); font-family: JetBrains Mono;"><?php echo $view_user_id; ?></span>
              <?php if (isset($_GET['user_id']) && (!isset($_SESSION['user_id']) || $_GET['user_id'] != $_SESSION['user_id'])): ?>
              <span style="color: var(--accent-red);"> (IDOR Active!)</span>
              <?php endif; ?>
            </small>
          </div>

          <?php if (!empty($orders)): ?>
          <?php foreach ($orders as $order): ?>
          <div class="order-card">
            <div class="order-header">
              <div>
                <div class="order-id">#<?php echo str_pad($order['id'] ?? 0, 6, '0', STR_PAD_LEFT); ?></div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                  <?php echo isset($order['created_at']) && !empty($order['created_at']) ? date('F d, Y \a\t h:i A', strtotime($order['created_at'])) : 'N/A'; ?>
                </div>
              </div>
              <span class="order-status status-<?php echo $order['status'] ?? 'pending'; ?>">
                <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
              </span>
            </div>
            <div class="order-product">
              <?php echo htmlspecialchars($order['product_name'] ?? 'Unknown Product'); ?>
            </div>
            <?php if (!empty($order['product_description'])): ?>
            <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">
              <?php echo htmlspecialchars(substr($order['product_description'], 0, 100)) . (strlen($order['product_description']) > 100 ? '...' : ''); ?>
            </div>
            <?php endif; ?>
            <div class="order-meta">
              <span class="order-price">
                <i class="fas fa-dollar-sign"></i>
                <?php echo number_format($order['total'] ?? ($order['product_price'] ?? 0), 2); ?>
              </span>
              <span><i class="fas fa-box"></i> Qty: <?php echo $order['quantity'] ?? 1; ?></span>
              <span><i class="fas fa-truck"></i>
                <?php echo htmlspecialchars($order['shipping_method'] ?? 'Standard'); ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-shopping-bag"></i></div>
            <h4>No orders yet</h4>
            <p>Your order history will appear here after purchases.</p>
            <a href="shop.php" class="btn-outline-darkhunter mt-3">
              <i class="fas fa-store"></i> Browse Shop
            </a>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ===================== SECURITY TAB ===================== -->
        <?php if ($active_tab === 'security'): ?>
        <div class="card-dark">
          <h3><i class="fas fa-lock"></i> Change Password</h3>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Update your password. Note: No current password verification required.
          </p>

          <form method="POST"
            action="account.php?tab=security<?php echo $view_user_id != (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0) ? '&user_id=' . $view_user_id : ''; ?>">
            <div class="form-group">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control-dark"
                placeholder="Enter new password (min 4 chars)" minlength="4">
              <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 0.35rem;">
                <i class="fas fa-bug"></i> VULNERABILITY: No old password verification required
              </small>
            </div>

            <div class="form-group">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control-dark"
                placeholder="Confirm new password">
            </div>

            <button type="submit" name="update_profile" class="btn-darkhunter">
              <i class="fas fa-key"></i> Update Password
            </button>
          </form>
        </div>

        <div class="card-dark">
          <h3><i class="fas fa-fingerprint"></i> Session Information</h3>
          <div class="session-info">
            <div class="session-info-row">
              <span class="session-info-label"><i class="fas fa-id-badge"></i> Session ID</span>
              <span class="session-info-value"><?php echo substr(session_id(), 0, 20); ?>...</span>
            </div>
            <div class="session-info-row">
              <span class="session-info-label"><i class="fas fa-network-wired"></i> IP Address</span>
              <span class="session-info-value"><?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?></span>
            </div>
            <div class="session-info-row">
              <span class="session-info-label"><i class="fas fa-clock"></i> Login Time</span>
              <span class="session-info-value"><?php echo $_SESSION['login_time'] ?? date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="session-info-row">
              <span class="session-info-label"><i class="fas fa-user-shield"></i> Current Role</span>
              <span class="session-info-value"
                style="color: var(--accent-<?php echo ($user['role'] ?? '') === 'admin' ? 'red' : 'cyan'; ?>);"><?php echo strtoupper($user['role'] ?? 'GUEST'); ?></span>
            </div>
          </div>
        </div>

        <div class="card-dark">
          <h3><i class="fas fa-exclamation-triangle"></i> Security Vulnerabilities</h3>
          <div
            style="background: var(--bg-secondary); border-radius: 12px; padding: 1rem; border: 1px solid var(--border-color);">
            <div style="margin-bottom: 0.75rem;">
              <span style="color: var(--accent-orange); font-weight: 600;"><i class="fas fa-bug"></i> Mass
                Assignment</span>
              <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0.25rem 0 0 0;">Any profile field can be
                updated including role and points.</p>
            </div>
            <div style="margin-bottom: 0.75rem;">
              <span style="color: var(--accent-orange); font-weight: 600;"><i class="fas fa-bug"></i> No CSRF
                Protection</span>
              <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0.25rem 0 0 0;">Forms can be submitted
                from external sites.</p>
            </div>
            <div>
              <span style="color: var(--accent-orange); font-weight: 600;"><i class="fas fa-bug"></i> Weak Password
                Policy</span>
              <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0.25rem 0 0 0;">Minimum 4 characters only.
              </p>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ===================== API TAB ===================== -->
        <?php if ($active_tab === 'api'): ?>
        <div class="card-dark">
          <h3><i class="fas fa-key"></i> API Keys</h3>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Your API key for accessing DarkHunter services programmatically.
          </p>

          <div class="api-key-box">
            <i class="fas fa-shield-alt"></i>
            <span><?php echo $api_key ?: 'No API key generated.'; ?></span>
          </div>

          <div class="mt-3">
            <h6 style="color: var(--accent-cyan); font-family: 'JetBrains Mono', monospace; margin-bottom: 0.75rem;">
              <i class="fas fa-terminal"></i> API Endpoints
            </h6>
            <div
              style="background: var(--bg-secondary); border-radius: 10px; padding: 1rem; border: 1px solid var(--border-color);">
              <table class="table-dark" style="margin: 0;">
                <thead>
                  <tr>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>Description</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span style="color: var(--accent-green);">GET</span></td>
                    <td style="font-family: 'JetBrains Mono', monospace;">/api/products</td>
                    <td>List all products</td>
                  </tr>
                  <tr>
                    <td><span style="color: var(--accent-green);">GET</span></td>
                    <td style="font-family: 'JetBrains Mono', monospace;">/api/products/{id}</td>
                    <td>Get product details</td>
                  </tr>
                  <tr>
                    <td><span style="color: var(--accent-orange);">POST</span></td>
                    <td style="font-family: 'JetBrains Mono', monospace;">/api/orders</td>
                    <td>Create order</td>
                  </tr>
                  <tr>
                    <td><span style="color: var(--accent-green);">GET</span></td>
                    <td style="font-family: 'JetBrains Mono', monospace;">/api/user/{id}</td>
                    <td>Get user info (IDOR possible)</td>
                  </tr>
                  <tr>
                    <td><span style="color: var(--accent-green);">GET</span></td>
                    <td style="font-family: 'JetBrains Mono', monospace;">/api/cart</td>
                    <td>Get cart items</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="mt-3">
            <h6 style="color: var(--accent-cyan); font-family: 'JetBrains Mono', monospace; margin-bottom: 0.75rem;">
              <i class="fas fa-code"></i> Example Request
            </h6>
            <div
              style="background: var(--bg-secondary); border-radius: 10px; padding: 1rem; border: 1px solid var(--border-color);">
              <pre
                style="color: var(--text-secondary); margin: 0; font-size: 0.85rem; font-family: 'JetBrains Mono', monospace; overflow-x: auto;">curl -H "X-API-Key: <?php echo $api_key; ?>"   -H "Content-Type: application/json"   http://localhost/api/products</pre>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ===================== SUBMISSIONS TAB ===================== -->
        <?php if ($active_tab === 'submissions'): ?>
        <div class="card-dark">
          <h3><i class="fas fa-flag"></i> Flag Submissions</h3>
          <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Your captured flags and earned points.
          </p>

          <?php if (!empty($submissions)): ?>
          <div style="display: grid; gap: 0.75rem;">
            <?php foreach ($submissions as $sub): ?>
            <div class="submission-card">
              <div>
                <div class="submission-flag"><i class="fas fa-flag"></i>
                  <?php echo htmlspecialchars($sub['flag_code'] ?? ''); ?></div>
                <div class="submission-type"><?php echo htmlspecialchars($sub['vulnerability_type'] ?? 'Unknown'); ?> |
                  <?php echo htmlspecialchars($sub['flag_name'] ?? 'Unnamed'); ?></div>
              </div>
              <div style="text-align: right;">
                <div class="submission-points">+<?php echo $sub['flag_points'] ?? 0; ?> pts</div>
                <div style="color: var(--text-muted); font-size: 0.75rem;">
                  <?php echo isset($sub['submitted_at']) && !empty($sub['submitted_at']) ? date('M d, Y', strtotime($sub['submitted_at'])) : 'N/A'; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="color: var(--text-secondary);">Total Points</span>
              <span
                style="font-family: 'JetBrains Mono', monospace; font-size: 1.5rem; font-weight: 700; color: var(--accent-green);">
                <?php echo number_format($total_points); ?> pts
              </span>
            </div>
          </div>
          <?php else: ?>
          <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-flag"></i></div>
            <h4>No submissions yet</h4>
            <p>Start hunting vulnerabilities and submit flags to earn points!</p>
            <a href="shop.php" class="btn-outline-darkhunter mt-3">
              <i class="fas fa-search"></i> Start Hunting
            </a>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ===================== NOTIFICATIONS TAB ===================== -->
        <?php if ($active_tab === 'notifications'): ?>
        <div class="card-dark">
          <h3><i class="fas fa-bell"></i> Notifications</h3>

          <?php if (!empty($notifications)): ?>
          <div>
            <?php foreach ($notifications as $notif): ?>
            <div class="notification-item <?php echo empty($notif['read_at']) ? 'notification-unread' : ''; ?>">
              <div class="notification-icon">
                <i class="fas fa-<?php echo $notif['icon'] ?? 'info-circle'; ?>"></i>
              </div>
              <div class="notification-content">
                <div class="notification-text"><?php echo htmlspecialchars($notif['message']); ?></div>
                <div class="notification-time">
                  <i class="far fa-clock"></i>
                  <?php echo isset($notif['created_at']) && !empty($notif['created_at']) ? date('M d, Y H:i', strtotime($notif['created_at'])) : 'N/A'; ?>
                  <?php if (empty($notif['read_at'])): ?>
                  <span style="color: var(--accent-cyan); margin-left: 0.5rem;">● New</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
            <h4>No notifications</h4>
            <p>You're all caught up! New alerts will appear here.</p>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <h6><i class="fas fa-bug"></i> DarkHunter</h6>
          <p style="color: var(--text-muted); font-size: 0.85rem;">Advanced cybersecurity training platform for ethical
            hackers and security professionals.</p>
        </div>
        <div class="footer-col">
          <h6>Account</h6>
          <a href="account.php?tab=profile">Profile</a>
          <a href="account.php?tab=orders">Orders</a>
          <a href="account.php?tab=security">Security</a>
          <a href="account.php?tab=api">API Keys</a>
        </div>
        <div class="footer-col">
          <h6>Platform</h6>
          <a href="shop.php">Shop</a>
          <a href="cart.php">Cart</a>
          <a href="submit.php">Submit Flag</a>
          <a href="admin.php">Admin Panel</a>
        </div>
        <div class="footer-col">
          <h6>Legal</h6>
          <a href="#">Terms of Service</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Responsible Disclosure</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>DarkHunter CTF Platform - Educational Use Only - <?php echo date('Y'); ?></p>
      </div>
    </div>
  </footer>

  <!-- VULNERABILITY: Exposed JavaScript config with sensitive data -->
  <script>
  // DarkHunter Account Client Configuration
  // Environment: production (staging config accidentally included)
  window.DARKHUNTER_ACCOUNT = {
    env: 'production',
    version: '2.1.0',
    apiBaseUrl: '/api/v2',
    userId: <?php echo $user['id'] ?? 0; ?>,
    username: '<?php echo addslashes($user['username'] ?? 'Guest'); ?>',
    role: '<?php echo $user['role'] ?? 'guest'; ?>',

    // VULNERABILITY: Internal endpoints exposed
    internalEndpoints: {
      userService: 'http://users.darkhunter.local:8080',
      orderService: 'http://orders.darkhunter.local:8081',
      notificationService: 'http://notifications.darkhunter.local:8082',
      sessionService: 'http://sessions.darkhunter.local:8083'
    },

    // VULNERABILITY: API key exposed
    apiKey: '<?php echo addslashes($api_key); ?>',

    // VULNERABILITY: JWT secret hint
    jwtSecretHint: 'darkhunter_secret_key_123',

    // VULNERABILITY: Debug mode
    debug: true,

    // Session info
    sessionId: '<?php echo session_id(); ?>',
    sessionStart: '<?php echo $_SESSION['login_time'] ?? date('Y-m-d H:i:s'); ?>'
  };

  // Internal API Client
  class AccountAPIClient {
    constructor() {
      this.baseURL = window.DARKHUNTER_ACCOUNT.apiBaseUrl;
      this.apiKey = window.DARKHUNTER_ACCOUNT.apiKey;
      this.userId = window.DARKHUNTER_ACCOUNT.userId;
    }

    // VULNERABILITY: IDOR - no ownership check
    async getUser(userId) {
      return fetch(`${this.baseURL}/users/${userId}`, {
        headers: {
          'X-API-Key': this.apiKey
        }
      });
    }

    // VULNERABILITY: Can update any user's profile
    async updateUser(userId, data) {
      return fetch(`${this.baseURL}/users/${userId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-API-Key': this.apiKey
        },
        body: JSON.stringify(data)
      });
    }

    // VULNERABILITY: SSRF through internal service
    async fetchInternal(service, endpoint) {
      const url = window.DARKHUNTER_ACCOUNT.internalEndpoints[service];
      if (url) {
        return fetch(`${url}${endpoint}`, {
          headers: {
            'Authorization': 'Bearer ' + this.apiKey
          }
        });
      }
    }
  }

  window.accountAPI = new AccountAPIClient();

  // Debug logging
  console.group('%c DarkHunter Account Debug ',
    'background: linear-gradient(135deg, #00d4ff, #b967ff); color: white; font-size: 14px; font-weight: bold; padding: 4px 8px; border-radius: 4px;'
  );
  console.log('%c User ID:', 'color: #00d4ff; font-weight: bold;', window.DARKHUNTER_ACCOUNT.userId);
  console.log('%c Role:', 'color: #00d4ff; font-weight: bold;', window.DARKHUNTER_ACCOUNT.role);
  console.log('%c API Key:', 'color: #00d4ff; font-weight: bold;', window.DARKHUNTER_ACCOUNT.apiKey);
  console.log('%c Session ID:', 'color: #00d4ff; font-weight: bold;', window.DARKHUNTER_ACCOUNT.sessionId);
  console.log('%c Internal Endpoints:', 'color: #ff1744; font-weight: bold;', window.DARKHUNTER_ACCOUNT
    .internalEndpoints);
  console.log('%c JWT Hint:', 'color: #ff1744; font-weight: bold;', window.DARKHUNTER_ACCOUNT.jwtSecretHint);
  console.groupEnd();

  // Hidden flag
  const _accountFlag = "DH{api_key_exposure}";

  // Toast auto-hide
  document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast-notification');
    toasts.forEach(toast => {
      setTimeout(() => {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
      }, 5000);
    });
  });

  // VULNERABILITY: Source map leak
  //# sourceMappingURL=/static/js/account.min.js.map
  </script>

  <!-- VULNERABILITY: Hidden debug info in comment -->
  <!--
        DEBUG: Account System Configuration
        ===================================
        Current User ID: <?php echo $view_user_id; ?>
        Session User ID: <?php echo $_SESSION['user_id'] ?? 'not set'; ?>
        User Role: <?php echo $user['role'] ?? 'unknown'; ?>
        Database: darkhunter_ctf
        Users Table: users (id, username, email, password, bio, avatar, api_key, points, role, created_at)
        Orders Table: orders (id, user_id, product_id, total, status, created_at)
        Submissions Table: submissions (id, user_id, flag_id, submitted_at)
        Notifications Table: notifications (id, user_id, message, icon, read_at, created_at)

        Internal Services:
        - User Service: users.darkhunter.local:8080
        - Order Service: orders.darkhunter.local:8081
        - Notification Service: notifications.darkhunter.local:8082

        FLAG: DH{debug_panel_exposed}
        FLAG: DH{idor_order_access}
        FLAG: DH{mass_assignment}
        FLAG: DH{csrf_profile_change}
        FLAG: DH{html_injection_bio}
        FLAG: DH{file_upload_rce}
        FLAG: DH{predictable_reset_token}
        FLAG: DH{api_key_exposure}
    -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>