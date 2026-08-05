<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

  $labs = [
  [
    'id' => 1,
    'name' => 'XSS Lab v1.0',
    'file' => 'newxsslab.php',
    'description' => 'Reflected XSS - Basic Level with Modern UI',
    'difficulty' => 'Easy',
    'icon' => '💉',
    'color' => 'green',
    'tags' => ['Reflected', 'Basic Filters', 'Script Tags'],
    'completed' => false
  ],
  [
    'id' => 2,
    'name' => 'XSS Lab v2.0',
    'file' => 'newxsslab2.php',
    'description' => 'Advanced Filter Bypass with WAF',
    'difficulty' => 'Medium/Hard',
    'icon' => '🛡️',
    'color' => 'orange',
    'tags' => ['WAF Bypass', 'Encoding', 'Double Filter'],
    'completed' => false
  ],
  [
    'id' => 3,
    'name' => 'XSS Case 1',
    'file' => 'Guestbook-case-1.php',
    'description' => 'Basic DOM XSS Injection',
    'difficulty' => 'Easy',
    'icon' => '🔓',
    'color' => 'green',
    'tags' => ['POST Method', ' No Filters', 'Basic Payloads'],
    'completed' => false
  ],
  [
    'id' => 4,
    'name' => 'XSS Case 2',
    'file' => 'Newsletter-case-2.php',
    'description' => 'URL Parameter Injection',
    'difficulty' => 'Easy',
    'icon' => '🔗',
    'color' => 'green',
    'tags' => ['URL Param', 'Reflected', 'HTML Injection'],
    'completed' => false
  ],
  [
    'id' => 5,
    'name' => 'XSS Case 3',
    'file' => 'Search Portal-case-3.php',
    'description' => 'Event Handler Exploitation',
    'difficulty' => 'Medium',
    'icon' => '🎯',
    'color' => 'yellow',
    'tags' => ['Event Handlers', 'Reflected', 'Filter Bypass', 'Blacklist'],
    'completed' => false
  ],
  [
    'id' => 6,
    'name' => 'XSS Case 4',
    'file' => 'Profile Viewer-case-4.php',
    'description' => 'JavaScript Protocol Abuse',
    'difficulty' => 'Medium',
    'icon' => '⚡',
    'color' => 'yellow',
    'tags' => ['javascript:', 'data URI', 'Reflected', 'Context Breaking', 'Angle Brackets Filtered', 'Quote Escaping'],
    'completed' => false
  ],
  [
    'id' => 7,
    'name' => 'XSS Case 5',
    'file' => 'advanced-case-5.php',
    'description' => 'Advanced Polyglot Payloads',
    'difficulty' => 'Hard',
    'icon' => '🔥',
    'color' => 'red',
    'tags' => ['WAF Bypass', 'Double Encoding', 'Filter Evasion', 'HTML Entities', 'No Parentheses'],
    'completed' => false
  ]
];

// Calculate stats
$total_labs = count($labs);
$easy_count = count(array_filter($labs, fn($l) => $l['difficulty'] === 'Easy'));
$medium_count = count(array_filter($labs, fn($l) => strpos($l['difficulty'], 'Medium') !== false));
$hard_count = count(array_filter($labs, fn($l) => strpos($l['difficulty'], 'Hard') !== false));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>XSS Training Labs | RXSS Portal</title>
  <link rel="stylesheet" type="text/css" href="css/index.css">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


  <style>
  <?php foreach ($labs as $i=> $lab): ?>.lab-card:nth-child(<?php echo $i + 1; ?>) {
    animation-delay: <?php echo $i * 0.1;
    ?>s;
  }

  <?php endforeach;
  ?>

  /* Back Button Style */
  .back-nav {
    position: fixed;
    top: 100px;
    left: 30px;
    z-index: 1000;
  }

  .back-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: rgba(0, 0, 0, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.8);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9rem;
    text-decoration: none;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
  }

  .back-btn:hover {
    background: rgba(0, 255, 136, 0.1);
    border-color: var(--neon-green);
    color: var(--neon-green);
    transform: translateX(-5px);
  }

  /* Adjust container padding for back button */
  .container {
    padding-top: 60px;
  }
  </style>

</head>

<body>
  <?php include_once __DIR__ . '/../../../includes/navbar.php';
  ?>

  <!-- Back Button -->
  <div class="back-nav">
    <a href="../../../Public/index.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="bg-grid"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <div class="container">
    <!-- Header -->
    <div class="header">
      <div class="logo">🛡️</div>
      <h1>Reflected XSS Labs</h1>
      <p>Master Cross-Site Scripting vulnerabilities through hands-on practice. From basic injections to advanced WAF
        bypass techniques.</p>
    </div>

    <!-- Stats -->
    <div class="stats-bar">
      <div class="stat-card" style="--accent-color: var(--neon-cyan); --accent-glow: rgba(0, 240, 255, 0.3);">
        <div class="stat-icon">📚</div>
        <div class="stat-value"><?php echo $total_labs; ?></div>
        <div class="stat-label">Total Labs</div>
      </div>
      <div class="stat-card" style="--accent-color: var(--neon-green); --accent-glow: rgba(0, 255, 136, 0.3);">
        <div class="stat-icon">🟢</div>
        <div class="stat-value"><?php echo $easy_count; ?></div>
        <div class="stat-label">Easy</div>
      </div>
      <div class="stat-card" style="--accent-color: var(--neon-yellow); --accent-glow: rgba(255, 204, 0, 0.3);">
        <div class="stat-icon">🟡</div>
        <div class="stat-value"><?php echo $medium_count; ?></div>
        <div class="stat-label">Medium</div>
      </div>
      <div class="stat-card" style="--accent-color: var(--neon-red); --accent-glow: rgba(255, 0, 64, 0.3);">
        <div class="stat-icon">🔴</div>
        <div class="stat-value"><?php echo $hard_count; ?></div>
        <div class="stat-label">Hard</div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterLabs('all')">All Labs</button>
      <button class="filter-btn" onclick="filterLabs('easy')">Easy</button>
      <button class="filter-btn" onclick="filterLabs('medium')">Medium</button>
      <button class="filter-btn" onclick="filterLabs('hard')">Hard</button>
    </div>

    <!-- Labs Grid -->
    <div class="labs-grid" id="labsGrid">
      <?php foreach ($labs as $lab):
        $color_map = [
          'green' => ['#00ff88', 'rgba(0, 255, 136, 0.3)', 'rgba(0, 255, 136, 0.1)', 'rgba(0, 255, 136, 0.3)'],
          'yellow' => ['#ffcc00', 'rgba(255, 204, 0, 0.3)', 'rgba(255, 204, 0, 0.1)', 'rgba(255, 204, 0, 0.3)'],
          'orange' => ['#ff6600', 'rgba(255, 102, 0, 0.3)', 'rgba(255, 102, 0, 0.1)', 'rgba(255, 102, 0, 0.3)'],
          'red' => ['#ff0040', 'rgba(255, 0, 64, 0.3)', 'rgba(255, 0, 64, 0.1)', 'rgba(255, 0, 64, 0.3)']
        ];
        $colors = $color_map[$lab['color']];
        $difficulty_lower = strtolower($lab['difficulty']);
      ?>
      <a href="<?php echo $lab['file']; ?>" class="lab-card"
        style="--lab-color: <?php echo $colors[0]; ?>; --lab-glow: <?php echo $colors[1]; ?>;"
        data-difficulty="<?php echo strpos($difficulty_lower, 'easy') !== false ? 'easy' : (strpos($difficulty_lower, 'hard') !== false ? 'hard' : 'medium'); ?>">

        <span class="lab-number">#<?php echo str_pad($lab['id'], 2, '0', STR_PAD_LEFT); ?></span>

        <div class="lab-header-row">
          <div class="lab-icon"><?php echo $lab['icon']; ?></div>
          <span class="lab-difficulty"
            style="--diff-bg: <?php echo $colors[2]; ?>; --diff-color: <?php echo $colors[0]; ?>; --diff-border: <?php echo $colors[3]; ?>">
            <?php echo $lab['difficulty']; ?>
          </span>
        </div>

        <h3 class="lab-title"><?php echo $lab['name']; ?></h3>
        <p class="lab-description"><?php echo $lab['description']; ?></p>

        <div class="lab-tags">
          <?php foreach ($lab['tags'] as $tag): ?>
          <span class="lab-tag"><?php echo $tag; ?></span>
          <?php endforeach; ?>
        </div>

        <div class="lab-footer">
          <div class="lab-status" style="--status-color: <?php echo $lab['completed'] ? '#00ff88' : '#666'; ?>">
            <span class="status-dot"></span>
            <span><?php echo $lab['completed'] ? 'Completed' : 'Not Started'; ?></span>
          </div>
          <button class="launch-btn">
            Launch <span>→</span>
          </button>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p>🔒 For educational purposes only. Use responsibly in authorized environments.</p>
      <p style="margin-top: 10px;">Built with 💜 for cybersecurity learners</p>
    </div>
  </div>

  <script>
  function filterLabs(difficulty) {
    const cards = document.querySelectorAll('.lab-card');
    const buttons = document.querySelectorAll('.filter-btn');

    // Update active button
    buttons.forEach(btn => {
      btn.classList.remove('active');
      if (btn.textContent.toLowerCase().includes(difficulty) ||
        (difficulty === 'all' && btn.textContent.includes('All'))) {
        btn.classList.add('active');
      }
    });

    // Filter cards
    cards.forEach(card => {
      if (difficulty === 'all' || card.dataset.difficulty === difficulty) {
        card.style.display = 'block';
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, 10);
      } else {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
          card.style.display = 'none';
        }, 300);
      }
    });
  }
  </script>
  <script src="../../assets/js/main.js"></script>
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