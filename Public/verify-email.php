<?php
/**
 * DarkHunter - Email Verification Page
 * Integrated Logic with Modern UI
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');

$verified = false;
$error = '';
$loginUrl = "login.php";

// --- START LOGIC ---
if (!isset($_GET['token']) || empty($_GET['token'])) {
  $error = "Invalid verification link. Access denied.";
} else {
  $token = $_GET['token'];

  // 🔍 نجيب اليوزر من التوكن
  $stmt = $pdo->prepare("
        SELECT id, token_expiry 
        FROM users 
        WHERE verification_token = ? AND is_verified = 0
    ");
  $stmt->execute([$token]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    $error = "This link is invalid or your account might already be verified.";
  } else {

    // ✅ check expiry صح
    if (empty($user['token_expiry']) || strtotime($user['token_expiry']) < time()) {
      $error = "The verification link has expired. Please resend a new one.";
    } else {

      // ✅ activate account
      $update = $pdo->prepare("
                UPDATE users 
                SET is_verified = 1, verification_token = NULL, token_expiry = NULL 
                WHERE id = ?
            ");

      if ($update->execute([$user['id']])) {
        $verified = true;
      } else {
        $error = "System error during activation.";
      }
    }
  }
}
// --- END LOGIC ---
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verified | DarkHunter Security</title>
  <link rel="stylesheet" type="text/css" href="/DarkHunter/Public/css/verify-email.css">

</head>

<body>
  <div class="particles">
    <div class="particle" style="left: 10%; top: 20%;"></div>
    <div class="particle" style="left: 80%; top: 70%;"></div>
  </div>

  <div class="container">
    <div class="card <?php echo !$verified ? 'error' : ''; ?>">

      <div class="icon-wrapper">
        <svg class="checkmark" viewBox="0 0 24 24">
          <?php if ($verified): ?>
          <polyline points="20 6 9 17 4 12"></polyline>
          <?php else: ?>
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
          <?php endif; ?>
        </svg>
      </div>

      <h1 class="title">
        <?php echo $verified ? 'Identity Verified' : 'Access Denied'; ?>
      </h1>

      <p class="subtitle">
        <?php echo $verified ? "Your neural link is established. Welcome to the elite circle of DarkHunter." : $error; ?>
      </p>

      <?php if ($verified): ?>
      <div class="redirect-notice">
        <div style="color:#3b82f6; font-size:14px; margin-bottom:8px;">Redirecting to terminal in 3 seconds...</div>
        <div class="progress-bar">
          <div class="progress-fill"></div>
        </div>
      </div>
      <?php endif; ?>

      <a href="<?php echo $loginUrl; ?>" class="btn">
        <?php echo $verified ? 'Enter Dashboard' : 'Retry Registration'; ?>
      </a>

      <div class="brand">
        <span>👾 DARK HUNTER</span>
      </div>
    </div>
  </div>

  <?php if ($verified): ?>
  <script>
  setTimeout(function() {
    window.location.href = '<?php echo $loginUrl; ?>';
  }, 3000);
  </script>
  <?php endif; ?>
</body>

</html>