<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = false;

// Prepare user data for JS state initialization
$userData = [
  'id' => $_SESSION['user_id'] ?? 1,
  'username' => $_SESSION['username'] ?? 'Hacker',
  'level' => $_SESSION['user_level'] ?? 1,
  'xp' => $_SESSION['user_xp'] ?? 0,
  'avatar' => strtoupper(substr($_SESSION['username'] ?? 'HA', 0, 2)),
  'profileImage' => $_SESSION['user_profile_image'] ?? null
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DarkHunter | Community</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;900&family=Inter:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Global Design System -->
  <link rel="stylesheet" href="/DarkHunter/Public/community/global.css">
  <!-- Page-Specific Styles -->
  <link rel="stylesheet" href="/DarkHunter/Public/community/community.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>

  <!-- Background Effects -->
  <div class="bg-grid"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <!-- Main Container -->
  <div class="dh-page-container">
    <div class="community-layout">

      <!-- LEFT COLUMN: Main Feed -->
      <main class="main-feed">

        <!-- Create Post -->
        <div class="create-post">
          <div class="create-post-header">
            <div class="user-avatar" data-size="48">
              <?php if (!empty($userData['profileImage'])): ?>
                <img src="<?php echo htmlspecialchars($userData['profileImage']); ?>" alt="Profile">
              <?php else: ?>
                <?php echo htmlspecialchars($userData['avatar']); ?>
              <?php endif; ?>
            </div>
            <textarea class="create-post-input" id="postInput"
              placeholder="Share your latest hack, ask a question, or post a writeup..."></textarea>
          </div>
          <div class="create-post-actions">
            <div class="post-tools">
              <button class="tool-btn" onclick="CommunityApp.addImage()">
                <span>📷</span>
                <span>Image</span>
              </button>
              <button class="tool-btn" onclick="CommunityApp.addPoll()">
                <span>📊</span>
                <span>Poll</span>
              </button>
              <button class="tool-btn" onclick="CommunityApp.addCode()">
                <span>💻</span>
                <span>Code</span>
              </button>
            </div>
            <button class="dh-btn dh-btn-primary" onclick="CommunityApp.createPost()">Post</button>
          </div>
        </div>

        <!-- Posts Container -->
        <div class="posts-container" id="postsContainer">
          <!-- Populated by community.js -->
        </div>

        <!-- Load More -->
        <div class="load-more" id="loadMore">
          <div class="spinner"></div>
        </div>
      </main>

      <!-- RIGHT COLUMN: Sidebar -->
      <aside class="sidebar-right">

        <!-- User Profile Widget -->
        <div class="sidebar-section">
          <a href="/DarkHunter/Public/profile.php" class="user-widget">
            <div class="user-avatar-wrapper">
              <div class="user-avatar" data-size="48">
                <?php if (!empty($userData['profileImage'])): ?>
                  <img src="<?php echo htmlspecialchars($userData['profileImage']); ?>" alt="Profile">
                <?php else: ?>
                  <?php echo htmlspecialchars($userData['avatar']); ?>
                <?php endif; ?>
              </div>
              <span class="online-indicator"></span>
            </div>
            <div class="user-info">
              <div class="user-name"><?php echo htmlspecialchars($userData['username']); ?></div>
              <div class="user-level">
                LVL <?php echo $userData['level']; ?> • <?php echo $userData['xp']; ?> XP
              </div>
              <div class="xp-bar">
                <div class="xp-progress" data-xp="<?php echo $userData['xp']; ?>"
                  data-level="<?php echo $userData['level']; ?>"></div>
              </div>
            </div>
          </a>
        </div>

        <!-- Trending Topics -->
        <div class="sidebar-section">
          <div class="sidebar-title">🔥 Trending Topics</div>
          <div class="trending-list" id="trendingContainer">
            <!-- Populated by community.js -->
          </div>
        </div>

        <!-- Online Users -->
        <div class="sidebar-section">
          <div class="sidebar-title">🟢 Online Now</div>
          <div class="online-users" id="onlineUsers">
            <!-- Populated by community.js -->
          </div>
        </div>

        <!-- Community Stats -->
        <div class="sidebar-section">
          <div class="sidebar-title">📊 Community Stats</div>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-value" id="statPosts">12.5K</div>
              <div class="stat-label">Posts</div>
            </div>
            <div class="stat-card">
              <div class="stat-value" id="statUsers">8.2K</div>
              <div class="stat-label">Hackers</div>
            </div>
            <div class="stat-card">
              <div class="stat-value" id="statSolved">45.8K</div>
              <div class="stat-label">Solved</div>
            </div>
            <div class="stat-card">
              <div class="stat-value" id="statActive">156</div>
              <div class="stat-label">Active</div>
            </div>
          </div>
        </div>

      </aside>
    </div>
  </div>

  <!-- Achievement Popup -->
  <div class="dh-achievement-popup" id="achievementPopup">
    <div class="dh-achievement-icon">🏆</div>
    <div class="dh-achievement-text">
      <div class="dh-achievement-title">Achievement Unlocked</div>
      <div class="dh-achievement-name" id="achievementName">Bug Hunter I</div>
    </div>
  </div>

  <!-- Toast Container -->
  <div class="dh-toast-container" id="toastContainer"></div>

  <!-- Community JS -->
  <script src="/DarkHunter/Public/community/community.js"></script>
  <script>
    // Initialize with PHP session data
    CommunityApp.init(<?php echo json_encode($userData); ?>);
  </script>

  <?php if (!$isLoggedIn): ?>
    <script>
      window.addEventListener('load', function() {
        if (typeof LoginModal !== 'undefined') {
          LoginModal.show();
        }
      });
    </script>
  <?php endif; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/footer.php'; ?>
</body>

</html>