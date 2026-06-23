<?php
$pageTitle = 'Google Authenticator Setup';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
require_once 'libraries/GoogleAuthenticator.php';
requireLogin();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT google_auth_secret, google_auth_enabled, ga_backup_code FROM users WHERE id = ?");
$stmt->execute([$userId]);
$ga = $stmt->fetch();

$gaEnabled = !empty($ga['google_auth_enabled']);
$gaSecret = $ga['google_auth_secret'] ?? '';
$hasBackup = !empty($ga['ga_backup_code']);

$step = 'status';
$error = '';
$success = '';

// ─── Disable GA ───
if (isset($_POST['disable_ga'])) {
    requireCSRF();
    $pdo->prepare("UPDATE users SET google_auth_secret = '', google_auth_enabled = 0 WHERE id = ?")->execute([$userId]);
    setFlashMessage('success', 'Google Authenticator disabled.');
    redirect('ga_setup.php');
}

// ─── Set backup password ───
if (isset($_POST['set_backup'])) {
    requireCSRF();
    $backup = $_POST['backup_password'] ?? '';
    $confirm = $_POST['confirm_backup'] ?? '';
    if (strlen($backup) < 6) {
        $error = 'Backup password must be at least 6 characters.';
    } elseif ($backup !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($backup, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET ga_backup_code = ? WHERE id = ?")->execute([$hash, $userId]);
        $hasBackup = true;
        $success = 'Backup password set successfully.';
    }
}

// ─── Clear backup password ───
if (isset($_POST['clear_backup'])) {
    requireCSRF();
    $pdo->prepare("UPDATE users SET ga_backup_code = '' WHERE id = ?")->execute([$userId]);
    $hasBackup = false;
    $success = 'Backup password removed.';
}

// ─── Change backup password ───
if (isset($_POST['change_backup'])) {
    requireCSRF();
    $old = $_POST['old_backup'] ?? '';
    $new = $_POST['new_backup'] ?? '';
    $confirm = $_POST['confirm_new_backup'] ?? '';

    $st = $pdo->prepare("SELECT ga_backup_code FROM users WHERE id = ?");
    $st->execute([$userId]);
    $stored = $st->fetchColumn();

    if (!password_verify($old, $stored)) {
        $error = 'Current backup password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New backup password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET ga_backup_code = ? WHERE id = ?")->execute([$hash, $userId]);
        $success = 'Backup password changed successfully.';
    }
}

// ─── Verify backup password (AJAX) ───
if (isset($_POST['check_backup'])) {
    requireCSRF();
    $check = $_POST['check_value'] ?? '';
    $st = $pdo->prepare("SELECT ga_backup_code FROM users WHERE id = ?");
    $st->execute([$userId]);
    $stored = $st->fetchColumn();
    echo json_encode(['valid' => password_verify($check, $stored)]);
    exit;
}

// ─── Verify and enable GA ───
if (isset($_POST['verify_ga'])) {
    requireCSRF();
    $secret = $_POST['secret'] ?? '';
    $code = preg_replace('/[^0-9]/', '', $_POST['verify_code'] ?? '');
    if (strlen($code) !== 6) {
        $error = 'Please enter a valid 6-digit code.';
    } elseif (!GoogleAuthenticator::verify($code, $secret)) {
        $error = 'Invalid code. Make sure your Google Authenticator app shows the correct code.';
    } else {
        $pdo->prepare("UPDATE users SET google_auth_secret = ?, google_auth_enabled = 1 WHERE id = ?")->execute([$secret, $userId]);
        setFlashMessage('success', 'Google Authenticator enabled successfully.');
        notifyEmail('Two-Factor Auth', 'enabled');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Enabled', 'Google Authenticator', $userId);
        redirect('ga_setup.php');
    }
    $step = 'verify';
    $gaSecret = $secret;
}

// ─── Generate secret for setup ───
if (!$gaEnabled && (!isset($_POST['verify_ga']) || $error)) {
    $gaSecret = GoogleAuthenticator::generateSecret();
    $step = 'setup';
}

if ($gaEnabled) {
    $step = 'status';
}

$showSecret = $gaSecret;
?>
<?php require 'includes/header.php'; ?>
<?php require 'includes/navbar.php'; ?>
<?php require 'includes/sidebar.php'; ?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><i class="fas fa-shield-halved me-2 text-primary"></i>Google Authenticator</h4>
                <p class="text-muted mb-0">Manage two-factor authentication for your account</p>
            </div>
        </div>

        <?php if ($error): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?>, confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'swal2-confirm' } });
        });
        </script>
        <?php endif; ?>

        <?php if ($success): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({ icon: 'success', title: 'Success', text: <?php echo json_encode($success); ?>, timer: 2500, showConfirmButton: false, buttonsStyling: false, background: '#191c24', color: '#fff' });
        });
        </script>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <?php if ($step === 'status' && $gaEnabled): ?>
                    <!-- ✓ Already enabled -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div style="font-size:64px;color:#00d25b;margin-bottom:16px;"><i class="fas fa-check-circle"></i></div>
                            <h4 class="mb-2">Google Authenticator is Active</h4>
                            <p class="text-muted mb-4">Your account is protected with two-factor authentication.</p>

                            <div class="mb-4 p-3" style="background:rgba(0,0,0,0.2);border-radius:12px;display:inline-block;">
                                <label class="text-muted small d-block mb-2">Secret Key</label>
                                <code id="secretKeyDisplay" style="font-size:16px;letter-spacing:2px;word-break:break-all;"><?php echo htmlspecialchars($showSecret); ?></code>
                                <div class="mt-2">
                                    <button onclick="copySecret()" class="btn btn-sm btn-outline-info"><i class="fas fa-copy me-1"></i>Copy Key</button>
                                </div>
                            </div>

                            <form method="POST" id="disableGaForm">
                                <?= csrfField() ?>
                                <button type="button" class="btn btn-outline-danger" onclick="confirmAction('Are you sure you want to disable Google Authenticator?', this.form, 'disable_ga');">
                                    <i class="fas fa-ban me-1"></i> Disable Google Authenticator
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- 🔑 Backup Password -->
                    <div class="card mt-4">
                        <div class="card-body py-4">
                            <h5 class="mb-3"><i class="fas fa-key me-2 text-warning"></i>Backup Password</h5>
                            <p class="text-muted small mb-3">If you lose access to your Google Authenticator app, use this backup password to login instead of the 6-digit code.</p>

                            <?php if ($hasBackup): ?>
                                <div style="color:#00d25b;margin-bottom:16px;"><i class="fas fa-check-circle me-1"></i> Backup password is set</div>

                                <!-- View / Verify -->
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-1">Verify backup password</label>
                                    <div class="input-group">
                                        <input type="password" id="checkBackupInput" class="form-control" placeholder="Type your backup password to verify" style="max-width:320px;">
                                        <button class="btn btn-outline-info" onclick="checkBackup()"><i class="fas fa-check me-1"></i>Check</button>
                                        <button class="btn btn-outline-secondary" onclick="toggleBackupView()"><i class="fas fa-eye"></i></button>
                                    </div>
                                    <div id="checkBackupResult" class="mt-1 small"></div>
                                </div>

                                <!-- Change -->
                                <form method="POST" class="row g-3">
                                    <?= csrfField() ?>
                                    <div class="col-sm-4">
                                        <input type="password" name="old_backup" class="form-control" placeholder="Current backup password" required>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="password" name="new_backup" class="form-control" placeholder="New backup password" required minlength="6">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="password" name="confirm_new_backup" class="form-control" placeholder="Confirm new password" required minlength="6">
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" name="change_backup" class="btn btn-warning"><i class="fas fa-save me-1"></i>Change Password</button>
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmAction('Remove backup password? You will not be able to login without Google Authenticator if you forget it.', document.getElementById('clearBackupForm'), 'clear_backup');"><i class="fas fa-trash me-1"></i>Remove</button>
                                    </div>
                                </form>
                                <form method="POST" id="clearBackupForm" style="display:none;"><?= csrfField() ?></form>
                            <?php else: ?>
                                <form method="POST" class="row g-3">
                                    <?= csrfField() ?>
                                    <div class="col-sm-5">
                                        <input type="password" name="backup_password" class="form-control" placeholder="New backup password" required minlength="6">
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="password" name="confirm_backup" class="form-control" placeholder="Confirm password" required minlength="6">
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" name="set_backup" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Set</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($step === 'verify'): ?>
                    <!-- 🔐 Verify setup -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <h4 class="mb-3">Verify Setup</h4>
                            <p class="text-muted mb-4">Enter the 6-digit code from your Google Authenticator app to confirm the setup.</p>
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="secret" value="<?php echo htmlspecialchars($gaSecret); ?>">
                                <div class="mb-4">
                                    <input type="text" name="verify_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="form-control form-control-lg text-center" style="max-width:200px;margin:0 auto;font-size:24px;letter-spacing:8px;" placeholder="000000" required autofocus>
                                </div>
                                <button type="submit" name="verify_ga" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i> Verify & Enable
                                </button>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- ⚙ New setup -->
                    <div class="card">
                        <div class="card-body py-5">
                            <h4 class="mb-4">Set Up Google Authenticator</h4>

                            <div class="row align-items-center">
                                <div class="col-md-5 text-center mb-4 mb-md-0">
                                    <?php
                                    $label = $_SESSION['user_name'] . ' (CMS)';
                                    $qrUrl = GoogleAuthenticator::getQRCodeUrl($label, $gaSecret, 'CMS BCA AI/ML');
                                    ?>
                                    <div style="display:inline-block;background:#fff;padding:12px;border-radius:12px;">
                                        <div id="gaQRCode"></div>
                                    </div>
                                    <p class="text-muted small mt-2">Scan with Google Authenticator app</p>

                                    <div class="mt-3">
                                        <code id="secretKeyDisplay" style="font-size:13px;letter-spacing:1px;word-break:break-all;background:rgba(0,0,0,0.2);padding:6px 12px;border-radius:8px;display:inline-block;"><?php echo htmlspecialchars($gaSecret); ?></code>
                                        <button onclick="copySecret()" class="btn btn-sm btn-outline-info ms-1"><i class="fas fa-copy"></i></button>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <ol style="padding-left:20px;line-height:2;">
                                        <li>Install <strong>Google Authenticator</strong> from your app store.</li>
                                        <li>Open the app and tap <strong>+</strong> (or Add Account).</li>
                                        <li>Select <strong>Scan a QR code</strong> and scan the image on the left.</li>
                                        <li>If you can't scan, click <strong>Copy Key</strong> and paste it in the app.</li>
                                        <li>Once added, enter the 6-digit code shown in the app below.</li>
                                    </ol>
                                    <form method="POST" class="mt-3">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="secret" value="<?php echo htmlspecialchars($gaSecret); ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-sm-5">
                                                <input type="text" name="verify_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="form-control form-control-lg text-center" style="font-size:24px;letter-spacing:8px;" placeholder="000000" required autofocus>
                                            </div>
                                            <div class="col-sm-4">
                                                <button type="submit" name="verify_ga" class="btn btn-primary btn-lg w-100">
                                                    <i class="fas fa-check me-1"></i> Verify
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function(){
    var qrEl = document.getElementById('gaQRCode');
    if (qrEl) {
        var url = <?php echo json_encode($qrUrl ?? ''); ?>;
        if (url) {
            try {
                new QRCode(qrEl, { text: url, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M });
            } catch(e) {
                qrEl.innerHTML = '<div style="width:200px;height:200px;display:flex;align-items:center;justify-content:center;color:#999;font-size:13px;text-align:center;padding:10px;">QR code<br>failed to load</div>';
            }
        }
    }
})();

function copySecret() {
    var el = document.getElementById('secretKeyDisplay');
    if (el) {
        var text = el.textContent || el.innerText;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function(){
                Swal.fire({ icon: 'success', title: 'Copied!', text: 'Secret key copied to clipboard', timer: 1500, showConfirmButton: false, buttonsStyling: false, background: '#191c24', color: '#fff' });
            }).catch(function(){
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }
}

function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        Swal.fire({ icon: 'success', title: 'Copied!', text: 'Secret key copied to clipboard', timer: 1500, showConfirmButton: false, buttonsStyling: false, background: '#191c24', color: '#fff' });
    } catch(e) {}
    document.body.removeChild(ta);
}

function confirmAction(msg, form, actionName) {
    Swal.fire({
        title: 'Are you sure?',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: { confirmButton: 'swal2-confirm', cancelButton: 'swal2-cancel' },
        background: '#191c24', color: '#fff'
    }).then(function(result){
        if (result.isConfirmed) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = actionName;
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    });
}

function toggleBackupView() {
    var inp = document.getElementById('checkBackupInput');
    if (inp.type === 'password') { inp.type = 'text'; } else { inp.type = 'password'; }
}

function checkBackup() {
    var val = document.getElementById('checkBackupInput').value;
    var res = document.getElementById('checkBackupResult');
    if (!val) { res.innerHTML = '<span style="color:#fc424a;">Please enter a backup password.</span>'; return; }

    var formData = new FormData();
    var csrf = document.querySelector('input[name="csrf_token"]');
    formData.append('csrf_token', csrf ? csrf.value : '');
    formData.append('check_backup', '1');
    formData.append('check_value', val);

    fetch('ga_setup.php', { method: 'POST', body: formData })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.valid) {
                res.innerHTML = '<span style="color:#00d25b;"><i class="fas fa-check-circle"></i> Correct! That is your backup password.</span>';
            } else {
                res.innerHTML = '<span style="color:#fc424a;"><i class="fas fa-times-circle"></i> Incorrect. Try again.</span>';
            }
        })
        .catch(function(){
            res.innerHTML = '<span style="color:#fc424a;">Error verifying. Please try again.</span>';
        });
}
</script>

<?php require 'includes/footer.php'; ?>
