<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

// SSRF Labs Data
$labs = [
  [
    'id' => 1,
    'name' => 'SSRF Case 1 - Basic URL',
    'file' => 'Labs/SSRF-CASE1.php',
    'description' => 'Unvalidated URL parameter. Supply internal addresses like 127.0.0.1 or localhost to access internal services and administrative endpoints.',
    'difficulty' => 'Easy',
    'icon' => '🌐',
    'color' => 'orange',
    'tags' => ['URL Param', 'Internal', '127.0.0.1', 'Basic'],
    'completed' => false
  ],
  [
    'id' => 2,
    'name' => 'SSRF Case 2 - DNS Rebinding',
    'file' => 'Labs/SSRF-CASE2.php',
    'description' => 'Bypass IP validation with DNS rebinding. Use domains that resolve to internal IPs after initial validation to reach restricted networks.',
    'difficulty' => 'Easy',
    'icon' => '🔄',
    'color' => 'orange',
    'tags' => ['DNS Rebind', 'TTL', 'Bypass', 'Internal Network'],
    'completed' => false
  ],
  [
    'id' => 3,
    'name' => 'SSRF Case 3 - Protocol Abuse',
    'file' => 'Labs/SSRF-CASE3.php',
    'description' => 'Abuse alternative protocols. Use file://, gopher://, dict://, or ftp:// to read local files or interact with internal services.',
    'difficulty' => 'Medium',
    'icon' => '📡',
    'color' => 'coral',
    'tags' => ['Protocol', 'file://', 'gopher://', 'dict://'],
    'completed' => false
  ],
  [
    'id' => 4,
    'name' => 'SSRF Case 4 - Cloud Metadata',
    'file' => 'Labs/SSRF-CASE4.php',
    'description' => 'Access cloud metadata endpoints. Target 169.254.169.254 to steal IAM credentials, instance metadata, and cloud configuration.',
    'difficulty' => 'Medium',
    'icon' => '☁️',
    'color' => 'coral',
    'tags' => ['Cloud', 'Metadata', '169.254.169.254', 'IAM'],
    'completed' => false
  ],
  [
    'id' => 5,
    'name' => 'SSRF Case 5 - Redirect Chain',
    'file' => 'Labs/SSRF-CASE5.php',
    'description' => 'Bypass URL filters through redirects. Use open redirect endpoints or URL shorteners to reach blocked internal destinations.',
    'difficulty' => 'Hard',
    'icon' => '⛓️',
    'color' => 'crimson',
    'tags' => ['Redirect', 'Chain', 'Open Redirect', 'Filter Bypass'],
    'completed' => false
  ],
  [
    'id' => 6,
    'name' => 'SSRF Case 6 - CRLF Injection',
    'file' => 'Labs/SSRF-CASE6.php',
    'description' => 'Inject CRLF sequences in HTTP requests. Split requests to perform smuggling attacks or inject arbitrary headers into backend requests.',
    'difficulty' => 'Hard',
    'icon' => '💉',
    'color' => 'crimson',
    'tags' => ['CRLF', 'HTTP Smuggling', 'Header Injection', 'Request Split'],
    'completed' => false
  ],
  [
    'id' => 7,
    'name' => 'SSRF Case 7 - Redis/Memcached',
    'file' => 'Labs/SSRF-CASE7.php',
    'description' => 'Attack internal databases via SSRF. Use gopher:// to send raw Redis/Memcached commands for RCE or data exfiltration.',
    'difficulty' => 'Hard',
    'icon' => '🗄️',
    'color' => 'crimson',
    'tags' => ['Redis', 'Memcached', 'gopher://', 'RCE'],
    'completed' => false
  ],
  [
    'id' => 8,
    'name' => 'SSRF Case 8 - Blind OOB',
    'file' => 'Labs/SSRF-CASE8.php',
    'description' => 'Blind SSRF with out-of-band detection. Use DNS lookups, Burp Collaborator, or time delays to confirm internal resource access.',
    'difficulty' => 'Hard',
    'icon' => '👁️‍🗨️',
    'color' => 'crimson',
    'tags' => ['Blind', 'OOB', 'DNS', 'Time Delay'],
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
  <title>SSRF Training Labs | DarkHunter</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/labs/SSRF/css/index.css">
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
    <a href="/DarkHunter/Public/index.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="container">
    <div class="header">
      <div class="page-logo">
        <div class="portal-outer"></div>
        <div class="portal-inner"></div>
        <span class="logo-icon">🕸️</span>
      </div>
      <h1>SSRF Labs</h1>
      <p>Master Server-Side Request Forgery vulnerabilities. From basic internal network access to advanced cloud
        metadata theft, protocol abuse, and blind out-of-band exploitation.</p>
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
      <button class="filter-btn active" onclick="filterLabs('all')">
        <i class="fas fa-th"></i> All Labs
      </button>
      <button class="filter-btn" onclick="filterLabs('easy')">
        <i class="fas fa-seedling"></i> Easy
      </button>
      <button class="filter-btn" onclick="filterLabs('medium')">
        <i class="fas fa-bolt"></i> Medium
      </button>
      <button class="filter-btn" onclick="filterLabs('hard')">
        <i class="fas fa-fire"></i> Hard
      </button>
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
          <button class="launch-btn">
            <span class="btn-text">Launch</span>
            <span class="btn-arrow">→</span>
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
      if (btn.textContent.toLowerCase().includes(difficulty) ||
        (difficulty === 'all' && btn.textContent.includes('All'))) {
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
      const duration = 1200;
      const increment = target / (duration / 16);
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