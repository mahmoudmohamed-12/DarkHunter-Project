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

// Get featured labs (limit to 3)
$stmtFeatured = $pdo->query("SELECT * FROM labs ORDER BY id ASC LIMIT 3");
$featuredLabs = $stmtFeatured->fetchAll();

// Get total labs count
$stmtLabs = $pdo->query("SELECT COUNT(*) as total FROM labs");
$labsCount = $stmtLabs->fetch()['total'];

// Calculate rank (placeholder logic)
$rank = $userData ? calculateRank($userData['score']) : '--';
$level = $userData ? calculateLevel($userData['total_xp']) : 1;

function calculateRank($score)
{
  if ($score >= 10000) return '#1337';
  if ($score >= 5000) return '#5K+';
  if ($score >= 1000) return '#10K+';
  return '#50K+';
}

function calculateLevel($xp)
{
  return floor($xp / 500) + 1;
}

function getRequiredXP($level)
{
  return $level * 500;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DarkHunter Dashboard</title>

  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/Public/css/index.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include 'login-modal.php'; ?>

  <div class="bg-grid"></div>
  <div class="bg-glow"></div>
  <div class="bg-glow-2"></div>

  <script>
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
    <section class="welcome-section">
      <div class="welcome-header">
        <div class="welcome-icon">
          <i class="fas fa-user-astronaut"></i>
        </div>
        <div class="welcome-text">
          <h1>Welcome back, <?php echo $userData ? htmlspecialchars($userData['username']) : 'Hacker'; ?> 👾</h1>
          <p>Sharpen your skills. Exploit. Learn. Repeat.</p>
        </div>
      </div>
    </section>

    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-icon completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <span class="stat-label">Completed Labs</span>
        </div>
        <div class="stat-value">0</div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-icon score">
            <i class="fas fa-bolt"></i>
          </div>
          <span class="stat-label">Total Score</span>
        </div>
        <div class="stat-value"><?php echo $userData ? number_format($userData['score']) : '0'; ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-icon rank">
            <i class="fas fa-trophy"></i>
          </div>
          <span class="stat-label">Global Rank</span>
        </div>
        <div class="stat-value"><?php echo $rank; ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-icon level">
            <i class="fas fa-layer-group"></i>
          </div>
          <span class="stat-label">Current Level</span>
        </div>
        <div class="stat-value"><?php echo $level; ?></div>
      </div>
    </section>

    <div class="dashboard-grid">
      <div class="left-column">
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-bars-progress"></i>
              Your Progress
            </div>
          </div>
          <div class="progress-container">
            <div class="progress-header">
              <div class="level-badge">
                <i class="fas fa-star"></i>
                Level <?php echo $level; ?>
              </div>
              <div class="xp-text">
                <span><?php echo $userData ? number_format($userData['total_xp']) : '0'; ?></span> /
                <?php echo number_format(getRequiredXP($level)); ?> XP
              </div>
            </div>
            <div class="progress-bar-container">
              <?php
              $currentXP = $userData ? $userData['total_xp'] : 0;
              $requiredXP = getRequiredXP($level);
              $progressPercent = min(100, ($currentXP % 500) / 500 * 100);
              ?>
              <div class="progress-bar" style="width: <?php echo $progressPercent; ?>%"></div>
            </div>
          </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-fire"></i>
              Featured Labs
            </div>
            <a href="labs.php" class="view-all-btn">
              View All <i class="fas fa-arrow-right"></i>
            </a>
          </div>
          <div class="featured-labs">
            <?php if (count($featuredLabs) > 0): ?>
              <?php foreach ($featuredLabs as $lab): ?>
                <div class="lab-card" onclick="window.location.href='../labs/<?php echo $lab['folder_name']; ?>/'">
                  <div class="lab-icon <?php echo $lab['difficulty']; ?>">
                    <?php if ($lab['difficulty'] == 'easy'): ?>
                      <i class="fas fa-bolt"></i>
                    <?php elseif ($lab['difficulty'] == 'medium'): ?>
                      <i class="fas fa-fire"></i>
                    <?php else: ?>
                      <i class="fas fa-skull"></i>
                    <?php endif; ?>
                  </div>
                  <div class="lab-info">
                    <div class="lab-title">
                      <?php echo htmlspecialchars($lab['title']); ?>
                      <span
                        class="lab-difficulty <?php echo $lab['difficulty']; ?>"><?php echo ucfirst($lab['difficulty']); ?></span>
                    </div>
                    <div class="lab-desc"><?php echo htmlspecialchars($lab['description']); ?></div>
                  </div>
                  <a href="../labs/<?php echo $lab['folder_name']; ?>/" class="lab-btn">
                    <i class="fas fa-play"></i> Start
                  </a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-flask"></i>
                <p>No labs available yet. Check back soon!</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="right-column">
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-medal"></i>
              Achievements
            </div>
          </div>
          <div class="achievements-grid">
            <div class="achievement-card unlocked">
              <div class="achievement-icon gold">
                <i class="fas fa-bug"></i>
              </div>
              <div class="achievement-title">XSS Master</div>
              <span class="achievement-status unlocked"><i class="fas fa-check"></i> Unlocked</span>
            </div>

            <div class="achievement-card unlocked">
              <div class="achievement-icon blue">
                <i class="fas fa-database"></i>
              </div>
              <div class="achievement-title">SQLi Hunter</div>
              <span class="achievement-status unlocked"><i class="fas fa-check"></i> Unlocked</span>
            </div>

            <div class="achievement-card locked">
              <div class="achievement-icon purple">
                <i class="fas fa-server"></i>
              </div>
              <div class="achievement-title">SSRF Pro</div>
              <span class="achievement-status locked"><i class="fas fa-lock"></i> Locked</span>
            </div>

            <div class="achievement-card locked">
              <div class="achievement-icon red">
                <i class="fas fa-crown"></i>
              </div>
              <div class="achievement-title">Elite Hacker</div>
              <span class="achievement-status locked"><i class="fas fa-lock"></i> Locked</span>
            </div>
          </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-terminal"></i>
              System Status
            </div>
          </div>
          <div class="status-list">
            <div class="status-item">
              <span class="status-label">Labs Online</span>
              <span class="status-value">
                <span class="status-dot"></span>
                <?php echo $labsCount; ?> Active
              </span>
            </div>

            <div class="status-item info">
              <span class="status-label">Server Status</span>
              <span class="status-value">
                <span class="status-dot" style="background: var(--neon-cyan);"></span>
                Online
              </span>
            </div>

            <div class="status-item warning">
              <span class="status-label">Platform Version</span>
              <span class="status-value" style="color: var(--neon-yellow);">v2.0.1</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Animate progress bar on load
    document.addEventListener('DOMContentLoaded', function() {
      const progressBar = document.querySelector('.progress-bar');
      const finalWidth = progressBar.style.width;
      progressBar.style.width = '0%';
      setTimeout(() => {
        progressBar.style.width = finalWidth;
      }, 300);
    });

    // Add hover effects to stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px) scale(1.02)';
      });
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
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