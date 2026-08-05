<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case8_attempts'])) {
  $_SESSION['priv_case8_attempts'] = 0;
}
if (!isset($_SESSION['priv_case8_solved'])) {
  $_SESSION['priv_case8_solved'] = false;
}

// ─── Simulated Multi-Tenant API Keys ─────────────────────────────────────
$tenants = [
  ['id' => 'tenant_001', 'name' => 'Acme Corp', 'api_key' => 'ak_live_51Hx9m2K9QZqV8Np'],
  ['id' => 'tenant_002', 'name' => 'TechStart Inc', 'api_key' => 'ak_live_37Bx7p4M8RwL2Kq'],
  ['id' => 'tenant_003', 'name' => 'GlobalSys', 'api_key' => 'ak_live_82Jm3n5P7TyQ9Wx'],
  ['id' => 'tenant_admin', 'name' => 'DarkHunter Admin', 'api_key' => 'ak_live_admin_master_key_001'],
];

// VULNERABLE: Predictable API key pattern
$predictable_keys = [
  'ak_live_51Hx9m2K9QZqV8Np',
  'ak_live_51Hx9m2K9QZqV8Nq', // Next in sequence
  'ak_live_51Hx9m2K9QZqV8Nr',
  'ak_live_admin_master_key_001',
  'ak_live_admin_master_key_002',
];

// ─── Handle API Key Testing ──────────────────────────────────────────────
$key_tested = false;
$key_valid = false;
$cross_tenant = false;
$admin_access = false;
$flag_triggered = false;
$accessed_tenant = null;
$api_response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_key'])) {
  $tested_key = $_POST['api_key'] ?? '';
  $target_tenant = $_POST['target_tenant'] ?? '';
  $key_tested = true;

  // Check if key exists in any tenant
  foreach ($tenants as $tenant) {
    if ($tenant['api_key'] === $tested_key) {
      $key_valid = true;
      $accessed_tenant = $tenant;

      // VULNERABLE: No tenant isolation - key works across tenants!
      if ($target_tenant && $target_tenant !== $tenant['id']) {
        $cross_tenant = true;
      }

      // Check for admin key
      if ($tenant['id'] === 'tenant_admin' || strpos($tested_key, 'admin') !== false) {
        $admin_access = true;
        $flag_triggered = true;
      }

      break;
    }
  }

  // VULNERABLE: Predictable key pattern allows guessing
  if (!$key_valid && preg_match('/^ak_live_[a-z0-9_]+$/', $tested_key)) {
    // Accept any key matching the pattern (weak validation)
    $key_valid = true;
    $accessed_tenant = ['id' => 'tenant_unknown', 'name' => 'Unknown Tenant', 'api_key' => $tested_key];

    if (strpos($tested_key, 'admin') !== false) {
      $admin_access = true;
      $flag_triggered = true;
    }
  }

  // Simulate API response
  if ($key_valid) {
    $api_response = [
      'status' => 'success',
      'tenant' => $accessed_tenant['name'],
      'tenant_id' => $accessed_tenant['id'],
      'access_level' => $admin_access ? 'admin' : 'user',
      'endpoints' => [
        '/api/v1/users',
        '/api/v1/data',
        '/api/v1/admin/settings' => $admin_access ? 'accessible' : 'forbidden',
        '/api/v1/admin/tenants' => $admin_access ? 'accessible' : 'forbidden'
      ],
      'cross_tenant_access' => $cross_tenant
    ];
  } else {
    $api_response = [
      'status' => 'error',
      'message' => 'Invalid API key'
    ];
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case8_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case8_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case8_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've successfully exploited an API Key Reuse vulnerability. By reusing or predicting API keys across different tenant contexts, you accessed admin endpoints belonging to other organizations and escalated your privileges!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_key'])) {
  $_SESSION['priv_case8_attempts']++;
}

$attempts = $_SESSION['priv_case8_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Gateway - PrivEsc Case 8 (API Key Reuse)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-key"></i> API Gateway</h1>
      <p class="lab-description">Test API keys against our multi-tenant system. This hard Privilege Escalation
        challenge has <strong>cross-tenant API key reuse</strong> and predictable key patterns.
        <strong>Access other organizations' admin endpoints!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this API Key Reuse vulnerability. You can continue exploring, but no additional
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

    <!-- API Grid -->
    <div class="api-grid">

      <!-- Key Tester -->
      <div class="api-card tester-card">
        <div class="card-header">
          <i class="fas fa-vial"></i>
          <h3>API Key Tester</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Cross-Tenant</span>
        </div>

        <form method="POST" action="" class="api-form" id="api-form">
          <input type="hidden" name="test_key" value="1">

          <div class="form-group">
            <label><i class="fas fa-key"></i> API Key</label>
            <input type="text" name="api_key" placeholder="Enter API key..." class="form-input"
              value="<?php echo isset($_POST['api_key']) ? htmlspecialchars($_POST['api_key']) : ''; ?>">
            <span class="field-hint">Try: ak_live_51Hx9m2K9QZqV8Np or ak_live_admin_master_key_001</span>
          </div>

          <div class="form-group">
            <label><i class="fas fa-building"></i> Target Tenant</label>
            <select name="target_tenant" class="form-select">
              <option value="">Select tenant...</option>
              <?php foreach ($tenants as $tenant): ?>
                <option value="<?php echo $tenant['id']; ?>"
                  <?php echo (isset($_POST['target_tenant']) && $_POST['target_tenant'] === $tenant['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($tenant['name']); ?> (<?php echo $tenant['id']; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn-test">
            <i class="fas fa-plug"></i> Test Key
          </button>
        </form>
      </div>

      <!-- API Response -->
      <div class="api-card response-card">
        <div class="card-header">
          <i class="fas fa-reply"></i>
          <h3>API Response</h3>
        </div>

        <?php if ($key_tested && $api_response): ?>
          <div class="response-content">
            <pre class="response-code"><code><?php echo json_encode($api_response, JSON_PRETTY_PRINT); ?></code></pre>
          </div>

          <?php if ($cross_tenant): ?>
            <div class="escalation-alert">
              <i class="fas fa-exclamation-triangle"></i>
              <span>Cross-tenant access detected! Key works across organizations!</span>
            </div>
          <?php endif; ?>

          <?php if ($admin_access): ?>
            <div class="escalation-alert admin-alert">
              <i class="fas fa-crown"></i>
              <span>Admin access granted! Full system control achieved!</span>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="response-placeholder">
            <i class="fas fa-plug"></i>
            <p>Test an API key to see the response</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Tenant List -->
      <div class="api-card tenants-card">
        <div class="card-header">
          <i class="fas fa-building"></i>
          <h3>Known Tenants</h3>
        </div>
        <div class="tenants-list">
          <?php foreach ($tenants as $tenant): ?>
            <div class="tenant-item <?php echo $tenant['id'] === 'tenant_admin' ? 'admin-tenant' : ''; ?>">
              <div class="tenant-icon">
                <i class="fas fa-<?php echo $tenant['id'] === 'tenant_admin' ? 'shield-alt' : 'building'; ?>"></i>
              </div>
              <div class="tenant-info">
                <span class="tenant-name"><?php echo htmlspecialchars($tenant['name']); ?></span>
                <code class="tenant-id"><?php echo $tenant['id']; ?></code>
              </div>
              <?php if ($tenant['id'] === 'tenant_admin'): ?>
                <span class="admin-badge">ADMIN</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Key Analysis -->
      <div class="api-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Key Validation Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Key Pattern:</span>
            <code class="analysis-code">ak_live_[alphanumeric] // Predictable!</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Predictable</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Tenant Isolation:</span>
            <code class="analysis-code">// No tenant validation</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Cross-Tenant Check:</span>
            <code class="analysis-code">// Keys work globally</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Disabled</span>
          </div>
        </div>
      </div>

      <!-- Attack Vectors -->
      <div class="api-card vectors-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>API Key Attack Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Key Reuse Across Tenants</div>
            <code class="vector-code">Use tenant_001 key on tenant_002</code>
            <span class="vector-desc">Same key works for different organizations</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Predictable Key Guessing</div>
            <code class="vector-code">ak_live_51Hx9m2K9QZqV8N[q,r,s...]</code>
            <span class="vector-desc">Brute force sequential key patterns</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Admin Key Enumeration</div>
            <code class="vector-code">ak_live_admin_master_key_001</code>
            <span class="vector-desc">Guess admin keys from naming pattern</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Cross-Tenant Data Access</div>
            <code class="vector-code">GET /api/v1/admin/tenants</code>
            <span class="vector-desc">Access all tenant data with one key</span>
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
          <span>Key Tested: <?php echo $key_tested ? 'YES' : 'NO'; ?></span>
          <span>Key Valid: <?php echo $key_valid ? 'YES' : 'NO'; ?></span>
          <span>Cross-Tenant: <?php echo $cross_tenant ? 'YES' : 'NO'; ?></span>
          <span>Admin Access: <?php echo $admin_access ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The API keys follow a predictable pattern. Try guessing the next key in sequence or look
          for admin keys with "admin" in the name.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">The system does NOT check if an API key belongs to the tenant you're accessing. Try using
          one tenant's key to access another tenant's data, especially the admin tenant!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Enter <code>ak_live_admin_master_key_001</code> as the API key and select any tenant.
          This admin master key works across ALL tenants without validation, giving you full system access!</div>
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
      const adminAlert = document.querySelector('.admin-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (adminAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>