<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

// Path Traversal Labs Data
$labs = [
  [
    'id' => 1,
    'name' => 'Path Traversal Case 1 - Basic Dot-Dot',
    'file' => 'Labs/PATH-TRAV-CASE1.php',
    'description' => 'Unsanitized filename parameter. Use ../ sequences to traverse directories and read arbitrary files outside the web root.',
    'difficulty' => 'Easy',
    'icon' => '📁',
    'color' => 'orange',
    'tags' => ['../', 'Basic', 'File Read', 'Directory Escape'],
    'completed' => false
  ],
  [
    'id' => 2,
    'name' => 'Path Traversal Case 2 - Absolute Path',
    'file' => 'Labs/PATH-TRAV-CASE2.php',
    'description' => 'Bypass relative path checks with absolute paths. Directly reference system files using /etc/passwd or C:\\Windows\\win.ini patterns.',
    'difficulty' => 'Easy',
    'icon' => '🛤️',
    'color' => 'orange',
    'tags' => ['Absolute Path', 'Windows', 'Linux', 'System Files'],
    'completed' => false
  ],
  [
    'id' => 3,
    'name' => 'Path Traversal Case 3 - Double Encoding',
    'file' => 'Labs/PATH-TRAV-CASE3.php',
    'description' => 'URL decode bypass. Use double-encoded sequences like %252e%252e%252f to bypass single-layer URL decoding filters.',
    'difficulty' => 'Medium',
    'icon' => '🔀',
    'color' => 'coral',
    'tags' => ['Double Encode', 'URL Decode', '%252f', 'Filter Bypass'],
    'completed' => false
  ],
  [
    'id' => 4,
    'name' => 'Path Traversal Case 4 - Unicode Bypass',
    'file' => 'Labs/PATH-TRAV-CASE4.php',
    'description' => 'Unicode normalization bypass. Exploit overlong UTF-8 encoding or Unicode equivalence to bypass path sanitization.',
    'difficulty' => 'Medium',
    'icon' => '🌐',
    'color' => 'coral',
    'tags' => ['Unicode', 'UTF-8', 'Normalization', 'Overlong'],
    'completed' => false
  ],
  [
    'id' => 5,
    'name' => 'Path Traversal Case 5 - Null Byte Truncation',
    'file' => 'Labs/PATH-TRAV-CASE5.php',
    'description' => 'Null byte injection to truncate appended extensions. Bypass forced .txt or .pdf suffixes with %00 in the path.',
    'difficulty' => 'Medium',
    'icon' => '⛔',
    'color' => 'coral',
    'tags' => ['Null Byte', '%00', 'Truncation', 'Extension Bypass'],
    'completed' => false
  ],
  [
    'id' => 6,
    'name' => 'Path Traversal Case 6 - Archive Extraction',
    'file' => 'Labs/PATH-TRAV-CASE6.php',
    'description' => 'ZipSlip vulnerability in archive extraction. Craft malicious ZIP entries with ../ paths to overwrite arbitrary files during extraction.',
    'difficulty' => 'Hard',
    'icon' => '📦',
    'color' => 'crimson',
    'tags' => ['ZipSlip', 'Archive', 'File Overwrite', 'Zip Entry'],
    'completed' => false
  ],
  [
    'id' => 7,
    'name' => 'Path Traversal Case 7 - Image Processing',
    'file' => 'Labs/PATH-TRAV-CASE7.php',
    'description' => 'Path traversal in image processing pipelines. Abuse EXIF metadata, image paths in thumbnails, or SVG xlink:href for file access.',
    'difficulty' => 'Hard',
    'icon' => '🖼️',
    'color' => 'crimson',
    'tags' => ['Image Processing', 'EXIF', 'SVG', 'Thumbnail'],
    'completed' => false
  ],
  [
    'id' => 8,
    'name' => 'Path Traversal Case 8 - SSRF Chain',
    'file' => 'Labs/PATH-TRAV-CASE8.php',
    'description' => 'Chain path traversal with SSRF. Abuse file:// protocol handlers, jar:// URLs, or gopher:// to access internal services and files.',
    'difficulty' => 'Hard',
    'icon' => '🔗',
    'color' => 'crimson',
    'tags' => ['SSRF Chain', 'file://', 'gopher://', 'Internal Access'],
    'completed' => false
  ]
];

$total_labs = count($labs);
$easy_count = count(array_filter($labs, fn($l) => $l['difficulty'] === 'Easy'));
$medium_count = count(array_filter($labs, fn($l) => $l['difficulty'] === 'Medium'));
$hard_count = count(array_filter($labs, fn($l) => $l['difficulty'] === 'Hard'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Path Traversal Labs | DarkHunter</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/labs/PathTraversal/css/index.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <div class="bg-grid"></div>
  <div class="tunnel-container">
    <div class="tunnel-ring"></div>
    <div class="tunnel-ring"></div>
    <div class="tunnel-ring"></div>
    <div class="tunnel-ring"></div>
  </div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div id="particles" class="particles"></div>

  <div class="back-nav">
    <a href="/DarkHunter/Public/index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
  </div>

  <div class="container">
    <div class="header">
      <div class="page-logo">
        <div class="portal-outer"></div>
        <div class="portal-inner"></div>
        <span class="logo-icon">🗂️</span>
      </div>
      <h1>Path Traversal Labs</h1>
      <p>Master directory traversal vulnerabilities. From basic dot-dot-slash sequences to advanced ZipSlip attacks,
        Unicode normalization bypasses, and SSRF chaining for internal file access.</p>
      <div class="header-glow"></div>
    </div>

    <div class="stats-bar">
      <div class="stat-card" data-tilt>
        <div class="stat-icon">🎯</div>
        <div class="stat-value" data-count="<?php echo $total_labs; ?>">0</div>
        <div class="stat-label">Total Labs</div>
      </div>
      <div class="stat-card" data-tilt>
        <div class="stat-icon">🟠</div>
        <div class="stat-value" data-count="<?php echo $easy_count; ?>">0</div>
        <div class="stat-label">Easy</div>
      </div>
      <div class="stat-card" data-tilt>
        <div class="stat-icon">🔴</div>
        <div class="stat-value" data-count="<?php echo $medium_count; ?>">0</div>
        <div class="stat-label">Medium</div>
      </div>
      <div class="stat-card" data-tilt>
        <div class="stat-icon">⚫</div>
        <div class="stat-value" data-count="<?php echo $hard_count; ?>">0</div>
        <div class="stat-label">Hard</div>
      </div>
    </div>

    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterLabs('all')"><i class="fas fa-th"></i> All Labs</button>
      <button class="filter-btn" onclick="filterLabs('easy')"><i class="fas fa-seedling"></i> Easy</button>
      <button class="filter-btn" onclick="filterLabs('medium')"><i class="fas fa-bolt"></i> Medium</button>
      <button class="filter-btn" onclick="filterLabs('hard')"><i class="fas fa-fire"></i> Hard</button>
    </div>

    <div class="labs-grid" id="labsGrid">
      <?php foreach ($labs as $i => $lab):
        $difficulty_lower = strtolower($lab['difficulty']);
        $color_class = $lab['color'];
      ?>
      <a href="<?php echo $lab['file']; ?>" class="lab-card <?php echo $color_class; ?>"
        data-difficulty="<?php echo $difficulty_lower; ?>" style="--delay: <?php echo $i * 0.15; ?>s">
        <div class="card-shine"></div>
        <div class="card-border-glow"></div>
        <div class="lab-header-row">
          <div class="lab-icon"><?php echo $lab['icon']; ?></div>
          <span class="lab-difficulty"><?php echo $lab['difficulty']; ?></span>
        </div>
        <h3 class="lab-title"><?php echo $lab['name']; ?></h3>
        <p class="lab-description"><?php echo $lab['description']; ?></p>
        <div class="lab-tags">
          <?php foreach ($lab['tags'] as $tag): ?>
          <span class="lab-tag"><i class="fas fa-tag"></i> <?php echo $tag; ?></span>
          <?php endforeach; ?>
        </div>
        <div class="lab-footer">
          <div class="lab-status">
            <span class="status-dot <?php echo $lab['completed'] ? 'completed' : ''; ?>"></span>
            <span><?php echo $lab['completed'] ? 'Completed' : 'Not Started'; ?></span>
          </div>
          <button class="launch-btn"><span class="btn-text">Launch</span><span class="btn-arrow">→</span>
            <div class="btn-glow"></div>
          </button>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="footer">
      <div class="footer-glow"></div>
      <p><i class="fas fa-shield-halved"></i> For educational purposes only. Use responsibly in authorized environments.
      </p>
      <p class="footer-credit">Built with <span class="heart">💜</span> for cybersecurity learners</p>
    </div>
  </div>

  <script>
  function filterLabs(difficulty) {
    const cards = document.querySelectorAll('.lab-card');
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(btn => {
      btn.classList.remove('active');
      if (btn.textContent.toLowerCase().includes(difficulty) || (difficulty === 'all' && btn.textContent.includes(
          'All'))) {
        btn.classList.add('active');
      }
    });
    cards.forEach((card, index) => {
      if (difficulty === 'all' || card.dataset.difficulty === difficulty) {
        card.style.display = 'block';
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0) scale(1)';
        }, index * 80);
      } else {
        card.style.opacity = '0';
        card.style.transform = 'translateY(40px) scale(0.9)';
        setTimeout(() => {
          card.style.display = 'none';
        }, 400);
      }
    });
  }

  function animateCounters() {
    document.querySelectorAll('.stat-value').forEach(counter => {
      const target = parseInt(counter.getAttribute('data-count'));
      const increment = target / 75;
      let current = 0;
      const update = () => {
        current += increment;
        if (current < target) {
          counter.textContent = Math.ceil(current);
          requestAnimationFrame(update);
        } else {
          counter.textContent = target;
        }
      };
      update();
    });
  }

  function createParticles() {
    const container = document.getElementById('particles');
    const colors = ['#ff6b35', '#ff8c42', '#ff3c38', '#ffa500', '#ff4500'];
    for (let i = 0; i < 25; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      p.style.left = Math.random() * 100 + '%';
      p.style.top = Math.random() * 100 + '%';
      p.style.animationDelay = Math.random() * 12 + 's';
      p.style.animationDuration = (8 + Math.random() * 8) + 's';
      p.style.background = colors[Math.floor(Math.random() * colors.length)];
      p.style.width = (2 + Math.random() * 5) + 'px';
      p.style.height = p.style.width;
      container.appendChild(p);
    }
  }

  function initTilt() {
    document.querySelectorAll('.lab-card').forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left,
          y = e.clientY - rect.top;
        const rx = (y - rect.height / 2) / 15,
          ry = (rect.width / 2 - x) / 15;
        card.style.transform =
          `perspective(1000px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-10px) scale(1.02)`;
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0) scale(1)';
      });
    });
  }

  function initShine() {
    document.querySelectorAll('.lab-card').forEach(card => {
      const shine = card.querySelector('.card-shine');
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        shine.style.background =
          `radial-gradient(circle at ${e.clientX - rect.left}px ${e.clientY - rect.top}px, rgba(255,255,255,0.12) 0%, transparent 50%)`;
      });
    });
  }
  window.onload = function() {
    filterLabs('all');
    animateCounters();
    createParticles();
    initTilt();
    initShine();
  };
  document.addEventListener('mousemove', (e) => {
    const x = e.clientX / window.innerWidth,
      y = e.clientY / window.innerHeight;
    document.querySelectorAll('.orb').forEach((orb, i) => {
      const s = (i + 1) * 25;
      orb.style.transform = `translate(${(0.5 - x) * s}px, ${(0.5 - y) * s}px)`;
    });
  });
  </script>
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
</body>

</html>