<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;


// Open Redirect Labs Data - Vulnerability-focused progression
$labs = [
  [
    'id' => 1,
    'name' => 'Open Redirect Case 1 - Basic',
    'file' => 'Labs/CASE1.php',
    'description' => 'Unvalidated redirect parameter. Exploit naive URL validation by manipulating redirect destinations to external attacker-controlled domains.',
    'difficulty' => 'Easy',
    'icon' => '🚪',
    'color' => 'orange',
    'tags' => ['Unvalidated', 'URL Param', 'Basic', 'Phishing'],
    'completed' => false
  ],
  [
    'id' => 2,
    'name' => 'Open Redirect Case 2 - Filter Bypass',
    'file' => 'Labs/CASE2.php',
    'description' => 'Weak blacklist filtering. Bypass domain checks using protocol-relative URLs, Unicode homoglyphs, or double-slash tricks.',
    'difficulty' => 'Medium',
    'icon' => '🎭',
    'color' => 'coral',
    'tags' => ['Blacklist', 'Bypass', 'Unicode', 'Protocol'],
    'completed' => false
  ],
  [
    'id' => 3,
    'name' => 'Open Redirect Case 3 - JavaScript',
    'file' => 'Labs/CASE3.php',
    'description' => 'Client-side redirects via JavaScript. Exploit window.location, meta refresh, or AJAX callbacks to hijack user navigation.',
    'difficulty' => 'Medium',
    'icon' => '⚡',
    'color' => 'coral',
    'tags' => ['JavaScript', 'DOM', 'Meta Refresh', 'Hijack'],
    'completed' => false
  ],
  [
    'id' => 4,
    'name' => 'Open Redirect Case 4 - Chain Attack',
    'file' => 'Labs/CASE4.php',
    'description' => 'Complex redirect chains combining SSRF, OAuth flow abuse, and token leakage. Advanced multi-step exploitation scenario.',
    'difficulty' => 'Hard',
    'icon' => '🔗',
    'color' => 'crimson',
    'tags' => ['SSRF', 'OAuth', 'Token Leak', 'Chain'],
    'completed' => false
  ]
];

// Calculate stats
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
  <title>Open Redirect Training Labs | DarkHunter</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/labs/Open-Redirect/css/index.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <!-- Animated Background Effects -->
  <div class="bg-grid"></div>
  <div class="tunnel-container">
    <div class="tunnel-ring"></div>
    <div class="tunnel-ring"></div>
    <div class="tunnel-ring"></div>
    <div class="tunnel-ring"></div>
  </div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <!-- Floating Particles Container -->
  <div id="particles" class="particles"></div>

  <!-- Back Button -->
  <div class="back-nav">
    <a href="/DarkHunter/Public/index.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="container">
    <!-- Header with Portal Effect -->
    <div class="header">
      <div class="page-logo">
        <div class="portal-outer"></div>
        <div class="portal-inner"></div>
        <span class="logo-icon">🌀</span>
      </div>
      <h1>Open Redirect Labs</h1>
      <p>Master URL validation bypasses and redirect manipulation. From basic parameter tampering to advanced multi-step
        chain attacks that hijack user flows and steal tokens.</p>
      <div class="header-glow"></div>
    </div>

    <!-- Stats Bar with Counters -->
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

    <!-- Filter Tabs with Glassmorphism -->
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

    <!-- Labs Grid -->
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

    <!-- Interactive Footer -->
    <div class="footer">
      <div class="footer-glow"></div>
      <p><i class="fas fa-shield-halved"></i> For educational purposes only. Use responsibly in authorized environments.
      </p>
      <p class="footer-credit">Built with <span class="heart">💜</span> for cybersecurity learners</p>
    </div>
  </div>

  <script>
  // Filter Labs Functionality
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

  // Animated Counter for Stats
  function animateCounters() {
    const counters = document.querySelectorAll('.stat-value');
    counters.forEach(counter => {
      const target = parseInt(counter.getAttribute('data-count'));
      const duration = 1200;
      const increment = target / (duration / 16);
      let current = 0;

      const updateCounter = () => {
        current += increment;
        if (current < target) {
          counter.textContent = Math.ceil(current);
          requestAnimationFrame(updateCounter);
        } else {
          counter.textContent = target;
        }
      };
      updateCounter();
    });
  }

  // Create Floating Particles
  function createParticles() {
    const container = document.getElementById('particles');
    const colors = ['#ff6b35', '#ff8c42', '#ff3c38', '#ffa500', '#ff4500'];

    for (let i = 0; i < 25; i++) {
      const particle = document.createElement('div');
      particle.className = 'particle';
      particle.style.left = Math.random() * 100 + '%';
      particle.style.top = Math.random() * 100 + '%';
      particle.style.animationDelay = Math.random() * 12 + 's';
      particle.style.animationDuration = (8 + Math.random() * 8) + 's';
      particle.style.background = colors[Math.floor(Math.random() * colors.length)];
      particle.style.width = (2 + Math.random() * 5) + 'px';
      particle.style.height = particle.style.width;
      container.appendChild(particle);
    }
  }

  // Card Tilt Effect
  function initTilt() {
    const cards = document.querySelectorAll('.lab-card');
    cards.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 15;
        const rotateY = (centerX - x) / 15;

        card.style.transform =
          `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px) scale(1.02)`;
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0) scale(1)';
      });
    });
  }

  // Shine Effect on Cards
  function initShine() {
    const cards = document.querySelectorAll('.lab-card');
    cards.forEach(card => {
      const shine = card.querySelector('.card-shine');
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        shine.style.background =
          `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.12) 0%, transparent 50%)`;
      });
    });
  }

  // Initialize everything on load
  window.onload = function() {
    filterLabs('all');
    animateCounters();
    createParticles();
    initTilt();
    initShine();
  };

  // Parallax Effect for Orbs
  document.addEventListener('mousemove', (e) => {
    const orbs = document.querySelectorAll('.orb');
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;

    orbs.forEach((orb, index) => {
      const speed = (index + 1) * 25;
      const xOffset = (0.5 - x) * speed;
      const yOffset = (0.5 - y) * speed;
      orb.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
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