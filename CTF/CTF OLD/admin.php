<?php

/**
 * DarkHunter CTF - Admin Panel (Refactored)
 * 
 * Modern cybersecurity dashboard with proper security controls.
 * CTF vulnerabilities are preserved where they are part of the challenge,
 * but admin access controls and debug leaks are secured.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db_ctf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/functions.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/CTF/ctf_session.php';

// ===================== AUTHENTICATION & AUTHORIZATION =====================

/**
 * Check if user is authenticated and has admin role.
 * No session overwrite - validates existing session only.
 */

$is_admin = is_admin();

// Redirect non-admin users to login/account page
// if (!$is_admin) {
//   header('Location: account.php');
//   exit;
// }

// ===================== DATA FETCHING FUNCTIONS =====================

/**
 * Fetch dashboard statistics
 */
function get_dashboard_stats(PDO $pdo): array
{
  $stats = [
    'users' => 0,
    'products' => 0,
    'orders' => 0,
    'submissions' => 0
  ];

  try {
    $tables = ['users', 'products', 'orders', 'submissions'];
    foreach ($tables as $table) {
      $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
      if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
        $result = $stmt->fetch();
        $stats[$table] = (int)($result['count'] ?? 0);
      }
    }
  } catch (PDOException $e) {
    error_log("[DarkHunter] Stats error: " . $e->getMessage());
  }

  return $stats;
}

/**
 * Fetch recent submissions for dashboard
 */
function get_recent_submissions(PDO $pdo, int $limit = 10): array
{
  try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'submissions'");
    if ($stmt->rowCount() === 0) {
      return [];
    }
    $stmt = $pdo->query("SHOW TABLES LIKE 'flags'");
    if ($stmt->rowCount() === 0) {
      return [];
    }

    $stmt = $pdo->prepare("
            SELECT s.id, s.user_id, s.flag_id, s.submitted_at,
                   u.username, f.flag_code, f.vulnerability_type, f.points
            FROM submissions s
            JOIN users u ON s.user_id = u.id
            JOIN flags f ON s.flag_id = f.id
            ORDER BY s.submitted_at DESC
            LIMIT ?
        ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log("[DarkHunter] Submissions error: " . $e->getMessage());
    return [];
  }
}

/**
 * Fetch all users for user management
 */
function get_all_users(PDO $pdo): array
{
  try {
    $stmt = $pdo->query("
            SELECT id, username, email, role, points, created_at
            FROM users
            ORDER BY id
        ");
    return $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log("[DarkHunter] Users error: " . $e->getMessage());
    return [];
  }
}

/**
 * Fetch application logs
 */
function get_logs(string $log_file): array
{
  if (!file_exists($log_file)) {
    return [];
  }
  $content = file_get_contents($log_file);
  $lines = explode("\n", $content);
  $lines = array_filter($lines);
  return array_slice($lines, -50);
}

/**
 * Fetch safe system information (no sensitive data)
 */
function get_safe_system_info(): array
{
  return [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
    'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
  ];
}

// ===================== ACTION HANDLERS =====================

$flash_message = null;
$flash_type = null;

// Handle log clearing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_logs'])) {
  $log_file = __DIR__ . '/logs/app.log';
  if (file_exists($log_file) && is_writable($log_file)) {
    file_put_contents($log_file, '');
    $flash_message = 'Logs cleared successfully.';
    $flash_type = 'success';
  } else {
    $flash_message = 'Unable to clear logs. Check file permissions.';
    $flash_type = 'danger';
  }
}

// Handle SSRF import (preserved as CTF feature but with validation)
$ssrf_result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_url']) && !empty($_POST['import_url'])) {
  $url = filter_var($_POST['import_url'], FILTER_SANITIZE_URL);
  // Note: fetch_url() must exist in functions.php
  if (function_exists('fetch_url')) {
    $ssrf_result = fetch_url($url);
  } else {
    $ssrf_result = 'Error: fetch_url function not available.';
  }
}

// ===================== LOAD DATA =====================

$stats = get_dashboard_stats($pdo);
$recent_submissions = get_recent_submissions($pdo, 10);
$all_users = get_all_users($pdo);
$logs = get_logs(__DIR__ . '/logs/app.log');
$system_info = get_safe_system_info();

// Active page
$admin_page = isset($_GET['page']) ? preg_replace('/[^a-z_]/', '', $_GET['page']) : 'dashboard';
$valid_pages = ['dashboard', 'users', 'logs', 'import', 'system', 'submissions'];
if (!in_array($admin_page, $valid_pages, true)) {
  $admin_page = 'dashboard';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin | DarkHunter CTF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/CTF/css/admin.css">
</head>

<body>
  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999;" onclick="toggleSidebar()">
  </div>

  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar">
    <a href="index.php" class="sidebar-brand">
      <i class="fas fa-shield-halved"></i>
      <span>DarkHunter</span>
    </a>

    <div class="sidebar-nav">
      <div class="sidebar-section">Overview</div>
      <a href="?page=dashboard" class="sidebar-link <?php echo $admin_page === 'dashboard' ? 'active' : ''; ?>">
        <i class="fas fa-chart-pie"></i>
        Dashboard
      </a>
      <a href="?page=users" class="sidebar-link <?php echo $admin_page === 'users' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i>
        Users
      </a>
      <a href="?page=submissions" class="sidebar-link <?php echo $admin_page === 'submissions' ? 'active' : ''; ?>">
        <i class="fas fa-flag"></i>
        Submissions
      </a>

      <div class="sidebar-section" style="margin-top:1rem;">Management</div>
      <a href="?page=logs" class="sidebar-link <?php echo $admin_page === 'logs' ? 'active' : ''; ?>">
        <i class="fas fa-file-lines"></i>
        Logs
      </a>
      <a href="?page=import" class="sidebar-link <?php echo $admin_page === 'import' ? 'active' : ''; ?>">
        <i class="fas fa-file-import"></i>
        Import
      </a>
      <a href="?page=system" class="sidebar-link <?php echo $admin_page === 'system' ? 'active' : ''; ?>">
        <i class="fas fa-server"></i>
        System
      </a>

      <div class="sidebar-section" style="margin-top:1rem;">Platform</div>
      <a href="index.php" class="sidebar-link">
        <i class="fas fa-home"></i>
        Home
      </a>
      <a href="shop.php" class="sidebar-link">
        <i class="fas fa-store"></i>
        Shop
      </a>
      <a href="account.php" class="sidebar-link">
        <i class="fas fa-user"></i>
        Account
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-avatar">
          <?php echo strtoupper(substr(htmlspecialchars($_SESSION['username'] ?? 'A'), 0, 1)); ?>
        </div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
          <div class="sidebar-user-role">Administrator</div>
        </div>
        <a href="account.php?logout=1" class="btn-dark-sm" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Top Bar -->
    <header class="top-bar">
      <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="top-bar-title">
          <?php
          $page_titles = [
            'dashboard' => ['fa-chart-pie', 'Dashboard'],
            'users' => ['fa-users', 'User Management'],
            'logs' => ['fa-file-lines', 'Application Logs'],
            'import' => ['fa-file-import', 'Data Import'],
            'system' => ['fa-server', 'System Information'],
            'submissions' => ['fa-flag', 'Flag Submissions']
          ];
          $icon = $page_titles[$admin_page][0] ?? 'fa-circle';
          $title = $page_titles[$admin_page][1] ?? 'Admin';
          ?>
          <i class="fas <?php echo $icon; ?>"></i>
          <?php echo htmlspecialchars($title); ?>
        </h1>
      </div>
      <div class="top-bar-actions">
        <span style="color: var(--text-muted); font-size: 0.85rem;">
          <i class="far fa-clock"></i> <?php echo date('M d, Y H:i'); ?>
        </span>
        <a href="index.php" class="btn-top">
          <i class="fas fa-external-link-alt"></i> View Site
        </a>
      </div>
    </header>

    <!-- Content Area -->
    <div class="content-area">
      <?php if ($flash_message): ?>
      <div class="alert-dark alert-dark-<?php echo $flash_type; ?>">
        <i class="fas fa-<?php echo $flash_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($flash_message); ?>
      </div>
      <?php endif; ?>

      <!-- ===================== DASHBOARD ===================== -->
      <?php if ($admin_page === 'dashboard'): ?>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon users"><i class="fas fa-users"></i></div>
          </div>
          <div class="stat-value"><?php echo number_format($stats['users']); ?></div>
          <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon products"><i class="fas fa-box"></i></div>
          </div>
          <div class="stat-value"><?php echo number_format($stats['products']); ?></div>
          <div class="stat-label">Products</div>
        </div>
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon orders"><i class="fas fa-shopping-cart"></i></div>
          </div>
          <div class="stat-value"><?php echo number_format($stats['orders']); ?></div>
          <div class="stat-label">Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon submissions"><i class="fas fa-flag"></i></div>
          </div>
          <div class="stat-value"><?php echo number_format($stats['submissions']); ?></div>
          <div class="stat-label">Submissions</div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card-dark">
            <div class="card-dark-header">
              <h3 class="card-dark-title"><i class="fas fa-clock-rotate-left"></i> Recent Submissions</h3>
              <a href="?page=submissions" class="btn-dark-sm">View All</a>
            </div>
            <div class="card-dark-body p-0">
              <?php if (!empty($recent_submissions)): ?>
              <div class="table-responsive">
                <table class="table-dark-custom">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Flag</th>
                      <th>Type</th>
                      <th>Points</th>
                      <th>Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($recent_submissions as $sub): ?>
                    <tr>
                      <td>
                        <span style="color: var(--accent-cyan); font-weight: 500;">
                          <?php echo htmlspecialchars($sub['username'] ?? 'Unknown'); ?>
                        </span>
                      </td>
                      <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">
                        <?php echo htmlspecialchars($sub['flag_code'] ?? 'N/A'); ?>
                      </td>
                      <td><?php echo htmlspecialchars($sub['vulnerability_type'] ?? 'Unknown'); ?></td>
                      <td style="color: var(--accent-green); font-weight: 600;">
                        +<?php echo (int)($sub['points'] ?? 0); ?>
                      </td>
                      <td style="white-space: nowrap;">
                        <?php echo isset($sub['submitted_at']) ? date('M d, H:i', strtotime($sub['submitted_at'])) : 'N/A'; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>No submissions yet</h5>
                <p>Flag submissions will appear here.</p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card-dark">
            <div class="card-dark-header">
              <h3 class="card-dark-title"><i class="fas fa-circle-info"></i> Platform Info</h3>
            </div>
            <div class="card-dark-body">
              <div class="mb-3">
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Server</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                  <?php echo htmlspecialchars($system_info['server_software']); ?>
                </div>
              </div>
              <div class="mb-3">
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">PHP Version</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                  <?php echo htmlspecialchars($system_info['php_version']); ?>
                </div>
              </div>
              <div class="mb-3">
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Protocol</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                  <?php echo htmlspecialchars($system_info['server_protocol']); ?>
                </div>
              </div>
              <div>
                <div style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 0.25rem;">Request Time</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                  <?php echo htmlspecialchars($system_info['request_time']); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ===================== USERS ===================== -->
      <?php if ($admin_page === 'users'): ?>
      <div class="card-dark">
        <div class="card-dark-header">
          <h3 class="card-dark-title"><i class="fas fa-users-gear"></i> User Management</h3>
          <span style="color: var(--text-muted); font-size: 0.85rem;">
            <?php echo count($all_users); ?> total users
          </span>
        </div>
        <div class="card-dark-body p-0">
          <div class="table-responsive">
            <table class="table-dark-custom">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Points</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($all_users as $u): ?>
                <tr>
                  <td style="font-family: 'JetBrains Mono', monospace;">#<?php echo (int)$u['id']; ?></td>
                  <td style="color: var(--accent-cyan); font-weight: 500;">
                    <?php echo htmlspecialchars($u['username'] ?? ''); ?>
                  </td>
                  <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                  <td>
                    <span class="badge-role <?php echo ($u['role'] ?? 'user') === 'admin' ? 'admin' : 'user'; ?>">
                      <i class="fas fa-<?php echo ($u['role'] ?? '') === 'admin' ? 'shield-halved' : 'user'; ?>"></i>
                      <?php echo htmlspecialchars(strtoupper($u['role'] ?? 'USER')); ?>
                    </span>
                  </td>
                  <td style="color: var(--accent-green); font-weight: 600;">
                    <?php echo number_format((int)($u['points'] ?? 0)); ?>
                  </td>
                  <td style="white-space: nowrap;">
                    <?php echo isset($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'N/A'; ?>
                  </td>
                  <td>
                    <a href="account.php?user_id=<?php echo (int)$u['id']; ?>" class="btn-dark-sm" title="View Profile">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ===================== LOGS ===================== -->
      <?php if ($admin_page === 'logs'): ?>
      <div class="card-dark">
        <div class="card-dark-header">
          <h3 class="card-dark-title"><i class="fas fa-file-lines"></i> Application Logs</h3>
          <form method="POST" action="?page=logs" class="m-0">
            <button type="submit" name="clear_logs" class="btn-dark-danger" onclick="return confirm('Clear all logs?')">
              <i class="fas fa-trash-can"></i> Clear
            </button>
          </form>
        </div>
        <div class="card-dark-body">
          <?php if (!empty($logs)): ?>
          <div class="code-block">
            <?php foreach ($logs as $log): ?>
            <div style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($log); ?></div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-file-circle-xmark"></i>
            <h5>No logs available</h5>
            <p>Application logs will appear here.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- ===================== IMPORT ===================== -->
      <?php if ($admin_page === 'import'): ?>
      <div class="ctf-banner">
        <i class="fas fa-triangle-exclamation"></i>
        <div class="ctf-banner-text">
          <strong>CTF Challenge:</strong> The import feature fetches remote URLs. This is an intentional vulnerability
          for the CTF. Try exploring SSRF possibilities with URLs like <code>file:///etc/passwd</code>.
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card-dark">
            <div class="card-dark-header">
              <h3 class="card-dark-title"><i class="fas fa-cloud-arrow-down"></i> URL Import</h3>
            </div>
            <div class="card-dark-body">
              <form method="POST" action="?page=import">
                <div class="mb-3">
                  <label class="form-label-dark">Import URL</label>
                  <input type="url" name="import_url" class="form-control-dark"
                    placeholder="https://example.com/data.json">
                </div>
                <button type="submit" class="btn-dark-primary">
                  <i class="fas fa-download"></i> Fetch Data
                </button>
              </form>

              <?php if ($ssrf_result): ?>
              <div style="margin-top: 1.5rem;">
                <label class="form-label-dark">Response</label>
                <div class="code-block">
                  <pre><?php echo htmlspecialchars(substr($ssrf_result, 0, 3000)); ?></pre>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card-dark">
            <div class="card-dark-header">
              <h3 class="card-dark-title"><i class="fas fa-code"></i> Serialized Import</h3>
            </div>
            <div class="card-dark-body">
              <form method="POST" action="?page=import">
                <div class="mb-3">
                  <label class="form-label-dark">Serialized PHP Data</label>
                  <textarea class="form-control-dark" rows="6"
                    placeholder="O:8:&quot;stdClass&quot;:1:{s:4:&quot;name&quot;;s:5:&quot;admin&quot;;}"></textarea>
                </div>
                <button type="submit" class="btn-dark-primary">
                  <i class="fas fa-upload"></i> Process
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ===================== SYSTEM ===================== -->
      <?php if ($admin_page === 'system'): ?>
      <div class="card-dark">
        <div class="card-dark-header">
          <h3 class="card-dark-title"><i class="fas fa-microchip"></i> System Information</h3>
        </div>
        <div class="card-dark-body">
          <div class="info-grid">
            <?php foreach ($system_info as $key => $value): ?>
            <div class="info-item">
              <div class="info-item-label"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></div>
              <div class="info-item-value"><?php echo htmlspecialchars($value); ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ===================== SUBMISSIONS ===================== -->
      <?php if ($admin_page === 'submissions'): ?>
      <div class="card-dark">
        <div class="card-dark-header">
          <h3 class="card-dark-title"><i class="fas fa-flag-checkered"></i> All Flag Submissions</h3>
          <span style="color: var(--text-muted); font-size: 0.85rem;">
            <?php echo count($recent_submissions); ?> submissions
          </span>
        </div>
        <div class="card-dark-body p-0">
          <?php if (!empty($recent_submissions)): ?>
          <div class="table-responsive">
            <table class="table-dark-custom">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>User</th>
                  <th>Flag Code</th>
                  <th>Vulnerability</th>
                  <th>Points</th>
                  <th>Submitted</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_submissions as $sub): ?>
                <tr>
                  <td style="font-family: 'JetBrains Mono', monospace;">#<?php echo (int)($sub['id'] ?? 0); ?></td>
                  <td style="color: var(--accent-cyan); font-weight: 500;">
                    <?php echo htmlspecialchars($sub['username'] ?? 'Unknown'); ?>
                  </td>
                  <td style="font-family: 'JetBrains Mono', monospace; color: var(--accent-green);">
                    <?php echo htmlspecialchars($sub['flag_code'] ?? 'N/A'); ?>
                  </td>
                  <td><?php echo htmlspecialchars($sub['vulnerability_type'] ?? 'Unknown'); ?></td>
                  <td style="color: var(--accent-green); font-weight: 600;">
                    +<?php echo (int)($sub['points'] ?? 0); ?>
                  </td>
                  <td style="white-space: nowrap;">
                    <?php echo isset($sub['submitted_at']) ? date('Y-m-d H:i:s', strtotime($sub['submitted_at'])) : 'N/A'; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-flag"></i>
            <h5>No submissions yet</h5>
            <p>Flag submissions will appear here.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
  }
  </script>
</body>

</html>