<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$message = '';
$uploaded = false;
$target = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    
    if (!file_exists('uploads/')) {
        mkdir('uploads/', 0777, true);
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $blocked = ['php', 'phtml', 'php3', 'php4', 'php5'];
    
    if (!in_array($ext, $blocked)) {
        $target = 'uploads/' . $filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $message = "Uploaded: " . htmlspecialchars($filename);
            $uploaded = true;

            if (solveLab($pdo, 7)) {
                echo "<script>alert('System Pwned! Double Extension Bypass Detected.');</script>";
            }
        }
    } else {
        $message = "Error: PHP files not allowed!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Double Extension - File Upload Lab 3</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/double-ext.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i>Easy</div>
      <h1 class="lab-title">Double Extension</h1>
      <div class="lab-tags">
        <span class="tag">pathinfo()</span>
        <span class="tag">Last Extension</span>
        <span class="tag">Double Ext</span>
        <span class="tag">Apache</span>
      </div>
      <p>Server checks only last extension. What about double extensions?</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="upload-area">
        <i class="fas fa-file-code upload-icon"></i>
        <h3>Upload Any File</h3>
        <p>PHP extensions are blocked: .php, .phtml, .php3, .php4, .php5</p>
        <input type="file" name="file" id="file" style="display: none;" required>
        <button type="button" class="upload-btn" onclick="document.getElementById('file').click()">Choose File</button>
        <br><br>
        <button type="submit" class="upload-btn" style="background: var(--neon-cyan);">Upload</button>
      </div>
    </form>

    <?php if ($message): ?>
    <?php if ($uploaded): ?>
    <div class="success-msg"><i class="fas fa-check-circle"></i><?php echo $message; ?></div>
    <?php else: ?>
    <div class="error-msg"><i class="fas fa-exclamation-circle"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i>Hint</div>
      <p>Try: <code>shell.php.jpg</code> or <code>shell.php.png</code><br>
        Apache may execute the first extension!<br><br>
        <strong>Note:</strong> This depends on server configuration (mod_php).
      </p>
    </div>
  </div>
</body>

</html>