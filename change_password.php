<?php
$pageTitle = 'Change Password';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        setFlashMessage('error', 'All fields are required.');
    } elseif (strlen($new) < 6) {
        setFlashMessage('error', 'New password must be at least 6 characters.');
    } elseif ($new !== $confirm) {
        setFlashMessage('error', 'New passwords do not match.');
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['password'])) {
            setFlashMessage('error', 'Current password is incorrect.');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            setFlashMessage('success', 'Password changed successfully.');
            notifyEmail('Password', 'changed');
            logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Changed', 'Password', $userId);
            redirect('profile.php');
        }
    }
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><i class="fas fa-key me-2 text-primary"></i>Change Password</h4>
                <p class="text-muted mb-0">Update your account password</p>
            </div>
            <a href="profile.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Profile</a>
        </div>

        <?= getFlashMessage() ?>

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="At least 6 characters">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
