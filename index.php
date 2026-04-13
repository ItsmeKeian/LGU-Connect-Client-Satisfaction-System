
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>LGU-Connect | Municipality of San Julian</title>
<link rel="stylesheet" href="assets/css/index.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" href="assets/img/logo.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

</head>
<body>

<div class="bg"></div>
<div class="orb o1"></div>
<div class="orb o2"></div>
<div class="orb o3"></div>

<div class="wrap">

  <!-- LEFT -->
  <div class="left">
    <div class="seal-wrap">
      <div class="seal-glow"></div>
      <div class="ring r2"></div>
      <div class="ring r1"></div>
      <img src="assets/img/logo.png"
           alt="Municipality of San Julian Official Seal"
           class="seal-img"
           onerror="this.style.opacity='0'" />
    </div>

    <div class="brand">
      <div class="brand-name">LGU<span>-Connect</span></div>
      <div class="brand-tag">Client Satisfaction System</div>
    </div>

    <div class="divider"></div>

    <p class="brand-desc">
      <strong>Municipality of San Julian</strong><br>
      Eastern Samar, Philippines<br><br>
      An integrated digital platform for<br>
      measuring and reporting citizen satisfaction<br>
      in compliance with <strong>RA 11032</strong>.
    </p>
  </div>

  <!-- RIGHT -->
  <div class="right">
    <div class="card">

      <div class="c-welcome">Welcome Back</div>
      <div class="c-title">Sign in to<br>your account</div>
      <div class="c-hint">Use your official LGU email credentials.</div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert">
          <span class="alert-icon">&#9888;</span>
          <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <form method="POST" action="php/login.php" id="loginForm" autocomplete="off">

        <div class="fg">
          <label class="flabel" for="email">Email Address</label>
          <div class="fi-wrap">
            <input type="email" id="email" name="email" class="finput"
                   placeholder="yourname@sanjulian.gov.ph" required
                   />
            <span class="ficon">&#9993;</span>
          </div>
        </div>

        <div class="fg">
          <label class="flabel" for="password">Password</label>
          <div class="fi-wrap">
            <input type="password" id="password" name="password" class="finput"
                   placeholder="Enter your password" required />
            <span class="ficon">&#128274;</span>
            <button type="button" class="toggle-pw" id="togglePw">&#128065;</button>
          </div>
        </div>

        <div class="extras">
          <label class="remember">
            <input type="checkbox" name="remember"/>
            <span>Remember me</span>
          </label>
          <a href="forgot-password.php" class="forgot">Forgot password?</a>
        </div>

        <button type="submit" class="btn-sub" id="submitBtn">
          Sign In &nbsp;&rarr;
        </button>
          
        <div class="card-bar"></div>
      </form>

      <div class="powered">
        Powered by <strong></strong>
        &nbsp;&middot;&nbsp; LGU-Connect 2026
      </div>

    </div>
  </div>

</div>

<footer class="footer">
  <p>&copy; 2026 Municipality of San Julian, Eastern Samar. All rights reserved.</p>
  <div class="online-badge">
    <div class="pdot"></div>
    SYSTEM ONLINE &nbsp;&middot;&nbsp; RA 11032 COMPLIANT
  </div>
</footer>

<script>
  // Password toggle
  const togglePw = document.getElementById('togglePw');
  const pwField  = document.getElementById('password');
  togglePw.addEventListener('click', () => {
    const show = pwField.type === 'password';
    pwField.type       = show ? 'text' : 'password';
    togglePw.innerHTML = show ? '&#128584;' : '&#128065;';
  });

  // Loading state
  document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled     = true;
    btn.textContent  = 'Signing in...';
  });

  // Subtle lift on focus
  document.querySelectorAll('.finput').forEach(el => {
    el.addEventListener('focus', () => {
      el.closest('.fg').style.cssText = 'transform:translateY(-2px);transition:transform .2s ease';
    });
    el.addEventListener('blur', () => {
      el.closest('.fg').style.transform = 'translateY(0)';
    });
  });
</script>

</body>
</html>