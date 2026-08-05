<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SQLI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['sqli_case3_attempts'])) {
  $_SESSION['sqli_case3_attempts'] = 0;
}
if (!isset($_SESSION['sqli_case3_solved'])) {
  $_SESSION['sqli_case3_solved'] = false;
}

// ─── WAF Filter Configuration ────────────────────────────────────────────
$waf_blocked_keywords = ['UNION', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'EXEC', 'SCRIPT'];
$waf_blocked_chars = ['--', '/*', '*/', ';', '0x', 'CHAR(', 'CONCAT('];

// ─── Simulated Admin Panel Data ─────────────────────────────────────────
$system_users = [
  ['id' => 1, 'username' => 'admin', 'password_hash' => '5f4dcc3b5aa765d61d8327deb882cf99', 'role' => 'Super Admin', 'last_login' => '2026-05-22 05:30:00', 'secret' => 'DH{sqli_waf_bypass_master}'],
  ['id' => 2, 'username' => 'security_admin', 'password_hash' => 'e99a18c428cb38d5f260853678922e03', 'role' => 'Security Admin', 'last_login' => '2026-05-22 04:15:00', 'secret' => 'API_KEY: dh_sec_9b2e4c6d8a0f1e3b'],
  ['id' => 3, 'username' => 'db_admin', 'password_hash' => 'fc5e038d38a57032085441e7fe7010b0', 'role' => 'Database Admin', 'last_login' => '2026-05-21 23:00:00', 'secret' => 'DB_ROOT: mysql_root_2026'],
  ['id' => 4, 'username' => 'monitoring', 'password_hash' => '25d55ad283aa400af464c76d713c07ad', 'role' => 'Monitor', 'last_login' => '2026-05-21 20:00:00', 'secret' => 'WEBHOOK: https://hooks.darkhunter.local/alert'],
];

$system_logs = [
  ['time' => '2026-05-22 05:30:00', 'event' => 'Admin login successful', 'ip' => '192.168.1.10', 'severity' => 'info'],
  ['time' => '2026-05-22 05:28:00', 'event' => 'WAF blocked SQL injection attempt', 'ip' => '10.0.0.7', 'severity' => 'high'],
  ['time' => '2026-05-22 05:25:00', 'event' => 'WAF blocked UNION keyword', 'ip' => '10.0.0.7', 'severity' => 'high'],
  ['time' => '2026-05-22 05:20:00', 'event' => 'Failed login attempt', 'ip' => '10.0.0.7', 'severity' => 'medium'],
  ['time' => '2026-05-22 05:15:00', 'event' => 'Database backup completed', 'ip' => '192.168.1.10', 'severity' => 'info'],
];

// ─── WAF Detection Function ─────────────────────────────────────────────
function wafDetect($input)
{
  global $waf_blocked_keywords, $waf_blocked_chars;
  $detected = [];
  $input_upper = strtoupper($input);

  foreach ($waf_blocked_keywords as $keyword) {
    if (stripos($input_upper, $keyword) !== false) {
      $detected[] = $keyword;
    }
  }
  foreach ($waf_blocked_chars as $char) {
    if (stripos($input, $char) !== false) {
      $detected[] = $char;
    }
  }
  return $detected;
}

// ─── Vulnerable Admin Search Logic ─────────────────────────────────────
$search_results = [];
$search_query = null;
$waf_triggered = false;
$waf_blocked = [];
$bypass_success = false;
$flag_revealed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
  $query_input = $_GET['query'];

  // Check WAF
  $waf_blocked = wafDetect($query_input);
  $waf_triggered = !empty($waf_blocked);

  if (!$waf_triggered) {
    // VULNERABLE: Even though WAF filters keywords, the query is still vulnerable
    // Bypass techniques: encoding, comments, alternative syntax
    $query = "SELECT * FROM system_users WHERE username = '" . $query_input . "' OR role = '" . $query_input . "'";
    $search_query = $query;

    // Simulate bypass detection
    // Check for encoding bypass (URL encoded, hex, etc.)
    $decoded_input = urldecode($query_input);
    $double_decoded = urldecode($decoded_input);

    // Check for comment-based bypass
    if (preg_match('/\/\*!\d+\*\//', $query_input) || preg_match('/\/\*.*\*\//', $query_input)) {
      $bypass_success = true;
    }

    // Check for stacked query (semicolon bypass using alternative encoding)
    if (stripos($query_input, '%3B') !== false || stripos($query_input, '\\x3b') !== false) {
      $bypass_success = true;
    }

    // Check for alternative UNION syntax
    if (
      preg_match('/un\\+ion|uni\\%6fn|\\%55nion/i', $query_input) ||
      stripos($query_input, 'UNIUNIONON') !== false ||
      stripos($query_input, 'SELESELECTCT') !== false
    ) {
      $bypass_success = true;
    }

    // Check for MySQL inline comments bypass
    if (preg_match('/\/\*!\d+\s*(union|select)\s*\*\//i', $query_input)) {
      $bypass_success = true;
    }

    // If bypass successful, return all data including admin secrets
    if ($bypass_success) {
      $search_results = $system_users;
      $flag_revealed = true;
    } else {
      // Normal search
      foreach ($system_users as $user) {
        if (stripos($user['username'], $query_input) !== false || stripos($user['role'], $query_input) !== false) {
          $search_results[] = $user;
        }
      }
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['sqli_case3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['sqli_case3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['sqli_case3_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've successfully bypassed a Web Application Firewall (WAF) using advanced SQL injection techniques. By encoding payloads, using MySQL inline comments, and alternative syntax, you evaded detection and extracted sensitive admin data!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
  $_SESSION['sqli_case3_attempts']++;
}

$attempts = $_SESSION['sqli_case3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - SQLI Case 3 (WAF Bypass)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SQLI-Case3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SQLI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-shield-alt"></i> DarkHunter Admin Panel</h1>
      <p class="lab-description">Manage system users and monitor security events. This expert-level SQL Injection
        challenge features a Web Application Firewall (WAF) that blocks common SQLi keywords. <strong>Bypass the WAF to
          extract admin secrets!</strong></p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this WAF bypass vulnerability. You can continue exploring, but no additional
          points will be awarded.</p>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
    <div class="success-alert">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Completed!</h3>
        <p><?php echo $success_msg; ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- WAF Status Panel -->
    <div class="waf-panel">
      <div class="waf-header">
        <i class="fas fa-shield-virus"></i>
        <span>Web Application Firewall Status</span>
        <span class="waf-status <?php echo $waf_triggered ? 'triggered' : 'active'; ?>">
          <?php echo $waf_triggered ? 'ALERT TRIGGERED' : 'ACTIVE'; ?>
        </span>
      </div>
      <div class="waf-body">
        <div class="waf-rules">
          <div class="rule-category">
            <span class="category-name">Blocked Keywords:</span>
            <div class="rule-tags">
              <?php foreach ($waf_blocked_keywords as $kw): ?>
              <span class="rule-tag"><?php echo $kw; ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="rule-category">
            <span class="category-name">Blocked Characters:</span>
            <div class="rule-tags">
              <?php foreach ($waf_blocked_chars as $ch): ?>
              <span class="rule-tag char"><?php echo htmlspecialchars($ch); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php if ($waf_triggered): ?>
        <div class="waf-alert">
          <i class="fas fa-ban"></i>
          <span>WAF blocked your request! Detected: <?php echo implode(', ', $waf_blocked); ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Admin Grid -->
    <div class="admin-grid">

      <!-- Search Form (Vulnerable + WAF Protected) -->
      <div class="admin-card search-card">
        <div class="card-header">
          <i class="fas fa-search"></i>
          <h3>User Search</h3>
          <span class="vuln-badge"><i class="fas fa-shield-virus"></i> WAF Protected</span>
        </div>

        <form method="GET" action="" class="admin-search-form" id="admin-search-form">
          <div class="form-group">
            <label><i class="fas fa-user-shield"></i> Search Query</label>
            <input type="text" name="query" placeholder="Search users by username or role..." class="form-input"
              value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
          </div>

          <button type="submit" class="btn-search">
            <i class="fas fa-search"></i> Search
          </button>
        </form>

        <div class="search-info">
          <i class="fas fa-exclamation-triangle"></i>
          <span>WAF filters SQL keywords. Find a way to bypass the filter!</span>
        </div>
      </div>

      <!-- Query Inspector -->
      <?php if ($search_query): ?>
      <div class="admin-card query-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Query Inspector</h3>
        </div>
        <div class="query-display">
          <div class="query-label">Executed Query:</div>
          <code class="query-code"><?php echo htmlspecialchars($search_query); ?></code>

          <?php if ($bypass_success): ?>
          <div class="bypass-alert">
            <i class="fas fa-check-circle"></i>
            <span>WAF Bypass Successful! Filter evasion detected.</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Search Results -->
      <div class="admin-card results-card">
        <div class="card-header">
          <i class="fas fa-users-cog"></i>
          <h3>System Users</h3>
          <span class="result-count"><?php echo count($search_results); ?> users</span>
        </div>
        <div class="users-table">
          <div class="table-header">
            <span>User</span>
            <span>Role</span>
            <span>Last Login</span>
            <span>Secret</span>
          </div>
          <?php if (empty($search_results)): ?>
          <div class="no-results">
            <i class="fas fa-inbox"></i>
            <span>No users found. Try a different query or bypass the WAF.</span>
          </div>
          <?php else: ?>
          <?php foreach ($search_results as $user): ?>
          <div class="user-row">
            <div class="user-cell">
              <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
              <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <span
              class="role-cell <?php echo strtolower(str_replace(' ', '-', $user['role'])); ?>"><?php echo htmlspecialchars($user['role']); ?></span>
            <span class="login-cell"><?php echo $user['last_login']; ?></span>
            <span class="secret-cell <?php echo $flag_revealed ? 'revealed' : ''; ?>">
              <?php echo $flag_revealed ? htmlspecialchars($user['secret']) : '************'; ?>
            </span>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Bypass Techniques -->
      <div class="admin-card techniques-card">
        <div class="card-header">
          <i class="fas fa-book-skull"></i>
          <h3>WAF Bypass Techniques</h3>
        </div>
        <div class="techniques-list">
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-code"></i>
              <span class="tech-name">MySQL Inline Comments</span>
            </div>
            <code class="tech-example">/*!50000SELECT*/ /*!50000FROM*/</code>
            <p class="tech-desc">MySQL treats /*!50000...*/ as executable code if version >= 5.0. WAF sees it as a
              comment.</p>
          </div>
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-font"></i>
              <span class="tech-name">Keyword Nesting</span>
            </div>
            <code class="tech-example">UNIUNIONON, SELESELECTCT</code>
            <p class="tech-desc">Single-pass filters remove the inner keyword, leaving the outer characters to
              reconstruct it.</p>
          </div>
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-percent"></i>
              <span class="tech-name">URL Encoding</span>
            </div>
            <code class="tech-example">%55nion, %53elect, %20</code>
            <p class="tech-desc">Encode characters using URL encoding. Some WAFs don't decode before filtering.</p>
          </div>
          <div class="technique">
            <div class="tech-header">
              <i class="fas fa-layer-group"></i>
              <span class="tech-name">Comment Injection</span>
            </div>
            <code class="tech-example">UN/**/ION, SEL/**/ECT</code>
            <p class="tech-desc">Insert comments inside keywords to break signature matching while SQL parser ignores
              them.</p>
          </div>
        </div>
      </div>

      <!-- Security Logs -->
      <div class="admin-card logs-card">
        <div class="card-header">
          <i class="fas fa-clipboard-list"></i>
          <h3>Security Event Logs</h3>
        </div>
        <div class="logs-list">
          <?php foreach ($system_logs as $log): ?>
          <div class="log-item severity-<?php echo $log['severity']; ?>">
            <span class="log-time"><?php echo $log['time']; ?></span>
            <span class="log-event"><?php echo htmlspecialchars($log['event']); ?></span>
            <span class="log-ip"><?php echo $log['ip']; ?></span>
            <span
              class="severity-badge <?php echo $log['severity']; ?>"><?php echo strtoupper($log['severity']); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>WAF Triggered: <?php echo $waf_triggered ? 'YES' : 'NO'; ?></span>
          <span>Bypass Success: <?php echo $bypass_success ? 'YES' : 'NO'; ?></span>
          <span>Query String: <?php echo $_SERVER['QUERY_STRING'] ?: 'None'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The WAF uses a simple keyword blacklist. Examine the blocked keywords panel. Try using
        <strong>MySQL inline comments</strong> like <code>/*!50000SELECT*/</code> - MySQL executes these as real code
        while the WAF sees them as harmless comments.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Try <strong>keyword nesting</strong> for single-pass filters: <code>UNIUNIONON</code>
        becomes <code>UNION</code> after the filter removes the inner "UNION". Also try URL encoding:
        <code>%55nion</code> for UNION and <code>%53elect</code> for SELECT.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use this bypass payload:
        <code>' /*!50000UNION*/ /*!50000SELECT*/ 1,username,password_hash,role,last_login,secret /*!50000FROM*/ system_users-- </code>.
        The MySQL inline comments with version number bypass the WAF while remaining executable by the database!
      </div>
    </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <!-- Hidden form for solve detection -->
  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
  window.addEventListener('load', function() {
    const bypassAlert = document.querySelector('.bypass-alert');
    const alreadySolved = document.querySelector('.solved-banner');

    if (bypassAlert && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>'