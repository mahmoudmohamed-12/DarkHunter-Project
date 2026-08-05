<?php
// XSS Lab - Reflected XSS Training Environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$server = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "darkhunter_db";

try {
    $conn = mysqli_connect($server, $user, $password, $dbname);
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get input
$comment = isset($_GET['comment']) ? $_GET['comment'] : "";
$name = isset($_GET['name']) ? $_GET['name'] : "Anonymous";

$error_message = "";
$success_message = "";
$xss_detected = false;
$payload_analysis = [];

// XSS Detection Patterns - FIXED ARRAY
$xss_patterns = [
    'script_tag' => '/<script[^>]*>.*?<\/script>/is',
    'javascript_protocol' => '/javascript\s*:/i',
    'on_event' => '/\s(on\w+)\s*=/i',
    'img_onerror' => '/<img[^>]+onerror\s*=/i',
    'svg_onload' => '/<svg[^>]+onload\s*=/i',
    'iframe_src' => '/<iframe[^>]+src\s*=/i',
    'alert_confirm_prompt' => '/(alert|confirm|prompt)\s*\(/i',
    'document_cookie' => '/document\.cookie/i',
    'window_location' => '/window\.location/i',
    'eval_expression' => '/eval\s*\(/i',
];

// Analyze payload
function analyzePayload($payload, $patterns) {
    $findings = [];
    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $payload)) {
            $findings[] = [
                'type' => $type,
                'pattern' => $pattern,
                'severity' => 'high'
            ];
        }
    }
    return $findings;
}

// Process submission
if ($comment !== "") {
    $payload_analysis = analyzePayload($comment, $xss_patterns);
    
    if (empty($payload_analysis)) {
        // No XSS detected - this is actually "wrong" for an XSS lab (user should try XSS)
        $error_message = "⚠️ No XSS payload detected! Try using <script> tags or event handlers.";
    } else {
        $xss_detected = true;
        $success_message = "🎯 XSS Payload detected! Vulnerability confirmed.";
        
        // Store in database (intentionally vulnerable - no sanitization)
        $query = "INSERT INTO comments (name, comment, created_at) VALUES ('$name', '$comment', NOW())";
        try {
            mysqli_query($conn, $query);
        } catch (Exception $e) {
            // Silently fail for demo purposes
        }
        
        // Check success and update score
        if (isset($_SESSION['user_id'])) {
            $success = solveLab($pdo, 8);
            if ($success) {
                $success_message .= " +50 pts added!";
            }
        }
    }
}

// Fetch all comments (vulnerable display)
$comments = [];
try {
    $result = mysqli_query($conn, "SELECT * FROM comments ORDER BY created_at DESC LIMIT 10");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $comments[] = $row;
        }
    }
} catch (Exception $e) {
    // Table might not exist yet
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XSS Lab | Cross-Site Scripting Training</title>
  <link rel="stylesheet" type="text/css" href="css/newxsslab.css">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Inter:wght@400;500;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <div class="container">
    <div class="terminal-card">
      <!-- Back Button -->
      <a href="index.php" class="back-link"
        style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: var(--neon-cyan); text-decoration: none; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i> Back to Labs
      </a>

      <div class="header">
        <div class="badge">⚠ Vulnerable Environment</div>
        <h1>XSS_INJECTION_LAB</h1>
        <p class="subtitle">Cross-Site Scripting Training Terminal v1.0</p>
      </div>

      <?php if ($error_message): ?>
      <div class="alert alert-error">
        <span class="alert-icon">⚠️</span>
        <div>
          <strong>Invalid Payload!</strong><br>
          <span style="font-size: 0.85rem; opacity: 0.8;"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($success_message): ?>
      <div class="alert alert-success">
        <span class="alert-icon">🎯</span>
        <div>
          <strong>XSS Detected!</strong><br>
          <span style="font-size: 0.85rem; opacity: 0.8;"><?php echo htmlspecialchars($success_message); ?></span>
        </div>
      </div>

      <div class="analysis-section">
        <div class="analysis-title">// Detected Attack Vectors</div>
        <div class="detected-patterns">
          <?php foreach ($payload_analysis as $finding): ?>
          <span class="pattern-tag"><?php echo str_replace('_', ' ', strtoupper($finding['type'])); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <form method="GET">
        <div class="input-section">
          <div class="input-group">
            <label class="input-label">// Target Name</label>
            <div class="input-wrapper">
              <span class="prompt">></span>
              <input type="text" name="name" placeholder="Enter your hacker alias..."
                value="<?php echo htmlspecialchars($name !== 'Anonymous' ? $name : ''); ?>">
            </div>
          </div>

          <div class="input-group">
            <label class="input-label">// XSS Payload</label>
            <div class="input-wrapper" style="align-items: flex-start;">
              <span class="prompt" style="margin-top: 14px;">$</span>
              <textarea name="comment"
                placeholder="<script>alert('XSS')</script>"><?php echo htmlspecialchars($comment); ?></textarea>
            </div>
          </div>
        </div>

        <button type="submit" class="cyber-btn">Inject_Payload()</button>
      </form>

      <?php if ($comment !== "" && $xss_detected): ?>
      <div class="comments-section">
        <div class="section-header">
          <h3 class="section-title">💉 Injected Payloads</h3>
          <span class="comment-count"><?php echo count($comments) + 1; ?> active</span>
        </div>

        <div class="comments-list">
          <!-- Current injection (vulnerable - not escaped) -->
          <div class="comment-card">
            <div class="comment-header">
              <span class="comment-author"><?php echo $name; ?></span>
              <span class="comment-time">Just now ⚡</span>
            </div>
            <span class="xss-badge">XSS EXECUTED</span>
            <div class="comment-body vulnerable">
              <?php echo $comment; // INTENTIONALLY VULNERABLE - NO ESCAPING ?>
            </div>
          </div>

          <!-- Previous comments -->
          <?php foreach ($comments as $c): ?>
          <div class="comment-card">
            <div class="comment-header">
              <span class="comment-author"><?php echo htmlspecialchars($c['name']); ?></span>
              <span class="comment-time"><?php echo htmlspecialchars($c['created_at']); ?></span>
            </div>
            <div class="comment-body vulnerable">
              <?php echo $c['comment']; // INTENTIONALLY VULNERABLE ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php elseif (empty($comments)): ?>
      <div class="empty-state">
        <div class="empty-icon">💀</div>
        <p>No payloads injected yet. Try executing some XSS!</p>
      </div>
      <?php else: ?>
      <div class="comments-section">
        <div class="section-header">
          <h3 class="section-title">📝 Previous Attempts</h3>
          <span class="comment-count"><?php echo count($comments); ?> total</span>
        </div>
        <div class="comments-list">
          <?php foreach ($comments as $c): ?>
          <div class="comment-card">
            <div class="comment-header">
              <span class="comment-author"><?php echo htmlspecialchars($c['name']); ?></span>
              <span class="comment-time"><?php echo htmlspecialchars($c['created_at']); ?></span>
            </div>
            <div class="comment-body vulnerable">
              <?php echo $c['comment']; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="suggestions">
        <div class="suggestions-title">⚡ Try These Payloads (Click to inject)</div>
        <div class="suggestion-grid">
          <div class="suggestion-item" onclick="injectPayload('<script>alert(String.fromCharCode(88,83,83))</script>')">
            &lt;script&gt;alert(String.fromCharCode(88,83,83))&lt;/script&gt;</div>
          <div class="suggestion-item" onclick="injectPayload('<img src=x onerror=alert(1)>')">&lt;img src=x
            onerror=alert(1)&gt;</div>
          <div class="suggestion-item" onclick='injectPayload("\' -alert(1)-\'")'>'-alert(1)-'</div>
          <div class="suggestion-item" onclick="injectPayload('<svg onload=alert(1)>')">&lt;svg onload=alert(1)&gt;
          </div>
          <div class="suggestion-item" onclick="injectPayload('<iframe src=javascript:alert(1)>')">&lt;iframe
            src=javascript:alert(1)&gt;</div>
          <div class="suggestion-item" onclick="injectPayload('javascript:alert(1)')">javascript:alert(1)</div>
        </div>
      </div>
    </div>
  </div>

  <script>
  function injectPayload(payload) {
    document.querySelector('textarea[name="comment"]').value = payload;
    document.querySelector('input[name="name"]').focus();

    // Visual feedback
    const textarea = document.querySelector('textarea[name="comment"]');
    textarea.style.borderColor = '#ff2a6d';
    textarea.style.boxShadow = '0 0 20px rgba(255, 42, 109, 0.3)';

    setTimeout(() => {
      textarea.style.borderColor = '';
      textarea.style.boxShadow = '';
    }, 1000);
  }

  // Real-time payload detection
  document.querySelector('textarea[name="comment"]').addEventListener('input', function(e) {
    const value = e.target.value;
    const patterns = [{
        regex: /<script[^>]*>.*?<\/script>/i,
        name: 'Script Tag'
      },
      {
        regex: /on\w+\s*=/i,
        name: 'Event Handler'
      },
      {
        regex: /javascript\s*:/i,
        name: 'JS Protocol'
      }
    ];

    let detected = false;
    patterns.forEach(p => {
      if (p.regex.test(value)) detected = true;
    });

    if (detected && value.length > 10) {
      e.target.style.borderColor = '#22c55e';
    }
  });
  </script>

</body>

</html>