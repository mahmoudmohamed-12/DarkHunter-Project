<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = true;

$userData = null;
if (isset($_SESSION['user_id'])) {
  $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmtUser->execute([$_SESSION['user_id']]);
  $userData = $stmtUser->fetch();
}

// Get DOM XSS labs count
$domLabs = [
  ['id' => 1, 'title' => 'Hash Fragment Injection', 'difficulty' => 'easy', 'folder' => 'hash-case-1'],
  ['id' => 2, 'title' => 'Document.Write Sink', 'difficulty' => 'easy', 'folder' => 'document-write-case-2'],
  ['id' => 3, 'title' => 'Eval() Calculator', 'difficulty' => 'medium', 'folder' => 'eval-sink-case-3'],
  ['id' => 4, 'title' => 'Prototype Pollution', 'difficulty' => 'hard', 'folder' => 'advanced-case-4'],
];

$easyCount = count(array_filter($domLabs, fn($l) => $l['difficulty'] === 'easy'));
$mediumCount = count(array_filter($domLabs, fn($l) => $l['difficulty'] === 'medium'));
$hardCount = count(array_filter($domLabs, fn($l) => $l['difficulty'] === 'hard'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DOM XSS Labs - DarkHunter</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/index.css">


</head>

<body><?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>;
  <div class="container"><a href="../../../Public/index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back
      to Dashboard </a>
    <!-- Header -->
    <div class="labs-header">
      <div class="header-icon"><i class="fas fa-code"></i></div>
      <h1 class="labs-title">DOM XSS Labs</h1>
      <p class="labs-subtitle">Master client-side Cross-Site Scripting vulnerabilities. From basic hash fragment
        injections to advanced prototype pollution attacks. </p>
    </div>
    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon"><?php echo count($domLabs);
                                ?></div>
        <div class="stat-label">Total Labs</div>
      </div>
      <div class="stat-card easy">
        <div class="stat-icon"><?php echo $easyCount;
                                ?></div>
        <div class="stat-label">Easy</div>
      </div>
      <div class="stat-card medium">
        <div class="stat-icon"><?php echo $mediumCount;
                                ?></div>
        <div class="stat-label">Medium</div>
      </div>
      <div class="stat-card hard">
        <div class="stat-icon"><?php echo $hardCount;

                                ?></div>
        <div class="stat-label">Hard</div>
      </div>
    </div>
    <!-- Filter Tabs -->
    <div class="filter-tabs"><button class="filter-btn active" onclick="filterLabs('all')">All
        Labs</button><button class="filter-btn" onclick="filterLabs('easy')">Easy</button><button class="filter-btn"
        onclick="filterLabs('medium')">Medium</button><button class="filter-btn"
        onclick="filterLabs('hard')">Hard</button></div>
    <!-- Labs Grid -->
    <div class="labs-grid" id="labsGrid">
      <!-- Lab 1: Hash Fragment -->
      <div class="lab-card-item easy" data-difficulty="easy"><span class="lab-number">#01</span>
        <div class="difficulty-badge-card"><i class="fas fa-bolt"></i>Easy</div>
        <div class="lab-icon"><i class="fas fa-hashtag"></i></div>
        <h3 class="lab-title-card">Hash Fragment Injection</h3>
        <p class="lab-description">Basic DOM XSS via location.hash and innerHTML sink. No filters applied.</p>
        <div class="lab-tags"><span class="lab-tag">DOM XSS</span><span class="lab-tag">location.hash</span><span
            class="lab-tag">innerHTML</span><span class="lab-tag">No
            Filters</span></div>
        <div class="lab-footer">
          <div class="lab-status"><span class="status-dot"></span>Not Started</div><a href="hash-case-1.php"
            class="launch-btn">Launch <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <!-- Lab 2: Document.Write -->
      <div class="lab-card-item easy" data-difficulty="easy"><span class="lab-number">#02</span>
        <div class="difficulty-badge-card"><i class="fas fa-bolt"></i>Easy</div>
        <div class="lab-icon"><i class="fas fa-pen-fancy"></i></div>
        <h3 class="lab-title-card">Document.Write Sink</h3>
        <p class="lab-description">Classic document.write() vulnerability with URL parameter reflection.</p>
        <div class="lab-tags"><span class="lab-tag">DOM XSS</span><span class="lab-tag">document.write</span><span
            class="lab-tag">URL Param</span><span class="lab-tag">No
            Filters</span></div>
        <div class="lab-footer">
          <div class="lab-status"><span class="status-dot"></span>Not Started</div><a href="document-write-case-2.php"
            class="launch-btn">Launch <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <!-- Lab 3: Eval Sink -->
      <div class="lab-card-item medium" data-difficulty="medium"><span class="lab-number">#03</span>
        <div class="difficulty-badge-card"><i class="fas fa-fire"></i>Medium</div>
        <div class="lab-icon"><i class="fas fa-calculator"></i></div>
        <h3 class="lab-title-card">Eval() Calculator</h3>
        <p class="lab-description">JavaScript injection via eval() sink in a calculator application.</p>
        <div class="lab-tags"><span class="lab-tag">DOM XSS</span><span class="lab-tag">eval()</span><span
            class="lab-tag">Code Execution</span><span class="lab-tag">Expression</span></div>
        <div class="lab-footer">
          <div class="lab-status"><span class="status-dot"></span>Not Started</div><a href="eval-sink-case-3.php"
            class="launch-btn">Launch <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <!-- Lab 4: Prototype Pollution -->
      <div class="lab-card-item hard" data-difficulty="hard"><span class="lab-number">#04</span>
        <div class="difficulty-badge-card"><i class="fas fa-skull"></i>Hard</div>
        <div class="lab-icon"><i class="fas fa-biohazard"></i></div>
        <h3 class="lab-title-card">Prototype Pollution</h3>
        <p class="lab-description">Advanced: Combine prototype pollution with DOM clobbering to bypass
          filters.</p>
        <div class="lab-tags"><span class="lab-tag">DOM Clobbering</span><span class="lab-tag">Prototype
            Pollution</span><span class="lab-tag">__proto__</span><span class="lab-tag">Gadget
            Chain</span></div>
        <div class="lab-footer">
          <div class="lab-status"><span class="status-dot"></span>Not Started</div><a href="advanced-case-4.php"
            class="launch-btn">Launch <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
  <script>
    function filterLabs(difficulty) {

      // Update active button
      document.querySelectorAll('.filter-btn').forEach(btn => {
          btn.classList.remove('active');

          if (btn.textContent.toLowerCase().includes(difficulty) || (difficulty === 'all' && btn.textContent ===
              'All Labs')) {
            btn.classList.add('active');
          }
        }

      );

      // Filter cards
      document.querySelectorAll('.lab-card-item').forEach(card => {
          if (difficulty === 'all' || card.dataset.difficulty === difficulty) {
            card.style.display = 'block';
            card.style.animation = 'slideUp 0.5s ease forwards';
          } else {
            card.style.display = 'none';
          }
        }

      );
    }
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