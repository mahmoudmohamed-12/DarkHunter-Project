<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

if (isset($_POST['update_profile'])) {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $current_pic = $_POST['current_pic'];
    $new_pic_name = $current_pic;

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "../assets/images/profiles/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_ext = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $new_pic_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_pic_name;

        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                if ($current_pic != 'default_avatar.png' && file_exists($target_dir . $current_pic)) {
                    unlink($target_dir . $current_pic);
                }
            } else {
                $error_msg = "Error uploading picture.";
                $new_pic_name = $current_pic;
            }
        } else {
            $error_msg = "Only JPG, PNG, JPEG, GIF allowed.";
            $new_pic_name = $current_pic;
        }
    }

    $stmt = $pdo->prepare("UPDATE users SET phone = ?, address = ?, profile_pic = ? WHERE id = ?");
    if ($stmt->execute([$phone, $address, $new_pic_name, $user_id])) {
        $success_msg = "Profile secured successfully! 🛡️";
    }
}


$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Calculate user stats
$level = floor(($user['total_xp'] ?? 0) / 500) + 1;
$xpProgress = ($user['total_xp'] ?? 0) % 500;
$xpPercent = ($xpProgress / 500) * 100;
$rank = calculateRank($user['score'] ?? 0);

function calculateRank($score) {
    if ($score >= 10000) return 'Elite';
    if ($score >= 5000) return 'Pro';
    if ($score >= 1000) return 'Hacker';
    return 'Script Kiddie';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hacker Profile | DarkHunter</title>

  <!-- Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/Public/css/profile.css">


</head>

<body>
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

  <!-- Background Effects -->
  <div class="bg-grid"></div>
  <div class="bg-glow"></div>
  <div class="bg-glow-2"></div>

  <!-- Floating Particles -->
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
    <div class="profile-layout">
      <!-- Left Sidebar -->
      <div class="profile-sidebar">
        <!-- Profile Card -->
        <div class="profile-card">
          <form method="POST" id="profileForm" enctype="multipart/form-data">
            <div class="avatar-wrapper">
              <div class="avatar-ring"></div>
              <div class="avatar-container">
                <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_pic']); ?>"
                  alt="Hacker Avatar" id="avatarPreview">
              </div>
              <label class="avatar-upload" id="uploadBtn">
                <i class="fas fa-camera"></i>
                <input type="file" name="profile_pic" id="profile_pic_input" onchange="previewImage(this)">
              </label>
            </div>
            <input type="hidden" name="current_pic" value="<?php echo htmlspecialchars($user['profile_pic']); ?>">

            <h2 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h2>
            <span class="profile-rank">
              <i class="fas fa-shield-halved"></i>
              <?php echo $rank; ?>
            </span>

            <div class="profile-id">
              <i class="fas fa-fingerprint"></i>
              <span>UID: <?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>

            <!-- Mini Stats -->
            <div class="mini-stats">
              <div class="mini-stat">
                <div class="mini-stat-value"><?php echo number_format($user['score'] ?? 0); ?></div>
                <div class="mini-stat-label">Score</div>
              </div>
              <div class="mini-stat">
                <div class="mini-stat-value"><?php echo $user['completed_labs'] ?? 0; ?></div>
                <div class="mini-stat-label">Labs</div>
              </div>
            </div>

            <!-- Achievements Preview -->
            <div class="achievements-preview">
              <div class="achievement-badge unlocked" title="XSS Master">
                <i class="fas fa-bug"></i>
              </div>
              <div class="achievement-badge unlocked" title="SQLi Hunter">
                <i class="fas fa-database"></i>
              </div>
              <div class="achievement-badge locked" title="SSRF Pro">
                <i class="fas fa-server"></i>
              </div>
              <div class="achievement-badge locked" title="Elite Hacker">
                <i class="fas fa-crown"></i>
              </div>
            </div>
        </div>

        <!-- XP Progress Card -->
        <div class="xp-card">
          <div class="xp-header">
            <span class="xp-title">
              <i class="fas fa-star"></i>
              Experience
            </span>
            <span class="xp-level">Lv. <?php echo $level; ?></span>
          </div>
          <div class="xp-bar-container">
            <div class="xp-bar" style="width: <?php echo $xpPercent; ?>%"></div>
          </div>
          <div class="xp-text">
            <span><?php echo number_format($xpProgress); ?></span> / 500 XP to next level
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="profile-main">
        <!-- Identity Card -->
        <div class="content-card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-id-card"></i>
              Identity Data
            </h3>
            <button type="button" class="edit-btn" id="editToggleBtn" onclick="toggleEdit()">
              <i class="fas fa-edit"></i>
              <span>Edit Profile</span>
            </button>
          </div>

          <?php if($success_msg): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_msg; ?></span>
          </div>
          <?php endif; ?>

          <?php if($error_msg): ?>
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_msg; ?></span>
          </div>
          <?php endif; ?>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-user-secret"></i>
                Hacker Alias
              </label>
              <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>

            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-envelope"></i>
                Secure Email
              </label>
              <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>

            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-satellite-dish"></i>
                Comms Link (Phone)
              </label>
              <input type="text" name="phone" id="phoneInput" class="form-input"
                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" disabled placeholder="Not configured">
            </div>

            <div class="form-group">
              <label class="form-label">
                <i class="fas fa-location-dot"></i>
                Base Coordinates
              </label>
              <input type="text" name="address" id="addressInput" class="form-input"
                value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" disabled placeholder="Not configured">
            </div>
          </div>

          <button type="submit" name="update_profile" id="saveBtn" class="save-btn">
            <i class="fas fa-floppy-disk"></i>
            Secure Changes
          </button>
          </form>
        </div>

        <!-- Danger Zone -->
        <div class="danger-card">
          <div class="danger-icon">
            <i class="fas fa-biohazard"></i>
          </div>
          <h4 class="danger-title">Self-Destruct Sequence</h4>
          <p class="danger-text">Terminate all entity data. This action is final and cannot be reversed.</p>

          <div class="danger-actions">
            <form action="delete_account.php" method="POST"
              onsubmit="return confirm('⚠️ WARNING: Initiate self-destruct? All data will be permanently lost.');"
              style="margin: 0;">
              <button type="submit" class="btn-danger">
                <i class="fas fa-radiation"></i>
                Delete Account
              </button>
            </form>
            <a href="logout.php" class="btn-logout">
              <i class="fas fa-power-off"></i>
              Disconnect
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  let isEditing = false;

  function toggleEdit() {
    isEditing = !isEditing;
    const phoneInput = document.getElementById('phoneInput');
    const addressInput = document.getElementById('addressInput');
    const uploadBtn = document.getElementById('uploadBtn');
    const saveBtn = document.getElementById('saveBtn');
    const editBtn = document.getElementById('editToggleBtn');

    if (isEditing) {
      // Enable editing
      phoneInput.disabled = false;
      addressInput.disabled = false;
      uploadBtn.classList.add('active');
      saveBtn.classList.add('visible');
      editBtn.classList.add('active');
      editBtn.innerHTML = '<i class="fas fa-times"></i> <span>Cancel</span>';

      // Focus first field
      phoneInput.focus();
      phoneInput.style.borderColor = 'var(--neon-green)';
      setTimeout(() => {
        phoneInput.style.borderColor = '';
      }, 1000);
    } else {
      // Disable editing
      phoneInput.disabled = true;
      addressInput.disabled = true;
      uploadBtn.classList.remove('active');
      saveBtn.classList.remove('visible');
      editBtn.classList.remove('active');
      editBtn.innerHTML = '<i class="fas fa-edit"></i> <span>Edit Profile</span>';

      // Reset form
      document.getElementById('profileForm').reset();
      document.getElementById('avatarPreview').src =
        '../assets/images/profiles/<?php echo htmlspecialchars($user['profile_pic']); ?>';
    }
  }

  function previewImage(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        document.getElementById('avatarPreview').src = e.target.result;
      }
      reader.readAsDataURL(input.files[0]);

      // Auto-enable edit mode if not already
      if (!isEditing) {
        toggleEdit();
      }
    }
  }

  // Animate XP bar on load
  document.addEventListener('DOMContentLoaded', function() {
    const xpBar = document.querySelector('.xp-bar');
    const finalWidth = xpBar.style.width;
    xpBar.style.width = '0%';
    setTimeout(() => {
      xpBar.style.width = finalWidth;
    }, 500);
  });
  </script>
</body>

</html>