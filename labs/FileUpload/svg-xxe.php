<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$message = '';
$uploaded = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // السماح بامتداد SVG
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'bmp', 'tiff'];
    
    if (!in_array($ext, $allowed_ext)) {
        $message = "Error: Invalid file extension!";
    } else {
        $target_dir = 'uploads/';
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $target = $target_dir . uniqid() . '_' . $filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            
            // المحاكاة البرمجية لثغرة XXE (هنا السيرفر بيحاول يقرأ ملف الـ SVG)
            if ($ext === 'svg') {
                // تفعيل معالجة الـ XML Entities (دي الثغرة الحقيقية في إعدادات PHP)
                libxml_disable_entity_loader(false); 
                $xml_content = file_get_contents($target);
                
                // محاولة تحميل الـ XML وتجهيزه للعرض
                $dom = new DOMDocument();
                // LIBXML_NOENT هي اللي بتسمح بتبديل الـ Entity بمحتوى الملف
                if ($dom->loadXML($xml_content, LIBXML_NOENT | LIBXML_DTDLOAD)) {
                    $message = "SVG Uploaded & Processed. Content: " . htmlspecialchars($dom->textContent);
                    $uploaded = true;
                    
                    // لو الهكر نجح في سحب داتا (يعني الـ textContent مش فاضي وفيه شغل XXE)
                    if (solveLab($pdo, 12)) {
                         // تم تسجيل الحل بنجاح
                    }
                } else {
                    $message = "Uploaded: " . htmlspecialchars(basename($target));
                    $uploaded = true;
                }
            } else {
                $message = "Uploaded: " . htmlspecialchars(basename($target));
                $uploaded = true;
                
                // في حالة الصور العادية، لو رفعها وسجلناها في اللاب
                if (solveLab($pdo, 12)) {}
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>SVG XXE - File Upload Lab 8</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/svg-xxe.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-skull"></i> HARD</div>
      <h1 class="lab-title">SVG XXE Injection</h1>
      <div class="lab-tags">
        <span class="tag">XXE</span>
        <span class="tag">SVG</span>
        <span class="tag">XML</span>
        <span class="tag">External Entity</span>
      </div>
      <p>Upload SVG files with XML External Entity (XXE) payloads</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="upload-area">
        <i class="fas fa-file-image upload-icon"></i>
        <h3>Upload Image</h3>
        <p>Accepted: JPG, PNG, GIF, SVG, BMP, TIFF</p>
        <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.gif,.svg,.bmp,.tiff" style="display: none;"
          required>
        <button type="button" class="upload-btn" onclick="document.getElementById('file').click()">Choose File</button>
        <br><br>
        <button type="submit" class="upload-btn" style="background: var(--neon-cyan);">Upload</button>
      </div>
    </form>

    <?php if ($message): ?>
    <?php if ($uploaded): ?>
    <div class="success-msg"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
    <?php else: ?>
    <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $message; ?></div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> XXE Payload</div>
      <p>Create an SVG file to read <code>C:/Windows/win.ini</code> (Windows) or <code>/etc/passwd</code> (Linux):</p>
      <pre style="background: #1a1a1a; padding: 10px; border-radius: 5px; color: #00ff9d; font-size: 0.85rem;">
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;!DOCTYPE svg [
  &lt;!ENTITY xxe SYSTEM "file:///C:/Windows/win.ini"&gt;
]&gt;
&lt;svg xmlns="http://www.w3.org/2000/swap"&gt;
  &lt;text x="0" y="20"&gt;&amp;xxe;&lt;/text&gt;
&lt;/svg&gt;</pre>
    </div>
  </div>
</body>

</html>