<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Insecure-Deserialization']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['deser_easy1_attempts'])) {
  $_SESSION['deser_easy1_attempts'] = 0;
}
if (!isset($_SESSION['deser_easy1_solved'])) {
  $_SESSION['deser_easy1_solved'] = false;
}
if (!isset($_SESSION['deser_easy1_stage'])) {
  $_SESSION['deser_easy1_stage'] = 1;
}

// ─── Simulated Application: DarkHunter Session Manager ─────────────────
// A user session management system that stores serialized User objects
// in cookies for "performance optimization" - vulnerable to object injection

class UserSession
{
  public $username;
  public $role = 'user';
  public $isAdmin = false;
  public $permissions = ['read'];
  public $sessionId;

  public function __construct($username = 'guest')
  {
    $this->username = $username;
    $this->sessionId = bin2hex(random_bytes(16));
  }

  public function __wakeup()
  {
    // Re-establish session state after deserialization
    if ($this->isAdmin) {
      $this->permissions = ['read', 'write', 'delete', 'admin'];
    }
  }

  public function getDashboard()
  {
    if ($this->isAdmin) {
      return 'admin_dashboard';
    }
    return 'user_dashboard';
  }
}

class FileAccessor
{
  public $filename;
  public $mode = 'r';

  public function __destruct()
  {
    if ($this->filename && file_exists($this->filename)) {
      // Simulated file operation
      $content = file_get_contents($this->filename);
      // In real app, this would log or process the file
    }
  }
}

// ─── Vulnerable Logic: Deserialize user-controlled cookie data ──────────
$session_data = isset($_COOKIE['darkhunter_session']) ? $_COOKIE['darkhunter_session'] : null;
$user = null;
$exploited = false;
$exploit_type = '';
$decoded_session = null;

if ($session_data) {
  // VULNERABLE: Direct unserialize without validation or allowed_classes
  try {
    $decoded_session = base64_decode($session_data);
    $user = @unserialize($decoded_session);

    // Detect exploitation attempts
    if ($user instanceof UserSession) {
      if ($user->isAdmin === true || $user->role === 'admin') {
        $exploited = true;
        $exploit_type = 'Privilege Escalation - Modified UserSession object properties';
      }
    } elseif ($user instanceof FileAccessor) {
      $exploited = true;
      $exploit_type = 'Object Injection - Arbitrary FileAccessor instantiation';
    } elseif (is_object($user) && !($user instanceof UserSession)) {
      $exploited = true;
      $exploit_type = 'Arbitrary Object Injection - Unexpected class instantiation';
    }

    // Check for serialized string tampering patterns
    if (is_string($decoded_session) && strpos($decoded_session, 'O:') === 0) {
      // Check for modified boolean values (common tampering pattern)
      if (strpos($decoded_session, 'b:1;') !== false && strpos($decoded_session, 'isAdmin') !== false) {
        if (!$exploited) {
          $exploited = true;
          $exploit_type = 'Property Tampering - isAdmin boolean modified to true';
        }
      }
      // Check for role modification
      if (preg_match('/s:4:"role".*s:5:"admin"/', $decoded_session)) {
        if (!$exploited) {
          $exploited = true;
          $exploit_type = 'Property Tampering - Role changed to admin';
        }
      }
    }
  } catch (Exception $e) {
    $user = null;
  }
}

// Generate a legitimate session for demo
$legit_session = new UserSession('demo_user');
$legit_cookie = base64_encode(serialize($legit_session));

// Generate exploit payload examples
$exploit_payloads = [
  'Admin Escalation' => base64_encode(serialize(
    (function () {
      $u = new UserSession('attacker');
      $u->isAdmin = true;
      $u->role = 'admin';
      return $u;
    })()
  )),
  'File Read (LFI)' => base64_encode(serialize(
    (function () {
      $f = new FileAccessor();
      $f->filename = '/etc/passwd';
      return $f;
    })()
  )),
];

$current_stage = $_SESSION['deser_easy1_stage'];
$stage_messages = [
  1 => "Stage 1: Understand how serialized objects are stored and transmitted. Examine the session cookie format.",
  2 => "Stage 2: Decode the base64 cookie and analyze the serialized PHP object structure. Identify tamperable properties.",
  3 => "Stage 3: Modify object properties (isAdmin, role) and re-encode to escalate privileges or inject arbitrary objects.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['deser_easy1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['deser_easy1_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['deser_easy1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited insecure deserialization through object property tampering. By decoding the base64-encoded session cookie, modifying the serialized UserSession object to set isAdmin=true and role='admin', then re-encoding it, you bypassed all authentication checks. This demonstrates how unserialize() on attacker-controlled data allows complete privilege escalation!";
}

// Increment attempts on any interaction
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($session_data || isset($_GET['reset']))) {
  $_SESSION['deser_easy1_attempts']++;
}
$attempts = $_SESSION['deser_easy1_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['deser_easy1_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['deser_easy1_stage'] = 3;
  $current_stage = 3;
}

// Reset session cookie if requested
if (isset($_GET['reset'])) {
  setcookie('darkhunter_session', $legit_cookie, time() + 3600, '/');
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Session Manager - Insecure Deserialization Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/INSECURE-DESERIALIZATION-CASE1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Deserialization Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-cookie-bite"></i> DarkHunter Session Manager</h1>
      <p class="lab-description">A high-performance session management system that serializes User objects
        into base64-encoded cookies for "zero-database lookups". <strong>Can you tamper with the serialized
          object</strong>
        to escalate privileges or inject malicious objects?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this insecure deserialization vulnerability.</p>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
      <div class="success-alert"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Completed!</h3>
          <p><?php echo $success_msg; ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="stage-tracker">
      <div class="stage-header"><i class="fas fa-layer-group"></i><span>Attack Chain Progress</span></div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info"><span class="stage-title">Reconnaissance</span><span class="stage-desc">Analyze
              session cookie</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">Decode & Analyze</span><span class="stage-desc">Inspect
              serialized object</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Property Tampering</span><span class="stage-desc">Modify &
              re-encode</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="session-card">
      <div class="session-header">
        <div class="session-brand"><i class="fas fa-fingerprint"></i><span>Session Inspector</span></div>
        <div class="session-badge"><i class="fas fa-user-shield"></i><span>Auth State</span></div>
      </div>
      <div class="session-body">
        <div class="cookie-panel">
          <div class="cookie-header"><i class="fas fa-cookie"></i><span>Current Session Cookie</span></div>
          <div class="cookie-display">
            <div class="cookie-name">darkhunter_session</div>
            <code
              class="cookie-value"><?php echo $session_data ? htmlspecialchars(substr($session_data, 0, 80)) . '...' : 'Not set'; ?></code>
          </div>
          <div class="cookie-actions">
            <a href="?reset=1" class="cookie-btn"><i class="fas fa-undo"></i> Reset to Default</a>
          </div>
        </div>

        <div class="object-panel">
          <div class="object-header"><i class="fas fa-cube"></i><span>Deserialized Object State</span></div>
          <?php if ($user && is_object($user)): ?>
            <div class="object-state">
              <div class="object-class">
                <span class="class-label">Class:</span>
                <code class="class-name"><?php echo get_class($user); ?></code>
              </div>
              <div class="object-props">
                <?php foreach ((array)$user as $prop => $val): ?>
                  <div
                    class="prop-row <?php echo ($prop === "\0UserSession\0isAdmin" || $prop === "isAdmin") && $val === true ? 'tampered' : ''; ?>">
                    <span class="prop-name"><?php echo htmlspecialchars($prop); ?></span>
                    <span class="prop-value"><?php
                                              if (is_bool($val)) echo $val ? '<span class="bool-true">true</span>' : '<span class="bool-false">false</span>';
                                              elseif (is_array($val)) echo '<span class="array-val">Array(' . count($val) . ')</span>';
                                              else echo htmlspecialchars((string)$val);
                                              ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="object-placeholder">
              <i class="fas fa-cube"></i>
              <p>No valid session object. Click "Reset to Default" to generate a legitimate session cookie.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="dashboard-panel">
          <div class="dashboard-header"><i class="fas fa-desktop"></i><span>Access Control Dashboard</span></div>
          <div class="dashboard-body">
            <?php if ($user instanceof UserSession && $user->isAdmin): ?>
              <div class="admin-dashboard">
                <div class="dashboard-banner admin">
                  <i class="fas fa-crown"></i>
                  <span>ADMINISTRATOR ACCESS GRANTED</span>
                </div>
                <div class="dashboard-perms">
                  <?php foreach ($user->permissions as $perm): ?>
                    <span class="perm-badge admin"><i class="fas fa-check"></i> <?php echo $perm; ?></span>
                  <?php endforeach; ?>
                </div>
                <div class="dashboard-actions">
                  <button class="action-btn admin"><i class="fas fa-users"></i> Manage Users</button>
                  <button class="action-btn admin"><i class="fas fa-cog"></i> System Settings</button>
                  <button class="action-btn admin"><i class="fas fa-file-alt"></i> View Logs</button>
                  <button class="action-btn admin"><i class="fas fa-database"></i> Database Admin</button>
                </div>
              </div>
            <?php elseif ($user instanceof UserSession): ?>
              <div class="user-dashboard">
                <div class="dashboard-banner user">
                  <i class="fas fa-user"></i>
                  <span>STANDARD USER ACCESS</span>
                </div>
                <div class="dashboard-perms">
                  <?php foreach ($user->permissions as $perm): ?>
                    <span class="perm-badge user"><i class="fas fa-check"></i> <?php echo $perm; ?></span>
                  <?php endforeach; ?>
                </div>
                <div class="dashboard-actions">
                  <button class="action-btn user"><i class="fas fa-eye"></i> View Profile</button>
                  <button class="action-btn user disabled"><i class="fas fa-lock"></i> Admin Panel</button>
                  <button class="action-btn user disabled"><i class="fas fa-lock"></i> System Logs</button>
                </div>
              </div>
            <?php else: ?>
              <div class="guest-dashboard">
                <div class="dashboard-banner guest">
                  <i class="fas fa-user-slash"></i>
                  <span>GUEST ACCESS - NO SESSION</span>
                </div>
                <p class="guest-msg">Set a valid session cookie to access the dashboard.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($exploited): ?>
          <div class="exploit-detected-panel">
            <div class="exploit-header"><i class="fas fa-bug"></i><span>Exploit Detected!</span></div>
            <div class="exploit-body">
              <div class="exploit-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>Detection:</strong> <?php echo htmlspecialchars($exploit_type); ?></span>
              </div>
              <?php if ($decoded_session): ?>
                <div class="raw-serialized">
                  <span class="raw-label">Raw Serialized Data:</span>
                  <pre><?php echo htmlspecialchars($decoded_session); ?></pre>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="payloads-panel">
      <div class="payloads-header"><i class="fas fa-flask"></i><span>Payload Laboratory</span></div>
      <div class="payloads-body">
        <p class="payloads-intro">Study these payload examples to understand the serialization format. Then craft your
          own!</p>

        <div class="payload-section">
          <h4><i class="fas fa-shield-alt"></i> Legitimate Session (Baseline)</h4>
          <div class="payload-row">
            <div class="payload-info">
              <span class="payload-name">Normal UserSession</span>
              <code class="payload-code"><?php echo htmlspecialchars($legit_cookie); ?></code>
            </div>
            <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $legit_cookie; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
          <div class="payload-decoded">
            <span class="decoded-label">Decoded:</span>
            <pre><?php echo htmlspecialchars(base64_decode($legit_cookie)); ?></pre>
          </div>
        </div>

        <div class="payload-section exploit">
          <h4><i class="fas fa-skull-crossbones"></i> Exploit Payloads</h4>
          <?php foreach ($exploit_payloads as $name => $payload): ?>
            <div class="payload-row">
              <div class="payload-info">
                <span class="payload-name"><?php echo $name; ?></span>
                <code class="payload-code"><?php echo htmlspecialchars(substr($payload, 0, 60)) . '...'; ?></code>
              </div>
              <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $payload; ?>')">
                <i class="fas fa-copy"></i>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="serialization-ref-panel">
      <div class="ref-header"><i class="fas fa-book"></i><span>PHP Serialization Format Reference</span></div>
      <div class="ref-body">
        <div class="ref-row">
          <code class="ref-type">s:6:"string";</code>
          <span class="ref-desc">String of length 6</span>
        </div>
        <div class="ref-row">
          <code class="ref-type">i:1337;</code>
          <span class="ref-desc">Integer value</span>
        </div>
        <div class="ref-row">
          <code class="ref-type">b:1;</code>
          <span class="ref-desc">Boolean true (change to b:0; for false)</span>
        </div>
        <div class="ref-row">
          <code class="ref-type">O:4:"User":2:{...}</code>
          <span class="ref-desc">Object of class "User" with 2 properties</span>
        </div>
        <div class="ref-row">
          <code class="ref-type">a:2:{i:0;s:3:"one";i:1;s:3:"two";}</code>
          <span class="ref-desc">Array with 2 elements</span>
        </div>
        <div class="ref-row">
          <code class="ref-type">N;</code>
          <span class="ref-desc">NULL value</span>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code>Cookie: darkhunter_session=<?php echo $session_data ? htmlspecialchars(substr($session_data, 0, 40)) . '...' : '...'; ?></code>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Direct unserialize on attacker-controlled cookie
$session_data = $_COOKIE['darkhunter_session'];
$decoded = base64_decode($session_data);

// No validation, no allowed_classes restriction!
$user = unserialize($decoded);

// Application trusts the deserialized object completely
if ($user->isAdmin) {
  // Grant admin access without verification!
  showAdminPanel();
}

// Attacker modifies: O:11:"UserSession":5:{...s:7:"isAdmin";b:1;...}
// Result: Instant admin privileges!</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> unserialize()
            on untrusted data allows attackers to instantiate arbitrary objects, modify properties, and trigger magic
            methods. The application blindly trusts the deserialized object's state without verification, enabling
            privilege escalation, file operations, and code execution.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The session cookie is base64-encoded. Decode it to reveal the serialized PHP object. Look
          for patterns like <code>O:11:"UserSession"</code> which indicate an object. Try modifying <code>b:0;</code>
          (false) to <code>b:1;</code> (true) near the isAdmin property.</div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">You can also change the <code>role</code> property from <code>s:4:"user"</code> to
          <code>s:5:"admin"</code>. Remember to update the string length prefix! After modifying, base64-encode and set as
          the cookie value.
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use the browser's DevTools → Application → Cookies to edit the cookie. Set it to the "Admin
          Escalation" payload from the Payload Laboratory, or manually craft:
          <code>O:11:"UserSession":5:{s:8:"username";s:8:"attacker";s:4:"role";s:5:"admin";s:7:"isAdmin";b:1;s:11:"permissions";a:4:{i:0;s:4:"read";i:1;s:5:"write";i:2;s:6:"delete";i:3;s:5:"admin";}s:10:"sessionId";s:32:"deadbeef1234567890abcdef12345678";}</code>
          then base64-encode it.
        </div>
      </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
    window.addEventListener('load', function() {
      // Check if admin access is granted (exploitation successful)
      const adminBanner = document.querySelector('.dashboard-banner.admin');
      if (adminBanner && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>