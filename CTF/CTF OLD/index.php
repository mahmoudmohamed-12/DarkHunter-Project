<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db_ctf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/auth.php');
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$isStrictAuth = false;

// VULNERABILITY: Open redirect in return parameter
// FLAG: DH{open_redirect}
if (isset($_GET['redirect'])) {
  header("Location: " . $_GET['redirect']);
  exit;
}

// Get leaderboard data
$stmt = $ctf_pdo->query("SELECT username, points FROM users WHERE role = 'user' ORDER BY points DESC LIMIT 10");
$leaderboard = $stmt->fetchAll();

// Get stats
$stmt = $ctf_pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];

$stmt = $ctf_pdo->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $stmt->fetch()['total_products'];

$stmt = $ctf_pdo->query("SELECT COUNT(*) as total_flags FROM flags");
$total_flags = $stmt->fetch()['total_flags'];

$stmt = $ctf_pdo->query("SELECT COUNT(*) as solved FROM submissions");
$solved = $stmt->fetch()['solved'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DarkHunter | CTF Bug Bounty Training Platform</title>
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
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Animated background */
    .bg-grid {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image:
        linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
      background-size: 50px 50px;
      pointer-events: none;
      z-index: 0;
    }

    .bg-glow {
      position: fixed;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      filter: blur(150px);
      opacity: 0.15;
      pointer-events: none;
      z-index: 0;
    }

    .glow-1 {
      top: -200px;
      left: -200px;
      background: var(--accent-cyan);
    }

    .glow-2 {
      bottom: -200px;
      right: -200px;
      background: var(--accent-purple);
    }

    /* Navbar */
    .navbar {
      background: rgba(10, 10, 15, 0.95) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 0;
      position: relative;
      z-index: 100;
    }

    .navbar-brand {
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--accent-cyan) !important;
      text-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
    }

    .navbar-brand i {
      margin-right: 0.5rem;
    }

    .nav-link {
      color: var(--text-secondary) !important;
      font-weight: 500;
      padding: 0.5rem 1rem !important;
      transition: all 0.3s;
      position: relative;
    }

    .nav-link:hover {
      color: var(--accent-cyan) !important;
    }

    .nav-link.active {
      color: var(--accent-cyan) !important;
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 1rem;
      right: 1rem;
      height: 2px;
      background: var(--accent-cyan);
      box-shadow: 0 0 10px var(--accent-cyan);
    }

    /* Hero Section */
    .hero {
      position: relative;
      z-index: 1;
      padding: 120px 0 80px;
      text-align: center;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(0, 240, 255, 0.1);
      border: 1px solid rgba(0, 240, 255, 0.3);
      color: var(--accent-cyan);
      padding: 0.5rem 1.5rem;
      border-radius: 50px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.85rem;
      margin-bottom: 2rem;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(0, 240, 255, 0.3);
      }

      50% {
        box-shadow: 0 0 0 10px rgba(0, 240, 255, 0);
      }
    }

    .hero h1 {
      font-family: 'JetBrains Mono', monospace;
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero p.lead {
      color: var(--text-secondary);
      font-size: 1.25rem;
      max-width: 700px;
      margin: 0 auto 2rem;
    }

    .hero-stats {
      display: flex;
      justify-content: center;
      gap: 3rem;
      margin-bottom: 3rem;
    }

    .hero-stat {
      text-align: center;
    }

    .hero-stat .number {
      font-family: 'JetBrains Mono', monospace;
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--accent-green);
    }

    .hero-stat .label {
      color: var(--text-muted);
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .btn-hero {
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      border: none;
      color: var(--bg-primary);
      font-weight: 700;
      padding: 1rem 3rem;
      border-radius: 50px;
      font-size: 1.1rem;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-block;
    }

    .btn-hero:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 40px rgba(0, 240, 255, 0.3);
      color: var(--bg-primary);
    }

    /* Section Styling */
    .section {
      position: relative;
      z-index: 1;
      padding: 80px 0;
    }

    .section-title {
      font-family: 'JetBrains Mono', monospace;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .section-title i {
      color: var(--accent-cyan);
    }

    .section-subtitle {
      color: var(--text-secondary);
      margin-bottom: 3rem;
      max-width: 600px;
    }

    /* Cards */
    .card-dark {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 2rem;
      transition: all 0.3s;
      height: 100%;
    }

    .card-dark:hover {
      border-color: var(--accent-cyan);
      transform: translateY(-5px);
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .card-dark .icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .card-dark h3 {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.25rem;
      margin-bottom: 0.75rem;
    }

    .card-dark p {
      color: var(--text-secondary);
      font-size: 0.95rem;
    }

    .card-dark .difficulty {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-top: 1rem;
    }

    .difficulty-easy {
      background: rgba(0, 255, 136, 0.15);
      color: var(--accent-green);
    }

    .difficulty-medium {
      background: rgba(255, 136, 0, 0.15);
      color: var(--accent-orange);
    }

    .difficulty-hard {
      background: rgba(255, 51, 102, 0.15);
      color: var(--accent-red);
    }

    /* Terminal Section */
    .terminal {
      background: #0d0d15;
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 1.5rem;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.9rem;
      overflow-x: auto;
    }

    .terminal-header {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .terminal-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
    }

    .dot-red {
      background: var(--accent-red);
    }

    .dot-yellow {
      background: #ffcc00;
    }

    .dot-green {
      background: var(--accent-green);
    }

    .terminal-line {
      color: var(--text-secondary);
      margin-bottom: 0.5rem;
    }

    .terminal-line .prompt {
      color: var(--accent-green);
    }

    .terminal-line .command {
      color: var(--accent-cyan);
    }

    .terminal-line .output {
      color: var(--text-primary);
    }

    .terminal-line .comment {
      color: var(--text-muted);
    }

    /* Leaderboard */
    .leaderboard-table {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      overflow: hidden;
    }

    .leaderboard-table th {
      background: var(--bg-secondary);
      color: var(--accent-cyan);
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      padding: 1rem;
      border-bottom: 1px solid var(--border-color);
    }

    .leaderboard-table td {
      padding: 1rem;
      border-bottom: 1px solid var(--border-color);
      color: var(--text-secondary);
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

    /* Warning Banner */
    .warning-banner {
      background: linear-gradient(135deg, rgba(255, 51, 102, 0.1), rgba(255, 136, 0, 0.1));
      border: 1px solid rgba(255, 51, 102, 0.3);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 3rem;
    }

    .warning-banner i {
      color: var(--accent-red);
      font-size: 1.5rem;
      margin-right: 1rem;
    }

    .warning-banner h4 {
      color: var(--accent-red);
      font-family: 'JetBrains Mono', monospace;
      margin-bottom: 0.5rem;
    }

    .warning-banner p {
      color: var(--text-secondary);
      margin: 0;
    }

    /* Footer */
    footer {
      background: var(--bg-secondary);
      border-top: 1px solid var(--border-color);
      padding: 3rem 0;
      position: relative;
      z-index: 1;
    }

    footer p {
      color: var(--text-muted);
      text-align: center;
      margin: 0;
    }

    footer .footer-links {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin-bottom: 1.5rem;
    }

    footer .footer-links a {
      color: var(--text-secondary);
      text-decoration: none;
      transition: color 0.3s;
    }

    footer .footer-links a:hover {
      color: var(--accent-cyan);
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-in {
      animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }

      .hero-stats {
        flex-direction: column;
        gap: 1.5rem;
      }

      .section {
        padding: 50px 0;
      }
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
  <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <!-- Background Effects -->
  <div class="bg-grid"></div>
  <div class="bg-glow glow-1"></div>
  <div class="bg-glow glow-2"></div>

  <!-- Navbar -->


  <?php
  if (isset($_GET['logout'])) {
    logout_user();
    header('Location: index.php');
    exit;
  }
  ?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="hero-badge">
        <i class="fas fa-shield-alt"></i>
        <span>CTF Bug Bounty Training Lab</span>
      </div>
      <h1>DarkHunter</h1>
      <p class="lead">Master the art of ethical hacking. Hunt vulnerabilities, exploit bugs, and sharpen your skills in
        a realistic cybersecurity marketplace environment.</p>

      <div class="hero-stats">
        <div class="hero-stat">
          <div class="number"><?php echo $total_users; ?></div>
          <div class="label">Hackers</div>
        </div>
        <div class="hero-stat">
          <div class="number"><?php echo $total_products; ?></div>
          <div class="label">Products</div>
        </div>
        <div class="hero-stat">
          <div class="number"><?php echo $total_flags; ?></div>
          <div class="label">Flags</div>
        </div>
        <div class="hero-stat">
          <div class="number"><?php echo $solved; ?></div>
          <div class="label">Solved</div>
        </div>
      </div>

      <a href="shop.php" class="btn-hero"><i class="fas fa-rocket"></i> Start Hacking</a>
    </div>
  </section>

  <!-- Warning Banner -->
  <section class="section" style="padding-top: 0;">
    <div class="container">
      <div class="warning-banner">
        <div class="d-flex align-items-start">
          <i class="fas fa-exclamation-triangle"></i>
          <div>
            <h4><i class="fas fa-terminal"></i> EDUCATIONAL DISCLAIMER</h4>
            <p>DarkHunter is an <strong>intentionally vulnerable</strong> web application designed for cybersecurity
              training and CTF competitions. All vulnerabilities are synthetic and exist solely for educational
              purposes. <strong>Never attempt these techniques on real systems without explicit authorization.</strong>
              This platform is designed to teach defensive and offensive security concepts in a safe, controlled
              environment.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="section" style="padding-top: 0;">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-info-circle"></i> What is DarkHunter?</h2>
      <p class="section-subtitle">DarkHunter is a realistic vulnerable web application that simulates a dark web
        cybersecurity marketplace called "DarkShop". Your mission is to find and exploit security vulnerabilities hidden
        throughout the platform.</p>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(0,240,255,0.1); color: var(--accent-cyan);">
              <i class="fas fa-search"></i>
            </div>
            <h3>Reconnaissance</h3>
            <p>Explore the platform, analyze requests, inspect source code, and map the attack surface. Every page,
              parameter, and interaction is a potential entry point.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(0,255,136,0.1); color: var(--accent-green);">
              <i class="fas fa-bug"></i>
            </div>
            <h3>Exploitation</h3>
            <p>Identify vulnerabilities and craft payloads to exploit them. From SQL injection to XSS, each bug requires
              a different approach and mindset.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(168,85,247,0.1); color: var(--accent-purple);">
              <i class="fas fa-flag"></i>
            </div>
            <h3>Submit Flags</h3>
            <p>When you successfully exploit a vulnerability, you'll discover a flag. Submit it on the Submit Flag page
              to earn points and climb the leaderboard.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Vulnerability Categories -->
  <section class="section">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-crosshairs"></i> Vulnerability Categories</h2>
      <p class="section-subtitle">DarkHunter contains <?php echo $total_flags; ?> intentionally planted vulnerabilities
        across multiple categories. Can you find them all?</p>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,51,102,0.1); color: var(--accent-red);">
              <i class="fas fa-code"></i>
            </div>
            <h3>Cross-Site Scripting (XSS)</h3>
            <p>Inject malicious scripts into web pages viewed by other users. Includes both stored and reflected XSS
              vulnerabilities in reviews and search functionality.</p>
            <span class="difficulty difficulty-easy">Easy</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,51,102,0.1); color: var(--accent-red);">
              <i class="fas fa-database"></i>
            </div>
            <h3>SQL Injection</h3>
            <p>Manipulate database queries through user input. Find blind SQL injection in search parameters and extract
              sensitive data from the database.</p>
            <span class="difficulty difficulty-medium">Medium</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,136,0,0.1); color: var(--accent-orange);">
              <i class="fas fa-user-secret"></i>
            </div>
            <h3>Insecure Direct Object Reference (IDOR)</h3>
            <p>Access resources by manipulating identifiers. View other users' orders, profiles, and hidden products by
              changing IDs in requests.</p>
            <span class="difficulty difficulty-medium">Medium</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,136,0,0.1); color: var(--accent-orange);">
              <i class="fas fa-key"></i>
            </div>
            <h3>JWT & Authentication</h3>
            <p>Exploit weak JWT implementation, predictable tokens, and authentication bypass mechanisms. Tokens are
              stored insecurely and use weak secrets.</p>
            <span class="difficulty difficulty-medium">Medium</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,136,0,0.1); color: var(--accent-orange);">
              <i class="fas fa-upload"></i>
            </div>
            <h3>File Upload</h3>
            <p>Upload malicious files to achieve Remote Code Execution. Weak file type validation and predictable
              filenames make this vulnerability exploitable.</p>
            <span class="difficulty difficulty-hard">Hard</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(168,85,247,0.1); color: var(--accent-purple);">
              <i class="fas fa-server"></i>
            </div>
            <h3>Server-Side Request Forgery (SSRF)</h3>
            <p>Force the server to make requests to internal resources. The admin import feature can be abused to access
              internal services and files.</p>
            <span class="difficulty difficulty-hard">Hard</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(0,255,136,0.1); color: var(--accent-green);">
              <i class="fas fa-globe"></i>
            </div>
            <h3>CORS Misconfiguration</h3>
            <p>Exploit permissive CORS policies to steal data from authenticated sessions. The API allows cross-origin
              requests from any domain with credentials.</p>
            <span class="difficulty difficulty-easy">Easy</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(0,255,136,0.1); color: var(--accent-green);">
              <i class="fas fa-shield-virus"></i>
            </div>
            <h3>Cross-Site Request Forgery (CSRF)</h3>
            <p>Perform unauthorized actions on behalf of authenticated users. Profile updates lack CSRF tokens, allowing
              attackers to change user data.</p>
            <span class="difficulty difficulty-easy">Easy</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(0,240,255,0.1); color: var(--accent-cyan);">
              <i class="fas fa-eye"></i>
            </div>
            <h3>Information Disclosure</h3>
            <p>Find sensitive information leaked through debug comments, error messages, source code, logs, and
              metadata. Secrets are hidden in plain sight.</p>
            <span class="difficulty difficulty-easy">Easy</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(0,240,255,0.1); color: var(--accent-cyan);">
              <i class="fas fa-mouse-pointer"></i>
            </div>
            <h3>Clickjacking</h3>
            <p>Trick users into clicking hidden elements. Missing X-Frame-Options headers allow the site to be embedded
              in malicious iframes.</p>
            <span class="difficulty difficulty-easy">Easy</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,136,0,0.1); color: var(--accent-orange);">
              <i class="fas fa-random"></i>
            </div>
            <h3>Open Redirect</h3>
            <p>Redirect users to malicious websites. The login and logout flows accept arbitrary redirect URLs without
              validation.</p>
            <span class="difficulty difficulty-easy">Easy</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark">
            <div class="icon" style="background: rgba(255,51,102,0.1); color: var(--accent-red);">
              <i class="fas fa-cogs"></i>
            </div>
            <h3>Mass Assignment</h3>
            <p>Modify restricted fields by sending unexpected parameters. Profile updates don't whitelist fields,
              allowing role escalation and data manipulation.</p>
            <span class="difficulty difficulty-medium">Medium</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How to Play -->
  <section class="section">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-gamepad"></i> How to Play</h2>
      <p class="section-subtitle">Follow these steps to start your bug hunting journey on DarkHunter.</p>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="terminal">
            <div class="terminal-header">
              <div class="terminal-dot dot-red"></div>
              <div class="terminal-dot dot-yellow"></div>
              <div class="terminal-dot dot-green"></div>
              <span style="margin-left: auto; color: var(--text-muted); font-size: 0.8rem;">darkhunter@lab:~</span>
            </div>
            <div class="terminal-line">
              <span class="prompt">$</span> <span class="command">register</span> <span class="comment"># Create an
                account</span>
            </div>
            <div class="terminal-line">
              <span class="output">Account created successfully. Welcome, hacker!</span>
            </div>
            <div class="terminal-line">
              <span class="prompt">$</span> <span class="command">explore</span> <span class="comment"># Browse the
                shop</span>
            </div>
            <div class="terminal-line">
              <span class="output">Found 12 products. 2 hidden products detected...</span>
            </div>
            <div class="terminal-line">
              <span class="prompt">$</span> <span class="command">inspect</span> <span class="comment"># Check source
                code</span>
            </div>
            <div class="terminal-line">
              <span class="output">Interesting comments found in HTML...</span>
            </div>
            <div class="terminal-line">
              <span class="prompt">$</span> <span class="command">exploit</span> <span class="comment"># Try
                payloads</span>
            </div>
            <div class="terminal-line">
              <span class="output">Payload executed! Flag found: DH{...}</span>
            </div>
            <div class="terminal-line">
              <span class="prompt">$</span> <span class="command">submit</span> <span class="comment"># Submit your
                flag</span>
            </div>
            <div class="terminal-line">
              <span class="output">Flag accepted! +150 points awarded.</span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card-dark" style="height: 100%;">
            <h3 style="color: var(--accent-cyan); margin-bottom: 1.5rem;"><i class="fas fa-list-ol"></i> Step-by-Step
              Guide</h3>
            <div style="margin-bottom: 1.5rem;">
              <h5 style="color: var(--accent-green);">1. Create an Account</h5>
              <p style="color: var(--text-secondary); font-size: 0.9rem;">Register on the Account page with any username
                and password. No email verification required.</p>
            </div>
            <div style="margin-bottom: 1.5rem;">
              <h5 style="color: var(--accent-green);">2. Explore the Platform</h5>
              <p style="color: var(--text-secondary); font-size: 0.9rem;">Browse the Shop page, view products, read
                reviews, and interact with all features. Pay attention to URLs, parameters, and responses.</p>
            </div>
            <div style="margin-bottom: 1.5rem;">
              <h5 style="color: var(--accent-green);">3. Inspect Everything</h5>
              <p style="color: var(--text-secondary); font-size: 0.9rem;">Check page source, network requests, cookies,
                localStorage, and JavaScript files. Hidden clues are everywhere.</p>
            </div>
            <div style="margin-bottom: 1.5rem;">
              <h5 style="color: var(--accent-green);">4. Exploit Vulnerabilities</h5>
              <p style="color: var(--text-secondary); font-size: 0.9rem;">Craft payloads, manipulate parameters, and
                bypass controls. Each vulnerability reveals a hidden flag.</p>
            </div>
            <div>
              <h5 style="color: var(--accent-green);">5. Submit Flags</h5>
              <p style="color: var(--text-secondary); font-size: 0.9rem;">Go to the Submit Flag page and enter the flag
                code you discovered. Earn points and rank up!</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Rules -->
  <section class="section">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-gavel"></i> Rules & Guidelines</h2>
      <p class="section-subtitle">Follow these rules to ensure a fair and educational experience for everyone.</p>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="card-dark">
            <div class="d-flex align-items-center mb-3">
              <i class="fas fa-check-circle"
                style="color: var(--accent-green); font-size: 1.5rem; margin-right: 1rem;"></i>
              <h3 style="margin: 0;">Do's</h3>
            </div>
            <ul style="color: var(--text-secondary); padding-left: 1.5rem;">
              <li>Explore all pages and features thoroughly</li>
              <li>Use browser DevTools to inspect elements</li>
              <li>Intercept and modify requests with Burp Suite or similar tools</li>
              <li>Document your findings and methodology</li>
              <li>Share knowledge and help others learn</li>
              <li>Report any unintended bugs to maintainers</li>
              <li>Have fun and learn something new!</li>
            </ul>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card-dark">
            <div class="d-flex align-items-center mb-3">
              <i class="fas fa-times-circle"
                style="color: var(--accent-red); font-size: 1.5rem; margin-right: 1rem;"></i>
              <h3 style="margin: 0;">Don'ts</h3>
            </div>
            <ul style="color: var(--text-secondary); padding-left: 1.5rem;">
              <li>Do not attack the underlying infrastructure</li>
              <li>Do not attempt to access the host system or other users' accounts</li>
              <li>Do not use automated tools that cause denial of service</li>
              <li>Do not delete or corrupt data for other users</li>
              <li>Do not share flags publicly - let others discover them</li>
              <li>Do not use these techniques on real systems without permission</li>
              <li>Do not brute force the platform into unusability</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Difficulty Explanation -->
  <section class="section">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-layer-group"></i> Difficulty Levels</h2>
      <p class="section-subtitle">Vulnerabilities are categorized by difficulty to help you progress at your own pace.
      </p>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card-dark text-center">
            <div
              style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0,255,136,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
              <i class="fas fa-seedling" style="color: var(--accent-green); font-size: 2rem;"></i>
            </div>
            <h3 style="color: var(--accent-green);">Beginner</h3>
            <p style="color: var(--text-secondary);">Easy to find with basic inspection. Perfect for learning
              fundamental concepts. Requires minimal tooling.</p>
            <div style="margin-top: 1rem;">
              <span class="difficulty difficulty-easy">Information Disclosure</span>
              <span class="difficulty difficulty-easy">XSS</span>
              <span class="difficulty difficulty-easy">CSRF</span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark text-center">
            <div
              style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,136,0,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
              <i class="fas fa-fire" style="color: var(--accent-orange); font-size: 2rem;"></i>
            </div>
            <h3 style="color: var(--accent-orange);">Intermediate</h3>
            <p style="color: var(--text-secondary);">Requires understanding of web technologies and some tooling. Good
              for practicing real-world techniques.</p>
            <div style="margin-top: 1rem;">
              <span class="difficulty difficulty-medium">SQL Injection</span>
              <span class="difficulty difficulty-medium">IDOR</span>
              <span class="difficulty difficulty-medium">JWT</span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-dark text-center">
            <div
              style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,51,102,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
              <i class="fas fa-skull" style="color: var(--accent-red); font-size: 2rem;"></i>
            </div>
            <h3 style="color: var(--accent-red);">Advanced</h3>
            <p style="color: var(--text-secondary);">Complex multi-step exploits requiring deep knowledge. Challenge
              yourself with advanced techniques.</p>
            <div style="margin-top: 1rem;">
              <span class="difficulty difficulty-hard">SSRF</span>
              <span class="difficulty difficulty-hard">File Upload RCE</span>
              <span class="difficulty difficulty-hard">Auth Bypass</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Leaderboard Preview -->
  <section class="section">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-trophy"></i> Leaderboard</h2>
      <p class="section-subtitle">Top bug hunters ranked by points earned from flag submissions.</p>

      <div class="leaderboard-table">
        <table class="table" style="margin: 0; color: var(--text-primary);">
          <thead>
            <tr>
              <th style="width: 80px;">Rank</th>
              <th>Hacker</th>
              <th style="width: 150px; text-align: right;">Points</th>
              <th style="width: 150px; text-align: right;">Flags</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $rank = 1;
            foreach ($leaderboard as $hacker):
              $stmt = $ctf_pdo->prepare("SELECT COUNT(*) as count FROM submissions WHERE user_id = (SELECT id FROM users WHERE username = ?)");
              $stmt->execute([$hacker['username']]);
              $flag_count = $stmt->fetch()['count'];
            ?>
              <tr>
                <td>
                  <?php if ($rank <= 3): ?>
                    <span class="rank-<?php echo $rank; ?>"><i class="fas fa-crown"></i> <?php echo $rank; ?></span>
                  <?php else: ?>
                    <span style="color: var(--text-muted);">#<?php echo $rank; ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <i class="fas fa-user-secret" style="color: var(--accent-cyan); margin-right: 0.5rem;"></i>
                  <?php echo htmlspecialchars($hacker['username']); ?>
                </td>
                <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--accent-green);">
                  <?php echo $hacker['points']; ?>
                </td>
                <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">
                  <?php echo $flag_count; ?>/<?php echo $total_flags; ?>
                </td>
              </tr>
            <?php $rank++;
            endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="text-center mt-4">
        <a href="submit.php" class="btn-hero" style="padding: 0.75rem 2rem; font-size: 1rem;">
          <i class="fas fa-flag"></i> View Full Scoreboard
        </a>
      </div>
    </div>
  </section>

  <!-- Tools Section -->
  <section class="section">
    <div class="container">
      <h2 class="section-title"><i class="fas fa-toolbox"></i> Recommended Tools</h2>
      <p class="section-subtitle">These tools will help you discover and exploit vulnerabilities more efficiently.</p>

      <div class="row g-4">
        <div class="col-md-3 col-6">
          <div class="card-dark text-center">
            <i class="fas fa-spider" style="font-size: 2rem; color: var(--accent-cyan); margin-bottom: 1rem;"></i>
            <h5>Burp Suite</h5>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Proxy & Scanner</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card-dark text-center">
            <i class="fas fa-code" style="font-size: 2rem; color: var(--accent-green); margin-bottom: 1rem;"></i>
            <h5>DevTools</h5>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Browser Inspector</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card-dark text-center">
            <i class="fas fa-terminal" style="font-size: 2rem; color: var(--accent-purple); margin-bottom: 1rem;"></i>
            <h5>curl</h5>
            <p style="font-size: 0.85rem; color: var(--text-muted);">HTTP Client</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="card-dark text-center">
            <i class="fas fa-database" style="font-size: 2rem; color: var(--accent-orange); margin-bottom: 1rem;"></i>
            <h5>sqlmap</h5>
            <p style="font-size: 0.85rem; color: var(--text-muted);">SQL Injection</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="section" style="text-align: center;">
    <div class="container">
      <h2 style="font-family: 'JetBrains Mono', monospace; font-size: 2.5rem; margin-bottom: 1rem;">
        Ready to <span style="color: var(--accent-cyan);">Hunt</span>?
      </h2>
      <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 2rem;">
        Join hundreds of hackers in the ultimate bug bounty training experience. Find vulnerabilities, submit flags, and
        become a master of ethical hacking.
      </p>
      <a href="shop.php" class="btn-hero" style="margin-right: 1rem;">
        <i class="fas fa-rocket"></i> Start Hacking
      </a>
      <a href="account.php" class="btn-hero"
        style="background: transparent; border: 2px solid var(--accent-cyan); color: var(--accent-cyan);">
        <i class="fas fa-user-plus"></i> Create Account
      </a>
    </div>
  </section>



  <!-- VULNERABILITY: Hidden debug info in HTML comments -->
  <!-- 
    DEBUG INFO - DarkHunter Internal
    ================================
    Server: Apache/2.4.41
    PHP Version: 8.1.2
    Database: MySQL 8.0.32
    App Version: 1.0.0-beta
    Internal API: http://localhost:8080/internal/
    Admin Panel: http://localhost:8080/admin.php
    JWT Secret: darkhunter_secret_key_123
    API Key: DH_INTERNAL_API_9f8e7d6c5b4a3210
    
    FLAG: DH{information_disclosure}
    
    Note: Remove this comment before production deployment!
    -->

  <!-- VULNERABILITY: Exposed API endpoint -->
  <script>
    // DarkHunter Frontend Configuration
    // VULNERABILITY: API keys and endpoints exposed in client-side JS
    window.DARKHUNTER_CONFIG = {
      apiBaseUrl: 'http://localhost:8080/api/',
      internalApiUrl: 'http://localhost:8080/internal/',
      apiKey: 'DH_FRONTEND_KEY_3a4b5c6d7e8f9g0h',
      jwtSecret: 'darkhunter_secret_key_123',
      debugMode: true,
      adminEmail: 'admin@darkhunter.local'
    };

    // VULNERABILITY: JWT token stored in localStorage
    // FLAG: DH{jwt_token_leak}
    <?php if (isset($_SESSION['jwt_token'])): ?>
      localStorage.setItem('darkhunter_jwt', '<?php echo $_SESSION['jwt_token']; ?>');
      localStorage.setItem('user_id', '<?php echo $_SESSION['user_id'] ?? ''; ?>');
      localStorage.setItem('username', '<?php echo $_SESSION['username'] ?? ''; ?>');
      localStorage.setItem('role', '<?php echo $_SESSION['role'] ?? ''; ?>');
    <?php endif; ?>

    // VULNERABILITY: Debug console messages
    console.log('%c DarkHunter Debug ', 'background: #00f0ff; color: #0a0a0f; font-size: 20px; font-weight: bold;');
    console.log('JWT Token:', localStorage.getItem('darkhunter_jwt'));
    console.log('User Role:', localStorage.getItem('role'));
    console.log('API Key:', window.DARKHUNTER_CONFIG.apiKey);
    console.log('Internal API:', window.DARKHUNTER_CONFIG.internalApiUrl);

    // Hidden flag in JS
    // FLAG: DH{api_key_exposure}
    const hiddenFlag = "DH{api_key_exposure}";
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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