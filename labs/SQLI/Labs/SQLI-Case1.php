<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['SQLI']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['sqli_case1_attempts'])) {
  $_SESSION['sqli_case1_attempts'] = 0;
}
if (!isset($_SESSION['sqli_case1_solved'])) {
  $_SESSION['sqli_case1_solved'] = false;
}

// ─── Simulated Employee Database ─────────────────────────────────────────
$employees = [
  ['id' => 1, 'username' => 'admin', 'password' => 'SuperSecretAdmin2026!', 'email' => 'admin@darkhunter.local', 'department' => 'IT Security', 'role' => 'System Administrator', 'salary' => 125000, 'ssn' => 'XXX-XX-XXXX', 'notes' => 'Flag: DH{sqli_union_master}'],
  ['id' => 2, 'username' => 'jsmith', 'password' => 'JSmith@2024!', 'email' => 'jsmith@darkhunter.local', 'department' => 'Engineering', 'role' => 'Senior Developer', 'salary' => 95000, 'ssn' => '123-45-6789', 'notes' => 'Working on API v3 migration.'],
  ['id' => 3, 'username' => 'mwilson', 'password' => 'Wilson#99', 'email' => 'mwilson@darkhunter.local', 'department' => 'HR', 'role' => 'HR Manager', 'salary' => 88000, 'ssn' => '987-65-4321', 'notes' => 'Annual reviews due next month.'],
  ['id' => 4, 'username' => 'rbrown', 'password' => 'Brownie42$', 'email' => 'rbrown@darkhunter.local', 'department' => 'Finance', 'role' => 'Financial Analyst', 'salary' => 78000, 'ssn' => '456-78-9012', 'notes' => 'Q2 budget report submitted.'],
  ['id' => 5, 'username' => 'tdavis', 'password' => 'DavisT#77', 'email' => 'tdavis@darkhunter.local', 'department' => 'Marketing', 'role' => 'Marketing Lead', 'salary' => 82000, 'ssn' => '789-01-2345', 'notes' => 'Campaign analytics review pending.'],
];

// ─── Vulnerable Login Logic ─────────────────────────────────────────────
$login_error = null;
$login_success = false;
$injected_query = null;
$user_data = null;
$flag_revealed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // VULNERABLE: Direct string concatenation into SQL query
  // No prepared statements, no sanitization
  $query = "SELECT * FROM employees WHERE username = '" . $username . "' AND password = '" . $password . "'";
  $injected_query = $query;

  // Simulate query execution (in real app this would hit the DB)
  // For the lab, we simulate the vulnerable behavior
  $found_user = null;

  // Check for UNION-based injection patterns
  if (stripos($username, 'UNION') !== false || stripos($password, 'UNION') !== false) {
    // UNION injection detected - simulate data extraction
    if (preg_match('/UNION\s+SELECT\s+.*admin.*|UNION\s+SELECT\s+.*notes.*/i', $username . $password)) {
      $flag_revealed = true;
      $found_user = $employees[0]; // Return admin data
    } elseif (preg_match('/UNION\s+SELECT/i', $username . $password)) {
      // Generic UNION - return first user as proof of injection
      $found_user = $employees[0];
    }
  }

  // Check for classic OR-based bypass
  if (!$found_user && (stripos($username, "' OR '") !== false || stripos($username, "' OR 1=1") !== false || stripos($password, "' OR '") !== false)) {
    $found_user = $employees[0]; // Bypass authentication
  }

  // Check for exact match (normal login)
  if (!$found_user) {
    foreach ($employees as $emp) {
      if ($emp['username'] === $username && $emp['password'] === $password) {
        $found_user = $emp;
        break;
      }
    }
  }

  if ($found_user) {
    $login_success = true;
    $user_data = $found_user;
  } else {
    $login_error = 'Invalid username or password.';
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['sqli_case1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['sqli_case1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['sqli_case1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a UNION-based SQL Injection vulnerability. By injecting malicious SQL into the login form, you bypassed authentication and extracted sensitive data from the database!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['sqli_case1_attempts']++;
}

$attempts = $_SESSION['sqli_case1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Portal - SQLI Case 1 (Basic Injection)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/SQLI-Case1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to SQLI Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-building"></i> DarkHunter Employee Portal</h1>
      <p class="lab-description">Access the internal employee management system. This beginner-friendly SQL Injection
        challenge features a vulnerable login form. <strong>No input sanitization applied!</strong> Extract sensitive
        employee data by manipulating the SQL query.</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this SQL Injection vulnerability. You can continue exploring, but no additional
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

    <!-- Login Section -->
    <div class="portal-grid">

      <!-- Login Form (Vulnerable) -->
      <div class="portal-card login-card">
        <div class="card-header">
          <i class="fas fa-sign-in-alt"></i>
          <h3>Employee Login</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Vulnerable</span>
        </div>

        <?php if ($login_error): ?>
          <div class="login-error">
            <i class="fas fa-times-circle"></i>
            <span><?php echo htmlspecialchars($login_error); ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form" id="login-form">
          <input type="hidden" name="login" value="1">

          <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <input type="text" name="username" placeholder="Enter username..." class="form-input"
              value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
          </div>

          <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="text" name="password" placeholder="Enter password..." class="form-input"
              value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>">
          </div>

          <button type="submit" class="btn-login">
            <i class="fas fa-arrow-right"></i> Sign In
          </button>
        </form>

        <div class="login-hint">
          <i class="fas fa-info-circle"></i>
          <span>Valid accounts: jsmith, mwilson, rbrown, tdavis (passwords unknown)</span>
        </div>
      </div>

      <!-- Query Inspector -->
      <?php if ($injected_query): ?>
        <div class="portal-card query-card">
          <div class="card-header">
            <i class="fas fa-code"></i>
            <h3>Executed Query</h3>
          </div>
          <div class="query-display">
            <div class="query-label">Raw SQL Query:</div>
            <code class="query-code"><?php echo htmlspecialchars($injected_query); ?></code>

            <div class="query-analysis">
              <?php if (stripos($injected_query, 'UNION') !== false): ?>
                <span class="analysis-badge danger"><i class="fas fa-exclamation-triangle"></i> UNION Injection
                  Detected</span>
              <?php elseif (stripos($injected_query, "' OR '") !== false || stripos($injected_query, "' OR 1=1") !== false): ?>
                <span class="analysis-badge warning"><i class="fas fa-exclamation-circle"></i> OR-Based Bypass
                  Detected</span>
              <?php else: ?>
                <span class="analysis-badge info"><i class="fas fa-info-circle"></i> Standard Query</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- User Profile (Shown on successful login/injection) -->
      <?php if ($login_success && $user_data): ?>
        <div class="portal-card profile-card">
          <div class="card-header">
            <i class="fas fa-id-card"></i>
            <h3>Employee Profile</h3>
            <span class="role-badge"><?php echo htmlspecialchars($user_data['role']); ?></span>
          </div>
          <div class="profile-content">
            <div class="profile-avatar">
              <?php echo strtoupper(substr($user_data['username'], 0, 1)); ?>
            </div>
            <div class="profile-details">
              <div class="detail-row">
                <span class="detail-label">Username</span>
                <span class="detail-value"><?php echo htmlspecialchars($user_data['username']); ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Department</span>
                <span class="detail-value"><?php echo htmlspecialchars($user_data['department']); ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Role</span>
                <span class="detail-value"><?php echo htmlspecialchars($user_data['role']); ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Salary</span>
                <span class="detail-value salary">$<?php echo number_format($user_data['salary']); ?></span>
              </div>
              <div class="detail-row">
                <span class="detail-label">SSN</span>
                <span class="detail-value sensitive"><?php echo htmlspecialchars($user_data['ssn']); ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Private Notes (Target for Flag) -->
        <div class="portal-card notes-card">
          <div class="card-header">
            <i class="fas fa-sticky-note"></i>
            <h3>Private Notes</h3>
            <?php if ($user_data['username'] === 'admin'): ?>
              <span class="secret-badge"><i class="fas fa-lock-open"></i> Admin Access</span>
            <?php endif; ?>
          </div>
          <div class="notes-content">
            <p class="notes-text"><?php echo htmlspecialchars($user_data['notes']); ?></p>
            <?php if ($flag_revealed || $user_data['username'] === 'admin'): ?>
              <div class="flag-reveal">
                <i class="fas fa-flag"></i>
                <code>DH{sqli_union_master}</code>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Database Schema Info -->
      <div class="portal-card schema-card">
        <div class="card-header">
          <i class="fas fa-database"></i>
          <h3>Database Schema</h3>
        </div>
        <div class="schema-content">
          <div class="schema-table">
            <div class="table-name"><i class="fas fa-table"></i> employees</div>
            <div class="table-columns">
              <div class="column"><span class="col-name">id</span><span class="col-type">INT</span></div>
              <div class="column"><span class="col-name">username</span><span class="col-type">VARCHAR(50)</span></div>
              <div class="column"><span class="col-name">password</span><span class="col-type">VARCHAR(100)</span></div>
              <div class="column"><span class="col-name">email</span><span class="col-type">VARCHAR(100)</span></div>
              <div class="column"><span class="col-name">department</span><span class="col-type">VARCHAR(50)</span>
              </div>
              <div class="column"><span class="col-name">role</span><span class="col-type">VARCHAR(50)</span></div>
              <div class="column"><span class="col-name">salary</span><span class="col-type">INT</span></div>
              <div class="column"><span class="col-name">ssn</span><span class="col-type">VARCHAR(11)</span></div>
              <div class="column highlight"><span class="col-name">notes</span><span class="col-type">TEXT</span><span
                  class="col-flag"><i class="fas fa-flag"></i></span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Employee Directory -->
      <div class="portal-card directory-card">
        <div class="card-header">
          <i class="fas fa-users"></i>
          <h3>Employee Directory</h3>
        </div>
        <div class="directory-list">
          <?php foreach ($employees as $emp): ?>
            <div class="directory-item">
              <div class="dir-avatar"><?php echo strtoupper(substr($emp['username'], 0, 1)); ?></div>
              <div class="dir-info">
                <span class="dir-name"><?php echo htmlspecialchars($emp['username']); ?></span>
                <span class="dir-dept"><?php echo htmlspecialchars($emp['department']); ?></span>
              </div>
              <span class="dir-role"><?php echo htmlspecialchars($emp['role']); ?></span>
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
          <span>Content-Type: <?php echo $_SERVER['CONTENT_TYPE'] ?? 'Not Set'; ?></span>
          <span>User-Agent: <?php echo substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50); ?>...</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The login form constructs SQL queries using direct string concatenation. Try entering
          <code>' OR '1'='1</code> in the username field to create a tautology that always evaluates to true.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">For UNION-based extraction, you need to match the number of columns. The
          <code>employees</code> table has 9 columns. Try: <code>' UNION SELECT 1,2,3,4,5,6,7,8,9-- </code> then replace
          numbers with actual column names to extract the admin notes containing the flag.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use this payload in the username field:
          <code>' UNION SELECT id,username,password,email,department,role,salary,ssn,notes FROM employees WHERE username='admin'-- </code>.
          This will bypass authentication and return the admin's data including the flag in the notes column!
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
      const flagRevealed = document.querySelector('.flag-reveal');
      const alreadySolved = document.querySelector('.solved-banner');

      if (flagRevealed && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>