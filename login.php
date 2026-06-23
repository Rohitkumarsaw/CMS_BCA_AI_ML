<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
require_once 'libraries/GoogleAuthenticator.php';

if (isLoggedIn()) {
    redirect('home.php');
}

$error = '';

// ─── Clear TOTP (back to login) ───
if (isset($_GET['clear_totp'])) {
    unset($_SESSION['totp_user_id']);
}

// ─── Handle backup password verification ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_backup'])) {
    $backup = $_POST['backup_password'] ?? '';
    $uid = $_POST['totp_uid'] ?? $_SESSION['totp_user_id'] ?? null;

    if (!$uid) {
        unset($_SESSION['totp_user_id']);
        $error = 'Session expired. Please login again.';
    } elseif (empty($backup)) {
        $error = 'Please enter your backup password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();

        if ($user && !empty($user['ga_backup_code']) && password_verify($backup, $user['ga_backup_code'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_semester'] = $user['semester'];

            $stmt2 = $pdo->prepare("SELECT id FROM profiles WHERE user_id = ?");
            $stmt2->execute([$user['id']]);
            if (!$stmt2->fetch()) {
                $stmt2 = $pdo->prepare("INSERT INTO profiles (user_id, name, college, semester, roll_no, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt2->execute([$user['id'], $user['name'], $user['college'] ?? '', $user['semester'], $user['roll_no'] ?? '', $user['email'] ?? '', $user['phone'] ?? '']);
            }

            unset($_SESSION['totp_user_id']);
            redirect('home.php');
        } else {
            $error = 'Invalid backup password.';
        }
    }
}

// ─── Handle TOTP verification ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_totp'])) {
    $code = preg_replace('/[^0-9]/', '', $_POST['totp_code'] ?? '');
    $uid = $_POST['totp_uid'] ?? $_SESSION['totp_user_id'] ?? null;

    if (!$uid) {
        unset($_SESSION['totp_user_id']);
        $error = 'Session expired. Please login again.';
    } elseif (strlen($code) !== 6) {
        $error = 'Please enter a valid 6-digit code.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();

        if ($user && !empty($user['google_auth_enabled']) && !empty($user['google_auth_secret'])) {
            if (GoogleAuthenticator::verify($code, $user['google_auth_secret'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_semester'] = $user['semester'];

                $stmt2 = $pdo->prepare("SELECT id FROM profiles WHERE user_id = ?");
                $stmt2->execute([$user['id']]);
                if (!$stmt2->fetch()) {
                    $stmt2 = $pdo->prepare("INSERT INTO profiles (user_id, name, college, semester, roll_no, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt2->execute([$user['id'], $user['name'], $user['college'] ?? '', $user['semester'], $user['roll_no'] ?? '', $user['email'] ?? '', $user['phone'] ?? '']);
                }

                unset($_SESSION['totp_user_id']);
                redirect('home.php');
            } else {
                $error = 'Invalid code. Please try again.';
            }
        } else {
            $error = 'Two-factor authentication not configured. Please login again.';
            unset($_SESSION['totp_user_id']);
        }
    }
}

// ─── Handle initial login ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['verify_totp']) && !isset($_POST['verify_backup'])) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (!empty($user['google_auth_enabled']) && !empty($user['google_auth_secret'])) {
                $_SESSION['totp_user_id'] = $user['id'];
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_semester'] = $user['semester'];

                $stmt2 = $pdo->prepare("SELECT id FROM profiles WHERE user_id = ?");
                $stmt2->execute([$user['id']]);
                if (!$stmt2->fetch()) {
                    $stmt2 = $pdo->prepare("INSERT INTO profiles (user_id, name, college, semester, roll_no, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt2->execute([$user['id'], $user['name'], $user['college'] ?? '', $user['semester'], $user['roll_no'] ?? '', $user['email'] ?? '', $user['phone'] ?? '']);
                }

                redirect('home.php');
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// ─── Show TOTP form if needed ───
$showTotpForm = isset($_SESSION['totp_user_id']);
?>
<!DOCTYPE html>
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
      /* ─── OTP INPUT STYLES ─── */
      .otp-input-wrap {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin: 24px 0 20px;
      }
      .otp-input-wrap input {
        width: 48px;
        height: 56px;
        text-align: center;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        background: rgba(255,255,255,0.04);
        border: 1.5px solid rgba(0,240,255,0.2);
        border-radius: 12px;
        outline: none;
        caret-color: #00f0ff;
        transition: all 0.2s ease;
        font-family: 'Inter', monospace;
      }
      .otp-input-wrap input:focus {
        border-color: rgba(0,240,255,0.6);
        box-shadow: 0 0 0 4px rgba(0,240,255,0.06), 0 0 20px rgba(0,240,255,0.06);
        background: rgba(0,240,255,0.04);
      }
      .otp-input-wrap input.filled {
        border-color: rgba(0,240,255,0.35);
        background: rgba(0,240,255,0.06);
      }
      .otp-info {
        text-align: center;
        color: rgba(255,255,255,0.35);
        font-size: 12px;
        margin-bottom: 20px;
      }
      .otp-info strong {
        color: rgba(0,240,255,0.6);
      }
      .otp-actions {
        display: flex;
        gap: 10px;
        margin-top: 4px;
      }
      .otp-actions .auth-btn {
        flex: 1;
      }
      .otp-back-btn {
        background: transparent;
        border: 1.5px solid rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.5);
        padding: 14px 20px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
        flex: 1;
        text-align: center;
        text-decoration: none;
      }
      .otp-back-btn:hover {
        border-color: rgba(255,255,255,0.15);
        color: #fff;
        background: rgba(255,255,255,0.04);
      }
      .otp-resend {
        text-align: center;
        margin-top: 16px;
      }
      .otp-resend button {
        background: none;
        border: none;
        color: rgba(0,240,255,0.5);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        font-family: 'Inter', sans-serif;
        transition: color 0.2s;
      }
      .otp-resend button:hover {
        color: rgba(0,240,255,0.8);
      }
      .otp-timer {
        text-align: center;
        color: rgba(255,255,255,0.25);
        font-size: 11px;
        margin-top: 12px;
        font-family: 'Inter', monospace;
      }
      #backupPassword:focus {
        border-color: rgba(0,240,255,0.6) !important;
        box-shadow: 0 0 0 4px rgba(0,240,255,0.06) !important;
        background: rgba(0,240,255,0.04) !important;
      }
      @media (max-width: 480px) {
        .otp-input-wrap input { width: 42px; height: 50px; font-size: 20px; }
        .otp-input-wrap { gap: 8px; }
      }
    </style>
</head>
<body class="auth-body">
    <canvas id="authCanvas"></canvas>

    <div class="auth-page">
        <div class="auth-card">
            <?php if ($showTotpForm): ?>
                <!-- ═══ TOTP FORM ═══ -->
                <div class="auth-card-top">
                    <div class="auth-brand-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h1>Two-Factor Auth</h1>
                    <p>Enter the code from your Google Authenticator app</p>
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

                <div id="totpMode">
                    <form method="POST" action="" id="totpForm">
                        <div class="otp-input-wrap" id="totpInputWrap">
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-box" required autofocus>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-box" required>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-box" required>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-box" required>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-box" required>
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" class="otp-box" required>
                        </div>
                        <input type="hidden" name="totp_code" id="totpHidden">
                        <input type="hidden" name="totp_uid" value="<?php echo (int)$_SESSION['totp_user_id']; ?>">

                        <div class="otp-actions">
                            <button type="submit" name="verify_totp" class="auth-btn" style="width:auto;flex:1;">
                                <span>Verify</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M13 4L6 12l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <a href="?clear_totp=1" class="otp-back-btn">Back</a>
                        </div>

                        <div class="otp-resend" style="margin-top:16px;">
                            <a href="#" onclick="toggleBackup();return false;" id="backupToggleLink" style="color:rgba(0,240,255,0.5);font-size:12px;font-weight:600;text-decoration:none;transition:color 0.2s;">Use backup password instead</a>
                        </div>
                    </form>
                </div>

                <div id="backupMode" style="display:none;">
                    <form method="POST" action="" id="backupForm">
                        <div style="margin:20px 0;">
                            <label style="color:rgba(255,255,255,0.5);font-size:12px;display:block;margin-bottom:8px;">Backup Password</label>
                            <input type="password" name="backup_password" id="backupPassword" class="form-control form-control-lg" style="background:rgba(255,255,255,0.04);border:1.5px solid rgba(0,240,255,0.2);border-radius:12px;color:#fff;padding:14px 16px;text-align:center;font-size:16px;font-family:'Inter',sans-serif;width:100%;outline:none;" placeholder="Enter your backup password">
                        </div>

                        <input type="hidden" name="totp_uid" value="<?php echo (int)$_SESSION['totp_user_id']; ?>">

                        <div class="otp-actions">
                            <button type="submit" name="verify_backup" class="auth-btn" style="width:auto;flex:1;">
                                <span>Verify</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M13 4L6 12l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <a href="?clear_totp=1" class="otp-back-btn">Back</a>
                        </div>

                        <div class="otp-resend" style="margin-top:16px;">
                            <a href="#" onclick="toggleBackup();return false;" style="color:rgba(0,240,255,0.5);font-size:12px;font-weight:600;text-decoration:none;transition:color 0.2s;">Use authenticator app instead</a>
                        </div>
                    </form>
                </div>

                <script>
                function toggleBackup() {
                    var t = document.getElementById('totpMode');
                    var b = document.getElementById('backupMode');
                    if (t.style.display !== 'none') {
                        t.style.display = 'none';
                        b.style.display = 'block';
                        document.getElementById('backupPassword').focus();
                    } else {
                        t.style.display = 'block';
                        b.style.display = 'none';
                        document.querySelector('.otp-box').focus();
                    }
                }
                (function(){
                    var boxes = document.querySelectorAll('.otp-box');
                    var hidden = document.getElementById('totpHidden');

                    function updateOtp() {
                        var val = '';
                        boxes.forEach(function(b){ val += b.value; });
                        hidden.value = val;
                        boxes.forEach(function(b){ b.classList.toggle('filled', b.value !== ''); });
                    }

                    boxes.forEach(function(box, idx){
                        box.addEventListener('input', function(){
                            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
                            updateOtp();
                            if (this.value && idx < boxes.length - 1) {
                                boxes[idx + 1].focus();
                            }
                        });
                        box.addEventListener('keydown', function(e){
                            if (e.key === 'Backspace' && !this.value && idx > 0) {
                                boxes[idx - 1].focus();
                                boxes[idx - 1].value = '';
                                updateOtp();
                            }
                            if (e.key === 'ArrowLeft' && idx > 0) { boxes[idx - 1].focus(); }
                            if (e.key === 'ArrowRight' && idx < boxes.length - 1) { boxes[idx + 1].focus(); }
                        });
                        box.addEventListener('paste', function(e){
                            e.preventDefault();
                            var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                            for (var i = 0; i < paste.length && i < boxes.length; i++) {
                                boxes[i].value = paste[i];
                            }
                            updateOtp();
                            var next = Math.min(paste.length, boxes.length - 1);
                            boxes[next].focus();
                        });
                    });

                    document.getElementById('totpForm').addEventListener('submit', function(){
                        updateOtp();
                    });

                    document.addEventListener('input', function(){
                        var filled = true;
                        boxes.forEach(function(b){ if (!b.value) filled = false; });
                        if (filled) {
                            setTimeout(function(){
                                document.querySelector('[name="verify_totp"]').click();
                            }, 200);
                        }
                    });
                })();
                </script>

            <?php else: ?>
                <!-- ═══ LOGIN FORM ═══ -->
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
            <?php endif; ?>
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