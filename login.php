<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_semester'] = $user['semester'];

            $stmt = $pdo->prepare("SELECT id FROM profiles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO profiles (user_id, name, college, semester, roll_no, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user['id'], $user['name'], $user['college'], $user['semester'], $user['roll_no'], $user['email'], $user['phone']]);
            }

            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>">
    <link rel="stylesheet" href="css/dark-fix.css?v=<?php echo filemtime('css/dark-fix.css'); ?>">
    <link rel="stylesheet" href="css/auth.css?v=<?php echo filemtime('css/auth.css'); ?>">
    <link rel="stylesheet" href="css/sweetalert2-dark.css?v=<?php echo filemtime('css/sweetalert2-dark.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>document.documentElement.setAttribute('data-theme', 'dark');</script>
    <style>
      /* ===== PASSWORD INPUT — kill ALL native white/blue backgrounds ===== */
      .auth-input-wrap input[type="password"],
      .auth-input-wrap input#password {
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        color: #ffffff !important;
        -webkit-appearance: none !important;
        appearance: none !important;
        caret-color: #ffffff !important;
        padding: 14px 16px !important;
        border-radius: 0 !important;
      }
      .auth-input-wrap input[type="password"]:focus,
      .auth-input-wrap input#password:focus {
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
      }
      /* Chrome autofill BULLETPROOF fix — blends with wrapper glass */
      .auth-input-wrap input[type="password"]:-webkit-autofill,
      .auth-input-wrap input[type="password"]:-webkit-autofill:hover,
      .auth-input-wrap input[type="password"]:-webkit-autofill:focus,
      .auth-input-wrap input[type="password"]:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 1000px rgba(255,255,255,0.04) inset !important;
        box-shadow: 0 0 0 1000px rgba(255,255,255,0.04) inset !important;
        -webkit-text-fill-color: #ffffff !important;
        caret-color: #ffffff !important;
        background: transparent !important;
        background-color: transparent !important;
        border-radius: 0 !important;
        transition: background-color 5000s ease-in-out 0s !important;
      }
      /* ===== SWEETALERT2 DARK FIX ===== */
      .swal2-popup { background: #191c24 !important; border: 1px solid #2c2e3e !important; }
      .swal2-title { color: #ffffff !important; }
      .swal2-html-container { color: #a3a6b7 !important; }
      .swal2-styled.swal2-confirm {
        background: linear-gradient(90deg, #8a2be2, #da70d6) !important;
        background-color: #fc424a !important;
        color: #ffffff !important;
        padding: 12px 32px !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        border: none !important;
        border-radius: 8px !important;
        outline: none !important;
        box-shadow: 0 0 14px rgba(252, 66, 74, 0.4) !important;
        transition: all 0.25s ease !important;
        min-width: 100px !important;
        cursor: pointer !important;
        font-family: 'Inter', sans-serif !important;
      }
      .swal2-styled.swal2-confirm:hover {
        box-shadow: 0 0 24px rgba(252, 66, 74, 0.6) !important;
        transform: translateY(-1px) !important;
      }
    </style>
</head>
<body class="auth-body">
    <canvas id="authCanvas"></canvas>


    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card-top">
                <div class="auth-brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function(){
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: <?php echo json_encode($error); ?>,
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: { confirmButton: 'swal2-confirm' }
                    });
                });
                </script>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="auth-field">
                    <label for="username">Username</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username"
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus
                               placeholder="Enter your username">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required
                               placeholder="Enter your password">
                        <button type="button" class="auth-pass-toggle" onclick="togglePassword()" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-btn">
                    <span>Sign In</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M3 8h10m0 0l-4-4m4 4l-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePassword() {
        var pwd = document.getElementById('password');
        var icon = document.querySelector('.auth-pass-toggle i');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    </script>
<script>
(function(){
  var c = document.getElementById('authCanvas'), ctx = c.getContext('2d');
  var particles = [], w, h;
  function resize(){ w = c.width = window.innerWidth; h = c.height = window.innerHeight; }
  resize(); window.addEventListener('resize', resize);
  var count = 80;
  for (var i = 0; i < count; i++) {
    particles.push({
      x: Math.random() * w, y: Math.random() * h,
      vx: (Math.random() - 0.5) * 0.5, vy: (Math.random() - 0.5) * 0.5,
      r: Math.random() * 2 + 0.5, a: Math.random() * 0.4 + 0.1
    });
  }
  function draw() {
    ctx.clearRect(0, 0, w, h);
    for (var i = 0; i < count; i++) {
      var p = particles[i];
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0 || p.x > w) p.vx *= -1;
      if (p.y < 0 || p.y > h) p.vy *= -1;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(0, 240, 255, ' + p.a + ')';
      ctx.fill();
      for (var j = i + 1; j < count; j++) {
        var q = particles[j];
        var dx = p.x - q.x, dy = p.y - q.y, dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 150) {
          ctx.beginPath();
          ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y);
          ctx.strokeStyle = 'rgba(0, 240, 255, ' + (0.08 * (1 - dist / 150)) + ')';
          ctx.stroke();
        }
      }
    }
    requestAnimationFrame(draw);
  }
  draw();
})();
</script>
</body>
</html>
