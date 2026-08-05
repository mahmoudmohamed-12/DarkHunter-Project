<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case2_attempts'])) {
  $_SESSION['html_case2_attempts'] = 0;
}
if (!isset($_SESSION['html_case2_solved'])) {
  $_SESSION['html_case2_solved'] = false;
}

// ─── Simulated User Profile Data ─────────────────────────────────────────
$profile_data = [
  'username' => 'cyber_student',
  'display_name' => 'Cyber Security Student',
  'bio' => 'Learning web security and penetration testing.',
  'website' => 'https://darkhunter.local',
  'avatar_url' => '/DarkHunter/assets/images/profiles/default-avatar.png',
  'location' => 'Cyber Space',
];

// ─── Vulnerable Profile Update Logic ─────────────────────────────────────
$update_success = false;
$attribute_injected = false;
$flag_triggered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  // VULNERABLE: User input placed inside HTML attributes without encoding
  // Allows breaking out of quotes and injecting event handlers

  $website = $_POST['website'] ?? $profile_data['website'];
  $location = $_POST['location'] ?? $profile_data['location'];
  $display_name = $_POST['display_name'] ?? $profile_data['display_name'];

  // Update profile data (simulated)
  $profile_data['website'] = $website;
  $profile_data['location'] = $location;
  $profile_data['display_name'] = $display_name;
  $update_success = true;

  // Check for attribute injection
  // Pattern: breaking out of quotes with " or ' then adding event handlers
  if (preg_match('/["\']\s*(on\w+|style|href|src)\s*=|["\']\s*>/', $website . $location . $display_name)) {
    $attribute_injected = true;
    $flag_triggered = true;
  }

  // Also check for simple quote breaking
  if (preg_match('/["\'].*[<>]/', $website . $location . $display_name)) {
    $attribute_injected = true;
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case2_solved'] = true;
  $already_solved = true;
  $success_msg = "Great job! You've successfully exploited an HTML Injection in an attribute context. By breaking out of the attribute quotes, you injected event handlers and demonstrated how attribute contexts are vulnerable to HTML injection!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_SESSION['html_case2_attempts']++;
}

$attempts = $_SESSION['html_case2_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile - HTML Injection Case 2 (Attribute Context)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge easy"><i class="fas fa-seedling"></i> Easy Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-user-circle"></i> User Profile Settings</h1>
      <p class="lab-description">Update your public profile information. This easy HTML Injection challenge places user
        input inside HTML attributes. <strong>No attribute encoding applied!</strong> Break out of quotes to inject
        event handlers and malicious links.</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this HTML Injection vulnerability. You can continue exploring, but no additional
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

    <?php if ($update_success): ?>
    <div class="update-alert">
      <i class="fas fa-check-circle"></i>
      <span>Profile updated successfully!</span>
    </div>
    <?php endif; ?>

    <!-- Profile Grid -->
    <div class="profile-grid">

      <!-- Profile Preview (Vulnerable Attribute Context) -->
      <div class="profile-card preview-card">
        <div class="card-header">
          <i class="fas fa-eye"></i>
          <h3>Profile Preview</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Vulnerable</span>
        </div>

        <div class="profile-preview">
          <div class="preview-avatar">
            <img src="<?php echo $profile_data['avatar_url']; ?>" alt="Avatar"
              onerror="this.onerror=null; this.src='/DarkHunter/assets/images/profiles/default-avatar.png';">
          </div>

          <div class="preview-info">
            <!-- VULNERABLE: display_name in attribute context -->
            <h2 class="preview-name" title="<?php echo $profile_data['display_name']; ?>">
              <?php echo $profile_data['display_name']; ?>
            </h2>

            <!-- VULNERABLE: location in attribute context -->
            <p class="preview-location">
              <i class="fas fa-map-marker-alt"></i>
              <span data-location="<?php echo $profile_data['location']; ?>">
                <?php echo $profile_data['location']; ?>
              </span>
            </p>

            <!-- VULNERABLE: website in href attribute -->
            <a href="<?php echo $profile_data['website']; ?>" class="preview-website" target="_blank" rel="noopener">
              <i class="fas fa-globe"></i>
              <?php echo $profile_data['website']; ?>
            </a>

            <p class="preview-bio"><?php echo $profile_data['bio']; ?></p>
          </div>
        </div>

        <?php if ($attribute_injected): ?>
        <div class="injection-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Attribute injection detected! HTML rendered inside attribute context.</span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Profile Edit Form -->
      <div class="profile-card edit-card">
        <div class="card-header">
          <i class="fas fa-edit"></i>
          <h3>Edit Profile</h3>
        </div>

        <form method="POST" action="" class="edit-form" id="edit-form">
          <input type="hidden" name="update_profile" value="1">

          <div class="form-group">
            <label><i class="fas fa-user"></i> Display Name</label>
            <input type="text" name="display_name"
              value="<?php echo htmlspecialchars($profile_data['display_name']); ?>" class="form-input">
            <span class="field-hint">Appears in title attribute</span>
          </div>

          <div class="form-group">
            <label><i class="fas fa-map-marker-alt"></i> Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($profile_data['location']); ?>"
              class="form-input">
            <span class="field-hint">Stored in data-location attribute</span>
          </div>

          <div class="form-group">
            <label><i class="fas fa-globe"></i> Website URL</label>
            <input type="text" name="website" value="<?php echo htmlspecialchars($profile_data['website']); ?>"
              class="form-input">
            <span class="field-hint">Used in href attribute (DANGEROUS!)</span>
          </div>

          <button type="submit" class="btn-update">
            <i class="fas fa-save"></i> Update Profile
          </button>
        </form>
      </div>

      <!-- Attribute Context Analysis -->
      <div class="profile-card analysis-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Attribute Context Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="context-item">
            <span class="context-label">title attribute:</span>
            <code class="context-code">title="<?php echo $profile_data['display_name']; ?>"</code>
            <span class="context-status vuln"><i class="fas fa-times-circle"></i> Unencoded</span>
          </div>
          <div class="context-item">
            <span class="context-label">data-location attribute:</span>
            <code class="context-code">data-location="<?php echo $profile_data['location']; ?>"</code>
            <span class="context-status vuln"><i class="fas fa-times-circle"></i> Unencoded</span>
          </div>
          <div class="context-item">
            <span class="context-label">href attribute:</span>
            <code class="context-code">href="<?php echo $profile_data['website']; ?>"</code>
            <span class="context-status vuln"><i class="fas fa-times-circle"></i> Unencoded</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="profile-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>Attribute Injection Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Event Handler Injection</div>
            <code class="payload-code">" onmouseover="alert('XSS')" "</code>
            <span class="payload-target">Target: any attribute</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Href JavaScript Protocol</div>
            <code class="payload-code">javascript:alert('Hacked')</code>
            <span class="payload-target">Target: href attribute</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Style Injection</div>
            <code class="payload-code">" style="background:red" "</code>
            <span class="payload-target">Target: any quoted attribute</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Tag Breakout</div>
            <?php echo htmlspecialchars('"><script>alert(1)</script>'); ?>
            <span class="payload-target">Target: closing quote + new tag</span>
          </div>
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
          <span>Display Name: <?php echo htmlspecialchars(substr($profile_data['display_name'], 0, 30)); ?></span>
          <span>Location: <?php echo htmlspecialchars(substr($profile_data['location'], 0, 30)); ?></span>
          <span>Injection Detected: <?php echo $attribute_injected ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">The profile fields are placed inside HTML attributes without encoding. Try breaking out of
        the quotes in the <strong>Website URL</strong> field using <code>"</code> followed by an event handler like
        <code>onmouseover="alert(1)"</code>.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">The href attribute is especially dangerous. Try entering
        <code>javascript:alert('Hacked')</code> as the website URL. When someone clicks your profile link, the
        JavaScript executes!
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Use this payload in the <strong>Display Name</strong> field:
        <code>" onmouseover="alert('HTML Injection')" style="color:red" "</code>. This breaks out of the title attribute
        quotes, injects an event handler, and adds inline styling. The injection will be detected when you update your
        profile!
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
    const injectionAlert = document.querySelector('.injection-alert');
    const alreadySolved = document.querySelector('.solved-banner');

    if (injectionAlert && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>