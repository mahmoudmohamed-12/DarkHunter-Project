<!-- Overlay -->
<div id="loginModal" class="modal-overlay">

  <!-- Modal Box -->
  <div class="modal-box">

    <span class="close-btn" onclick="LoginModal.close()">×</span>

    <h2>🔐 Access Required</h2>
    <p>You need to login to access this feature.</p>

    <div class="modal-actions">
      <a href="/DarkHunter/Public/login.php" class="btn-primary">Login Now</a>
      <button id="guestBtn" class="btn-secondary" onclick="LoginModal.close()">
        Continue as Guest
      </button>
    </div>

  </div>
</div>

<style>
/* ============================================
   CYBERSECURITY MODAL - DARK THEME
   Same structure, upgraded design
   ============================================ */

/* Overlay - Enhanced with scanlines */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(5, 5, 8, 0.92);
  backdrop-filter: blur(12px);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.modal-overlay.active {
  opacity: 1;
}

/* Scanline effect on overlay */
.modal-overlay::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: repeating-linear-gradient(0deg,
      rgba(0, 240, 255, 0.02) 0px,
      rgba(0, 240, 255, 0.02) 1px,
      transparent 1px,
      transparent 3px);
  pointer-events: none;
  animation: scanline 6s linear infinite;
}

@keyframes scanline {
  0% {
    transform: translateY(-100%);
  }

  100% {
    transform: translateY(100%);
  }
}

/* Modal - Cyberpunk styling */
.modal-box {
  background: linear-gradient(145deg, rgba(15, 15, 25, 0.98), rgba(10, 10, 18, 0.98));
  padding: 40px 35px;
  border-radius: 20px;
  text-align: center;
  width: 380px;
  max-width: 90%;
  color: #ffffff;
  border: 1px solid rgba(0, 240, 255, 0.2);
  box-shadow:
    0 0 40px rgba(0, 240, 255, 0.15),
    0 25px 50px -12px rgba(0, 0, 0, 0.8),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
  position: relative;
  overflow: hidden;
  transform: scale(0.9);
  opacity: 0;
  animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* Animated border glow */
.modal-box::before {
  content: '';
  position: absolute;
  top: -1px;
  left: -1px;
  right: -1px;
  bottom: -1px;
  background: linear-gradient(45deg, #00f0ff, #7b2dff, #ff0055, #00f0ff);
  border-radius: 20px;
  z-index: -1;
  opacity: 0.4;
  animation: borderRotate 4s linear infinite;
  background-size: 400% 400%;
}

@keyframes borderRotate {
  0% {
    background-position: 0% 50%;
  }

  50% {
    background-position: 100% 50%;
  }

  100% {
    background-position: 0% 50%;
  }
}

/* Corner decorations */
.modal-box::after {
  content: '';
  position: absolute;
  top: 15px;
  left: 15px;
  width: 20px;
  height: 20px;
  border-top: 2px solid #00f0ff;
  border-left: 2px solid #00f0ff;
  opacity: 0.6;
}

@keyframes pop {
  from {
    transform: scale(0.8);
    opacity: 0;
  }

  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Header styling */
.modal-box h2 {
  margin-bottom: 12px;
  font-family: 'Orbitron', sans-serif;
  font-size: 1.4rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  background: linear-gradient(135deg, #00f0ff, #7b2dff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.modal-box p {
  color: #a0a0b0;
  margin-bottom: 30px;
  font-size: 0.95rem;
  line-height: 1.5;
}

/* Alert badge */
.modal-box p::before {
  content: '⚠️ ';
}

/* Buttons container */
.modal-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  flex-direction: column;
}

/* Primary Button - Login */
.btn-primary {
  background: linear-gradient(135deg, #00f0ff, #7b2dff);
  padding: 14px 24px;
  border-radius: 12px;
  text-decoration: none;
  color: #0a0a0f;
  font-weight: 700;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0, 240, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.5s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 35px rgba(0, 240, 255, 0.5);
}

.btn-primary:hover::before {
  left: 100%;
}

.btn-primary:active {
  transform: translateY(0);
}

/* Secondary Button - Guest */
.btn-secondary {
  background: transparent;
  padding: 14px 24px;
  border-radius: 12px;
  border: 2px solid rgba(255, 255, 255, 0.15);
  color: #a0a0b0;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  font-family: 'Inter', sans-serif;
}

.btn-secondary:hover {
  border-color: #00f0ff;
  color: #00f0ff;
  background: rgba(0, 240, 255, 0.05);
  transform: translateY(-2px);
}

/* Close button - Enhanced */
.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  cursor: pointer;
  font-size: 24px;
  color: #606070;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
  z-index: 10;
}

.close-btn:hover {
  color: #ff0055;
  background: rgba(255, 0, 85, 0.1);
  transform: rotate(90deg);
}

/* Responsive */
@media (max-width: 480px) {
  .modal-box {
    width: 90%;
    padding: 30px 25px;
  }

  .modal-box h2 {
    font-size: 1.2rem;
  }

  .btn-primary,
  .btn-secondary {
    padding: 12px 20px;
    font-size: 0.9rem;
  }
}
</style>

<div id="loginModal" class="modal-overlay">
  <div class="modal-content-wrapper">
    <div class="modal-header">
      <div class="lock-icon">
        <i class="fas fa-lock"></i>
      </div>
      <h2>Authentication Required</h2>
      <p>You must be logged in to access this part of DarkHunter</p>
    </div>

    <div class="modal-body">
      <div class="benefits-list">
        <div class="benefit-item">
          <i class="fas fa-check-circle"></i>
          <span>Track your progress in labs</span>
        </div>
        <div class="benefit-item">
          <i class="fas fa-check-circle"></i>
          <span>Earn XP and rank up globaly</span>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <a href="/DarkHunter/Public/login.php" class="btn-primary">
        <i class="fas fa-sign-in-alt"></i> LOGIN NOW
      </a>

      <?php if (!isset($isStrictAuth) || !$isStrictAuth): ?>
      <button id="guestBtn" class="btn-outline-guest" onclick="LoginModal.close()">
        Continue as Guest
      </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const LoginModal = {
  modal: null,
  guestBtn: null,
  isProtected: <?php echo (isset($isStrictAuth) && $isStrictAuth) ? 'true' : 'false'; ?>,

  init() {
    this.modal = document.getElementById("loginModal");
    this.guestBtn = document.getElementById("guestBtn");

    this.modal.addEventListener('click', (e) => {
      if (e.target === this.modal && !this.isProtected) {
        this.close();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === "Escape" && !this.isProtected) {
        this.close();
      }
    });
  },

  show() {
    this.modal.style.display = "flex";
    void this.modal.offsetWidth; // Trigger reflow
    this.modal.classList.add("active");
    document.body.style.overflow = "hidden";

    if (this.isProtected && this.guestBtn) {
      this.guestBtn.style.display = "none";
    }
  },

  close() {
    if (!this.isProtected) {
      this.modal.classList.remove("active");
      setTimeout(() => {
        this.modal.style.display = "none";
        document.body.style.overflow = "";
      }, 300);
    } else {
      const wrapper = this.modal.querySelector('.modal-content-wrapper');
      wrapper.classList.add('shake-animation');
      setTimeout(() => wrapper.classList.remove('shake-animation'), 500);
    }
  }
};

document.addEventListener("DOMContentLoaded", () => LoginModal.init());
</script>

<style>
@keyframes shake {

  0%,
  100% {
    transform: translateX(0);
  }

  25% {
    transform: translateX(-10px);
  }

  75% {
    transform: translateX(10px);
  }
}

.shake-animation {
  animation: shake 0.3s ease-in-out;
  border: 1px solid var(--neon-red) !important;
}
</style>