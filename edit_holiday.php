<?php
$pageTitle = 'Edit Holiday';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

$holiday_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($holiday_id <= 0) {
    setFlashMessage('danger', 'Invalid holiday ID.');
    redirect('holiday.php');
}

$stmt = $pdo->prepare("SELECT * FROM holidays WHERE id = ? AND user_id = ?");
$stmt->execute([$holiday_id, $userId]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Holiday not found.');
    redirect('holiday.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = sanitize($_POST['date']);
    $holidayName = sanitize($_POST['holiday_name']);
    $type = sanitize($_POST['type']);

    if (empty($date) || empty($holidayName) || empty($type)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: edit_holiday.php?id=' . $holiday_id);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE holidays SET date = ?, holiday_name = ?, type = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$date, $holidayName, $type, $holiday_id, $userId]);

    setFlashMessage('success', 'Holiday updated successfully');
    header('Location: holiday.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Holiday</h4>
                    <p class="text-muted mb-0">Update holiday record</p>
                </div>
                <a href="holiday.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
<form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?= $record['date'] ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="holiday_name" class="form-label">Holiday Name *</label>
                                <input type="text" class="form-control" id="holiday_name" name="holiday_name" value="<?= htmlspecialchars($record['holiday_name']) ?>" required placeholder="e.g., Republic Day">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?= htmlspecialchars($record['type'] ?? '') ?>" placeholder="National, College, Private, or custom">
                                <datalist id="type_list">
                                    <option value="National">
                                    <option value="College">
                                    <option value="Private">
                                </datalist>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Holiday
                            </button>
                            <a href="holiday.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
