<!DOCTYPE html>
<html>

<head>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="/DarkHunter/includes/css/footer.css">
</head>

<body>

  <!-- DarkHunter Footer -->
  <footer class="dh-footer">
    <div class="footer-container">

      <!-- Top Wave Divider -->
      <div class="footer-wave">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
            fill="url(#footerGradient)" />
          <defs>
            <linearGradient id="footerGradient" x1="0" y1="0" x2="1440" y2="0">
              <stop offset="0%" stop-color="#00ff88" stop-opacity="0.05" />
              <stop offset="50%" stop-color="#8800ff" stop-opacity="0.05" />
              <stop offset="100%" stop-color="#00ff88" stop-opacity="0.05" />
            </linearGradient>
          </defs>
        </svg>
      </div>

      <!-- Main Footer Content -->
      <div class="footer-content">

        <!-- Brand Column -->
        <div class="footer-col brand-col">
          <div class="footer-brand">
            <div class="footer-logo">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="footer-brand-name">DARK<span>HUNTER</span></h3>
          </div>
          <p class="footer-desc">
            Master the art of cybersecurity. Learn, practice, and hunt vulnerabilities in a safe environment.
          </p>
          <div class="footer-social">
            <a href="#" class="social-link" title="GitHub">
              <i class="fab fa-github"></i>
            </a>
            <a href="#" class="social-link" title="Discord">
              <i class="fab fa-discord"></i>
            </a>
            <a href="#" class="social-link" title="Twitter">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-link" title="YouTube">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>

        <!-- Quick Links Column -->
        <div class="footer-col">
          <h4 class="footer-heading">
            <i class="fas fa-bolt"></i> Quick Links
          </h4>
          <ul class="footer-links">
            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
            <li><a href="labs.php"><i class="fas fa-chevron-right"></i> Labs</a></li>
            <li><a href="Learning.php"><i class="fas fa-chevron-right"></i> Learning</a></li>
            <li><a href="community/community.php"><i class="fas fa-chevron-right"></i> Community</a></li>
            <li><a href="mobile_pentest_hub.php"><i class="fas fa-chevron-right"></i> Mobile</a></li>
          </ul>
        </div>

        <!-- Resources Column -->
        <div class="footer-col">
          <h4 class="footer-heading">
            <i class="fas fa-book"></i> Resources
          </h4>
          <ul class="footer-links">
            <li><a href="#"><i class="fas fa-chevron-right"></i> Documentation</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Blog</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Writeups</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Tools</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
          </ul>
        </div>

        <!-- Contact Column -->
        <div class="footer-col">
          <h4 class="footer-heading">
            <i class="fas fa-envelope"></i> Contact
          </h4>
          <ul class="footer-contact">
            <li>
              <i class="fas fa-envelope"></i>
              <span>support@darkhunter.com</span>
            </li>
            <li>
              <i class="fas fa-map-marker-alt"></i>
              <span>Cyber City, Digital World</span>
            </li>
            <li>
              <i class="fas fa-clock"></i>
              <span>24/7 CTF Support</span>
            </li>
          </ul>

          <!-- Newsletter -->
          <div class="footer-newsletter">
            <p>Subscribe to our newsletter</p>
            <form class="newsletter-form" onsubmit="return false;">
              <input type="email" placeholder="your@email.com" class="newsletter-input">
              <button type="submit" class="newsletter-btn">
                <i class="fas fa-paper-plane"></i>
              </button>
            </form>
          </div>
        </div>

      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom">
        <div class="footer-bottom-left">
          <p>© 2026 DarkHunter. All rights reserved.</p>
        </div>
        <div class="footer-bottom-right">
          <a href="#">Privacy Policy</a>
          <span class="divider">|</span>
          <a href="#">Terms of Service</a>
          <span class="divider">|</span>
          <a href="#">Cookie Policy</a>
        </div>
      </div>

    </div>
  </footer>
</body>

</html>