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
                <p class="text-muted mb-0">Configure email notifications for all CRUD operations</p>
            </div>
        </div>

        <?= getFlashMessage() ?>

        <div class="smtp-wrapper">

            <div class="smtp-status" id="smtpStatus"></div>

            <form id="smtpForm" method="POST">
                <div class="smtp-card">
                    <h5><i class="fas fa-server me-2 text-info"></i>SMTP Server</h5>
                    <p class="card-subtitle">Mail server configuration for sending emails</p>
                    <div class="smtp-row">
                        <div class="smtp-field">
                            <label for="mail_host">SMTP Host</label>
                            <input type="text" id="mail_host" name="mail_host" value="<?= htmlspecialchars($host) ?>" placeholder="smtp.gmail.com">
                            <div class="help-text">Gmail: smtp.gmail.com, Outlook: smtp.office365.com</div>
                        </div>
                        <div class="smtp-field">
                            <label for="mail_port">SMTP Port</label>
                            <input type="number" id="mail_port" name="mail_port" value="<?= $port ?>" placeholder="587">
                            <div class="help-text">TLS: 587, SSL: 465</div>
                        </div>
                    </div>
                    <div class="smtp-row">
                        <div class="smtp-field">
                            <label for="mail_encryption">Encryption</label>
                            <select id="mail_encryption" name="mail_encryption">
                                <option value="tls" <?= $encryption === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= $encryption === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                        <div class="smtp-field">
                            <label for="mail_from_name">From Name</label>
                            <input type="text" id="mail_from_name" name="mail_from_name" value="<?= htmlspecialchars($fromName) ?>" placeholder="CMS BCA AI/ML">
                            <div class="help-text">Display name shown in recipient's inbox</div>
                        </div>
                    </div>
                </div>

                <div class="smtp-card">
                    <h5><i class="fas fa-lock me-2 text-warning"></i>Authentication</h5>
                    <p class="card-subtitle">Gmail App Password or SMTP credentials</p>
                    <div class="smtp-field">
                        <label for="mail_username">Email Address (Sender)</label>
                        <input type="email" id="mail_username" name="mail_username" value="<?= htmlspecialchars($username) ?>" placeholder="your-email@gmail.com">
                        <div class="help-text">The Gmail/email address used to send notifications</div>
                    </div>
                    <div class="smtp-field">
                        <label for="mail_password">App Password</label>
                        <div class="password-wrap">
                            <input type="password" id="mail_password" name="mail_password" value="<?= htmlspecialchars($password) ?>" placeholder="16-digit app password" autocomplete="off">
                            <button type="button" class="password-toggle" onclick="toggleSMTPPassword()" title="Show/Hide password"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="help-text">16-digit Gmail App Password (no spaces). Get it from <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#0090e7">Google App Passwords</a></div>
                    </div>
                </div>

                <div class="smtp-card">
                    <h5><i class="fas fa-bell me-2 text-success"></i>Notification Recipient</h5>
                    <p class="card-subtitle">Where to receive CRUD update notifications</p>
                    <div class="smtp-field">
                        <label for="mail_to">Send Notifications To</label>
                        <input type="email" id="mail_to" name="mail_to" value="<?= htmlspecialchars($to) ?>" placeholder="your-email@gmail.com">
                        <div class="help-text">All CRUD operation notifications will be sent to this email</div>
                    </div>
                    <div class="smtp-field">
                        <label for="mail_from">From Email</label>
                        <input type="email" id="mail_from" name="mail_from" value="<?= htmlspecialchars($from) ?>" placeholder="your-email@gmail.com">
                        <div class="help-text">Usually same as sender email above</div>
                    </div>
                </div>

                <div class="smtp-card">
                    <h5><i class="fas fa-info-circle me-2 text-info"></i>How to Get Gmail App Password</h5>
                    <p class="card-subtitle">Step-by-step guide</p>
                    <ol style="color:var(--text-secondary);font-size:0.85rem;line-height:1.8;padding-left:20px;margin:0">
                        <li>Go to <a href="https://myaccount.google.com/security" target="_blank" style="color:#0090e7">Google Account Security</a></li>
                        <li>Enable <strong>2-Step Verification</strong> (if not already enabled)</li>
                        <li>Click on <strong>App Passwords</strong> (search in Settings if not visible)</li>
                        <li>Select <strong>Mail</strong> and <strong>Other (Custom name)</strong> &rarr; Name: <code>CMS Notification</code></li>
                        <li>Click <strong>Generate</strong> &rarr; Copy the 16-digit password &rarr; Paste it above</li>
                        <li>Your Gmail password WILL NOT work here &mdash; you <strong>must</strong> use an App Password</li>
                    </ol>
                </div>

                <div class="smtp-actions">
                    <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fas fa-save me-1"></i>Save Settings</button>
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
            if (res.status === 'success') status.style.color = '#00d25b';
            else status.style.color = '#fc424a';
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Settings';
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Settings';
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
            if (res.status === 'success') status.style.color = '#00d25b';
            else status.style.color = '#fc424a';
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test Email';
        })
        .catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test Email';
        });
});
</script>

<?php include 'includes/footer.php'; ?>
