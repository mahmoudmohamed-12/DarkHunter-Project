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

// Get all labs
$stmtLabs = $pdo->query("SELECT * FROM labs ORDER BY 
    CASE difficulty 
        WHEN 'easy' THEN 1 
        WHEN 'medium' THEN 2 
        WHEN 'hard' THEN 3 
    END, id ASC");
$allLabs = $stmtLabs->fetchAll();

// Get counts by difficulty
$easyCount = count(array_filter($allLabs, fn($lab) => $lab['difficulty'] == 'easy'));
$mediumCount = count(array_filter($allLabs, fn($lab) => $lab['difficulty'] == 'medium'));
$hardCount = count(array_filter($allLabs, fn($lab) => $lab['difficulty'] == 'hard'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Labs - DarkHunter</title>

  <!-- Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/Public/css/labs.css">

</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include 'login-modal.php'; ?>
  <div class="bg-grid"></div>

  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">
        <i class="fas fa-flask"></i>
        Training Labs
      </h1>
      <p class="page-subtitle">Choose your challenge. Master the art of ethical hacking.</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-pill">
        <div class="stat-pill-icon easy">
          <i class="fas fa-bolt"></i>
        </div>
        <div class="stat-pill-info">
          <span class="stat-pill-value"><?php echo $easyCount; ?></span>
          <span class="stat-pill-label">Easy Labs</span>
        </div>
      </div>

      <div class="stat-pill">
        <div class="stat-pill-icon medium">
          <i class="fas fa-fire"></i>
        </div>
        <div class="stat-pill-info">
          <span class="stat-pill-value"><?php echo $mediumCount; ?></span>
          <span class="stat-pill-label">Medium Labs</span>
        </div>
      </div>

      <div class="stat-pill">
        <div class="stat-pill-icon hard">
          <i class="fas fa-skull"></i>
        </div>
        <div class="stat-pill-info">
          <span class="stat-pill-value"><?php echo $hardCount; ?></span>
          <span class="stat-pill-label">Hard Labs</span>
        </div>
      </div>
    </div>

    <!-- Controls -->
    <div class="controls-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Search labs by name or category...">
      </div>

      <select class="filter-select" id="difficultyFilter">
        <option value="all">All Difficulties</option>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
      </select>
    </div>

    <!-- Labs Grid -->
    <div class="labs-grid" id="labsGrid">
      <?php if (count($allLabs) > 0): ?>
      <?php foreach ($allLabs as $lab): ?>
      <div class="lab-card <?php echo $lab['difficulty']; ?>"
        data-title="<?php echo strtolower(htmlspecialchars($lab['title'])); ?>"
        data-difficulty="<?php echo $lab['difficulty']; ?>">
        <div class="lab-header">
          <span class="lab-difficulty-badge <?php echo $lab['difficulty']; ?>">
            <?php if ($lab['difficulty'] == 'easy'): ?>
            <i class="fas fa-bolt"></i> Easy
            <?php elseif ($lab['difficulty'] == 'medium'): ?>
            <i class="fas fa-fire"></i> Medium
            <?php else: ?>
            <i class="fas fa-skull"></i> Hard
            <?php endif; ?>
          </span>
          <h3 class="lab-title"><?php echo htmlspecialchars($lab['title']); ?></h3>
          <div class="lab-category">
            <i class="fas fa-tag"></i>
            <span>Web Exploitation</span>
          </div>
        </div>

        <div class="lab-body">
          <p class="lab-description"><?php echo htmlspecialchars($lab['description']); ?></p>

          <div class="lab-meta">
            <div class="lab-meta-item">
              <i class="fas fa-star"></i>
              <span>100 XP</span>
            </div>
            <div class="lab-meta-item">
              <i class="fas fa-clock"></i>
              <span>~30 min</span>
            </div>
            <div class="lab-meta-item">
              <i class="fas fa-users"></i>
              <span>1.2k solved</span>
            </div>
          </div>

          <div class="lab-actions">
            <a href="../labs/<?php echo $lab['folder_name']; ?>/" class="btn btn-primary">
              <i class="fas fa-play"></i> Start Lab
            </a>
            <button class="btn btn-secondary" onclick="showInfo('<?php echo htmlspecialchars($lab['title']); ?>')">
              <i class="fas fa-info-circle"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-flask"></i>
        <h3>No Labs Available</h3>
        <p>New challenges are being prepared. Check back soon!</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
  // Search and Filter Functionality
  const searchInput = document.getElementById('searchInput');
  const difficultyFilter = document.getElementById('difficultyFilter');
  const labCards = document.querySelectorAll('.lab-card');

  function filterLabs() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedDifficulty = difficultyFilter.value;

    labCards.forEach(card => {
      const title = card.getAttribute('data-title');
      const difficulty = card.getAttribute('data-difficulty');

      const matchesSearch = title.includes(searchTerm);
      const matchesDifficulty = selectedDifficulty === 'all' || difficulty === selectedDifficulty;

      if (matchesSearch && matchesDifficulty) {
        card.classList.remove('hidden');
        card.style.animation = 'fade-in-up 0.4s ease-out';
      } else {
        card.classList.add('hidden');
      }
    });
  }

  searchInput.addEventListener('input', filterLabs);
  difficultyFilter.addEventListener('change', filterLabs);

  // Info button handler
  function showInfo(labTitle) {
    // You can expand this to show a modal with lab details
    console.log('Showing info for:', labTitle);
  }

  // Add entrance animations
  document.addEventListener('DOMContentLoaded', () => {
    labCards.forEach((card, index) => {
      card.style.animationDelay = `${index * 0.05}s`;
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