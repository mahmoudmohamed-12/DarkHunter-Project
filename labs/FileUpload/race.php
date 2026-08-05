<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$message = '';
$uploaded = false;
$target = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $tmp_name = $_FILES['file']['tmp_name'];
    

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $blocked = ['php', 'phtml', 'php3', 'php4', 'php5', 'htaccess'];
    
    if (in_array($ext, $blocked)) {
        $message = "Error: Bad extension!";
    } else {

        $target = 'uploads/' . uniqid() . '_' . $filename;

        if (move_uploaded_file($tmp_name, $target)) {
            

            $content = file_get_contents($target);
            if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {

                unlink($target);
                $message = "Error: PHP code detected! File deleted.";
                $target = '';
            } else {
                $message = "Uploaded: " . htmlspecialchars(basename($target));
                $uploaded = true;

                if (solveLab($pdo, 11)) {
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
  <title>Race Condition - File Upload Lab 7</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/race.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-skull"></i> HARD</div>
      <h1 class="lab-title">Race Condition (TOCTOU)</h1>
      <div class="lab-tags">
        <span class="tag">Race Condition</span>
        <span class="tag">TOCTOU</span>
        <span class="tag">Time-of-Check</span>
        <span class="tag">Time-of-Use</span>
        <span class="tag">Multi-Thread</span>
      </div>
      <p>File is checked AFTER upload. Can you access it before deletion?</p>
    </div>

    <div class="warning-box">
      <i class="fas fa-stopwatch"></i> <strong>Time-Critical!</strong>
      File exists for milliseconds before content check deletes it.
    </div>

    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <div class="upload-area">
        <i class="fas fa-bolt upload-icon"></i>
        <h3>Race Condition Challenge</h3>
        <p>Upload valid extension → File saved → Content checked → Deleted if PHP</p>
        <input type="file" name="file" id="file" style="display: none;" required>
        <button type="button" class="upload-btn" onclick="document.getElementById('file').click()">Choose File</button>
        <br><br>
        <button type="submit" class="upload-btn" style="background: var(--neon-purple);">Upload & Race!</button>
      </div>
    </form>

    <?php if ($message): ?>
    <?php if ($uploaded): ?>
    <div class="success-msg">
      <i class="fas fa-check-circle"></i> <?php echo $message; ?>
      <?php if ($target): ?>
      <br><br>
      <a href="<?php echo $target; ?>" target="_blank" style="color: var(--neon-cyan);">
        <i class="fas fa-external-link-alt"></i> Access File Quickly!
      </a>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $message; ?></div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Race Strategy</div>
      <p><strong>The Gap:</strong></p>
      <code>1. move_uploaded_file()</code> <span style="color:gray;">← File is LIVE now</span><br>
      <code>2. file_get_contents()</code> <span style="color:gray;">← Checking...</span><br>
      <code>3. unlink()</code> <span style="color:gray;">← Gone.</span>

      <p style="margin-top: 15px;"><strong>The Goal:</strong></p>
      <p>Request the file while the server is still at step 2. If your request hits at the right microsecond, the code
        executes before it's deleted!</p>
    </div>
  </div>
</body>

</html>