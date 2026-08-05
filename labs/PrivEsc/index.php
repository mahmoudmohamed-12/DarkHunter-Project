<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

// Privilege Escalation Labs Data
$labs = [
  [
    'id' => 1,
    'name' => 'PrivEsc Case 1 - IDOR Role Bypass',
    'file' => 'Labs/PRIV-CASE1.php',
    'description' => 'Manipulate role parameters in requests. Change user_type, is_admin, or role_id fields to escalate from regular user to administrator privileges.',
    'difficulty' => 'Easy',
    'icon' => '🔓',
    'color' => 'orange',
    'tags' => ['IDOR', 'Role Param', 'User Type', 'Admin Bypass'],
    'completed' => false
  ],
  [
    'id' => 2,
    'name' => 'PrivEsc Case 2 - JWT Token Tampering',
    'file' => 'Labs/PRIV-CASE2.php',
    'description' => 'Weak JWT signature validation. Modify payload claims like role, permissions, or user_id and re-sign with none/HS256 algorithm confusion.',
    'difficulty' => 'Easy',
    'icon' => '🔑',
    'color' => 'orange',
    'tags' => ['JWT', 'Token Tamper', 'alg:none', 'Claims Manipulation'],
    'completed' => false
  ],
  [
    'id' => 3,
    'name' => 'PrivEsc Case 3 - Mass Assignment',
    'file' => 'Labs/PRIV-CASE3.php',
    'description' => 'Overly permissive model binding. Inject admin, role, or is_superuser fields in registration/profile update forms to auto-escalate privileges.',
    'difficulty' => 'Medium',
    'icon' => '💣',
    'color' => 'coral',
    'tags' => ['Mass Assignment', 'Model Binding', 'Auto Escalate', 'Hidden Fields'],
    'completed' => false
  ],
  [
    'id' => 4,
    'name' => 'PrivEsc Case 4 - Session Fixation',
    'file' => 'Labs/PRIV-CASE4.php',
    'description' => 'Session handling vulnerabilities. Fixate or hijack admin sessions through predictable session IDs, session regeneration flaws, or concurrent session abuse.',
    'difficulty' => 'Medium',
    'icon' => '🎭',
    'color' => 'coral',
    'tags' => ['Session Fixation', 'Session Hijack', 'Predictable ID', 'Concurrent'],
    'completed' => false
  ],
  [
    'id' => 5,
    'name' => 'PrivEsc Case 5 - OAuth Scope Escalation',
    'file' => 'Labs/PRIV-CASE5.php',
    'description' => 'OAuth2 scope manipulation. Modify scope parameters in authorization flows to gain elevated permissions beyond what the user originally consented to.',
    'difficulty' => 'Hard',
    'icon' => '🔗',
    'color' => 'crimson',
    'tags' => ['OAuth2', 'Scope', 'Authorization', 'Consent Bypass'],
    'completed' => false
  ],
  [
    'id' => 6,
    'name' => 'PrivEsc Case 6 - GraphQL Introspection',
    'file' => 'Labs/PRIV-CASE6.php',
    'description' => 'Abuse GraphQL introspection and mutations. Access admin-only queries, bypass field-level authorization, and escalate through nested resolver manipulation.',
    'difficulty' => 'Hard',
    'icon' => '📊',
    'color' => 'crimson',
    'tags' => ['GraphQL', 'Introspection', 'Admin Queries', 'Resolver'],
    'completed' => false
  ],
  [
    'id' => 7,
    'name' => 'PrivEsc Case 7 - Race Condition',
    'file' => 'Labs/PRIV-CASE7.php',
    'description' => 'TOCTOU in privilege checks. Race the authorization logic by sending simultaneous requests that pass validation before the privilege state updates.',
    'difficulty' => 'Hard',
    'icon' => '⏱️',
    'color' => 'crimson',
    'tags' => ['Race Condition', 'TOCTOU', 'Concurrent', 'State Abuse'],
    'completed' => false
  ],
  [
    'id' => 8,
    'name' => 'PrivEsc Case 8 - API Key Reuse',
    'file' => 'Labs/PRIV-CASE8.php',
    'description' => 'Cross-tenant API key abuse. Reuse or predict API keys across different tenant contexts to access admin endpoints belonging to other organizations.',
    'difficulty' => 'Hard',
    'icon' => '🗝️',
    'color' => 'crimson',
    'tags' => ['API Key', 'Cross-Tenant', 'Multi-Tenant', 'Key Reuse'],
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
  <title>Privilege Escalation Labs | DarkHunter</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/labs/PrivEsc/css/index.css">
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
        <span class="logo-icon">👑</span>
      </div>
      <h1>Privilege Escalation Labs</h1>
      <p>Master vertical and horizontal privilege escalation. From basic role parameter tampering to advanced race
        conditions, OAuth scope abuse, GraphQL introspection attacks, and cross-tenant API key exploitation.</p>
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