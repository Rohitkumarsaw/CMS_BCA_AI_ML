<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
header('Content-Type: application/json');

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

$action = $_POST['action'] ?? '';

if ($action === 'save') {
    $config = json_encode([
        'mail_host' => $_POST['mail_host'] ?? 'smtp.gmail.com',
        'mail_port' => (int)($_POST['mail_port'] ?? 587),
        'mail_username' => $_POST['mail_username'] ?? '',
        'mail_password' => $_POST['mail_password'] ?? '',
        'mail_from' => $_POST['mail_from'] ?? '',
        'mail_from_name' => $_POST['mail_from_name'] ?? 'CMS BCA AI/ML',
        'mail_to' => $_POST['mail_to'] ?? '',
        'mail_encryption' => $_POST['mail_encryption'] ?? 'tls'
    ]);
    $stmt = $pdo->prepare("SELECT id FROM system_settings LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("UPDATE system_settings SET mail_config = ? WHERE id = ?")->execute([$config, $row['id']]);
    } else {
        $pdo->prepare("INSERT INTO system_settings (mail_config) VALUES (?)")->execute([$config]);
    }
    echo json_encode(['status' => 'success', 'message' => 'SMTP settings saved successfully.']);
    exit;
}

if ($action === 'test') {
    $cfg = [
        'mail_host' => $_POST['mail_host'] ?? 'smtp.gmail.com',
        'mail_port' => (int)($_POST['mail_port'] ?? 587),
        'mail_username' => $_POST['mail_username'] ?? '',
        'mail_password' => $_POST['mail_password'] ?? '',
        'mail_from' => $_POST['mail_from'] ?? '',
        'mail_from_name' => $_POST['mail_from_name'] ?? 'CMS BCA AI/ML',
        'mail_to' => $_POST['mail_to'] ?? '',
        'mail_encryption' => $_POST['mail_encryption'] ?? 'tls'
    ];
    require_once 'libraries/PHPMailer/src/Exception.php';
    require_once 'libraries/PHPMailer/src/PHPMailer.php';
    require_once 'libraries/PHPMailer/src/SMTP.php';
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $cfg['mail_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['mail_username'];
        $mail->Password = $cfg['mail_password'];
        $mail->SMTPSecure = $cfg['mail_encryption'];
        $mail->Port = $cfg['mail_port'];
        $mail->setFrom($cfg['mail_from'], $cfg['mail_from_name']);
        $mail->addAddress($cfg['mail_to']);
        $mail->Subject = 'CMS - SMTP Test Email';
        $mail->isHTML(true);
        $mail->Body = '<div style="font-family:Inter,sans-serif;padding:30px"><h2 style="color:#00d2ff">SMTP Test Successful</h2><p style="color:#4a4a6a">Your SMTP configuration is working correctly.</p><p style="color:#6b7280;font-size:13px">Sent from: ' . htmlspecialchars($cfg['mail_from']) . '<br>To: ' . htmlspecialchars($cfg['mail_to']) . '</p></div>';
        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Test email sent successfully! Check your inbox.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Test failed: ' . $mail->ErrorInfo]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
