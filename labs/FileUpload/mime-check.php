<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$message = '';
$uploaded = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['file']['type']; 
    
    if (in_array($file_type, $allowed_types)) {
        $target_dir = 'uploads/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target = $target_dir . $_FILES['file']['name'];
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $message = "Uploaded: " . htmlspecialchars($_FILES['file']['name']);
            $uploaded = true;

            if (solveLab($pdo, 8)) {
                echo "<script>alert('Mission Accomplished! Content-Type Spoofing Success.');</script>";
            }
        }
    } else {
        $message = "Error: Invalid file type. Only images allowed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MIME Check - File Upload Lab 4</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/mime-check.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i>Easy</div>
      <h1 class="lab-title">MIME Type Validation</h1>
      <div class="lab-tags">
        <span class="tag">MIME Type</span>
        <span class="tag">Content-Type</span>
        <span class="tag">Burp Suite</span>
        <span class="tag">Intercept</span>
      </div>
      <p>Server checks MIME type only. Can you spoof it?</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="upload-area">
        <i class="fas fa-file-upload upload-icon"></i>
        <h3>Upload File</h3>
        <p>Server validates Content-Type header</p>
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
      <p>
        1. Create file: <code>shell.php</code> with PHP code<br>
        2. Upload and intercept with Burp Suite<br>
        3. Change <code>Content-Type: application/x-php</code> to <code>Content-Type: image/jpeg</code><br>
        4. Forward the request!
      </p>
    </div>
  </div>
</body>

</html>