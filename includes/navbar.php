<?php
// navbar.php - DarkHunter Navigation Component
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav class="navbar">
  <div class="nav-container">
    <a href="/DarkHunter/public/index.php" class="logo">
      <span class="logo-icon">
        <i class="fas fa-shield-halved"></i>
      </span>
      <span class="logo-text">DARK HUNTER</span>
      <span class="logo-badge">v2.0</span>
    </a>

    <ul class="nav-links">
      <li>
        <a href="/DarkHunter/public/index.php"
          class="nav-link <?php echo ($current_page == 'index.php' && $current_dir == 'public') ? 'active' : ''; ?>">
          <i class="fas fa-chart-line"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/public/mobile_pentest_hub.php"
          class="nav-link <?php echo ($current_page == 'mobile_pentest_hub.php' && $current_dir == 'public') ? 'active' : ''; ?>">
          <i class="fas fa-mobile-screen-button"></i>
          <span>Mobile</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/public/labs.php"
          class="nav-link <?php echo $current_page == 'labs.php' ? 'active' : ''; ?>">
          <i class="fas fa-flask"></i>
          <span>Labs</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/Recon/index.php" class="nav-link <?php echo $current_dir == 'recon' ? 'active' : ''; ?>">
          <i class="fas fa-satellite-dish"></i> <span>Recon</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/ToolKit/toolkit.php"
          class="nav-link <?php echo $current_dir == 'toolkit.php' ? 'active' : ''; ?>">
          <i class="fas fa-terminal"></i> <span>ToolKit</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/public/learning.php"
          class="nav-link <?php echo $current_page == 'learning.php' ? 'active' : ''; ?>">
          <i class="fas fa-graduation-cap"></i>
          <span>Learning</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/public/community/community.php"
          class="nav-link <?php echo $current_page == 'community.php' ? 'active' : ''; ?>">
          <i class="fas fa-users"></i>
          <span>Community</span>
        </a>
      </li>
      <li>
        <a href="/DarkHunter/public/profile.php"
          class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
          <i class="fas fa-user-shield"></i>
          <span>My Account</span>
        </a>
      </li>
    </ul>

    <div class="nav-right">
      <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/DarkHunter/public/logout.php" class="nav-link logout">
        <i class="fas fa-power-off"></i>
        <span>Logout</span>
      </a>
      <?php else: ?>
      <a href="/DarkHunter/public/login.php" class="nav-link login">
        <i class="fas fa-right-to-bracket"></i>
        <span>Login</span>
      </a>
      <?php endif; ?>
    </div>

    <button class="mobile-toggle" id="mobileToggle">
      <i class="fas fa-bars"></i>
    </button>
  </div>

  <div class="mobile-menu" id="mobileMenu">
    <a href="/DarkHunter/public/index.php" class="mobile-link">Dashboard</a>
    <a href="/DarkHunter/public/labs.php" class="mobile-link">Labs</a>
  </div>
</nav>

<style>
/* ============================================
   DARKHUNTER NAVBAR - MODERN CYBERSECURITY UI
   ============================================ */

:root {
  --neon-green: #00ff88;
  --neon-cyan: #00ffff;
  --neon-red: #ff0040;
  --neon-purple: #8800ff;
  --neon-pink: #ff0088;
  --neon-yellow: #ffcc00;
  --neon-blue: #0088ff;
  --dark-bg: #0a0a0f;
  --panel-bg: rgba(15, 15, 25, 0.95);
  --card-bg: rgba(20, 20, 35, 0.8);
  --border-glow: rgba(0, 255, 136, 0.2);
  --text-primary: #ffffff;
  --text-secondary: rgba(255, 255, 255, 0.7);
  --text-muted: rgba(255, 255, 255, 0.5);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: var(--panel-bg);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border-glow);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
}

.nav-container {
  max-width: 1500px;
  /* تعديل: تكبير الحاوية شوية عشان تاخد مساحة أكبر للـ Tabs */
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 70px;
  padding: 0 30px;
  gap: 20px;
  /* تعديل: تقليل الـ gap */
}

/* Logo Styles */
.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.logo:hover {
  transform: scale(1.02);
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, var(--neon-purple), var(--neon-pink));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: white;
  box-shadow: 0 0 20px rgba(136, 0, 255, 0.5);
  animation: pulse-glow 3s ease-in-out infinite;
}

@keyframes pulse-glow {

  0%,
  100% {
    box-shadow: 0 0 20px rgba(136, 0, 255, 0.5);
  }

  50% {
    box-shadow: 0 0 30px rgba(136, 0, 255, 0.8);
  }
}

.logo-text {
  font-family: 'Orbitron', monospace;
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--text-primary);
  letter-spacing: 2px;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
}

.logo-badge {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.7rem;
  color: var(--neon-green);
  background: rgba(0, 255, 136, 0.1);
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid rgba(0, 255, 136, 0.3);
  margin-left: 5px;
}

/* Navigation Links */
.nav-links {
  display: flex;
  align-items: center;
  gap: 6px;
  /* تعديل: تقليل المسافة بين الزراير لتوفير مساحة */
  list-style: none;
  margin: 0;
  padding: 0 20px;
  /* تعديل */
  flex: 1;
  justify-content: flex-start;
  /* تعديل مهم جداً: ترحيل العناصر جهة اليمين/الشمال بعد اللوجو فوراً بدل التوسيط */
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  /* تعديل */
  padding: 8px 14px;
  /* تعديل: صغرنا الـ padding عشان الـ Tabs ما تفرشش أوي */
  color: var(--text-secondary);
  text-decoration: none;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  /* تعديل: تصغير الحجم حاجة بسيطة لزيادة الاستيعاب */
  font-weight: 500;
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  white-space: nowrap;
}

.nav-link i {
  font-size: 0.95rem;
  /* تعديل */
  transition: transform 0.3s ease;
}

.nav-link:hover {
  color: var(--text-primary);
  background: rgba(255, 255, 255, 0.05);
  transform: translateY(-2px);
}

.nav-link:hover i {
  transform: scale(1.1);
}

/* Active State */
.nav-link.active {
  color: var(--neon-green);
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
  box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 25%;
  right: 25%;
  height: 2px;
  background: var(--neon-green);
  box-shadow: 0 0 10px var(--neon-green);
  border-radius: 2px;
}

/* Right Side Actions */
.nav-right {
  flex-shrink: 0;
  margin-left: auto;
  /* تعديل مهم: يضمن إن زرار اللوج أوت يطير لآخر اليمين دايماً ويفتح مساحة */
}

.nav-right .nav-link {
  padding: 8px 18px;
  /* تعديل */
}

/* Logout Button */
.nav-link.logout {
  color: var(--neon-red);
  background: rgba(255, 0, 64, 0.08);
  border: 1px solid rgba(255, 0, 64, 0.2);
}

.nav-link.logout:hover {
  background: rgba(255, 0, 64, 0.2);
  border-color: rgba(255, 0, 64, 0.4);
  box-shadow: 0 0 25px rgba(255, 0, 64, 0.4);
}

/* Login Button */
.nav-link.login {
  color: var(--neon-green);
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.nav-link.login:hover {
  background: rgba(0, 255, 136, 0.2);
  box-shadow: 0 0 25px rgba(0, 255, 136, 0.4);
}

/* Mobile Toggle */
.mobile-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text-primary);
  font-size: 1.5rem;
  cursor: pointer;
  padding: 10px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.mobile-toggle:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Mobile Menu */
.mobile-menu {
  display: none;
  position: absolute;
  top: 70px;
  left: 0;
  right: 0;
  background: var(--panel-bg);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border-glow);
  padding: 20px;
  flex-direction: column;
  gap: 8px;
  transform: translateY(-100%);
  opacity: 0;
  transition: all 0.3s ease;
  pointer-events: none;
}

.mobile-menu.active {
  transform: translateY(0);
  opacity: 1;
  pointer-events: all;
}

.mobile-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px 20px;
  color: var(--text-secondary);
  text-decoration: none;
  font-family: 'Inter', sans-serif;
  font-size: 1rem;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.mobile-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--text-primary);
}

.mobile-link.active {
  color: var(--neon-green);
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
}

/* Responsive Design */

/* تعديل: Media query ذكي للشاشات المتوسطة (من 769px لحد 1250px) عشان الـ Navbar متبوظش لو الشاشة صغرت شوية */
@media (min-width: 769px) and (max-width: 1250px) {
  .logo-text {
    font-size: 1.1rem;
  }

  .logo-badge {
    display: none;
    /* إخفاء رقم الإصدار لتوفير مساحة على الشاشات المتوسطة */
  }

  .nav-links {
    padding: 0 10px;
    gap: 2px;
  }

  .nav-link {
    padding: 6px 10px;
    font-size: 0.8rem;
    gap: 4px;
  }

  .nav-link i {
    font-size: 0.85rem;
  }
}

@media (max-width: 768px) {
  .nav-links {
    display: none;
  }

  .nav-right {
    display: none;
  }

  .mobile-toggle {
    display: block;
  }

  .mobile-menu {
    display: flex;
  }

  .logo-badge {
    display: none;
  }
}
</style>

<script>
// Mobile menu toggle
document.getElementById('mobileToggle').addEventListener('click', function() {
  document.getElementById('mobileMenu').classList.toggle('active');
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
  const navbar = document.querySelector('.navbar');
  const mobileMenu = document.getElementById('mobileMenu');
  if (!navbar.contains(e.target) && mobileMenu.classList.contains('active')) {
    mobileMenu.classList.remove('active');
  }
});
</script>