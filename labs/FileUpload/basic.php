<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$message = '';
$uploaded = false;
$target = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadFolder = 'uploads/';
    if (!file_exists($uploadFolder)) {
        mkdir($uploadFolder, 0777, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $target = $uploadFolder . $fileName; 

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $message = "File uploaded successfully: " . htmlspecialchars($fileName);
        $uploaded = true;

        if (solveLab($pdo, 5)) {
            echo "<script>alert('Congratulations! Lab Solved.');</script>";
        }
    } else {
        $message = "Error: Failed to move file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Basic Upload - File Upload Lab 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/basic.css">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-bolt"></i> Easy</div>
      <h1 class="lab-title">Basic File Upload</h1>
      <div class="lab-tags">
        <span class="tag">No Validation</span>
        <span class="tag">Any Extension</span>
        <span class="tag">PHP Shell</span>
        <span class="tag">Direct Access</span>
      </div>
      <p>Upload any file without restrictions. Perfect for beginners!</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="upload-area">
        <i class="fas fa-cloud-upload-alt upload-icon"></i>
        <h3>Drop your file here</h3>
        <p>or click to browse</p>
        <input type="file" name="file" class="file-input" id="file" required onchange="this.form.submit()">
        <label for="file" class="upload-btn">Choose File</label>
      </div>
    </form>

    <?php if ($message): ?>
    <div class="success-msg">
      <i class="fas fa-check-circle"></i> <?php echo $message; ?>
      <?php if ($uploaded): ?>
      <br><br>
      <a href="<?php echo $target; ?>" class="file-link" target="_blank">
        <i class="fas fa-external-link-alt"></i> Access File: <?php echo htmlspecialchars($target); ?>
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint</div>
      <p>Try uploading a PHP file: <code>shell.php</code> with content:
        <code>&lt;?php system($_GET['cmd']); ?&gt;</code>
      </p>
    </div>
  </div>
</body>

</html>