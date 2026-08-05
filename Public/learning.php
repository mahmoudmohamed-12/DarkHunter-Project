<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = false;

$userData = null;
if (isset($_SESSION['user_id'])) {
  $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmtUser->execute([$_SESSION['user_id']]);
  $userData = $stmtUser->fetch();
}

// Learning modules data
$modules = [
  [
    'id' => 'xss',
    'title' => 'Cross-Site Scripting (XSS)',
    'icon' => 'fa-code',
    'color' => 'green',
    'difficulty' => 'Beginner',
    'lessons' => 8,
    'description' => 'Master the art of injecting malicious scripts into web applications. Learn about reflected, stored, and DOM-based XSS attacks.',
    'topics' => ['Reflected XSS', 'Stored XSS', 'DOM XSS', 'Filter Bypass', 'CSP Bypass'],
    'link' => '/DarkHunter/learningBugs/xss-info.php'
  ],
  [
    'id' => 'sqli',
    'title' => 'SQL Injection',
    'icon' => 'fa-database',
    'color' => 'cyan',
    'difficulty' => 'Intermediate',
    'lessons' => 12,
    'description' => 'Deep dive into database exploitation. From basic UNION injections to advanced blind SQLi techniques.',
    'topics' => ['UNION Based', 'Error Based', 'Blind SQLi', 'Time Based', 'WAF Bypass'],
    'link' => '/DarkHunter/learningBugs/sqli-info.php'
  ],
  [
    'id' => 'csrf',
    'title' => 'CSRF Attacks',
    'icon' => 'fa-shield-virus',
    'color' => 'purple',
    'difficulty' => 'Beginner',
    'lessons' => 6,
    'description' => 'Understand Cross-Site Request Forgery vulnerabilities and how to exploit them to perform unauthorized actions.',
    'topics' => ['Token Bypass', 'SameSite Cookies', 'Double Submit', 'Referrer Validation'],
    'link' => '/DarkHunter/learningBugs/csrf-info.php'
  ],
  [
    'id' => 'ssrf',
    'title' => 'Server-Side Request Forgery',
    'icon' => 'fa-server',
    'color' => 'orange',
    'difficulty' => 'Advanced',
    'lessons' => 9,
    'description' => 'Exploit server-side requests to access internal resources, cloud metadata, and perform port scanning.',
    'topics' => ['URL Parsing', 'DNS Rebinding', 'Protocol Smuggling', 'Cloud Metadata'],
    'link' => '/DarkHunter/learningBugs/ssrf-info.php'
  ],
  [
    'id' => 'idor',
    'title' => 'IDOR Vulnerabilities',
    'icon' => 'fa-fingerprint',
    'color' => 'yellow',
    'difficulty' => 'Beginner',
    'lessons' => 5,
    'description' => 'Find and exploit Insecure Direct Object Reference vulnerabilities to access unauthorized data.',
    'topics' => ['ID Enumeration', 'UUID Prediction', 'Mass Assignment', 'Access Control'],
    'link' => '/DarkHunter/learningBugs/idor-info.php'
  ],
  [
    'id' => 'file_upload',
    'title' => 'File Upload Vulnerabilities',
    'icon' => 'fa-file-upload',
    'color' => 'teal',
    'difficulty' => 'Beginner',
    'lessons' => 7,
    'description' => 'Learn how insecure file upload mechanisms can lead to remote code execution and sensitive file exposure.',
    'topics' => ['File Type Bypass', 'MIME Spoofing', 'Double Extension', 'Upload Filters', 'RCE via Upload'],
    'link' => '/DarkHunter/learningBugs/file-upload-info.php'
  ],
  [
    'id' => 'open_redirect',
    'title' => 'Open Redirect',
    'icon' => 'fa-external-link-alt',
    'color' => 'cyan',
    'difficulty' => 'Beginner',
    'lessons' => 4,
    'description' => 'Understand how unvalidated redirects can be abused for phishing and bypassing security controls.',
    'topics' => ['URL Manipulation', 'Whitelist Bypass', 'Phishing Attacks', 'Redirect Chains'],
    'link' => '/DarkHunter/learningBugs/open-redirect-info.php'
  ],
  [
    'id' => 'cache_poisoning',
    'title' => 'Web Cache Poisoning',
    'icon' => 'fa-database',
    'color' => 'orange',
    'difficulty' => 'Advanced',
    'lessons' => 8,
    'description' => 'Exploit caching mechanisms to deliver malicious responses to other users.',
    'topics' => ['Cache Keys', 'Header Injection', 'Web Cache Deception', 'CDN Attacks'],
    'link' => '/DarkHunter/learningBugs/cache-poisoning-info.php'
  ],
  [
    'id' => 'host_header',
    'title' => 'Host Header Injection',
    'icon' => 'fa-network-wired',
    'color' => 'yellow',
    'difficulty' => 'Intermediate',
    'lessons' => 5,
    'description' => 'Manipulate Host headers to perform password reset poisoning, cache poisoning, and SSRF.',
    'topics' => ['Password Reset Poisoning', 'Virtual Host Bypass', 'Cache Poisoning', 'SSRF via Host'],
    'link' => '/DarkHunter/learningBugs/host-header-info.php'
  ],
  [
    'id' => 'oauth',
    'title' => 'OAuth Vulnerabilities',
    'icon' => 'fa-user-shield',
    'color' => 'purple',
    'difficulty' => 'Advanced',
    'lessons' => 9,
    'description' => 'Analyze OAuth flows and exploit misconfigurations to hijack accounts.',
    'topics' => ['Authorization Code Leak', 'State Parameter Bypass', 'Token Theft', 'Open Redirect Abuse'],
    'link' => '/DarkHunter/learningBugs/oauth-info.php'
  ],
  [
    'id' => 'http_smuggling',
    'title' => 'HTTP Request Smuggling',
    'icon' => 'fa-random',
    'color' => 'red',
    'difficulty' => 'Advanced',
    'lessons' => 10,
    'description' => 'Exploit inconsistencies between front-end and back-end servers to smuggle requests.',
    'topics' => ['CL.TE', 'TE.CL', 'Desync Attacks', 'Cache Poisoning', 'Bypass WAF'],
    'link' => '/DarkHunter/learningBugs/http-smuggling-info.php'
  ],
  [
    'id' => 'race_condition',
    'title' => 'Race Conditions',
    'icon' => 'fa-stopwatch',
    'color' => 'pink',
    'difficulty' => 'Intermediate',
    'lessons' => 6,
    'description' => 'Exploit timing issues in applications to bypass business logic and gain unauthorized access.',
    'topics' => ['TOCTOU', 'Parallel Requests', 'Limit Bypass', 'Double Spending'],
    'link' => '/DarkHunter/learningBugs/race-condition-info.php'
  ],
  [
    'id' => 'ssti',
    'title' => 'Server-Side Template Injection',
    'icon' => 'fa-code',
    'color' => 'orange',
    'difficulty' => 'Advanced',
    'lessons' => 8,
    'description' => 'Inject malicious payloads into template engines to achieve remote code execution.',
    'topics' => ['Template Engines', 'Payload Injection', 'Sandbox Escape', 'RCE'],
    'link' => '/DarkHunter/learningBugs/ssti-info.php'
  ],
  [
    'id' => 'html_injection',
    'title' => 'HTML Injection',
    'icon' => 'fa-html5',
    'color' => 'blue',
    'difficulty' => 'Beginner',
    'lessons' => 4,
    'description' => 'Inject arbitrary HTML into web pages to manipulate content and perform phishing attacks.',
    'topics' => ['Reflected HTML', 'Stored HTML', 'UI Redressing', 'Phishing'],
    'link' => '/DarkHunter/learningBugs/html-injection-info.php'
  ],
  [
    'id' => 'cors',
    'title' => 'CORS Misconfiguration',
    'icon' => 'fa-globe',
    'color' => 'green',
    'difficulty' => 'Intermediate',
    'lessons' => 6,
    'description' => 'Exploit misconfigured Cross-Origin Resource Sharing policies to access sensitive data.',
    'topics' => ['Wildcard Origins', 'Credentials Abuse', 'Origin Reflection', 'Preflight Bypass'],
    'link' => '/DarkHunter/learningBugs/cors-info.php'
  ],
  [
    'id' => 'jwt',
    'title' => 'JWT Security',
    'icon' => 'fa-key',
    'color' => 'blue',
    'difficulty' => 'Intermediate',
    'lessons' => 8,
    'description' => 'Attack JSON Web Tokens through signature bypasses, algorithm confusion, and weak secrets.',
    'topics' => ['None Algorithm', 'RS256 to HS256', 'Weak Secrets', 'Kid Injection'],
    'link' => '/DarkHunter/learningBugs/jwt-info.php'
  ],
  [
    'id' => 'lfi',
    'title' => 'Local File Inclusion',
    'icon' => 'fa-folder-open',
    'color' => 'pink',
    'difficulty' => 'Intermediate',
    'lessons' => 7,
    'description' => 'Exploit file inclusion vulnerabilities to read sensitive files and achieve remote code execution.',
    'topics' => ['Path Traversal', 'Null Byte', 'PHP Wrappers', 'Log Poisoning'],
    'link' => '/DarkHunter/learningBugs/lfi-info.php'
  ],
  [
    'id' => 'rce',
    'title' => 'Remote Code Execution',
    'icon' => 'fa-terminal',
    'color' => 'red',
    'difficulty' => 'Advanced',
    'lessons' => 10,
    'description' => 'The ultimate goal. Learn various techniques to execute arbitrary code on target systems.',
    'topics' => ['Command Injection', 'Deserialization', 'Template Injection', 'Type Juggling'],
    'link' => '/DarkHunter/learningBugs/rce-info.php'
  ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning - DarkHunter</title>

  <!-- Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/Public/css/learning.css">


</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include 'login-modal.php'; ?>
  <div class="bg-grid"></div>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <h1 class="page-title">Learning Center</h1>
      <p class="page-subtitle">Master cybersecurity concepts through structured learning paths and hands-on exercises.
      </p>
    </div>

    <!-- Progress Overview -->
    <?php
    $totalModules = count($modules);
    $totalLessons = array_sum(array_column($modules, 'lessons'));

    $completedLabs = 0;
    if (isset($_SESSION['user_id'])) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_progress WHERE user_id = ?");
      $stmt->execute([$_SESSION['user_id']]);
      $completedLabs = (int) $stmt->fetchColumn();
    }

    $progressPercentage = ($totalModules > 0) ? round(($completedLabs / $totalModules) * 100) : 0;
    ?>

    <div class="progress-overview">
      <div class="progress-item">
        <div class="progress-value"><?php echo $totalModules; ?></div>
        <div class="progress-label">Total Modules</div>
      </div>
      <div class="progress-item">
        <div class="progress-value"><?php echo $totalLessons; ?></div>
        <div class="progress-label">Total Lessons</div>
      </div>
      <div class="progress-item">
        <div class="progress-value"><?php echo $completedLabs; ?></div>
        <div class="progress-label">Completed Labs</div>
      </div>
      <div class="progress-item">
        <div class="progress-value"><?php echo $progressPercentage; ?>%</div>
        <div class="progress-label">Progress</div>
      </div>
    </div>

    <!-- Modules Grid -->
    <div class="modules-grid">
      <?php foreach ($modules as $module): ?>
      <div class="module-card <?php echo $module['color']; ?>">
        <div class="module-header">
          <div class="module-icon <?php echo $module['color']; ?>">
            <i class="fas <?php echo $module['icon']; ?>"></i>
          </div>
          <span class="module-difficulty <?php echo strtolower($module['difficulty']); ?>">
            <?php echo $module['difficulty']; ?>
          </span>
        </div>

        <h3 class="module-title"><?php echo $module['title']; ?></h3>
        <p class="module-description"><?php echo $module['description']; ?></p>

        <div class="module-topics">
          <?php foreach ($module['topics'] as $topic): ?>
          <span class="topic-tag"><?php echo $topic; ?></span>
          <?php endforeach; ?>
        </div>

        <div class="module-footer">
          <div class="module-stats">
            <div class="module-stat">
              <i class="fas fa-book-open"></i>
              <span><?php echo $module['lessons']; ?> Lessons</span>
            </div>
            <div class="module-stat">
              <i class="fas fa-clock"></i>
              <span>~<?php echo $module['lessons'] * 15; ?> min</span>
            </div>
          </div>
          <a href="<?php echo $module['link']; ?>" class="start-btn">
            <i class="fas fa-play"></i> Start
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
  // Add hover effects
  document.querySelectorAll('.module-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px)';
    });
    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
    });
  });
  </script>
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