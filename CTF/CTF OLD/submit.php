<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db_ctf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/functions.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/CTF/ctf_session.php';

$message = '';
$message_type = '';

// Handle flag submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_flag'])) {
  $flag_code = trim($_POST['flag_code']);
  $vulnerability_type = $_POST['vulnerability_type'] ?? '';
  $endpoint = $_POST['endpoint'] ?? '';
  $payload = $_POST['payload'] ?? '';
  $description = $_POST['description'] ?? '';

  // Check if flag exists
  $stmt = $pdo->prepare("SELECT * FROM flags WHERE flag_code = ?");
  $stmt->execute([$flag_code]);
  $flag = $stmt->fetch();

  if ($flag) {
    // Check if already submitted by this user
    $user_id = is_logged_in() ? $_SESSION['user_id'] : 0;

    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE user_id = ? AND flag_id = ?");
    $stmt->execute([$user_id, $flag['id']]);
    $existing = $stmt->fetch();

    if ($existing) {
      $message = 'You have already submitted this flag!';
      $message_type = 'warning';
    } else {
      // Insert submission
      $stmt = $pdo->prepare("INSERT INTO submissions (user_id, flag_id) VALUES (?, ?)");
      $stmt->execute([$user_id, $flag['id']]);

      // Update user points
      if ($user_id > 0) {
        $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt->execute([$flag['points'], $user_id]);
      }

      // Update flag solved count
      $stmt = $pdo->prepare("UPDATE flags SET solved_count = solved_count + 1 WHERE id = ?");
      $stmt->execute([$flag['id']]);

      $message = 'Flag accepted! +' . $flag['points'] . ' points awarded!';
      $message_type = 'success';
    }
  } else {
    $message = 'Invalid flag. Try harder!';
    $message_type = 'danger';
  }
}

// Get leaderboard
$stmt = $pdo->query("SELECT u.id, u.username, u.points, COUNT(s.id) as flags_solved 
                     FROM users u 
                     LEFT JOIN submissions s ON u.id = s.user_id 
                     WHERE u.role = 'user'
                     GROUP BY u.id 
                     ORDER BY u.points DESC, flags_solved DESC 
                     LIMIT 20");
$leaderboard = $stmt->fetchAll();

// Get all flags with solve status
$user_id = is_logged_in() ? $_SESSION['user_id'] : 0;
$stmt = $pdo->query("SELECT f.*, 
                     (SELECT COUNT(*) FROM submissions WHERE flag_id = f.id AND user_id = $user_id) as solved_by_user,
                     (SELECT COUNT(*) FROM submissions WHERE flag_id = f.id) as total_solves
                     FROM flags f ORDER BY f.points DESC");
$flags = $stmt->fetchAll();

// Get user's submissions
$user_submissions = [];
if (is_logged_in()) {
  $stmt = $pdo->prepare("SELECT s.*, f.flag_code, f.vulnerability_type, f.points as flag_points 
                          FROM submissions s 
                          JOIN flags f ON s.flag_id = f.id 
                          WHERE s.user_id = ? 
                          ORDER BY s.submitted_at DESC");
  $stmt->execute([$user_id]);
  $user_submissions = $stmt->fetchAll();
}

// Calculate stats
$total_flags = count($flags);
$solved_flags = count(array_filter($flags, function ($f) {
  return $f['solved_by_user'] > 0;
}));
$total_points = array_sum(array_column($user_submissions, 'flag_points'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Flag | DarkHunter CTF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@300;400;600;700&display=swap"
    rel="stylesheet">
  <style>
  :root {
    --bg-primary: #0a0a0f;
    --bg-secondary: #12121a;
    --bg-card: #1a1a2e;
    --bg-hover: #252542;
    --accent-cyan: #00f0ff;
    --accent-green: #00ff88;
    --accent-red: #ff3366;
    --accent-purple: #a855f7;
    --accent-orange: #ff8800;
    --text-primary: #e2e8f0;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
    --border-color: #2d2d44;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    background: var(--bg-primary);
    color: var(--text-primary);
    font-family: 'Inter', sans-serif;
  }

  .navbar {
    background: rgba(10, 10, 15, 0.95) !important;
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border-color);
    padding: 1rem 0;
  }

  .navbar-brand {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    font-size: 1.5rem;
    color: var(--accent-cyan) !important;
  }

  .nav-link {
    color: var(--text-secondary) !important;
    font-weight: 500;
    transition: all 0.3s;
  }

  .nav-link:hover,
  .nav-link.active {
    color: var(--accent-cyan) !important;
  }

  /* Hero */
  .submit-hero {
    background: linear-gradient(135deg, rgba(0, 255, 136, 0.05), rgba(0, 240, 255, 0.05));
    border-bottom: 1px solid var(--border-color);
    padding: 60px 0 40px;
    text-align: center;
  }

  .submit-hero h1 {
    font-family: 'JetBrains Mono', monospace;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
  }

  .submit-hero p {
    color: var(--text-secondary);
  }

  /* Stats Bar */
  .stats-bar {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
  }

  .stat-item {
    text-align: center;
  }

  .stat-item .value {
    font-family: 'JetBrains Mono', monospace;
    font-size: 2rem;
    font-weight: 700;
  }

  .stat-item .label {
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .stat-solved {
    color: var(--accent-green);
  }

  .stat-total {
    color: var(--accent-cyan);
  }

  .stat-points {
    color: var(--accent-purple);
  }

  /* Cards */
  .card-dark {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
  }

  .card-dark h3 {
    font-family: 'JetBrains Mono', monospace;
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .card-dark h3 i {
    color: var(--accent-cyan);
  }

  /* Form */
  .form-group {
    margin-bottom: 1.5rem;
  }

  .form-label {
    display: block;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    font-weight: 500;
  }

  .form-control-dark {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 0.75rem 1rem;
    border-radius: 10px;
    width: 100%;
    transition: all 0.3s;
    font-family: 'JetBrains Mono', monospace;
  }

  .form-control-dark:focus {
    outline: none;
    border-color: var(--accent-green);
    box-shadow: 0 0 15px rgba(0, 255, 136, 0.1);
  }

  .form-control-dark::placeholder {
    color: var(--text-muted);
  }

  textarea.form-control-dark {
    min-height: 100px;
    resize: vertical;
    font-family: 'Inter', sans-serif;
  }

  select.form-control-dark {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
  }

  .btn-submit {
    background: linear-gradient(135deg, var(--accent-green), var(--accent-cyan));
    border: none;
    color: var(--bg-primary);
    font-weight: 700;
    padding: 1rem 2rem;
    border-radius: 10px;
    transition: all 0.3s;
    cursor: pointer;
    width: 100%;
    font-size: 1.1rem;
  }

  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 255, 136, 0.3);
  }

  /* Alert */
  .alert {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
  }

  .alert-success {
    background: rgba(0, 255, 136, 0.1);
    color: var(--accent-green);
    border: 1px solid rgba(0, 255, 136, 0.2);
  }

  .alert-danger {
    background: rgba(255, 51, 102, 0.1);
    color: var(--accent-red);
    border: 1px solid rgba(255, 51, 102, 0.2);
  }

  .alert-warning {
    background: rgba(255, 136, 0, 0.1);
    color: var(--accent-orange);
    border: 1px solid rgba(255, 136, 0, 0.2);
  }

  /* Leaderboard */
  .leaderboard-table {
    width: 100%;
    color: var(--text-secondary);
  }

  .leaderboard-table th {
    color: var(--accent-cyan);
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
  }

  .leaderboard-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
  }

  .leaderboard-table tr:hover td {
    background: var(--bg-hover);
  }

  .rank-1 {
    color: #ffd700;
    font-weight: 700;
  }

  .rank-2 {
    color: #c0c0c0;
    font-weight: 700;
  }

  .rank-3 {
    color: #cd7f32;
    font-weight: 700;
  }

  /* Flags List */
  .flag-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 10px;
    margin-bottom: 0.75rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s;
  }

  .flag-item:hover {
    border-color: var(--accent-cyan);
  }

  .flag-item.solved {
    border-color: var(--accent-green);
    background: rgba(0, 255, 136, 0.05);
  }

  .flag-info h5 {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.95rem;
    margin: 0 0 0.25rem;
    color: var(--text-primary);
  }

  .flag-info p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-muted);
  }

  .flag-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .flag-points {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    color: var(--accent-green);
  }

  .flag-status {
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .status-solved {
    background: rgba(0, 255, 136, 0.15);
    color: var(--accent-green);
  }

  .status-unsolved {
    background: rgba(255, 51, 102, 0.15);
    color: var(--accent-red);
  }

  .difficulty-badge {
    padding: 0.2rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
  }

  .diff-easy {
    background: rgba(0, 255, 136, 0.15);
    color: var(--accent-green);
  }

  .diff-medium {
    background: rgba(255, 136, 0, 0.15);
    color: var(--accent-orange);
  }

  .diff-hard {
    background: rgba(255, 51, 102, 0.15);
    color: var(--accent-red);
  }

  /* Progress Bar */
  .progress-container {
    background: var(--bg-secondary);
    border-radius: 50px;
    height: 8px;
    overflow: hidden;
    margin-top: 0.5rem;
  }

  .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--accent-green), var(--accent-cyan));
    border-radius: 50px;
    transition: width 0.5s;
  }

  /* Footer */
  footer {
    background: var(--bg-secondary);
    border-top: 1px solid var(--border-color);
    padding: 2rem 0;
    margin-top: 3rem;
  }

  footer p {
    color: var(--text-muted);
    text-align: center;
    margin: 0;
  }

  /* Scrollbar */
  ::-webkit-scrollbar {
    width: 8px;
  }

  ::-webkit-scrollbar-track {
    background: var(--bg-primary);
  }

  ::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 4px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: var(--accent-cyan);
  }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-bug"></i> DarkHunter</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
          <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          <li class="nav-item"><a class="nav-link active" href="submit.php">Submit Flag</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="submit-hero">
    <div class="container">
      <h1><i class="fas fa-flag"></i> Submit Flag</h1>
      <p>Found a vulnerability? Submit the flag and claim your points!</p>

      <div class="stats-bar">
        <div class="stat-item">
          <div class="value stat-solved"><?php echo $solved_flags; ?></div>
          <div class="label">Solved</div>
        </div>
        <div class="stat-item">
          <div class="value stat-total"><?php echo $total_flags; ?></div>
          <div class="label">Total Flags</div>
        </div>
        <div class="stat-item">
          <div class="value stat-points"><?php echo $total_points; ?></div>
          <div class="label">Your Points</div>
        </div>
      </div>

      <div style="max-width: 400px; margin: 1.5rem auto 0;">
        <div
          style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
          <span>Progress</span>
          <span><?php echo $total_flags > 0 ? round(($solved_flags / $total_flags) * 100) : 0; ?>%</span>
        </div>
        <div class="progress-container">
          <div class="progress-bar"
            style="width: <?php echo $total_flags > 0 ? ($solved_flags / $total_flags) * 100 : 0; ?>%;"></div>
        </div>
      </div>
    </div>
  </section>

  <div class="container" style="padding: 2rem 0;">
    <div class="row">
      <div class="col-lg-5">
        <!-- Flag Submission Form -->
        <div class="card-dark">
          <h3><i class="fas fa-paper-plane"></i> Submit Your Flag</h3>

          <?php if ($message): ?>
          <div class="alert alert-<?php echo $message_type; ?>">
            <i
              class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-circle' : 'times-circle'); ?>"></i>
            <?php echo $message; ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="submit.php">
            <div class="form-group">
              <label class="form-label">Flag Code</label>
              <input type="text" name="flag_code" class="form-control-dark" placeholder="DH{...}" required>
              <small style="color: var(--text-muted);">Format: DH{vulnerability_name}</small>
            </div>

            <div class="form-group">
              <label class="form-label">Vulnerability Type</label>
              <select name="vulnerability_type" class="form-control-dark">
                <option value="">Select type...</option>
                <option value="XSS">Cross-Site Scripting (XSS)</option>
                <option value="SQL Injection">SQL Injection</option>
                <option value="IDOR">Insecure Direct Object Reference (IDOR)</option>
                <option value="JWT">JWT Issues</option>
                <option value="File Upload">File Upload</option>
                <option value="SSRF">Server-Side Request Forgery (SSRF)</option>
                <option value="CORS">CORS Misconfiguration</option>
                <option value="CSRF">Cross-Site Request Forgery (CSRF)</option>
                <option value="Information Disclosure">Information Disclosure</option>
                <option value="Authentication Bypass">Authentication Bypass</option>
                <option value="Open Redirect">Open Redirect</option>
                <option value="Mass Assignment">Mass Assignment</option>
                <option value="HTML Injection">HTML Injection</option>
                <option value="Clickjacking">Clickjacking</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Endpoint</label>
              <input type="text" name="endpoint" class="form-control-dark" placeholder="e.g., shop.php, account.php">
            </div>

            <div class="form-group">
              <label class="form-label">Payload (Optional)</label>
              <textarea name="payload" class="form-control-dark" placeholder="Enter the payload you used..."></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Description (Optional)</label>
              <textarea name="description" class="form-control-dark"
                placeholder="Describe how you found and exploited this vulnerability..."></textarea>
            </div>

            <button type="submit" name="submit_flag" class="btn-submit">
              <i class="fas fa-flag"></i> Submit Flag
            </button>
          </form>
        </div>

        <!-- User Submissions -->
        <?php if (is_logged_in() && !empty($user_submissions)): ?>
        <div class="card-dark">
          <h3><i class="fas fa-history"></i> Your Submissions</h3>
          <table class="leaderboard-table">
            <thead>
              <tr>
                <th>Flag</th>
                <th style="text-align: right;">Points</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($user_submissions as $sub): ?>
              <tr>
                <td style="font-family: JetBrains Mono; color: var(--accent-green); font-size: 0.85rem;">
                  <?php echo $sub['flag_code']; ?>
                </td>
                <td style="text-align: right; color: var(--accent-green); font-weight: 700;">
                  +<?php echo $sub['flag_points']; ?>
                </td>
                <td style="font-size: 0.85rem;">
                  <?php echo date('M d, H:i', strtotime($sub['submitted_at'])); ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <div class="col-lg-7">
        <!-- Available Flags -->
        <div class="card-dark">
          <h3><i class="fas fa-list"></i> Available Flags</h3>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Find these flags hidden across the platform. Each represents a different vulnerability type.
          </p>

          <?php foreach ($flags as $flag): ?>
          <div class="flag-item <?php echo $flag['solved_by_user'] > 0 ? 'solved' : ''; ?>">
            <div class="flag-info">
              <h5>
                <?php if ($flag['solved_by_user'] > 0): ?>
                <i class="fas fa-check-circle" style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                <?php echo $flag['flag_code']; ?>
                <?php else: ?>
                <i class="fas fa-lock" style="color: var(--text-muted); margin-right: 0.5rem;"></i>
                <span style="color: var(--text-muted);">DH{???????????????}</span>
                <?php endif; ?>
              </h5>
              <p>
                <?php echo $flag['vulnerability_type']; ?>
                <span style="margin: 0 0.5rem;">|</span>
                <?php echo $flag['endpoint']; ?>
                <span style="margin: 0 0.5rem;">|</span>
                <?php echo $flag['total_solves']; ?> solves
              </p>
            </div>
            <div class="flag-meta">
              <span class="flag-points">+<?php echo $flag['points']; ?></span>
              <span
                class="difficulty-badge 
                                    <?php echo $flag['points'] <= 100 ? 'diff-easy' : ($flag['points'] <= 150 ? 'diff-medium' : 'diff-hard'); ?>">
                <?php echo $flag['points'] <= 100 ? 'Easy' : ($flag['points'] <= 150 ? 'Medium' : 'Hard'); ?>
              </span>
              <span
                class="flag-status <?php echo $flag['solved_by_user'] > 0 ? 'status-solved' : 'status-unsolved'; ?>">
                <?php echo $flag['solved_by_user'] > 0 ? 'Solved' : 'Unsolved'; ?>
              </span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Leaderboard -->
        <div class="card-dark">
          <h3><i class="fas fa-trophy"></i> Leaderboard</h3>
          <table class="leaderboard-table">
            <thead>
              <tr>
                <th style="width: 60px;">Rank</th>
                <th>Hacker</th>
                <th style="width: 100px; text-align: right;">Points</th>
                <th style="width: 100px; text-align: right;">Flags</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $rank = 1;
              foreach ($leaderboard as $hacker):
              ?>
              <tr>
                <td>
                  <?php if ($rank <= 3): ?>
                  <span class="rank-<?php echo $rank; ?>">
                    <i class="fas fa-crown"></i> <?php echo $rank; ?>
                  </span>
                  <?php else: ?>
                  <span style="color: var(--text-muted);">#<?php echo $rank; ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <i class="fas fa-user-secret" style="color: var(--accent-cyan); margin-right: 0.5rem;"></i>
                  <?php echo htmlspecialchars($hacker['username']); ?>
                </td>
                <td
                  style="text-align: right; font-family: JetBrains Mono; color: var(--accent-green); font-weight: 700;">
                  <?php echo $hacker['points']; ?>
                </td>
                <td style="text-align: right; font-family: JetBrains Mono;">
                  <?php echo $hacker['flags_solved']; ?>/<?php echo $total_flags; ?>
                </td>
              </tr>
              <?php $rank++;
              endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>DarkHunter CTF Platform - Educational Use Only</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>