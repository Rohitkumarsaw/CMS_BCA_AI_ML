<?php
$pageTitle = 'SMTP Settings';
$extraCSS = ['smtp_settings.css'];
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) DEFAULT NULL,
    site_description TEXT DEFAULT NULL,
    partner_details TEXT DEFAULT NULL,
    mail_config TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

try {
    $pdo->exec("ALTER TABLE system_settings ADD COLUMN mail_config TEXT DEFAULT NULL");
} catch (PDOException $e) {}

$stmt = $pdo->prepare("SELECT mail_config FROM system_settings LIMIT 1");
$stmt->execute();
$row = $stmt->fetch();
$config = $row && $row['mail_config'] ? json_decode($row['mail_config'], true) : [];

// Fallback to config/mail.php for display only
if (empty($config)) {
    require_once 'config/mail.php';
    $config = [
        'mail_host' => MAIL_HOST,
        'mail_port' => MAIL_PORT,
        'mail_username' => MAIL_USERNAME,
        'mail_password' => MAIL_PASSWORD,
        'mail_from' => MAIL_FROM,
        'mail_from_name' => MAIL_FROM_NAME,
        'mail_to' => MAIL_TO,
        'mail_encryption' => 'tls'
    ];
}

$host = $config['mail_host'] ?? 'smtp.gmail.com';
$port = $config['mail_port'] ?? 587;
$username = $config['mail_username'] ?? '';
$password = $config['mail_password'] ?? '';
$from = $config['mail_from'] ?? '';
$fromName = $config['mail_from_name'] ?? 'CMS BCA AI/ML';
$to = $config['mail_to'] ?? '';
$encryption = $config['mail_encryption'] ?? 'tls';

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><i class="fas fa-cogs me-2 text-primary"></i>SMTP Settings</h4>
                <p class="text-muted mb-0">Manage email notification recipient</p>
            </div>
        </div>

        <div class="smtp-wrapper">

            <div class="smtp-status" id="smtpStatus"></div>

            <form id="smtpForm" method="POST">

                <!-- READ-ONLY: Server Config -->
                <div class="smtp-card">
                    <h5><i class="fas fa-server me-2 text-info"></i>SMTP Server</h5>
                    <p class="card-subtitle">Mail server configuration (read-only)</p>
                    <div class="smtp-row">
                        <div class="smtp-field">
                            <label>SMTP Host</label>
                            <input type="text" value="<?= htmlspecialchars($host) ?>" readonly>
                        </div>
                        <div class="smtp-field">
                            <label>SMTP Port</label>
                            <input type="text" value="<?= $port ?>" readonly>
                        </div>
                    </div>
                    <div class="smtp-row">
                        <div class="smtp-field">
                            <label>Encryption</label>
                            <input type="text" value="<?= strtoupper($encryption) ?>" readonly>
                        </div>
                        <div class="smtp-field">
                            <label>From Name</label>
                            <input type="text" value="<?= htmlspecialchars($fromName) ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- READ-ONLY: Auth -->
                <div class="smtp-card">
                    <h5><i class="fas fa-lock me-2 text-warning"></i>Authentication</h5>
                    <p class="card-subtitle">Sender credentials (read-only)</p>
                    <div class="smtp-field">
                        <label>Sender Email</label>
                        <input type="text" value="<?= htmlspecialchars($username) ?>" readonly>
                    </div>
                    <div class="smtp-field">
                        <label>App Password</label>
                        <div class="password-wrap">
                            <input type="password" id="mail_password" value="<?= htmlspecialchars($password) ?>" readonly autocomplete="off">
                            <button type="button" class="password-toggle" onclick="toggleSMTPPassword()" title="Show/Hide password"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="smtp-field">
                        <label>From Email</label>
                        <input type="text" value="<?= htmlspecialchars($from) ?>" readonly>
                    </div>
                </div>

                <!-- EDITABLE: Recipient -->
                <div class="smtp-card">
                    <h5><i class="fas fa-bell me-2 text-success"></i>Notification Recipient</h5>
                    <p class="card-subtitle">All CRUD notifications will be sent to this email</p>
                    <div class="smtp-field">
                        <label for="mail_to">Send Notifications To <span class="text-danger">*</span></label>
                        <input type="email" id="mail_to" name="mail_to" value="<?= htmlspecialchars($to) ?>" placeholder="your-email@gmail.com" required>
                    </div>
                </div>

                <div class="smtp-actions">
                    <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fas fa-save me-1"></i>Update Email</button>
                    <button type="button" class="btn btn-outline-info" id="testBtn"><i class="fas fa-paper-plane me-1"></i>Send Test Email</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
function toggleSMTPPassword() {
    var input = document.getElementById('mail_password');
    var icon = document.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

document.getElementById('smtpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('saveBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    var data = new FormData(this);
    data.append('action', 'save');
    fetch('smtp_settings_handler.php', { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var status = document.getElementById('smtpStatus');
            status.className = 'smtp-status ' + res.status;
            status.textContent = res.message;
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i>Update Email';
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i>Update Email';
        });
});

document.getElementById('testBtn').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    var data = new FormData(document.getElementById('smtpForm'));
    data.append('action', 'test');
    fetch('smtp_settings_handler.php', { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var status = document.getElementById('smtpStatus');
            status.className = 'smtp-status ' + res.status;
            status.textContent = res.message;
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test Email';
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test Email';
        });
});
</script>

<?php include 'includes/footer.php'; ?>
