<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

// 1. تهيئة القيم الافتراضية في السيسشن عشان نتجنب الـ Undefined Key Warning
if (!isset($_SESSION['rxss_easy1_attempts'])) {
    $_SESSION['rxss_easy1_attempts'] = 0;
}
if (!isset($_SESSION['guestbook_comments'])) {
    $_SESSION['guestbook_comments'] = [];
}

// 2. معالجة إرسال التعليق (بافتراض إنك محتاج تسجل المحاولات)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $_SESSION['rxss_easy1_attempts']++; // زيادة عدد المحاولات
    
    $new_comment = [
        'name' => $_POST['name'] ?? 'Anonymous',
        'comment' => $_POST['comment']
    ];
    
    $_SESSION['guestbook_comments'][] = $new_comment;
}

// 3. معالجة نجاح التحدي (لو الـ JavaScript alert بعت طلب GET)
if (isset($_GET['check']) && $_GET['solved'] === '1') {
    $success_msg = "Excellent! You've successfully executed a Reflected XSS attack.";
    // هنا ممكن تنادي دالة solveLab لو عايز تربطها بقاعدة البيانات
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guestbook - RXSS Easy 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/xss-vuln-case-1.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to XSS Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy Difficulty </div>
      <h1 class="lab-title"><i class="fas fa-book"></i> Guestbook</h1>
      <p class="lab-description">Leave a message in our guestbook! This is a beginner-friendly XSS challenge. Try to
        execute a JavaScript alert(1) in your comment. <strong>No filters applied!</strong></p>
    </div>

    <?php if (isset($success_msg)): ?>
    <div class="success-alert">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Completed!</h3>
        <p><?php echo $success_msg; ?></p>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <h2 class="form-title"><i class="fas fa-pen"></i> Write a Comment</h2>
      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Your Name</label>
          <input type="text" name="name" class="form-input" placeholder="Enter your name..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Your Comment</label>
          <textarea name="comment" class="form-textarea" placeholder="Share your thoughts..." required></textarea>
        </div>
        <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Post Comment </button>
      </form>

      <?php if (isset($_SESSION['rxss_easy1_attempts']) && $_SESSION['rxss_easy1_attempts'] >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint</div>
        <div class="hint-text">Try typing HTML tags in your comment. What happens if you type
          <code>&lt;script&gt;alert(1)&lt;/script&gt;</code>? </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="comments-section">
      <h2 class="section-title"><i class="fas fa-comments"></i> Recent Comments</h2>
      <?php if (!empty($_SESSION['guestbook_comments'])): ?>
      <?php 
        $reversed = array_reverse($_SESSION['guestbook_comments']);
        foreach ($reversed as $index => $entry): 
        ?>
      <div class="comment-item">
        <div class="comment-header">
          <div class="comment-avatar"><?php echo strtoupper(substr($entry['name'], 0, 1)); ?></div>
          <div class="comment-meta">
            <div class="comment-author"><?php echo $entry['name']; ?></div>
            <div class="comment-time"><i class="fas fa-clock"></i> Just now </div>
          </div>
        </div>
        <div class="comment-body"><?php echo $entry['comment']; ?></div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-comment-slash"></i>
        <p>No comments yet. Be the first!</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
  window.addEventListener('load', function() {
    const originalAlert = window.alert;
    window.alert = function(msg) {
      if (msg == '1') {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
      return originalAlert.apply(this, arguments);
    };
  });
  </script>
</body>

</html>