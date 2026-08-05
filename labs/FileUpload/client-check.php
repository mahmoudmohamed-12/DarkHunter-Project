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
    
    $target = 'uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $message = "Uploaded: " . htmlspecialchars($filename);
        $uploaded = true;

        if (solveLab($pdo, 6)) {
          echo "<script>alert('Great Job! Client-side bypass successful.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Client Check - File Upload Lab 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/client-check.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i>Easy</div>
      <h1 class="lab-title">Client-Side Validation</h1>
      <div class="lab-tags">
        <span class="tag">Client-Side Only</span>
        <span class="tag">JS Validation</span>
        <span class="tag">Bypass</span>
        <span class="tag">Intercept</span>
      </div>
      <p>JavaScript checks file extension, but server doesn't!</p>
    </div>

    <div class="warning-box">
      <i class="fas fa-info-circle"></i> Only images allowed (jpg, png, gif) - Client Side Check
    </div>

    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <div class="upload-area">
        <i class="fas fa-image upload-icon"></i>
        <h3>Upload Image</h3>
        <p>Accepted: JPG, PNG, GIF</p>
        <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.gif" style="display: none;" required>
        <button type="button" class="upload-btn" onclick="document.getElementById('file').click()">Select Image</button>
        <br><br>
        <button type="submit" class="upload-btn" style="background: var(--neon-cyan);">Upload</button>
      </div>
    </form>

    <?php if ($message): ?>
    <div class="success-msg">
      <i class="fas fa-check-circle"></i> <?php echo $message; ?>
      <?php if ($uploaded): ?>
      <br><br>
      <a href="<?php echo $target; ?>" target="_blank" style="color: var(--neon-cyan);">
        <i class="fas fa-external-link-alt"></i> View File
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i>Hint</div>
      <p>
        1. Select a .jpg file first (to pass JS check)<br>
        2. Use Burp Suite to intercept and change filename to <code>shell.php</code><br>
        3. Or disable JavaScript and upload directly
      </p>
    </div>
  </div>

  <script>
  // الـ Validation ده في المتصفح بس، سهل جداً يتخطاه الهكر بـ Burp Suite
  document.getElementById('file').addEventListener('change', function() {
    const ext = this.value.split('.').pop().toLowerCase();
    if (!['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
      alert('Only images allowed!');
      this.value = '';
    }
  });
  </script>
</body>

</html>