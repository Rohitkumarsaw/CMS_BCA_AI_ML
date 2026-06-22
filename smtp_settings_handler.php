<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Ensure table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        site_name VARCHAR(255) DEFAULT NULL,
        site_description TEXT DEFAULT NULL,
        partner_details TEXT DEFAULT NULL,
        mail_config TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

if ($action === 'save') {
    $mailTo = trim($_POST['mail_to'] ?? '');

    if (!filter_var($mailTo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
        exit;
    }

    // Get existing config
    $stmt = $pdo->prepare("SELECT mail_config FROM system_settings LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    $config = $row && $row['mail_config'] ? json_decode($row['mail_config'], true) : [];

    // Fallback to config/mail.php constants if no DB config yet
    if (empty($config) && file_exists('config/mail.php')) {
        require_once 'config/mail.php';
        $config = [
            'mail_host' => defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com',
            'mail_port' => defined('MAIL_PORT') ? MAIL_PORT : 587,
            'mail_username' => defined('MAIL_USERNAME') ? MAIL_USERNAME : '',
            'mail_password' => defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '',
            'mail_from' => defined('MAIL_FROM') ? MAIL_FROM : '',
            'mail_from_name' => defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'CMS BCA AI/ML',
            'mail_to' => defined('MAIL_TO') ? MAIL_TO : '',
            'mail_encryption' => 'tls'
        ];
    }

    // Update only mail_to
    $config['mail_to'] = $mailTo;

    $json = json_encode($config);
    $stmt = $pdo->prepare("SELECT id FROM system_settings LIMIT 1");
    $stmt->execute();
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE system_settings SET mail_config = ? WHERE id = ?");
        $stmt->execute([$json, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO system_settings (mail_config) VALUES (?)");
        $stmt->execute([$json]);
    }

    logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_name'] ?? 'User', 'Updated', 'SMTP Settings', null, 'Notification email set to: ' . $mailTo);
    echo json_encode(['status' => 'success', 'message' => 'Notification email updated successfully!']);
    exit;
}

if ($action === 'test') {
    require_once 'config/mail.php';
    require_once 'libraries/PHPMailer/src/Exception.php';
    require_once 'libraries/PHPMailer/src/PHPMailer.php';
    require_once 'libraries/PHPMailer/src/SMTP.php';

    // Get recipient from DB first, fallback to POST or config
    $stmt = $pdo->prepare("SELECT mail_config FROM system_settings LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    $dbConfig = $row && $row['mail_config'] ? json_decode($row['mail_config'], true) : [];

    $mailTo = $dbConfig['mail_to'] ?? $_POST['mail_to'] ?? MAIL_TO;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'tls';
        $mail->Port = (int)MAIL_PORT;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($mailTo);

        $mail->isHTML(true);
        $mail->Subject = 'Test Email - CMS BCA AI/ML';
        $mail->Body = '<div style="background:#0c0e1a;padding:40px;font-family:Arial,sans-serif">
            <div style="max-width:500px;margin:auto;background:#191c24;border-radius:10px;padding:30px;border:1px solid #2c2e3e">
                <h2 style="color:#fff;margin:0 0 10px;text-align:center">&#x2705; Test Successful!</h2>
                <p style="color:#a3a6b7;text-align:center;font-size:14px">Your SMTP settings are working correctly.</p>
                <hr style="border-color:#2c2e3e;margin:20px 0">
                <p style="color:#8f94a8;font-size:12px;text-align:center">CMS BCA AI/ML &bull; SITM College</p>
            </div>
        </div>';

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Test email sent successfully to ' . htmlspecialchars($mailTo) . '! Check your inbox.']);
    } catch (Exception $e) {
        $error = $mail->ErrorInfo;
        if (empty($error)) $error = $e->getMessage();
        echo json_encode(['status' => 'error', 'message' => 'Test email failed: ' . $error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
