<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['PathTraversal']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['pt_hard2_attempts'])) {
  $_SESSION['pt_hard2_attempts'] = 0;
}
if (!isset($_SESSION['pt_hard2_solved'])) {
  $_SESSION['pt_hard2_solved'] = false;
}
if (!isset($_SESSION['pt_hard2_stage'])) {
  $_SESSION['pt_hard2_stage'] = 1;
}

// ─── Simulated Application: DarkHunter Image Processor ───────────────────
$gallery_images = [
  'logo'      => ['name' => 'Company Logo', 'thumb' => 'thumbs/logo_thumb.jpg'],
  'office'    => ['name' => 'Office Photo', 'thumb' => 'thumbs/office_thumb.jpg'],
  'team'      => ['name' => 'Team Picture', 'thumb' => 'thumbs/team_thumb.jpg'],
  'event'     => ['name' => 'Event Shot', 'thumb' => 'thumbs/event_thumb.jpg'],
];

// ─── Vulnerable Logic: Thumbnail path from user input ────────────────────
$thumb = isset($_GET['thumb']) ? $_GET['thumb'] : 'logo';

// VULNERABLE: Constructs thumbnail path directly
$thumb_path = 'uploads/images/' . $thumb . '_thumb.jpg';

// ─── Stage Logic ─────────────────────────────────────────────────────────
$current_stage = $_SESSION['pt_hard2_stage'];
$stage_messages = [
  1 => "Stage 1: Identify how thumbnail paths are constructed from user input.",
  2 => "Stage 2: Abuse EXIF metadata or SVG xlink:href for file access.",
  3 => "Stage 3: Access arbitrary files through image processing pipeline.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['pt_hard2_solved'];
$exploited = false;

// Detect image processing exploitation
$image_indicators = [
  '../',
  '..\\',
  '..%2f',
  '..%5c',
  '/etc/passwd',
  '/var/log/',
  '.svg',
  '.xml',
  'xlink:href',
  'file://',
  'phar://',
  'php://',
  'data:image',
  'data://',
];

foreach ($image_indicators as $indicator) {
  if (stripos($thumb, $indicator) !== false) {
    $exploited = true;
    break;
  }
}

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['pt_hard2_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['pt_hard2_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've exploited a Path Traversal vulnerability in an image processing pipeline. You manipulated thumbnail paths or abused image format features (EXIF, SVG) to access files outside the upload directory. Image processing is a common but overlooked attack surface!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['thumb'])) {
  $_SESSION['pt_hard2_attempts']++;
}

$attempts = $_SESSION['pt_hard2_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['pt_hard2_stage'] = 2;
  $current_stage = 2;
}
if ($attempts >= 6 && $current_stage < 3) {
  $_SESSION['pt_hard2_stage'] = 3;
  $current_stage = 3;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Image Processor - Path Traversal Hard 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PATH-TRAV-CASE7.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Path Traversal Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-image"></i> DarkHunter Image Processor</h1>
      <p class="lab-description">Process and display uploaded images. The application generates thumbnail paths from
        user input. <strong>Can you abuse image processing to access arbitrary files?</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this image processing vulnerability. You can continue exploring, but no additional
            points will be awarded.</p>
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

    <!-- Stage Tracker -->
    <div class="stage-tracker">
      <div class="stage-header">
        <i class="fas fa-layer-group"></i>
        <span>Attack Chain Progress</span>
      </div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info">
            <span class="stage-title">Reconnaissance</span>
            <span class="stage-desc">Map image pipeline</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info">
            <span class="stage-title">Format Abuse</span>
            <span class="stage-desc">Exploit SVG/EXIF</span>
          </div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info">
            <span class="stage-title">File Access</span>
            <span class="stage-desc">Arbitrary file read</span>
          </div>
        </div>
      </div>
      <div class="stage-message">
        <i class="fas fa-info-circle"></i>
        <span><?php echo $stage_messages[$current_stage]; ?></span>
      </div>
    </div>

    <!-- Image Processor Interface -->
    <div class="processor-card">
      <div class="processor-header">
        <div class="processor-brand">
          <i class="fas fa-camera-retro"></i>
          <span>Image Processor</span>
        </div>
        <div class="processor-format">
          <i class="fas fa-file-image"></i>
          <span>JPG, PNG, SVG</span>
        </div>
      </div>

      <div class="processor-body">
        <!-- Thumbnail Gallery -->
        <div class="thumb-gallery">
          <?php foreach ($gallery_images as $key => $img_data): ?>
            <a href="?thumb=<?php echo $key; ?>" class="thumb-card <?php echo $thumb === $key ? 'active' : ''; ?>">
              <div class="thumb-preview">
                <i class="fas fa-image"></i>
              </div>
              <span class="thumb-name"><?php echo $img_data['name']; ?></span>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Image Viewer -->
        <div class="image-viewer">
          <div class="viewer-header">
            <i class="fas fa-eye"></i>
            <span>Image Preview</span>
            <code class="viewer-path"><?php echo htmlspecialchars($thumb_path); ?></code>
          </div>
          <div class="viewer-body">
            <?php if (isset($gallery_images[$thumb])): ?>
              <div class="viewer-normal">
                <i class="fas fa-image fa-4x"></i>
                <h3><?php echo $gallery_images[$thumb]['name']; ?></h3>
                <p>Thumbnail generated from original</p>
              </div>
            <?php else: ?>
              <div class="viewer-attempt">
                <i class="fas fa-search"></i>
                <p>Attempting to load: <code><?php echo htmlspecialchars($thumb_path); ?></code></p>
                <?php if ($exploited): ?>
                  <div class="viewer-exploit">
                    <i class="fas fa-bug"></i>
                    <span>Image pipeline exploitation detected!</span>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Attack Vectors -->
    <div class="vectors-panel">
      <div class="vectors-header">
        <i class="fas fa-crosshairs"></i>
        <span>Image-Based Attack Vectors</span>
      </div>
      <div class="vectors-body">
        <div class="vector-item">
          <div class="vector-icon"><i class="fas fa-file-code"></i></div>
          <div class="vector-content">
            <span class="vector-title">SVG xlink:href</span>
            <code class="vector-payload">&lt;image xlink:href="../../../etc/passwd"&gt;</code>
          </div>
        </div>
        <div class="vector-item">
          <div class="vector-icon"><i class="fas fa-file-alt"></i></div>
          <div class="vector-content">
            <span class="vector-title">EXIF Metadata</span>
            <code class="vector-payload">MakerNote: &lt;?php system($_GET['cmd']); ?&gt;</code>
          </div>
        </div>
        <div class="vector-item">
          <div class="vector-icon"><i class="fas fa-file-image"></i></div>
          <div class="vector-content">
            <span class="vector-title">Path Traversal</span>
            <code class="vector-payload">?thumb=../../../etc/passwd</code>
          </div>
        </div>
      </div>
    </div>

    <!-- URL Parameter Display -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code>GET /PATH-TRAV-CASE7.php?thumb=<?php echo urlencode($thumb); ?></code>
      </div>
    </div>

    <!-- Vulnerable Code -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-bug"></i>
        <span>Vulnerable Code Snippet</span>
      </div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Thumbnail path from user input
$thumb = $_GET['thumb'];
$thumb_path = 'uploads/images/' . $thumb . '_thumb.jpg';

// No validation! Attacker can use:
// ?thumb=../../../etc/passwd
// Result: uploads/images/../../../etc/passwd_thumb.jpg
// Which resolves to: /etc/passwd_thumb.jpg</pre>
        </div>
        <div class="vuln-note">
          <i class="fas fa-exclamation-triangle"></i>
          <span><strong>Vulnerability:</strong> Image processing pipelines often construct paths dynamically. SVG files
            can reference external resources, and thumbnail generation may read arbitrary files.</span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The thumbnail path is built as <code>uploads/images/{thumb}_thumb.jpg</code>. Try
          <code>?thumb=../../../etc/passwd</code> to escape the uploads directory and read system files.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try also SVG-based attacks. An SVG with
          <code>&lt;image xlink:href="file:///etc/passwd"&gt;</code> might be processed by the image pipeline. Or try
          <code>?thumb=../../Config/db</code> for app configs.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?thumb=../../../etc/passwd</code> for direct path traversal, or
          <code>?thumb=../../Config/db</code> for application files. The image pipeline doesn't validate the thumbnail
          name!
        </div>
      </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="thumb" value="<?php echo htmlspecialchars($thumb); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const thumb = urlParams.get('thumb') || '';
      const patterns = ['../', '..\\', '..%2f', '..%5c', '/etc/passwd', '/var/log/',
        '.svg', '.xml', 'xlink:href', 'file://', 'phar://', 'php://', 'data:image', 'data://'
      ];
      const hasExploit = patterns.some(p => thumb.toLowerCase().includes(p.toLowerCase()));
      if (hasExploit && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>