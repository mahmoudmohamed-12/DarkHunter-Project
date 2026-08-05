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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Security Recon Command Library</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --bg-dark: #0d1117;
    --bg-card: #161b22;
    --bg-hover: #1c2128;
    --border: #30363d;
    --text-primary: #c9d1d9;
    --text-secondary: #8b949e;
    --accent-green: #3fb950;
    --accent-cyan: #58a6ff;
    --accent-orange: #f78166;
    --accent-purple: #a371f7;
    --accent-yellow: #d29922;
    --accent-red: #f85149;
    --font-mono: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
    --font-sans: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: var(--font-sans);
    background: var(--bg-dark);
    color: var(--text-primary);
    line-height: 1.6;
    overflow-x: hidden;
  }

  ::-webkit-scrollbar {
    width: 8px;
  }

  ::-webkit-scrollbar-track {
    background: var(--bg-dark);
  }

  ::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 4px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: var(--accent-cyan);
  }

  .app-container {
    display: flex;
    min-height: 100vh;
  }

  .sidebar {
    width: 280px;
    background: var(--bg-card);
    border-right: 1px solid var(--border);
    position: fixed;
    top: 70px;
    height: calc(100vh - 70px);
    overflow-y: auto;
    z-index: 100;
    transition: transform 0.3s ease;
  }

  .sidebar-header {
    padding: 24px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .sidebar-header .logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--accent-green), var(--accent-cyan));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
  }

  .sidebar-header h1 {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.5px;
  }

  .sidebar-header p {
    font-size: 12px;
    color: var(--text-secondary);
    margin-top: 2px;
  }

  .nav-section {
    padding: 16px 0;
  }

  .nav-section-title {
    padding: 0 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-secondary);
    margin-bottom: 8px;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
    cursor: pointer;
    border-left: 3px solid transparent;
  }

  .nav-item:hover {
    background: var(--bg-hover);
    color: #fff;
    border-left-color: var(--accent-cyan);
  }

  .nav-item.active {
    background: var(--bg-hover);
    color: var(--accent-cyan);
    border-left-color: var(--accent-cyan);
  }

  .nav-item i {
    width: 20px;
    text-align: center;
    font-size: 14px;
  }

  .nav-badge {
    margin-left: auto;
    background: var(--border);
    color: var(--text-secondary);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
  }

  .main-content {
    flex: 1;
    margin-left: 280px;
    padding: 120px 40px 32px 40px;
    max-width: calc(100% - 280px);
  }

  .top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
    gap: 20px;
    flex-wrap: wrap;
  }

  .search-box {
    position: relative;
    flex: 1;
    max-width: 500px;
  }

  .search-box input {
    width: 100%;
    padding: 12px 16px 12px 44px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 14px;
    font-family: var(--font-sans);
    transition: all 0.2s;
  }

  .search-box input:focus {
    outline: none;
    border-color: var(--accent-cyan);
    box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.1);
  }

  .search-box input::placeholder {
    color: var(--text-secondary);
  }

  .search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    font-size: 14px;
  }

  .top-actions {
    display: flex;
    gap: 12px;
  }

  .btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-primary {
    background: var(--accent-green);
    color: #000;
  }

  .btn-primary:hover {
    background: #4ae660;
    transform: translateY(-1px);
  }

  .btn-outline {
    background: transparent;
    color: var(--text-primary);
    border: 1px solid var(--border);
  }

  .btn-outline:hover {
    background: var(--bg-hover);
    border-color: var(--accent-cyan);
    color: var(--accent-cyan);
  }

  .section {
    margin-bottom: 40px;
    scroll-margin-top: 20px;
  }

  .section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
  }

  .section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
  }

  .section-title-group h2 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
  }

  .section-title-group p {
    font-size: 14px;
    color: var(--text-secondary);
  }

  .cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 16px;
    align-items: start;
  }

  .command-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
  }

  .command-card:hover {
    border-color: var(--accent-cyan);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  }

  .command-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
  }

  .command-name {
    font-family: var(--font-mono);
    font-size: 14px;
    font-weight: 600;
    color: var(--accent-cyan);
  }

  .command-toggle {
    color: var(--text-secondary);
    transition: transform 0.2s;
  }

  .command-card.expanded .command-toggle {
    transform: rotate(180deg);
  }

  .command-body {
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
    opacity: 0;
    padding-top: 0;
  }

  .command-card.expanded .command-body {
    max-height: 1000px;
    opacity: 1;
  }

  .command-content {
    padding: 0 20px 20px;
  }

  .code-block {
    background: #0d1117;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 16px;
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--accent-green);
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
    margin-bottom: 16px;
    position: relative;
  }

  .code-block .copy-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--bg-hover);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    cursor: pointer;
    opacity: 0;
    transition: all 0.2s;
  }

  .code-block:hover .copy-btn {
    opacity: 1;
  }

  .code-block .copy-btn:hover {
    background: var(--accent-cyan);
    color: #000;
    border-color: var(--accent-cyan);
  }

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
  }

  .info-item {
    background: var(--bg-dark);
    border-radius: 8px;
    padding: 12px;
  }

  .info-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-bottom: 6px;
  }

  .info-value {
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.5;
  }

  .difficulty-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .difficulty-easy {
    background: rgba(63, 185, 80, 0.15);
    color: var(--accent-green);
  }

  .difficulty-medium {
    background: rgba(242, 129, 102, 0.15);
    color: var(--accent-orange);
  }

  .difficulty-hard {
    background: rgba(248, 81, 73, 0.15);
    color: var(--accent-red);
  }

  .card-actions {
    display: flex;
    gap: 8px;
  }

  .card-btn {
    flex: 1;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--border);
    background: var(--bg-dark);
    color: var(--text-primary);
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .card-btn:hover {
    background: var(--bg-hover);
    border-color: var(--accent-cyan);
    color: var(--accent-cyan);
  }

  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
  }

  .modal-overlay.active {
    opacity: 1;
    visibility: visible;
  }

  .modal {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    transform: translateY(20px);
    transition: transform 0.3s;
  }

  .modal-overlay.active .modal {
    transform: translateY(0);
  }

  .modal-header {
    padding: 24px 24px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .modal-header h3 {
    font-size: 18px;
    color: #fff;
  }

  .modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 20px;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: all 0.2s;
  }

  .modal-close:hover {
    background: var(--bg-hover);
    color: #fff;
  }

  .modal-body {
    padding: 20px 24px 24px;
  }

  .modal-section {
    margin-bottom: 20px;
  }

  .modal-section:last-child {
    margin-bottom: 0;
  }

  .modal-section h4 {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent-cyan);
    margin-bottom: 8px;
  }

  .modal-section p,
  .modal-section li {
    font-size: 14px;
    color: var(--text-primary);
    line-height: 1.7;
  }

  .modal-section ul {
    padding-left: 20px;
  }

  .modal-section li {
    margin-bottom: 6px;
  }

  .workflow-container {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
  }

  .workflow-steps {
    display: flex;
    flex-direction: column;
    gap: 0;
  }

  .workflow-step {
    display: flex;
    gap: 20px;
    position: relative;
    padding-bottom: 32px;
  }

  .workflow-step:last-child {
    padding-bottom: 0;
  }

  .workflow-step:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 48px;
    bottom: 0;
    width: 2px;
    background: var(--border);
  }

  .step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent-green);
    color: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
    z-index: 1;
  }

  .step-content h4 {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
    margin-top: 8px;
  }

  .step-content p {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 8px;
  }

  .step-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .tool-tag {
    background: var(--bg-dark);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-family: var(--font-mono);
    color: var(--accent-cyan);
  }

  .dork-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
  }

  .dork-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
  }

  .dork-card:hover {
    border-color: var(--accent-purple);
  }

  .dork-card h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--accent-purple);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .dork-list {
    list-style: none;
  }

  .dork-list li {
    background: var(--bg-dark);
    border-radius: 6px;
    padding: 10px 12px;
    margin-bottom: 8px;
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  .dork-list li:last-child {
    margin-bottom: 0;
  }

  .dork-list li .copy-icon {
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
    flex-shrink: 0;
  }

  .dork-list li .copy-icon:hover {
    color: var(--accent-cyan);
    background: var(--bg-hover);
  }

  .hero {
    background: linear-gradient(135deg, rgba(88, 166, 255, 0.08), rgba(63, 185, 80, 0.08));
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px;
    margin-bottom: 40px;
    text-align: center;
  }

  .hero h2 {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
    background: linear-gradient(90deg, var(--accent-cyan), var(--accent-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .hero p {
    font-size: 16px;
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto 24px;
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
  }

  .hero-stat {
    text-align: center;
  }

  .hero-stat .number {
    font-size: 28px;
    font-weight: 800;
    color: var(--accent-cyan);
  }

  .hero-stat .label {
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: var(--accent-green);
    color: #000;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s;
    z-index: 2000;
    box-shadow: 0 4px 20px rgba(63, 185, 80, 0.3);
  }

  .toast.show {
    transform: translateY(0);
    opacity: 1;
  }

  .mobile-toggle {
    display: none;
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 56px;
    height: 56px;
    background: var(--accent-cyan);
    color: #000;
    border: none;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    z-index: 200;
    box-shadow: 0 4px 20px rgba(88, 166, 255, 0.4);
  }

  @media (max-width: 1024px) {
    .sidebar {
      transform: translateX(-100%);
    }

    .sidebar.open {
      transform: translateX(0);
    }

    .main-content {
      margin-left: 0;
      max-width: 100%;
      padding: 24px;
    }

    .mobile-toggle {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cards-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 640px) {
    .hero {
      padding: 24px;
    }

    .hero h2 {
      font-size: 24px;
    }

    .info-grid {
      grid-template-columns: 1fr;
    }

    .dork-grid {
      grid-template-columns: 1fr;
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .section {
    animation: fadeIn 0.4s ease forwards;
  }

  .no-results {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
  }

  .no-results i {
    font-size: 48px;
    margin-bottom: 16px;
    color: var(--border);
  }

  .no-results h3 {
    font-size: 18px;
    color: var(--text-primary);
    margin-bottom: 8px;
  }
  </style>
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Public/login-modal.php'; ?>
  <div class="app-container">
    <nav class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
        <div>
          <h1>Recon Library</h1>
          <p>Cybersecurity Education</p>
        </div>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Recon Categories</div>
        <a class="nav-item active" href="#web-recon"><i class="fas fa-globe"></i> Web Recon <span
            class="nav-badge">8</span></a>
        <a class="nav-item" href="#dir-enum"><i class="fas fa-folder-open"></i> Directory Enum <span
            class="nav-badge">8</span></a>
        <a class="nav-item" href="#subdomain"><i class="fas fa-sitemap"></i> Subdomain Enum <span
            class="nav-badge">7</span></a>
        <a class="nav-item" href="#param-discovery"><i class="fas fa-sliders"></i> Parameter Discovery <span
            class="nav-badge">6</span></a>
        <a class="nav-item" href="#auth-session"><i class="fas fa-key"></i> Auth & Session <span
            class="nav-badge">6</span></a>
        <a class="nav-item" href="#api-recon"><i class="fas fa-plug"></i> API Recon <span class="nav-badge">7</span></a>
        <a class="nav-item" href="#network-recon"><i class="fas fa-network-wired"></i> Network Recon <span
            class="nav-badge">6</span></a>
        <a class="nav-item" href="#cloud-storage"><i class="fas fa-cloud"></i> Cloud & Storage <span
            class="nav-badge">6</span></a>
        <a class="nav-item" href="#js-recon"><i class="fab fa-js"></i> JavaScript Recon <span
            class="nav-badge">6</span></a>
        <a class="nav-item" href="#advanced"><i class="fas fa-brain"></i> Advanced Techniques <span
            class="nav-badge">6</span></a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Search Engines</div>
        <a class="nav-item" href="#google-dorks"><i class="fab fa-google"></i> Google Dorks <span
            class="nav-badge">30+</span></a>
        <a class="nav-item" href="#shodan"><i class="fas fa-satellite-dish"></i> Shodan Dorks <span
            class="nav-badge">20+</span></a>
        <a class="nav-item" href="#fofa"><i class="fas fa-search"></i> FOFA Dorks <span class="nav-badge">15+</span></a>
      </div>
      <div class="nav-section">
        <div class="nav-section-title">Resources</div>
        <a class="nav-item" href="#workflow"><i class="fas fa-diagram-project"></i> Bug Bounty Workflow</a>
      </div>
    </nav>

    <main class="main-content">
      <div class="top-bar">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" placeholder="Search commands, tools, techniques...">
        </div>
        <div class="top-actions">
          <button class="btn btn-outline" onclick="expandAll()"><i class="fas fa-expand"></i> Expand All</button>
          <button class="btn btn-outline" onclick="collapseAll()"><i class="fas fa-compress"></i> Collapse All</button>
        </div>
      </div>

      <div class="hero">
        <h2><i class="fas fa-shield-halved"></i> Security Recon Command Library</h2>
        <p>A comprehensive educational resource for learning web reconnaissance, information gathering, and enumeration
          methodologies. All content is for educational purposes only.</p>
        <div class="hero-stats">
          <div class="hero-stat">
            <div class="number">66+</div>
            <div class="label">Commands</div>
          </div>
          <div class="hero-stat">
            <div class="number">10</div>
            <div class="label">Categories</div>
          </div>
          <div class="hero-stat">
            <div class="number">65+</div>
            <div class="label">Dorks</div>
          </div>
          <div class="hero-stat">
            <div class="number">3</div>
            <div class="label">Search Engines</div>
          </div>
        </div>
      </div>
      <!-- ==================== WEB RECON ==================== -->
      <section class="section" id="web-recon">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(88,166,255,0.15); color: var(--accent-cyan);"><i
              class="fas fa-globe"></i></div>
          <div class="section-title-group">
            <h2>Web Reconnaissance</h2>
            <p>Techniques for identifying web technologies, analyzing headers, and fingerprinting target applications
            </p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="whatweb technology fingerprinting cms framework">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">whatweb</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">whatweb -a 3 https://target.com<span class="copy-btn"
                    onclick="copyText(event, 'whatweb -a 3 https://target.com')"><i class="fas fa-copy"></i> Copy</span>
                </div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Identifies CMS, frameworks, server software, and technologies used by a
                      website</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">Initial reconnaissance phase to understand the tech stack before deeper
                      testing</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">CMS type (WordPress, Drupal), server headers, JavaScript libraries,
                      analytics IDs</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'whatweb -a 3 https://target.com')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('whatweb')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('whatweb')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="httpx probe fast http status code screenshot">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">httpx</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">cat subdomains.txt | httpx -title -tech-detect -status-code
                  -follow-redirects<span class="copy-btn"
                    onclick="copyText(event, 'cat subdomains.txt | httpx -title -tech-detect -status-code -follow-redirects')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fast HTTP prober that checks live hosts, extracts titles, tech, and status
                      codes</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">After subdomain enumeration to filter live targets and gather initial
                      metadata</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Live endpoints, page titles, HTTP status codes, server technologies,
                      redirects</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'cat subdomains.txt | httpx -title -tech-detect -status-code -follow-redirects')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('httpx')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('httpx')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="wappalyzer technology stack detection browser extension">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">wappalyzer</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">wappalyzer https://target.com --pretty<span class="copy-btn"
                    onclick="copyText(event, 'wappalyzer https://target.com --pretty')"><i class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Identifies technologies on websites including CMS, e-commerce platforms,
                      analytics, and more</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need detailed technology stack information for vulnerability
                      mapping</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Frameworks, libraries, CDNs, analytics tools, payment processors, hosting
                      providers</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'wappalyzer https://target.com --pretty')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('wappalyzer')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('wappalyzer')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="curl headers analysis http response server information">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">curl -I</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -I -L https://target.com<span class="copy-btn"
                    onclick="copyText(event, 'curl -I -L https://target.com')"><i class="fas fa-copy"></i> Copy</span>
                </div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fetches HTTP headers to analyze server responses, redirects, and security
                      headers</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">Quick header inspection to check for missing security headers or server info
                      leaks</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Server type, X-Powered-By, security headers (CSP, HSTS), redirect chains,
                      cookies</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'curl -I -L https://target.com')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('curl')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('curl')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="nuclei vulnerability scanner template based">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">nuclei</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">nuclei -u https://target.com -t ~/nuclei-templates/ -severity
                  low,medium,high,critical<span class="copy-btn"
                    onclick="copyText(event, 'nuclei -u https://target.com -t ~/nuclei-templates/ -severity low,medium,high,critical')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fast template-based vulnerability scanner that detects known CVEs,
                      misconfigurations, and exposures</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">After initial recon to quickly identify known vulnerabilities and exposed
                      services</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">CVEs, exposed panels, misconfigurations, default credentials, sensitive file
                      exposures</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'nuclei -u https://target.com -t ~/nuclei-templates/ -severity low,medium,high,critical')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('nuclei')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('nuclei')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="waybackurls historical urls archive internet wayback machine">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">waybackurls</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">echo "target.com" | waybackurls | tee urls.txt<span class="copy-btn"
                    onclick="copyText(event, 'echo &quot;target.com&quot; | waybackurls | tee urls.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fetches historical URLs from the Wayback Machine archive for a given domain
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">To discover old endpoints, parameters, and files that may still exist on the
                      target</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Old API endpoints, deleted pages, parameter patterns, file uploads, backup
                      URLs</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'echo &quot;target.com&quot; | waybackurls | tee urls.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('waybackurls')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('waybackurls')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="gau getallurls historical endpoints archive">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">gau</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">gau target.com --subs | tee all-urls.txt<span class="copy-btn"
                    onclick="copyText(event, 'gau target.com --subs | tee all-urls.txt')"><i class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fetches URLs from multiple sources: Wayback Machine, Common Crawl, Alien
                      Vault OTX, and URLScan</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need comprehensive historical URL discovery beyond just the Wayback
                      Machine</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Historical endpoints, parameters, file paths, API versions, query strings
                      from multiple archives</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'gau target.com --subs | tee all-urls.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('gau')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('gau')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="aquatone screenshot visual reconnaissance http screenshot">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">aquatone</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">cat live-hosts.txt | aquatone -out ./screenshots/<span class="copy-btn"
                    onclick="copyText(event, 'cat live-hosts.txt | aquatone -out ./screenshots/')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Takes screenshots of websites and generates HTML reports for visual
                      reconnaissance</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">After identifying live hosts to visually inspect applications and find
                      interesting targets</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Visual confirmation of applications, admin panels, error pages, exposed
                      services</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'cat live-hosts.txt | aquatone -out ./screenshots/')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('aquatone')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('aquatone')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== DIRECTORY ENUMERATION ==================== -->
      <section class="section" id="dir-enum">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(163,113,247,0.15); color: var(--accent-purple);"><i
              class="fas fa-folder-open"></i></div>
          <div class="section-title-group">
            <h2>Directory & File Enumeration</h2>
            <p>Discover hidden directories, files, backups, and endpoints through brute-forcing and wordlist-based
              scanning</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="ffuf fuzz fast web fuzzer directory file discovery">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">ffuf - Directory Brute Force</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u https://target.com/FUZZ -w /usr/share/wordlists/dirb/common.txt -mc
                  200,301,302,403 -t 50<span class="copy-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com/FUZZ -w /usr/share/wordlists/dirb/common.txt -mc 200,301,302,403 -t 50')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fast web fuzzer that brute-forces directories and files using wordlists
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to discover hidden directories, files, and endpoints on a web
                      server</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden admin panels, backup files, config files, API endpoints, test pages
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com/FUZZ -w /usr/share/wordlists/dirb/common.txt -mc 200,301,302,403 -t 50')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('ffuf-dir')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('ffuf-dir')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="gobuster directory file dns vhost brute force">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">gobuster dir</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">gobuster dir -u https://target.com -w
                  /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x php,txt,html,js,bak,zip -t 50<span
                    class="copy-btn"
                    onclick="copyText(event, 'gobuster dir -u https://target.com -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x php,txt,html,js,bak,zip -t 50')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Directory/file brute forcer with extension support and multiple modes (dir,
                      dns, vhost)</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to find files with specific extensions or perform DNS
                      subdomain enumeration</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Directories, files with extensions, backup files, source code, config files
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'gobuster dir -u https://target.com -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -x php,txt,html,js,bak,zip -t 50')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('gobuster')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('gobuster')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="dirsearch python directory brute force recursive">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">dirsearch</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">python3 dirsearch.py -u https://target.com -e php,html,js,txt,bak -t 50 -r<span
                    class="copy-btn"
                    onclick="copyText(event, 'python3 dirsearch.py -u https://target.com -e php,html,js,txt,bak -t 50 -r')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Python-based directory brute forcer with recursive scanning and extension
                      support</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need recursive directory discovery with automatic extension testing
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Nested directories, hidden files, backup copies, source code, configuration
                      files</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'python3 dirsearch.py -u https://target.com -e php,html,js,txt,bak -t 50 -r')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('dirsearch')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('dirsearch')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="feroxbuster recursive directory enumeration rust fast">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">feroxbuster</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">feroxbuster -u https://target.com -w /usr/share/wordlists/dirb/common.txt -r -x
                  php,txt,html,js,bak<span class="copy-btn"
                    onclick="copyText(event, 'feroxbuster -u https://target.com -w /usr/share/wordlists/dirb/common.txt -r -x php,txt,html,js,bak')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Rust-based recursive content discovery tool with smart filtering and state
                      management</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need fast, recursive directory enumeration with automatic link
                      extraction</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Deeply nested directories, linked resources, API endpoints, hidden
                      applications</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'feroxbuster -u https://target.com -w /usr/share/wordlists/dirb/common.txt -r -x php,txt,html,js,bak')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('feroxbuster')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('feroxbuster')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="backup file discovery old config source code leak">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Backup File Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u https://target.com/FUZZ -w backup-wordlist.txt -mc 200 -H "User-Agent:
                  Mozilla/5.0"<span class="copy-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com/FUZZ -w backup-wordlist.txt -mc 200 -H &quot;User-Agent: Mozilla/5.0&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Searches for backup files, old versions, and source code leaks using
                      specialized wordlists</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">After initial directory scan to find sensitive backup and configuration
                      files</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">.bak, .old, .zip, .tar.gz, .sql, .env, .git, source code archives, database
                      dumps</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com/FUZZ -w backup-wordlist.txt -mc 200 -H &quot;User-Agent: Mozilla/5.0&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('backup-discovery')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('backup-discovery')"><i class="fas fa-briefcase"></i>
                    Use Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="wfuzz web fuzzer payload fuzzing parameter">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">wfuzz</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">wfuzz -c -z file,/usr/share/wordlists/dirb/common.txt --hc 404
                  https://target.com/FUZZ<span class="copy-btn"
                    onclick="copyText(event, 'wfuzz -c -z file,/usr/share/wordlists/dirb/common.txt --hc 404 https://target.com/FUZZ')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Versatile web application fuzzer for directory, parameter, and payload
                      fuzzing</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need flexible fuzzing with custom payloads, headers, and multiple
                      encodings</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden endpoints, parameter-based vulnerabilities, authentication bypasses
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'wfuzz -c -z file,/usr/share/wordlists/dirb/common.txt --hc 404 https://target.com/FUZZ')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('wfuzz')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('wfuzz')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="git exposure source code repository leak .git">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Git Exposure Detection</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -s https://target.com/.git/HEAD | grep -q "ref:" && echo "Git
                  exposed!"<span class="copy-btn"
                    onclick="copyText(event, 'curl -s https://target.com/.git/HEAD | grep -q &quot;ref:&quot; && echo &quot;Git exposed!&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Detects exposed .git directories which can leak entire source code
                      repositories</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">During initial reconnaissance to check for common source code exposure
                      misconfigurations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Exposed Git repositories, commit history, source code, credentials in code,
                      API keys</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -s https://target.com/.git/HEAD | grep -q &quot;ref:&quot; && echo &quot;Git exposed!&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('git-exposure')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('git-exposure')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="svn cvs mercurial source control exposure leak">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Source Control Exposure</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">for path in .svn .hg .bzr CVS; do curl -s -o /dev/null -w "%{http_code}"
                  https://target.com/$path; echo " $path"; done<span class="copy-btn"
                    onclick="copyText(event, 'for path in .svn .hg .bzr CVS; do curl -s -o /dev/null -w &quot;%{http_code}&quot; https://target.com/$path; echo &quot; $path&quot;; done')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Checks for exposed SVN, Mercurial, Bazaar, and CVS source control
                      directories</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When checking for legacy source control system exposures beyond Git</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Legacy source control data, revision history, deleted files, old code
                      versions</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'for path in .svn .hg .bzr CVS; do curl -s -o /dev/null -w &quot;%{http_code}&quot; https://target.com/$path; echo &quot; $path&quot;; done')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('source-control')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('source-control')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- ==================== SUBDOMAIN ENUMERATION ==================== -->
      <section class="section" id="subdomain">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(63,185,80,0.15); color: var(--accent-green);"><i
              class="fas fa-sitemap"></i></div>
          <div class="section-title-group">
            <h2>Subdomain Enumeration</h2>
            <p>Discover subdomains through passive and active enumeration techniques to expand the attack surface</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="subfinder passive subdomain discovery certificate transparency">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">subfinder</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">subfinder -d target.com -all -o subdomains.txt<span class="copy-btn"
                    onclick="copyText(event, 'subfinder -d target.com -all -o subdomains.txt')"><i
                      class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Passive subdomain discovery tool that queries multiple sources including
                      certificate transparency logs</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">First step in subdomain enumeration to quickly gather known subdomains
                      without sending traffic to target</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Subdomains from CT logs, passive DNS, search engines, and public APIs</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'subfinder -d target.com -all -o subdomains.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('subfinder')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('subfinder')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="amass comprehensive subdomain enumeration active passive dns">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">amass</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">amass enum -d target.com -o amass-results.txt<span class="copy-btn"
                    onclick="copyText(event, 'amass enum -d target.com -o amass-results.txt')"><i
                      class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Comprehensive subdomain enumeration using both passive and active techniques
                      with extensive data sources</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need the most thorough subdomain discovery with DNS resolution and
                      network mapping</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Subdomains, IP addresses, ASN info, DNS records, network infrastructure,
                      relationships</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'amass enum -d target.com -o amass-results.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('amass')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('amass')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="assetfinder subdomain discovery passive certificate transparency">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">assetfinder</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">assetfinder --subs-only target.com | tee subs.txt<span class="copy-btn"
                    onclick="copyText(event, 'assetfinder --subs-only target.com | tee subs.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Lightweight passive subdomain discovery tool that queries multiple sources
                      efficiently</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">Quick passive enumeration when you need fast results without heavy resource
                      usage</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Subdomains from certificate transparency, passive DNS, and related domains
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'assetfinder --subs-only target.com | tee subs.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('assetfinder')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('assetfinder')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="crt.sh certificate transparency subdomain discovery web">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">crt.sh Enumeration</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -s "https://crt.sh/?q=%.target.com&output=json" | jq -r '.[].name_value' |
                  sort -u<span class="copy-btn"
                    onclick="copyText(event, 'curl -s &quot;https://crt.sh/?q=%.target.com&output=json&quot; | jq -r &quot;.[].name_value&quot; | sort -u')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Queries certificate transparency logs via crt.sh to discover subdomains from
                      SSL certificates</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you want to leverage certificate transparency for passive subdomain
                      discovery</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Subdomains listed in SSL certificates, wildcard certificates, SAN entries
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -s &quot;https://crt.sh/?q=%.target.com&output=json&quot; | jq -r &quot;.[].name_value&quot; | sort -u')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('crtsh')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('crtsh')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="dnsrecon dns enumeration brute force zone transfer">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">dnsrecon</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">dnsrecon -d target.com -t brt -D
                  /usr/share/wordlists/dnsrecon/subdomains-top1mil-5000.txt<span class="copy-btn"
                    onclick="copyText(event, 'dnsrecon -d target.com -t brt -D /usr/share/wordlists/dnsrecon/subdomains-top1mil-5000.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Active DNS reconnaissance tool with brute force, zone transfer, and record
                      enumeration capabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need active DNS-based enumeration including zone transfer attempts
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Subdomains, DNS records (A, MX, NS, TXT), zone transfer vulnerabilities, SPF
                      records</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'dnsrecon -d target.com -t brt -D /usr/share/wordlists/dnsrecon/subdomains-top1mil-5000.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('dnsrecon')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('dnsrecon')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="altdns permutation subdomain generation wordlist">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">altdns</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">altdns -i known-subdomains.txt -o permutations.txt -w words.txt -r -s
                  resolved.txt<span class="copy-btn"
                    onclick="copyText(event, 'altdns -i known-subdomains.txt -o permutations.txt -w words.txt -r -s resolved.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Generates subdomain permutations by combining known subdomains with mutation
                      words</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">After initial passive discovery to find subdomains that don't appear in
                      public sources</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Non-obvious subdomains like dev-api, staging-v2, test-admin that follow
                      naming patterns</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'altdns -i known-subdomains.txt -o permutations.txt -w words.txt -r -s resolved.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('altdns')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('altdns')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="massdns high performance dns resolver subdomain brute force">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">massdns</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">massdns -r resolvers.txt -t A -o S -w results.txt subdomains.txt<span
                    class="copy-btn"
                    onclick="copyText(event, 'massdns -r resolvers.txt -t A -o S -w results.txt subdomains.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">High-performance DNS stub resolver for mass subdomain resolution at
                      incredible speed</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you have a large list of potential subdomains and need to resolve them
                      quickly</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Resolves subdomains to IPs, identifies dead vs live subdomains, CNAME
                      records</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'massdns -r resolvers.txt -t A -o S -w results.txt subdomains.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('massdns')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('massdns')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== PARAMETER DISCOVERY ==================== -->
      <section class="section" id="param-discovery">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(242,129,102,0.15); color: var(--accent-orange);"><i
              class="fas fa-sliders"></i></div>
          <div class="section-title-group">
            <h2>Parameter Discovery</h2>
            <p>Find hidden parameters, fuzz inputs, and discover API endpoints through systematic testing</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="ffuf parameter fuzzing get post hidden parameters">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">ffuf - Parameter Fuzzing</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u "https://target.com/page?FUZZ=value" -w
                  /usr/share/wordlists/seclists/Discovery/Web-Content/burp-parameter-names.txt -mc 200<span
                    class="copy-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/page?FUZZ=value&quot; -w /usr/share/wordlists/seclists/Discovery/Web-Content/burp-parameter-names.txt -mc 200')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers hidden GET parameters by fuzzing parameter names against a target
                      URL</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you suspect a page accepts additional parameters that aren't visible in
                      the UI</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden parameters, debug flags, admin parameters, API version parameters
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/page?FUZZ=value&quot; -w /usr/share/wordlists/seclists/Discovery/Web-Content/burp-parameter-names.txt -mc 200')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('ffuf-param')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('ffuf-param')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="post parameter fuzzing ffuf body data hidden">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">ffuf - POST Parameter Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u "https://target.com/api/endpoint" -X POST -d "FUZZ=test" -w params.txt
                  -H "Content-Type: application/x-www-form-urlencoded"<span class="copy-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/endpoint&quot; -X POST -d &quot;FUZZ=test&quot; -w params.txt -H &quot;Content-Type: application/x-www-form-urlencoded&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers hidden POST parameters by sending fuzzed parameter names in
                      request body</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing API endpoints or forms that may accept additional POST
                      parameters</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden POST parameters, debug modes, internal API parameters, feature flags
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/endpoint&quot; -X POST -d &quot;FUZZ=test&quot; -w params.txt -H &quot;Content-Type: application/x-www-form-urlencoded&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('ffuf-post')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('ffuf-post')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="arjun http parameter discovery brute force">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">arjun</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">arjun -u https://target.com/page -m GET,POST -oT params.txt<span
                    class="copy-btn"
                    onclick="copyText(event, 'arjun -u https://target.com/page -m GET,POST -oT params.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">HTTP parameter discovery suite that finds query parameters for URL endpoints
                      using brute force</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need a dedicated parameter discovery tool with smart response
                      analysis</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Valid GET/POST parameters, JSON parameters, XML parameters, custom headers
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'arjun -u https://target.com/page -m GET,POST -oT params.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('arjun')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('arjun')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="idor insecure direct object reference parameter manipulation">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">IDOR Discovery Pattern</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u "https://target.com/api/user/FUZZ" -w ids.txt -H "Authorization: Bearer
                  TOKEN" -mc 200<span class="copy-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/user/FUZZ&quot; -w ids.txt -H &quot;Authorization: Bearer TOKEN&quot; -mc 200')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests for Insecure Direct Object Reference by fuzzing ID parameters with
                      sequential values</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you encounter endpoints with numeric IDs and want to test for
                      horizontal/vertical privilege escalation</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Unauthorized access to other users' data, admin endpoints, sensitive
                      resources</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/user/FUZZ&quot; -w ids.txt -H &quot;Authorization: Bearer TOKEN&quot; -mc 200')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('idor')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('idor')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="param miner burp suite extension parameter discovery">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Param Miner (Burp Suite)</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Right-click request > Extensions > Param Miner > Guess GET parameters<span
                    class="copy-btn"
                    onclick="copyText(event, 'Right-click request > Extensions > Param Miner > Guess GET parameters')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Burp Suite extension that guesses hidden parameters, headers, and cookies
                      using wordlists</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When working within Burp Suite to find parameters that change application
                      behavior</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden parameters, cache-busting headers, debug parameters, internal flags
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Right-click request > Extensions > Param Miner > Guess GET parameters')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('param-miner')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('param-miner')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="api parameter brute force endpoint discovery rest graphql">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">API Parameter Brute Force</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u "https://target.com/api/v1/FUZZ" -w api-endpoints.txt -H "Accept:
                  application/json" -mc 200,201,401,403<span class="copy-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/v1/FUZZ&quot; -w api-endpoints.txt -H &quot;Accept: application/json&quot; -mc 200,201,401,403')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Brute forces API endpoint paths and parameters to discover hidden API
                      functionality</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing REST APIs to find undocumented endpoints and parameters</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden API endpoints, admin APIs, debug endpoints, versioned endpoints,
                      internal APIs</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/v1/FUZZ&quot; -w api-endpoints.txt -H &quot;Accept: application/json&quot; -mc 200,201,401,403')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('api-brute')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('api-brute')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== AUTH & SESSION ==================== -->
      <section class="section" id="auth-session">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(248,81,73,0.15); color: var(--accent-red);"><i
              class="fas fa-key"></i></div>
          <div class="section-title-group">
            <h2>Authentication & Session Analysis</h2>
            <p>Analyze cookies, JWT tokens, session behavior, and authentication mechanisms for security flaws</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="cookie analysis session hijacking secure httponly flags">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Cookie Security Analysis</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -I -s https://target.com | grep -i "set-cookie"<span class="copy-btn"
                    onclick="copyText(event, 'curl -I -s https://target.com | grep -i &quot;set-cookie&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Analyzes HTTP response headers to inspect cookie security attributes and
                      configurations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">During initial recon to check for missing security flags on session cookies
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Missing Secure/HttpOnly flags, weak session IDs, cookie scope issues,
                      SameSite misconfigurations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -I -s https://target.com | grep -i &quot;set-cookie&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('cookie-analysis')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('cookie-analysis')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="jwt json web token decode inspect signature algorithm">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">JWT Inspection</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">echo "TOKEN" | jwt_tool.py -t<span class="copy-btn"
                    onclick="copyText(event, 'echo &quot;TOKEN&quot; | jwt_tool.py -t')"><i class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Decodes and analyzes JWT tokens to inspect claims, algorithms, and potential
                      vulnerabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you encounter JWT-based authentication and need to analyze token
                      structure and security</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Weak algorithms (none/HS256), exposed secrets, privilege escalation claims,
                      token expiration issues</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'echo &quot;TOKEN&quot; | jwt_tool.py -t')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('jwt-inspect')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('jwt-inspect')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="session token behavior testing fixation prediction">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Session Token Analysis</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">for i in {1..10}; do curl -s -c - https://target.com/login | grep session;
                  done<span class="copy-btn"
                    onclick="copyText(event, 'for i in {1..10}; do curl -s -c - https://target.com/login | grep session; done')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Analyzes session token generation patterns to detect predictability and
                      fixation issues</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to assess the randomness and security of session token
                      generation</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Predictable tokens, session fixation, weak entropy, time-based generation
                      patterns</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'for i in {1..10}; do curl -s -c - https://target.com/login | grep session; done')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('session-analysis')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('session-analysis')"><i class="fas fa-briefcase"></i>
                    Use Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="auth bypass patterns 401 403 bypass techniques">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Auth Bypass Patterns</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -H "X-Original-URL: /admin" -H "X-Rewrite-URL: /admin"
                  https://target.com/<span class="copy-btn"
                    onclick="copyText(event, 'curl -H &quot;X-Original-URL: /admin&quot; -H &quot;X-Rewrite-URL: /admin&quot; https://target.com/')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests common authentication bypass techniques using HTTP header manipulation
                      and path variations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you encounter 401/403 responses and want to test for bypass
                      opportunities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Header-based bypasses, path normalization issues, case sensitivity bypasses,
                      verb tampering</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -H &quot;X-Original-URL: /admin&quot; -H &quot;X-Rewrite-URL: /admin&quot; https://target.com/')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('auth-bypass')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('auth-bypass')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="burp sequencer session token entropy randomness">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Burp Sequencer</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Send request to Sequencer > Start live capture > Analyze token entropy<span
                    class="copy-btn"
                    onclick="copyText(event, 'Send request to Sequencer > Start live capture > Analyze token entropy')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Statistical analysis tool for testing the randomness quality of session
                      tokens and CSRF tokens</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need quantitative analysis of token entropy and predictability
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Low entropy tokens, predictable patterns, bit-level analysis, statistical
                      weaknesses</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Send request to Sequencer > Start live capture > Analyze token entropy')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('burp-sequencer')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('burp-sequencer')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="oauth saml openid connect authentication flow analysis">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">OAuth/SAML Flow Analysis</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Analyze redirect_uri, state parameter, scope, and response_type in OAuth
                  flows<span class="copy-btn"
                    onclick="copyText(event, 'Analyze redirect_uri, state parameter, scope, and response_type in OAuth flows')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Analyzes OAuth 2.0 and SAML authentication flows for misconfigurations and
                      vulnerabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing applications that use third-party authentication providers
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Open redirect in redirect_uri, missing state validation, scope escalation,
                      SAML injection</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Analyze redirect_uri, state parameter, scope, and response_type in OAuth flows')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('oauth-analysis')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('oauth-analysis')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== API RECON ==================== -->
      <section class="section" id="api-recon">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(210,153,34,0.15); color: var(--accent-yellow);"><i
              class="fas fa-plug"></i></div>
          <div class="section-title-group">
            <h2>API Recon & Testing</h2>
            <p>Discover, enumerate, and analyze REST, GraphQL, and versioned APIs for security assessment</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="swagger openapi api documentation discovery">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Swagger/OpenAPI Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u https://target.com/FUZZ -w swagger-paths.txt -mc 200<span
                    class="copy-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com/FUZZ -w swagger-paths.txt -mc 200')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers Swagger/OpenAPI documentation endpoints that expose API
                      specifications</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you suspect a REST API exists and want to find its documentation</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">OpenAPI specs, Swagger UI, API schemas, endpoint definitions, authentication
                      requirements</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com/FUZZ -w swagger-paths.txt -mc 200')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('swagger-discovery')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('swagger-discovery')"><i class="fas fa-briefcase"></i>
                    Use Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="postman collection api endpoints discovery export">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Postman Collection Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -s https://target.com/postman/collection.json | jq '.item[].name'<span
                    class="copy-btn"
                    onclick="copyText(event, 'curl -s https://target.com/postman/collection.json | jq &quot;.item[].name&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Searches for exposed Postman collections that document internal API
                      endpoints</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When looking for developer documentation that may expose internal API
                      structure</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">API endpoint collections, request examples, environment variables,
                      authentication tokens</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -s https://target.com/postman/collection.json | jq &quot;.item[].name&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('postman-discovery')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('postman-discovery')"><i class="fas fa-briefcase"></i>
                    Use Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="graphql introspection query mutation schema discovery">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">GraphQL Introspection</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -X POST https://target.com/graphql -H "Content-Type: application/json" -d
                  '{"query":"{__schema{types{name fields{name args{name type{name}}}}}}"}'<span class="copy-btn"
                    onclick="copyText(event, 'curl -X POST https://target.com/graphql -H &quot;Content-Type: application/json&quot; -d &apos;{&quot;query&quot;:&quot;{__schema{types{name fields{name args{name type{name}}}}}}&quot;}&apos;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Queries GraphQL introspection to discover the entire API schema including
                      types, queries, and mutations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you discover a GraphQL endpoint and want to map all available
                      operations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">All queries, mutations, subscriptions, types, fields, arguments, and their
                      data types</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -X POST https://target.com/graphql -H &quot;Content-Type: application/json&quot; -d &apos;{&quot;query&quot;:&quot;{__schema{types{name fields{name args{name type{name}}}}}}&quot;}&apos;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('graphql-introspection')"><i
                      class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('graphql-introspection')"><i
                      class="fas fa-briefcase"></i>
                    Use Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="api version discovery v1 v2 v3 deprecated endpoints">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">API Version Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u "https://target.com/api/FUZZ/users" -w versions.txt -mc 200,401,403<span
                    class="copy-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/FUZZ/users&quot; -w versions.txt -mc 200,401,403')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers API versions by fuzzing version identifiers in API paths</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you want to find deprecated or undocumented API versions that may have
                      weaker security</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Old API versions, beta endpoints, internal versions, deprecated but active
                      endpoints</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u &quot;https://target.com/api/FUZZ/users&quot; -w versions.txt -mc 200,401,403')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('api-versions')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('api-versions')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="kiterunner api endpoint discovery route scanning">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">kiterunner</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">kr scan https://target.com -w routes-large.kite -x 20<span class="copy-btn"
                    onclick="copyText(event, 'kr scan https://target.com -w routes-large.kite -x 20')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">API endpoint discovery tool that uses specialized wordlists for API route
                      scanning</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to discover API endpoints with proper HTTP method and
                      content-type handling</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">REST API endpoints, GraphQL endpoints, API documentation, swagger specs, API
                      parameters</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'kr scan https://target.com -w routes-large.kite -x 20')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('kiterunner')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('kiterunner')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="arjun api parameter discovery json xml content type">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Arjun - API Parameters</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">arjun -u https://target.com/api/endpoint -m JSON -oT json-params.txt<span
                    class="copy-btn"
                    onclick="copyText(event, 'arjun -u https://target.com/api/endpoint -m JSON -oT json-params.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers parameters in JSON/XML API endpoints by analyzing response
                      differences</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing APIs that accept JSON or XML payloads to find hidden parameters
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Hidden JSON parameters, XML attributes, nested object properties,
                      undocumented fields</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'arjun -u https://target.com/api/endpoint -m JSON -oT json-params.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('arjun-api')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('arjun-api')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="ffuf vhost virtual host discovery subdomain takeover">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">ffuf - VHost Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">ffuf -u https://target.com -H "Host: FUZZ.target.com" -w subdomains.txt -mc
                  200<span class="copy-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com -H &quot;Host: FUZZ.target.com&quot; -w subdomains.txt -mc 200')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers virtual hosts by fuzzing the Host header to find additional web
                      applications</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When multiple applications may be hosted on the same IP using virtual
                      hosting</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Virtual hosts, internal applications, staging environments, admin panels on
                      shared IPs</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'ffuf -u https://target.com -H &quot;Host: FUZZ.target.com&quot; -w subdomains.txt -mc 200')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('ffuf-vhost')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('ffuf-vhost')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== NETWORK RECON ==================== -->
      <section class="section" id="network-recon">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(88,166,255,0.15); color: var(--accent-cyan);"><i
              class="fas fa-network-wired"></i></div>
          <div class="section-title-group">
            <h2>Network & Port Recon Concepts</h2>
            <p>Educational overview of network scanning concepts, service enumeration, and banner grabbing techniques
            </p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="nmap port scan service version detection syn connect">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">nmap - Port Scanning</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">nmap -sV -sC -O -p- target.com -oN nmap-results.txt<span class="copy-btn"
                    onclick="copyText(event, 'nmap -sV -sC -O -p- target.com -oN nmap-results.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Comprehensive port scanner that identifies open ports, services, versions,
                      and OS fingerprinting</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to map the network attack surface and identify running
                      services</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Open ports, service versions, OS information, default scripts output,
                      filtered ports</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'nmap -sV -sC -O -p- target.com -oN nmap-results.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('nmap-portscan')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('nmap-portscan')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="masscan high speed port scanner internet scale">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">masscan</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">masscan target.com -p1-65535 --rate 1000 -oG masscan-results.txt<span
                    class="copy-btn"
                    onclick="copyText(event, 'masscan target.com -p1-65535 --rate 1000 -oG masscan-results.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">High-speed asynchronous port scanner capable of scanning the entire Internet
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to scan large IP ranges or entire port ranges very quickly
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Open ports across large networks, service availability, network topology
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'masscan target.com -p1-65535 --rate 1000 -oG masscan-results.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('masscan')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('masscan')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="service enumeration nmap scripts default nse">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Service Enumeration</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">nmap -sV --script=default,vuln -p 21,22,80,443,3306,3389 target.com<span
                    class="copy-btn"
                    onclick="copyText(event, 'nmap -sV --script=default,vuln -p 21,22,80,443,3306,3389 target.com')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Uses Nmap Scripting Engine (NSE) to enumerate services and detect
                      vulnerabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">After identifying open ports to deeply enumerate services and find known
                      vulnerabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Service versions, default credentials, known CVEs, misconfigurations, banner
                      information</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'nmap -sV --script=default,vuln -p 21,22,80,443,3306,3389 target.com')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('service-enum')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('service-enum')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="banner grabbing netcat nc telnet service fingerprint">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Banner Grabbing</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">nc -v target.com 22 && nc -v target.com 80<span class="copy-btn"
                    onclick="copyText(event, 'nc -v target.com 22 && nc -v target.com 80')"><i class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Connects to services to retrieve banner information that reveals software
                      versions and types</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">Quick manual check of service banners when automated scanning is not
                      available</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Service versions, server software, OS information, protocol details, custom
                      banners</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'nc -v target.com 22 && nc -v target.com 80')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('banner-grab')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('banner-grab')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="open port analysis service detection tcp udp">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Open Port Analysis</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">nmap -sS -sU -p T:1-1000,U:53,69,123 target.com<span class="copy-btn"
                    onclick="copyText(event, 'nmap -sS -sU -p T:1-1000,U:53,69,123 target.com')"><i
                      class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Performs both TCP SYN and UDP scanning to comprehensively identify open
                      ports</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to check both TCP and UDP services for a complete network
                      picture</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">TCP services, UDP services (DNS, SNMP, NTP), filtered ports, stealthy
                      services</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'nmap -sS -sU -p T:1-1000,U:53,69,123 target.com')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('port-analysis')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('port-analysis')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="naabu fast port scanner go lang passive">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">naabu</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">naabu -list targets.txt -top-ports 1000 -o ports.txt<span class="copy-btn"
                    onclick="copyText(event, 'naabu -list targets.txt -top-ports 1000 -o ports.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Fast port scanner written in Go that can scan multiple hosts simultaneously
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to scan multiple targets quickly with SYN or CONNECT scanning
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Open ports across multiple hosts, service availability, CDN detection, WAF
                      detection</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'naabu -list targets.txt -top-ports 1000 -o ports.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('naabu')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('naabu')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== CLOUD & STORAGE ==================== -->
      <section class="section" id="cloud-storage">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(63,185,80,0.15); color: var(--accent-green);"><i
              class="fas fa-cloud"></i></div>
          <div class="section-title-group">
            <h2>Cloud & Storage Recon</h2>
            <p>Discover exposed cloud assets, storage buckets, and misconfigured cloud resources</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="s3 bucket enumeration aws amazon cloud storage">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">S3 Bucket Enumeration</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">python3 s3scanner.py -l buckets.txt | tee s3-results.txt<span class="copy-btn"
                    onclick="copyText(event, 'python3 s3scanner.py -l buckets.txt | tee s3-results.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Scans for exposed AWS S3 buckets and checks their permissions (public
                      read/write)</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When assessing cloud infrastructure for exposed storage containers</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Public S3 buckets, bucket listings, writable buckets, sensitive files,
                      backups</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'python3 s3scanner.py -l buckets.txt | tee s3-results.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('s3-enum')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('s3-enum')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="cloud storage azure blob google cloud gcp bucket">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Multi-Cloud Storage Scan</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">cloud_enum -k target -t 50 | tee cloud-results.txt<span class="copy-btn"
                    onclick="copyText(event, 'cloud_enum -k target -t 50 | tee cloud-results.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Enumerates storage containers across AWS S3, Azure Blob, and Google Cloud
                      Storage</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you want to check all major cloud providers for exposed storage
                      resources</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">S3 buckets, Azure containers, GCS buckets, public files, misconfigured
                      permissions</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'cloud_enum -k target -t 50 | tee cloud-results.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('cloud-enum')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('cloud-enum')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="exposed storage detection public read write permissions">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Exposed Storage Detection</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">aws s3 ls s3://target-bucket-name --no-sign-request<span class="copy-btn"
                    onclick="copyText(event, 'aws s3 ls s3://target-bucket-name --no-sign-request')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests if an S3 bucket allows unauthenticated listing of its contents</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you have identified a potential bucket name and want to verify access
                      permissions</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Public bucket listings, file names, directory structures, potentially
                      sensitive files</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'aws s3 ls s3://target-bucket-name --no-sign-request')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('storage-detect')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('storage-detect')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="misconfigured cloud assets iam policy public access">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Cloud Asset Misconfiguration</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">scout aws --profile default --report-dir ./scout-report/<span class="copy-btn"
                    onclick="copyText(event, 'scout aws --profile default --report-dir ./scout-report/')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Scans cloud infrastructure for security misconfigurations across AWS, Azure,
                      and GCP</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When performing comprehensive cloud security assessments with proper
                      credentials</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">IAM misconfigurations, public S3 buckets, exposed databases, weak security
                      groups</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'scout aws --profile default --report-dir ./scout-report/')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('cloud-misconfig')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('cloud-misconfig')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="gitHub gitLab repository leak secrets api keys tokens">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Repository Secret Scanning</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">truffleHog --regex --entropy=False https://github.com/target/repo.git<span
                    class="copy-btn"
                    onclick="copyText(event, 'truffleHog --regex --entropy=False https://github.com/target/repo.git')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Searches through git repositories for high-entropy strings and regex
                      patterns matching secrets</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you have access to source code repositories and want to find exposed
                      credentials</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">API keys, passwords, tokens, private keys, database credentials, cloud
                      access keys</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'truffleHog --regex --entropy=False https://github.com/target/repo.git')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('secret-scan')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('secret-scan')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="docker registry exposed container images vulnerability">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Docker Registry Exposure</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -s https://registry.target.com/v2/_catalog | jq '.repositories[]'<span
                    class="copy-btn"
                    onclick="copyText(event, 'curl -s https://registry.target.com/v2/_catalog | jq &quot;.repositories[]&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Checks for exposed Docker registries that may leak container images and
                      configurations</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you suspect a Docker registry may be publicly accessible without
                      authentication</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Container images, image tags, layer contents, Dockerfile instructions,
                      embedded secrets</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -s https://registry.target.com/v2/_catalog | jq &quot;.repositories[]&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('docker-registry')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('docker-registry')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== JAVASCRIPT RECON ==================== -->
      <section class="section" id="js-recon">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(210,153,34,0.15); color: var(--accent-yellow);"><i
              class="fab fa-js"></i></div>
          <div class="section-title-group">
            <h2>JavaScript Recon</h2>
            <p>Extract endpoints, API keys, and hidden functionality from JavaScript files and source maps</p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="javascript file analysis endpoint extraction url path discovery">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">JS Endpoint Extraction</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">cat urls.txt | hakrawler -subs -u | grep \\.js$ | tee js-files.txt<span
                    class="copy-btn"
                    onclick="copyText(event, 'cat urls.txt | hakrawler -subs -u | grep \\.js$ | tee js-files.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Crawls websites to discover and extract all JavaScript file URLs for further
                      analysis</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you need to collect all JS files from a target for endpoint and secret
                      extraction</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">JavaScript files, bundled JS, minified scripts, third-party libraries,
                      inline scripts</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'cat urls.txt | hakrawler -subs -u | grep \\.js$ | tee js-files.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('js-endpoints')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('js-endpoints')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="linkfinder endpoint extraction regex url path api">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">LinkFinder</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">python3 linkfinder.py -i https://target.com/app.js -o cli<span class="copy-btn"
                    onclick="copyText(event, 'python3 linkfinder.py -i https://target.com/app.js -o cli')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers endpoints and URLs hidden in JavaScript files using regex pattern
                      matching</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you have JS files and want to extract all internal API endpoints and
                      URL patterns</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">API endpoints, URL paths, parameter patterns, fetch/XHR URLs, WebSocket
                      endpoints</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'python3 linkfinder.py -i https://target.com/app.js -o cli')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('linkfinder')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('linkfinder')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="source map discovery .js.map deobfuscate original source code">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Source Map Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -s https://target.com/static/app.js | grep -o 'sourceMappingURL=[^ ]*' |
                  sed 's/sourceMappingURL=//' | xargs -I {} curl -s https://target.com/static/{}<span class="copy-btn"
                    onclick="copyText(event, 'curl -s https://target.com/static/app.js | grep -o &quot;sourceMappingURL=[^ ]*&quot; | sed &quot;s/sourceMappingURL=//&quot; | xargs -I {} curl -s https://target.com/static/{}')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers and downloads source maps that can reveal original unminified
                      source code</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you find JavaScript files that may have source maps exposing original
                      code</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Original source code, comments, developer names, internal paths, hidden
                      functionality</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -s https://target.com/static/app.js | grep -o &quot;sourceMappingURL=[^ ]*&quot; | sed &quot;s/sourceMappingURL=//&quot; | xargs -I {} curl -s https://target.com/static/{}')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('source-maps')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('source-maps')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="secretfinder api keys tokens credentials javascript grep">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">SecretFinder</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">python3 SecretFinder.py -i https://target.com/app.js -o results.html<span
                    class="copy-btn"
                    onclick="copyText(event, 'python3 SecretFinder.py -i https://target.com/app.js -o results.html')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Searches JavaScript files for API keys, tokens, secrets, and sensitive
                      information using regex</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you want to find hardcoded credentials and secrets in client-side
                      JavaScript</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">API keys, AWS keys, Google API keys, Slack tokens, private keys, database
                      URLs, auth tokens</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'python3 SecretFinder.py -i https://target.com/app.js -o results.html')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('secretfinder')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('secretfinder')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="jsmon javascript monitoring changes diff website">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">JSMon - JS Change Monitoring</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">jsmon -f js-files.txt -c config.json -t 30<span class="copy-btn"
                    onclick="copyText(event, 'jsmon -f js-files.txt -c config.json -t 30')"><i class="fas fa-copy"></i>
                    Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Monitors JavaScript files for changes over time to detect new endpoints and
                      functionality</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you want continuous monitoring of JS files for new API endpoints and
                      features</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">New endpoints, added functionality, removed features, changed API versions,
                      new secrets</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn" onclick="copyText(event, 'jsmon -f js-files.txt -c config.json -t 30')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('jsmon')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('jsmon')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="subjs subdomain javascript discovery linked files">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">subjs - Subdomain JS Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">cat subdomains.txt | httpx -silent | subjs | tee js-urls.txt<span
                    class="copy-btn"
                    onclick="copyText(event, 'cat subdomains.txt | httpx -silent | subjs | tee js-urls.txt')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Discovers JavaScript files linked across all subdomains of a target
                      organization</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When you want to find JS files across the entire subdomain attack surface
                    </div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">JS files on all subdomains, third-party JS, CDN resources, internal JS
                      endpoints</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-easy">Easy</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'cat subdomains.txt | httpx -silent | subjs | tee js-urls.txt')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('subjs')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('subjs')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== ADVANCED TECHNIQUES ==================== -->
      <section class="section" id="advanced">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(248,81,73,0.15); color: var(--accent-red);"><i
              class="fas fa-brain"></i></div>
          <div class="section-title-group">
            <h2>Advanced Recon Techniques</h2>
            <p>Complex methodologies for discovering logic flaws, race conditions, and business logic vulnerabilities
            </p>
          </div>
        </div>
        <div class="cards-grid">
          <div class="command-card" data-search="logic flaw discovery business logic testing workflow manipulation">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Logic Flaw Discovery</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Analyze application workflows: skip steps, repeat actions, change sequence,
                  manipulate state<span class="copy-btn"
                    onclick="copyText(event, 'Analyze application workflows: skip steps, repeat actions, change sequence, manipulate state')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Systematically tests application workflows for logic flaws by manipulating
                      the expected flow</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing multi-step processes like checkout, registration, or approval
                      workflows</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Price manipulation, privilege escalation, state bypasses, unauthorized
                      actions</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Analyze application workflows: skip steps, repeat actions, change sequence, manipulate state')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('logic-flaws')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('logic-flaws')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="race condition testing time attack concurrency vulnerability">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Race Condition Testing</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">turbo-intruder -p race.py -t 20 https://target.com/api/transfer<span
                    class="copy-btn"
                    onclick="copyText(event, 'turbo-intruder -p race.py -t 20 https://target.com/api/transfer')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests for race conditions by sending multiple concurrent requests to exploit
                      timing windows</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing operations that should be atomic like transfers, redemptions,
                      or limit checks</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Double-spending, limit bypasses, duplicate actions, state corruption
                      vulnerabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'turbo-intruder -p race.py -t 20 https://target.com/api/transfer')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('race-conditions')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('race-conditions')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="privilege escalation web horizontal vertical access control">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Privilege Escalation Patterns</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Test role parameters: role=admin, is_admin=true, admin=1,
                  type=administrator<span class="copy-btn"
                    onclick="copyText(event, 'Test role parameters: role=admin, is_admin=true, admin=1, type=administrator')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests for privilege escalation by manipulating role-related parameters and
                      headers</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing role-based access control (RBAC) implementations in web
                      applications</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Horizontal privilege escalation, vertical privilege escalation, role bypass,
                      admin access</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Test role parameters: role=admin, is_admin=true, admin=1, type=administrator')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('priv-esc')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('priv-esc')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="business logic abuse coupon manipulation price bypass">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Business Logic Abuse</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Test: negative quantities, decimal quantities, coupon stacking, currency
                  switching, refund loops<span class="copy-btn"
                    onclick="copyText(event, 'Test: negative quantities, decimal quantities, coupon stacking, currency switching, refund loops')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests business rules and validations for flaws that allow financial or
                      operational abuse</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing e-commerce, financial, or transactional applications with
                      complex business rules</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Price manipulation, coupon abuse, currency arbitrage, inventory
                      manipulation, refund fraud</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Test: negative quantities, decimal quantities, coupon stacking, currency switching, refund loops')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('biz-logic')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('biz-logic')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="cors misconfiguration cross origin resource sharing bypass">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">CORS Misconfiguration</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">curl -I -H "Origin: https://evil.com" https://target.com/api/user | grep -i
                  "access-control-allow-origin"<span class="copy-btn"
                    onclick="copyText(event, 'curl -I -H &quot;Origin: https://evil.com&quot; https://target.com/api/user | grep -i &quot;access-control-allow-origin&quot;')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Tests for CORS misconfigurations that could allow cross-origin attacks and
                      data theft</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing APIs and endpoints that may have overly permissive CORS
                      policies</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Wildcard CORS, reflected origins, null origin allowed, credentials with
                      wildcards</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-medium">Medium</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'curl -I -H &quot;Origin: https://evil.com&quot; https://target.com/api/user | grep -i &quot;access-control-allow-origin&quot;')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('cors')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('cors')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>

          <div class="command-card" data-search="web cache deception poisoning cdn cloudflare akamai">
            <div class="command-header" onclick="toggleCard(this)">
              <span class="command-name">Web Cache Analysis</span>
              <i class="fas fa-chevron-down command-toggle"></i>
            </div>
            <div class="command-body">
              <div class="command-content">
                <div class="code-block">Test cache behavior: Add cache-buster, check X-Cache headers, test path
                  normalization<span class="copy-btn"
                    onclick="copyText(event, 'Test cache behavior: Add cache-buster, check X-Cache headers, test path normalization')"><i
                      class="fas fa-copy"></i> Copy</span></div>
                <div class="info-grid">
                  <div class="info-item">
                    <div class="info-label">What it does</div>
                    <div class="info-value">Analyzes web caching behavior to find cache deception and poisoning
                      vulnerabilities</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">When to use</div>
                    <div class="info-value">When testing applications behind CDNs or caching proxies like Cloudflare,
                      Akamai, or Varnish</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">What it finds</div>
                    <div class="info-value">Cache poisoning, cache deception, sensitive data caching, cache key
                      manipulation</div>
                  </div>
                  <div class="info-item">
                    <div class="info-label">Difficulty</div>
                    <div class="info-value"><span class="difficulty-badge difficulty-hard">Hard</span></div>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="card-btn"
                    onclick="copyText(event, 'Test cache behavior: Add cache-buster, check X-Cache headers, test path normalization')"><i
                      class="fas fa-copy"></i> Copy</button>
                  <button class="card-btn" onclick="showExplain('cache-analysis')"><i class="fas fa-lightbulb"></i>
                    Explain</button>
                  <button class="card-btn" onclick="showUseCase('cache-analysis')"><i class="fas fa-briefcase"></i> Use
                    Case</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ==================== GOOGLE DORKS ==================== -->
      <section class="section" id="google-dorks">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(234,67,53,0.15); color: #ea4335;"><i
              class="fab fa-google"></i>
          </div>
          <div class="section-title-group">
            <h2>Google Dorks</h2>
            <p>Advanced search operators to discover sensitive files, admin panels, credentials, and exposed directories
            </p>
          </div>
        </div>
        <div class="dork-grid">
          <div class="dork-card">
            <h4><i class="fas fa-file-shield"></i> Sensitive Files</h4>
            <ul class="dork-list">
              <li>site:target.com filetype:env <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:env')"></i></li>
              <li>site:target.com filetype:log <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:log')"></i></li>
              <li>site:target.com filetype:sql <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:sql')"></i></li>
              <li>site:target.com filetype:bak <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:bak')"></i></li>
              <li>site:target.com filetype:zip <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:zip')"></i></li>
              <li>site:target.com inurl:config <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:config')"></i></li>
              <li>site:target.com ext:xml | ext:conf | ext:cnf | ext:reg | ext:inf | ext:rdp | ext:cfg | ext:txt |
                ext:ora | ext:ini <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com ext:xml | ext:conf | ext:cnf | ext:reg | ext:inf | ext:rdp | ext:cfg | ext:txt | ext:ora | ext:ini')"></i>
              </li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-user-shield"></i> Admin Panels</h4>
            <ul class="dork-list">
              <li>site:target.com inurl:admin <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:admin')"></i></li>
              <li>site:target.com inurl:login <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:login')"></i></li>
              <li>site:target.com intitle:"login" "admin" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;login&quot; &quot;admin&quot;')"></i></li>
              <li>site:target.com inurl:wp-admin <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:wp-admin')"></i></li>
              <li>site:target.com inurl:phpmyadmin <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:phpmyadmin')"></i></li>
              <li>site:target.com intitle:"index of" "admin" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;index of&quot; &quot;admin&quot;')"></i></li>
              <li>site:target.com inurl:cpanel <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:cpanel')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-key"></i> Credentials Exposure</h4>
            <ul class="dork-list">
              <li>site:target.com intext:"password" filetype:txt <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;password&quot; filetype:txt')"></i></li>
              <li>site:target.com intext:"api_key" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;api_key&quot;')"></i></li>
              <li>site:target.com "-----BEGIN RSA PRIVATE KEY-----" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com &quot;-----BEGIN RSA PRIVATE KEY-----&quot;')"></i></li>
              <li>site:target.com intext:"jdbc:mysql" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;jdbc:mysql&quot;')"></i></li>
              <li>site:target.com intext:"aws_access_key_id" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;aws_access_key_id&quot;')"></i></li>
              <li>site:target.com ext:sql intext:"INSERT INTO" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com ext:sql intext:&quot;INSERT INTO&quot;')"></i></li>
              <li>site:target.com intext:"token" filetype:json <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;token&quot; filetype:json')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-plug"></i> API Discovery</h4>
            <ul class="dork-list">
              <li>site:target.com inurl:api <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:api')"></i></li>
              <li>site:target.com inurl:swagger <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:swagger')"></i></li>
              <li>site:target.com intitle:"api" "documentation" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;api&quot; &quot;documentation&quot;')"></i>
              </li>
              <li>site:target.com inurl:graphql <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:graphql')"></i></li>
              <li>site:target.com filetype:json inurl:api <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:json inurl:api')"></i></li>
              <li>site:target.com inurl:/v1/ | inurl:/v2/ | inurl:/api/v1/ <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:/v1/ | inurl:/v2/ | inurl:/api/v1/')"></i></li>
              <li>site:target.com intitle:"index of" "api" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;index of&quot; &quot;api&quot;')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-folder-open"></i> Directory Exposure</h4>
            <ul class="dork-list">
              <li>site:target.com intitle:"index of" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;index of&quot;')"></i></li>
              <li>site:target.com intitle:"index of" "parent directory" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;index of&quot; &quot;parent directory&quot;')"></i>
              </li>
              <li>site:target.com intitle:"index of" "backup" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;index of&quot; &quot;backup&quot;')"></i></li>
              <li>site:target.com inurl:backup <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:backup')"></i></li>
              <li>site:target.com inurl:.git <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:.git')"></i></li>
              <li>site:target.com inurl:.env <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com inurl:.env')"></i></li>
              <li>site:target.com intitle:"index of" "config" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intitle:&quot;index of&quot; &quot;config&quot;')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-database"></i> Database & Config</h4>
            <ul class="dork-list">
              <li>site:target.com ext:sql | ext:dbf | ext:mdb <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com ext:sql | ext:dbf | ext:mdb')"></i></li>
              <li>site:target.com intext:"phpMyAdmin" "running on" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;phpMyAdmin&quot; &quot;running on&quot;')"></i>
              </li>
              <li>site:target.com intext:"sql syntax near" | intext:"syntax error has occurred" | intext:"incorrect
                syntax near" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;sql syntax near&quot; | intext:&quot;syntax error has occurred&quot; | intext:&quot;incorrect syntax near&quot;')"></i>
              </li>
              <li>site:target.com intext:"Connection refused" | intext:"SQLServer JDBC Driver" <i
                  class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;Connection refused&quot; | intext:&quot;SQLServer JDBC Driver&quot;')"></i>
              </li>
              <li>site:target.com filetype:yaml | filetype:yml <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com filetype:yaml | filetype:yml')"></i></li>
              <li>site:target.com intext:"mongodb://" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;mongodb://&quot;')"></i></li>
              <li>site:target.com intext:"redis://" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'site:target.com intext:&quot;redis://&quot;')"></i></li>
            </ul>
          </div>
        </div>
      </section>

      <!-- ==================== SHODAN DORKS ==================== -->
      <section class="section" id="shodan">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(210,153,34,0.15); color: var(--accent-yellow);"><i
              class="fas fa-satellite-dish"></i></div>
          <div class="section-title-group">
            <h2>Shodan Dorks</h2>
            <p>Search engine queries for discovering exposed servers, IoT devices, and network infrastructure</p>
          </div>
        </div>
        <div class="dork-grid">
          <div class="dork-card">
            <h4><i class="fas fa-server"></i> Exposed Servers</h4>
            <ul class="dork-list">
              <li>hostname:"target.com" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'hostname:&quot;target.com&quot;')"></i></li>
              <li>ssl:"target.com" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'ssl:&quot;target.com&quot;')"></i></li>
              <li>org:"Target Organization" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'org:&quot;Target Organization&quot;')"></i></li>
              <li>net:192.168.1.0/24 <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'net:192.168.1.0/24')"></i>
              </li>
              <li>asn:AS15169 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'asn:AS15169')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-lock-open"></i> Default Credentials</h4>
            <ul class="dork-list">
              <li>default password <i class="fas fa-copy copy-icon" onclick="copyText(event, 'default password')"></i>
              </li>
              <li>"default password" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;default password&quot;')"></i></li>
              <li>"admin" "password" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;admin&quot; &quot;password&quot;')"></i></li>
              <li>"Authentication: none" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Authentication: none&quot;')"></i></li>
              <li>"Set-Cookie: admin=yes" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Set-Cookie: admin=yes&quot;')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-network-wired"></i> Open Ports</h4>
            <ul class="dork-list">
              <li>port:21 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:21')"></i></li>
              <li>port:22 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:22')"></i></li>
              <li>port:80 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:80')"></i></li>
              <li>port:443 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:443')"></i></li>
              <li>port:3389 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:3389')"></i></li>
              <li>port:3306 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:3306')"></i></li>
              <li>port:5900 <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port:5900')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-video"></i> IoT & Cameras</h4>
            <ul class="dork-list">
              <li>"Server: yawcam" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: yawcam&quot;')"></i></li>
              <li>"Server: IP Webcam" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: IP Webcam&quot;')"></i></li>
              <li>"Server: webcamXP" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: webcamXP&quot;')"></i></li>
              <li>"Server: MJPG-Streamer" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: MJPG-Streamer&quot;')"></i></li>
              <li>"Hikvision" <i class="fas fa-copy copy-icon" onclick="copyText(event, '&quot;Hikvision&quot;')"></i>
              </li>
              <li>"DVRDVS-Webs" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;DVRDVS-Webs&quot;')"></i>
              </li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-router"></i> Routers & Network</h4>
            <ul class="dork-list">
              <li>"Server: MikroTik" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: MikroTik&quot;')"></i></li>
              <li>"Server: nginx" "Router" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: nginx&quot; &quot;Router&quot;')"></i></li>
              <li>"Server: Apache" "Router" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: Apache&quot; &quot;Router&quot;')"></i></li>
              <li>"Cisco" "IOS" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Cisco&quot; &quot;IOS&quot;')"></i></li>
              <li>"Server: TP-LINK" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Server: TP-LINK&quot;')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-industry"></i> Industrial & SCADA</h4>
            <ul class="dork-list">
              <li>"SCADA" <i class="fas fa-copy copy-icon" onclick="copyText(event, '&quot;SCADA&quot;')"></i></li>
              <li>"Modbus" <i class="fas fa-copy copy-icon" onclick="copyText(event, '&quot;Modbus&quot;')"></i></li>
              <li>"Siemens" <i class="fas fa-copy copy-icon" onclick="copyText(event, '&quot;Siemens&quot;')"></i></li>
              <li>"Schneider Electric" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, '&quot;Schneider Electric&quot;')"></i></li>
              <li>"Rockwell" <i class="fas fa-copy copy-icon" onclick="copyText(event, '&quot;Rockwell&quot;')"></i>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- ==================== FOFA DORKS ==================== -->
      <section class="section" id="fofa">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(88,166,255,0.15); color: var(--accent-cyan);"><i
              class="fas fa-search"></i></div>
          <div class="section-title-group">
            <h2>FOFA Dorks</h2>
            <p>FOFA (Fingerprint of Full Asset) queries for asset discovery and fingerprinting</p>
          </div>
        </div>
        <div class="dork-grid">
          <div class="dork-card">
            <h4><i class="fas fa-heading"></i> Title-Based Search</h4>
            <ul class="dork-list">
              <li>title="admin" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'title=&quot;admin&quot;')"></i>
              </li>
              <li>title="login" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'title=&quot;login&quot;')"></i>
              </li>
              <li>title="dashboard" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'title=&quot;dashboard&quot;')"></i></li>
              <li>title="phpMyAdmin" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'title=&quot;phpMyAdmin&quot;')"></i></li>
              <li>title="Apache" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'title=&quot;Apache&quot;')"></i>
              </li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-header"></i> Header-Based Detection</h4>
            <ul class="dork-list">
              <li>header="Apache" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'header=&quot;Apache&quot;')"></i></li>
              <li>header="nginx" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'header=&quot;nginx&quot;')"></i>
              </li>
              <li>header="IIS" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'header=&quot;IIS&quot;')"></i>
              </li>
              <li>header="PHP" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'header=&quot;PHP&quot;')"></i>
              </li>
              <li>header="ASP.NET" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'header=&quot;ASP.NET&quot;')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-globe"></i> Country & Region</h4>
            <ul class="dork-list">
              <li>country="US" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'country=&quot;US&quot;')"></i>
              </li>
              <li>country="CN" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'country=&quot;CN&quot;')"></i>
              </li>
              <li>country="JP" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'country=&quot;JP&quot;')"></i>
              </li>
              <li>region="Beijing" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'region=&quot;Beijing&quot;')"></i></li>
              <li>city="Shanghai" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'city=&quot;Shanghai&quot;')"></i></li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-plug"></i> Port Filtering</h4>
            <ul class="dork-list">
              <li>port="21" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port=&quot;21&quot;')"></i></li>
              <li>port="22" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port=&quot;22&quot;')"></i></li>
              <li>port="80" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port=&quot;80&quot;')"></i></li>
              <li>port="443" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port=&quot;443&quot;')"></i>
              </li>
              <li>port="3306" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'port=&quot;3306&quot;')"></i>
              </li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-certificate"></i> SSL Certificate</h4>
            <ul class="dork-list">
              <li>cert="target.com" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'cert=&quot;target.com&quot;')"></i></li>
              <li>cert.subject="target.com" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'cert.subject=&quot;target.com&quot;')"></i></li>
              <li>cert.issuer="Let's Encrypt" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'cert.issuer=&quot;Let\'s Encrypt&quot;')"></i></li>
              <li>cert.is_valid=true <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'cert.is_valid=true')"></i>
              </li>
            </ul>
          </div>
          <div class="dork-card">
            <h4><i class="fas fa-code"></i> Body & Content</h4>
            <ul class="dork-list">
              <li>body="admin" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'body=&quot;admin&quot;')"></i>
              </li>
              <li>body="login" <i class="fas fa-copy copy-icon" onclick="copyText(event, 'body=&quot;login&quot;')"></i>
              </li>
              <li>body="index of /" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'body=&quot;index of /&quot;')"></i></li>
              <li>body="phpinfo()" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'body=&quot;phpinfo()&quot;')"></i></li>
              <li>body="Apache" <i class="fas fa-copy copy-icon"
                  onclick="copyText(event, 'body=&quot;Apache&quot;')"></i>
              </li>
            </ul>
          </div>
        </div>
      </section>
      <!-- ==================== BUG BOUNTY WORKFLOW ==================== -->
      <section class="section" id="workflow">
        <div class="section-header">
          <div class="section-icon" style="background: rgba(63,185,80,0.15); color: var(--accent-green);"><i
              class="fas fa-diagram-project"></i></div>
          <div class="section-title-group">
            <h2>Recon Workflow (Real Bug Bounty Flow)</h2>
            <p>Step-by-step methodology used by professional bug bounty hunters for comprehensive target reconnaissance
            </p>
          </div>
        </div>
        <div class="workflow-container">
          <div class="workflow-steps">
            <div class="workflow-step">
              <div class="step-number">1</div>
              <div class="step-content">
                <h4>Subdomain Enumeration</h4>
                <p>Begin by discovering all subdomains of the target to expand the attack surface. Use passive sources
                  first, then active brute-forcing.</p>
                <div class="step-tools">
                  <span class="tool-tag">subfinder</span>
                  <span class="tool-tag">amass</span>
                  <span class="tool-tag">assetfinder</span>
                  <span class="tool-tag">crt.sh</span>
                  <span class="tool-tag">altdns</span>
                  <span class="tool-tag">massdns</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">2</div>
              <div class="step-content">
                <h4>Live Host Discovery</h4>
                <p>Filter discovered subdomains to identify live hosts. Probe HTTP/HTTPS services and capture
                  screenshots for visual inspection.</p>
                <div class="step-tools">
                  <span class="tool-tag">httpx</span>
                  <span class="tool-tag">naabu</span>
                  <span class="tool-tag">aquatone</span>
                  <span class="tool-tag">nmap</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">3</div>
              <div class="step-content">
                <h4>Directory & File Discovery</h4>
                <p>Brute-force directories and files on live hosts to find hidden endpoints, admin panels, and backup
                  files.</p>
                <div class="step-tools">
                  <span class="tool-tag">ffuf</span>
                  <span class="tool-tag">gobuster</span>
                  <span class="tool-tag">dirsearch</span>
                  <span class="tool-tag">feroxbuster</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">4</div>
              <div class="step-content">
                <h4>Parameter Fuzzing</h4>
                <p>Discover hidden GET and POST parameters. Test for IDOR vulnerabilities and API parameter injection
                  points.</p>
                <div class="step-tools">
                  <span class="tool-tag">arjun</span>
                  <span class="tool-tag">ffuf</span>
                  <span class="tool-tag">Param Miner</span>
                  <span class="tool-tag">Burp Suite</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">5</div>
              <div class="step-content">
                <h4>JavaScript Analysis</h4>
                <p>Extract endpoints, API keys, and secrets from JavaScript files. Check for source maps and hidden
                  functionality.</p>
                <div class="step-tools">
                  <span class="tool-tag">LinkFinder</span>
                  <span class="tool-tag">SecretFinder</span>
                  <span class="tool-tag">subjs</span>
                  <span class="tool-tag">source maps</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">6</div>
              <div class="step-content">
                <h4>API Testing</h4>
                <p>Discover API endpoints, test for GraphQL introspection, find Swagger docs, and versioned API paths.
                </p>
                <div class="step-tools">
                  <span class="tool-tag">kiterunner</span>
                  <span class="tool-tag">ffuf</span>
                  <span class="tool-tag">GraphQL</span>
                  <span class="tool-tag">Swagger</span>
                  <span class="tool-tag">Postman</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">7</div>
              <div class="step-content">
                <h4>Dorking Phase</h4>
                <p>Use Google Dorks, Shodan, and FOFA to find exposed assets, credentials, and misconfigurations that
                  automated tools missed.</p>
                <div class="step-tools">
                  <span class="tool-tag">Google Dorks</span>
                  <span class="tool-tag">Shodan</span>
                  <span class="tool-tag">FOFA</span>
                  <span class="tool-tag">Wayback Machine</span>
                </div>
              </div>
            </div>
            <div class="workflow-step">
              <div class="step-number">8</div>
              <div class="step-content">
                <h4>Manual Validation</h4>
                <p>Manually verify all findings. Test for logic flaws, business logic abuse, race conditions, and
                  authentication bypasses.</p>
                <div class="step-tools">
                  <span class="tool-tag">Burp Suite</span>
                  <span class="tool-tag">Browser DevTools</span>
                  <span class="tool-tag">curl</span>
                  <span class="tool-tag">Manual Testing</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Modal -->
      <div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
        <div class="modal" onclick="event.stopPropagation()">
          <div class="modal-header">
            <h3 id="modalTitle">Explanation</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
          </div>
          <div class="modal-body" id="modalBody">
            <!-- Content populated by JS -->
          </div>
        </div>
      </div>

      <!-- Toast -->
      <div class="toast" id="toast"><i class="fas fa-check-circle"></i> <span id="toastMsg">Copied to clipboard!</span>
      </div>

      <!-- Mobile Toggle -->
      <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/footer.php'; ?>
    </main>

  </div>

  <script>
  function toggleCard(header) {
    // .closest بتدور لفوق في الـ HTML لحد ما تلاقي أول عنصر واخد كلاس command-card بالتحديد
    const currentCard = header.closest('.command-card');

    if (currentCard) {
      currentCard.classList.toggle('expanded');
    }
  }

  // Expand all cards
  function expandAll() {
    document.querySelectorAll('.command-card').forEach(card => card.classList.add('expanded'));
  }

  // Collapse all cards
  function collapseAll() {
    document.querySelectorAll('.command-card').forEach(card => card.classList.remove('expanded'));
  }

  // Copy text to clipboard
  function copyText(event, text) {
    event.stopPropagation();
    navigator.clipboard.writeText(text).then(() => {
      showToast('Copied to clipboard!');
    }).catch(() => {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
      showToast('Copied to clipboard!');
    });
  }

  // Show toast
  function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
  }

  // Modal data
  const explainData = {
    'whatweb': {
      title: 'whatweb - Technology Fingerprinting',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>WhatWeb identifies websites by matching fingerprints against a database of known technologies. It analyzes HTTP headers, HTML content, JavaScript, CSS, and other page elements to determine the tech stack.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Over 1800 plugins for technology detection</li><li>Passive and aggressive scanning modes</li><li>Custom plugin support</li><li>JSON and XML output formats</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Understanding the technology stack helps security researchers map known vulnerabilities to specific versions. For example, identifying WordPress 5.8.1 allows checking for CVEs affecting that version.</p></div>
                `
    },
    'httpx': {
      title: 'httpx - Fast HTTP Prober',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>httpx sends HTTP requests to a list of targets and analyzes responses to determine live hosts, technologies, titles, and status codes. It uses fast concurrency and smart probing.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Probes multiple protocols (HTTP/HTTPS)</li><li>Technology detection via response analysis</li><li>Custom request support</li><li>CDN and WAF detection</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Learning to filter live hosts from large subdomain lists is crucial for efficient reconnaissance. httpx teaches the importance of HTTP probing before deeper testing.</p></div>
                `
    },
    'wappalyzer': {
      title: 'wappalyzer - Technology Detection',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Wappalyzer uses browser extension technology to analyze page content, headers, scripts, and network requests to identify technologies used by a website.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Browser extension and CLI versions</li><li>Over 3000 technologies in database</li><li>Real-time detection as you browse</li><li>Technology categorization</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Understanding how technology fingerprinting works helps developers avoid information leakage and helps researchers identify potential attack vectors.</p></div>
                `
    },
    'curl': {
      title: 'curl - Header Analysis',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>curl is a command-line tool for transferring data with URLs. The -I flag fetches only headers, and -L follows redirects, allowing analysis of the complete response chain.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Custom header support</li><li>Multiple protocol support</li><li>Cookie handling</li><li>Proxy support</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Header analysis teaches HTTP protocol fundamentals and security header importance. Missing security headers like CSP, HSTS, and X-Frame-Options indicate potential vulnerabilities.</p></div>
                `
    },
    'nuclei': {
      title: 'nuclei - Vulnerability Scanner',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>nuclei uses YAML-based templates to send crafted requests and match responses against vulnerability signatures. It can detect known CVEs, misconfigurations, and exposures.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Template-based scanning</li><li>Community-driven template repository</li><li>Fast concurrent execution</li><li>Custom template support</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Studying nuclei templates teaches how vulnerabilities manifest in HTTP responses and helps understand the signatures of known security issues.</p></div>
                `
    },
    'waybackurls': {
      title: 'waybackurls - Historical URL Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Queries the Internet Archive's Wayback Machine API to retrieve historical snapshots of URLs for a given domain, revealing endpoints that may no longer be linked but still exist.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Passive reconnaissance</li><li>No direct target interaction</li><li>Date range filtering</li><li>Subdomain support</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Historical analysis teaches that deleted content often persists. Old API versions, debug endpoints, and test pages frequently remain accessible long after being removed from navigation.</p></div>
                `
    },
    'gau': {
      title: 'gau - GetAllUrls',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>gau queries multiple data sources including Wayback Machine, Common Crawl, Alien Vault OTX, and URLScan to compile a comprehensive list of known URLs for a domain.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multiple source aggregation</li><li>Subdomain enumeration</li><li>JSON output support</li><li>Date filtering</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Understanding multiple data sources for URL discovery teaches the breadth of internet archiving and how digital footprints persist across services.</p></div>
                `
    },
    'aquatone': {
      title: 'aquatone - Visual Reconnaissance',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>aquatone uses headless Chrome to take screenshots of websites and generates an HTML report with visual thumbnails, making it easy to identify interesting targets at a glance.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Headless browser screenshots</li><li>HTML report generation</li><li>Technology detection</li><li>Responsive design testing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Visual reconnaissance teaches the importance of human review in automated processes. Screenshots can reveal admin panels, error messages, and exposed services that automated tools miss.</p></div>
                `
    },
    'ffuf-dir': {
      title: 'ffuf - Directory Brute Force',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>ffuf (Fuzz Faster U Fool) is a fast web fuzzer that sends requests with wordlist entries substituted at the FUZZ keyword position, then filters responses by status code, size, or word count.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Fast concurrent fuzzing</li><li>Multiple output formats</li><li>Recursion support</li><li>Custom headers and methods</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Directory brute-forcing teaches how web servers handle unknown paths and how to distinguish between valid responses (200), redirects (301/302), and forbidden access (403).</p></div>
                `
    },
    'gobuster': {
      title: 'gobuster - Directory/File/DNS Brute Forcer',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>gobuster sends HTTP requests with wordlist entries appended to the target URL. It supports directory, DNS, vhost, and fuzz modes with extension support.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multiple modes (dir, dns, vhost, fuzz)</li><li>Extension brute-forcing</li><li>Wildcard detection</li><li>TLS certificate enumeration</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>gobuster teaches the fundamentals of brute-forcing web paths and the importance of testing multiple file extensions to find backup and config files.</p></div>
                `
    },
    'dirsearch': {
      title: 'dirsearch - Python Directory Scanner',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>dirsearch is a Python-based tool that brute-forces directories and files with recursive scanning, extension testing, and smart filtering to reduce false positives.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Recursive scanning</li><li>Multiple extension support</li><li>Custom wordlists</li><li>Report generation</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>dirsearch teaches recursive enumeration concepts and how to handle common web server responses to avoid false positives in directory scanning.</p></div>
                `
    },
    'feroxbuster': {
      title: 'feroxbuster - Rust Recursive Scanner',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>feroxbuster is written in Rust for performance and uses recursion to follow links discovered during scanning, making it effective at finding deeply nested content.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Recursive content discovery</li><li>Smart filtering</li><li>State management</li><li>Configuration files</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>feroxbuster teaches recursive scanning concepts and how modern tools optimize for speed while maintaining accuracy in content discovery.</p></div>
                `
    },
    'backup-discovery': {
      title: 'Backup File Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Uses specialized wordlists containing common backup file patterns (.bak, .old, .zip, .sql) to find files that may contain sensitive data or source code.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Specialized backup wordlists</li><li>Source code archive detection</li><li>Database dump discovery</li><li>Config file exposure</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Backup discovery teaches that development artifacts frequently leak to production. Understanding common backup naming conventions helps identify these exposures.</p></div>
                `
    },
    'wfuzz': {
      title: 'wfuzz - Web Application Fuzzer',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>wfuzz is a versatile fuzzer that supports multiple payload types, encodings, and filters. It can fuzz parameters, headers, paths, and data simultaneously.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multiple payload encoders</li><li>Session handling</li><li>Proxy support</li><li>Custom plugins</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>wfuzz teaches advanced fuzzing concepts including payload manipulation, response filtering, and multi-position fuzzing for complex attack scenarios.</p></div>
                `
    },
    'git-exposure': {
      title: 'Git Exposure Detection',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests for the presence of .git directories by checking for the HEAD file. Exposed Git repositories can be downloaded entirely using tools like git-dumper.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Quick exposure check</li><li>No specialized tools needed</li><li>Source code leakage detection</li><li>Commit history exposure</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Git exposure teaches the importance of proper web server configuration. A single misconfigured .gitignore or server rule can expose an entire codebase.</p></div>
                `
    },
    'source-control': {
      title: 'Source Control Exposure',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Checks for exposed source control directories beyond Git, including SVN, Mercurial, Bazaar, and CVS which may contain historical code and credentials.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multi-SCM support</li><li>Legacy system detection</li><li>Historical data access</li><li>Automated checking</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Legacy source control exposure teaches that old systems often remain deployed and can contain valuable historical data including deleted credentials and code.</p></div>
                `
    },
    'subfinder': {
      title: 'subfinder - Passive Subdomain Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>subfinder queries over 50 passive sources including certificate transparency logs, search engines, and public APIs to discover subdomains without sending traffic to the target.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>50+ data sources</li><li>Rate limiting</li><li>Output formatting</li><li>Source filtering</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Passive reconnaissance teaches that much information is publicly available without ever touching the target. Certificate transparency logs alone can reveal extensive subdomain data.</p></div>
                `
    },
    'amass': {
      title: 'amass - Comprehensive Enumeration',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>amass uses both passive and active techniques including DNS resolution, brute-forcing, and network mapping to build a comprehensive picture of target infrastructure.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Passive and active modes</li><li>DNS resolution</li><li>Network mapping</li><li>Graph visualization</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>amass teaches comprehensive reconnaissance methodology, showing how passive and active techniques complement each other for complete attack surface mapping.</p></div>
                `
    },
    'assetfinder': {
      title: 'assetfinder - Lightweight Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>assetfinder queries certificate transparency logs and related domain sources to quickly find subdomains with minimal resource usage.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Fast execution</li><li>Low resource usage</li><li>Subdomain-only output</li><li>Related domain discovery</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>assetfinder teaches efficient reconnaissance and the value of certificate transparency as a passive data source for subdomain enumeration.</p></div>
                `
    },
    'crtsh': {
      title: 'crt.sh - Certificate Transparency',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Queries the crt.sh database which aggregates certificate transparency logs from multiple CT log servers to find subdomains listed in SSL certificates.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Free public API</li><li>JSON output</li><li>Wildcard certificate support</li><li>Historical certificate data</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Certificate transparency logs teach that SSL certificates are public records. Every subdomain with a certificate is logged, making CT a powerful passive reconnaissance source.</p></div>
                `
    },
    'dnsrecon': {
      title: 'dnsrecon - DNS Reconnaissance',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>dnsrecon performs DNS enumeration including zone transfers, record enumeration, and brute-forcing to discover DNS-based information about targets.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Zone transfer testing</li><li>DNS record enumeration</li><li>Brute-forcing</li><li>Reverse lookup</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>DNS reconnaissance teaches how the DNS system works and how misconfigurations like zone transfers can leak entire network topologies.</p></div>
                `
    },
    'altdns': {
      title: 'altdns - Permutation Generation',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>altdns takes known subdomains and mutation words, then generates permutations by combining them to find non-obvious subdomain names.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Permutation generation</li><li>Custom wordlists</li><li>Resolution support</li><li>Output filtering</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>altdns teaches that subdomain naming often follows predictable patterns. Understanding these patterns helps discover hidden infrastructure.</p></div>
                `
    },
    'massdns': {
      title: 'massdns - High-Speed Resolver',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>massdns uses asynchronous DNS resolution to query thousands of DNS servers simultaneously, achieving extremely high resolution speeds.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Asynchronous resolution</li><li>Custom resolver lists</li><li>Multiple output formats</li><li>High performance</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>massdns teaches DNS resolution at scale and the importance of using reliable DNS resolvers for accurate results in large-scale enumeration.</p></div>
                `
    },
    'ffuf-param': {
      title: 'ffuf - Parameter Fuzzing',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Fuzzes URL parameters by substituting wordlist entries at the FUZZ position in query strings, then analyzes response differences to identify valid parameters.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>GET parameter discovery</li><li>Response filtering</li><li>Custom wordlists</li><li>Concurrent execution</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Parameter fuzzing teaches that web applications often have hidden parameters that control functionality, debugging, or access control.</p></div>
                `
    },
    'ffuf-post': {
      title: 'ffuf - POST Parameter Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Sends POST requests with fuzzed parameter names in the request body to discover hidden POST parameters that affect application behavior.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>POST body fuzzing</li><li>Content-Type handling</li><li>Custom headers</li><li>Response analysis</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>POST parameter discovery teaches that hidden parameters exist in API endpoints and forms, not just in visible URL query strings.</p></div>
                `
    },
    'arjun': {
      title: 'arjun - HTTP Parameter Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>arjun sends requests with parameter candidates and analyzes response differences (status code, length, content) to identify valid parameters.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>GET/POST/JSON/XML support</li><li>Smart response analysis</li><li>Threading support</li><li>Output formatting</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>arjun teaches systematic parameter discovery and how to interpret HTTP response differences to identify valid parameters.</p></div>
                `
    },
    'idor': {
      title: 'IDOR - Insecure Direct Object Reference',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests for IDOR by manipulating object identifiers (IDs) in API endpoints to access resources belonging to other users or roles.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Sequential ID testing</li><li>GUID/UUID testing</li><li>Role-based testing</li><li>Response comparison</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>IDOR testing teaches the importance of server-side authorization checks. Client-side hiding of links is not sufficient protection against unauthorized access.</p></div>
                `
    },
    'param-miner': {
      title: 'Param Miner - Burp Suite Extension',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Param Miner guesses parameters, headers, and cookies by analyzing response differences when these values are added to requests.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Parameter guessing</li><li>Header guessing</li><li>Cookie guessing</li><li>Cache-buster detection</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Param Miner teaches that applications often have hidden inputs that can be discovered through differential response analysis.</p></div>
                `
    },
    'api-brute': {
      title: 'API Parameter Brute Force',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Brute forces API endpoint paths using wordlists of common API patterns to discover undocumented or hidden API functionality.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>REST API discovery</li><li>Version enumeration</li><li>Method testing</li><li>Response filtering</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>API brute-forcing teaches that APIs often have undocumented endpoints and that version management can leave old, insecure versions active.</p></div>
                `
    },
    'cookie-analysis': {
      title: 'Cookie Security Analysis',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Analyzes Set-Cookie headers to check for security attributes like Secure, HttpOnly, SameSite, and proper expiration settings.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Security flag checking</li><li>Domain scope analysis</li><li>Path validation</li><li>Expiration review</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Cookie analysis teaches HTTP cookie security fundamentals and how missing flags can lead to session hijacking and XSS attacks.</p></div>
                `
    },
    'jwt-inspect': {
      title: 'JWT Inspection',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Decodes JWT tokens to inspect header, payload, and signature components. Tests for weak algorithms, exposed secrets, and privilege escalation claims.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Token decoding</li><li>Algorithm analysis</li><li>Secret cracking</li><li>Claim manipulation</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>JWT inspection teaches token-based authentication security and common implementation flaws like algorithm confusion and weak secrets.</p></div>
                `
    },
    'session-analysis': {
      title: 'Session Token Analysis',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Collects multiple session tokens and analyzes them for predictability, entropy, and generation patterns that could enable session hijacking.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Entropy analysis</li><li>Pattern detection</li><li>Time-based analysis</li><li>Statistical testing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Session analysis teaches the importance of cryptographically secure random number generation for session tokens and the risks of predictable tokens.</p></div>
                `
    },
    'auth-bypass': {
      title: 'Auth Bypass Patterns',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests common authentication bypass techniques including header manipulation, path variations, HTTP verb tampering, and parameter pollution.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Header manipulation</li><li>Path normalization</li><li>Verb tampering</li><li>Parameter pollution</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Auth bypass testing teaches that authentication can fail in multiple ways beyond simple password guessing, including middleware misconfigurations.</p></div>
                `
    },
    'burp-sequencer': {
      title: 'Burp Sequencer',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Collects a large sample of tokens and performs statistical analysis to measure entropy and detect patterns that indicate weak randomness.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Statistical entropy analysis</li><li>Bit-level analysis</li><li>FIPS testing</li><li>Visual graphs</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Burp Sequencer teaches quantitative security analysis and how to measure the strength of random number generation in security tokens.</p></div>
                `
    },
    'oauth-analysis': {
      title: 'OAuth/SAML Flow Analysis',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Analyzes OAuth 2.0 and SAML authentication flows to find misconfigurations in redirect handling, state validation, and token exchange.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Flow interception</li><li>Parameter analysis</li><li>Token inspection</li><li>Redirect validation</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>OAuth/SAML analysis teaches federated authentication security and how complex flows can introduce vulnerabilities if not properly implemented.</p></div>
                `
    },
    'swagger-discovery': {
      title: 'Swagger/OpenAPI Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Searches for common Swagger UI and OpenAPI specification paths that may expose complete API documentation.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Common path brute-forcing</li><li>Specification parsing</li><li>Endpoint extraction</li><li>Schema analysis</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Swagger discovery teaches that API documentation should not be publicly accessible without authentication as it reveals the entire API surface.</p></div>
                `
    },
    'postman-discovery': {
      title: 'Postman Collection Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Searches for exposed Postman collection files that contain API endpoint definitions, environment variables, and sometimes authentication tokens.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Collection file discovery</li><li>Environment extraction</li><li>Request analysis</li><li>Token extraction</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Postman discovery teaches that developer tools and artifacts can leak sensitive information when accidentally deployed to production.</p></div>
                `
    },
    'graphql-introspection': {
      title: 'GraphQL Introspection',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Queries the GraphQL __schema introspection endpoint to retrieve the complete API schema including all types, queries, mutations, and subscriptions.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Schema discovery</li><li>Type enumeration</li><li>Query mapping</li><li>Field analysis</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>GraphQL introspection teaches that GraphQL APIs should disable introspection in production as it exposes the entire API structure to attackers.</p></div>
                `
    },
    'api-versions': {
      title: 'API Version Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Fuzzes API version identifiers in URL paths to discover deprecated or undocumented API versions that may have weaker security controls.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Version enumeration</li><li>Deprecated endpoint discovery</li><li>Security comparison</li><li>Change detection</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>API version discovery teaches that old API versions often remain active with weaker security, creating a shadow attack surface.</p></div>
                `
    },
    'kiterunner': {
      title: 'kiterunner - API Route Scanner',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Uses specialized API route wordlists and proper HTTP method handling to discover API endpoints that generic scanners might miss.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>API-specific wordlists</li><li>Method-aware scanning</li><li>Content-type handling</li><li>Response analysis</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>kiterunner teaches that API discovery requires specialized approaches beyond standard directory brute-forcing, including proper HTTP method testing.</p></div>
                `
    },
    'arjun-api': {
      title: 'Arjun - API Parameters',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Discovers parameters in JSON and XML API endpoints by sending requests with candidate parameters and analyzing how the response changes.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>JSON parameter discovery</li><li>XML parameter discovery</li><li>Nested object support</li><li>Response differential analysis</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Arjun teaches that APIs often accept undocumented parameters in JSON/XML payloads that can be discovered through differential response analysis.</p></div>
                `
    },
    'ffuf-vhost': {
      title: 'ffuf - Virtual Host Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Discovers virtual hosts by sending requests with different Host headers to find additional web applications hosted on the same IP address.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Host header fuzzing</li><li>Shared IP detection</li><li>Application isolation testing</li><li>Response comparison</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>VHost discovery teaches that multiple applications can share an IP address through virtual hosting, expanding the attack surface beyond the primary domain.</p></div>
                `
    },
    'nmap-portscan': {
      title: 'nmap - Port Scanning',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>nmap sends crafted packets to target ports and analyzes responses to determine port states (open, closed, filtered) and identify running services.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multiple scan types (SYN, Connect, UDP)</li><li>Service version detection</li><li>OS fingerprinting</li><li>NSE scripting</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>nmap teaches TCP/IP fundamentals, port states, and how different scan types interact with firewalls and IDS systems.</p></div>
                `
    },
    'masscan': {
      title: 'masscan - High-Speed Scanner',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>masscan uses a custom TCP/IP stack to send packets asynchronously, achieving scan rates of millions of packets per second.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Asynchronous scanning</li><li>High performance</li><li>Custom TCP/IP stack</li><li>Internet-scale capability</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>masscan teaches the performance limits of network scanning and how custom networking implementations can achieve speeds impossible with standard tools.</p></div>
                `
    },
    'service-enum': {
      title: 'Service Enumeration',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Uses Nmap Scripting Engine (NSE) to perform deep service enumeration, including version detection, banner grabbing, and vulnerability checking.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>NSE script execution</li><li>Version detection</li><li>Vulnerability scanning</li><li>Default credential checking</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Service enumeration teaches how to move from port discovery to service-specific testing, applying targeted checks based on identified services.</p></div>
                `
    },
    'banner-grab': {
      title: 'Banner Grabbing',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Connects to network services and captures the initial response (banner) which often contains software version and type information.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Simple TCP connections</li><li>Protocol-specific handling</li><li>Manual verification</li><li>Quick checks</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Banner grabbing teaches that services often voluntarily disclose version information, which can be used to identify known vulnerabilities.</p></div>
                `
    },
    'port-analysis': {
      title: 'Open Port Analysis',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Performs both TCP and UDP scanning to identify all listening services, including stealthy UDP services that are often overlooked.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>TCP SYN scanning</li><li>UDP scanning</li><li>Port range specification</li><li>Protocol-specific probes</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Port analysis teaches that UDP services are frequently forgotten in security assessments, creating blind spots in network security.</p></div>
                `
    },
    'naabu': {
      title: 'naabu - Fast Port Scanner',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>naabu is a Go-based port scanner optimized for speed and multiple target scanning, with SYN and CONNECT scan support.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Fast scanning</li><li>Multiple targets</li><li>CDN detection</li><li>WAF detection</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>naabu teaches modern port scanning techniques and the importance of speed when assessing large target sets in bug bounty programs.</p></div>
                `
    },
    's3-enum': {
      title: 'S3 Bucket Enumeration',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests S3 bucket names for existence and checks their ACL permissions to identify publicly readable or writable buckets.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Bucket name permutation</li><li>Permission checking</li><li>Public access detection</li><li>File listing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>S3 enumeration teaches cloud security fundamentals and how simple naming conventions can lead to exposed sensitive data in cloud storage.</p></div>
                `
    },
    'cloud-enum': {
      title: 'Multi-Cloud Storage Scan',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>cloud_enum tests for exposed storage containers across AWS S3, Azure Blob Storage, and Google Cloud Storage using permutations of target names.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multi-cloud support</li><li>Name permutation</li><li>Permission checking</li><li>Comprehensive coverage</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Multi-cloud scanning teaches that organizations often use multiple cloud providers, each with different security models and potential misconfigurations.</p></div>
                `
    },
    'storage-detect': {
      title: 'Exposed Storage Detection',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests specific S3 bucket URLs for public access by attempting to list contents without authentication using the AWS CLI.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Unauthenticated access testing</li><li>Permission verification</li><li>Content listing</li><li>Quick checks</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Storage detection teaches that cloud permissions can be complex and that public access can be granted accidentally through misconfigured ACLs or policies.</p></div>
                `
    },
    'cloud-misconfig': {
      title: 'Cloud Asset Misconfiguration',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Scout Suite scans cloud accounts for security misconfigurations across IAM, storage, networking, and other services using read-only API access.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multi-cloud support</li><li>Comprehensive checks</li><li>Report generation</li><li>Remediation guidance</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Cloud misconfiguration scanning teaches the complexity of cloud security and how default configurations often prioritize functionality over security.</p></div>
                `
    },
    'secret-scan': {
      title: 'Repository Secret Scanning',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>truffleHog searches through git commit history for high-entropy strings and regex patterns that match known secret formats like API keys and passwords.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Git history scanning</li><li>Entropy analysis</li><li>Regex matching</li><li>Commit-level detection</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Secret scanning teaches that credentials committed to version control persist in history even after deletion, creating long-term exposure risks.</p></div>
                `
    },
    'docker-registry': {
      title: 'Docker Registry Exposure',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Checks Docker Registry API endpoints for public access, which can expose container images, layers, and potentially embedded secrets.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Registry API querying</li><li>Image enumeration</li><li>Layer analysis</li><li>Secret extraction</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Docker registry exposure teaches that container images can contain sensitive data and that registry access control is critical for container security.</p></div>
                `
    },
    'js-endpoints': {
      title: 'JS Endpoint Extraction',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Crawls websites to collect JavaScript files, then analyzes them to extract API endpoints, URL patterns, and internal paths.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>JavaScript discovery</li><li>URL extraction</li><li>Path analysis</li><li>Endpoint mapping</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>JS endpoint extraction teaches that client-side code often contains hardcoded API endpoints and paths that reveal server-side functionality.</p></div>
                `
    },
    'linkfinder': {
      title: 'LinkFinder - JS URL Extraction',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>LinkFinder uses regex patterns to extract URLs and endpoints from JavaScript files, including those in minified and bundled code.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Regex-based extraction</li><li>Minified code support</li><li>Multiple output formats</li><li>Browser integration</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>LinkFinder teaches that JavaScript files are a goldmine for endpoint discovery and that even minified code can be analyzed for URL patterns.</p></div>
                `
    },
    'source-maps': {
      title: 'Source Map Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Checks JavaScript files for sourceMappingURL comments that point to .js.map files, which can be downloaded to reveal original unminified source code.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Source map detection</li><li>Original code recovery</li><li>Comment extraction</li><li>Developer information</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Source map discovery teaches that build processes can leak original source code and that source maps should never be deployed to production.</p></div>
                `
    },
    'secretfinder': {
      title: 'SecretFinder - JS Secret Detection',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>SecretFinder uses regex patterns to search for API keys, tokens, passwords, and other secrets embedded in JavaScript files.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Regex-based detection</li><li>Multiple secret types</li><li>HTML report generation</li><li>Custom patterns</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>SecretFinder teaches that client-side JavaScript frequently contains hardcoded credentials and that secrets should never be exposed in frontend code.</p></div>
                `
    },
    'jsmon': {
      title: 'JSMon - JS Change Monitoring',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Monitors JavaScript files for changes over time, alerting when new endpoints, parameters, or secrets are added to the codebase.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Change detection</li><li>Diff generation</li><li>Alert notifications</li><li>Historical tracking</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>JSMon teaches that web applications change continuously and that monitoring these changes can reveal new attack surfaces as they are deployed.</p></div>
                `
    },
    'subjs': {
      title: 'subjs - Subdomain JS Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>subjs discovers JavaScript files across all subdomains of a target, aggregating them for centralized analysis and endpoint extraction.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Multi-subdomain scanning</li><li>JavaScript URL extraction</li><li>CDN detection</li><li>Batch processing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>subjs teaches that JavaScript analysis should cover the entire subdomain attack surface, not just the primary domain, as different subdomains may expose different functionality.</p></div>
                `
    },
    'logic-flaws': {
      title: 'Logic Flaw Discovery',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Systematically manipulates application workflows by skipping steps, repeating actions, changing sequences, and manipulating state to find logic errors.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Workflow manipulation</li><li>State bypass testing</li><li>Sequence modification</li><li>Multi-step analysis</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Logic flaw testing teaches that security is not just about input validation but also about ensuring business processes cannot be abused through unexpected flows.</p></div>
                `
    },
    'race-conditions': {
      title: 'Race Condition Testing',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Sends multiple concurrent requests to exploit timing windows where operations that should be atomic are processed simultaneously.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Concurrent request sending</li><li>Timing manipulation</li><li>State collision testing</li><li>Limit bypass testing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Race condition testing teaches that concurrency issues can lead to security vulnerabilities when multiple operations modify shared state simultaneously.</p></div>
                `
    },
    'priv-esc': {
      title: 'Privilege Escalation Patterns',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests for privilege escalation by manipulating role-related parameters, headers, and cookies to gain unauthorized access to higher-privilege functionality.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Role parameter testing</li><li>Header manipulation</li><li>Cookie tampering</li><li>Access control bypass</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Privilege escalation testing teaches that client-side role indicators are not sufficient for access control and that server-side authorization is essential.</p></div>
                `
    },
    'biz-logic': {
      title: 'Business Logic Abuse',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests business rules and validations by providing unexpected inputs like negative quantities, decimal values, and invalid state combinations.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Input manipulation</li><li>Rule bypass testing</li><li>State abuse</li><li>Financial impact testing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Business logic testing teaches that applications must validate business rules server-side, as client-side validation can be easily bypassed.</p></div>
                `
    },
    'cors': {
      title: 'CORS Misconfiguration',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Tests CORS policies by sending requests with various Origin headers to check if the server allows cross-origin requests from untrusted domains.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Origin header testing</li><li>Wildcard detection</li><li>Credentials analysis</li><li>Null origin testing</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>CORS testing teaches that overly permissive cross-origin policies can allow malicious websites to access sensitive data from other domains.</p></div>
                `
    },
    'cache-analysis': {
      title: 'Web Cache Analysis',
      content: `
                    <div class="modal-section"><h4>How it works</h4><p>Analyzes web caching behavior by testing cache key variations, header influences, and path normalization to find cache poisoning and deception vulnerabilities.</p></div>
                    <div class="modal-section"><h4>Key Features</h4><ul><li>Cache key analysis</li><li>Header influence testing</li><li>Path normalization testing</li><li>Poisoning detection</li></ul></div>
                    <div class="modal-section"><h4>Educational Value</h4><p>Cache analysis teaches that caching proxies can be manipulated to serve malicious content to other users or leak sensitive cached data.</p></div>
                `
    }
  };

  // Show explain modal
  function showExplain(key) {
    const data = explainData[key];
    if (data) {
      document.getElementById('modalTitle').textContent = data.title;
      document.getElementById('modalBody').innerHTML = data.content;
      document.getElementById('modalOverlay').classList.add('active');
    }
  }

  // Show use case modal
  function showUseCase(key) {
    const data = useCaseData[key];
    if (data) {
      document.getElementById('modalTitle').textContent = data.title;
      document.getElementById('modalBody').innerHTML = data.content;
      document.getElementById('modalOverlay').classList.add('active');
    }
  }

  // Close modal
  function closeModal(event) {
    if (!event || event.target === document.getElementById('modalOverlay')) {
      document.getElementById('modalOverlay').classList.remove('active');
    }
  }

  // Search functionality
  document.getElementById('searchInput').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.command-card, .dork-card');
    const sections = document.querySelectorAll('.section');

    cards.forEach(card => {
      const searchText = card.getAttribute('data-search') || '';
      const textContent = card.textContent.toLowerCase();
      if (searchText.includes(query) || textContent.includes(query)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });

    // Show/hide sections based on visible cards
    sections.forEach(section => {
      const visibleCards = section.querySelectorAll(
        '.command-card:not([style*="display: none"]), .dork-card:not([style*="display: none"])');
      if (visibleCards.length === 0 && query.length > 0) {
        section.style.display = 'none';
      } else {
        section.style.display = '';
      }
    });
  });

  // Sidebar toggle for mobile
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
  }

  // Active nav item on scroll
  const sections = document.querySelectorAll('.section');
  const navItems = document.querySelectorAll('.nav-item');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      if (scrollY >= sectionTop - 100) {
        current = section.getAttribute('id');
      }
    });

    navItems.forEach(item => {
      item.classList.remove('active');
      if (item.getAttribute('href') === '#' + current) {
        item.classList.add('active');
      }
    });
  });

  // Close modal on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModal();
    }
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

</body>

</html>