<?php

/**
 * DarkHunter Cybersecurity Toolkit
 * Matches the exact visual style of labs.php
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

$userData = null;
if (isset($_SESSION['user_id'])) {
  $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmtUser->execute([$_SESSION['user_id']]);
  $userData = $stmtUser->fetch();
}

// ─── Security Headers ─────────────────────────────────────────────
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ─── Helper Functions ─────────────────────────────────────────────
function e(string $text): string
{
  return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitizeInput(string $input): string
{
  return trim(strip_tags($input));
}

function jsonResponse(array $data): void
{
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  exit;
}

// ─── AJAX API Endpoints ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = sanitizeInput($_POST['action']);

  // Base64
  if ($action === 'base64') {
    $mode = sanitizeInput($_POST['mode'] ?? '');
    $text = $_POST['text'] ?? '';
    if ($mode === 'encode') {
      jsonResponse(['success' => true, 'result' => base64_encode($text)]);
    } elseif ($mode === 'decode') {
      $decoded = base64_decode($text, true);
      if ($decoded === false) jsonResponse(['success' => false, 'error' => 'Invalid Base64 string']);
      jsonResponse(['success' => true, 'result' => $decoded]);
    }
    jsonResponse(['success' => false, 'error' => 'Invalid mode']);
  }

  // URL
  if ($action === 'url') {
    $mode = sanitizeInput($_POST['mode'] ?? '');
    $text = $_POST['text'] ?? '';
    if ($mode === 'encode') jsonResponse(['success' => true, 'result' => rawurlencode($text)]);
    if ($mode === 'decode') jsonResponse(['success' => true, 'result' => rawurldecode($text)]);
    jsonResponse(['success' => false, 'error' => 'Invalid mode']);
  }

  // Hash
  if ($action === 'hash') {
    $algo = sanitizeInput($_POST['algo'] ?? '');
    $text = $_POST['text'] ?? '';
    $result = '';
    switch ($algo) {
      case 'md5':
        $result = hash('md5', $text);
        break;
      case 'sha1':
        $result = hash('sha1', $text);
        break;
      case 'sha256':
        $result = hash('sha256', $text);
        break;
      case 'bcrypt':
        $result = password_hash($text, PASSWORD_BCRYPT);
        break;
      default:
        jsonResponse(['success' => false, 'error' => 'Invalid algorithm']);
    }
    jsonResponse(['success' => true, 'result' => $result, 'algo' => $algo]);
  }

  // JWT
  if ($action === 'jwt') {
    $token = sanitizeInput($_POST['token'] ?? '');
    $parts = explode('.', $token);
    if (count($parts) !== 3) jsonResponse(['success' => false, 'error' => 'Invalid JWT format. Expected 3 parts.']);
    $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    if ($header === null || $payload === null) jsonResponse(['success' => false, 'error' => 'Failed to decode JWT. Invalid Base64URL.']);
    jsonResponse([
      'success' => true,
      'header' => json_encode($header, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
      'payload' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
      'signature' => $parts[2]
    ]);
  }

  // JSON
  if ($action === 'json') {
    $jsonText = $_POST['json'] ?? '';
    $decoded = json_decode($jsonText);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
      jsonResponse(['success' => false, 'error' => json_last_error_msg()]);
    }
    jsonResponse(['success' => true, 'result' => json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
  }

  // Regex
  if ($action === 'regex') {
    $pattern = $_POST['pattern'] ?? '';
    $text = $_POST['text'] ?? '';
    $test = @preg_match($pattern, '');
    if ($test === false) jsonResponse(['success' => false, 'error' => 'Invalid regular expression pattern']);
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
    jsonResponse(['success' => true, 'matches' => $matches, 'matchCount' => count($matches)]);
  }

  // Cookie
  if ($action === 'cookie') {
    $cookieStr = $_POST['cookie'] ?? '';
    $pairs = explode(';', $cookieStr);
    $cookies = [];
    foreach ($pairs as $pair) {
      $pair = trim($pair);
      if (empty($pair)) continue;
      $eqPos = strpos($pair, '=');
      if ($eqPos !== false) {
        $cookies[] = ['key' => trim(substr($pair, 0, $eqPos)), 'value' => trim(substr($pair, $eqPos + 1))];
      } else {
        $cookies[] = ['key' => $pair, 'value' => ''];
      }
    }
    jsonResponse(['success' => true, 'cookies' => $cookies]);
  }

  jsonResponse(['success' => false, 'error' => 'Unknown action']);
}

// ─── Server Info ──────────────────────────────────────────────────
$visitorIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$requestHeaders = getallheaders() ?: [];

function parseUserAgent(string $ua): array
{
  $browser = 'Unknown';
  $os = 'Unknown';
  $device = 'Desktop';
  if (stripos($ua, 'Edg/') !== false) $browser = 'Microsoft Edge';
  elseif (stripos($ua, 'Chrome/') !== false && stripos($ua, 'Edg/') === false) $browser = 'Google Chrome';
  elseif (stripos($ua, 'Firefox/') !== false) $browser = 'Mozilla Firefox';
  elseif (stripos($ua, 'Safari/') !== false && stripos($ua, 'Chrome/') === false) $browser = 'Apple Safari';
  elseif (stripos($ua, 'Opera') !== false || stripos($ua, 'OPR/') !== false) $browser = 'Opera';
  elseif (stripos($ua, 'Trident/') !== false || stripos($ua, 'MSIE') !== false) $browser = 'Internet Explorer';

  if (stripos($ua, 'Windows NT 10.0') !== false) $os = 'Windows 10/11';
  elseif (stripos($ua, 'Windows NT 6.3') !== false) $os = 'Windows 8.1';
  elseif (stripos($ua, 'Windows NT 6.2') !== false) $os = 'Windows 8';
  elseif (stripos($ua, 'Windows NT 6.1') !== false) $os = 'Windows 7';
  elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'macOS') !== false) $os = 'macOS';
  elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';
  elseif (stripos($ua, 'Android') !== false) $os = 'Android';
  elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';

  if ((stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false) && stripos($ua, 'Mobile') !== false) $device = 'Mobile';
  elseif (stripos($ua, 'iPad') !== false || stripos($ua, 'Tablet') !== false) $device = 'Tablet';

  return ['browser' => $browser, 'os' => $os, 'device' => $device];
}

$uaParsed = parseUserAgent($userAgent);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Toolkit - DarkHunter</title>

  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/ToolKit/css/toolkit.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>

  <div class="bg-grid"></div>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">
        <i class="fas fa-toolbox"></i>
        Security Toolkit
      </h1>
      <p class="page-subtitle">Essential tools for CTF, reconnaissance, and payload crafting.</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-pill">
        <div class="stat-pill-icon encoder">
          <i class="fas fa-code"></i>
        </div>
        <div class="stat-pill-info">
          <span class="stat-pill-value">10</span>
          <span class="stat-pill-label">Tools</span>
        </div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon hash">
          <i class="fas fa-fingerprint"></i>
        </div>
        <div class="stat-pill-info">
          <span class="stat-pill-value">4</span>
          <span class="stat-pill-label">Hash Algos</span>
        </div>
      </div>
      <div class="stat-pill">
        <div class="stat-pill-icon decoder">
          <i class="fas fa-key"></i>
        </div>
        <div class="stat-pill-info">
          <span class="stat-pill-value">JWT</span>
          <span class="stat-pill-label">Decoder</span>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <div class="controls-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Search tools by name...">
      </div>
      <select class="filter-select" id="categoryFilter">
        <option value="all">All Categories</option>
        <option value="encoder">Encoders</option>
        <option value="decoder">Decoders</option>
        <option value="crypto">Cryptography</option>
        <option value="network">Network</option>
        <option value="parser">Parsers</option>
      </select>
    </div>

    <!-- Tools Grid -->
    <div class="tools-grid" id="toolsGrid">

      <!-- 1. Base64 -->
      <div class="tool-card encoder" data-title="base64 encoder decoder" data-category="encoder">
        <div class="tool-header">
          <span class="tool-category-badge encoder"><i class="fas fa-code"></i> Encoder</span>
          <h3 class="tool-title">Base64 Encoder / Decoder</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Encoding</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Encode text to Base64 or decode Base64 back to plain text instantly.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="b64-input" rows="4"
              placeholder="Enter text to encode or Base64 to decode..."></textarea>
            <div class="tool-actions-row">
              <button class="btn btn-primary" onclick="base64Action('encode')"><i class="fas fa-lock"></i>
                Encode</button>
              <button class="btn btn-success" onclick="base64Action('decode')"><i class="fas fa-lock-open"></i>
                Decode</button>
            </div>
            <div class="tool-output-wrap">
              <textarea class="tool-output" id="b64-output" rows="3" readonly placeholder="Result..."></textarea>
              <div class="tool-output-actions">
                <button class="btn btn-icon" onclick="copyToClipboard('b64-output')" title="Copy"><i
                    class="fas fa-copy"></i></button>
                <button class="btn btn-icon btn-danger" onclick="clearFields('b64')" title="Clear"><i
                    class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. URL Encoder -->
      <div class="tool-card encoder" data-title="url encoder decoder" data-category="encoder">
        <div class="tool-header">
          <span class="tool-category-badge encoder"><i class="fas fa-link"></i> Encoder</span>
          <h3 class="tool-title">URL Encoder / Decoder</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>URL Encoding</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Percent-encode special characters or decode URL-encoded strings.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="url-input" rows="4"
              placeholder="Enter text to URL encode or decode..."></textarea>
            <div class="tool-actions-row">
              <button class="btn btn-primary" onclick="urlAction('encode')"><i class="fas fa-lock"></i> Encode</button>
              <button class="btn btn-success" onclick="urlAction('decode')"><i class="fas fa-lock-open"></i>
                Decode</button>
            </div>
            <div class="tool-output-wrap">
              <textarea class="tool-output" id="url-output" rows="3" readonly placeholder="Result..."></textarea>
              <div class="tool-output-actions">
                <button class="btn btn-icon" onclick="copyToClipboard('url-output')" title="Copy"><i
                    class="fas fa-copy"></i></button>
                <button class="btn btn-icon btn-danger" onclick="clearFields('url')" title="Clear"><i
                    class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Hash Generator -->
      <div class="tool-card crypto" data-title="hash generator md5 sha1 sha256 bcrypt" data-category="crypto">
        <div class="tool-header">
          <span class="tool-category-badge crypto"><i class="fas fa-fingerprint"></i> Crypto</span>
          <h3 class="tool-title">Hash Generator</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Cryptography</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Generate MD5, SHA1, SHA256, and bcrypt hashes from any input text.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="hash-input" rows="3" placeholder="Enter text to hash..."></textarea>
            <div class="algo-select-row">
              <button class="algo-pill active" data-algo="md5" onclick="selectAlgo(this)">MD5</button>
              <button class="algo-pill" data-algo="sha1" onclick="selectAlgo(this)">SHA1</button>
              <button class="algo-pill" data-algo="sha256" onclick="selectAlgo(this)">SHA256</button>
              <button class="algo-pill" data-algo="bcrypt" onclick="selectAlgo(this)">bcrypt</button>
            </div>
            <button class="btn btn-primary w-100 mt-2" onclick="generateHash()"><i class="fas fa-bolt"></i> Generate
              Hash</button>
            <div class="tool-output-wrap mt-2">
              <textarea class="tool-output" id="hash-output" rows="2" readonly
                placeholder="Hash will appear here..."></textarea>
              <div class="tool-output-actions">
                <button class="btn btn-icon" onclick="copyToClipboard('hash-output')" title="Copy"><i
                    class="fas fa-copy"></i></button>
                <button class="btn btn-icon btn-danger" onclick="clearFields('hash')" title="Clear"><i
                    class="fas fa-trash"></i></button>
              </div>
            </div>
            <div class="hash-meta" id="hash-info"></div>
          </div>
        </div>
      </div>

      <!-- 4. JWT Decoder -->
      <div class="tool-card decoder" data-title="jwt decoder json web token" data-category="decoder">
        <div class="tool-header">
          <span class="tool-category-badge decoder"><i class="fas fa-key"></i> Decoder</span>
          <h3 class="tool-title">JWT Decoder</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Token Analysis</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Decode JWT headers and payloads. Inspect token claims and signatures.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="jwt-input" rows="3"
              placeholder="Paste JWT token (eyJhbGciOiJ...)"></textarea>
            <button class="btn btn-primary w-100" onclick="decodeJWT()"><i class="fas fa-unlock"></i> Decode
              JWT</button>
            <div class="jwt-output-grid mt-3">
              <div>
                <label class="jwt-label">Header</label>
                <pre class="jwt-block" id="jwt-header"><code>// Header</code></pre>
              </div>
              <div>
                <label class="jwt-label">Payload</label>
                <pre class="jwt-block" id="jwt-payload"><code>// Payload</code></pre>
              </div>
            </div>
            <label class="jwt-label">Signature</label>
            <div class="jwt-sig" id="jwt-signature">// Signature</div>
            <div class="tool-output-actions mt-2">
              <button class="btn btn-icon btn-danger" onclick="clearFields('jwt')" title="Clear"><i
                  class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. JSON Formatter -->
      <div class="tool-card parser" data-title="json formatter beautifier validator" data-category="parser">
        <div class="tool-header">
          <span class="tool-category-badge parser"><i class="fas fa-brackets-curly"></i> Parser</span>
          <h3 class="tool-title">JSON Formatter</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Data Format</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Beautify and validate JSON. Syntax highlighting with error reporting.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="json-input" rows="5"
              placeholder='{"key": "value", "array": [1,2,3]}'></textarea>
            <button class="btn btn-primary w-100" onclick="formatJSON()"><i class="fas fa-wand-magic-sparkles"></i>
              Format JSON</button>
            <div class="tool-output-wrap mt-2">
              <pre class="json-block" id="json-output"><code>// Formatted JSON will appear here</code></pre>
              <div class="tool-output-actions">
                <button class="btn btn-icon" onclick="copyElementText('json-output')" title="Copy"><i
                    class="fas fa-copy"></i></button>
                <button class="btn btn-icon btn-danger" onclick="clearFields('json')" title="Clear"><i
                    class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. HTTP Header Viewer -->
      <div class="tool-card network" data-title="http header viewer request headers" data-category="network">
        <div class="tool-header">
          <span class="tool-category-badge network"><i class="fas fa-globe"></i> Network</span>
          <h3 class="tool-title">HTTP Header Viewer</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Reconnaissance</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Inspect all incoming HTTP request headers and connection metadata.</p>
          <div class="info-grid-3">
            <div class="info-box">
              <div class="info-box-icon"><i class="fas fa-globe"></i></div>
              <div class="info-box-label">Your IP</div>
              <div class="info-box-value"><?php echo e($visitorIP); ?></div>
            </div>
            <div class="info-box">
              <div class="info-box-icon"><i class="fas fa-paper-plane"></i></div>
              <div class="info-box-label">Method</div>
              <div class="info-box-value"><?php echo e($requestMethod); ?></div>
            </div>
            <div class="info-box">
              <div class="info-box-icon"><i class="fas fa-user-secret"></i></div>
              <div class="info-box-label">User Agent</div>
              <div class="info-box-value text-truncate" title="<?php echo e($userAgent); ?>">
                <?php echo e($userAgent); ?></div>
            </div>
          </div>
          <div class="headers-table-wrap mt-3">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Header</th>
                  <th>Value</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($requestHeaders as $name => $value): ?>
                  <tr>
                    <td class="data-key"><?php echo e($name); ?></td>
                    <td class="data-val"><?php echo e($value); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 7. User-Agent Parser -->
      <div class="tool-card parser" data-title="user agent parser browser os device" data-category="parser">
        <div class="tool-header">
          <span class="tool-category-badge parser"><i class="fas fa-laptop"></i> Parser</span>
          <h3 class="tool-title">User-Agent Parser</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Fingerprinting</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Parse User-Agent strings to detect browser, OS, and device type.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="ua-input" rows="3"><?php echo e($userAgent); ?></textarea>
            <button class="btn btn-primary w-100" onclick="parseUA()"><i class="fas fa-magnifying-glass"></i> Parse
              User-Agent</button>
            <div class="info-grid-3 mt-3">
              <div class="info-box">
                <div class="info-box-icon"><i class="fab fa-chrome"></i></div>
                <div class="info-box-label">Browser</div>
                <div class="info-box-value" id="ua-browser"><?php echo e($uaParsed['browser']); ?></div>
              </div>
              <div class="info-box">
                <div class="info-box-icon"><i class="fab fa-windows"></i></div>
                <div class="info-box-label">OS</div>
                <div class="info-box-value" id="ua-os"><?php echo e($uaParsed['os']); ?></div>
              </div>
              <div class="info-box">
                <div class="info-box-icon"><i class="fas fa-mobile-screen"></i></div>
                <div class="info-box-label">Device</div>
                <div class="info-box-value" id="ua-device"><?php echo e($uaParsed['device']); ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 8. IP Lookup -->
      <div class="tool-card network" data-title="ip lookup address connection" data-category="network">
        <div class="tool-header">
          <span class="tool-category-badge network"><i class="fas fa-network-wired"></i> Network</span>
          <h3 class="tool-title">IP Lookup</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Reconnaissance</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">View your current IP address and basic connection information.</p>
          <div class="ip-big text-center">
            <div class="ip-big-label">Your IP Address</div>
            <div class="ip-big-value"><?php echo e($visitorIP); ?></div>
            <span class="badge-online"><i class="fas fa-check-circle"></i> Connected</span>
          </div>
          <div class="detail-list mt-3">
            <div class="detail-row"><span class="detail-key">Request Method:</span><span
                class="detail-val"><?php echo e($requestMethod); ?></span></div>
            <div class="detail-row"><span class="detail-key">Protocol:</span><span
                class="detail-val"><?php echo e($_SERVER['SERVER_PROTOCOL'] ?? 'Unknown'); ?></span></div>
            <div class="detail-row"><span class="detail-key">Server Port:</span><span
                class="detail-val"><?php echo e($_SERVER['SERVER_PORT'] ?? 'Unknown'); ?></span></div>
            <div class="detail-row"><span class="detail-key">HTTPS:</span><span
                class="detail-val"><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Yes' : 'No'; ?></span>
            </div>
            <div class="detail-row"><span class="detail-key">Server Software:</span><span
                class="detail-val"><?php echo e($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></span></div>
            <div class="detail-row"><span class="detail-key">Request Time:</span><span
                class="detail-val"><?php echo date('Y-m-d H:i:s T'); ?></span></div>
          </div>
        </div>
      </div>

      <!-- 9. Regex Tester -->
      <div class="tool-card parser" data-title="regex tester regular expression" data-category="parser">
        <div class="tool-header">
          <span class="tool-category-badge parser"><i class="fas fa-spell-check"></i> Parser</span>
          <h3 class="tool-title">Regex Tester</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Pattern Matching</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Test regular expressions against sample text with live match results.</p>
          <div class="tool-input-group">
            <label class="input-label">Pattern</label>
            <div class="regex-wrap">
              <span class="regex-slash">/</span>
              <input type="text" class="tool-input regex-input" id="regex-pattern" placeholder="[a-zA-Z0-9]+">
              <span class="regex-slash">/</span>
            </div>
            <label class="input-label mt-2">Test Text</label>
            <textarea class="tool-input" id="regex-text" rows="4" placeholder="Enter text to test..."></textarea>
            <div class="tool-actions-row mt-2">
              <button class="btn btn-primary" onclick="testRegex()"><i class="fas fa-play"></i> Test</button>
              <button class="btn btn-secondary" onclick="clearFields('regex')"><i class="fas fa-trash"></i>
                Clear</button>
            </div>
            <label class="input-label mt-3">Matches</label>
            <div class="matches-box" id="regex-matches">
              <div class="matches-empty">Run a test to see matches</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 10. Cookie Parser -->
      <div class="tool-card parser" data-title="cookie parser key value" data-category="parser">
        <div class="tool-header">
          <span class="tool-category-badge parser"><i class="fas fa-cookie-bite"></i> Parser</span>
          <h3 class="tool-title">Cookie Parser</h3>
          <div class="tool-category"><i class="fas fa-tag"></i><span>Session Analysis</span></div>
        </div>
        <div class="tool-body">
          <p class="tool-description">Parse raw cookie strings into clean key-value pair tables.</p>
          <div class="tool-input-group">
            <textarea class="tool-input" id="cookie-input" rows="3"
              placeholder="session_id=abc123; user=admin; theme=dark"></textarea>
            <button class="btn btn-primary w-100" onclick="parseCookie()"><i class="fas fa-cookie"></i> Parse
              Cookies</button>
            <div class="cookie-table-wrap mt-3">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Key</th>
                    <th>Value</th>
                  </tr>
                </thead>
                <tbody id="cookie-tbody">
                  <tr>
                    <td colspan="2" class="text-muted text-center">Parsed cookies will appear here</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="tool-output-actions mt-2">
              <button class="btn btn-icon btn-danger" onclick="clearFields('cookie')" title="Clear"><i
                  class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Toast Container -->
  <div id="toastContainer" class="toast-container"></div>

  <script src="/DarkHunter/ToolKit/js/toolkit.js"></script>
  <?php if (!$isLoggedIn): ?>
    <script>
      window.addEventListener("load", function() {
        if (typeof LoginModal !== "undefined") {
          LoginModal.show();
        }
      });
    </script>
  <?php endif; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/footer.php'; ?>
</body>

</html>